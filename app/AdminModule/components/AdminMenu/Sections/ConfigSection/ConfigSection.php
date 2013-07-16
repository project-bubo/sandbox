<?php

namespace AdminMenu\Sections;

class ConfigSection extends Section {   

    public function render() {
        $template = parent::initTemplate(dirname(__FILE__) . '/configSection.latte');
                
        $template->render();
    }

    
}
