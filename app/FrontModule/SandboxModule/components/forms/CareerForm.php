<?php

namespace FrontModule\SandboxModule\Forms;

use Nette\Application\UI\Form;

class CareerForm extends MailForm {

    
    public function __construct($parent, $name) {
        parent::__construct($parent, $name);        
        
        
        $renderer = $this->getRenderer();
        $renderer->wrappers['controls']['container'] = NULL;
        $renderer->wrappers['pair']['container'] = 'div';
        $renderer->wrappers['label']['container'] = NULL;
        $renderer->wrappers['control']['container'] = NULL;

        $this->getElementPrototype()->class[] = 'input-box-form';
        
        $this->addGroup('Osobní údaje');
        $this->addText('name', 'Jméno')
                            ->setRequired();
        
        $this->addText('surname', 'Příjmení')
                            ->setRequired();
        
        $this->addText('title', 'Titul');
        
        $this->addText('date_birth', 'Datum narození')
                            ->setRequired();
        
        $this->addText('address', 'Adresa')
                            ->setRequired();
        
        
        $this->addGroup(' ');
        $this->addText('city', 'Město')
                            ->setRequired();
        
        $this->addText('zip', 'PSČ')
                            ->setRequired();
        
        $this->addText('phone', 'Telefon')
                            ->setRequired();
        
        $this->addText('email', 'Email')
                            ->addRule(Form::EMAIL, 'Email není ve správném formátu')
                            ->setRequired('Zadejte, prosím, Váš email');
        
        
        $edicationList = array(
                            'zakladni' =>  'základní',
                            'stredoskolske' =>  'středoškolské',
                            'vyssi' =>  'vyšší',
                            'vysokoskolske' =>  'vysokoškolské'
        );
        
        $this->addSelect('education', 'Vzdělání', $edicationList)
                        ->setPrompt('-- vyberte --')
                        ->setRequired();
        
        $this->addUpload('cv', 'CV:')
                       ->addRule(Form::MAX_FILE_SIZE, 'Maximální velikost souboru je 1 MB.', 1 * 1024 * 1024 /* v bytech */);
        
        
        $this->addGroup('Jazykové znalosti');
        
        $langSkils = array(
                       '0'  =>  'výborná - 0',
                       'B'  =>  'dobrá - B',
                       'C'  =>  'ucházející - C',
                       'S'  =>  'základní - S'
        );
        
        $this->addSelect('first_language', 'První jazyk', $langSkils)
                        ->setPrompt('-- vyberte --');
        
        $this->addSelect('second_language', 'Druhý jazyk', $langSkils)
                        ->setPrompt('-- vyberte --');
        
        $this->addGroup(' ');
        $this->addText('previous_job', 'Předcházející zaměstnání')
                            ->setRequired();
        
        $this->addText('entry', 'Možnost nástupu')
                            ->setRequired();
        
        $this->addText('position', 'Poptávaná pozice')
                            ->setRequired();
        
        $this->addCheckbox('agree', 'Souhlasím se zpracováním osobních údajů pro účely výběrového řízení.')
                            ->addRule(Form::FILLED, 'Musíte souhlasit se zpracováním osobních údajů');
        
        $this->setCurrentGroup(NULL);
        $this->addSubmit('send','Odeslat');
        
        $this->onSuccess[] = array($this, 'formSubmited');        
        
    }

    
    public function formSubmited($form) {

        
        $formValues = $form->getValues();  
        
        $this->subject = 'Vyplněný formulář Kariéra na webu MaxPraga';
        $this->templateFilename = 'career.latte';
        $this->sendMail($formValues);
        
//        dump($formValues);
//        die();
        
        //$this->sendMail($formValues);
        
        //mail('jurasm2@gmail.com', 'Kontakt z webové stránky', $this->mailBody, $headers);
        //$this->presenter->redirect('this');
        //$this->parent->invalidateControl();
        
        $this->presenter->flashMessage('Odesláno.');
        $this->presenter->redirect('this');
        //$this->presenter->invalidateControl();

    }
    
    
}

