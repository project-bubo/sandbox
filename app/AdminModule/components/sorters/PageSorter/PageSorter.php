<?php

namespace AdminModule\Sorters;

class PageSorter extends GeneralSorter {   
    
    
    public function saveSortorder($items) {
        $this->presenter->pageModel->savePageSorting($items);
        
    }
    
    public function getItemList($parentId) {

        $parentId = $parentId ?: 0;
        
        $desc = $this->presenter->pageManagerService->getDescendantsFromLoadedPages($parentId);
        
        $output = array();
        
        if (!empty($desc)) {
            
            foreach ($desc as $treeNodeId => $page) {
                
                $output[$treeNodeId] = array(
                                        'title' => $page['name'],
                                        'link'  =>  $this->link('showDescendats!', array('parentId' => $treeNodeId))
                                        );   
            }
            
        }
        
        return $output;
        
    }
        
    
}
