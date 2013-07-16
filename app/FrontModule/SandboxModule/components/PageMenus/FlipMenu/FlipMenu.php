<?php

namespace FrontModule\SandboxModule\Components\PageMenus;

use Nette\Utils\Html,
    Netstars;

class FlipMenu extends Netstars\Navigation\PageMenu {
    
    public function __construct($parent, $name, $lang) {
        parent::__construct($parent, $name, $lang);
        $this->setLabelName('Flipchart')->disableCaching();
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
        
        //$renderer->getTopLevelPrototype()->class = 'news-box-row';
        //$renderer->getTopLevelItemPrototype()->class = 'news-box fleft';
        
        return $renderer;
    }
    
    public function customFilter($page) {
        
        $date = $page->_ext_flip_publish_from;
        
        if ($date) {
            $d = \DateTime::createFromFormat('d.m.Y', $date);
            if ($d) {
                $t = mktime(0,0,0,$d->format('n'),$d->format('j'),$d->format('Y'));
                return $t < time();
            }
        }
        
        return TRUE;
    }
    
    
    // return configured traverser
    public function getTraverser() {
        $traverser = $this->createLabelTraverser();
        return $traverser
                    ->setEntity('flippage')
                    ->setFilterCallback(callback($this, 'customFilter'))
                    ->skipFirst();
    }
    
     
    public function renderMenuItem($page, $acceptedStates, $menuItemContainer, $level, $horizontalLevel, $highlight) {

        if ($highlight) {
            $menuItemContainer->class .= 'active';
        }

        $flipType = $page->_ext_flip_type;
        
        $templateName = isset($flipType['key']) ? $flipType['key'] : 'v1';
        
        //print_r($templateName);
        
        $template = $this->createNewTemplate(__DIR__ . '/templates/'.$templateName.'.latte');        
        $template->page = $page;
        
//        if ($horizontalLevel == 3) {
//            $menuItemContainer->class .= ' last';
//        }
        
        $menuItemContainer->add(Html::el()
                            ->setHtml($template->__toString())
                            );
        
        return $menuItemContainer;
    
    }
    
}