<?php

namespace Bubo\Commanding\PageCommands;

use Bubo;

/**
 * Copy - Paste command
 * 
 * @author Marek Juras
 */
final class CopyPasteCommand extends Bubo\Commanding\Command {
    
    private $lg;
    private $clipboard;
    
    public function __construct($lg, $clipboard) {
        parent::__construct();
        
        $this->lg = $lg;
        $this->clipboard = $clipboard;
    }
    
    public function setUpCommander($commander) {
        $commander->onPerformCommand = callback($this, 'copyPaste');
        return $commander;
    }
    
    public function getTraverser() {
        
        $firstItem = $this->clipboard->getFirstItem();
        
        $v = array_values($firstItem['data']);
        
        $page = $this->getPageManager()->getPage($v[0]);
        
        $traverser = new Bubo\Traversing\CommandingTraverser();
        return $traverser
                    ->setRoots($page)
                    ->setAcceptedStates(array('draft', 'published'))
                    ->setTraverseOnlyClipboardedPages(TRUE);
        
    }

    public function copyPaste($page, $level, $newParent) {
        
        $parentLg = $this->lg;

        $page = $page->context->pageManager->getPage($page->getProperty('tree_node_id'));
        
        $pm = $page->presenter->context->pageManager;
        $lgIndex = $pm->getLgIndex();
        $langCodeIndex = $pm->getLanguageCodeIndex();
        
        if ($level != 1) {        
//            $parentPage = $pm->getPage($newParent);
//            $parentLg = $parentPage->getProperty('lg');
            
            $parentLg = $newParent;
        }
        
        $langGroup = $pm->getPages($lgIndex[$page->getProperty('lg')]);
        
        // langGroup must be connected to parentGroup
        
//        dump($parentGroup);
//        dump($langGroup);
        
        
        // assumed that page in default language is first in $langGroup
        $lg = NULL;
        foreach ($langGroup as $langPage) {
            
            
            $parentTreeNodeId = $lgIndex[$parentLg][$langPage->getProperty('language_id')];
            //echo "chci navazat ".$langPage->getProperty('tree_node_id').' na '.$parentTreeNodeId.'<br />';
            
            
//            if (isset($langPage->presenter)) {
//                echo "o";
//            } else {
//                echo "x";
//            }
            
            $lg = $langPage->copyAndMove($parentTreeNodeId, $lg);
            
        }
        

        
        //die();
        // reattach
        $pm->reattachPages($page->presenter);
        
        // perform direct labeling on $lg

        $lgIndex = $pm->getLgIndex();

        $lgPages = $pm->getPages($lgIndex[$lg]);
        
        foreach ($lgPages as $page) {
            $_page = $page->context->pageManager->getPage($page->getProperty('tree_node_id'));
            $_page->presenter->getModelPage()->performDirectPassiveLabeling($_page->presenter, $_page->getProperty('tree_node_id'));
        }
        
        
        
        return $lg;
        
        
        
        
        
        
        
        
        
        
        
//        $newParentTreeNodeId = $newParent;
//        
//        
//        if ($level == 1) {
//            
//            $treeNodeId = $lgIndex[$this->lg][$page->getProperty('language_id')];
//            $newParentTreeNodeId = $treeNodeId;
//        } 
//        
//        $newTreeNodeId = $page->copyAndMove($newParentTreeNodeId);
//        
//        // perform direct passive labeling
//        //$page->presenter->getModelPage()->performDirectPassiveLabeling($page->presenter, $newTreeNodeId);
//        
//        return $newTreeNodeId;
    }


    public function execute() {
        parent::execute();
        $this->clipboard->clean();
    }
}