<?php

namespace Bubo\Media\Components;

use Bubo, Nette\Utils\Html, Nette;

/**
 * Navigation bar (breadcrumbs)
 * 
 * @property Bubo\Media $parent
 */
class NavBar extends Bubo\Components\RegisteredControl 
{   
    
    /**
     * Data for breadcrumbs
     * @var \DibiRow[]
     */
    private $breadCrumbs;
    
    private function _getNavBarStart($section, $folderId) {
        $config = $this->parent->getConfig();
        $sectionConfig = $config['layout']['sections'][$section];

        $link = $this->parent['sectionSwitch']->link('switchSection', array('section' => $section));
        
        //if ($folderId !== NULL) {
            $el = Html::el('a');
            $el->href($link)
               ->class('ajax');
            $i = $el->create('i');
            $i->class($sectionConfig['class']);
        //}
        
        return $el;
    }
    

    /**
     * Generic factory for breadcrumbs as items
     * @see Content::createComponentContentItem()
     * @return \Nette\Application\UI\Multiplier
     */
    public function createComponentNavBarItem() 
    {
        return new Nette\Application\UI\Multiplier(function ($itemId) {
            $itemChunks = explode('_',$itemId);
            $className = 'Bubo\\Media\\Components\\Items\\'.ucfirst($itemChunks[0]);
            $contentItem = new $className;
            $contentItem->setId($itemChunks[1]);
            return $contentItem;
        });
        
    }
    
    public function getBreadcrumbItem($id, $getFile = FALSE) {
        
        foreach ($this->breadCrumbs as $breadcrumb) {
            if ($getFile) {
                if (isset($breadcrumb['file_id'])) {
                    if ($breadcrumb['file_id'] == $id) {
                        //$breadcrumb['type'] = 'file';
                        return $breadcrumb;
                    };
                }
            } else {
                if (isset($breadcrumb['folder_id'])) {
                    if ($breadcrumb['folder_id'] == $id) {
                        return $breadcrumb;
                    };
                }
            }
        }
        
        return NULL;
    }
    
    private function _getSeparator() {
        $el = Html::el('span');
        $el->class('divider');
        $i = $el->create('i');
        $i->class('icon-angle-right');
        
        return $el;
    }
    
    /**
     * Returns breadcrumbs as an array of DibiRows representing folder items lying on the branch
     * from $fileId to the root
     * @param int $folderId
     * @param int $fileId
     * @return \DibiRow[]
     */
    private function _getBreadcrumbs($folderId, $fileId) 
    {
        $breadcrumbData = $this->presenter->mediaManagerService->getBreadcrumbs($folderId, $fileId);
        return array_reverse($breadcrumbData, TRUE);
    }
    
    /**
     * Renders the navbar
     */
    public function render() {
        
        $currentSection = $this->parent->getCurrentSection();
        $folderId = $this->parent->folderId;
        $fileId = $this->parent->fileId;
        
        
        $breadcrumbs = $this->_getBreadcrumbs($folderId, $fileId);
        
        $template = $this->createNewTemplate(__DIR__ . '/templates/default.latte');
        
//        dump($breadcrumbs);
//        die();
        
        //dump($this->parent->fileId);
        //die();
        
        $template->breadcrumbs = $this->breadCrumbs = $breadcrumbs;
//	dump($template->breadcrumbs);
//	die();
        $template->start = $this->_getNavBarStart($currentSection, $folderId);
        $template->separator = $this->_getSeparator();
        
        $template->render();
        
    }

    
}
