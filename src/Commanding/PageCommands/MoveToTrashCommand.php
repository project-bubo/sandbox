<?php

namespace Bubo\Commanding\PageCommands;

use Bubo;

/**
 * Move to trash command
 * 
 * @author Marek Juras
 */
final class MoveToTrashCommand extends Bubo\Commanding\Command {
    
    private $treeNodeId;
    
    public function __construct($treeNodeId) {
        parent::__construct();
        $this->treeNodeId = $treeNodeId;
    }
    
    public function setUpCommander($commander) {
        $commander->onPerformCommand = callback($this, 'moveToTrashPage');
        return $commander;
    }
    
    public function getTraverser() {
        $traverser = new Bubo\Traversing\CommandingTraverser();
        
        $pages = $this->getPageManager()->getAllLangPagesByTreeNodeId($this->treeNodeId);
        
        return $traverser
                    ->setRoots($pages)
                    ->setAcceptedStates(array('draft', 'published'));
    }

    public function moveToTrashPage($page, $level, $data) {

        $page->moveToTrash();

    }

}