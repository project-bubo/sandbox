<?php

namespace AdminModule\Forms;

use Nette\Application\UI\Form,
    Nette\Environment,
    Components\Core\PageProperty;

class PageForm extends BaseForm {

    public $properties;
    
    public $labelProperties;
    
    public $createUrl;
    
    public function getProperties() {
        return $this->properties;
    }
    
    public function __construct($parent, $name) {
        parent::__construct($parent, $name);        
        
//        dump('tu');
//        die();
        
        $treeNodeId = $this->presenter->getParam('id');        
        $labelId = $this->presenter->getParam('labelId');        
        $entity = $this->presenter->getParam('entity');        
        
        if ($entity === NULL) {
            // determine entity by $treeNodeId
            if ($treeNodeId === NULL) {
                throw new \Nette\InvalidArgumentException("Unable to determine entity");
            }            
            $entity = $this->presenter->pageModel->getEntity($treeNodeId);
        }
        
        // load page configuration
        $entityConfig = $this->presenter->configLoaderService->loadEntityConfig($entity);
        $this->properties = $this->presenter->extModel->filterEntityProperties($entityConfig['properties'], $labelId);
                
        $this->createUrl = TRUE;
        if (isset($entityConfig['entityMeta']) && isset($entityConfig['entityMeta']['createUrl'])) {
            if (!$entityConfig['entityMeta']['createUrl']) {
                $this->createUrl = FALSE;
            }
        }
        
//        dump($entityConfig);
//        die();
        
        $labelProperties = array();
        $label = NULL;
        // for proper form generation...$labelId must be known
        if (!empty($labelId)) {
            // load all label extensions
            $allExtensions = $this->presenter->configLoaderService->loadLabelExtentsionProperties();
            
            $labelProperties = $this->presenter->pageManagerService->intersectLabelProperties($labelId, $allExtensions);
            $this->properties = array_merge($this->properties, $labelProperties);            
            
            // sort properties
                    $extSortOrder = $this->presenter->extModel->loadExtSorting($labelId);

                    if ($extSortOrder) {
                        $extSorting = NULL;
                        if ($extSorting = \Utils\MultiValues::unserialize($extSortOrder)) {

                            uksort($this->properties, function($a, $b) use($extSorting) {

                                                $aPos = array_search(str_replace('_','*',$a), $extSorting);
                                                $bPos = array_search(str_replace('_','*',$b), $extSorting);

                                                if (($aPos !== FALSE) && ($bPos !== FALSE)) {
                                                    return $aPos - $bPos;
                                                } else if ($aPos === FALSE) {
                                                    return -1;
                                                } else {
                                                    return 1;
                                                }

                                            });

                        }




                    }
            
            
            $label = $this->presenter->pageManagerService->getLabel($labelId);
            
            
            
        }
        
        // getLanguages
        $langs = $originalLangs = $this->presenter->langManagerService->getLangs();
        
        $alienLangs = array();
        
        $referencingPages = NULL;
        
        if ($treeNodeId !== NULL) {
        
            //$defaults = $this->presenter->pageManagerService->getDefaults($treeNodeId, $this->presenter->getParam('waiting'));

            $referencingPages = $this->presenter->pageModel->getReferencingPages($treeNodeId);
            $alienLangs = $this->presenter->langManagerService->getAlienLangs($referencingPages, $langs);
          
            $langs = $langs + $alienLangs;
        }

        $pageNameListeners = array('title', 'link_title', 'menu_title', 'page_title', 'url_name');
        
        if ($this->presenter->preselectedParent !== NULL) {
            
            if (is_array($this->presenter->preselectedParent)) {
                
                $this->addSelect('parent', "Parent", $this->presenter->preselectedParent)
                                ->setPrompt(':: Vyberte předka ::')
                                ->setRequired('Musíte vybrat předka');
                $this['parent']->getControlPrototype()->style = 'font-family:monospace;float:right;z-index:250;font-size:12px;';
            } else {
                $this->addHidden('parent', $this->presenter->preselectedParent);
            }
            
            
        } else {
            // parent select
            
            //dump($entityConfig);
            
            $disablePagesWithNoUrl = isset($entityConfig['entityMeta']['createUrl']) ? $entityConfig['entityMeta']['createUrl'] : FALSE;
            
            //dump($disablePagesWithNoUrl);
            
            $st = new \AdminModule\Components\SelectTraverser($this->presenter, $treeNodeId, NULL, $disablePagesWithNoUrl);
            $selectData = $st->getSelectMenu($this->presenter['structureManager']->getLanguage());
            
            $this->addSelect('parent', "Parent", $selectData)
                                ->setPrompt(':: Vyberte předka ::')
                                ->setRequired('Musíte vybrat předka');
            $this['parent']->getControlPrototype()->style = 'font-family:monospace;float:right;z-index:250;font-size:12px;';
        }
        
        $this->addHidden('entity');
        
        $identity = $this->presenter->getUser()->getIdentity();
        $editorId = $identity ? $identity->id : NULL;
        
        
        $mandatoryProperties = $this->presenter->configLoaderService->loadMandatoryProperties();
                
        $langForms = array();
        
        if (!empty($this->properties)) {
            
            // container with checkboxes
            $publishContainer = $this->addContainer('lang_versions');
            foreach ($langs as $langCode => $language) {
                $publishContainer->addCheckbox($langCode);
            }
            
            // language containers
            foreach ($langs as $langCode => $language) {

                // !! create language section
                $langForms[$langCode] = $this->addContainer($langCode);
                
                // mandatory parameters (will be in all pageForms??)
                foreach ($mandatoryProperties as $key => $array) {
                    
                    $formItem = NULL;
                    switch ($array['type']) {
                        case 'text': $formItem = $langForms[$langCode]->addText($key, $array['label']);
                                break;
                        case 'textArea': $formItem = $langForms[$langCode]->addTextArea($key, $array['label']);
                                break;
                    }

                    if ($formItem !== NULL && isset($array['class'])) {
                        $formItem->getControlPrototype()->class[] = $array['class'];
                    }
                    
                    // manage classes for name and its listeners
                    if ($key == 'name') {
                        $formItem->getControlPrototype()->class[] = '_page_name_'.$langCode;
                    } else if (in_array($key, $pageNameListeners)) {
                        $formItem->getControlPrototype()->class[] = '_page_name_listener_'.$langCode;
                    }
                    
                    
                }
                                
                $langForms[$langCode]->addText('parent_url')
                                        ->addConditionOn($langForms[$langCode]['name'], Form::FILLED)
                                        ->addRule(~Form::EQUAL, 'Předek je duch. Pro uložení stránky v této mutaci je potřeba nejprve vytvořit předka.', '_undefin@_');
                                        
                
                
                
                
//                dump($labelProperties);
//                die();
                
                // parameters loaded from page configuration
                foreach ($this->properties as $propertyName => $property) {

                    $formItem = NULL;

                    if (isset($property['engine'])) {
                        
                        
                        switch ($property['engine']) {
                            case 'parametrizer':
                                $formItem = $langForms[$langCode]->addSubmit($propertyName, $property['label']);
                                break;
                            case 'media':
                                switch ($property['type']) {
                                    case 'mediaFile': 
                                        $formItem = $langForms[$langCode]->addMediaFile($propertyName, $property['label']);
                                        break;
                                    case 'mediaGallery': 
                                        $formItem = $langForms[$langCode]->addMediaFile($propertyName, $property['label']);
                                        break;
                                }
//                            default:
//                                $formItem = $langForms[$langCode]->addHidden($propertyName, $property['label']);
//                                break;
                        }
                        
                        
                        
                    } else {
                        
                            switch ($property['type']) {
                                case 'text': $formItem = $langForms[$langCode]->addText($propertyName, $property['label']);
                                        if (isset($property['class'])) {
                                            $formItem->getControlPrototype()->class = $property['class'];
                                        }
                                        break;
                                case 'textArea': $formItem = $langForms[$langCode]->addTextArea($propertyName, $property['label']);
                                        if (isset($property['class'])) {
                                            $formItem->getControlPrototype()->class = $property['class'];
                                        }
                                        break;
                                case 'select': 
                                        $selectData = isset($property['data']) ? $property['data'] : call_user_func_array(array($this->presenter, $property['method']), array());
                                        $formItem = $langForms[$langCode]->addSelect($propertyName, $property['label'], $selectData);
                                        if (isset($property['prompt'])) {
                                            $formItem->setPrompt($property['prompt']);
                                        }
                                        if (isset($property['required'])) {
                                            $formItem->setRequired($property['required']);
                                        }
                                        break;
                                case 'color': $formItem = $langForms[$langCode]->addText($propertyName, $property['label']);
                                        $formItem->getControlPrototype()->type = 'color';
                                        break;
                            }
                        
                    }
                }
                
            }
            
        }

        $this->addSubmit('send', 'Proveď');
        $this->addSubmit('cancel', 'Cancel')->setValidationScope(FALSE);

        $whatToPublish = array(
                            'none'      =>  'Uložit a nepublikovat nic',
                            'selected'  =>  'Uložit a publikovat vybrané',
                            'all'       =>  'Uložit a publikovat vše'
        );
        
        $this->addSelect('what_to_publish', '', $whatToPublish)
                            ->addRule(array($this, 'draftNotInFuture'), 'Draft nemůže mít odloženou publikaci', $this)
                            ->addRule(array($this, 'atLeastOnePageFilled'), 'Vyplňte alespoň jednu stránku', $this)
                            ->getControlPrototype()->style = 'display: inline;';
         
        
        // load templates -> get only existing and with nice names (if possible)
//        dump($entityConfig);
//        die();
        $loadTemplatesWithUrl = TRUE;
        if (isset($entityConfig['entityMeta']) && isset($entityConfig['entityMeta']['createUrl'])) {
            $loadTemplatesWithUrl = $entityConfig['entityMeta']['createUrl'];
        } else {
            throw new Nette\InvalidArgumentException("Missing parameter 'createUrl' in entity config");
        }
        
        $_templates = $this->presenter->projectManagerService->getListOfTemplates($loadTemplatesWithUrl); 
        $templateConfig = $this->presenter->configLoaderService->loadLayoutConfig(); 
        $res = \Nette\Utils\Arrays::mergeTree($templateConfig['layouts'], $_templates);
        $templates = array_intersect_key($res, $_templates);
        
        // any entity template restriction?
        if (isset($entityConfig['templates']) && isset($entityConfig['templates']['restrict'])) {
            $templates = array_intersect_key($templates, array_flip($entityConfig['templates']['restrict']));
            if (empty($templates)) {
                throw new \Nette\InvalidStateException('Template restriction is empty.');
            }
        }
        
        // any entity template exclusion?
        if (isset($entityConfig['templates']) && isset($entityConfig['templates']['exclude'])) {
            $templates = array_diff_key($templates, array_flip($entityConfig['templates']['exclude']));
            if (empty($templates)) {
                throw new \Nette\InvalidStateException('Template exlusion is empty.');
            }
        }
        
//        dump($templates);
//        die();
//        
//        dump($templates, $entityConfig);
//        die();
        
        
//        if (isset($_templates['headless'])) {
//            $res = \Nette\Utils\Arrays::mergeTree($templateConfig['layouts'], $_templates['headless']);
//            $templates['Stránky bez url'] = array_intersect_key($res, $_templates['headless']);
//        }
        
        $this->addSelect('layout', 'Šablona', $templates);
        
//        dump($label);
//        die();
        if ($label && $label['lock_layout'] == 1) {
            $this['layout']->setDisabled();
//            dump($label);
//            die();
        }
        
        $this['send']->getControlPrototype()->class = "submit";
        $this['cancel']->getControlPrototype()->class = "submit";
        
        //$this['send']->addRule(callback($this, 'newUrlUniqueValidator'), '', $this);
        
        
//        if (!$isEditable) {
//            foreach ($this->getComponents() as $component) {
//                $component->setDisabled();
//            }
//        }
        
        
        $this->getElementPrototype()->onsubmit('tinyMCE.triggerSave()');
        switch ($this->getPresenter()->getAction()){
            case 'default': // edit
                
                $this->addSubmit('move_to_trash', 'Hodit do koše')->setValidationScope(FALSE);                
                $this->onSuccess[] = array($this, 'editFormSubmited');                     
                
                if (!$this->isSubmitted()) {
                    
//                    dump($alienLangs);
//                    die();
                    
                    $defaults = $this->presenter->pageManagerService->getDefaults($treeNodeId, $this->presenter->getParam('waiting'), array_keys($alienLangs));

                    //\Nette\Diagnostics\Debugger::$maxDepth = 8;
//                    dump($defaults);
//                    die();
                    
                    $currentDefault = reset($defaults);
//                    $currentDefault = current($ccurrentDefault);

                    
                    //$allLanguages = $this->presenter->langManagerService->getLangs();
                    $defaultLanguage = $this->presenter->langManagerService->getDefaultLanguage();

                    $urls = $this->presenter->pageModel->getPagesUrls($currentDefault['parent'], array_keys($originalLangs), $defaultLanguage);
                    $alienUrls = $this->presenter->pageModel->getAlienPagesUrls($referencingPages, $alienLangs, $this->presenter->langManagerService);
                    
                    
                    $alienTabUrls = $this->presenter->pageModel->getAlienTabUrls($alienLangs, $alienUrls);
                    //dump($referencingPages);
//                    dump($referencingPages, $alienUrls);
//                    $this->presenter->pageModel->getAllAlienUrls($referencingPages, $alienUrls, $this->presenter->langManagerService);
//                    die();
//                    die();
                    
                    $urls = $urls + $alienTabUrls;
                    
                    // add urls to defauls (even to nonexisting lang sections!)
                    if (!empty($urls)) {
                        foreach ($urls as $langCode => $parentUrl) {
                            $defaults[$langCode]['parent_url'] = $urls[$langCode]['parent_url'];
                        }
                    }
                    
                    
//                    dump($currentDefault);
                    
                    $defaults['parent'] = $currentDefault['parent'];
                    $defaults['layout'] = $currentDefault['layout'];
                    
                    $defaults['entity'] = $entity;
                    
                    
                    // expand extended values
                    $identifiers = array();
                    foreach ($langs as $langCode => $langName) {
                        if (isset($defaults[$langCode]) && isset($defaults[$langCode]['time_zone'])) {
                            foreach ($defaults[$langCode]['time_zone'] as $timeZoneFlag => $timeZone) {

                                foreach ($timeZone as $status => $labels) {

                                    foreach ($labels as $labelId => $pageData) {
//                                        unset($defaults[$langCode]['time_zone'][$timeZoneFlag][$status][$labelId]['ext_identifier']);
//                                        unset($defaults[$langCode]['time_zone'][$timeZoneFlag][$status][$labelId]['ext_value']);
                                        if (!empty($pageData['ext_identifier'])) {				
                                            foreach ($pageData['ext_identifier'] as $extIdentifier => $d) {
                                                if ($extIdentifier != "") {
                                                    if (isset($labelProperties) && isset($labelProperties['ext_'.$extIdentifier]) ) {
                                                        $identifiers[$langCode]['ext_'.$extIdentifier] = $d['ext_value'];
                                                    }
                                                }
                                            }
                                        }
                                    }

                                }

                            }
                        }
                    }
                    
//                    dump($defaults);
//                    die();
                    
//                    dump($allExtensions, $identifiers);
//                    die();
                    
                    $newDefaults = \Nette\Utils\Arrays::mergeTree($defaults, $identifiers);
                    
                    //dump($newDefaults);
                    
                    $this->setDefaults($newDefaults);

                    
                }
                
                
                //$defaults = $this->presenter->pageManagerService->getDefaults($treeNodeId);
                
                break;
            case 'addPage':    
            case 'addScrap': 
                $this->onSuccess[] = array($this, 'addformSubmited');
                
                if (!$this->isSubmitted()) {
                    
                    
                    //$allLanguages = $this->presenter->langManagerService->getLangs();
                    $defaultLanguage = $this->presenter->langManagerService->getDefaultLanguage();

                    $defaults = $this->presenter->pageModel->getPagesUrls(NULL, array_keys($langs), $defaultLanguage);
                    
                    $defaults['entity'] = $entity;
                    
//                    dump($entity);
//                    die();
                    
                    if ($label) {
                        //dump($label);
                        $defaults['layout'] = $label['layout'];
                    }
                    
                    if (($this->presenter->preselectedParent !== NULL) && !(is_array($this->presenter->preselectedParent))) {
            
                        //$this->addHidden('parent', $this->presenter->preselectedParent);
                    
                        $urls = $this->presenter->pageModel->getPagesUrls($this->presenter->preselectedParent, array_keys($langs), $defaultLanguage);

                        // add urls to defauls (even to nonexisting lang sections!)
                        if (!empty($urls)) {
                            foreach ($urls as $langCode => $parentUrl) {
                                $defaults[$langCode]['parent_url'] = $urls[$langCode]['parent_url'];
                            }
                        }
                    }
                    
                    
                    $this->setDefaults($defaults);
                }
                
                break;

        }
        
        $this->onError[] = array($this, 'invalidSubmit');
        
        
    }

