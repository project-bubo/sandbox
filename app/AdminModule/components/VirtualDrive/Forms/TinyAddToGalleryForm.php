<?php

namespace BuboApp\AdminModule\VirtualDrive\Forms;

use Nette\Application\UI\Form,
    Nette\Environment;

class TinyAddToGalleryForm extends \BuboApp\AdminModule\Forms\BaseForm {

    public function __construct($parent, $name) {
        parent::__construct($parent, $name);
       // $this->getElementPrototype()->class("ajax");  //nemuze to byt ajax kvuli mfu!
        $editorId = $this->getPresenter()->getUser()->getId();
        
        $this->addMultipleFileUpload('upload','', 999);
        $this->addSubmit('send', 'Přidat');
        $this->addHidden('editor_id', $editorId);     
        $this->addHidden('gallery_id', $parent->gid);    
        $this->onSuccess[] = array($this, 'formSubmited');
              
      
        $this['send']->getControlPrototype()->class = "submit";
    }

    public function formSubmited($form) {
        $data = $form->getValues(); 
        $r = $this->presenter->virtualDriveService->addFilesToGallery($data['upload'], $data['gallery_id']);
        
        $this->parent->handleSetView('galleries', $data['gallery_id']);
        
    }
}