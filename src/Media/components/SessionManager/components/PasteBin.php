<?php

namespace Bubo\Media\Components\SessionManager;

use Nette, Bubo, Bubo\Media\Components\SessionManager, Nette\Utils\Html;

class PasteBin extends SessionManager {   
    
    private $section;
    
    public function __construct($parent, $name) {
        parent::__construct($parent, $name);
        $this->sessionSectionName = 'pasteBin';
        $this->section = $this->getSessionSection();
    }
    
    public function isEmpty() {
        return !isset($this->section->data);
    }
    
    public function contains($folderItemId, $folderItemType) {
        $item = array(
                    'folderItemId'      => $folderItemId,
                    'folderItemType'    => $folderItemType
        );
        
        return !$this->isEmpty() && 
                is_array($this->section->data['items']) &&
                array_key_exists($this->_getItemHash($item), $this->section->data['items']);
    }
    
    public function clean() {
        unset($this->section->data);
    }
    
    public function getContents() {
        return $this->section->data;
    }
    
    private function _amIInTheSourceFolder($sourceFolderId, $sourceSection) {
        return !$this->isEmpty() && 
                $this->section->data['sourceFolderId'] == $sourceFolderId &&
                $this->section->data['sourceSection'] == $sourceSection;
    }
    
    private function _getSourceSection() {
        $section = NULL;
        if (!$this->isEmpty()) {
            $section = $this->section->data['sourceSection'];
        }
        return $section;
    }
    
    private function _resetSourceFolder($sourceFolderId, $sourceSection) {
        $data = array(
                    'sourceFolderId'    => $sourceFolderId,
                    'sourceSection'     => $sourceSection,
                    'items'             => array()
        );
        $this->section->data = $data;
    }
    
    private function _getItemHash($item) {
        return sha1(serialize($item));
    }
    
    private function _addItem($item) {
        $this->section->data['items'][$this->_getItemHash($item)] = $item;
    }
    
    /**
     * Add item into the paste bin
     * 
     * The behaviour branches upon the source folder
     * if i am in the source folder (adressed by folder id and section)
     *  - add item to the array of items
     * else
     *  - reset the source folder
     * 
     * @param type $sourceFolderId
     * @param type $sourceSection
     * @param type $item
     */
    public function add($sourceFolderId, $sourceSection, $item) {
        if (!$this->_amIInTheSourceFolder($sourceFolderId, $sourceSection)) {
            $this->_resetSourceFolder($sourceFolderId, $sourceSection);
        }
        $this->_addItem($item);
    }
    
    private function _createInsertButton($numberOfItems) {
        $button = Html::el('a');
        $button->href($this->parent->link('pasteFolderContent!'))
                ->class('button ajax fright');
        
        $i = $button->create('i');
        $i->class('icon-paste m5');
        
        $text = $button->create(NULL);
        $text->setText(sprintf('Vložit položky (%d)', $numberOfItems));
            
        return $button;    
    }
    
    private function _createEmptyButton() {
        $button = Html::el('a');
        $button->href($this->parent->link('emptyPasteBin!'))
                ->class('button ajax fright');
        
        $i = $button->create('i');
        $i->class('icon-remove m5');
        
        return $button;  
    }
    
    public function getEmptyButton() {
        if ($this->showInsertButton()) {
            return $this->_createEmptyButton();
        }
    }
    
    public function getInsertButton() {
        if ($this->showInsertButton()) {
            return $this->_createInsertButton($this->getSize());
        }
    }
    
    public function getButtons() {
        return array(
                    $this->getEmptyButton(),
                    $this->getInsertButton()
                );
    }
    
    public function getSize() {
        $size = 0;
        if (!$this->isEmpty()) {
            $contents = $this->getContents();
            $size = count($contents['items']);
        }
        
        return $size;
    }
    
    public function showInsertButton() {
        $currentSection = $this->parent->getCurrentSection();
        $sourceSection = $this->_getSourceSection();
        
        $sectionOK = TRUE;
        if (($sourceSection !== NULL) && ($currentSection != $sourceSection)) {
            $sectionOK = FALSE;
        }
        
        return !$this->isEmpty() && $sectionOK;
    }
}
