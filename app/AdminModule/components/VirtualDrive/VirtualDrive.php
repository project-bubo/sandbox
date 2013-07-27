<?php

namespace AdminModule\Components;

use Bubo;

class VirtualDrive extends Bubo\Components\ContextMenuControl {   

    private $userId;
    
    /** @persistent */
    public $fid;

    /** @persistent */
    public $fileId;    
    
    /** @persistent */
    public $gid = 0;
    
    /** @persistent */
    public $view;

    /** @persistent */
    public $clipboard; 
    
    /** @persistent */
    public $displayType;
    
    private $fileInfo = "";
    
    private $folderInfo = "";
    
    
    public function setError($s){
        $this->error = $s;
    }
    
    public function createComponentTinyAddFileForm($name) {
        return new \AdminModule\VirtualDrive\Forms\TinyAddFileForm($this, $name);
    }
    public function createComponentTinyAddFolderForm($name) {
        return new \AdminModule\VirtualDrive\Forms\TinyAddFolderForm($this, $name);
    }
    public function createComponentTinyAddGalleryForm($name) {
        return new \AdminModule\VirtualDrive\Forms\TinyAddGalleryForm($this, $name);
    }
    
    public function createComponentTinyAddToGalleryForm($name) {
        return new \AdminModule\VirtualDrive\Forms\TinyAddToGalleryForm($this, $name);
    }
    public function __construct($parent, $name) {
        parent::__construct($parent, $name);
        $this->userId = $this->getPresenter()->getUser()->getId();
    }
    
    public function handleMove($fid = 0){
        $this->view = 'default';
        $this->fid = $fid;
        $this->invalidateControl();
    }
    
    public function handleGalleryMove($gid = 0){
        $this->gid = $gid;
        $this->invalidateControl();
    }
    
    public function handleChangeDisplayType($type){
        $this->displayType = $type;
        $this->invalidateControl();
    }
    
    public function handleSetView($view, $gid = 0, $id = NULL, $fid = 0){
        $this->view = $view;
        $this->fileId = $id;
        $this->gid = $gid;                                                  //galleryid neni tak podstatne a muze se brat z parametru handleru
        if($this->getParam('fid')) $this->fid = $this->getParam('fid');     //aby nebylo nutne v kazdem linku udavat fid, zmeni se pouze kdyz prijde
        $this->invalidateControl();
    }
    public function handleGetInfo($id){
        $this->fileInfo = $this->presenter->virtualDriveService->getFileInfo($id);
        $this->invalidateControl('left');
    }
    public function handleGetFolderInfo($id){
        $this->folderInfo = $this->presenter->virtualDriveService->getFolderById($id);
        $this->invalidateControl('left');
    }
    
    public function handleSortGallery($payload, $data){
        parse_str($this->presenter->getParam('data'));
        $this->presenter->virtualDriveService->sortGallery($galleryimage);
        //$this->invalidateControl();
        die;
    }
    
    public function handleDeleteFile($fileId){
        try{
            $this->presenter->virtualDriveService->setPathFromFileId($fileId);
            $finfo = $this->presenter->virtualDriveService->getFileInfo($fileId);
            $this->presenter->virtualDriveService->deleteFile($finfo['filename']);
        }catch(\Exception $e){
            $this->flashMessage($e->getMessage());
        }
        $this->view = 'default';
        $this->invalidateControl();
    }
    
    public function handleDeleteFolder($id, $recursively = FALSE){
        $this->presenter->virtualDriveService->setPathByFolderId($id);
        $fonfo = $this->presenter->virtualDriveService->getFolder($id);
        try{
            $this->presenter->virtualDriveService->deleteFolder($fonfo['name'], $recursively);
        }catch(\Exception $e){
            $this->flashMessage($e->getMessage());
        }
        $this->invalidateControl();
    }
    public function handleDeleteImage($gid, $fileId, $filename){
        $this->gid = $gid;
        try{
            $this->presenter->virtualDriveService->deleteFileFromGallery($gid,$fileId);
        }catch(\Exception $e){
            $this->flashMessage($e->getMessage());
        }
        $this->invalidateControl('gall');
        $this->invalidateControl('#flashMessages');
    }
    
    public function render() {
        
        if(!file_exists(dirname(__FILE__) . '/templates/'.$this->view.'.latte')) $this->view = "default";
        $template = parent::initTemplate(dirname(__FILE__) . '/templates/'.$this->view.'.latte');
        $template->display = $this->displayType ?: 'icons';
        $vd = $this->presenter->virtualDriveService;
        $vd->setPresenter($this->presenter);
        $template->data = false;
        if($this->view == 'default' || empty($this->view)){
            $vd->setStorage('file');
            $template->data = $vd->getDrive($this->fid); //gets folders and files in current folder
            $vd->setPathByFolderId($this->fid);
        }elseif($this->view == 'galleries' || $this->view == 'addToGallery'){
            $vd->setStorage('gallery');
            $template->galleries = $vd->getGalleries();
            $template->files = false;
            $template->gal = '';
            if($this->gid > 0){
                $template->galleries = false;
                $gal = $vd->getGalleries(array($this->gid));
                $template->files = $vd->getGalleryFiles($this->gid);
                $template->gal = $gal[0];
                $template->galThumb = $vd->getGalleryThumbnail($this->gid, 150, 150);
            }
            
        }elseif($this->view == 'file'){
            $vd->setPathByFolderId($this->fid);
            $this->fileInfo = $this->presenter->virtualDriveService->getFileInfo($this->fileId);
        }
        
        $template->fileInfo = $this->fileInfo;
        $template->thumb = false;
        $template->gallery = false;
        $template->galleryConfig = false;
        if($this->fileInfo){
            if($this->fileInfo['image']){
                $template->thumb = $vd->getImageThumbnail($this->fileInfo['filename'], 190, 190, 'CROP');
                if($this->fileInfo['gallery_id'] > 0){
                    $gal = $vd->getGalleries(array($this->fileInfo['gallery_id']));
                    $template->detail = $gal[0]['path'].'/'.$this->fileInfo['filename'];
                    $template->gallery = $gal[0];
                    $template->galleryConfig = @unserialize($gal[0]['config']);
                }else{
                    $template->detail = $vd->getFullPath().'/'.$this->fileInfo['filename'];
                }
            }
        }
        $template->folderInfo = $this->folderInfo;
        $template->tree = $vd->getFolderTree($this->fid);
        $template->fid = $this->fid;
        $template->gid = $this->gid;
        $template->parent = isSet($template->data['parent']) ? $template->data['parent'] : false;
        $template->render();
    }

    
}
