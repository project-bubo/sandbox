<?php

namespace Bubo\Components;

use Nette;

/**
 * All components that inherit from this class will have
 * - registered translator
 * - installed custom macros
 * 
 * For obtaining the template please use: parent::initTemplate($templateFile)
 */
abstract class RegisteredControl extends Nette\Application\UI\Control {

//    public function templatePrepareFilters($tpl) {
//        $tpl->registerFilter($latte = new \Nette\Latte\Engine);
//        \CMS\Macros\PluginMacros::install($latte->compiler);
//    }
    
    public function initTemplate($templateFile) {
        $template = $this->template;
        $template->setFile($templateFile);
        $template->setTranslator($this->getPresenter()->context->translator);

        return $template;
    }
    
    public function getBasePath() {
        $baseUrl = rtrim($this->presenter->context->httpRequest->getUrl()->getBaseUrl(), '/');
        return preg_replace('#https?://[^/]+#A', '', $baseUrl);
    }
    
    public function createNewTemplate($fileName = NULL) {
        $template = NULL;
        
        if ($fileName !== NULL) {
            $template = new \Nette\Templating\FileTemplate();
            $template->setFile($fileName);
        } else {
            $template = new Nette\Templating\Template();
        }
        
        $template->setTranslator($this->getPresenter()->context->translator);
        $template->registerFilter(new \Nette\Latte\Engine());
        $template->registerHelperLoader('Nette\Templating\Helpers::loader');
        
        //$baseUrl = rtrim($this->presenter->context->httpRequest->getUrl()->getBaseUrl(), '/');
        $template->basePath = $this->getBasePath();
        $template->themePath = $template->basePath . '/' . strtolower($this->presenter->pageManagerService->getCurrentModule());
        $template->_presenter = $this->presenter;
        $template->_control = $this;
        
        return $template;
    }

}