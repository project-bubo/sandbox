<?php

namespace FrontModule\SandboxModule\Components\PageMenus;

use Nette\Utils\Html,
    Netstars;

class BottomMenu extends Netstars\Navigation\PageMenu {
    
    public function __construct($parent, $name, $lang) {
        parent::__construct($parent, $name, $lang);
        $this->setLabelName('Spodní menu');
    }
    
    public function setUpRenderer($renderer) {
        $renderer->onRenderMenuItem = callback($this, 'renderMenuItem');
        
//        $newWrappers = array(
//                        'innerLevel'      =>  'div',
//                        'innerLevelItem'  =>  'span'
//        );
        
//        $renderer->setWrappers($newWrappers);
        
        $renderer->getTopLevelPrototype()->class = 'footer-menu fleft';
        //$renderer->getInnerLevelPrototype()->class = 'submenu';
        
        return $renderer;
    }
    
    // return configured traverser
    public function getTraverser() {
        $traverser = $this->createLabelTraverser();
        return $traverser;
    }
    
     
    public function renderMenuItem($page, $acceptedStates, $menuItemContainer, $level, $horizontalLevel, $highlight) {
        
        if ($highlight) {
            $menuItemContainer->class[] = 'active';
        }
        
        $menuItemContainer->add(Html::el('a')
                            ->href($this->presenter->link('Default:default', array('lang' => $page->getUrlLang(), 'url' => $page->getUrl())))
                            ->setText($page->_menu_title)
                            );
        
        if ($horizontalLevel == 3) {
            $menuItemContainer->class[] = 'newsletter-button';
        }
        
        return $menuItemContainer;
    
    }
    
}