<?php

namespace Bubo\Components;

use Bubo;

class ContextMenuControl extends Bubo\Components\RegisteredControl {

    /**
     * Id obaloveho relativniho divu
     * @var type 
     */
    private $contextMenuListenerId;

    
    public function __construct($parent, $name) {
        parent::__construct($parent, $name);
        $this->contextMenuListenerId = \Nette\Utils\Strings::webalize($name).'-context-menu-listener';
    }

    public function getContextMenuListenerId() {
        return $this->contextMenuListenerId;
    }
    
    /**
     * Factory method for all context menus
     * 
     * @param type $name
     * @return classname 
     */
    public function createComponent($name) {
        
        if (preg_match('#([a-zA-Z0-9]+ContextMenu)_([^_]+)_([^_]+)#', $name, $matches)) {
     
            $className = $matches[1];
            $menuSelector = $matches[2];
            $snippetName = $matches[3];
            
       
            $classname = "ContextMenu\\" . ucfirst($className);
            if (class_exists($classname)) {
                
                $contextMenu = new $classname($this, $name, $menuSelector, $snippetName);
                return $contextMenu;
            }
        } else {
            return parent::createComponent($name);
        }
    }
    
    
    public function handleShowContextMenuSignal() {
        
        
        // retrieve context menu attributes
        $contextMenuClassName = $this->presenter->getParam('contextMenuClassName');
        $contextMenuSelector = $this->presenter->getParam('contextMenuSelector');
        $contextMenuSnippetName = $this->presenter->getParam('contextMenuSnippetName');
        $contextMenuX = $this->presenter->getParam('contextMenuX');
        $contextMenuY = $this->presenter->getParam('contextMenuY');
        $contextMenuParams = $this->presenter->getParam('contextMenuParams');
        
        // create componentName
        $contextMenuComponentName = $contextMenuClassName.'_'.$contextMenuSelector.'_'.$contextMenuSnippetName;
        $this->getComponent($contextMenuComponentName)->setPosition($contextMenuX, $contextMenuY)
                                                                ->setParams($contextMenuParams)
                                                                ->show();
        
        
        $this->invalidateControl($contextMenuSnippetName);
        
    }
    
    public function initTemplate($templateFile) {
        $template = parent::initTemplate($templateFile);
        
        $template->contextMenuListenerId = $this->contextMenuListenerId;
        $template->name = $this->name;

        return $template;
    }
    
    
}
