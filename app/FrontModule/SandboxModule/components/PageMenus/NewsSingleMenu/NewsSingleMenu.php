<?php

namespace FrontModule\SandboxModule\Components\PageMenus;

use Nette\Utils\Html,
    Netstars;

class NewsSingleMenu extends Netstars\Navigation\PageMenu {
    
    public function __construct($parent, $name, $lang) {
        parent::__construct($parent, $name, $lang);
        $this->setLabelName('Novinky');
    }
    
    public function setUpRenderer($renderer) {
        $renderer->onRenderMenuItem = callback($this, 'renderMenuItem');
        
        $newWrappers = array(
                        'topLevel'      =>  NULL,
                        'topLevelItem'  =>  NULL,
//                        'innerLevel'      =>  'div',
//                        'innerLevelItem'      =>  'div'
        );
        
        $renderer->setWrappers($newWrappers);
//        
//        $renderer->getTopLevelPrototype()->class = 'news-box-row';
//        $renderer->getTopLevelItemPrototype()->class = 'news-box fleft';
        
        return $renderer;
    }
    
    
    // return configured traverser
    public function getTraverser() {
        $traverser = $this->createLabelTraverser();
        return $traverser
                    ->setGoThroughActive();
    }
   
     
    public function renderMenuItem($page, $acceptedStates, $menuItemContainer, $level, $horizontalLevel, $highlight) {

        if ($highlight) {
            $menuItemContainer->class .= 'active';
        }

        $template = $this->createNewTemplate(__DIR__ . '/templates/menuItem.latte');
        
        $template->page = $page;

        $menuItemContainer->add(Html::el('a')
                            ->setHtml($template->__toString())
                            );
        
        return $menuItemContainer;
    
    }
    
}