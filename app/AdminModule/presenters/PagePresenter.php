<?php

namespace AdminModule;


class PagePresenter extends SecuredPresenter {

    public $page;

    public $treeNodeId;

    public $preselectedParent;

    public $langsToAdd;

    public function beforeRender() {
        parent::beforeRender();
        $this->template->numberOfConcepts = 0;
        $this->template->numberOfTrashed = 0;
    }

    public function handleGetParentsUrls($treeNodeId) {

        $allLanguages = $this->presenter->langManagerService->getLangs();
        $defaultLanguage = $this->presenter->langManagerService->getDefaultLanguage();

        $defaults = $this->presenter->pageModel->getPagesUrls($treeNodeId, array_keys($allLanguages), $defaultLanguage);
        $this->payload->parentUrls = $defaults;
        $this->terminate();
    }


    public function renderDefault($id, $labelId = NULL, $tab = NULL, $waiting = FALSE) {
        //$p = $this->pageManagerService->getPage($id, 'cs');

        // is this page editable? (is it labeled with not editable label?)
        //$this->template->isEditable = $this->getModelPage()->isPageEditable($id, $this->user->identity);
        $this->template->isEditable = TRUE;

        //$identity = $this->user->identity;

        $this->treeNodeId = $id;

//        dump('sutu');
//        die();


        $this->template->treeNodeId = $id;
        $this->template->pageAuthor = 1;



        $activatedLanguages = $this->langManagerService->getLangs();

//        dump($this->pageModel->getReferencingPages($id));
//        die();

        // add referencing languages from different modules
        $referencingPages = $this->pageModel->getReferencingPages($id);
        $alienLangs = $this->langManagerService->getAlienLangs($referencingPages, $activatedLanguages);

//        dump($alienLangs);
//        die();

        $defaults = $this->presenter->pageManagerService->getDefaults($id, $waiting, array_keys($alienLangs));

//        $this->template->drive = array();
//        if($defaults){
//            $pageIds = array();
//            foreach($defaults as $lang=>$page){
//                $pageIds[] = $page['page_id'];
//                foreach($page['time_zone'] as $zones){
//                    foreach($zones as $status => $versions){
//                        foreach($versions as $p){
//                            $pageIds[] = $p['page_id'];
//                        }
//                    }
//                }
//            }
//            if ($pageIds) {
//                $this->template->drive['galleries'] = $this->presenter->virtualDriveService->getGalleriesByPageId($pageIds);
//                $this->template->drive['files'] = $this->presenter->virtualDriveService->getFilesByPageId($pageIds);
//            }
//        }
        //dump($defaults);
        $this->template->defaults = $defaults;

//        dump($defaults);
//        die();

//        dump($labelId);
//        die();

        $labelRoot = NULL;
        if (!empty($labelId)) {
            // set parent by labelId
            //$labelRoot = $this->labelModel->getLabelRootTreeNodeId($labelId);

            //dump($this->lang ?: $this->langManagerService->getDefaultLanguage());

            //dump($this['structureManager']->getLanguage());

            //$labelRoots = $this->pageManagerService->getLabelRoots($labelId, $this->lang ?: $this->langManagerService->getDefaultLanguage());
            $labelRoots = $this->pageManagerService->getLabelRoots($labelId, $this['structureManager']->getLanguage());

            if (count($labelRoots) <= 1) {
                if (count($labelRoots) == 1) {
                    $_labelRoot = reset($labelRoots);
                    $labelRoot = $_labelRoot->_tree_node_id;
                } else {
                    $labelRoot = 0;
                }

            } else {
                $labelRoot = array();

                foreach ($labelRoots as $lr) {
                    $labelRoot[$lr->_tree_node_id] = $lr->_name;
                }
            }
//            dump($labelRoot);
//            die();

            $this->template->label = $this->pageManagerService->getLabel($labelId);

            if ($labelRoot)
                $this->preselectedParent = $labelRoot;
        }





//        $activatedLanguages['pl'] = 'Polski';
        if (!$this->isAjax()) {
            $this->template->activatedLanguages = array_merge($activatedLanguages, $alienLangs);
            if ($tab !== NULL) {
                $this['structureManager']->setLanguage($tab);
            }
        }

        // compute preset tab based on the presetLang parameter passed from structure manager
        $presetLang = $this['structureManager']->getLanguage();
        $this->template->presetTab = array_search($presetLang, array_keys($activatedLanguages));

        $module = $this->presenter->pageManagerService->getCurrentModule();
        $this->template->moduleNamespace = ':'.strtr($module, "/", ":").':Default:default';

//        dump('tu');
//        die();

    }


