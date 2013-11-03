<?php

namespace BuboApp\AdminModule\Dialogs;

final class TrashConfirmDialog extends BaseConfirmDialog {

    public function __construct($parentPresenter, $name) {
        parent::__construct($parentPresenter, $name);

        $this->buildConfirmDialog();
    }

    public function buildConfirmDialog() {

        $this
                ->addConfirmer(
                        'delete', // název signálu bude 'confirmDelete!'
                        array($this, 'deleteItem'), // callback na funkci při kliku na YES
                        'Opravdu smazat stránku?' // otázka (může být i callback vracející string)
                )
                ->addConfirmer(// všimněte si Fluent rozhraní
                        'restore', // 'confirmRestore!'
                        array($this, 'restoreItem'),
                        "Opravdu obnovit stránku?"
        );


    }


    public function restoreItem($page_id) {
        $page = $this->parentPresenter->getModelPage()->getActualPageByPageId($page_id);

        $result = $this->parentPresenter->getModelPage()->reloadFromBackup($page_id);

        if ($result) {
            $this->parentPresenter->flashMessage("Stránka byla obnovena z koše");
            $this->parentPresenter->redirect('Page:default', array('id' => $page->tree_node_id));
        } else {
            $this->parentPresenter->flashMessage("Obnova stránky se nezdařila");
            $this->parentPresenter->redirect('Page:default', array('id' => $page->tree_node_id));
        }
    }


    public function deleteItem($page_id) {

        $pageModel = $this->modelLoader->loadModel('PageModel');

        $result = $this->presenter->getModelPage()->deletePage($page_id);

        $this->parentPresenter->flashMessage("Stránka byla včetně historie odstraněna");
        $this->parentPresenter->redirect('this');
    }




}