    public function invalidSubmit($form) {
        
        $values = $form->getValues();
        $errors = $form->getErrors();
        
        dump($errors);
        die();
        
        $this->presenter->flashMessage(implode(', ', $errors), 'error');
        //$this->presenter->redirect('this');
        //$this->presenter->flashMessage($reason,'error');
        //dump($errors);
        //die();
        
//        
////        dump($errors, $values);
////        die();
//        
//        $errorLang = NULL;
//        foreach ($errors as $error) {
//            
//            if (preg_match('#Chybné URL ve stránce ([a-z]+)#', $error, $match)) {
//                $errorLang = $match[1];
//            }
//            
//        }
//            
//        $suggestedUrlChunk = NULL;
//        
//        // if error source is url, try to suggest allowed url
//        if ($form['send']->hasErrors()) {
//            
//            if ($errorLang != NULL) {
//
//                $suggestedUrlChunk = $this->presenter->getModelPage()->suggestAlternativeUrlChunk($values[$errorLang]['url_chunk'], $values[$errorLang]['parent'], $this->presenter->context->pageManager);
//                //$suggestedUrlChunk = 'hala-bala';
//            }
//        }
//        
//        // put errors into flash messanger
//        foreach ($errors as $error) {
//            if (!empty($error)) {
//                $this->presenter->flashMessage($error, 'error');
//            }
//        }
//    
//        // dont show errors in form (they are in flash already)
//        $form->cleanErrors();
//        
//        // set values in urlEditor
//        // at this point all url editors must be initialized
//            
//        foreach ($values['lang_versions'] as $langVersion => $publish) {
//
//            $parentUrl = $this->presenter->context->pageManager->getPage($values[$errorLang]['parent'])->getUrl();  
//
//            if ($langVersion == $errorLang) {
//                $this->presenter['urlEditor_'.$errorLang]
//                                    ->setUrl($suggestedUrlChunk ? $suggestedUrlChunk : $values[$errorLang]['url_chunk'])
//                                    ->setParentUrl($parentUrl);
//            } else {
//                $this->presenter['urlEditor_'.$langVersion]
//                                    ->setUrl($values[$langVersion]['url_chunk'])
//                                    ->setParentUrl($parentUrl);
//            }
//        }
//
//
//        $this[$errorLang]['url_chunk']->setValue($suggestedUrlChunk ? $suggestedUrlChunk : $values[$errorLang]['url_chunk']);
//        $this[$errorLang]['url']->setValue($parentUrl.'/'.($suggestedUrlChunk ? $suggestedUrlChunk : $values[$errorLang]['url_chunk']));
//        
        
        
    }
    
