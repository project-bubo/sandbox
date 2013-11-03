<?php

namespace ContextMenu;


class VirtualDriveFolderContextMenu extends \BuboApp\AdminModule\Components\ContextMenu {

    
    public function createComponentDeleteFolder(){
        return new \BuboApp\AdminModule\Components\VirtialDrive\Dialogs\FolderConfirmDialog($this->presenter);
    }
    
    public function render() {
        $template = parent::initTemplate(dirname(__FILE__) . '/virtualDriveFolderContextMenu.latte');
        $template->id = $this->treeNodeId;
        $template->clipboard = $this->getParent()->clipboard;
        $template->render();
    }

    public function handleCutFolder($id){
        $this->parent->clipboard = "folder:".$id;
        $this->getParent()->invalidateControl();
    }
    
    public function handlePaste($id){
        if(preg_match("|^folder\:([0-9]+)$|",$this->parent->clipboard, $mtch)){
            $model = $this->presenter->getModelTinyMce();
            $fileId = $mtch[1];
            try{
                $model->moveFolder($fileId, $id);
                $this->parent->clipboard = NULL;
            }catch(\Exception $e){
                $this->getParent()->setError($e->getMessage());
            }
            $this->getParent()->invalidateControl();
        }elseif(preg_match("|^file\:([0-9]+)$|",$this->parent->clipboard, $mtch)){
            $model = $this->presenter->getModelTinyMce();
            $fileId = $mtch[1];
            $model->moveFile($fileId, $id);
            $this->parent->clipboard = NULL;
            $this->getParent()->invalidateControl();
        }
    }
    public function handleDeleteFolder($id){
        $model = $this->presenter->getModelTinyMce();
        $model->deleteFolder($id);
        $this->getParent()->invalidateControl();
    }
    
}
