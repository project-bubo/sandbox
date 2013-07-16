<?php

namespace FrontModule\SandboxModule\Components\PageMenus;

use Nette\Utils\Html,
    Netstars;

class ImageMaxPragaClubMenu extends Netstars\Navigation\PageMenu {
    
    public function __construct($parent, $name, $lang) {
        parent::__construct($parent, $name, $lang);
        $this->setLabelName('MaxPraga klub');
    }
    
    public function setUpRenderer($renderer) {
        $renderer->onRenderMenuItem = callback($this, 'renderMenuItem');
        
//        $newWrappers = array(
//                        'topLevel'      =>  NULL,
//                        'topLevelItem'  =>  NULL,
//                        'innerLevel'      =>  NULL,
//                        'innerLevelItem'      =>  NULL
//        );
//        
//        $renderer->setWrappers($newWrappers);
        
        $renderer->getTopLevelPrototype()->class = 'crossroad';
//        $renderer->getTopLevelItemPrototype()->class = 'news-box fleft';
        
        return $renderer;
    }
    
    // return configured traverser
    public function getTraverser() {
        $traverser = $this->createLabelTraverser();
        return $traverser;
    }
    
     
    public function renderMenuItem($page, $acceptedStates, $menuItemContainer, $level, $horizontalLevel, $highlight) {

        if ($highlight) {
            $menuItemContainer->class .= 'active';
        }
        
        $menuItemContainer->class[] = 'featured';
        $menuItemContainer->class[] = 'maxpragaclub-'.$horizontalLevel;
        
        $template = $this->createNewTemplate(__DIR__ . '/templates/menuItem.latte');
        
        $template->page = $page;
        
        $menuItemContainer->add(Html::el()
                            ->setHtml($template->__toString())
                            );
        
        return $menuItemContainer;
    
    }
    
}