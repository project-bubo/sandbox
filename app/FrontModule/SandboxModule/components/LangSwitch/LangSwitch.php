<?php

namespace FrontModule\SandboxModule\Components;

use Bubo\Application\UI\Control;

class LangSwitch extends Control {


    public function render($page) {
        $template = parent::initTemplate(dirname(__FILE__) . '/templates/menu.latte');
        $langs = $this->presenter->langManagerService->getLangs();

        $template->langs = $langs;
        $template->page = $page;
        $template->defaultLang = $this->presenter->langManagerService->getDefaultLanguage();

        echo $template;



    }


}
