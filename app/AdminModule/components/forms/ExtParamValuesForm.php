<?php

namespace AdminModule\Forms;

use Nette\Application\UI\Form,
    Nette\Environment;

class ExtParamValuesForm extends BaseForm {

    public function __construct($parent, $name) {
        parent::__construct($parent, $name);        
                    
          
        // getLanguages
        $langs = $this->presenter->langManagerService->getLangs();
 
        $parentId = $this->presenter->getParam('parentId');
        $identifier = $this->presenter->getParam('identifier');
        
        $params = $this->presenter->extModel->getParams($parentId, $identifier);
        
//        dump($params);
        
        $langForms = array();
        
        
        $labelExtensionsProperties = $this->presenter->configLoaderService->loadLabelExtentsionProperties();
//        dump($labelExtensionsProperties['properties']['parameterValues']['values']);
//        die();

        $units = NULL;
        if (isset($labelExtensionsProperties['properties']['parameterValues']['units'])) {
            $units = $labelExtensionsProperties['properties']['parameterValues']['units'];
        }
        
//        dump($units);
//        die();
        
//        \Nette\Diagnostics\Debugger::$maxDepth = 6;
//        dump($params);
//        die();
        
        $extForms = array();
        
        // create extTreeNodeIdContainers
        foreach ($params as $extTreeNodeId => $langSections) {
            
            
            $extForms[$extTreeNodeId] = $this->addContainer('ext_tree_node_id_'.$extTreeNodeId);
            
            foreach ($langs as $langCode => $language) {
                
                if (isset($langSections[$langCode])) {
                
                    if ($units === NULL) {
                        $extForms[$extTreeNodeId]->addText($langCode, $langSections[$langCode]['param_name']);
                    } else {
                        $c = $extForms[$extTreeNodeId]->addContainer($langCode);
                        foreach ($units as $unitName => $unitLabel) {
                            $c->addText($unitName, $langSections[$langCode]['param_name']);
                        }
                    }

                
                }
            }
            
            
        }
        
        
//        dump($this->components);
//        die();
        
//        
//        // language containers
//        foreach ($langs as $langCode => $language) {
//
//            // !! create language section
//            $langForms[$langCode] = $this->addContainer($langCode);
//
//            if (!empty($params[$langCode]['ext_tree_node_id'])) {
//                foreach ($params[$langCode]['ext_tree_node_id'] as $extTreeNodeId => $data) {
//                    if ($units === NULL) {
//                        // only one unit
//                        $formItem = $langForms[$langCode]->addText('ext_tree_node_id_'.$extTreeNodeId, $data['param_name']);
//                    } else {
//                        $p = $langForms[$langCode]->addContainer('ext_tree_node_id_'.$extTreeNodeId);
//                        foreach ($units as $k => $v) {
//                            $p->addText($k, $data['param_name']);
//                            //$formItem = $langForms[$langCode]->
//                        }
//                    }
//                }
//            }
//            
//
//        }

        
        //$labelId = $this->presenter->getParam('labelId');
        $this->addHidden('label_id', $this->presenter->getParam('labelId'));
        $this->addHidden('tree_node_id', $this->presenter->getParam('id'));
        $this->addHidden('identifier', $this->presenter->getParam('identifier'));
        $this->addHidden('parent_id', $this->presenter->getParam('parentId'));
        $this->addHidden('ext_tree_node_id', $this->presenter->getParam('extTreeNodeId'));
        
//        $extTreeNodeId = $this->presenter->getParam('extTreeNodeId');
         
        $this->addSubmit('send', 'Uložit');

        //dump($parentId);
        
        $defaults = $this->presenter->extModel->getParamValues($this->presenter->getParam('id'), $identifier);
//
//                dump($defaults);
//                die();

        $this->onSuccess[] = array($this, 'editFormSubmited');    
        //dump($defaults);
        if ($defaults !== NULL)
            $this->setDefaults((array) $defaults);
        
       
    }


    
    public function editFormSubmited($form) {
        $langs = $this->presenter->langManagerService->getLangs();
        $formValues = $form->getValues();
//        dump($formValues);
//        die();
//        $extTreeNodeId = $formValues['ext_tree_node_id'];
//        unset($formValues['ext_tree_node_id']);
        
        if ($form['send']->isSubmittedBy()) {
           
            $this->presenter->extModel->editParamValues($formValues, $langs);
            $this->getPresenter()->flashMessage('Hodnoty parametrů byly upraveny');            
            $this->getPresenter()->redirect('this');
        } 
    }
    
}