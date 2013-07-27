<?php

namespace AdminModule\Components;

use Bubo;

class StructureManager extends Bubo\Components\ContextMenuControl {   

    private $userId;
    
    private $sessionSection;
    
    public function __construct($parent, $name, $userId) {
        parent::__construct($parent, $name);
        $this->userId = $userId;
        
        $this->sessionSection = $this->presenter->context->session->getSection('structureManager');
        $this->sessionSection->labelMode = 'admin';
    }
    
    
    public function getSessionSection() {
        return $this->sessionSection;
    }
    
    public function setLanguage($lang) {
        $this->sessionSection->langCode = $lang;
    }
    
    public function getLanguage() {
        // language must be among allowed languages
        $allowedLangs = $this->presenter->langManagerService->getLangs();
        $currentLang = $this->sessionSection->langCode ?: $this->presenter->langManagerService->getDefaultLanguage();
        return $this->sessionSection->langCode = array_key_exists($currentLang, $allowedLangs) ? $currentLang : $this->presenter->langManagerService->getDefaultLanguage();
    }
    
    public function createComponentAdminPageMenu($name) {
        return new StructureManager\Components\AdminPageMenu($this, $name);
    } 
    
    public function handleChangeLanguageView($code) {
        
        $this->sessionSection->langCode = $code;
        $this->presenter->invalidateControl('structureManager');
    }
    
    public function render() {
        $template = parent::initTemplate(dirname(__FILE__) . '/adminMenu.latte');
        
        $identity = $this->presenter->user->identity;
        $template->userId = $this->userId;
        $template->name = $this->name;

        $template->contextMenuSelector = 'structureManagerContextMenuSelector';
        $template->optionsContextMenuSelector = 'optionsContextMenuSelector';
        
        
        $template->treeNodeId = $this->presenter->getParam('id');
        
        $template->actualLangCode = $this->getLanguage();
        
        $template->labelViewGlobal = $this->sessionSection->labelViewGlobal; 
        $template->labelViewLocal = $this->sessionSection->labelViewLocal; 
        
        //$languageIndex = $this->presenter->context->pageManager->getLanguageCodeIndex();
        //$languageCodes[$template->actualLangCode] = $languageIndex[$template->actualLangCode];

//        foreach ($this->presenter->context->pageManager->getLanguageCodeIndex() as $code => $langugage) {
//            //if ($langugage->enabled && $code != $template->actualLangCode) {
//            if ($langugage->enabled) {    
//                $languageCodes[$code] = $langugage;
//            }
//        }
        
        
        $this->presenter->langManagerService->getLangs();
        
        $template->langCodes = $this->presenter->langManagerService->getLangs();
        
        
        $template->render();
    }

    
}
