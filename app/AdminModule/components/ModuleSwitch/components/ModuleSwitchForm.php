<?php

namespace AdminModule\Components\ModuleSwitch\Components;

use Nette\Application\UI\Form;

class ModuleSwitchForm extends \AdminModule\Forms\BaseForm {   
    
    public function __construct($parent, $name) {
        parent::__construct($parent, $name);        
                    
  
        $allModules = $this->parent->getAllModules();
//        dump($allModules);
//        die();
        
        $selectData = array();
        if (!empty($allModules)) {
            foreach ($allModules as $moduleName => $moduleData) {
                $selectData[$moduleName] = $moduleData['title'];
            }
        }

        $defaultValue = $this->parent->getActualModule();
        
        $this->addSelect('moduleName','',$selectData)->setDefaultValue($defaultValue);
        $this->addSubmit('send', 'Přepnout');
        
        $this->onSuccess[] = array($this, 'editformSubmited');
              
    }

    
    public function editFormSubmited($form) {
        $formValues = $form->getValues();

        $this->parent->setActualModule($formValues['moduleName']);
        $this->getPresenter()->redirect('Default:default');
        
    }
    


    
}
