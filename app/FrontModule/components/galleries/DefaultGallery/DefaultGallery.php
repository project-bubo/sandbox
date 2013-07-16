<?php

namespace FrontModule\Components\Galleries;

use Bubo;

class DefaultGallery extends Bubo\Components\RegisteredControl {   
    
    private $galleryId = NULL;
    
    public $templateFile;
    
    public function __construct($gid = NULL){
        parent::__construct(); 
        $this->galleryId = $gid;
    }
    
    public function setTemplateFile() {
        $this->templateFile = __DIR__ . '/templates/advanced.latte';
    }
    
    public function getGalleryId() {
        return $this->galleryId;
    }
    
//    public function __get($name) {
////        dump($name);
////        die();
//        parent::__get($name);
//    }
    
    public function render($gid = NULL, $page = NULL, $mode = NULL) {
        $this->setTemplateFile();
        
        
//        dump($gid, $mode);
//        die();
        
        if ($mode !== NULL) {
            
            //if (preg_match('#gallery\-([0-9]+)#', $gid, $matches)) {
                $images = $this->presenter->mediaManagerService->loadImages($gid, $mode);
                
//                dump($images);
//                die();
                
                $template = $this->template;
                $template->setFile($this->templateFile);
                $template->page = $page;
                $template->presenter = $this->presenter;
                
                $template->images = $images;
                $template->render();
                
                //die();
            //}
            
            
        } else {
        
        
    //        dump($gid);
    //        die();

            //dump($thumbNumber);
    //        die();

            if($gid) $this->galleryId = $gid;
            $template = $this->template;
            $template->setFile($this->templateFile);
            $template->rel = rand();
            $template->gallery = false;
            $template->images = false;
            $template->page = $page;
            $template->presenter = $this->presenter;
            $gal = $this->presenter->virtualDriveService->getGalleries((array) $gid);
            $this->presenter->virtualDriveService->setStorage('gallery');
            if (isset($gal[0])) {
                $this->presenter->virtualDriveService->setPath($gal[0]['path'].'/'.$gal[0]['nicename']);
                $template->galPath = $this->presenter->virtualDriveService->getFullPath();
                $template->images = $this->presenter->virtualDriveService->getGalleryFiles($gid);
                $template->galleryId = $gid;

                $template->render();
            }
        }
    }

    
}
