<?php

namespace ContextMenu;


class VirtualDriveFileContextMenu extends \BuboApp\AdminModule\Components\ContextMenu {

    
    public function createComponentDeleteFile() {
        return new \BuboApp\AdminModule\Components\VirtialDrive\Dialogs\FileConfirmDialog($this->presenter);
    }
    
    public function render() {
        $template = parent::initTemplate(dirname(__FILE__) . '/virtualDriveFileContextMenu.latte');
        $template->id = $this->treeNodeId;
        $template->clipboard =  (empty($this->getParent()->clipboard))?false:true;
        $template->render();
    }
    public function handleCutFile($id){
        $this->parent->clipboard = "file:".$id;
        $this->getParent()->invalidateControl();
    }
    
    public function handlePasteFile($id){
        if(preg_match("|^file\:([0-9]+)$|",$this->parent->clipboard, $mtch)){
            $model = $this->presenter->getModelTinyMce();
            $fileId = $mtch[1];
            try{
                $model->moveFile($fileId, false, $id);
                $this->parent->clipboard = NULL;
            }catch(\Exception $e){
                $this->getParent()->setError($e->getMessage());
            }            
            $this->getParent()->invalidateControl();
        }
    }
    public function handleDeleteFile($id){
        $model = $this->presenter->getModelTinyMce();
        $model->deleteFile($id);
        $this->getParent()->invalidateControl();
    }
}
