<?php

namespace Bubo\Services\VirtualDrive;

use \Nette\Utils\Strings;

final class Drive extends DriveDb{
    
    
    public $config;
    
    public function __construct($arg, $context = NULL){
        if(file_exists(__DIR__.'/config/config.neon')){
            $config = \Nette\Utils\Neon::decode(file_get_contents(__DIR__.'/config/config.neon'));
            if(isSet($config['Drive']['basePath'])) $this->basePath = $config['Drive']['basePath'];
            if(isSet($config['Drive']['mapping'])) $this->enableMapping = $config['Drive']['mapping'] == 'yes' ? true : false;
            if(isSet($config['Drive']['mappingFileName'])) $this->mappingFileName = $config['Drive']['mappingFileName'];
            if(isSet($config['Drive']['storages'])) $this->storages = $config['Drive']['storages'];
            if(isSet($config['Drive']['defaultStorage'])) $this->currentStorage = $config['Drive']['defaultStorage'];
            $this->config = $config;
        }
        parent::__construct($arg, $context);
        if($this->presenter instanceof \Nette\Application\UI\Presenter){
            $session = $this->presenter->session;
            $section = $session->getSection('VD');
            if(isSet($section['path'])){
                $this->setPath($section['path']);
            }
        }
        
//        dump($context);
//        die();
    }
    
    
    public function setLocation($section, $path){
        $this->setStorage($section);
        $this->setPath($path);
    }
    
    public function setFilePath($path){
        $this->setStorage('file');
        $this->setDrivePath($path);
    }
    
    
    public function setGalleryPath($path){
        $this->setStorage('gallery');
        $this->setPath($path);
    }
    
    /**
     * Vytvoreni nove galerie
     * @param type $values - name,editor_id,public[bool],start_public,stop_public
     * 
     * @return type 
     */
    public function addGallery($files, $galleryName, $conf = NULL){
        if(!is_array($conf)){
            $params = $this->context->getParameters();
            $driveConfigPath = $params['projectDir'] . '/config/drive.neon';
            
            if (!is_file($driveConfigPath)) {
                $driveConfigPath = __DIR__.'/examples/gal.neon';
            }
           
            $conf = \Nette\Utils\Neon::decode(file_get_contents($driveConfigPath));
            $conf = $conf['gallery'];
        }
        $this->setStorage('gallery');
        $userId = 0;
        if($this->presenter){
            $userId = $this->presenter->user->isLoggedIn() ? $this->presenter->user->getId() : 0;
        }
        $galPath = (isSet($conf['path']) && !empty($conf['path'])) ? Strings::webalize($conf['path']) : '';
        $id = $this->createGallery($galleryName, $userId, $galPath, serialize($conf));
        $galleryFolder = $this->relativePath;
        $this->mkDir($this->getFullPath(TRUE).'/thumbs', FALSE, TRUE);
        $this->mkDir($this->getFullPath(TRUE).'/originals', FALSE, TRUE);
        $this->_addFilesToGallery($galleryFolder, $files, $userId, $id, $conf);
        if(isSet($conf['preserveOriginals']) && !$conf['preserveOriginals'] ){
            $this->setPath($galleryFolder);
            $this->rmDir($this->getFullPath(TRUE).'/originals/');
        }
        return $id;
    }
    
    public function getGalleryThumbnail($gid, $width, $height, $method = NULL){
        $fromImage = $this->getGalleryMainImage($gid);
        return $this->getImageThumbnail($fromImage, $width, $height, $method);
    }

    public function getImageThumbnail($name, $width, $height, $method = NULL){
        $path = $this->getFullPath(TRUE).basename($name);
        return $this->getImageThumbnailFromRealPath($path, $width, $height, $method);
    }
    
    public function getImageThumbnailFromRealPath($path, $width, $height, $method = NULL){
        if(!file_exists($path)) $path = $this->realPath.'/'.$path;
        $name = basename($path);
        $tmpName = $width . '_' . $height . '_' . strtolower($method) . '_' . $name;
        $resize = array("width" => $width, 'height' => $height, 'method' => $method);
        $to = '/'.$this->basePath.'/temp/';
        
        if(!file_exists($this->realPath.$to.$tmpName)){
            $this->copyAndResize(dirname($path).'/', $this->realPath.$to, $name, $resize);
            $this->move($this->realPath.$to.$name,$this->realPath.$to.$tmpName);
        }
        return str_replace('//','/',$to.$tmpName);
    }
    
    
    public function getFolderListByParent($parent = 0){
        return $this->getDrive($parent);
    }
    
    public function setPathFromFileId($fileId){
        $finfo = $this->getFileInfo($fileId);
        if($finfo['gallery_id']){
            $gal = $this->getGalleries(array($finfo['gallery_id']));
            if(isSet($gal[0])){
                $this->setStorage('gallery');
                $this->setPath('/'.$gal[0]['path']);
            }
        }
        if($finfo['folder_id']){
            $this->setPathByFolderId($finfo['folder_id']);
        }
    }
    
    public function getFilePath($fileId, $absolute = FALSE){
        $finfo = $this->getFileInfo($fileId);
        $storage = $this->getStorage();
        $path = $this->getPath();
        $filename = false;
        if($finfo['gallery_id']){
            $gal = $this->getGalleries(array($finfo['gallery_id']));
            if(isSet($gal[0])){
                $this->setStorage('gallery');
                $this->setPath('/'.$gal[0]['path'].'/'.$gal[0]['nicename']);
                $filename = $this->getFullPath($absolute).'/'.$finfo['filename'];
                $this->setPath($path);
                $this->setStorage($storage);
                return $filename;
            }
        }
        if($finfo['folder_id']){
            $this->setPathByFolderId($finfo['folder_id']);
            $filename = $this->getFullPath($absolute).$finfo['filename'];
            $this->setPath($path);
            $this->setStorage($storage);
        }
        if($filename){
            return $filename;
        }
        return NULL;
        //throw new VirtualDriveException('File not found!',3500);
    }
    
    
    public function deleteFileFromGallery($gid, $fileId){
        $gal = $this->getGalleries($gid);
        $thumbs = array();
        if($gal){
            $config = @unserialize($gal[0]['config']);
            if(isset($config['thumbnails'])){
                $thumbs = array_keys($config['thumbnails']);
            }
        }
        
        $path = $this->getFilePath($fileId, TRUE);        
        $dirname = dirname($path);
        $filename = basename($path);
        $this->unlink($path);
        foreach($thumbs as $thumb){
            $this->unlink($dirname.'/'.$thumb.'/'.$filename);
        }        
        $this->model->deleteFile($fileId);
    }
    
    public function getFolderById($id){
        return $this->model->getFolderById($id, TRUE);
    }
}