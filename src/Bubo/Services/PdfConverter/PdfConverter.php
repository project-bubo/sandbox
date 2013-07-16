<?php

/**
 * @author     Marek Juras
 */

namespace Bubo\Services;

class PdfConverter extends BaseService {
    
    private $context;
    
    private $presenter;
    
    public function __construct($context) {
        $this->context = $context;
    }

    public function isPresenterSet() {
        return $this->presenter !== NULL;
    }
    
    public function setPresenter($presenter) {
        $this->presenter = $presenter;
    }
    

    /**
     * 
     * 
     * 
     * Data comes in following format
     * 
     * array(
     *         <fileId>  =>  <pdfFilePath>
     *      );
     * 
     * @param type $data
     * @return int
     */
    public function createThumbnails($data){
        
        return NULL;
        if (!extension_loaded('imagick')) {
        }
        
        
        //die();
        $imagick = new \Imagick();
        
        
        
        foreach ($data as $fileId => $filePath) {
            
            if (is_file($filePath)) {
                dump($filePath . ' exists');
            }
            
            
        }
        
        
        die();
        
        
        
        
        if(!file_exists($pdfName)) return 0;
        $pdf = new \Imagick();
        $pdf->readImage($pdfName);
        $pages = count($pdf);
        $pages = ($pages)?$pages+1:0;
        foreach($pdf as $index => $image){
                $image->setcolorspace(\Imagick::COLORSPACE_RGB);
                $image->setCompression(\Imagick::COMPRESSION_JPEG);
                $image->setCompressionQuality(90);
                $image->setResolution(600, 600);
                $image->setImageFormat('jpeg');
              //  $image->resizeImage(405, 574, \Imagick::FILTER_LANCZOS, 1 , TRUE);
                $image->writeImage($folder.'/'.($index+1).'.jpg');
        }
        $pdf->destroy();
        $pages = count(glob($folder.'/*.jpg'));
        
        return $pages;
    }
}