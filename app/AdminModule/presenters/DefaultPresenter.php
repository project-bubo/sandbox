<?php

namespace AdminModule;

class DefaultPresenter extends SecuredPresenter {


    public function renderDefault($plugin, $view) {

        $args = $this->getParam();

        unset($args['action'], $args['plugin'], $args['view']);

        if (!empty($plugin)) {

            $this->template->plugin = $plugin;
            $this->template->view = $view;
            $this->template->args = $args;
        }

        $this->template->numberOfConcepts = 0;
        $this->template->numberOfTrashed = 0;
                
    }


    public function actionLogout() {

        $this->getUser()->logOut(TRUE);
        $this->flashMessage('Právě jste se odhlásili z administrace.');
        $this->redirect('Auth:login');
    }





}