    public function actionEmptyTrash() {

        // get list of trashed pages

        $this->pageModel->emptyTrash();

        $this->redirect('Default:default');

//        dump('Trash is now empty');
//        die();
    }

    public function renderAdd() {

        // get all labels with buttons

        $labels = $this->pageManagerService->getAllLabels();


//        $buttons['page'] = 'Vytvořit stránku';
//        $buttons['link'] = 'Vytvořit odkaz na stránku';

        $buttons = array();

        if (!empty($labels)) {
            foreach ($labels as $labelId => $label) {
                if ($label['create_button'] && $label['show_button']) {

                    $entity = $label['entity'];

                    if ($entity == NULL) {
                        // set default entity (page)
                        $entity = 'page';
                    }

                    $entityConfig = $this->configLoaderService->loadEntityConfig($entity);

                    $createUrl = isset($entityConfig['entityMeta']['createUrl']) ? $entityConfig['entityMeta']['createUrl'] : TRUE;
//                    dump($entityConfig);
//                    die();

                    $buttons[] = array(
                                    'labelId'       =>  $labelId,
                                    'title'         =>  "Vytvořit stránku '".$label['name']."'",
                                    'destination'   =>  $createUrl ? 'addPage' : 'addScrap',
                                    'entity'        =>  $entity
                    );
                    //$buttons[$labelId] = "Vytvořit stránku '".$label['name']."'";
                }

            }
        }

        $this->template->buttons = $buttons;

    }


    public function actionChoosePageEntity() {

        // get all labels with buttons

        $pageEntities = $this->pageManagerService->getAllPageEntities();

        if (count($pageEntities) == 1) {
            foreach ($pageEntities as $entityName => $entityTitle) {
                $this->redirect(301, 'addPage', array('entity' => $entityName));
            }
        }

        $buttons = array();

        if (!empty($pageEntities)) {
            foreach ($pageEntities as $entityName => $entityTitle) {
                $buttons[$entityName] = $entityTitle;
            }
        }

        $this->template->buttons = $buttons;

    }

    public function actionChooseScrapEntity() {

        // get all labels with buttons

        $pageEntities = $this->pageManagerService->getAllScrapEntities();

        if (count($pageEntities) == 1) {
            foreach ($pageEntities as $entityName => $entityTitle) {
                $this->redirect(301, 'addScrap', array('entity' => $entityName));
            }
        }

        $buttons = array();

        if (!empty($pageEntities)) {
            foreach ($pageEntities as $entityName => $entityTitle) {
                $buttons[$entityName] = $entityTitle;
            }
        }

        $this->template->buttons = $buttons;

    }

    public function renderAddScrap($labelId, $entity) {

        $this->treeNodeId = NULL;

        $activatedLanguages = $this->langManagerService->getLangs();
        if (!$this->isAjax()) {
            $this->template->activatedLanguages = $activatedLanguages;
        }

        $this->template->treeNodeId = NULL;
        $this->template->pageAuthor = 1;

        $labelRoot = NULL;
        if (!empty($labelId)) {

            $labelRoots = $this->pageManagerService->getLabelRoots($labelId, $this->lang ?: $this->langManagerService->getDefaultLanguage(), array('published', 'draft'));

            if (count($labelRoots) <= 1) {

                if (count($labelRoots) == 1) {
                    $_labelRoot = reset($labelRoots);
                    $labelRoot = $_labelRoot->_tree_node_id;
                } else {
                    $labelRoot = 0;
                }

            } else {
                $labelRoot = array();

                foreach ($labelRoots as $lr) {
                    $labelRoot[$lr->_tree_node_id] = $lr->_name;
                }
            }

            // set parent by labelId
            if ($labelRoot)
                $this->preselectedParent = $labelRoot;
        }

        // compute preset tab based on the presetLang parameter passed from structure manager
        $presetLang = $this['structureManager']->getLanguage();
        $this->template->presetTab = array_search($presetLang, array_keys($activatedLanguages));
    }

