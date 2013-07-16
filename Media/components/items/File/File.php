<?php
namespace Bubo\Media\Components\Items;

use Nette\Utils\Html;

/**
 * File content item
 */
final class File extends AbstractItem 
{   
    
    /**
     * Factory for image detail
     * @param string $name
     * @return \Bubo\Media\Components\Items\File\Details\ImageDetail
     */
    public function createComponentImageDetail($name) 
    {
        return new File\Details\ImageDetail($this, $name);
    }
    
    /**
     * Factory for file detail
     * @param string $name
     * @return \Bubo\Media\Components\Items\File\Details\FileDetail
     */
    public function createComponentFileDetail($name) 
    {
        return new File\Details\FileDetail($this, $name);
    }
    
    /**
     * Delete current file
     * @param int $fileId
     */
    public function handleDeleteFile($fileId) 
    {
        $media = $this->lookup('Bubo\\Media');
        $this->presenter->mediaManagerService->deleteFile($fileId, $media->section);
        
        $media->invalidateControl();
    }
    
    /**
     * Open current file
     * @param int $fileId
     * @param boolean $snippetMode
     */
    public function handleOpenFile($fileId, $snippetMode = TRUE) 
    {
        $file = $this->presenter->mediaManagerService->getFile($fileId);
        $media = $this->lookup('Bubo\\Media');
        
        $media['content']->actions = NULL;
        $media['content']->view = NULL;
        $media->fileId = NULL;
        if ($this->presenter->mediaManagerService->fileExists($fileId)) {
            $media['content']->actions = 'file';
            $media['content']->view = 'fileDetail';
            $media->fileId = $fileId;
        } 
        
        
        if ($snippetMode) {
            if ($file) {
                $mediaData['contentType'] = $file['is_image'] ? 'image-detail' : 'file-detail';
                $this->presenter->payload->mediaData = $mediaData;
            }
            $media->invalidateControl();
        }
    }
    
    
    public function createTitlesMenuItem($folderItem) 
    {
        $content = $this->getContent();
        return $this->getMenuItem('Popisky', 'icon-tag', array(
                    'href' => $content->link('openEditTitlesPopup', array('fileId' => $folderItem['id'])),
                    'class' => 'ajax'
                ));
    }
    
    public function createRenameMenuItem($folderItem) 
    {
        $content = $this->getContent();
        return $this->getMenuItem('Přejmenovat', 'icon-pencil', array(
                            'href' => $content->link('openRenameFilePopup', array('fileId' => $folderItem['id'])),
                            'class' => 'ajax'
                        ));
    }
    
    public function createCutFileMenuItem($folderItem) 
    {
        $content = $this->getContent();
        $menuItem = Html::el(NULL);
        if ($content->getCurrentSection() == 'files') {
            $menuItem = $this->createCutFolderItemMenuItem($folderItem, 'file');
        }
        return $menuItem;
    }
    
    public function createInsertFileToContainerMenuItem($folderItem) 
    {
        $content = $this->getContent();
        
        $menuItem = Html::el(NULL);
        if ($content->parent->extName == 'mediaFile') {
            $menuItem = $this->getMenuItem('Vložit tento soubor do stránky', 'icon-external-link', array(
                            'href' => $content->link('insertFileToContainer!', array('fileId' => $folderItem['id'])),
                            'class' => 'ajax'
                        ));

        }
            
        return $menuItem;
    }
    
    
    public function createDeleteFileMenuItem($folderItem) 
    {
        $content = $this->getContent();
        return $this->getMenuItem('Smazat', 'icon-trash', array(
                            'href' => $content->link('openDeleteFilePopup', array('fileId' => $folderItem['id'])),
                            'class' => 'ajax'
                        ), array(
                            'class' => 'deleteButton'
                        )
                );
    }
    
    public function createToggleSelectFileMenuItem($folderItem) 
    {
        $content = $this->getContent();
        
        $menuItem = NULL;
        
        if ($folderItem['selected']) {
            $menuItem = $this->getMenuItem('Zrušit označení', 'icon-bookmark', array(
                                            'href' => $content->link('toggleSelectFile', array('fileId' => $folderItem['id'], 'select' => FALSE)),
                                            'class' => 'ajax'
                                        )
                                );
        } else {
            $menuItem = $this->getMenuItem('Označit', 'icon-bookmark', array(
                                            'href' => $content->link('toggleSelectFile', array('fileId' => $folderItem['id'], 'select' => TRUE)),
                                            'class' => 'ajax'
                                        )
                                );
        }
        
        return $menuItem;
    }
    
    /**
     * Renders current file content item in content page
     */
    public function render() 
    {
	/* @var $content \Bubo\Media\Components\Content */
        $content = $this->lookup('Bubo\\Media\\Components\\Content');
	/* @var $media \Bubo\Media */
        $media = $this->lookup('Bubo\\Media');
        
        $folderItem = $content->getFolderContentItem('files', $this->id);
        
        $template = $this->createNewTemplate(__DIR__ . '/templates/default.latte');
        $template->folderItem = $folderItem;
        $template->iconSrc = $this->presenter->mediaManagerService->getFileIconSrc($folderItem);
        
        $template->sortable = $media->getCurrentSection() == 'galleries';        
        $template->menu = $this->createMenu($folderItem);        
        $template->isInPasteBin = $this->isInPasteBin();
        
        $template->render();
    }
    
    /**
     * Renders current file content item as breadcrumb
     * @param bool $isLast
     */
    public function renderAsBreadcrumb($isLast) 
    {
        $navBar = $this->lookup('Bubo\\Media\\Components\\NavBar');        
        $breadcrumb = $navBar->getBreadcrumbItem($this->id, TRUE); 
        $el = $this->getBreadcrumbElement($breadcrumb['name'], $isLast);
        
        if (!$isLast) {
            $el->href($this->link('enterGallery', array('galleryId' => NULL)));
        }
        
        echo $el;
    }
    
    /**
     * Renders current file content item as detail
     */
    public function renderAsDetail() 
    {
        $_file = $this->presenter->mediaManagerService->getFile($this->id);
        
        if ($_file['is_image']) {
            $this['imageDetail']->render();
        } else {
            $this['fileDetail']->render();
        }
        
    }
    
}
