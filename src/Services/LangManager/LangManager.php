<?php

/**
 * @author     Marek Juras
 */

namespace Bubo\Services;

use Bubo;

class LangManager extends BaseService {
    
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
    
    public function getAlienLangs($referencingPages, $activatedLanguages) {
        $alienLangs = array();
        
        $langsToAdd = array();
        $langTitles = $activatedLanguages;
        if (!empty($referencingPages)) {
            foreach ($referencingPages as $moduleName => $aliens) {
                
                $referencingModuleLangs = $this->getLangs($moduleName);

                // compute languages to add
                $langsToAdd = array_merge($langsToAdd, array_diff(array_keys($referencingModuleLangs), array_keys($activatedLanguages)));
                $langTitles = array_merge($langTitles, $referencingModuleLangs);
                
            }
            if (!empty($langsToAdd)) {
                foreach ($langsToAdd as $langCode) {
                    $alienLangs[$langCode] = $langTitles[$langCode];
                }
            }
        }
        
        return $alienLangs;
    }
    
    
    public function getLangs($specificModule = NULL) {
        
        $langs = NULL;
        
        $actualModule = NULL;
        if ($specificModule == NULL) {
           
            if ($this->presenter->moduleName == 'Admin') {
                // get module from module switch
                $actualModule = $this->presenter['moduleSwitch']->getActualModule();
            } else {
                $actualModule = $this->presenter->pageManagerService->getCurrentModule();
            }
        } else {
            $actualModule = $specificModule;
        }
            
        $moduleConfig = $this->presenter->configLoaderService->loadModulesConfig($actualModule);

        if (isset($moduleConfig['modules'][$actualModule]['langs'])) {
            $langs = $moduleConfig['modules'][$actualModule]['langs'];
        }
        
        if ($langs === NULL) {
            $p = $this->context->getParameters();
            $langs = $p['langs'];
        }
        return $langs;
    }
    
    public function getGhostPriority($lang) {
        
        $langs = $this->getLangs();
        $ghostPriority = array();
        
        if ($lang !== NULL) {
            if (isset($langs[$lang])) {
                unset($langs[$lang]);
            }
            $ghostPriority = array_keys($langs);
            array_unshift($ghostPriority, $lang);
        } else {
            throw new \Exception('Lang is null!!!');
        }
        
        return $ghostPriority;
    }
    
    public function getDefaultLanguage() {
        
        $defaultLanguage = NULL;
        
        $actualModule = NULL;
        if ($this->presenter->moduleName == 'Admin') {
            // get module from module switch
            $actualModule = $this->presenter['moduleSwitch']->getActualModule();
//            dump($actualModule);
//            die();
        } else {
            $actualModule = $this->presenter->pageManagerService->getCurrentModule();
        }
            
        $moduleConfig = $this->presenter->configLoaderService->loadModulesConfig($actualModule);
//            dump($moduleConfig);
//            die();

        if (isset($moduleConfig['modules'][$actualModule]['defaultLang'])) {
            $defaultLanguage = $moduleConfig['modules'][$actualModule]['defaultLang'];
        }
            
        
//        dump($this->presenter->moduleName);
//        dump('ptam se na jazyk');
//        die();
        
        if ($defaultLanguage === NULL) {
            $p = $this->context->getParameters();
            $defaultLanguage = $p['defaultLang'];
        }
        return $defaultLanguage; 
    }
    
   
    public function renderFlags() {
        $template = $this->template;
        $template->setFile(__DIR__ . '/templates/flags.latte');
        $template->langs = $this->getLangs();
        $template->render();
    }
}