<?php

namespace AdminMenu\Sections;

class VirtualDriveSection extends Section {   

    public function render() {
        $template = parent::initTemplate(dirname(__FILE__) . '/virtualDriveSection.latte');
       
        $template->render();
    }

    
}
