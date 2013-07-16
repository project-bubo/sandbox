<?php

namespace FrontModule\SandboxModule\Components\PageMenus;

use Nette\Utils\Html,
    Bubo;

class TabMenu extends Bubo\Navigation\PageMenu {
    
    public function __construct($parent, $name, $lang) {
        parent::__construct($parent, $name, $lang);
        $this->setLabelName('Detail značky');
    }
    
    public function setUpRenderer($renderer) {
        $renderer->onRenderMenuItem = callback($this, 'renderMenuItem');
        
        $newWrappers = array(
                        'topLevel'      =>  NULL,
                        'topLevelItem'  =>  NULL,
                        'innerLevel'      =>  NULL,
                        'innerLevelItem'      =>  NULL
        );
        
        $renderer->setWrappers($newWrappers);
        
//        $renderer->getTopLevelPrototype()->class = 'news-box-row';
//        $renderer->getTopLevelItemPrototype()->class = 'news-box fleft';
        
        return $renderer;
    }
    
    // return configured traverser
    public function getTraverser() {
        $traverser = $this->createLabelTraverser();
        return $traverser->highlight();
    }
    
     
    public function renderMenuItem($page, $acceptedStates, $menuItemContainer, $level, $horizontalLevel, $highlight) {

//        if ($highlight) {
//            $menuItemContainer->class .= 'active';
//        }

        $template = $this->createNewTemplate(__DIR__ . '/templates/menuItem.latte');
        
        $template->page = $page;
        $template->highlight = $highlight;
        
        $menuItemContainer->add(Html::el()
                            ->setHtml($template->__toString())
                            );
        
        return $menuItemContainer;
    
    }
    
}