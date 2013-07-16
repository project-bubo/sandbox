<?php

namespace Bubo\Commanding\PageCommands;

use Bubo;

/**
 * Cut command
 * 
 * @author Marek Juras
 */
final class CutCommand extends Bubo\Commanding\Command {
    
    private $treeNodeId;
    private $clipboard;
    
    public function __construct($treeNodeId, $clipboard) {
        parent::__construct();
        $this->treeNodeId = $treeNodeId;
        $this->clipboard = $clipboard;
    }
    
    public function setUpCommander($commander) {
        $commander->onPerformCommand = callback($this, 'cut');
        return $commander;
    }
    
    public function getTraverser() {
        $traverser = new Bubo\Traversing\CommandingTraverser();
        return $traverser
                    ->setRoots($this->getPageManager()->getPage($this->treeNodeId))
                    ->setAcceptedStates(array('draft', 'published'));
        
    }

    /**
     * Vloží do clipboardu celou skupinu stránek.
     * 
     * @param type $page
     * @param type $level
     * @param type $data 
     */
    public function cut($page, $level, $data) {
        $page->copy(TRUE);
    }
    
    public function execute() {
        $this->clipboard->clean();
        parent::execute();
    }

}