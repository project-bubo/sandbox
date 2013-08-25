<?php

namespace BuboApp\AdminModule\Forms;

use Nette\Application\UI\Form,
    Nette\Environment;

class LinkForm extends BaseForm {

    public function __construct($parent, $name) {
        parent::__construct($parent, $name);        
                    
        /* PREPARE DATA */
        
        //$pageId = $this->getPresenter()->getParam('id');
    
        //dump($labelId);
        
        
        $st = new \BuboApp\AdminModule\Components\SelectTraverser($this->presenter);
        
        $cm = $this->presenter->pageManagerService->getCurrentModule();
        $moduleConfig = $this->presenter->configLoaderService->loadModulesConfig($cm);
        
        $linkSelectData = array();
        if (isset($moduleConfig['modules'])) {
            foreach ($moduleConfig['modules'] as $moduleName => $module) {
                $linkSelectData[$module['title']] = $st->getSelectMenu($this->presenter['structureManager']->getLanguage(), TRUE, $moduleName);
            }
        }
        
        $selectData = $st->getSelectMenu($this->presenter['structureManager']->getLanguage());
        
//        dump($selectData);
//        dump($linkSelectData);
        
        //$this->addGroup('Obraz');
        
        $this->addSelect('parent', "Rodič", $selectData)
                            ->setPrompt(':: Vyberte předka ::')
                            ->setRequired('Musíte vybrat předka');
        $this['parent']->getControlPrototype()->style = 'font-family:monospace;z-index:250;font-size:12px;';
        
        //$this->addGroup('Vzor');
        
        $this->addSelect('pattern', "Vzorová stránka", $linkSelectData)
                            ->setPrompt(':: Vyberte vzorovou stránku ::')
                            ->setRequired('Musíte vybrat vzorovou stránku');
        $this['pattern']->getControlPrototype()->style = 'font-family:monospace;z-index:250;font-size:12px;';
        
        $moduleData = array(
                        'default'   =>  'Výchozí modul'
        );
        
//        if (count($moduleData) > 1) {
//        
//            $this->addSelect('pattern_module', "Modul", $moduleData)
//                                ->setPrompt(':: Vyberte vzorový modul ::')
//                                ->setRequired('Musíte vybrat modul');
//        
//        } else {
//        
//            $this->addHidden('pattern_module', $this->presenter->pageManagerService->getCurrentModule());
//        }
    
        $this->addHidden('image_module', $this->presenter->pageManagerService->getCurrentModule());
        
        
        // load templates -> get only existing and with nice names (if possible)
        $_templates = $this->presenter->projectManagerService->getListOfTemplates();        
        $templateConfig = $this->presenter->configLoaderService->loadLayoutConfig();        
        $res = \Nette\Utils\Arrays::mergeTree($templateConfig['layouts'], $_templates);
        $templates = array_intersect_key($res, $_templates);
        $this->addSelect('layout', 'Šablona', $templates);
        
        //$this->addCheckbox('create_subtree', 'Vytvořit celý podstrom');
        
        switch ($this->getPresenter()->getAction()){
            case 'addLink':                
                //$this->setCurrentGroup(NULL);
                $this->addSubmit('send', 'Vytvořit');
                
                $this->onSuccess[] = array($this, 'addformSubmited');
                break;
            case 'editLink': // edit
         
                $this->addSubmit('send', 'Uložit');

                $this->addSubmit('delete', 'Smazat')
                                    ->setValidationScope(NULL);
                
                $image = $this->presenter->getParam('id');

                $this->addHidden('image', $image);

                $defaults = $this->presenter->pageModel->getLink($image);

                $this->setDefaults($defaults);
                $this->onSuccess[] = array($this, 'editFormSubmited');                                
                break;

        }
       
    }


    private function _sanitizePattern($formValues) {
        
         $chunks = explode('-', $formValues['pattern']);
        
        $pattern = $chunks[0];
        if ($chunks[1] != 'x') {
            $pattern = $chunks[1];
        }
        
        $formValues['pattern'] = $pattern;
        
        return $formValues;
    }
    
