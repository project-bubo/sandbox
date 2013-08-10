<?php

namespace AdminModule\Dialogs;

final class ExtConfirmDialog extends BaseConfirmDialog {

    public function __construct($parentPresenter, $name) {
        parent::__construct($parentPresenter, $name);

        $this->buildConfirmDialog();
    }

    public function buildConfirmDialog() {

        $this
                ->addConfirmer(
                        'delete', // název signálu bude 'confirmDelete!'
                        array($this, 'deleteItem'), // callback na funkci při kliku na YES
                        'Opravdu odstranit toto rozšíření?' // otázka (může být i callback vracející string)
                );

    }

    public function deleteItem($extId) {
        //$labelId = $this->parentPresenter->getParam('labelId');

        $result = $this->parentPresenter->labelModel->removeExtension($extId);

        $this->parentPresenter->flashMessage("Rozšíření bylo smazáno");
        $this->parentPresenter->redirect('this');

    }




}