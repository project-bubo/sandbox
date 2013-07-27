<?php

namespace Bubo\Media\Components\Items\File\Details;

use Bubo;

class AbstractFileDetail extends Bubo\Components\RegisteredControl {   
    
    public function getId() {
        $file = $content = $this->lookup('Bubo\\Media\\Components\\Items\\File');
        return $file->id;
    }
    
}