    public function addformSubmited($form) {
        
        $values = $form->getValues();
        
//        dump($values);
//        die();
        
        $labelId = $this->presenter->getParam('labelId');
        
        $entity = $values['entity'];
        $parent = $values['parent'];

        $whatToPublish = $values['what_to_publish'];
        
        if (!isset($values['layout'])) {
            $values['layout'] = NULL;
            if ($labelId) {
                $label = $this->presenter->pageManagerService->getLabel($labelId);
                if ($label) {
                    $values['layout'] = $label['layout'];
                }
            }
        }
        
        $layout = $values['layout'];
        
        if ($form['cancel']->isSubmittedBy()) {
            //$this->presenter->getModelAutosaver()->deleteAutosaveData($values['autosave_id']);            
            $this->getPresenter()->redirect('add');
        }
         
        $pageSet = array();            
        $langVersions = $values['lang_versions'];

        $status = 'draft';                
        //$latestPageVersions = $this->presenter->pageModel->getLatestPageVersions($treeNodeId);        
        //$allPageVersions = $this->presenter->pageModel->getAllPageVersions($treeNodeId);
        
        
        // create tree node first
        $parentData = array(
                        'parent%i'  =>  $parent,
                        'layout%s'  =>  $layout,
                        'module%s'    =>  $this->presenter->pageManagerService->getCurrentModule()
        );
        
        $treeNodeId = $this->presenter->pageModel->createTreeNodeId($parentData);
        
        // first creating cycle
        foreach ($langVersions as $langCode => $publish) {
            // page form data
            $pageFormData = $values[$langCode];
            // page does not have filled name! -> skip!
            if (empty($pageFormData['name'])) {
                continue;
            }
            
            // add language
            $pageFormData['lang'] = $langCode;
            // add entity
            $pageFormData['entity'] = $entity;
            // add version number (computed from previous version)
            $pageFormData['version'] = 1;
            // add language
            $pageFormData['tree_node_id'] = $treeNodeId;
            
            
            // add status
            switch ($whatToPublish) {
                case 'none': 
                    $status = 'draft';
                    break;
                case 'selected':
                    $status = $publish ? 'published' : 'draft';
                    break;
                case 'all':
                    $status = 'published';

            }
            $pageFormData['status'] = $status;
            
//            dump($pageFormData);
//            die();
            
//            dump($entity);
//            die();
            
            // create new version of the page
            $pageSet[$langCode] = $page = new \Bubo\Pages\CMSPage($this->presenter->context, $treeNodeId, $pageFormData, $entity);

            $page->save($this->presenter);
         
          
            $label = NULL;
            if (!empty($labelId)) {
                // parameters assigned to label
                $label = $this->presenter->pageManagerService->getLabel($labelId);

                if (!empty($label)) {

//                    dump($label);
//                    die();
                    
                    //$page->assignPassiveLabel($labelId);
                    $this->presenter->labelModel->addPassiveLabelling($treeNodeId, $label);
                    
                }

            }
            

        }

        //if ($res['success']) {
        if (TRUE) {
            $saveWithStatus = 'draft';
            $this->getPresenter()->flashMessage($this->_createFlashMessage($saveWithStatus));
        } else {
            $reason = 'Stránku se nepodařilo vytvořit';
            if (!empty($res['reason'])) {
                $reason = $res['reason'];
            }
            $this->getPresenter()->flashMessage($reason,'error');
        }            

        $this->getPresenter()->redirect('default',array('id'=>$treeNodeId, 'labelId' => $labelId));

    }
    
