<?php

namespace AdminModule\Components\StructureManager\Components;

use Nette\Application\UI\Form,
    Nette\Utils\Html,
    Nette\Environment,
    Bubo;

class AdminPageMenu extends \Bubo\Navigation\PageMenu {

    public function setUpRenderer($renderer) {
        $renderer->getTopLevelPrototype()->class = 'w_menu treeview-white';
        $renderer->getTopLevelPrototype()->id = 'tree';

        // custom onRenderMenuItem callback
        $renderer->onRenderMenuItem = callback($this, 'renderMenuItem');

        return $renderer;
    }

    public function getTraverser() {
//        dump($section->langCode);
//        die();
        
        $traverser = new Bubo\Traversersing\RenderingTraversers\AdminMenuTraverser($this);
        
        // try to configure page loading via entity config
        $entityConfig = $this->presenter->configLoaderService->loadEntityConfig('page');
        
        //dump($this->parent->getLanguage());
        
        
        
        $traverser
            ->setPresenter($this->presenter)
            ->setAcceptedStates(array('draft','published','trashed'))
            ->setLang($this->parent->getLanguage())
            ->searchGhosts()
            ->searchAllTimeZones()
//            ->setFilterCallback(callback($this, 'customFilter'))
            ->highlight();
            //->configureLoading($entityConfig, 'full');
        
        return $traverser;
    }

//    public function customFilter($page) {
//        
//        if ($page->_entity == 'page') {
//            return TRUE;
//        }
//        
//        return FALSE;
//    }
    
    private function _addAdminLabelBullets($menuItemContainer, $labels, $labelIndex, $sessionSection) {
        
        if (!empty($labels)) {
            foreach ($labels as $labelData) {
                $label = $this->presenter->pageManagerService->getLabel($labelData['labelId']);
//                dump($label);
                if (array_key_exists($this->parent->getLanguage(), $label['langs'])) {
                    $el = Html::el('span')
                                    ->class('label-bullet')
                                    ->style('color:#' . $label['color']);
                    
                    if ($label['create_button'] == 1) {
                        $el->setHtml($labelData['active'] ? '&diams;' : '&loz;');
                    } else {
                        $el->setHtml($labelData['active'] ? '&#x25cf;' : '&#9675;');
                    }
                    
                    $menuItemContainer->add($el);
                }
            }
        }
             
        return $menuItemContainer;
    }

    
    private function _addUserLabelBullets($menuItemContainer, $labels, $labelIndex, $sessionSection) {

        if (isset($labels['active'])) {
            foreach ((array) $labels['active'] as $labelId) {

                $label = $labelIndex[$labelId];
                
                if ($label['name'] == 'Homepage') {
                    $menuItemContainer->add(Html::el('span')
                                    ->class('user-label-bullet homepage-label-bullet')
                    );
                }
                
            }
        }
        
//        foreach ((array) $this->presenter->pageManagerService->getAllLabels() as $labelId => $l) {
//            
//            
//            if (!in_array($labelId, $labels['passive']['assigned']) && !in_array($labelId, $labels['active'])) {
//                
//                $label = $labelIndex[$labelId];
//                
//                if ($label['name'] == 'Show in main menu') {
//                    $menuItemContainer->add(Html::el('span')
//                                    ->class('user-label-bullet not-in-mainmenu-label-bullet')
//                    );
//                }
//                
//                
//                if ($label['name'] == 'Show in sitemap.xml') {
//                    $menuItemContainer->add(Html::el('span')
//                                    ->class('user-label-bullet not-in-sitemap-label-bullet')
//                    );
//                }
//                
//            }
//            
//            
//        }
        
       
        
        
        return $menuItemContainer;
    }
    
    
    private function _addLabelBullets($menuItemContainer, $labels, $labelIndex) {

        

        $section = $this->presenter->context->session->getSection('structureManager');

        $adminLabelMode = TRUE;
        if ($section->labelMode) {
            $adminLabelMode = $section->labelMode == 'admin' ? TRUE : FALSE;
        }
        

        if (!empty($labels)) {
            
            if ($adminLabelMode) {
                $menuItemContainer = $this->_addAdminLabelBullets($menuItemContainer, $labels, $labelIndex, $section);
            } else {
                $menuItemContainer = $this->_addUserLabelBullets($menuItemContainer, $labels, $labelIndex, $section);
            }
        }

        return $menuItemContainer;
    }

    
    private function _addTrashBullet($menuItemContainer) {
        
        $menuItemContainer->add(Html::el('span')
                                    ->class('user-label-bullet trashed-bullet')
                    );
        
        
        return $menuItemContainer;
    }
    
    
    public function renderMenuItem($page, $getDescendantsParams, $menuItemContainer, $level, $horizontalLevel, $highlight) {
        
        $presenter = $page->getPresenter();

        //dump('ensuring '.$page->treeNodeId.' has descendants');
        
        
        
        $descendants = $page->getDescendants($getDescendantsParams);
        
        if ($descendants) {
            $menuItemContainer->class[] = 'expandable';
            $menuItemContainer->add(Html::el('div')
                            ->class('hitarea expandable-hitarea')
            );
            //$menuItemContainer->setHtml('<div class="hitarea expandable-hitarea">');
        }
        
        if ($getDescendantsParams['lang'] == $page->_lang) {
            
            $menuItemContainer = $this->_addLabelBullets($menuItemContainer, $page->_labels, $this->presenter->pageManagerService->getAllLabels());
        }
//        if ($page->getProperty('status') == 'trashed') {
//            $menuItemContainer = $this->_addTrashBullet($menuItemContainer);
//        }
//                
//                
//        
//
//        if ($page->isInClipboard()) {
//            $text = '[c]';
//            if ($page->isCut())
//                $text = '[x]';
//
//            $menuItemContainer->add(Html::el('span')
//                            ->setText($text)
//            );
//        }

        //dump($getDescendantsParams);
        
        
        $allLabels = $this->presenter->pageManagerService->getAllLabels();
//        dump($allLabels);
//        
//        dump($page->_labels);
//        die();
        $pageLabels = $page->_labels;
        $buttonLabelId = NULL;
        if (!empty($pageLabels)) {
//            dump($pageLabels);
            foreach ($pageLabels as $label) {
                if (!$label['active'] && ($allLabels[$label['labelId']]['create_button'] == 1)) {
                    $buttonLabelId = $label['labelId'];
                    break;
                }
            }
        }
        
        $linkParams['id'] = $page->treeNodeId;
//        dump($buttonLabelId);
        if ($buttonLabelId !== NULL) {
            $linkParams['labelId'] = $buttonLabelId;
        }
        
        $el = Html::el('a')
                ->id('menuitem-' . $page->treeNodeId)
                ->href($page->_is_link ? $presenter->link('Page:editLink', array('id' => $page->treeNodeId)) : $presenter->link('Page:default', $linkParams))
                ->menu_id($page->treeNodeId)
                ->depth($level - 1)
                ->setText($page->_name)
                ->class($highlight ? 'current' : '');
        
        if($page->_is_link) {
            $el->style('font-family: mono-space');
        }
        
        // if this condition holds, the $page is GHOST!
        if ($getDescendantsParams['lang'] !== $page->_lang) {
            $el->style('font-style: italic;color: black');
        }
//        dump($page->_status);
        
        if ($page->_status == 'trashed') {
            
            $strike = Html::el('strike');
            $strike->add($el);
            $menuItemContainer->add($strike);
        } else {
            
            $menuItemContainer->add($el);
        }
        

        
        return $menuItemContainer;
    }

}