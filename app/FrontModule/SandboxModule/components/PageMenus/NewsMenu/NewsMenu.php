<?php

namespace FrontModule\SandboxModule\Components\PageMenus;

use Nette\Utils\Html,
    Bubo;

class NewsMenu extends Bubo\Navigation\PageMenu {
    
    public function __construct($parent, $name, $lang) {
        parent::__construct($parent, $name, $lang);
        $this->setLabelName('Novinky');
    }
    
    public function setUpRenderer($renderer) {
        $renderer->onRenderMenuItem = callback($this, 'renderMenuItem');
        
        $newWrappers = array(
                        'topLevel'      =>  'div',
                        'topLevelItem'  =>  'div',
                        'innerLevel'      =>  'div',
                        'innerLevelItem'      =>  'div'
        );
        
        $renderer->setWrappers($newWrappers);
        
        $renderer->getTopLevelPrototype()->class = 'news-box-row';
        $renderer->getTopLevelItemPrototype()->class = 'news-box fleft';
        
        return $renderer;
    }
    
    // return configured traverser
    public function getTraverser() {
        $traverser = $this->createLabelTraverser();
        return $traverser
                    ->setSortingCallback(callback($this, 'customSort'))
                    ->limit(3)
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
            $menuItemContainer->class .= 'active';
        }

        $template = $this->createNewTemplate(__DIR__ . '/templates/menuItem.latte');
        
        $template->page = $page;
        
        if ($horizontalLevel == 3) {
            $menuItemContainer->class .= ' last';
        }
        
        $menuItemContainer->add(Html::el()
                            ->setHtml($template->__toString())
                            );
        
        return $menuItemContainer;
    
    }
    
}