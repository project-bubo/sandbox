<?php

namespace Bubo\Services\VirtualDrive;

use Nette\Utils\Strings;
/**
 * Description of DriveDbLear
 *
 * @author toretak
 */
class DriveDb extends DriveIO{
    
    /**
     * @var \DibiConnection 
     */
    private $connection;
    
    /**
     * @var \Services\VirtualDrive\Model\DriveModel
     */
    public $model;
    
    /**
     * @var \Nette\Application\UI\Presenter
     */
    public $presenter;
    
    
    
    public function __construct($arg, $context = NULL){
        parent::__construct($context);
        if($arg instanceof \DibiConnection){
            $this->connection = $arg;
            $this->model = new \Services\VirtualDrive\Model\DriveModel($arg, $this);
        }elseif($arg instanceof \Model\ModelLoader){
            $this->connection = NULL;
            $this->model = $arg->loadModel('DriveModel');
        }else{
            throw new VirtualDriveException('Construct fail! VirtualDrive needs DibiConnection or ModelLoader',3000);
        }
        
        
    }
    
        
    /**
     * @param \Nette\Application\UI\Presenter $presenter 
     */
    public function setPresenter($presenter){
        $this->presenter = $presenter;
    }
    /**
     * @return \Nette\Application\UI\Presenter
     */
    public function getPresenter(){
        return $this->presenter;
    }
    
    /**
     * Funkce vraci informace o slozce ulozene v db, vlastnik, datum vytvoreni, parent, has_child
     * @param string $name
     * @param [OPT] bool $loadData nacist take soubory a slozky uvnitr pozadovane slozky [TRUE]
     * @return DiBiRow
     */
    public function getFolder($name = '', $loadData = TRUE){
        $path = $this->getPath();
        $id = parent::getFolder($name);
        if(!$id){
            $id = $this->model->getFolderId($path.$name);
        }
        return $this->model->getFolderById($id, $loadData);
    }
    
    
    /**
     * Prida soubor do db
     * @param \Nette\Http\FileUpload $file
     * @return type 
     */
    public function addFile($file, $userId = NULL){
        if($file instanceof \Nette\Http\FileUpload){
            if(!$file->isOk()){
                return false;
            }
            $folder = $this->getFolder('', FALSE);
            if(!$folder){
                throw new VirtualDriveException('Current folder not found',3011);
            }
            $name = $this->model->addFile($file, $folder['folder_id'], 0, $userId);
            /*if(!file_exists($this->getFullPath(TRUE).'/images')){
                @mkdir($this->getFullPath(TRUE).'/images', 0777, TRUE);
            }*/
            $filename = $this->uploadFile($file, $name);
            return $this->_getIdFromFileName($filename);
        }else{
            throw new VirtualDriveException('Wrong file type!', 3012);
        }
    }
      
    
    private function _getIdFromFileName($filename) {        
        if (preg_match('#.*\-([0-9]+)\.[^\.]*$#', $filename, $matches)) {
            return $matches[1];
        }
        return NULL;
    }
    
    /**
     * Insert files 
     * array or \Nette\Http\FileUpload
     *
     * @param mixed $files
     * @param \Nette\Http\FileUpload $file
     */
    public function addFiles($files, $userId = NULL){
        if($files instanceof \Nette\Http\FileUpload){
            $this->addFile($files, $userId);
        }elseif(is_array($files)){
            foreach($files as $file){
                $this->addFile($file, $userId);
            }
        }else{
            throw new VirtualDriveException('Unknow files format!',3012);
        }        
    }
    
    
    public function addFilesToGallery($files, $galleryId){
        $gal = $this->getGalleries($galleryId);
        if($gal && isset($gal[0])){
            $this->_addFilesToGallery($gal[0]['path'].'/'.$gal[0]['nicename'], $files, $gal[0]['editor_id'], $gal[0]['gallery_id'], unserialize($gal[0]['config']));
        }
    }
    
