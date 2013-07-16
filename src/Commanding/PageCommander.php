<?php

namespace Bubo\Commanding;

use Nette,
    Nette\Application\UI\Control,
    Nette\Utils\Html,
    \Components\PageMenu;

class PageCommander extends Nette\Object {
      
    // callback
    public $onPerformCommand;
    
    public function __construct() {        
        // default callback
        $this->onPerformCommand = callback($this, 'performPageCommand');
    }
    
    // default command
    public function performPageCommand($page, $level) {
        return NULL;
    }
    
    public function performPageCommandCallback() {
        return $this->onPerformCommand->invokeArgs(func_get_args());
    }
    
    
}
