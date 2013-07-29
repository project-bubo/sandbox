<?php

namespace AdminModule\Dialogs;

final class BackupConfirmDialog extends BaseConfirmDialog {

    public function __construct($parentPresenter, $name) {
        parent::__construct($parentPresenter, $name);

        $this->buildConfirmDialog();
    }

    public function buildConfirmDialog() {

        $this
                ->addConfirmer(
                        'backup', // název signálu bude 'confirmDelete!'
                        array($this, 'backupItem'), // callback na funkci při kliku na YES
                        'Vrátit ze zálohy? V tomto případě se stránka nepublikuje, pouze se vytvoří "draft" verze' // otázka (může být i callback vracející string)
                );



    }

    public function backupItem($page_id) {
        $treeNodeId = $this->parentPresenter->getParam('id');

        $result = $this->parentPresenter->getModelPage()->reloadFromBackup($page_id);

        if ($result) {
            $this->parentPresenter->flashMessage("Stránka byla obnovena ze zálohy");
            $this->parentPresenter->redirect('Page:default', array('id' => $treeNodeId));
        } else {
            $this->parentPresenter->flashMessage("Obnova stránky se nezdařila");
            $this->parentPresenter->redirect('Page:default', array('id' => $treeNodeId));
        }
    }




}