<?php
namespace AdminModule;

/**
 * Description of SessionPresenter
 *
 * @author toretak
 */
class SessionPresenter extends SecuredPresenter {
    
    
    
    public function actionSaveSession($key, $value, $section = 'default'){
        $session = $this->getSession();
        $session = $session->getSection($section);
        $session->$key = $value;
        $this->terminate();
    }
    
}
