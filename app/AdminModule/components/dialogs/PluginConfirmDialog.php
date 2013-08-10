<?php

namespace BuboApp\AdminModule\Dialogs;

final class PluginConfirmDialog extends BaseConfirmDialog {

    public function __construct($parentPresenter, $name) {
        parent::__construct($parentPresenter, $name);

        $this->buildConfirmDialog();
    }

    public function buildConfirmDialog() {

        $this
                ->addConfirmer(
                        'install',
                        array($this, 'installPlugin'),
                        'Opravdu nainstalovat plugin?'
                )
                ->addConfirmer(// všimněte si Fluent rozhraní
                        'uninstall', // 'confirmEnable!'
                        array($this, 'uninstallPlugin'),
                        'Opravdu odinstalovat plugin?'
        );


    }

    private function _getPluginManager() {
        return $this->presenter->context->pluginManager;
    }

    public function installPlugin($plugin_id) {
        $plugin = $this->_getPluginManager()->getPlugin($plugin_id);

        if ($this->_getPluginManager()->installPlugin($plugin_id)) {
            $this->presenter->flashMessage("Plugin \"$plugin->name\" byl úspěšně nainstalován");
        } else {
            $this->presenter->flashMessage("Při instalaci pluginu \"$plugin->name\" nastala chyba", 'warning');
        }

        $this->presenter->redirect('management');

    }

    public function uninstallPlugin($plugin_id) {
        $plugin = $this->_getPluginManager()->getPlugin($plugin_id);

        if ($this->_getPluginManager()->uninstallPlugin($plugin_id)) {
            $this->presenter->flashMessage("Plugin \"$plugin->name\" byl úspěšně odinstalován");
        } else {
            $this->presenter->flashMessage("Během odinstalování pluginu \"$plugin->name\" nastala chyba", 'warning');
        }

        $this->presenter->redirect('management');
    }


}