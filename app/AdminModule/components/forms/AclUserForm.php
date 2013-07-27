<?php

namespace AdminModule\Forms;

use Nette\Application\UI\Form,
    Nette\Environment;

class AclUserForm extends BaseForm {

    public function __construct($parent, $name) {
        parent::__construct($parent, $name);        
        
        $acl = $parent->context->resourceManager->getAcl();
        $aclTitles = $parent->context->resourceManager->getAclTitles();
        
        
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
        
        
        $userId = $this->presenter->getParam('user_id');
        $this->addHidden('user_id', $userId);
        
        $this->onSuccess[] = array($this, 'editFormSubmited');  

        $defaults = $parent->getModelAcl()->loadFormValuesForUser($userId);
        $this->setDefaults($defaults);
            
        $this['send']->getControlPrototype()->class = "submit";
    }

    
    public function editFormSubmited($form) {
        
        $values = $form->getValues();
       
        $userId = $values['user_id'];
        unset($values['user_id']);
        
        $acl = $this->presenter->getModelAcl()->makeArray($values);
        
        
        $res = $this->presenter->getModelUser()->updateUsersAcl(serialize($acl), $userId);

        if ($res) {
            $this->getPresenter()->flashMessage('Přístupová práva byla nastavena');
        }else{
            $reason = 'Žádné změny nebyly provedeny';

            $this->getPresenter()->flashMessage($reason,'error');
        }
        $this->getPresenter()->redirect('User:default');
        
    }
    
}