<?php

namespace BuboApp\AdminModule\Components;

use Bubo\Application\UI\Control;

class PageSorter extends Control {

    public function createComponentPageSortTraverser($name) {
        return new PageSorter\Components\PageSortTraverser($this, $name);
    }

    public function render() {
        $template = parent::initTemplate(dirname(__FILE__) . '/templates/default.latte');

        $template->render();

    }


}