    public function renderAddPage($labelId, $entity) {

        $this->treeNodeId = NULL;

        $activatedLanguages = $this->langManagerService->getLangs();
        if (!$this->isAjax()) {
            $this->template->activatedLanguages = $activatedLanguages;
        }

        $this->template->treeNodeId = NULL;
        $this->template->pageAuthor = 1;

        $labelRoot = NULL;
        if (!empty($labelId)) {

            $labelRoots = $this->pageManagerService->getLabelRoots($labelId, $this->lang ?: $this->langManagerService->getDefaultLanguage(), array('published', 'draft'));

            if (count($labelRoots) <= 1) {

                if (count($labelRoots) == 1) {
                    $_labelRoot = reset($labelRoots);
                    $labelRoot = $_labelRoot->_tree_node_id;
                } else {
                    $labelRoot = 0;
                }

            } else {
                $labelRoot = array();

                foreach ($labelRoots as $lr) {
                    $labelRoot[$lr->_tree_node_id] = $lr->_name;
                }
            }

            // set parent by labelId
            if ($labelRoot)
                $this->preselectedParent = $labelRoot;
        }

        // compute preset tab based on the presetLang parameter passed from structure manager
        $presetLang = $this['structureManager']->getLanguage();
        $this->template->presetTab = array_search($presetLang, array_keys($activatedLanguages));
    }

    public function actionAutosave() {
        $autosavedItem = $this->getModelAutosaver()->autosave();
        $identity = $this->getUser()->getIdentity();
        $userId = $identity ? $identity->id : NULL;
        $autosavedItem['number_of_concepts'] = $this->getModelAutosaver()->getNumberOfConcepts($userId);

        $this->payload->autosave = $autosavedItem;
        $this->terminate();
    }




    public function renderHistory($id) {
        $pageVersions = $this->getModelPage()->getPageVersions($id);
        $this->template->actualPage = $this->getModelPage()->getActualPage($id);
        $this->template->versions = $pageVersions;
    }

    public function renderEditLabel($id) {
        $this->template->id = $id;
    }

    public function renderSortLabelPages($id) {

        $this->template->label = $this->pageManagerService->getLabel($id);
    }


    public function createComponentLabelSorter($name) {
        return new \LabelSorter\LabelSorter($this, $name);
    }

//    public function createComponentPageSorter($name) {
//        return new Components\PageSorter($this, $name);
//    }


    public function getProducts() {


        $selectData = array();
        $selectData[0] = 'Nepřiřazeno k žádnému stroji';

        $label = $this->pageManagerService->getLabelByName('Stroje');

//        dump($label);
//        die();

        if (!empty($label)) {

            $roots = $this->pageManagerService->getLabelRoots($label['label_id'], $this['structureManager']->getLanguage());

            $descendantsParams = array(
                                    'lang'          => $this['structureManager']->getLanguage(),
                                    'states'        =>  NULL,
                                    'searchGhosts'  =>  TRUE
            );

            foreach ($roots as $root) {

                foreach ($root->getDescendants($descendantsParams) as $product) {
                    $selectData[$root->_title][$product->_tree_node_id] = $product->_title;
                }

            }

        }

        return $selectData;

    }

    public function getPeletizers() {


        $selectData = array();
        $selectData[0] = 'Nepřiřazeno k žádnému stroji';

        $label = $this->pageManagerService->getLabelByName('Peletizery');

//        dump($label);
//        die();

        if (!empty($label)) {

            $roots = $this->pageManagerService->getLabelRoots($label['label_id'], $this['structureManager']->getLanguage());

            $descendantsParams = array(
                                    'lang'          => $this['structureManager']->getLanguage(),
                                    'states'        =>  NULL,
                                    'searchGhosts'  =>  TRUE
            );

            foreach ($roots as $root) {

                foreach ($root->getDescendants($descendantsParams) as $product) {
                    $selectData[$root->_title][$product->_tree_node_id] = $product->_title;
                }

            }

        }

        return $selectData;

    }

}