    public function editFormSubmited($form) {
        
        $treeNodeId = (int) $this->presenter->getParam('id');
        $labelId = $this->presenter->getParam('labelId');
        
        $submitter = $form->isSubmitted();
        
        if (\Nette\Utils\Strings::startsWith($submitter->name, 'ext_')) {
            $this->presenter->extManagerService->redirect($submitter->name);
        }
        
//        dump($submitter->name);
//        die();
                
        $values = $form->getValues();
       
        if (!isset($values['layout'])) {
            $values['layout'] = NULL;
            if ($labelId) {
                $label = $this->presenter->pageManagerService->getLabel($labelId);
                if ($label) {
                    $values['layout'] = $label['layout'];
                }
            }
        }
        
//        
//        dump($values);
//        die();
        
        $entity = $values['entity'];
        $parent = $values['parent'];
        $layout = $values['layout'];
        
        // TODO; WHAT IF NEW PARENT IS GHOST????????????????
        $parentData = array(
                        'parent'    =>  $parent,
                        'layout'    =>  $layout,
                        'module'    =>  $this->presenter->pageManagerService->getCurrentModule()
        );
        $this->presenter->pageModel->maybeChangeParent($treeNodeId, $parentData);        
//        dump('tu');
//        die();
        $whatToPublish = $values['what_to_publish'];
        
        
        if ($form['cancel']->isSubmittedBy()) {
            //$this->presenter->getModelAutosaver()->deleteAutosaveData($values['autosave_id']);
            $id = $values['tree_node_id'];
            
            $this->getPresenter()->redirect('default',array('id'=>$id));
        } 
        
        
        $pageSet = array();            
        $langVersions = $values['lang_versions'];

        $status = 'draft';                
        $latestPageVersions = $this->presenter->pageModel->getLatestPageVersions($treeNodeId);        
        $allPageVersions = $this->presenter->pageModel->getAllPageVersions($treeNodeId);
        
        
        $langs = $this->presenter->langManagerService->getLangs();

        $referencingPages = $this->presenter->pageModel->getReferencingPages($treeNodeId);
        $alienLangs = $this->presenter->langManagerService->getAlienLangs($referencingPages, $langs);
        $alienUrls = $this->presenter->pageModel->getAlienPagesUrls($referencingPages, $alienLangs, $this->presenter->langManagerService);


        $allAlienUrls = $this->presenter->pageModel->getAllAlienUrls($referencingPages, $alienUrls, $this->presenter->langManagerService);
        
//        dump($allAlienUrls);
//        die();
        
        // first creating cycle
        foreach ($langVersions as $langCode => $publish) {
            // page form data
            $pageFormData = $values[$langCode];
            // page does not have filled name! -> skip!
            if (empty($pageFormData['name'])) {
                continue;
            }
                        
            // add language
            $pageFormData['lang'] = $langCode;
            // add entity
            $pageFormData['entity'] = $entity;
            // add version number (computed from previous version)
            $pageFormData['version'] = isset($latestPageVersions[$langCode]) ? ($latestPageVersions[$langCode]['version'] + 1) : 1;
            // add language
            $pageFormData['tree_node_id'] = $treeNodeId;
            
            
            
            // add status
            switch ($whatToPublish) {
                case 'none': 
                    $status = 'draft';
                    break;
                case 'selected':
                    $status = $publish ? 'published' : 'draft';
                    break;
                case 'all':
                    $status = 'published';

            }
            
            if ($form['move_to_trash']->isSubmittedBy()) {
                $status = 'trashed';
            }
            
            $pageFormData['status'] = $status;

            // create new version of the page
            $pageSet[$langCode] = $page = new \Bubo\Pages\CMSPage($this->presenter->context, $treeNodeId, $pageFormData, $entity);
            
            
//            dump('chci ulozit stránku');
//            die();

            $page->save($this->presenter, $allPageVersions, $allAlienUrls);

        }
       
            
        if (TRUE) {
            // TODO
            $saveWithStatus = 'draft';
            $this->presenter->flashMessage($this->_createFlashMessage($saveWithStatus, TRUE));
        }else{
            $reason = 'Stránku se nepodařilo uložit';
            if (!empty($res['reason'])) {
                $reason = $res['reason'];
            }
            $this->getPresenter()->flashMessage($reason,'error');
        }
        $this->getPresenter()->redirect('default',array('id'=>$treeNodeId, 'labelId' => $labelId));
       
    }
    
    private function _createFlashMessage($status, $edit = FALSE) {
        $flashMessage = 'Stránka byla ';
        
        switch ($status) {
            case 'draft':
                $flashMessage .= $edit ? 'uložena' : 'vytvořena';
                break;
            case 'published':
                $flashMessage .= 'publikována';
                break;
        }
        
        return $flashMessage;
    }
    
}