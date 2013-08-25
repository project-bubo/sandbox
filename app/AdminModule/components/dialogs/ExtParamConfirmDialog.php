<?php

namespace BuboApp\AdminModule\Dialogs;

final class ExtParamConfirmDialog extends BaseConfirmDialog {

    public function __construct($parentPresenter, $name) {
        parent::__construct($parentPresenter, $name);

        $this->buildConfirmDialog();
    }

    public function buildConfirmDialog() {

        $this
                ->addConfirmer(
                        'delete', // název signálu bude 'confirmDelete!'
                        array($this, 'deleteItem'), // callback na funkci při kliku na YES
                        'Opravdu odstranit tento parametr?' // otázka (může být i callback vracející string)
                );



    }

    public function deleteItem($extTreeNodeId) {
        //$labelId = $this->parentPresenter->getParam('labelId');

        $this->parentPresenter->extModel->removeParam($extTreeNodeId);

        $this->parentPresenter->flashMessage("Parametr byl smazán");
        $this->parentPresenter->redirect('this');

    }




}