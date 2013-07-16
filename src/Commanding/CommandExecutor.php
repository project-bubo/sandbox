<?php

namespace Bubo\Commanding;

use Nette;

/**
 * Command factory
 * 
 * @author Marek Juras
 */
final class CommandExecutor extends Nette\Application\UI\Control {
    
    private $pageManager;
    
    public function __construct($pageManager) {
        $this->pageManager = $pageManager;
    }
    
    public function __call($name, $args) {
        if (preg_match('#([[:alnum:]]+)Command#', $name, $matches)) {
            $commandClassName = 'Bubo\\Commanding\\PageCommands\\'.ucfirst($matches[0]);
            
            if (class_exists($commandClassName)) {
                $reflector = new \ReflectionClass($commandClassName);
                $command = $reflector->newInstanceArgs($args);
                return $command->setPageManager($this->pageManager)->execute();
            }
        } 
        parent::__call($name, $args);
    }
    
}