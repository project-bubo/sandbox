<?php

namespace BuboApp\AdminModule\Sorters;

use Bubo\Application\UI\Control;

class GeneralSorter extends Control {

    /**
     * @persistent
     */
    public $parentId;


    public function handleShowDescendats($parentId) {

    }

    public function handleSaveSortorder() {
        $data = $this->presenter->getParam('data');
        parse_str($data);
        // order is in $items

        $this->saveSortorder($items);

    }


    public function render() {
        $template = parent::initTemplate(dirname(__FILE__) . '/templates/default.latte');
        $template->itemList = $this->getItemList($this->parentId);
        echo $template;
    }


}
