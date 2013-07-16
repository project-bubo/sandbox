<?php

namespace Model;

use Nette;
use Nette\Image;
use Nette\Utils\Finder;

final class MediaFSModel extends Nette\Object {

    /**
     * Media manager
     * @var \Bubo\Services\MediaManager
     */
    private $mediaManager;
    
    public function __construct($mediaManager) {
        $this->mediaManager = $mediaManager;
    }

    private function _checkBaseDir($dir) {

        if (!is_dir($dir)) {
            throw new Nette\InvalidStateException("Adresář '$dir' neexistuje. Vytvořte jej a nastavte práva pro zápis.");
        }
        
        $uniq = uniqid("_", TRUE);
        if (!@mkdir("$dir/$uniq", 0777)) { // @ - is escalated to exception
			throw new Nette\InvalidStateException("Není povolen zápis do adresáře '$dir'. Nastavte, prosím, práva pro zápis.");
		}
        @rmdir("$dir/$uniq"); // @ - directory may not already exist
        
    }
    
    public function createStructure() {
        $baseDir = $this->mediaManager->getBaseDir();
        $config = $this->mediaManager->getConfig();
        $structure = $config['structure'];
        
        $this->_checkBaseDir($baseDir);
        
        foreach ($structure as $dir) {
            $_dir = "$baseDir/$dir";

            if (!is_dir($_dir)) {
                @mkdir($_dir, 0777);
            }
            
            if ($dir !== 'temp') {
            
                // create root dirs
                $rootDir = $_dir . '/root';
                if (!is_dir($rootDir)) {
                    @mkdir($rootDir, 0777);
                }
                
            }
        }
        
    }
    
    public function createFolder($folderDir) {

        $dir = $this->mediaManager->getBaseDir() . '/' . $folderDir;
        if (!is_dir($dir)) {
            mkdir($dir, 0777);
        }
        
    }
    
    public function createGallery($folderDir) {
        
        $dir = $this->mediaManager->getBaseDir() . '/' . $folderDir;
        mkdir($dir, 0777);
        
        $dir = $this->mediaManager->getBaseDir() . '/' . $folderDir . '/originals';
        mkdir($dir, 0777);
        
//        $dir = $this->mediaManager->getBaseDir() . '/' . $folderDir . '/placeholder';
//        mkdir($dir, 0777);
        
    }
    
    public function deleteFolder($path) {
        
        $dir = $this->mediaManager->getBaseDir() . '/' . $path;
        
        if (is_dir($dir)) {
        
            $it = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($it as $file) {
                if (in_array($file->getBasename(), array('.', '..'))) {
                    continue;
                } elseif ($file->isDir()) {
                    rmdir($file->getPathname());
                } elseif ($file->isFile() || $file->isLink()) {
                    unlink($file->getPathname());
                }
            }

            @rmdir($dir); // @ - directory may not already exist
        }
    }
    
    private function _getModchunkFilename($filename, $modchunk) {
        
        $modChunkFilename = NULL;
        
        if (preg_match('#(.*)\.(.+)#', $filename, $matches)) {
            $modChunkFilename = $matches[1] . '_' . $modchunk . '.' . $matches[2];
        } else {
            $modChunkFilename = $filename . '_' . $modchunk;
        }
        
        return $modChunkFilename;
    }
    
    public function createModchunk($params) {
        return $params['width'] . 'x' . $params['height'] . '-' . strtolower($params['method']);
    }
    
    public function createThumbnail(&$image, $filename, $params) {
        
        if($params['method'] == 'CROP'){
            list($w,$h) = getimagesize($filename);
            if($w > $params['width'] && $h > $params['height']){
                $image->resize($params['width'], $params['height'], Image::FILL);
                $image->crop('50%','50%', $params['width'], $params['height']);
            }
            
            if (($params['width'] <= 150) && ($params['height'] <= 150)) {
                $image->sharpen();
            }
            
        }else{
            
            $oClass = new \ReflectionClass('Nette\Image');
            $array = $oClass->getConstants();
            
            $image->resize($params['width'], $params['height'], $array[$params['method']]);
            
            if (($params['width'] <= 150) && ($params['height'] <= 150)) {
                $image->sharpen();
            }
            
        }
        
    }
    
