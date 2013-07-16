<?php

namespace Bubo\Commanding\PageCommands;

use Bubo;

/**
 * Copy command
 * 
 * @author Marek Juras
 */
final class ToggleActiveLabelAtPageCommand extends Bubo\Commanding\Command {
    
    private $treeNodeId;
    private $label;
    private $assign;
    private $lang;
    
    public function __construct($treeNodeId, $label, $assign, $lang) {
        parent::__construct();
        $this->treeNodeId = $treeNodeId;
        $this->label = $label;
        $this->assign = $assign;
        $this->lang = $lang;
    }
    
    public function setUpCommander($commander) {
        $commander->onPerformCommand = callback($this, 'label');
        return $commander;
    }
    
    public function getTraverser() {
        $traverser = new Bubo\Traversing\CommandingTraverser();
        
        $params = array('treeNodeId' => $this->treeNodeId, 'lang' => $this->lang, 'searchAllTimeZones' => TRUE);
        
        return $traverser
                    ->setRoots($this->getPageManager()->getPage($params));
      
    }

    /**
     * Oštítkuje danou stránku (nebo skupinu stránek) lokálním / globálním
     * štítkem.
     * 
     * Pokud je štítek rekurzivní, tak podstránky štítkuje pasivně.
     * 
     * @param type $page
     * @param type $level
     * @param type $data
     * @return type 
     */
    public function label($page, $level, $data) {
        
        $depthOfRecursion = $this->label['depth_of_recursion'];
        $maxLevelOfRecursion = $this->label['depth_of_recursion'] + 1;
        
        if ($level == 1) {
            if ($this->assign) {
                $page->assignActiveLabel($this->label['label_id']);
                
//                if ($this->label['is_global']) {
//                    $pm = $page->presenter->context->pageManager;
//                    $pages = $pm->getPageGroup($page->getProperty('lg'));
//                    foreach ($pages as $page) {
//                        $page->assignActiveLabel($this->label['label_id']);
//                    }
//                } else {
//                    $page->assignActiveLabel($this->label['label_id']);
//                }
            } else {
                $page->removeActiveLabel($this->label['label_id']);
//                if ($this->label['is_global']) {
//                    $pm = $page->presenter->context->pageManager;
//                    $pages = $pm->getPageGroup($page->getProperty('lg'));
//                    foreach ($pages as $page) {
//                        $page->removeActiveLabel($this->label['label_id']);
//                    }
//                } else {
//                    $page->removeActiveLabel($this->label['label_id']);
//                }
            }
            
        } else {
            if ($depthOfRecursion === NULL)
                return NULL;
            if ((($depthOfRecursion == 0) || ($level <= $maxLevelOfRecursion)))
                if ($this->assign) {
                    $page->assignPassiveLabel($this->label['label_id']);
//                    if ($this->label['is_global']) {
//                        $pm = $page->presenter->context->pageManager;
//                        $pages = $pm->getPageGroup($page->getProperty('lg'));
//                        foreach ($pages as $page) {
//                            $page->assignPassiveLabel($this->label['label_id']);
//                        }
//                    } else {
//                        $page->assignPassiveLabel($this->label['label_id']);
//                    }

                } else {
                    $page->removePassiveLabel($this->label['label_id']);
//                    if ($this->label['is_global']) {
//                        $pm = $page->presenter->context->pageManager;
//                        $pages = $pm->getPageGroup($page->getProperty('lg'));
//                        foreach ($pages as $page) {
//                            $page->removePassiveLabel($this->label['label_id']);
//                        }
//                    } else {
//                        $page->removePassiveLabel($this->label['label_id']);
//                    }

                }
        }
        
        $page->refresh();
    }

}