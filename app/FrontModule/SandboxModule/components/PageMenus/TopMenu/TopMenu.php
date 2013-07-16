<?php

namespace FrontModule\SandboxModule\Components\PageMenus;

use Netstars;

class TopMenu extends Netstars\Navigation\PageMenu {
    
    public function __construct($parent, $name, $lang) {
        parent::__construct($parent, $name, $lang);
        $this->setLabelName('Horní menu');
    }
    
    public function setUpRenderer($renderer) {
        
//        $newWrappers = array(
//                        'innerLevel'      =>  'div',
//                        'innerLevelItem'  =>  'span'
//        );
        
//        $renderer->setWrappers($newWrappers);
        
        $renderer->getTopLevelPrototype()->class = 'main-menu fright';
        $renderer->getInnerLevelPrototype()->class = 'submenu';
        
        return $renderer;
    }
    
    // return configured traverser
    public function getTraverser() {
        $traverser = $this->createLabelTraverser();
        return $traverser;
    }

    
}