    protected function _addFilesToGallery($galleryFolder, $files, $userId, $id, $conf){
        if($files instanceof \Nette\Http\FileUpload){
            $files = array(0 => $files);
        }
        if(is_array($files)){
            $this->setStorage('gallery');
            foreach($files as $i=>$file){
                $name = $this->model->addFile($file, NULL, $id, $userId, $i);
                $this->setPath($galleryFolder.'/originals');
                $this->uploadFile($file, $name);
                //manipulation
                $this->setPath($galleryFolder);
                $resize = $conf['size'];
                $this->copyAndResize($this->getFullPath(TRUE).'/originals/', $this->getFullPath(TRUE), $name, $resize);

                foreach($conf['thumbnails'] as $tn => $thumb){
                    $this->mkDir($this->getFullPath(TRUE).'/thumbs/'.$tn.'/', FALSE, TRUE);
                    $this->copyAndResize($this->getFullPath(TRUE).'/originals/', $this->getFullPath(TRUE).'/thumbs/'.$tn.'/', $name, $thumb);
                }
            }
            if(isSet($conf['preserveOriginals']) && !$conf['preserveOriginals']){
                $this->setPath($galleryFolder);
                $this->rmDir($this->getFullPath(TRUE).'/originals/');
            }
        }else{
            return false;
            //throw new VirtualDriveException('Unknow files format!',3012);
        }
    }
    
    
        /**
     * Smazani souboru
     * @param INT $id
     * @return bool 
     */
    public function deleteFile($name){
        $id = $this->_getIdFromFileName($name);
        parent::deleteFile($name);        
        return $this->model->deleteFile($id);
    }
    
    /**
     * smazani folder
     * @param INT $id
     * @return bool 
     */
    public function deleteFolder($name, $recursively = TRUE){
        //@TODO: odmazat soubory a slozku jako takovou
        if(Strings::startsWith($name, '/')){
            if(preg_match('|(.+)/([^/]+)$|',$name,$mtch)){
                $this->setPath($mtch[1]);
                $name = $mtch[2];
            }
        }
        $folder = $this->getFolder($name, TRUE);
        if(!$recursively && (count($folder['folders']) > 0 || count($folder['files']) > 0)){
            throw new VirtualDriveException('Folder is not empty',3105);
        }
        $this->model->deleteFolder($folder);
        return parent::deleteFolder($folder['nicename'], $recursively);
    }
    
    public function deleteGallery($id){
        $gal = $this->getGalleries((array) $id);
        if(!$gal){
            throw new VirtualDriveException('Gallery not found');
        }
        $this->setStorage('gallery');
        $this->setPath($gal[0]['path']);
        //$files = $this->model->getGalleryItems($gal[0]['gallery_id']);
        $this->model->deleteFilesByGalleryId($gal[0]['gallery_id']);
        $this->model->deleteGallery($gal[0]['gallery_id']);
        $this->rmDir($this->getFullPath(TRUE).$gal[0]['nicename']);
    }
    
