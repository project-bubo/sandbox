<?php
namespace Bubo\Media\Components\Items;

use Nette\Utils\Html;

/**
 * Folder content item
 */
final class Folder extends AbstractItem 
{   

    /**
     * Enters the folder
     * @param int $folderId
     */
    public function handleEnterFolder($folderId) 
    {
        $media = $this->lookup('Bubo\\Media');
        $media['content']->view = NULL;
        $media['content']->actions = NULL;
        $media->folderId = $folderId;
        $media->fileId = NULL;
        $media->invalidateControl();
    }
    
    /**
     * Deletes the folder
     * @param int $folderId
     */
    public function handleDeleteFolder($folderId) 
    {
        $this->presenter->mediaManagerService->deleteFolder($folderId);
        
        $media = $this->lookup('Bubo\\Media');
        $media->invalidateControl();
    }
    
    /**
     * Creator of 'renameFolder' context menu item
     * @param Folder $folderItem
     * @return Html
     */
    public function createRenameFolderMenuItem($folderItem) 
    {
        $content = $this->getContent();
        return $this->getMenuItem('Přejmenovat', 'icon-pencil', array(
	    'href' => $content->link('openRenameFolderPopup', array('folderId' => $folderItem['id'])),
	    'class' => 'ajax'
	));
    }
    
    /**
     * Creator of 'deleteFolder' context menu item
     * @param Folder $folderItem
     * @return Html
     */
    public function createDeleteFolderMenuItem($folderItem) 
    {
        $content = $this->getContent();
        return $this->getMenuItem('Smazat', 'icon-trash', array(
	    'href' => $content->link('openDeleteFolderPopup', array('folderId' => $folderItem['id'])),
	    'class' => 'ajax'
	), array(
	    'class' => 'deleteButton'
	));
    }
    
    /**
     * Creator of 'cutFolder' context menu item
     * @param Folder $folderItem
     * @return Html
     */
    public function createCutFolderMenuItem($folderItem) 
    {
        return $this->createCutFolderItemMenuItem($folderItem, 'folder');
    }
    
    /**
     * Renders current folder content item in content page
     */
    public function render() 
    {
        $content = $this->lookup('Bubo\\Media\\Components\\Content');

        $folderItem = $content->getFolderContentItem('folders', $this->id);

        $template = $this->createNewTemplate(__DIR__ . '/templates/default.latte');
        $template->folderItem = $folderItem;
        $template->iconSrc =  $this->presenter->mediaManagerService->getIconBasePath() . '/folder_close.png';
        
        $template->menu = $this->createMenu($folderItem);
        $template->render();
    }
    
    /**
     * Renders current folder content item as breadcrumb
     * @param bool $isLast
     */
    public function renderAsBreadcrumb($isLast) 
    {
        $navBar = $this->lookup('Bubo\\Media\\Components\\NavBar');

        $breadcrumb = $navBar->getBreadcrumbItem($this->id);
        $el = $this->getBreadcrumbElement($breadcrumb['name'], $isLast);
        
        if (!$isLast) {
            $el->href($this->link('enterFolder', array('folderId' => $breadcrumb['id'])));
        }
        
        echo $el;
    }
}

