<?php

namespace ContextMenu;


class VirtualDriveGalleryContextMenu extends \AdminModule\Components\ContextMenu {

    
    public function createComponentConfirmer(){
        return new \AdminModule\Components\VirtialDrive\Dialogs\GalleryConfirmDialog($this->presenter);
    }
    
    public function render() {
        $template = parent::initTemplate(dirname(__FILE__) . '/virtualDriveGalleryContextMenu.latte');
        $template->id = $this->treeNodeId;
        $template->render();
    }

    
    public function handleDelete($id){
        
           // $this->presenter->getModelTinyMce()->deleteGallery($id);
           // $this->getParent()->invalidateControl();
        
    }
    
    public function handleEdit($id){
        $this->parent->handleSetView('editGallery',$id);
        $this->parent->invalidateControl();
    }
    
}
