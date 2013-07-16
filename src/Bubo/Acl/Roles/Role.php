<?php

namespace Acl\Roles;

use Nette;

final class Role implements Nette\Security\IRole {

    private $roleId;    
    private $userId;
    private $acl;
    
    public function __construct($roleId, $userId, $acl) {
        $this->roleId = $roleId;
        $this->userId = $userId;
        $this->acl = $acl;
    }
    
    public function getRoleId() {
        return $this->roleId;
    }

    public function getUserId() {
        return $this->userId;
    }
    
    public function getAcl() {
        return $this->acl;
    }
}