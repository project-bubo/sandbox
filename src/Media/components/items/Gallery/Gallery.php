<?php

namespace Bubo\Media\Components\Items;

use Nette\Utils\Html;
use Nette\Image;

/**
 * Gallery content item
 */
final class Gallery extends AbstractItem 
{   
    
    /**
     * Enters the gallery
     * @param string $galleryId
     * @param bool $snippetMode
     */
    public function handleEnterGallery($galleryId, $snippetMode = TRUE) 
    {
        $media = $this->lookup('Bubo\\Media');
        
        $media['content']->view = NULL;
        $media['content']->actions = NULL;
        $media->folderId = NULL;
        $media->fileId = NULL;
        if ($this->presenter->mediaManagerService->folderExists($galleryId)) {
            $media['content']->view = NULL;
            $media['content']->actions = 'gallery';
            $media->folderId = $galleryId;
            $media->fileId = NULL;
        }
        
        if ($snippetMode) {
            $media->invalidateControl();
        }
    }
    
    
    public function getIconSrc($galleryId) 
    {
        return $this->presenter->mediaManagerService->getGalleryIconSrc($galleryId);
    }
    
    public function createRenameGalleryMenuItem($folderItem) 
    {
        $content = $this->getContent();
        return $this->getMenuItem('Přejmenovat', 'icon-pencil', array(
                            'href' => $content->link('openRenameGalleryPopup', array('galleryId' => $folderItem['id'])),
                            'class' => 'ajax'
                        ));
    }
    
    public function createDeleteGalleryMenuItem($folderItem) 
    {
        $content = $this->getContent();
        return $this->getMenuItem('Smazat', 'icon-trash', array(
                            'href' => $content->link('openDeleteGalleryPopup', array('galleryId' => $folderItem['id'])),
                            'class' => 'ajax'
                        ), array(
                            'class' => 'deleteButton'
                        )
                );
    }
    
    private function _createInsertGalleryToContainerMenuItem($folderItem) 
    {
        $content = $this->getContent();
        return $this->getMenuItem('Vložit galerii do stránky', 'icon-external-link', array(
                            'href' => $content->link('insertGalleryToContainer!', array('galleryId' => $folderItem['id'])),
                            'class' => 'ajax'
                        ));
    }
    
    private function _createInsertGalleryToTinyMenuItem($folderItem) 
    {
        $content = $this->getContent();
        return $this->getMenuItem('Vložit galerii do stránky', 'icon-external-link', array(
                            'href' => $content->link('insertGalleryToTiny!', array('galleryId' => $folderItem['id'])),
                            'class' => 'ajax'
                        ));
    }
    
    public function createInsertGalleryMenuItem($mediaTrigger, $folderItem) 
    {
        $menuItem = Html::el(NULL);
        $media = $this->lookup('Bubo\\Media');
        switch ($mediaTrigger) {
            case 'container':
                    if ($media->extName == 'mediaGallery') {
                        $menuItem = $this->_createInsertGalleryToContainerMenuItem($folderItem);
                    }
                    break;
            case 'tiny':
                    $menuItem = $this->_createInsertGalleryToTinyMenuItem($folderItem);
                    break;
        }
        
        return $menuItem;
    }
    
    public function createCutGalleryMenuItem($folderItem) 
    {
        return $this->createCutFolderItemMenuItem($folderItem, 'gallery');
    }
    
    public function render() 
    {
        $content = $this->lookup('Bubo\\Media\\Components\\Content');

        $folderItem = $content->getFolderContentItem('folders', $this->id);

        $template = $this->createNewTemplate(__DIR__ . '/templates/default.latte');
        $template->folderItem = $folderItem;
        //$template->iconSrc = $this->presenter->mediaManagerService->getGalleryThumb($this->id);
        
        $template->menu = $this->createMenu($folderItem);        
        $template->isInPasteBin = $this->isInPasteBin();        
        $template->cid = $this->presenter->getParam('cid');
        
        $template->iconSrc = $this->presenter->mediaManagerService->getGalleryIconSrc($this->id);
        $template->render();
    }
    
    public function renderAsBreadcrumb($isLast) 
    {
        $navBar = $this->lookup('Bubo\\Media\\Components\\NavBar');
        $breadcrumb = $navBar->getBreadcrumbItem($this->id); 
        $el = $this->getBreadcrumbElement($breadcrumb['name'], $isLast);
        
        if (!$isLast) {
            $el->href($this->link('enterGallery', array('galleryId' => $breadcrumb['id'])));
        }
        
        echo $el;
    }
}