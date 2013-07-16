<?php

namespace AdminModule\Components;

class PageSorter extends \Components\Core\RegisteredControl {   
    
    public function createComponentPageSortTraverser($name) {
        return new PageSorter\Components\PageSortTraverser($this, $name);
    }
    
    public function render() {
        
        $template = parent::initTemplate(dirname(__FILE__) . '/templates/default.latte');
        
        $template->render();
        
    }

    
}
