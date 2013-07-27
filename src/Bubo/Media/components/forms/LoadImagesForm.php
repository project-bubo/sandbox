<?php

namespace Bubo\Media\Components\Forms;

use AdminModule\Forms\BaseForm;

class LoadImagesForm extends BaseForm {

    public function __construct($parent, $name) {
        parent::__construct($parent, $name);
        //$this->getElementPrototype()->class("ajax");
        //$editorId = $this->getPresenter()->getUser()->getId();
        //$fid = $parent->fid;
        $this->addMultipleFileUpload('upload','', 999);
        
        $media = $this->lookup('Bubo\\Media');
        $this->addHidden('folderId', $media->folderId);
        
        //$id = $this->getPresenter()->getParam('id');
        //$fid = $this->getPresenter()->getParam('fid');
        $this->addSubmit('send', 'Uložit');
        
//        $this->addHidden('editor_id', $editorId);
//        $this->addHidden('parent', $fid);
//        $this->addHidden('id', false);
        $this->onSuccess[] = array($this, 'formSubmited');
       
        
        $this->getElementPrototype()->class = 'ajax mfu';
        
        $this['send']->getControlPrototype()->class = "submit";
    }

    public function formSubmited($form) {
        $formValues = $form->getValues();
        
        $media = $this->lookup('Bubo\\Media');
        
        
//        dump($formValues);
//        die();
        
        $this->presenter->mediaManagerService->addImages($formValues);
        
        $this->parent->view = NULL;
        $media->invalidateControl();
        
//        dump($formValues);
//        die();
//        try{
//            $this->presenter->virtualDriveService->setPathByFolderId($this->parent->fid);
//            $r = $this->presenter->virtualDriveService->addFiles($data['upload']);
//            $this->parent->handleSetView('default', 0, $this->parent->fid);
//        }catch(\Exception $e){
//            $this->parent->flashMessage($e->getMessage());
//            $this->parent->handleSetView('default', 0, $this->parent->fid);
//        }
    }
}