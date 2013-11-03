<?php

namespace BuboApp\AdminModule\Dialogs;

final class LanguageConfirmDialog extends BaseConfirmDialog {

    public function __construct($parentPresenter, $name) {
        parent::__construct($parentPresenter, $name);

        $this->buildConfirmDialog();
    }

    public function buildConfirmDialog() {

        $this
                ->addConfirmer(
                        'install',
                        array($this, 'installLanguage'),
                        'Opravdu nainstalovat jazyk?'
                )
                ->addConfirmer(// všimněte si Fluent rozhraní
                        'uninstall', // 'confirmEnable!'
                        array($this, 'uninstallLanguage'),
                        'Opravdu odinstalovat jazyk?'
        );


    }


    public function installLanguage($language_id) {
        $model = $this->presenter->context->modelLoader->loadModel('LanguageModel');
        $language = $model->getLanguage($language_id);

        $model->installLanguage($language_id);

        $this->presenter->flashMessage("Jazyk \"$language->name\" byl úspěšně nainstalován");

        $this->presenter->redirect('default');

    }

    public function uninstallLanguage($language_id) {
        $model = $this->presenter->context->modelLoader->loadModel('LanguageModel');
        $language = $model->getLanguage($language_id);

        $model->uninstallLanguage($language_id);

        $this->presenter->flashMessage("Jazyk \"$language->name\" byl úspěšně odinstalován");

        $this->presenter->redirect('default');
    }


}