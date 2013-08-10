<?php

namespace BuboApp\AdminModule;

final class LabelPresenter extends BasePresenter {

    /**
     * @persistent
     */
    public $labelId;


    public function beforeRender() {
        parent::beforeRender();
//        dump($this->labelId);
        $labelId = $this->labelId;
        if (!empty($labelId)) {
            $label = $this->pageManagerService->getLabel($this->labelId);
            $this->template->labelName = $label ? $label['name'] : '';
        }
    }

    public function renderManageLabelExtensions($labelId) {
        $this->labelId = $labelId;
        $this->template->labelId = $labelId;
    }


    public function renderEditLabel($labelId) {
        $this->template->labelId = $labelId;
    }

}
