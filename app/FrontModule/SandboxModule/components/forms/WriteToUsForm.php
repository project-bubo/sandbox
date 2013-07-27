<?php

namespace FrontModule\SandboxModule\Forms;

use Nette\Application\UI\Form;

class WriteToUsForm extends MailForm {

    
    public function __construct($parent, $name) {
        parent::__construct($parent, $name);        
        
        $this->addText('name', 'Jméno')
                            ->setRequired('Zadejte, prosím, Vaše jméno');
        
        $this->addText('email', 'Email')
                            ->addRule(Form::EMAIL, 'Email není ve správném formátu')
                            ->setRequired('Zadejte, prosím, Váš email');
        
        $this->addTextArea('text', 'Text')
                            ->addRule(Form::MAX_LENGTH, 'Maximální dálka zprávy je %d znaků', 500)
                            ->setRequired('Zadejte, prosím, text zprávy');
        
        $this->addSubmit('send','Odeslat');
        
        $this['name']->getControlPrototype()->class = 'full-width-input';
//        $this['email']->getControlPrototype()->class = 'input';
//        $this['text']->getControlPrototype()->class = 'input2';
        
        $this->onSuccess[] = array($this, 'formSubmited');        
        
    }

    
    public function formSubmited($form) {

        
        $formValues = $form->getValues();  
        
        
        $this->subject = 'Odeslaný formulář "Napište nám"';
        $this->templateFilename = 'writeToUs.latte';
        $this->sendMail($formValues);
        
        //mail('jurasm2@gmail.com', 'Kontakt z webové stránky', $this->mailBody, $headers);
        //$this->presenter->redirect('this');
        //$this->parent->invalidateControl();
        
        $this->presenter->flashMessage('Zpráva byla úspěšně odeslána.');
        $this->presenter->redirect('this');
        //$this->presenter->invalidateControl();

    }
    
    
}

