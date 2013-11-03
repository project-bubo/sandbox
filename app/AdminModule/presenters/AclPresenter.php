<?php

namespace BuboApp\AdminModule;

final class AclPresenter extends BasePresenter {

    public function startup() {
        parent::startup();

        // zkontroluj, jestli vsechny role (v acl listu) maji svoje predlohy v tabulce
        // [:core:roles]


        $_dbRoles = $this->getModelAcl()->getRoles();
        $dbRoles = array_keys($_dbRoles);

        $appRoles = $this->context->authorizator->getRoles();


        $missingRoles = array_diff($appRoles, $dbRoles);

        if (!empty($missingRoles)) {
            $acl = $this->context->resourceManager->getAcl();
            $this->getModelAcl()->addMissingRoles($missingRoles, $acl);
        }


        $aclTitles = $this->context->resourceManager->getAclTitles();
    }


    public function renderDefault() {

    }



}
