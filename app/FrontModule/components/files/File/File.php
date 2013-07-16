<?php

namespace FrontModule\Components\Files;

use Bubo;

class File extends Bubo\Components\RegisteredControl {   

    public function render($fileId = NULL, $getAs = NULL) {
        //$this->setTemplateFile();
  
   
        //echo "";
        if ($fileId !== NULL) {



            $path = $this->presenter->virtualDriveService->getFilePath($fileId);

            if ($path) {
                if ($getAs == 'src') {
                    echo $this->getBasePath().$path;
                } else if ($getAs == 'path') {
                    echo WWW_DIR.$path;
                } else {
                    $img = \Nette\Utils\Html::el('img')
                                        ->src($this->getBasePath().$path);

                    echo $img;
                }
            }
        }

        
    }        

    
}
