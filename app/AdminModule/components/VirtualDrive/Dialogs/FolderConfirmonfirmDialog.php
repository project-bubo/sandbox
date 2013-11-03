<?php

namespace BuboApp\AdminModule\Components\VirtialDrive\Dialogs;

final class FolderConfirmDialog extends \BuboApp\AdminModule\Dialogs\BaseConfirmDialog {

    public function __construct($parentPresenter, $name) {
        parent::__construct($parentPresenter, $name);

        $this->buildConfirmDialog();
    }

    public function buildConfirmDialog() {

        $this
                ->addConfirmer(
                        'delete', // název signálu bude 'confirmDelete!'
                        array($this, 'deleteItem'), // callback na funkci při kliku na YES
                        $this->translate('Opravdu smazat?') // otázka (může být i callback vracející string)
                )
                ->addConfirmer(// všimněte si Fluent rozhraní
                        'enable', // 'confirmEnable!'
                        array($this, 'enableItem'), array($this, 'questionEnable')
        );


    }

    public function deleteItem($id) {


        $result = $this->presenter->getModelTinyMce()->deleteFolder($id);

        $this->parentPresenter->flashMessage("Galerie byla odstraněna");
        if($this->presenter->isAjax()){
            $this->parent->parent->invalidateControl();
        }else{
            $this->parentPresenter->redirect('this');
        }
    }




}