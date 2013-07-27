<?php

namespace AdminModule\Components;

use Bubo;

class ModuleSwitch extends \Bubo\Components\RegisteredControl {   
    
    public function createComponentModuleSwitchForm($name) {
        return new ModuleSwitch\Components\ModuleSwitchForm($this, $name);
    }
    
    private function _getSessionSection() {
        return $this->presenter->getSession('moduleSwitch');
    }
    
    public function getActualModule($currentModule = NULL) {
        $moduleSwitchSection = $this->_getSessionSection();
        
        if (isset($moduleSwitchSection->actualModule)) {
            return $moduleSwitchSection->actualModule;
        } else {
            return $moduleSwitchSection->actualModule = $currentModule;
        }
    }
    
    public function setActualModule($actualModule) {
        $moduleSwitchSection = $this->_getSessionSection();
        $moduleSwitchSection->actualModule = $actualModule;
    }
    
    public function getAllModules() {
        $currentModule = $this->presenter->pageManagerService->getCurrentModule();
        $config = $this->presenter->configLoaderService->loadModulesConfig($currentModule);
        return $config['modules'];
    }
    
    public function render() {
        $allModules = $this->getAllModules();
        
        if (count($allModules) > 1) {
            $template = $this->createNewTemplate(__DIR__.'/templates/form.latte');
            echo $template;
        } 
        
    }
        
    
}