    public function addformSubmited($form) {
        
        $formValues = $this->_sanitizePattern($form->getValues());
        $formValues['pattern_module'] = $this->presenter->pageModel->getModuleByTreeNodeId($formValues['pattern']);
       
//        dump($formValues);
//        die();
        
        if ($form['send']->isSubmittedBy()) {

            try {
               
                $image = $this->presenter->pageModel->createLink($formValues);

                
                
                if ($formValues['pattern_module'] !== $this->presenter->pageManagerService->getCurrentModule()) {
                    // make urls in pattern module available in current module
                    $currentLangs = $this->presenter->langManagerService->getLangs();
                    $foreignLangs = $this->presenter->langManagerService->getLangs($formValues['pattern_module']);

                    // intersenction langs
                    $iLangs = array_intersect(array_keys($foreignLangs), array_keys($currentLangs));

                    if (!empty($iLangs)) {
                        // find urls with treeNodeId = 
                        $urls = $this->presenter->pageModel->getForeignModuleUrls($formValues['pattern'], $iLangs);

                        if (!empty($urls)) {
                            $urlData = array();
                            foreach ($urls as $langUrl) {
                                $urlDataItem = $langUrl;
                                //$urlDataItem['tree_node_id_'] = $image;
                                $urlDataItem['access_through'] = $this->presenter->pageManagerService->getCurrentModule();
                                $urlData[] = $urlDataItem;
                            }

                            $this->presenter->pageModel->insertUrls($urlData);

                        }

                    }
                }
                
                
                
                
                $this->getPresenter()->flashMessage('Odkaz byl vytvořen');
                
                $this->getPresenter()->redirect('Default:default');
                
            } catch (AuthenticationException $e) {
                $form->addError($e->getMessage());
            }
        } 
    }
    
    public function editFormSubmited($form) {
        
        if ($form['delete']->isSubmittedBy()) {
            $formValues = $form->getValues();
            $this->presenter->pageModel->deleteLink($formValues['image']);
            $this->getPresenter()->flashMessage('Odkaz byl smazán');
            $this->getPresenter()->redirect('Default:default');
        }
        
        
        
        $formValues = $this->_sanitizePattern($form->getValues());
        $image = $formValues['image'];
        unset($formValues['ext_id']);

        $formValues['pattern_module'] = $this->presenter->pageModel->getModuleByTreeNodeId($formValues['pattern']);
        
        
        if ($form['send']->isSubmittedBy()) {
            
//            dump($formValues['pattern_module'], $this->presenter->pageManagerService->getCurrentModule());
//            die();
            
            if ($formValues['pattern_module'] !== $this->presenter->pageManagerService->getCurrentModule()) {
                // make urls in pattern module available in current module
                $currentLangs = $this->presenter->langManagerService->getLangs();
                $foreignLangs = $this->presenter->langManagerService->getLangs($formValues['pattern_module']);
                
                // intersenction langs
                $iLangs = array_intersect(array_keys($foreignLangs), array_keys($currentLangs));
                
                if (!empty($iLangs)) {
                    // find urls with treeNodeId = 
                    $urls = $this->presenter->pageModel->getForeignModuleUrls($formValues['pattern'], $iLangs);
                    
                    if (!empty($urls)) {
                        $urlData = array();
                        foreach ($urls as $langUrl) {
                            $urlDataItem = $langUrl;
                            //$urlDataItem['tree_node_id_'] = $image;
                            $urlDataItem['access_through'] = $this->presenter->pageManagerService->getCurrentModule();
                            $urlData[] = $urlDataItem;
                        }
                        
                        $this->presenter->pageModel->insertUrls($urlData);
                        
                    }
                    
                }
            }
                      
            $res = $this->presenter->pageModel->editLink($formValues, $image);

            
            
            $this->getPresenter()->flashMessage('Odkaz byl upraven');
            $this->getPresenter()->redirect('Page:editLink', array('id' => $formValues['image']));
        }
    }
    
}

