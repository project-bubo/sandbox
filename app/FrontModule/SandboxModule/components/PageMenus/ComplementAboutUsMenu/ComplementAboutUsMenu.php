<?php

namespace FrontModule\SandboxModule\Components\PageMenus;

use Nette\Utils\Html,
    Bubo;

class ComplementAboutUsMenu extends Bubo\Navigation\PageMenu {
    
    private $aboutUsLabel;
    
    public function __construct($parent, $name, $lang) {
        parent::__construct($parent, $name, $lang);
        $this->setLabelName('Horní menu');
        $this->addCacheTag('labels/o-nas');
        
        $this->aboutUsLabel = $this->presenter->pageManagerService->getLabelByName('O nás');
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
        
        //$renderer->getTopLevelPrototype()->class = 'crossroad';
//        $renderer->getTopLevelItemPrototype()->class = 'news-box fleft';
        
        return $renderer;
    }

    // return configured traverser
    public function getTraverser() {
        $traverser = $this->createLabelTraverser();
        return $traverser
                    ->setFilterCallback(callback($this, 'customFilter'))
                    ->skipFirst();
    }
    
    public function customFilter($page) {
        
        if ($this->aboutUsLabel) {
            return !$page->isActivelyLabelledBy($this->aboutUsLabel['label_id']);
        }
        
        return TRUE;
        
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