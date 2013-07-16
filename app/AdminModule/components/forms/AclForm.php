<?php

namespace AdminModule\Forms;

use Nette\Application\UI\Form,
    Nette\Environment;

class AclForm extends BaseForm {

    public function __construct($parent, $name) {
        parent::__construct($parent, $name);        
        
        $choosedRole = $parent->getParam('role');
        
        $acl = $parent->context->resourceManager->getAcl();
        $aclTitles = $parent->context->resourceManager->getAclTitles();
        
        $roles = $parent->context->authorizator->getRoles();
        
        $this->addGroup('Vyberte roli');
        $this->addSelect('role', 'Role', $roles)
                ->setPrompt('--- Vyberte roli ---');
        
        foreach ($acl as $resourceName => $privileges) {
            
            $this->addGroup($aclTitles[$resourceName]);
            $this->addContainer($resourceName);
            
            foreach ($privileges as $privilegeName => $privilegeValue) {
                $this[$resourceName]->addCheckbox($privilegeName, $aclTitles[$resourceName.':'.$privilegeName]);
            }
            
        }
        
        $this->addGroup('_send');
        $this->removeGroup('_send');       
        $this->addSubmit('send', 'Uložit');
        
        switch ($this->getPresenter()->getAction()){
            case 'default': 
                $this->onSuccess[] = array($this, 'editFormSubmited');  
                
                $defaults = $parent->getModelAcl()->loadFormValues($choosedRole);
                
                $defaults['role'] = $choosedRole;
                $this->setDefaults($defaults);
                break;
            

        }
        
        
        
        $this['send']->getControlPrototype()->class = "submit";
    }

    
    public function editFormSubmited($form) {
        $model = $this->modelLoader->loadModel('AclModel');
        $values = $form->getValues();
       
        
        $role = $values['role'];
        unset($values['role']);
        
        $array = $this->getModelAcl()->makeArray($values);
        
        try {
            
            $res = $model->updateRole($role, serialize($array));
            
            if ($res) {
                $this->getPresenter()->flashMessage('Uloženo do db...');
            }else{
                $reason = 'Nepovedlo se uložit do db...';
                
                $this->getPresenter()->flashMessage($reason,'error');
            }
            $this->getPresenter()->redirect('default',array('role'=>$role));
        } catch (AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }
    
}