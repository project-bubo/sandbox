<?php

namespace FrontModule\SandboxModule\Components;

use Bubo;

class BreadcrumbNavigation extends Bubo\Navigation\BreadcrumbNavigation {

    public function render($page) {
        //\SimpleProfiler\Profiler::advancedTimer();

        $breadBrumbs = $page->getBreadcrumbs();

        $template = $this->initTemplate(__DIR__.'/templates/default.latte');
        $template->breadBrumbs = $breadBrumbs;
        $template->currentPage = $page;
        echo $template;

        //\SimpleProfiler\Profiler::advancedTimer($this->reflection->shortName);

    }

}