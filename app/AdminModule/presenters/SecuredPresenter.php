<?php

namespace AdminModule;

use Nette\Http\User;

abstract class SecuredPresenter extends BasePresenter {

    public function startup() {
        parent::startup();
        
        $user = $this->getUser();
        
//        dump($user->isLoggedIn());
//        die();
        
        if ($user->isLoggedIn()) {
        
            $role = $user->identity->data['role'];
            
            //$detectedResource = $this->context->resourceManager->detectResource($this);
            
//            if (!empty($detectedResource)) {
//                
//                $resource = $detectedResource['resource'];
//                $privilege = $detectedResource['privilege'];
//                
//                if (!$this->context->authorizator->isAllowed($role, $resource, $privilege)) {
//                    $this->flashMessage('Pro vstup na tuto stránku nemáte dostatečné oprávnění!', 'error');
//                    $this->redirect('Default:default');
//                }
//            }
            
        } else {
            $this->flashMessage('Musite se přihlásit!', 'warning');
            $this->redirect('Auth:login');
        }

    }

}