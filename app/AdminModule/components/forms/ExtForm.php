<?php

namespace AdminModule\Forms;

use Nette\Application\UI\Form,
    Nette\Environment;

class ExtForm extends BaseForm {

    public function __construct($parent, $name) {
        parent::__construct($parent, $name);        
                    
        /* PREPARE DATA */
        
        //$pageId = $this->getPresenter()->getParam('id');
    
        //dump($labelId);
          
        $extProperties = $this->presenter->configLoaderService->loadLabelExtentsionProperties();
        
        $extSelectData = array();
        
        if (!empty($extProperties) && isset($extProperties['properties'])) {
            
            foreach ($extProperties['properties'] as $propertyName => $property) {
                
                $key = 'Formulářové prvky';
                if (isset($property['engine'])) {
                    switch ($property['engine']) {
                        case 'drive':
                            $key = 'Virtuální disk';
                            break;
                        case 'parametrizer':
                            $key = 'Parametry';
                            break;
                        default:
                            $key = $property['engine'];  
                    }
                }
                
                $extSelectData[$key][$propertyName] = $property['label'];
                
            }
            
        }
        
        
        $this->addText('title', 'Titulek')
                            ->setRequired();
        $this->addText('identifier', 'Identifikátor')
                            ->setRequired();
        $this->addSelect('name','Typ',$extSelectData)
                        ->setPrompt(':: Vyberte typ ::')
                        ->setRequired();
        
        $labelId = $this->presenter->getParam('labelId');
        $this->addHidden('label_id', $labelId);
        
        
        switch ($this->getPresenter()->getAction()){
            case 'addExt':                
                $this->addSubmit('send', 'Vytvořit');
                
                $this->onSuccess[] = array($this, 'addformSubmited');
                break;
            case 'editExt': // edit
         
                $this->addSubmit('send', 'Uložit');
                
                $extId = $this->presenter->getParam('ext_id');
//                dump($extId);
//                die();
                $this->addHidden('ext_id', $extId);
                
//                dump($extId);
//                die();
                
                //$this['is_global']->setDisabled();
                
                $defaults = $this->presenter->labelModel->getExtension($extId);
                    
//                dump($defaults);
//                die();
                
                $this->onSuccess[] = array($this, 'editFormSubmited');                                
                $this->setDefaults((array) $defaults);
                break;

        }
       
    }


    
    public function addformSubmited($form) {
        
        $formValues = $form->getValues();
//        dump($formValues);
//        die();
        
        if ($form['send']->isSubmittedBy()) {

            try {
               
                $res = $this->presenter->labelModel->addExtension($formValues);

                $this->getPresenter()->flashMessage('Rozšíření bylo vytvořeno');
                
                $this->getPresenter()->redirect('manageLabelExtensions');
            } catch (AuthenticationException $e) {
                $form->addError($e->getMessage());
            }
        }
    }
    
    public function editFormSubmited($form) {
        $formValues = $form->getValues();
//        dump($formValues);
//        die();
        $extId = $formValues['ext_id'];

        unset($formValues['ext_id']);
        
        if ($form['send']->isSubmittedBy()) {
           
            $res = $this->presenter->labelModel->editExtension($formValues, $extId);

            if ($res) {
                $this->getPresenter()->flashMessage('Štítek byl upraven');
            }else{
                $reason = 'Źádné změny nebyly provedeny';
                $this->getPresenter()->flashMessage($reason);
            }
            $this->getPresenter()->redirect('manageLabelExtensions');
        } 
    }
    
}