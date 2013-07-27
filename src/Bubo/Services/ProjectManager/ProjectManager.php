<?php

/**
 * @author     Marek Juras
 */

namespace Bubo\Services;

class ProjectManager extends BaseService {
    
    private $context;
    
    private $presenter;
    
    public function __construct($context) {
        $this->context = $context;
    }

    public function isPresenterSet() {
        return $this->presenter !== NULL;
    }
    
    public function setPresenter($presenter) {
        $this->presenter = $presenter;
    }
    
    public function getOrderedModuleChunks($module = NULL) {
        $module = $module ?: $this->presenter->pageManagerService->getCurrentModule();
        $moduleChunks = explode('/', $module);
        
        return $moduleChunks;
    }
    
    public function getOrderedModulePaths($module = NULL, $reversed = TRUE) {
        
        $temp = '';
        $paths = array();
        
        $moduleChunks = $this->getOrderedModuleChunks($module);
        
        foreach ($moduleChunks as $moduleChunk) {
            $temp .= $moduleChunk . 'Module/';
            $paths[] = $temp;
        }
        
        return $reversed ? array_reverse($paths) : $paths;
    }
    
    public function getLayout($layout) {
        return $this->_getLayoutTemplate($layout);
    }
    
    public function getDoctype($doctype) {
        return $this->_getDoctypeTemplate($doctype);
    }
    
    private function _getDoctypeTemplate($doctype) {
        
        $doctypePath = APP_DIR . '/FrontModule/templates';
        
        $template = NULL;
        
        if (is_dir($doctypePath)) {
            foreach (\Nette\Utils\Finder::findFiles($doctype.'*.latte')
                    ->in($doctypePath) as $file) {                
                        $template = $file->getRealPath();
                        break;
            }
        }
      
        if ($template === NULL) {
            throw new \Nette\FileNotFoundException("Doctype '$doctype' was not found");
        }
        
        return $template;
        
    }

    
    private function _getLayoutTemplate($layout) {
        $paths = $this->getOrderedModulePaths(NULL);

        $template = NULL;
        
        foreach ($paths as $path) {
            $pathToTemplates = APP_DIR . '/' . $path . 'templates';    
            
            if (is_dir($pathToTemplates)) {
                foreach (\Nette\Utils\Finder::findFiles($layout.'*.latte')
                        ->in($pathToTemplates) as $file) {                
                            $template = $file->getRealPath();
                            break;
                }
            }
        }
        
        if ($template === NULL) {
            throw new \Nette\FileNotFoundException("Layout '$layout' was not found");
        }
        
        return $template;
    }
    
    private function _getFileTemplates($templates, $paths, $templateSubdir = '') {
        
        foreach ($paths as $path) {
            $pathToTemplates = APP_DIR . '/' . $path . 'templates' . ($templateSubdir ? '/'.$templateSubdir : $templateSubdir);    
            
            if (is_dir($pathToTemplates)) {
                foreach (\Nette\Utils\Finder::findFiles('*.latte')
                        ->exclude('*@*')->in($pathToTemplates) as $file) {                
                            $templates[($templateSubdir ? $templateSubdir.'/' : $templateSubdir) . $file->getFilename()] = $file->getFilename();
                }
            }
        }
        
        return $templates;
        
    }
    
    public function getListOfTemplates($loadTemplatesWithUrl = TRUE) {
        
        $templates = array();
        
        $paths = $this->getOrderedModulePaths(NULL, FALSE);
        
        return $loadTemplatesWithUrl ? $this->_getFileTemplates($templates, $paths) : $this->_getFileTemplates($templates, $paths, 'scraps');
            
    }
    
}