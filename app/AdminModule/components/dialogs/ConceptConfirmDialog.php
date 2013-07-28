<?php

namespace AdminModule\Dialogs;

final class ConceptConfirmDialog extends BaseConfirmDialog {

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

        $pageModel = $this->modelLoader->loadModel('PageModel');

        $result = $this->presenter->getModelAutosaver()->deleteAutosaveData($id);

        $this->parentPresenter->flashMessage("Koncept byl smazán");
        $this->parentPresenter->redirect('this');
    }




}