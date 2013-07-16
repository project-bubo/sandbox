<?php

namespace ContextMenu;

use \Components\Core\PageManager\Commands;

class OptionsContextMenu extends ContextMenu {
    
    
    public function render() {
        $template = parent::initTemplate(dirname(__FILE__) . '/optionsContextMenu.latte');
        
        $template->labelViewGlobal = $this->parent->sessionSection->labelViewGlobal; 
        $template->labelViewLocal = $this->parent->sessionSection->labelViewLocal; 
       
        $template->labelAdminMode = $this->parent->sessionSection->labelMode == 'admin';
        
        $template->render();
    }
    
    public function handleChangeMode($mode) {
        $this->parent->sessionSection->labelMode = $mode;
        
        $this->presenter->invalidateControl('structureManager');
        
        $message = '';
        switch ($mode) {
            case 'admin':
                $message = "Zapnut administrátorský mód štítků";
                break;
            case 'user':
                $message = "Zapnut uživatelský mód štítků";
                break;
        }
        
        $this->_finalizeRequest($message);
    }
    
    public function handleToggleLocal() {
        $this->parent->sessionSection->labelViewLocal = !$this->parent->sessionSection->labelViewLocal;
        $this->presenter->invalidateControl('structureManager');
        $messageTail = $this->parent->getSessionSection()->labelViewLocal ? 'zapnuto' : 'vypnuto';
        $this->_finalizeRequest("Zobrazení lokálních štítků $messageTail");
    }
  

    public function handleToggleGlobal() {
        $this->parent->getSessionSection()->labelViewGlobal = !$this->parent->sessionSection->labelViewGlobal;
        $this->presenter->invalidateControl('structureManager');
        
        $messageTail = $this->parent->getSessionSection()->labelViewGlobal ? 'zapnuto' : 'vypnuto';
        $this->_finalizeRequest("Zobrazení globálních štítků $messageTail");
    }
    
    private function _finalizeRequest($message) {
        $this->getPresenter()->flashMessage($message);
        $this->getPresenter()->invalidateControl('structureManager');
        $this->getPresenter()->invalidateControl('flashMessages');
    }
 
}
