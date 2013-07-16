<?php

namespace FrontModule\SandboxModule\Forms;

use Nette\Application\UI\Form;

class CatalogForm extends MailForm {

    public function atLeastOneCheckBoxChecked($item, $arg) {
        // only $arg is importat -> contains all elements
        $bool = FALSE;
        foreach ($arg->getComponents() as $componentName => $component) {
            $bool |= $component->value;
            if ($bool) break;
        }
        return $bool;
    }
    
    
    public function __construct($parent, $name) {
        parent::__construct($parent, $name);        
        
        
        $renderer = $this->getRenderer();
        $renderer->wrappers['controls']['container'] = NULL;
        $renderer->wrappers['pair']['container'] = 'div';
        $renderer->wrappers['label']['container'] = NULL;
        $renderer->wrappers['control']['container'] = NULL;

        $this->getElementPrototype()->class[] = 'input-box-form';
        
        $this->addGroup();
        $this->addText('name', 'Jméno')
                            ->setRequired();
        
        $this->addText('surname', 'Příjmení')
                            ->setRequired();
        
        $this->addText('address', 'Adresa')
                            ->setRequired();
                
        $this->addText('city', 'Město')
                            ->setRequired();
        
        $this->addText('zip', 'PSČ')
                            ->setRequired();
        
        $this->addText('email', 'Email')
                            ->addRule(Form::EMAIL, 'Email není ve správném formátu')
                            ->setRequired('Zadejte, prosím, Váš email');
        
        $this->addGroup();
        $c = $this->addContainer('catalogs');
        $c->addCheckbox('maxmara', 'Katalog Max Mara');
        $c->addCheckbox('weekend', 'Katalog Weekend Max Mara');
        $c->addCheckbox('marina', 'Katalog Marina Rinaldi');
        $c->addCheckbox('marella', 'Katalog Marella');        
        $c->addCheckbox('iblues', 'Katalog iBlues');        
        
        
        $this['catalogs']['maxmara']
                    ->addRule(array($this, 'atLeastOneCheckBoxChecked'), 'Označte alespoň jeden katalog', $this['catalogs']);

        
        $this->addGroup();
        $this->addCheckbox('agree', 'Souhlasím s použitím svých kontaktních údajů pro marketingové účely společnosti MaxPraga s.r.o.')
                ->setRequired(); 

        $this->addSubmit('send','Odeslat');
        
        //$this['name']->getControlPrototype()->class = 'full-width-input';
//        $this['email']->getControlPrototype()->class = 'input';
//        $this['text']->getControlPrototype()->class = 'input2';
        
        $this->onSuccess[] = array($this, 'formSubmited');        
        
    }

    
    public function formSubmited($form) {

        
        $catalogs = array(
                        'maxmara'   => 'Max Mara',
                        'weekend'   => 'Weekend Max Mara',
                        'marina'   => 'Marina Rinaldi',
                        'marella'   => 'Marella',
                        'iblues'   => 'iBlues'
        );
        
        $formValues = $form->getValues();  
        
        foreach ($formValues['catalogs'] as $key => $bool) {
            
            if ($bool) {
                $formValues['catalogs'][$key] = $catalogs[$key];
            }
            
        }
        
//        dump($formValues);
//        die();
        
        
        $this->subject = 'Objednávka katalogu na webu MaxPraga';
        $this->templateFilename = 'catalog.latte';
        $this->sendMail($formValues);
        
        //mail('jurasm2@gmail.com', 'Kontakt z webové stránky', $this->mailBody, $headers);
        //$this->presenter->redirect('this');
        //$this->parent->invalidateControl();
        
        $this->presenter->flashMessage('Objednávka odeslána.');
        $this->presenter->redirect('this');
        //$this->presenter->invalidateControl();

    }
    
    
}

