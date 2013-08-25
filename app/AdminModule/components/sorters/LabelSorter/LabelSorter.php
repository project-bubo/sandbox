<?php

namespace BuboApp\AdminModule\Sorters;

class LabelSorter extends GeneralSorter {   
    
    
    public function saveSortorder($items) {
        $this->presenter->labelModel->savePageSorting($this->presenter->labelId, $this->parentId ?: 0, $items);
    }
    
    public function getItemList($parentId) {

        $parentId = $parentId ?: 0;
            
        $lang = $this->presenter['structureManager']->getLanguage();
        $states = array('published', 'draft');
        
        
        $desc = NULL;
        if ($parentId == 0) {
            $desc = $this->presenter->pageManagerService->getLabelRoots($this->presenter->labelId, $lang, $states);
        } else {
            $getPageParams = array(
                        'treeNodeId'            =>  $parentId,
                        'lang'                  =>  $lang,
                        'labelId'               =>  $this->presenter->labelId,
                        'searchGhosts'          =>  TRUE

            );
            
            $p = $this->presenter->pageManagerService->getPage($getPageParams);
            
            $descParams = array(
                            'labelId'       =>  $this->presenter->labelId,
                            'lang'          =>  $lang,
                            'states'        =>  $states,
                            'searchGhosts'  =>  TRUE
            );
            
            if (!empty($p)) {
                $desc = $p->getDescendants($descParams);
            }
            
        }
        
        $output = array();
        
        if (!empty($desc)) {
            
            foreach ($desc as $page) {
                
                $output[$page->_tree_node_id] = array(
                                        'title' => $page->_name,
                                        'link'  =>  $this->link('showDescendats!', array('parentId' => $page->_tree_node_id))
                                        );   
            }
            
        }
        
        return $output;
        
    }
        
    
}
