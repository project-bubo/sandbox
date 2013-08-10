<?php

namespace BuboApp\AdminModule\VirtualDrive\Forms;

use Nette\Application\UI\Form,
    Nette\Environment;

class TinyAddGalleryForm extends \BuboApp\AdminModule\Forms\BaseForm {

    public function __construct($parent, $name) {
        parent::__construct($parent, $name);
       // $this->getElementPrototype()->class("ajax");  //nemuze to byt ajax kvuli mfu!
        $editorId = $this->getPresenter()->getUser()->getId();
        
        $id = $this->getPresenter()->getParam('id');
        $this->addText('name','Název galerie')->setRequired('Název galerie musí být vyplněn ');
        $this->addMultipleFileUpload('upload','', 999);
        $this->addSubmit('send', 'Uložit');
        $this->addHidden('editor_id', $editorId);
        $this['name']->getControlPrototype()->class[] = "input";            
        $this->onSuccess[] = array($this, 'formSubmited');
              
      
        $this['send']->getControlPrototype()->class = "submit";
    }

    public function formSubmited($form) {
        $data = $form->getValues(); 
        try{
            $r = $this->presenter->virtualDriveService->addGallery($data['upload'], $data['name']);
            $this->parent->handleSetView('galleries');
        }catch(\Exception $e){
            $this->parent->flashMessage($e->getMessage());
            $this->parent->invalidateControl();
        }
    }
}