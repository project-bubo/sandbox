<?php

namespace FrontModule\SandboxModule\Forms;

use Nette\Application\UI\Form;

class NewsletterForm extends MailForm {

    
    public function __construct($parent, $name) {
        parent::__construct($parent, $name);        
       
        $this->addText('email', 'Email')
                            ->addRule(Form::EMAIL, 'Email není ve správném formátu')
                            ->setRequired('Zadejte, prosím, Váš email');

        
        $this->addSubmit('send','Odeslat');
        
        $this->addCheckbox('agree','Souhlasím s podmínkami')
                        ->setRequired();
        
        $this['email']->getControlPrototype()->placeholder = 'Zadejte email';
//        $this['email']->getControlPrototype()->class = 'input';
//        $this['text']->getControlPrototype()->class = 'input2';
        
        $this->onSuccess[] = array($this, 'formSubmited');        
        
    }

    
    public function formSubmited($form) {

        
        $formValues = $form->getValues();  
        
        
        $this->presenter->newsletterModel->saveEmail($formValues['email']);
        
//        $this->subject = 'Žádost o zasílání newsletteru z webu MaxPraga';
//        $this->templateFilename = 'newsletter.latte';
//        $this->sendMail($formValues);
        
        //mail('jurasm2@gmail.com', 'Kontakt z webové stránky', $this->mailBody, $headers);
        //$this->presenter->redirect('this');
        //$this->parent->invalidateControl();
        
        $this->presenter->flashMessage('Žádost úspěšně odeslána.');
        $this->presenter->redirect('this');
        //$this->presenter->invalidateControl();

    }
    
    
}