    public function getInternalImageFilename($filename, $type) {
        $config = $this->mediaManager->getConfig();
        $thumbParams = $config['internalThumbnails'][$type];
        $modchunk = $this->createModchunk($thumbParams);
        
        return $this->_getModchunkFilename($filename, $modchunk);
    }
    
    public function addFiles($files, $folderPath) {
        
        $fileBasePath = $this->mediaManager->getBaseDir() . '/' . $folderPath;        
        $config = $this->mediaManager->getConfig();        
        $internalThumbnails = $config['internalThumbnails'];
        
        foreach ($files as $file) {

            $filename = $fileBasePath . '/' . $file['filename'];
            $file['file']->move($filename);


            if ($file['file']->isImage()) {
                // create thumbnail for the image
                foreach ($internalThumbnails as $thumbName => $thumbParams) {
                    $image = $file['file']->toImage();

                    $this->createThumbnail($image, $filename, $thumbParams);

                    $modchunkFilename = $this->getInternalImageFilename($file['filename'], $thumbName);      

                    $tempPath = $this->mediaManager->getBaseDir() . '/temp/' . $modchunkFilename;                
                    $image->save($tempPath, 95);                
                }
            }           
        }
        
        
    }
    
    
    public function addImages($files, $folderPath) {
        
        $fileBasePath = $this->mediaManager->getBaseDir() . '/' . $folderPath;
        $config = $this->mediaManager->getConfig();
        $internalThumbnails = $config['internalThumbnails'];
        
        foreach ($files as $file) {
            
            if ($file['file']->isImage()) {
                $filename = $fileBasePath . '/originals/' . $file['filename'];

                $file['file']->move($filename);
                
                // create thumbnail for the image
                foreach ($internalThumbnails as $thumbName => $thumbParams) {
                    $image = $file['file']->toImage();

                    $this->createThumbnail($image, $filename, $thumbParams);

                    $modchunkFilename = $this->getInternalImageFilename($file['filename'], $thumbName);      

                    $tempPath = $this->mediaManager->getBaseDir() . '/temp/' . $modchunkFilename;                
                    $image->save($tempPath, 95);  
                }
            }           
        }
        
    }
    
    public function getAllInternalImagePaths($filename) {
        
        $internalImagePaths = array();
        
        $config = $this->mediaManager->getConfig();
        $internalThumbnails = $config['internalThumbnails'];
        
        if ($internalThumbnails) {
            foreach ($internalThumbnails as $name => $params) {
                $modchunkFilename = $this->getInternalImageFilename($filename, $name);                
                $internalImagePaths[] = $this->mediaManager->getTempDir() . '/' . $modchunkFilename;  
            }
        }
        
        return $internalImagePaths;
        
    }
    
    private function _removeAllInternalImages($filename) {
        
        //$internalImagePaths = $this->getAllInternalImagePaths($filename);
        
        
        $baseName = $filename;
        if (preg_match('#^(.*)\.(.+)$#', $filename, $matches)) {
            $baseName = $matches[1];
        }
//        dump($baseName);
//        die();
        
        $tempDir = $this->mediaManager->getTempDir();
        foreach (Finder::findFiles($baseName.'*.*')->in($tempDir) as $key => $file) {
            $_filename = $file->getFilename();

            if (preg_match('#(.+)\_([0-9]+)x([0-9]+)\-(.+)#', $_filename, $matches)) {
                if ($matches[1] == $baseName) {
                    @unlink($key);
                }
            }
            
        }
       
    }
    
    private function _removeImageFromGallery($filename, $folderPath) {
        
        $fullFolderPath = $this->mediaManager->getBaseDir() . '/' . $folderPath;
        
        if (is_dir($fullFolderPath)) {
            if (preg_match('#(.*)\.(.+)#', $filename, $matches)) {
                foreach (Finder::findFiles($matches[1].'*')->from($fullFolderPath) as $key => $file) {
                    @unlink($key);
                }
            }
        }
    }
   
    
    