    /**
     * Sets current relative path and update DB structure, setPath do the same thing without creating db structure..
     * @param string $path 
     */
    public function setDrivePath($path){
        if($this->setPath($path)){
            $this->_updateStructure($path);
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    public function setPathByFolderId($folderId){
        $path = $this->model->getFolderPath($folderId);
        return $this->setPath($path);
    }
    
    public function createGallery($galleryName, $userId, $path = '', $config = NULL){
        $nicename = Strings::webalize($galleryName);
        if(($d = $this->mkDir($this->getFullPath(TRUE).$path.'/'.$nicename)) === FALSE){
            throw new VirtualDriveException('Can\'t create directory.',3005);
        }
        $nicename = $d;
        $this->setPath($path.'/'.$d);
        return $this->model->addGallery($galleryName, $nicename, $userId, $path, $config);
    }
    
    public function getGalleryMainImage($gid){
        $gal = $this->model->getGallery($gid);
        $this->setPath($gal['path'].'/'.$gal['nicename']);
        $arr = $this->getFiles();
        if(count($arr) < 1) $arr[] = 'no-image.png';
        return $this->getFullPath().$arr[0];
    }
    
    public function getGalleries($ids = array()){
        return $this->model->getGalleries((array) $ids);
    }
    
    public function getGalleryFiles($gid){
        return $this->model->getGalleryItems($gid);
    }
    
    public function getFileInfo($id){
        return $this->model->getFileInfo($id);
    }
    
    public function getDrive($parent){
        return $this->model->getFolders($parent);
    }
    
    public function getFolderTree($level){
        return $this->model->getFolderTree($level);
    }
    
    public function isFolderEmpty($fid){
        return $this->model->isFolderEmpty($fid);
    }
    
    public function sortGallery(array $data){
        $this->model->sortGallery($data);
    }
    /**
     * synchronizuje skutecnou strukturu do db [CREATE]
     */
    private function _updateStructure($path) {
        if($this->currentStorage == 'file'){
            $path = Strings::startsWith($path, '/') ? substr($path, 1) : $path;
            $id = $this->model->updateStructure($path);
        }
        if($this->enableMapping && $id > 0){
            parent::createMappingFile($id);
        }
    }
    
    /*public function getFilesByPageId($ids, $type = 'files'){
        return $this->model->getFilesByPageId($ids, $type);
    }*/
    public function getGalleriesByPageId($ids){
        $files = $this->model->getGalleriesByPageId($ids);
        $this->setStorage('gallery');
        $storage = isSet($this->storages[$this->currentStorage]) ? $this->storages[$this->currentStorage] : $this->storages['file'];
        $array = array();
        if($files && is_array($files)){
            foreach($files as $pageId => $propertyArray){
                $array[$pageId] = array();
                foreach($propertyArray as $propertyName => $images){
                        $array[$pageId][$propertyName] = array();
                        $config = false;
                        foreach($images as $image){
                            $url = $this->basePath.$storage.'/'.$image['path'].'/'.$image['gallery_url'].'/';
                            $thumbnails = array();
                            $config = $config === false ? @unserialize($image['config']) : $config;

                            if($config && isSet($config['thumbnails'])){
                                foreach($config['thumbnails'] as $tnName => $foo){
                                    $thumbnails[$tnName] = array(
                                        'url' => $url.'thumbs/'.$tnName.'/'.$image['filename'],
                                    );
                                }
                            }
                            $array[$pageId][$propertyName][] = array(
                                'image'   =>  array(
                                                'url' => $url.$image['filename'],
                                                'labels' => array(
                                                                'main' => $image['name']
                                                            )
                                            ),
                                'thumbnails' => $thumbnails

                            );

                        }

                }

            }
        }
        return $array;
        
    }
    
    public function getFilesByPageId($ids){
        $files = $this->model->getFilesByPageId($ids);
        $this->setStorage('file');
        $storage = isSet($this->storages[$this->currentStorage]) ? $this->storages[$this->currentStorage] : $this->storages['file'];
        $array = array();
        if($files && is_array($files)){
            foreach($files as $pageId => $propertyArray){
                $array[$pageId] = array();
                foreach($propertyArray as $propertyName => $files){
                        $array[$pageId][$propertyName] = array();
                        $config = false;
                        foreach($files as $file){
                            $this->setPathByFolderId($file['folder_id']);
                            $array[$pageId][$propertyName]['name'] = $file['name'];
                            $array[$pageId][$propertyName]['url'] = $this->getFullPath().$file['filename'];
                        }

                }

            }
        }
        return $array;
    }
    
    public function __call($name, $args) {
        if (preg_match('#^attach([a-zA-Z]+)ToPage$#', $name, $matches)) {
            if (count($args) < 4) {
                $args[] = NULL;
            }
            $args[] = $matches[1];
            return call_user_func_array(array($this, '__attach'), $args);
        }
        parent::__call($name, $args);
    }
    
   
    
    private function __attach($pageId, $id, $propertyName, $oldPageId = NULL, $storage = 'file'){
        if($oldPageId !== NULL){
            return $this->model->reattachFileToPage($pageId, $oldPageId);
        }
        return $this->model->attachFileToPage(strtolower($storage), $pageId, $id, $propertyName);
    }
    
    /**
     * Update vazeb z oldPageId na newPageId
     * @param int $newPageId
     * @param int $oldPageId
     * @return boolean 
     */
    public function reconnectPages($newPageId, $oldPageId) {
        return $this->model->reconnectPages($newPageId, $oldPageId);
    }
    /**
     * Vytvori duplikat vazby mezi soubory a strankami, z $sourcePageId vytvori vazbu na $newPageId
     * @param int $newPageId
     * @param int $oldPageId
     * @return boolean 
     */
    public function crateDuplicates($newPageId, $oldPageId){
        return $this->model->createDuplicates($newPageId, $oldPageId);
    }
}

