<?php

namespace Bubo\Services\VirtualDrive;

use Nette\Utils\Strings,
    Nette\Image;
/**
 * Description of DriveIO
 *
 * @author toretak
 */
class DriveIO extends DriveBasicIO {

    

    /**
     * sets current session 
     * If You want to work with files you choose file
     * If You want to upload galery You set gallery (but You don't have tu because addGallery() do it for you)
     * @var string
     */
    public $currentStorage = 'file';
    
    public $realPath = WWW_DIR;
    
    public $basePath = '/data';
    
    public $relativePath = '';
    
    /**
     * Folder mapping
     * @var bool
     */
    public $enableMapping = TRUE;
    /**
     * Mapping file name
     * @var string
     */
    public $mappingFileName = ".mapping.vd";
    
    /**
     * Avaiable storages
     * @var array 
     */
    public $storages = array(
        'file' => '/files',
        'gallery' => '/gallery',
        'movie' => '/movie'
    );
    

    public function __construct($context) {
        
        parent::__construct($context);
        $params = $this->context->getParameters();
        
        $this->basePath = '/data/'.$params['projectName'];
//        dump($this->context->getParameters());
//        die();
        
        if(!preg_match('|[a-z0-9A-Z_\-\/]|',$this->basePath)){
            throw new VirtualDriveException("Invalid base path!", 3000);
        }
        
        
        
        $this->basePath = (Strings::startsWith($this->basePath, '/') ? $this->basePath : '/'.$this->basePath);
        $this->basePath .= Strings::endsWith($this->basePath , '/') ? '' : '/';
        if(!file_exists($this->realPath.$this->basePath)){
            $this->mkDir($this->basePath);
        }
        if(is_array($this->storages)){
            foreach($this->storages as $storage){
                if(!file_exists($this->realPath.$this->basePath.$storage)){
                    $this->mkDir($this->realPath.$this->basePath.$storage);
                }
            }
        }
    }
    
    public function setStorage($type){
        $this->currentStorage = $type;
    }
    
    public function getStorage(){
        return $this->currentStorage;
    }

    
    
       /**
     * Returns current relative path
     * @return string
     */
    public function getPath($withContext = FALSE){
       
        $session = isSet($this->storages[$this->currentStorage]) ? $this->storages[$this->currentStorage] : $this->storages['file'];
        $session = Strings::endsWith($session, '/') ? $session : $session.'/';
        $result = ($withContext ? $session : '').$this->relativePath . (Strings::endsWith($this->relativePath, '/') ? '':'/');
        if($result && $this->presenter instanceof \Nette\Application\UI\Presenter){
            $session = $this->presenter->session;
            $section = $session->getSection('VD');
            $section['path'] = $this->relativePath . (Strings::endsWith($this->relativePath, '/') ? '':'/');
        }
        return $result;
    }

    
    /**
     * Returns current full path
     * @return string
     */
    public function getFullPath($real = false){
        return ($real ? $this->realPath : '').$this->basePath.$this->getPath(TRUE);
    }
    
     /**
      * vstup do podslozky
      * @param type $subpath 
      */
    public function cd($subpath){
        $path = ( Strings::startsWith($subpath, '/') ? $subpath : $this->relativePath . '/'.$subpath );
        $this->setPath($path);
    }
    /**
     * cd .. -> go upper
     */
    public function cdd(){
        $path = $this->relativePath;
        $path = Strings::endsWith($path, '/') ? substr($path, 0, -1) : $path;
        if(strpos($path, '/') !== FALSE){
            $path = substr($path, 0, strrpos($path, '/'));
        }
        $this->setPath($path);
    }
    
