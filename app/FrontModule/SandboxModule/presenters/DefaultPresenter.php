<?php

namespace BuboApp\FrontModule\SandboxModule;

use BuboApp;

final class DefaultPresenter extends BuboApp\FrontModule\DefaultPresenter {

     /**
     * Frontend dispatcher
     *
     * @param type $url - url (without first slash)
     */
    public function actionDefault($lang, $url) {
        $this->loadPage($lang, $url);
    }


}