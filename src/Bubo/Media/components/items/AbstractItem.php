<?php
namespace Bubo\Media\Components\Items;

use Bubo;

use Nette\Utils\Html;

/**
 * Abstract media item
 */
abstract class AbstractItem extends Bubo\Components\RegisteredControl 
{   
    /**
     * Id
     * @var int
     */
    private $id;
    
    /**
     * Sets id
     * @param int $id
     */
    public function setId($id) 
    {
        $this->id = $id;
    }
    
    /**
     * Retuns id
     * @return int
     */
    public function getId() 
    {
        return $this->id;
    }
 
    /**
     * Returns parent media component
     * @return \Bubo\Media
     */
    public function getMedia() 
    {
        return $this->lookup('Bubo\\Media');
    }
    
    /**
     * Returns content component
     * @return \Bubo\Media\Components\Media
     */
    public function getContent() 
    {
        return $this->lookup('Bubo\\Media\\Components\\Content');
    }
    
    /**
     * Returns current media trigger
     * @return string
     */
    public function getMediaTrigger() 
    {
        $media = $this->getMedia();
        return $media->getTrigger();
    }
    
    /**
     * Parse action list
     * @param string $actions
     * @return array
     */
    public function parseActionList($actions) 
    {
        $content = $this->getContent();
        return $content->parseActionList($actions);
    }
    
    /**
     * Returns icon base path
     * @return string
     */
    public function getIconBasePath() 
    {
        return $this->presenter->mediaManagerService->getIconBasePath();
    }
    
    /**
     * 
     * @param string $title
     * @param bool $isLast
     * @return Html
     */
    public function getBreadcrumbElement($title, $isLast) 
    {
        $el = NULL;
        
        if ($isLast) {
            $el = Html::el('span');
        } else {
            $el = Html::el('a');
            $el->class('ajax');
        }
        
        $el->setText($title);
        
        return $el;
    }
    
    /**
     * Renders current content item as breadcrumb
     * @param bool $isLast
     */
    abstract public function renderAsBreadcrumb($isLast);
    
    /**
     * Renders current content item
     */
    abstract public function render();
    
    /**
     * Is in pasteBin
     * @return bool
     */
    public function isInPasteBin() {
        $content = $this->lookup('Bubo\\Media\\Components\\Content');
        $reflection = $this->getReflection();
        $className = strtolower($reflection->getShortName());
        //dump($this->id, $className, 'files');
        return $content['pasteBin']->contains($this->id, $className); //!!!!!!!!!!!!!!!!!!!!!!!!!!
    }
    
    /**
     * Creator of 'cutFolder' context menu item
     * @param AbstractItem $folderItem
     * @param string $folderItemType
     * @return Html
     */
    public function createCutFolderItemMenuItem($folderItem, $folderItemType) 
    {
        $content = $this->lookup('Bubo\\Media\\Components\\Content');
        $menuItem = $this->getMenuItem('Vyjmout', 'icon-cut', array(
                            'href' => $content->link('cutFolderItem', array('folderItemId' => $folderItem['id'], 'folderItemType' => $folderItemType)),
                            'class' => 'ajax'
                        ));
        
        return $menuItem;
    }
    
    /**
     * Menu item creators
     * @param string $title
     * @param string $class
     * @param array $aParams
     * @param array $liParams
     * @return Html
     */
    public function getMenuItem($title, $class, $aParams = array(), $liParams = array()) 
    {        
        $menuItem = Html::el('li');
        $a = $menuItem->create('a');
        
        $i = Html::el('i');
        $i->class($class);
        
        $label = Html::el();
        $label->setText($title);
        
        $a->add($i);
        $a->add($label);
     
        foreach ($aParams as $k => $v) {
            $a->$k = $v;
        }
        
        foreach ($liParams as $k => $v) {
            $menuItem->$k = $v;
        }
        
        return $menuItem;
    }

    /**
     * Create menu separator
     * @return Html
     */
    public function createMenuSeparator() 
    {
        $menuItem = Html::el('li');
        $menuItem->class = 'vd-menuDivider';
        return $menuItem;
    }
    
    /**
     * Adds html snippet to menu
     * Snippet coresponds to selected operation in context menu 
     * All create***MenuItem method must be implemented by descendant classes
     * @param string $operationName
     * @param type $menu
     * @param type $mediaTrigger
     * @param type $folderItem
     */
    private function _addOperationToMenu($operationName, &$menu, $mediaTrigger, $folderItem) 
    {
        
        switch ($operationName) {
            case 'editTitles':
                $menu->add($this->createTitlesMenuItem($folderItem));
                break;
            case 'renameFile':
                $menu->add($this->createRenameMenuItem($folderItem));
                break;
            case 'insertFile':
                $menu->add($this->createInsertFileToContainerMenuItem($folderItem));
                break;
            case 'deleteFile':
                $menu->add($this->createDeleteFileMenuItem($folderItem));
                break;
            case 'cutFile':
                $menu->add($this->createCutFileMenuItem($folderItem));
                break;
            case 'toggleSelectFile':
                $menu->add($this->createToggleSelectFileMenuItem($folderItem));
                break;
            case 'renameFolder':
                $menu->add($this->createRenameFolderMenuItem($folderItem));
                break;
            case 'deleteFolder':
                $menu->add($this->createDeleteFolderMenuItem($folderItem));
                break;
            case 'cutFolder':
                $menu->add($this->createCutFolderMenuItem($folderItem));
                break;
            case 'renameGallery':
                $menu->add($this->createRenameGalleryMenuItem($folderItem));
                break;
            case 'deleteGallery':
                $menu->add($this->createDeleteGalleryMenuItem($folderItem));
                break;
            case 'insertGallery':
                $menu->add($this->createInsertGalleryMenuItem($mediaTrigger, $folderItem));
                break;
            case 'cutGallery':
                $menu->add($this->createCutGalleryMenuItem($folderItem));
                break;
            case 'sep':
                $menu->add($this->createMenuSeparator());
                break;
            
        }
        
    }
    
    /**
     * Create content menu based on the folderItem class name and trigger
     * The list of operations is taken from config file
     * @param AbstractItem $folderItem
     * @return Html
     */
    public function createMenu($folderItem) {        
        $menu = Html::el('ul');
        $mediaTrigger = $this->getMediaTrigger();
        
        $media = $this->getMedia();
        $config = $media->getConfig('contextMenu');
        
        $folderItemClassName = strtolower($this->reflection->getShortName());
        $actionList = $config[$mediaTrigger][$folderItemClassName];
        
        $content = $this->getContent();
        $actions = $content->parseActionList($actionList);
        
        foreach ($actions as $action) {
            $this->_addOperationToMenu($action, $menu, $mediaTrigger, $folderItem);
        }
        
        return $menu;
    }
    
}
