<?php

namespace AdminMenu\Sections;

class LabelSection extends Section {

    public function render() {
        $template = $this->initTemplate(dirname(__FILE__) . '/labelSection.latte');

        $template->labels = $this->presenter->pageManagerService->getAllLabels();
        $template->render();
    }


}
