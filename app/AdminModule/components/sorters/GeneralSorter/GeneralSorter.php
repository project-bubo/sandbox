<?php

namespace AdminModule\Sorters;

use Bubo;

class GeneralSorter extends Bubo\Components\RegisteredControl {   

    /**
     * @persistent
     */
    public $parentId;
    
    
    public function handleShowDescendats($parentId) {
        
    }
    
    public function handleSaveSortorder() {
        $data = $this->presenter->getParam('data');
        parse_str($data);
        // order is in $items
        
        $this->saveSortorder($items);

    }
    
    
    public function render() {
        $template = $this->createNewTemplate(__DIR__.'/templates/default.latte');
        $template->itemList = $this->getItemList($this->parentId);
        echo $template;
    }
        
    
}
