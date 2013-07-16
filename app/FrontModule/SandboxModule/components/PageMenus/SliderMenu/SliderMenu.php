<?php

namespace FrontModule\SandboxModule\Components\PageMenus;

use Nette\Application\UI\Form,
    Nette\Utils\Html,
    Netstars;

class SliderMenu extends Netstars\Navigation\PageMenu {
    
    public function __construct($parent, $name, $lang) {
        parent::__construct($parent, $name, $lang);
        $this->setLabelName('Naše značky');
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
        
        $renderer->getTopLevelPrototype()->id = 'slideshow';
        $renderer->getTopLevelItemPrototype()->class = 'slide-container';
        
        return $renderer;
    }
    
    // return configured traverser
    public function getTraverser() {
        $traverser = $this->createLabelTraverser();
        return $traverser->skipFirst();
    }
    
     
    public function renderMenuItem($page, $acceptedStates, $menuItemContainer, $level, $horizontalLevel, $highlight) {

        if ($highlight) {
            $menuItemContainer->class[] = 'active';
        }
        
        $template = $this->createNewTemplate(__DIR__ . '/templates/menuItem.latte');
        
        $template->page = $page;
        
        $menuItemContainer->add(Html::el()
                            ->setHtml($template->__toString())
                            );
        
        return $menuItemContainer;
    
    }
    
}