<?php

namespace Bubo\ExtEngines;

use Nette;

/**
 * 
 * @author Marek Juras
 */
class BaseExtEngine extends Nette\Object {

    private $page;
    
    public function __construct($page) {
        $this->page = $page;
        
    }
    

    public function getPage() {
        return $this->page;
    }
    
}