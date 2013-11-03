<?php

namespace BuboApp\AdminModule\Forms;

use Nette\Application\UI\Form,
    Nette\Environment;

class UserForm extends BaseForm {

    public function __construct($parent, $name) {
        parent::__construct($parent, $name);        
        
        $roles = $parent->getModelAcl()->getRolesSelectData();
        
        $this->addText('login','Login')
                ->addRule(Form::FILLED, 'Zadejte login');
        
        $this->addText('email','E-mail')
                ->addRule(Form::FILLED, 'Zadejte email')
                ->addRule(Form::EMAIL);
        
        $this->addSelect('role', 'Role', $roles)
                ->setRequired('Vyberte roli');
        
        $this->addPassword('password','Heslo');
                
        
        $this->addPassword('passwordVerify','Heslo znovu');
        
        switch ($this->getPresenter()->getAction()){
            case 'add':                
                $this->addSubmit('send', 'Vytvořit');
        
                $this['password']->addRule(Form::FILLED);
                $this['passwordVerify']
                            ->setRequired('Zadejte prosím heslo ještě jednou pro kontrolu')
                            ->addRule(Form::EQUAL, 'Hesla se neshodují', $this['password']);
                
                $validatorParams = array(
                                    'userId'    => NULL 
                );
                
                $this['login']->addRule(callback($this, 'loginUniqueValidator'), 'Login musí být unikátní', $validatorParams);
                
                $this->onSuccess[] = array($this, 'addformSubmited');
                break;
            case 'edit': // edit
 
                $this['passwordVerify']
                        ->addConditionOn($this['password'], Form::FILLED, TRUE)
                            ->addRule(Form::EQUAL, 'Hesla se neshodují', $this['password']);
                
                $userId = $this->presenter->getParam('user_id');
                $this->addHidden('user_id', $userId);
                
                $validatorParams = array(
                                    'userId'    => $this['user_id']->value 
                );
                
                $this['login']->addRule(callback($this, 'loginUniqueValidator'), 'Login musí být unikátní', $validatorParams);
                
                $defaults = $this->getPresenter()->getModelUser()->getUserDefaults($userId);
                
                $this->addSubmit('send', 'Upravit');
        
                $this->onSuccess[] = array($this, 'editFormSubmited');                                
                $this->setDefaults($defaults);
                break;

        }
        
        $this['send']->getControlPrototype()->class = "submit";

        
    }

    private function _saltPassword($password) {
        $salt = $this->presenter->context->authenticator->getSalt();         
        return sha1($salt.$password);
    }
    
    public function addformSubmited($form) {

        $values = $form->getValues();
        unset($values['passwordVerify']);
        
        $values['password'] = $this->_saltPassword($values['password']);
        
        $res = $this->presenter->getModelUser()->registerUser($values);

        if ($res) {
            $this->getPresenter()->flashMessage('Uživatel byl vytvořen');
        } else {
            $reason = 'Uživatele se nepovedlo vytvořit';
            $this->getPresenter()->flashMessage($reason,'error');
        }            

        $this->getPresenter()->redirect('User:default');
        
    }
    
    public function editFormSubmited($form) {
        
        
        $values = $form->getValues();
        unset($values['passwordVerify']);
        
        if (!empty($values['password'])) {
            $salt = $this->presenter->context->authenticator->getSalt();        
            $values['password'] = $this->_saltPassword($values['password']);
        } else {
            unset($values['password']);
        }
        
        $userId = $values['user_id'];
        unset($values['user_id']);
        
        $res = $this->presenter->getModelUser()->updateUser($values, $userId);

        if ($res) {
            $this->getPresenter()->flashMessage('Uživatel byl upraven');
        } else {
            $reason = 'Žádné změny nebyly provedeny';
            $this->getPresenter()->flashMessage($reason,'error');
        }            

        $this->getPresenter()->redirect('User:default');
        
        
    }
    
}