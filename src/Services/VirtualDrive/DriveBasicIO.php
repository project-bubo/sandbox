<?php


namespace Bubo\Services\VirtualDrive;

use Nette\Utils\Strings, Bubo;

/**
 * Description of DriveBasicIO
 *
 * @author toretak
 */
class DriveBasicIO extends Bubo\Services\BaseService {
    
    public $context;
    
    public function __construct($context) {
        $this->context = $context;
    }
    
    /**
     * 
     * @param string $from
     * @param string $to
     * @return bool 
     */
    public function copy($from, $to){
        return $this->_copy($from, $to);
    }
    
    
    
    /**
     *
     * @param string $from
     * @param string $to
     * @return bool 
     */
    public function move($from, $to){
        return $this->_copy($from, $to, TRUE);
    }
    
    /**
     *
     * @param string $path
     * @return bool 
     */
    public function unlink($path){
        return $this->_rm($path);
    }
    
    /**
     *
     * @param string $path
     * @return mixed 
     */
    public function mkDir($path, $protected = FALSE, $skipExisting = FALSE){
        $r = false;
        if(!file_exists($path)){
            $r = @mkdir($path, 0777, TRUE);
        }
        if(!$r && $skipExisting == FALSE){
            $i = 0;
            while(!$r && $i < 200){
                $i++;
                $r = @mkdir($path.'_'.$i, 0777, TRUE);
            }
            $r = substr($path,strrpos($path,'/') + 1).'_'.$i;
        }elseif($skipExisting){
            $r = TRUE;
        }
        if($protected){
            file_put_contents($path.'/.htaccess', 'Deny from All');
        }
        return $r === TRUE ? substr($path,strrpos($path,'/') + 1) : $r;
    }
    
    /**
     *
     * @param string $path
     * @param string $recursive
     * @return bool 
     */
    public function rmDir($path, $recursive = TRUE){
        if($recursive){
            return $this->_rm($path);
        }else{
            return rmdir($path);
        }
    }
    
    /**
     * Recursive deletineg - files and folders
     * @param type $path
     * @return boolean 
     */
    private function _rm($path){
        if(is_file($path)){
            return @unlink($path);
        }elseif(is_dir($path)){
            $ls = glob($path.'/*');
            $r = true;
            if ($ls !== FALSE) {
                foreach($ls as $lsit){
                    $r &= $this->_rm($lsit);
                }
            }
            if(file_exists($path.'/'.$this->mappingFileName)){
                @unlink($path.'/'.$this->mappingFileName);
            }
            $r &= rmdir($path);
            return $r;
        }
        return false;
    }
        
    /**
     * Copy or move recursive dirs from .. to ..
     * @param string $path
     * @param string $to
     * @param bool $move default false
     * @param string $chunk
     * @return boolean 
     */
    private function _copy($path, $to, $move = FALSE, $chunk = ''){
        if(is_file($path.$chunk)){
            $r = copy($path.$chunk, $to);
            if($move) $r &= @unlink($path.$chunk);
            return $r;
        }elseif(is_dir($path.$chunk)){
            $ls = glob($path.$chunk.'/');
            $r = true;
            if(!empty($chunk)) mkdir($to.$chunk);
            foreach($ls as $lsit){
                $r &= $this->_copy($path, $to, $move, '/'.$lsit);
            }
            return $r;
        }
        return false;
    }
}
