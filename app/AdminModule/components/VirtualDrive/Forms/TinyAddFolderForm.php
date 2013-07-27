<?php

namespace AdminModule\VirtualDrive\Forms;

use Nette\Application\UI\Form,
    Nette\Environment,
    AdminModule\Forms;

class TinyAddFolderForm extends Forms\BaseForm {

    
    public function __construct($parent, $name) {
        parent::__construct($parent, $name);
        $this->getElementPrototype()->class("ajax");
        $fid = $parent->fid;
        $identity = $this->presenter->getUser()->getIdentity();
        $editorId = $identity ? $identity->id : NULL;
        
//        $model = $this->presenter->mceModel;
//        $id = $this->getPresenter()->getParam('id');
//        //$fid = $this->getPresenter()->getParam('fid');
//        $data = false;
//        if($id){
//            $data = $model->getFolder($id);
//        }
        $this->addText('name','Název adresáře')->addRule(Form::FILLED,'Název adresáře musí být vyplněn ');
        $this->addSubmit('send', 'Uložit');
        $this->addHidden('editor_id', $editorId);
        $this->addHidden('parent', $fid);
        $this['name']->getControlPrototype()->class[] = "input";
 
        $this->onSuccess[] = array($this, 'formSubmited');
 
        
        $this['send']->getControlPrototype()->class = "submit";
    }

    public function formSubmited($form) {
        try {    
            $values = $form->getValues();
            $this->presenter->virtualDriveService->setPathByFolderId($this->parent->fid);
            $path = $this->presenter->virtualDriveService->getPath(FALSE); //gets current path without context
            $res = $this->presenter->virtualDriveService->setDrivePath($path.'/'.  $values['name']);
            if(!$res){
                $this->parent->flashMessage('Došlo k chybě při ukládání.');
            }
            $this->parent->handleSetView('default', $this->parent->fid);
        }catch(\Exception $e){
            $this->parent->flashMessage($e->getMessage());
            $this->parent->invalidateControl();
            if($this->presenter->isAjax()){
                $this->parent->invalidateControl();
            }
        }
    }
}