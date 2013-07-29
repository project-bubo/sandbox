<?php

namespace AdminModule\Dialogs;

class BaseConfirmDialog extends \ConfirmationDialog {

    /* owner presenter */
    public $parentPresenter;

    /* model */
    public $modelLoader;

    public function __construct($parent, $name) {
        /* @var $session \Nette\Http\Session */
        $session = $parent->context->session;
        $section = $session->getSection('confirm-dialog-'.$name);
        parent::__construct($section, $parent, $name);

        $this->parentPresenter = $parent;
        $this->modelLoader = $parent->context->modelLoader;

        $this->getFormElementPrototype()->addClass('ajax');

    }

}