    public function deleteImages($images, $folderPath) {
        
        foreach ($images as $image) {
            $this->deleteImage($image, $folderPath);
        }
        
    }
    
    
    public function deleteImage($file, $folderPath) {
        
        if ($file['is_image']) {
            // delete all internal thumbnails
            $this->_removeAllInternalImages($file['filename']);
            
            // traverse all dirs in gallery and delete 
            $this->_removeImageFromGallery($file['filename'], $folderPath);
        }
        
    }
    
    
    public function deleteFile($file, $folderPath) {
        
        if ($file['is_image']) {
            // delete all internal thumbnails
            $this->_removeAllInternalImages($file['filename']);
        }
        
        $fileToDelete = $this->mediaManager->getBaseDir() . '/' . $folderPath . '/' . $file['filename'];
        
        if (is_file($fileToDelete)) {
            unlink($fileToDelete);
        }
    }

    
    private function _parseMode($mode) {
        
        $params = NULL;
        
        if (preg_match('#([0-9]+)x([0-9]+)\-(.+)#', $mode, $matches)) {
            $params = array(
                        'width'     =>  $matches[1],
                        'height'    =>  $matches[2],
                        'method'    => strtoupper($matches[3])
            );
        }
        
        return $params;
    }
    
    public function parseModes($mode) {
        return explode('|', $mode);
    }
    
    public function loadFileFromFiles($folderPath, $file, $mode = NULL) {
        
        $paths = array();
        $dirPaths = array();
        
        if ($file['is_image']) {
            // process image
            
            // fill original
            $originalFilePath = $this->mediaManager->getBaseDir() . '/' . $folderPath . '/' . $file['filename'];
            
            $paths[] = $this->mediaManager->getBasePath() . '/' . $folderPath . '/' . $file['filename'];
            $dirPaths[] = $this->mediaManager->getBaseDir() . '/' . $folderPath . '/' . $file['filename'];
            
            if ($mode !== NULL) {
                $modes = $this->parseModes($mode);
                foreach ($modes as $mode) {

                    $modchunkFilename = $this->_getModchunkFilename($file['filename'], $mode);

                    $filePath = $this->mediaManager->getTempDir() . '/' . $modchunkFilename;

                    if (!is_file($filePath)) {
                        $params = $this->_parseMode($mode);
                        $_image = Image::fromFile($originalFilePath);
                        $this->createThumbnail($_image, $originalFilePath, $params);
                        $_image->save($filePath);

                    }
                    $paths[] = $this->mediaManager->getTempPath() . '/' . $modchunkFilename;
                    $dirPaths[] = $this->mediaManager->getTempDir() . '/' . $modchunkFilename;
                }
            }
            
        } else {
            // process file
            $paths[] = $this->mediaManager->getBasePath() . '/' . $folderPath . '/' . $file['filename'];
            $dirPaths[] = $this->mediaManager->getBaseDir() . '/' . $folderPath . '/' . $file['filename'];
        }
        
        return array(
                    'urls'     =>  $paths,
                    'dirPaths'  =>  $dirPaths
        );
        
    }
    
