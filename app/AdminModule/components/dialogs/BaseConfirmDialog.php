<?php

namespace AdminModule\Dialogs;

class BaseConfirmDialog extends \ConfirmationDialog {
    
    /* owner presenter */
    public $parentPresenter;
    
    /* model */
    public $modelLoader;
    
    public function __construct($parentPresenter) {
        parent::__construct();
        
        $this->parentPresenter = $parentPresenter;
        $this->modelLoader = $parentPresenter->context->modelLoader;
        
        $this->getFormElementPrototype()->addClass('ajax');      
            
    }
    
}