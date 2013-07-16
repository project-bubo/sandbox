<?php

namespace AdminModule;

use Nette\Http\User,
    \AdminModule\DataGrids\TestDataGrid,
    AdminModule\Forms\LoginForm;

final class PluginPresenter extends SecuredPresenter {
    
    public $pluginManager;
    
    public function startup() {
        parent::startup();

        $this->pluginManager = $this->context->pluginManager;

    }

   
//    public function actionInstall($plugin_id) {         
//        $plugin = $this->pluginManager->getPlugin($plugin_id);
//        
//        if ($this->pluginManager->installPlugin($plugin_id)) {
//            $this->flashMessage("Plugin \"$plugin->name\" byl úspěšně nainstalován");
//        } else {
//            $this->flashMessage("Při instalaci pluginu \"$plugin->name\" nastala chyba", 'warning');
//        }
//        
//        $this->redirect('management');
//    }
//    
//    public function actionUninstall($plugin_id) {        
//        $plugin = $this->pluginManager->getPlugin($plugin_id);
//        
//        if ($this->pluginManager->uninstallPlugin($plugin_id)) {
//            $this->flashMessage("Plugin \"$plugin->name\" byl úspěšně odinstalován");
//        } else {
//            $this->flashMessage("Během odinstalování pluginu \"$plugin->name\" nastala chyba", 'warning');
//        }
//        
//        $this->redirect('management');
//    }
    
    
    public function renderManagenent() {        
    }
    
    public function renderInterpret($plugin, $view) {
        $args = $this->getParam();
        
        unset($args['action'], $args['plugin'], $args['view']);        
        if (!empty($plugin)) {            
            $this->template->plugin = $plugin;
            $this->template->view = $view;
            $this->template->args = $args;
        }
    }
    
    
}