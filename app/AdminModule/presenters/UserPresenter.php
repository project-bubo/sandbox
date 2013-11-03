<?php

namespace BuboApp\AdminModule;

final class UserPresenter extends BasePresenter {


    public function renderSetAcl($user_id) {

        $user = $this->getModelUser()->getUser($user_id);
        if (empty($user)) {
            throw new \Nette\Application\BadRequestException('User not found');
        }

        $this->template->user = $user;

    }

}
