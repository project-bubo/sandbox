<?php

namespace FrontModule\SandboxModule\Components;

use Netstars;

class BreadcrumbNavigation extends Netstars\Navigation\BreadcrumbNavigation {
    
    public function render($page) {
        \SimpleProfiler\Profiler::advancedTimer();

        $breadBrumbs = $page->getBreadcrumbs();
            
        $template = $this->createNewTemplate(__DIR__.'/templates/default.latte');
        $template->breadBrumbs = $breadBrumbs;
        $template->currentPage = $page;
        echo $template;
        
        \SimpleProfiler\Profiler::advancedTimer($this->reflection->shortName);
        
    }
    
}