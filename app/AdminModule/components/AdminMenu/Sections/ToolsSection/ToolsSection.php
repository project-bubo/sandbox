<?php

namespace AdminMenu\Sections;

class ToolsSection extends Section {   

    public function render() {
        $template = parent::initTemplate(dirname(__FILE__) . '/toolsSection.latte');
        
        $template->render();
    }

    
}
