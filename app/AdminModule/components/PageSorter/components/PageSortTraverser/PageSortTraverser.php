<?php

namespace AdminModule\Components\PageSorter\Components;

use Nette\Application\UI\Form,
    Nette\Utils\Html,
    Netstars,
    \Components\Core\PageTraversers\RenderingTraversers\AdminMenuTraverser;

class PageSortTraverser extends \Netstars\Navigation\PageMenu {

    public function setUpRenderer($renderer) {
        //$renderer->getTopLevelPrototype()->class = 'w_menu treeview-white';
        //$renderer->getTopLevelPrototype()->id = 'tree';

        // custom onRenderMenuItem callback
        $renderer->onRenderMenuItem = callback($this, 'renderPageItem');

        return $renderer;
    }

    public function getTraverser() {
//        dump($section->langCode);
//        die();
        
        $traverser = new AdminMenuTraverser();
        
        // try to configure page loading via entity config
        $entityConfig = $this->presenter->configLoaderService->loadEntityConfig('page');
        
        $traverser
            ->setPresenter($this->presenter)
            ->setAcceptedStates(array('draft', 'published', 'trashed'))
            ->setLang($this->presenter->langManagerService->getDefaultLanguage())
            ->searchGhosts()
            ->searchAllTimeZones();
            //->configureLoading($entityConfig, 'full');
        
        if (isset($this->presenter->treeNodeId)) {
            $params = array(
                        'lang'                  => $this->parent->getLanguage(),
                        'treeNodeId'            => $this->presenter->treeNodeId
//                        'searchGhosts'          => TRUE,
//                        'searchAllTimeZones'    => TRUE
            );
            
        }
        
        return $traverser;
    }

    
    public function renderPageItem($page, $getDescendantsParams, $menuItemContainer, $level, $highlight) {
        
        $presenter = $page->getPresenter();

        
        $el = Html::el('a')
                ->id('menuitem-' . $page->treeNodeId)
                ->href($presenter->link('Page:default', array('id' => $page->treeNodeId)))
                ->menu_id($page->treeNodeId)
                ->depth($level - 1)
                ->setText($page->_name);
                
        $menuItemContainer->add($el);

        return $menuItemContainer;
    }

}