<?php

namespace AdminModule;

use Nette\Http\User,
    \AdminModule\DataGrids\TestDataGrid,
    AdminModule\Forms\LoginForm;

final class UserPresenter extends AbstractAclPresenter {


    public function renderSetAcl($user_id) {

        $user = $this->getModelUser()->getUser($user_id);
        if (empty($user)) {
            throw new \Nette\Application\BadRequestException('User not found');
        }
                
        $this->template->user = $user;
        
    }
    
}
