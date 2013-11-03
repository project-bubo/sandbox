<?php

namespace BuboApp\AdminModule\Forms;

use Nette\Application\UI\Form,
    Nette\Environment;

class ExtParamForm extends BaseForm {

    public function __construct($parent, $name) {
        parent::__construct($parent, $name);        
                    
          
        // getLanguages
        $langs = $this->presenter->langManagerService->getLangs();
        
//        dump($langs);
//        die();
        $tagContainer = $this->addContainer('tags');
        
        foreach ($this->presenter->tags as $tagName => $tagCaption) {
            $tagContainer->addCheckbox($tagName, $tagCaption);
        }
        
                
        $langForms = array();
        

        // language containers
        foreach ($langs as $langCode => $language) {

            // !! create language section
            $langForms[$langCode] = $this->addContainer($langCode);

            $formItem = $langForms[$langCode]->addText('param_name', 'Jméno parametru');

        }

        
        $labelId = $this->presenter->getParam('labelId');
        $this->addHidden('label_id', $this->presenter->getParam('labelId'));
        $this->addHidden('tree_node_id', $this->presenter->getParam('id'));
        $this->addHidden('identifier', $this->presenter->getParam('identifier'));
        
        
        
        switch ($this->getPresenter()->getAction()){
            case 'addParam':                
                $this->addSubmit('send', 'Vytvořit');
                
                
                
        
                
                
                $this->onSuccess[] = array($this, 'addformSubmited');
                break;
            case 'editParam': // edit
         
                $this->addSubmit('send', 'Uložit');
                
                $extTreeNodeId = $this->presenter->getParam('ext_tree_node_id');
//                dump($extId);
//                die();
                $this->addHidden('ext_tree_node_id', $extTreeNodeId);
                
//                dump($extId);
//                die();
                
                //$this['is_global']->setDisabled();
                
                $defaults = $this->presenter->extModel->getParam($extTreeNodeId, $this->presenter->tags);
                    
//                dump($defaults);
//                die();
                
                $this->onSuccess[] = array($this, 'editFormSubmited');                                
                $this->setDefaults((array) $defaults);
                break;

        }
       
    }


    
    public function addformSubmited($form) {
        $langs = $this->presenter->langManagerService->getLangs();
        $formValues = $form->getValues();
//        dump($formValues);
//        die();
        
        if ($form['send']->isSubmittedBy()) {

            try {
               
                $res = $this->presenter->extModel->addParam($formValues, $langs);

                $this->getPresenter()->flashMessage('Rozšíření bylo vytvořeno');
                
                $this->getPresenter()->redirect('params');
            } catch (AuthenticationException $e) {
                $form->addError($e->getMessage());
            }
        }
    }
    
    public function editFormSubmited($form) {
        $langs = $this->presenter->langManagerService->getLangs();
        $formValues = $form->getValues();
        //dump($formValues);
        //die();
        $extTreeNodeId = $formValues['ext_tree_node_id'];
        unset($formValues['ext_tree_node_id']);
        
        if ($form['send']->isSubmittedBy()) {
           
            $res = $this->presenter->extModel->editParam($formValues, $extTreeNodeId, $langs);

            $this->getPresenter()->flashMessage('Parametr byl upraven');
            
            $this->getPresenter()->redirect('params');
        } 
    }
    
}