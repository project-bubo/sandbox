<?php

namespace Bubo\Commanding\PageCommands;

use Bubo;

/**
 * Graphviz
 * 
 * @author Marek Juras
 */
final class GraphVizCommand extends Bubo\Commanding\Command {
    
    private $treeNodeId;
    
    public function __construct($treeNodeId) {
        parent::__construct();
        $this->treeNodeId = $treeNodeId;
    }
    
    public function setUpCommander($commander) {
        $commander->onPerformCommand = callback($this, 'graphViz');
        return $commander;
    }
    
    public function getTraverser() {
        $traverser = new Bubo\Traversing\CommandingTraverser();
        return $traverser
                    ->setRoots($this->getPageManager()->getPage($this->treeNodeId))
                    ->setAcceptedStates(NULL);
        
    }

    public function graphViz($page, $level, $data) {
        $page->graphViz();
    }


}