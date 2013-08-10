<?php

namespace BuboApp\AdminModule\VirtualDrive\Forms;

use Nette\Application\UI\Form,
    Nette\Environment;

class TinyAddFileForm extends \BuboApp\AdminModule\Forms\BaseForm {

    public function __construct($parent, $name) {
        parent::__construct($parent, $name);
        //$this->getElementPrototype()->class("ajax");
        $editorId = $this->getPresenter()->getUser()->getId();
        $fid = $parent->fid;
        $this->addMultipleFileUpload('upload','', 999);
        
        //$id = $this->getPresenter()->getParam('id');
        //$fid = $this->getPresenter()->getParam('fid');
        $this->addSubmit('send', 'Uložit');
        $this->addHidden('editor_id', $editorId);
        $this->addHidden('parent', $fid);
        $this->addHidden('id', false);
        $this->onSuccess[] = array($this, 'formSubmited');
       
        $this['send']->getControlPrototype()->class = "submit";
    }

    public function formSubmited($form) {
        $data = $form->getValues();
        try{
            $this->presenter->virtualDriveService->setPathByFolderId($this->parent->fid);
            $r = $this->presenter->virtualDriveService->addFiles($data['upload']);
            $this->parent->handleSetView('default', 0, $this->parent->fid);
        }catch(\Exception $e){
            $this->parent->flashMessage($e->getMessage());
            $this->parent->handleSetView('default', 0, $this->parent->fid);
        }
    }
}