<?php
namespace BuboApp\AdminModule\Presenters;

/**
 * Description of SessionPresenter
 *
 * @author toretak
 */
class SessionPresenter extends BasePresenter {



    public function actionSaveSession($key, $value, $section = 'default'){
        $session = $this->getSession();
        $session = $session->getSection($section);
        $session->$key = $value;
        $this->terminate();
    }

}
