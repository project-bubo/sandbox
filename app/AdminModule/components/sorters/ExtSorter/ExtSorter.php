<?php

namespace AdminModule\Sorters;

class ExtSorter extends GeneralSorter {   
    
    
    public function saveSortorder($items) {
        $this->presenter->extModel->saveExtSorting($this->presenter->labelId, $items);
    }
    
    public function getItemList($parentId) {
        $sortedExtensions = $this->presenter->pageManagerService->loadSortedLabelExtensions($this->presenter->labelId, 'page');

        $output = array();
        foreach ($sortedExtensions as $name => $title) {
            $output[$name] = array(
                                'title' => $title
                                );
        }
        
        return $output;
        
    }
        
    
}
