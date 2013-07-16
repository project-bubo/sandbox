<?php

namespace Bubo\Commanding\PageCommands;

use Bubo;

/**
 * Toggle passive label command
 * 
 * @author Marek Juras
 */
final class TogglePassiveLabelAtPageCommand extends Bubo\Commanding\Command {
    
    private $treeNodeId;
    private $label;
    private $assign;
    
    
    public function __construct($treeNodeId, $label, $assign) {
        parent::__construct();
        $this->treeNodeId = $treeNodeId;
        $this->label = $label;
        $this->assign = $assign;
    }
    
    public function setUpCommander($commander) {
        $commander->onPerformCommand = callback($this, 'label');
        return $commander;
    }
    
    public function getTraverser() {
        $traverser = new Traversing\CommandingTraverser();
        
        
        $pm = $this->getPageManager();
        $_page = $this->getPageManager()->getPage($this->treeNodeId);
        
        
        $lgIndex = $pm->getLgIndex();
        $langCodeIndex = $pm->getLanguageCodeIndex();
        
        $page = $pm->getPage($lgIndex[$_page->getProperty('lg')][$langCodeIndex[DEFAULT_LANGUAGE]['language_id']]);
        
//        dump(array_keys($page->getAllParents()));
//        dump($this->getPageManager()->getLabelRoots($this->label['label_id']));

        
        $parents = (array) array_keys($page->getAllParents());
        $labelRoots = (array)$this->getPageManager()->getLabelRoots($this->label['label_id']);
        $labelRootTreeNodeId = array_intersect($parents, $labelRoots);

//        var_dump($parents);
//        var_dump($labelRoots);
//        var_dump($labelRootTreeNodeId);
        
        
        $temp = array_values($labelRootTreeNodeId);

        $key = 1;
        $singlePage = TRUE;
        
        if (isset($temp[0])) {
            $labelRootTreeNodeId = $temp[0];
            $key = array_search($labelRootTreeNodeId, $parents) + 2;
            $singlePage = FALSE;
        } 

        return $traverser
                    ->setRoots($page)
                    ->setAcceptedStates(array('draft', 'published'))
                    ->setLevel($key)
                    ->setSinglePage(FALSE);
      
    }

    public function label($page, $level, $data) {
        
        $depthOfRecursion = $this->label['depth_of_recursion'];
        $maxLevelOfRecursion = $this->label['depth_of_recursion'] + 1;

        if ($depthOfRecursion === NULL)
            return NULL;
        if ((($depthOfRecursion == 0) || ($level <= $maxLevelOfRecursion))) {
            if ($this->assign) {
                 if ($this->label['is_global']) {
                    $pm = $page->presenter->context->pageManager;
                    $pages = $pm->getPageGroup($page->getProperty('lg'));
                    
                    foreach ($pages as $page) {
                        
                        $page->assignPassiveLabel($this->label['label_id']);
                    }
                } else {
                    $page->assignPassiveLabel($this->label['label_id']);
                }
                
                
                
            } else {
                
                
                 if ($this->label['is_global']) {
                    $pm = $page->presenter->context->pageManager;
                    $pages = $pm->getPageGroup($page->getProperty('lg'));
                    foreach ($pages as $page) {
                        $page->removePassiveLabel($this->label['label_id']);
                    }
                } else {
                    $page->removePassiveLabel($this->label['label_id']);
                }

                
            }
                
        }
        
    }

}