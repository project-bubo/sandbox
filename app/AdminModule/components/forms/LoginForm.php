<?php

namespace AdminModule\Forms;

use Nette\Application\UI\Form,
    Nette\Environment,
    Nette\Security\AuthenticationException;

class LoginForm extends Form {

    public function __construct($parent, $name, $action = '') {
        parent::__construct($parent, $name);

        $this->getElementPrototype()->class = 'login-form';
        
        $this->addProtection('STFU!');
        if($action == ''){
            $request = $this->getHttpRequest();
            $value = $request->getCookie('login');
            $this->addText('login', 'Uživatelské jméno:	')
                    ->addRule(Form::FILLED, 'Prosím zadajte přihlašovací jméno.');
            $this['login']->getControlPrototype()->class = "input";
            if($value) $this->setValues(array('login'=>$value));
        }
        $this->addPassword('password', 'Heslo:')
                ->addRule(Form::FILLED, 'Prosím zadajte heslo.');
        $this['password']->getControlPrototype()->addAttributes(array('class' => "input"));
        $this->addCheckbox('remember', 'Zapamatovat')->setValue(true);
        
        if($action == ''){
            $this->addSubmit('send', 'Přihlásit');
            $this->onSuccess[] = array($this, 'formSubmited');
        }else{
            $this->addSubmit('send', 'Změnit');
            $this->onSuccess[] = array($this, 'editFormSubmited');
        }
        $this['send']->getControlPrototype()->addAttributes(array('class' => "submit"));
    }

    public function formSubmited($form) {
        try {
            $user = $this->getPresenter()->getUser();
            $user->login($form['login']->value, $form['password']->value);
            $user->setExpiration('+ 5 days', FALSE);
            
            $this->getPresenter()->getApplication()->restoreRequest($this->getPresenter()->backlink);
            $this->getPresenter()->redirect('Default:default');
        } catch (AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }
    public function editFormSubmited($form) {
        try {
            
            $model = $this->getPresenter()->context->modelLoader->loadModel('ConfigModel');
            $userId = $this->getPresenter()->getUser()->getId();
            $pass = $form['password']->value;
            $model->changePassword($userId, sha1($pass));
            
            $this->getPresenter()->redirect('Default:settings');
        } catch (AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }
}