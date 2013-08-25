<?php

namespace BuboApp\AdminModule\Forms;

use Nette\Application\UI\Form,
    Nette\Environment;

class EntityParamsForm extends BaseForm {

    public function __construct($parent, $name) {
        parent::__construct($parent, $name);        
                    
        /* PREPARE DATA */
        
        //$pageId = $this->getPresenter()->getParam('id');
    
        //dump($labelId);
          
        $entityConfig = $this->presenter->configLoaderService->loadEntityConfig('page');
        
        
        foreach ($entityConfig['properties'] as $entityParamName => $entityParam) {
            
            $this->addGroup($entityParam['label']);
            
            $cont = $this->addContainer($entityParamName);
            
            $cont->addText('label', 'Titulek')
                                ->setRequired('Zadejte titulek pro parametr');
            $cont->addCheckbox('exclude', 'Nezobrazovat');
            
        }
        
        $this->setCurrentGroup(NULL);
        $this->addSubmit('send', 'Uložit');
        
        
        $defaults = $this->presenter->extModel->getDefaultsForEntityParamForm($this->presenter->labelId, $entityConfig);
        $this->setDefaults($defaults);
//        dump($defaults);
//        die();
        
        $this->onSuccess[] = array($this, 'formSubmited');
        
       
    }



    
    public function formSubmited($form) {
        $formValues = $form->getValues();
//        dump($formValues);
//        die();
        
        if ($form['send']->isSubmittedBy()) {
           
            $res = $this->presenter->extModel->saveEntityParamFormData($formValues, $form->presenter->labelId);

            $this->getPresenter()->flashMessage('Uloženo');
            
            $this->getPresenter()->redirect('editLabel');
        } 
    }
    
}