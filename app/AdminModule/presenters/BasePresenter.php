<?php

namespace AdminModule;

use ContextMenu, Netstars;

/**
 * @persistent(urlEditor)
 */
abstract class BasePresenter extends \BasePresenter {

    public $basePath;
    public $system = array();
    public $css = array();
    public $label = '.';
    
    public function startup() {
        parent::startup();

        
//        dump($this->getHttpRequest()->url->host);
//        die();
        
        $this->basePath = $this->getHttpRequest()->getUrl()->basePath;
        $this->system['cms_version'] = '0.1';
        
        
        // register admin menu sections (installed plugins only)
//        foreach ($this->plugins as $plugin) { 
//            if ($plugin['instance']->isInstalled() && $plugin['instance']->hasAdminSection()) {
//                $this['adminMenu']->registerSection($plugin['instance']->getAdminSection()->setParent($this));
//            }
//        }
        
        $this['adminMenu']->registerSection('labelSection');
        $this['adminMenu']->registerSection('virtualDriveSection');
        //$this['adminMenu']->registerSection('pluginSection');
        //$this['adminMenu']->registerSection('toolsSection');
        
        
        
        $currentModule = $this->pageManagerService->getCurrentModule();
        // set session
        $this['moduleSwitch']->getActualModule($currentModule);
        
        
//        $info = $this->virtualDriveService->getFileInfo(1);
//        dump($info);
//        die();
        
    }

    public function createComponentAdminMenu($name) {
        return new Components\AdminMenu($this, $name);
    }

    public function createComponentSitemapIndexer($name) {
        return new Sitemap\SitemapIndexer($this, $name);
    }
    
    public function createComponentMedia($name) {
        return new Netstars\Media($this, $name);
    }
    
    public function beforeRender() {
        parent::beforeRender();

        
        //$this['sitemapIndexer']->render();

        $this->template->pageManager = $this->pageManagerService;
        
    }

    /**
     * Factory method for all
     * - forms
     * - datagrids
     * - confirmdialogs
     *
     * @param type $name
     * @return classname 
     */
    public function createComponent($name) {

        if ($name == 'pageMultiForm') {
            return new Components\MultiForms\PageMultiForm($this, $name);
            
        } else if (preg_match('([a-zA-Z0-9]+Form)', $name)) {
            // detect forms   
            $classname = "AdminModule\\Forms\\" . ucfirst($name);
            if (class_exists($classname)) {
                $form = new $classname($this, $name);
                $form->setTranslator($this->context->translator);
                return $form;
            }
        } else if (preg_match('([a-zA-Z0-9]+DataGrid)', $name)) {
            // detect datagrids
            $classname = "AdminModule\\DataGrids\\" . ucfirst($name);
            if (class_exists($classname)) {
                $datagrid = new $classname($this, $name);
                $datagrid->setTranslator($this->context->translator);
                return $datagrid;
            }
        } else if (preg_match('([a-zA-Z0-9]+ConfirmDialog)', $name)) {
            // detect confrim dialogs
            $classname = "AdminModule\\Dialogs\\" . ucfirst($name);
            if (class_exists($classname)) {
                $dialog = new $classname($this);
                //$dialog->setTranslator($this->context->translator);
                return $dialog;
            }
        }  else if (preg_match('([a-zA-Z0-9]+Sorter)', $name)) {
            // detect confrim dialogs
            $classname = "AdminModule\\Sorters\\" . ucfirst($name);
            if (class_exists($classname)) {
                $sorter = new $classname($this, $name);
                //$dialog->setTranslator($this->context->translator);
                return $sorter;
            }
        } 
        
        return parent::createComponent($name);
        
    }

    public function createComponentLanguageManager($name) {
        return new \Components\LanguageManager($this, $name);
    }

    public function createComponentModuleSwitch($name) {
        return new Components\ModuleSwitch($this, $name);
    }
    
    public function createComponentCss() {

        $css = new \WebLoader\CssLoader;

        // cesta na disku ke zdroji
        $css->sourcePath = PLUGINS_DIR;

        // cesta na webu k cílovému adresáři
        $css->tempUri = $this->basePath . "css/plugins";

        // cesta na disku k cílovému adresáři
        $css->tempPath = WWW_DIR . "/css/plugins";

        $css->joinFiles = FALSE;

        return $css;
    }

    public function createComponentStructureManager($name) {
        return new Components\StructureManager($this, $name, $this->userId);
    }


}