    public function loadFileFromGalleries($folderPath, $file, $mode = NULL) {
        
        $paths = array();
        $dirPaths = array();
        
        $originalFilePath = $this->mediaManager->getBaseDir() . '/' . $folderPath . '/originals/' . $file['filename'];        
        
        $paths[] = $this->mediaManager->getBasePath() . '/' . $folderPath . '/originals/' . $file['filename'];
        $dirPaths[] = $this->mediaManager->getBaseDir() . '/' . $folderPath . '/originals/' . $file['filename'];
        
        
        if ($mode !== NULL) {
            $modes = $this->parseModes($mode);
            foreach ($modes as $mode) {

                $modchunkFilename = $this->_getModchunkFilename($file['filename'], $mode);

                $filePath = $this->mediaManager->getTempDir() . '/' . $modchunkFilename;

                if (!is_file($filePath)) {
                    $params = $this->_parseMode($mode);
                    $_image = Image::fromFile($originalFilePath);
                    $this->createThumbnail($_image, $originalFilePath, $params);
                    $_image->save($filePath);

                }
                $paths[] = $this->mediaManager->getTempPath() . '/' . $modchunkFilename;
                $dirPaths[] = $this->mediaManager->getTempDir() . '/' . $modchunkFilename;
            }
        }
        
        return array(
                    'urls'     =>  $paths,
                    'dirPaths'  =>  $dirPaths
        );
        
    }

    
    public function loadImages($folderBasePath, $images, $mode) {
        
        $modes = $this->parseModes($mode);
        
        foreach ($modes as $_mode) {
            $thumbDir = $this->mediaManager->getBaseDir() . '/' . $folderBasePath . '/' . $_mode;
            $this->createFolder($folderBasePath . '/' . $_mode);

            foreach ($images as $image) {

                $filepath = $thumbDir . '/' . $image['filename'];

                if (!is_file($filepath)) {
                    // try to create thumbnail
                    $originalImagePath = $this->mediaManager->getBaseDir() . '/' . $folderBasePath . '/originals/' . $image['filename'];

                    $params = $this->_parseMode($_mode);

                    $_image = Image::fromFile($originalImagePath);
                    $this->createThumbnail($_image, $originalImagePath, $params);

                    $_image->save($filepath);
                    
                }
            }
        }
        return TRUE;
        
    }
    
    public function deleteTinyPlaceholder($galleryId, $placeholderPattern) {
        $placeholderFilepath = $this->mediaManager->getTempDir() . '/' . sprintf($placeholderPattern, $galleryId);
        if (is_file($placeholderFilepath)) {
            @unlink($placeholderFilepath);
        } 
    }
    
    
    /**
     * Moves file to new folder
     * 
     * @param type $sourceFileId
     * @param type $destinationFolderId
     */
    public function moveFile($sourceFileId, $destinationFolderId) {
       $success = FALSE;

       $destFolderDir = $this->mediaManager->getBaseDir() . '/' . $this->mediaManager->getFolderDir($destinationFolderId);
       
       $sourceFile = $this->mediaManager->getFile($sourceFileId);
       $sourceFolderDir = $this->mediaManager->getBaseDir() . '/' . $this->mediaManager->getFolderDir($sourceFile['folder_id']);

       $sourceFilename = $sourceFolderDir . '/' . $sourceFile['filename'];
       $destFilename = $destFolderDir . '/' . $sourceFile['filename'];
       
       if (is_file($sourceFilename) && is_dir($destFolderDir)) {
           $success = rename($sourceFilename, $destFilename);
       }
       
       return $success;
    }
    
    public function moveGallery($sourceGalleryId, $destinationFolderId) {
    
        $targetDir = $returnDir = $this->mediaManager->getFolderDir($destinationFolderId, 'galleries');
        
        $galleryDir = $this->mediaManager->getGalleryDir($sourceGalleryId);
        $sourceFolderDir = $this->mediaManager->getBaseDir() . '/' . $galleryDir;

        //dump($sourceFolderDir);
        if (preg_match('#.*\/([[:alnum:]\-]+_[0-9]+)#', $sourceFolderDir, $matches)) {

//            dump($matches);
//            die();
            $destFolderChunks = array(
                                $this->mediaManager->getBaseDir(),
                                $targetDir,
                                $matches[1]
            );

            $destFolderDir = implode('/', $destFolderChunks);

//            dump($sourceFolderDir, $destFolderDir);
//            die();
            
            $returnDir .=  '/'.$matches[1];
            
            $this->recurseMove($sourceFolderDir, $destFolderDir);
            $this->deleteFolder($galleryDir);
        }
        
        return $returnDir;
    }
    
    
    public function recurseMove($src,$dst) { 
        $dir = opendir($src); 
        @mkdir($dst); 
        while (false !== ( $file = readdir($dir))) { 
            if (( $file != '.' ) && ( $file != '..' )) { 
                if ( is_dir($src . '/' . $file) ) { 
                    $this->recurseMove($src . '/' . $file,$dst . '/' . $file); 
                } 
                else { 
                    rename($src . '/' . $file,$dst . '/' . $file); 
                } 
            } 
        } 
        closedir($dir); 
    }
    
}