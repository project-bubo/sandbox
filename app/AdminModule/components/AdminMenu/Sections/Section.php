<?php
namespace AdminMenu\Sections;

class Section extends \Nette\Application\UI\Control {

    public function initTemplate($templateFile) {
        $template = $this->template;
        $template->setFile($templateFile);
        $template->setTranslator($this->getPresenter()->context->translator);

        return $template;
    }


}
