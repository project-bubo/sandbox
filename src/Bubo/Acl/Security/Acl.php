<?php

namespace Acl\Security;

use Nette\Security\Permission;

class Acl extends Permission {

    public function __construct($resourceManager) {
        
        // roles
        $this->addRole('guest');
        $this->addRole('member', 'guest');        
        $this->addRole('admin');
        
        $resources = $resourceManager->resources;
        
        foreach ($resources as $resourceName => $resourceData) {
            $this->addResource($resourceName);
            
            $this->allow('member', $resourceName, Permission::ALL);
            foreach ($resourceData['privileges'] as $privilegeName => $privilegeTitle) {
                $this->allow('member', $resourceName, $privilegeName, callback($this, 'assert'));
            }
        }

        $this->allow('admin');
    }
    
    public function getRoles($excludedRoles = array('guest')) {
        $roles = parent::getRoles();
        
        $returnRoles = array();
        foreach ($roles as $r) {
            if (is_array($excludedRoles)) {
                if (!in_array($r, $excludedRoles)) {
                    $returnRoles[$r] = $r;
                }
            }
        }
        
        return $returnRoles;
    }
    
    public function assert($permission, $role, $resource, $privilege) {
        // user's acl is here to use .o)
        $usersAcl = $permission->getQueriedRole()->getAcl();
        return isset($usersAcl[$resource][$privilege]) ? $usersAcl[$resource][$privilege] : FALSE;
    }

}