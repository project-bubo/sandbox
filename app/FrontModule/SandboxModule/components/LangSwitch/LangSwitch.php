<?php

namespace FrontModule\SandboxModule\Components;

use Netstars;

class LangSwitch extends Netstars\Components\RegisteredControl {
    
    
    public function render($page) {
    
        $langs = $this->presenter->langManagerService->getLangs();
        
        $template = $this->createNewTemplate(__DIR__ . '/templates/menu.latte');
        $template->langs = $langs;
        $template->page = $page;
        $template->defaultLang = $this->presenter->langManagerService->getDefaultLanguage();

        echo $template;
        
       
        
    }

    
}
