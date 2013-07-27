<?php

namespace FrontModule\SandboxModule\Forms;

use Nette\Application\UI\Form;

class PartnersForm extends MailForm {

    
    public function __construct($parent, $name) {
        parent::__construct($parent, $name);        
                
        $renderer = $this->getRenderer();
        $renderer->wrappers['controls']['container'] = NULL;
        $renderer->wrappers['pair']['container'] = 'div';
        $renderer->wrappers['label']['container'] = NULL;
        $renderer->wrappers['control']['container'] = NULL;

        $this->getElementPrototype()->class[] = 'input-box-form';
        
        $this->addText('company', 'Firma:')
                            ->setRequired();
        
        $this->addText('headquarters', 'Sídlo firmy:');
        
        $this->addText('major_interest', 'Hlavní obor činnosti:');
        
        $this->addTextArea('cooperation_field', 'Zájem o spolupráci v oblasti:');
        
        $this->addText('contact_person', 'Kontaktní osoba:')
                            ->setRequired();
        
        $this->addText('job', 'Pozice:');
        
        $this->addText('phone', 'Telefon:');
        
        $this->addText('email', 'Email:')
                        ->addRule(Form::EMAIL)
                        ->setRequired();
        
        $this->addTextArea('comments', 'Komentář:');
        
        $this->addCheckbox('agree', 'Souhlasím se zpracováním osobních údajů pro účely výběrového řízení.')
                            ->addRule(Form::FILLED, 'Musíte souhlasit se zpracováním osobních údajů');
        
        $this->addSubmit('send','Odeslat');
        
        $this->onSuccess[] = array($this, 'formSubmited');        
        
    }

    
    public function formSubmited($form) {

        
        $formValues = $form->getValues();  
        
        $this->subject = 'Vyplněný Formulář pro zájemce o spolupráci na webu MaxPraga';
        $this->templateFilename = 'partners.latte';
        $this->sendMail($formValues);
        
        //$this->sendMail($formValues);
        
        //mail('jurasm2@gmail.com', 'Kontakt z webové stránky', $this->mailBody, $headers);
        //$this->presenter->redirect('this');
        //$this->parent->invalidateControl();
        
        $this->presenter->flashMessage('Odesláno.');
        $this->presenter->redirect('this');
        //$this->presenter->invalidateControl();

    }
    
    
}

