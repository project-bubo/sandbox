<?php

namespace Bubo\Commanding;

use Nette;

/**
 * Command factory
 * 
 * @author Marek Juras
 */
abstract class Command extends Nette\Object implements ICommand {
        
    private $commander;
    
    private $pageManager;
    
    public function __construct() {
        $this->commander = new PageCommander();
    }
    
    public function setPageManager($pageManager) {
        $this->pageManager = $pageManager;
        return $this;
    }
    
    public function getPageManager() {
        return $this->pageManager;
    }
    
    public function getTraverser() {
        return NULL;
    }

    public function setUpCommander($commander) {
        return $commander;
    }
    
    
    public function execute() {
        
        $this->getTraverser()
                    ->setCommander($this->setUpCommander($this->commander))
                    ->traverse();
        
    }
    
}