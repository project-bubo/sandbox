<?php

namespace FrontModule\SandboxModule\Components\PageMenus;

use Nette\Utils\Html,
    Bubo;

class FreeStuffMenu extends Bubo\Navigation\PageMenu {
    
    public function __construct($parent, $name, $lang) {
        parent::__construct($parent, $name, $lang);
        $this->setLabelName('Volné pozice')->disableCaching();
       
    }
    
    public function setUpRenderer($renderer) {
        $renderer->onRenderMenuItem = callback($this, 'renderMenuItem');
        
//        $newWrappers = array(
//                        'topLevel'      =>  'div',
//                        'topLevelItem'  =>  'div',
//                        'innerLevel'      =>  'div',
//                        'innerLevelItem'      =>  'div'
//        );
//        
//        $renderer->setWrappers($newWrappers);
        
//        $renderer->getTopLevelPrototype()->class = 'news-box-row';
//        $renderer->getTopLevelItemPrototype()->class = 'news-box fleft';
        
        return $renderer;
    }
    
    // return configured traverser
    public function getTraverser() {
        $traverser = $this->createLabelTraverser();
        return $traverser
                    ->setEntity('scrap')
                    ->skipFirst();
    }
    
    
    public function decorate($html) {
        $div = Html::el('div');
        $div->class('career-free-positions fleft');
        
        $div->create('h2', 'Volné pozice');
        $div->add($html);
        
        return $html->getHtml() ? $div : '';
    }
     
    public function renderMenuItem($page, $acceptedStates, $menuItemContainer, $level, $horizontalLevel, $highlight) {

        if ($highlight) {
            $menuItemContainer->class .= 'active';
        }

        $template = $this->createNewTemplate(__DIR__ . '/templates/menuItem.latte');
        
        $template->page = $page;
        
        
        $menuItemContainer->add(Html::el()
                            ->setHtml($template->__toString())
                            );
        
        return $menuItemContainer;
    
    }
    
}