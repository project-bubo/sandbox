<?php

namespace Bubo\Commanding\PageCommands;

use Bubo;

/**
 * Copy command
 * 
 * @author Marek Juras
 */
final class CopyCommand extends Bubo\Commanding\Command {
    
    private $treeNodeId;
    private $isSinglePage;
    
    private $clipboard;
    
    public function __construct($treeNodeId, $isSinglePage, $clipboard) {
        parent::__construct();
        $this->treeNodeId = $treeNodeId;
        $this->isSinglePage = $isSinglePage;
        
        $this->clipboard = $clipboard;
    }
    
    public function setUpCommander($commander) {
        $commander->onPerformCommand = callback($this, 'copy');
        return $commander;
    }
    
    public function getTraverser() {
        $traverser = new Bubo\Traversing\CommandingTraverser();
        return $traverser
                    ->setRoots($this->getPageManager()->getPage($this->treeNodeId))
                    ->setAcceptedStates(array('draft', 'published'))
                    ->setSinglePage($this->isSinglePage);
        
    }

    /**
     * Vloží do clipboardu celou skupinu stránek.
     * 
     * @param type $page
     * @param type $level
     * @param type $data 
     */
    public function copy($page, $level, $data) {
        $page->copy(FALSE);
    }
    
    public function execute() {
        $this->clipboard->clean();
        parent::execute();
    }

}