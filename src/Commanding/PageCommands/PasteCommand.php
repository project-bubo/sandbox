<?php

namespace Bubo\Commanding\PageCommands;

use Bubo;

/**
 * Paste command
 * 
 * @author Marek Juras
 */
final class PasteCommand extends Bubo\Commanding\Command {
    
    private $lg;
    private $clipboard;
    
    public function __construct($lg, $clipboard) {
        parent::__construct();
        $this->lg = $lg;
        $this->clipboard = $clipboard;
    }
    
    public function setUpCommander($commander) {
        $commander->onPerformCommand = callback($this, 'paste');
        return $commander;
    }
    
    public function getTraverser() {
        
        $firstItem = $this->clipboard->getFirstItem();
        
        $v = array_values($firstItem['data']);
        
        $pages = $this->getPageManager()->getPages($v);
        
        $traverser = new Bubo\Traversing\CommandingTraverser();
        return $traverser
                    ->setRoots($pages)
                    ->setAcceptedStates(array('draft', 'published'))
                    ->setTraverseOnlyClipboardedPages(TRUE);
        
    }

    public function paste($page, $level, $data) {
        if ($level == 1) {
            $lgIndex = $page->presenter->context->pageManager->getLgIndex();
            $langCodeIndex = $page->presenter->context->pageManager->getLanguageCodeIndex();
            
            $treeNodeId = $lgIndex[$this->lg][$page->getProperty('language_id')];

            
            $page->relocate($treeNodeId);
        }
        
        $page->move();
    }


     public function execute() {
        parent::execute();
        $this->clipboard->clean();
    }
}