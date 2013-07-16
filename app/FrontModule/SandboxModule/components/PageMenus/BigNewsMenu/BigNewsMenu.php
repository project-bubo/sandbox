<?php

namespace FrontModule\SandboxModule\Components\PageMenus;

use Nette\Utils\Html,
    Netstars;

class BigNewsMenu extends Netstars\Navigation\PageMenu {
    
    public function __construct($parent, $name, $lang) {
        parent::__construct($parent, $name, $lang);
        $this->setLabelName('Novinky');
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
                    ->setSortingCallback(callback($this, 'customSort'))
                    ->skipFirst();
    }
    
     
    public function customSort($page1, $page2) {
        
        $date1 = \DateTime::createFromFormat('d.m.Y', $page1->_ext_news_date);
        $timestamp1 = $date1 ? $date1->getTimestamp() : 0;
        
        
        $date2 = \DateTime::createFromFormat('d.m.Y', $page2->_ext_news_date);
        $timestamp2 = $date2 ? $date2->getTimestamp() : 0;
        
        $sorting =  $timestamp2 - $timestamp1;
        
        return $sorting;
        
        
    }
    
    public function renderMenuItem($page, $acceptedStates, $menuItemContainer, $level, $horizontalLevel, $highlight) {

        if ($highlight) {
            $menuItemContainer->class[] = 'active';
        }
        
        $template = $this->createNewTemplate(__DIR__ . '/templates/menuItem.latte');
        
        $template->page = $page;
        $template->level = $level;
        $template->horizontalLevel = $horizontalLevel;
        
        //$menuItemContainer->class .= ' brand_'.$horizontalLevel;
        
//        dump($page->_ext_perex);
        
        $menuItemContainer->add(Html::el()
                            //->setText($page->_ext_perex)
                            ->setHtml($template->__toString())
                            );
        
        return $menuItemContainer;
    
    }
    
}