    /**
     * Sets current relative path
     * @param string $path 
     */
    public function setPath($path, $protected = FALSE, $skipExisting = FALSE){
        /*if(!preg_match('|[a-z0-9A-Z_\-\/]|',$path)){
            throw new VirtualDriveException("Invalid path!", 3001);
        }*/
        if(strpos($path, '/') !== FALSE){
            $arr = explode('/',$path);   
            foreach($arr as $k => $chunk){
                $arr[$k] = Strings::webalize($chunk);
            }
            $path = implode('/', $arr);
        }else{
            $path = Strings::webalize($path);
        }
        $oldPath = $this->relativePath;
        
        $this->relativePath = Strings::startsWith($path, '/') ? substr($path,1) : $path;
        if(!file_exists($this->getFullPath(TRUE))){
            
            $result = $this->mkDir($this->getFullPath(TRUE), $protected, $skipExisting);
            if($result){
                $this->relativePath = strpos($this->relativePath, '/') !== FALSE ? substr($this->relativePath,0,strrpos($this->relativePath,'/')).'/' : '';
                $this->relativePath .= $result;
            }
        }
        if(!file_exists($this->getFullPath(TRUE))){
            $this->relativePath = $oldPath;
            throw new VirtualDriveException("Folder creating failed!",3010);
        }
        return TRUE;
    }
    
    /**
     * Smazani souboru
     * @param INT $id
     * @return bool 
     */
    public function deleteFile($file){
        if(Strings::startsWith($file, '/')){
            if(preg_match('|(.+)/([^/]+)$|',$file,$mtch)){
                $this->setPath($mtch[1]);
                $file = $mtch[2];
            }
        }
        return $this->unlink($this->getFullPath(TRUE).$file);
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
        return $this->rmDir($this->getFullPath(TRUE).$name, $recursively);
    }
    
    
    /**
     * Prida soubor do db
     * @param \Nette\Http\FileUpload $file
     * @return type 
     */
    public function uploadFile($file, $name){
        return $file->move($this->getFullPath(TRUE).$name);
    }
     
    
     /**
     * Funkce vraci informace o slozce ulozene v db, vlastnik, datum vytvoreni, parent, has_child
     * @param string $name
     * @param [OPT] bool $loadData nacist take soubory a slozky uvnitr pozadovane slozky [TRUE]
     * @return DiBiRow
     */
    public function getFolder($name = ''){
        if(file_exists($this->getFullPath(TRUE).$name.'/'.$this->mappingFileName)){
            return file_get_contents($this->getFullPath(TRUE).$name.'/'.$this->mappingFileName);
        }
        return FALSE;
    }

    
    
    public function createMappingFile($id){
        file_put_contents($this->getFullPath(TRUE).'/'.$this->mappingFileName, $id);
    }
    
    public function getFiles(){
        $ret = glob($this->getFullPath(TRUE).'/*');
        $return = array();
        foreach($ret as $it){
            if(is_file($it)){
                $return[] = basename($it);
            }
        }
        return $return;
    }
    
    
    public function copyAndResize($from, $to, $name, $resize){
        //$this->copy($from.$name, $to.$name);
        if(is_array($resize) && (isSet($resize['width']) || isSet($resize['height'])) && file_exists($from.$name)){
            if($resize['method'] == 'MAX_WIDTH'){
                $resize['height'] = NULL;
                $resize['method'] = Image::FIT;
            }
            if($resize['method'] == 'MAX_HEIGHT'){
                $resize['width'] = NULL;
                $resize['method'] = Image::FIT;
            }
            $crop = false;
            switch (strtoupper($resize['method'])) {
                case 'FILL':
                    $resize['method'] = Image::FILL;
                    break;
                case 'STRETCH':
                    $resize['method'] = Image::STRETCH;
                    break;
                case 'SHRINK_ONLY':
                    $resize['method'] = Image::SHRINK_ONLY;
                    break;
                case 'ENLARGE':
                    $resize['method'] = Image::ENLARGE;
                    break;
                case 'EXACT':
                    $resize['method'] = Image::EXACT;
                    break;
                case 'CROP':
                    $crop = true;
                    break;
                default:
                    $resize['method'] = Image::FIT;
            }
            
            $img = Image::fromFile($from.$name);
            if($crop){
                list($w,$h) = getimagesize($from.$name);
                if($w > $resize['width'] && $h > $resize['height']){
                    $img->resize($resize['width'], $resize['height'], Image::FILL);
                    $img->crop('50%','50%', $resize['width'], $resize['height']);
                }
            }else{
                $img->resize($resize['width'], $resize['height'], $resize['method']);
                $img->sharpen();
            }
            $img->save($to.$name, 90);
        }
    }
    
}


final class VirtualDriveException extends \Exception{}