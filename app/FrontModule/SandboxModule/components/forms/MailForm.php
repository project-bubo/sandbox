<?php

namespace FrontModule\SandboxModule\Forms;

use Nette, Nette\Application\UI\Form;


class MailForm extends BaseForm {
    
    public $to = 'max@maxpraga.cz';
    //public $to = 'jurasm2@gmail.com';
    
    public $subject = 'Kontakt z webove stranky';

    public $from = 'MaxPraga <noreplay@maxpraga.cz>';
    
    public $templateFilename = 'contact.latte';
    
    
    public function __construct($parent, $name) {
        parent::__construct($parent, $name);
        
        $this->getElementPrototype()
                            ->novalidate('novalidate');
    }
    
    public function sendMail($formValues) {
        
//        dump($formValues);
//        die();
//        
        $template = new Nette\Templating\FileTemplate(__DIR__.'/emailTemplates/'.$this->templateFilename);
        $template->registerFilter(new Nette\Latte\Engine);
        $template->registerHelperLoader('Nette\Templating\Helpers::loader');
        
        $attachments = array();
        
        if (isset($formValues['cv']) && $formValues['cv'] instanceof Nette\Http\FileUpload) {
            if ($formValues['cv']->isOk()) {
                $attachments[$formValues['cv']->getName()] = $formValues['cv']->getContents();
            }
        }

        unset($formValues['cv']);
        
        $template->values = $formValues;

        
        
//        echo $template;
//        die();
        
        $mail = new \Nette\Mail\Message;
        $mail->setFrom($this->from)
            ->setSubject($this->subject)
            ->addTo($this->to)
            ->setHtmlBody($template);
        
        if ($attachments) {
            foreach ($attachments as $name => $content) {
                $mail->addAttachment($name, $content);
            }
        }
        
//        foreach ($tos as $to) {
//            $mail->addTo($to);
//        }
        
        $mail->send();

    }
    
}

