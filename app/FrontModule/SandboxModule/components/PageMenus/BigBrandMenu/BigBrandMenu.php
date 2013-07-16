<?php

namespace FrontModule\SandboxModule\Components\PageMenus;

use Nette\Utils\Html,
    Netstars;

class BigBrandMenu extends Netstars\Navigation\PageMenu {
    
    public function __construct($parent, $name, $lang) {
        parent::__construct($parent, $name, $lang);
        $this->setLabelName('Naše značky');
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
        
//        $renderer->getTopLevelPrototype()->id = 'nav';
//        $renderer->getTopLevelItemPrototype()->class = 'brand_logo';
        
        return $renderer;
    }
    
    // return configured traverser
    public function getTraverser() {
        $traverser = $this->createLabelTraverser();
        return $traverser
                    ->skipFirst();
    }
    
     
    public function renderMenuItem($page, $acceptedStates, $menuItemContainer, $level, $horizontalLevel, $highlight) {

        if ($highlight) {
            $menuItemContainer->class[] = 'active';
        }
        
        $template = $this->createNewTemplate(__DIR__ . '/templates/menuItem.latte');
        
        $template->page = $page;
        //$template->level = $level;
        //$template->horizontalLevel = $horizontalLevel;
        
        //$menuItemContainer->class .= ' brand_'.$horizontalLevel;
        
//        dump($page->_ext_perex);
        
        $menuItemContainer->add(Html::el()
                            //->setText($page->_ext_perex)
                            ->setHtml($template->__toString())
                            );
        
        return $menuItemContainer;
    
    }
    
}