<?php

namespace FrontModule\SandboxModule\Components\PageMenus;

use Nette\Utils\Html,
    Netstars;

class RegionMenu extends Netstars\Navigation\PageMenu {
    
    public function __construct($parent, $name, $lang) {
        parent::__construct($parent, $name, $lang);
        $this->setLabelName('Obchody');
    }
    
    public function setUpRenderer($renderer) {
        $renderer->onRenderMenuItem = callback($this, 'renderMenuItem');
        
        $newWrappers = array(
                        'topLevel'      =>  'div',
                        'topLevelItem'  =>  ''
                        
        );
        
        $renderer->setWrappers($newWrappers);
        
        $renderer->getTopLevelPrototype()->class = 'shop-map-address';
        //$renderer->getTopLevelItemPrototype()->class = 'news-box fleft';
        
        return $renderer;
    }
    
    public function getTraverser() {
        //$label = $this->presenter->getModelLabel()->getLabelByName($this->labelName);
        
        $label = $this->presenter->pageManagerService->getLabelByName($this->labelName);
        
        if (!$label) {
            return NULL;
        }
        
        $labelRoots = $this->presenter->pageManagerService->getLabelRoots($label['label_id'], $this->lang);
        $traverser = new \FrontModule\Components\Traversers\ShopTraverser;
        
        return $traverser
                    ->setRoots($labelRoots)
                    ->setAcceptedStates(array('published'))
                    ->setLabel($this->labelName, $label)
                    ->setLang($this->lang)
                    ->setSortingCallback(array($this, 'sort'))
                    ->skipFirst();
                    //->highlight($this);
    }
    
     
    private function _getRegionIndex($regionName) {
        $labelExtensionsProperties = $this->presenter->configLoaderService->loadLabelExtentsionProperties();
        $regionData = array_keys($labelExtensionsProperties['properties']['select']['data']);
        
        $index = array_search($regionName, $regionData);
        return $index === FALSE ? 0 : $index;
    }
    
    public function sort($page1, $page2) {

        $regionIndex1 = $page1->_ext_region !== NULL ? $this->_getRegionIndex($page1->_ext_region['key']) : 0;
        $regionIndex2 = $page2->_ext_region !== NULL ? $this->_getRegionIndex($page2->_ext_region['key']) : 0;
  
        if ($regionIndex1 < $regionIndex2) return -1;
        if ($regionIndex1 > $regionIndex2) return 1;
        
        if ($page1->_sortorder < $page2->_sortorder) return -1;
        if ($page1->_sortorder > $page2->_sortorder) return 1;
        
        return 0;
        
    }
    
    public function renderMenuItem($page, $acceptedStates, $menuItemContainer, $level, $horizontalLevel, $highlight, $firstInRegion) {

//        dump($page->_ext_region);
//        die();
        
        $template = $this->createNewTemplate(__DIR__ . '/templates/menuItem.latte');
                
        $template->page = $page;
        $template->firstInRegion = $firstInRegion;
        
        $menuItemContainer->add(Html::el()
                            ->setHtml($template->__toString())
                            );
        
        return $menuItemContainer;
    
    }
    
}