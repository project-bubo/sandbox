<?php

namespace Model;

final class AclModel extends BaseModel {

    private function _queryRoles() {
        return $this->connection->query('SELECT * FROM [:core:roles]');
    }
    
    public function getRoles() {
        return $this->_queryRoles()->fetchAssoc('role');
    }
    
    public function getRolesSelectData() {
        $roles = $this->_queryRoles()->fetchAssoc('role', 'role');
        return $this->createSelectData($roles, 'role');
    }
    
    public function addMissingRoles($missingRoles, $acl) {

        
        $values = array();
        
        foreach ($missingRoles as $missingRole) {
            if ($missingRole != 'guest') {            
                $values[] = array(
                                'role'  =>  $missingRole,
                                'acl'   =>  serialize($acl)
                );
            }
        }
        
        if (!empty($values))
            $this->connection->query('INSERT INTO [:core:roles] %ex', $values);
    }
    
    public function loadFormValues($role) {
        $defaults = $this->connection->fetchSingle('SELECT [acl] FROM [:core:roles] WHERE [role] = %s', $role);
        return unserialize($defaults);
    }
    
    public function loadFormValuesForUser($userId) {
        $defaults = $this->connection->fetchSingle('SELECT [acl] FROM [:core:users] WHERE [user_id] = %i', $userId);
        return unserialize($defaults);
    }
    
    /**
     * Update users acl and template acl
     * @param type $role
     * @param type $acl
     * @return type 
     */
    public function updateRole($role, $acl) {
        $this->connection->query('UPDATE [:core:users] SET [acl] = %s WHERE [role] = %s', $acl, $role);
        return $this->connection->query('UPDATE [:core:roles] SET [acl] = %s WHERE [role] = %s', $acl, $role);
    }
    
    public function updateUser($data, $userId) {
        return $this->connection->query('UPDATE [:core:users] SET', $data, 'WHERE [user_id] = %i', $userId);
    }
    
    public function getRolesAcl($role) {
        return $this->connection->fetchSingle('SELECT [acl] FROM [:core:roles] WHERE [role] = %s', $role);
    }
    
    
    public function makeArray($values) {
        $array = array();
        foreach ($values as $resourceName => $privileges) {
            $array[$resourceName] = (array) $privileges;
        }
        
        return $array;
    }
    
    
    private function _getUsersWithInvalidAcl($validAclFingerprint) {
        return $this->connection->fetchAll('SELECT * FROM [:core:users] WHERE [acl_fingerprint] != %s', $validAclFingerprint);
    }
    
    /**
     * Comparable (flattened) list items have following structure:
     * 
     *      <domain>_<resource>_<privilege>
     *      |                  |
     *      |<--resourceName-->|<--privilegeName
     * 
     * 
     * Acl has following structure
     * array (
     *       <domain>_<resource>  =>  array(
     *                                  <privilege>     =>  TRUE or FALSE
     *                                  )   
     *       )
     * 
     * @param type $comparableListItem
     * @return type 
     */
    private function _extractResourceChunks($comparableListItem) {
        $chunks = explode('_', $comparableListItem);
        
        $resourceName = '';
        $privilegeName = '';
        if (!empty($chunks) && count($chunks) >= 2) {
            $privilegeName = $chunks[count($chunks)-1];
            unset($chunks[count($chunks)-1]);
            $resourceName = implode('_', $chunks);
        }
        
        return array(
                    'resourceName'  =>  $resourceName,
                    'privilegeName' =>  $privilegeName
        );
    }
    
    
    private function _updateAcl($acl, $addList, $removeList) {
        
        $updatedAcl = $acl;
        
        // apply addList
        foreach ($addList as $addListItem) {
            $rch = $this->_extractResourceChunks($addListItem);            
            $updatedAcl[$rch['resourceName']][$rch['privilegeName']] = FALSE;
        }
        
        // apply removeList
        foreach ($removeList as $removeListItem) {
            $rch = $this->_extractResourceChunks($removeListItem); 
            unset($updatedAcl[$rch['resourceName']][$rch['privilegeName']]);
        }
        
        // delete empty sections in updatedAcl
        foreach ($updatedAcl as $resourceName => $privileges) {
            if (empty($privileges)) {
                unset($updatedAcl[$resourceName]);
            }            
        }
        
        return $updatedAcl;
    }
    
    private function _computeAclFingerprint($aclComparable) {
        return sha1(implode(',',$aclComparable));
    }
    
    /**
     * Acl synchronization
     * -------------------
     * 
     * Before acl setting (rendering acl form) it is necessary
     * to show actual resources. 
     * Actual resources are maintained (and injected into this method) 
     * in resource manager.
     * 
     * This method synchornizes valid resources and all acl records in
     * [:core:user] (and [:core:roles] ??) table. 
     * 
     * ---------------------
     * 
     * Each subject with acl (aclSubject) has so called "acl fingerprint".
     * Resource manager provides actual fingerprint (from actual acl).
     * Every aclSubject needs to be synchrnonized if its fingerprint differs
     * with actual one.
     * 
     * 
     * @param type $validAcl 
     */
    public function synchronize($validAcl) {
        
        $res = 0;
        
        $validAclComparable = \Helpers\Inflectors::flatten($validAcl);        
        $validFingerprint = $this->_computeAclFingerprint($validAclComparable);
        
        $users = $this->_getUsersWithInvalidAcl($validFingerprint);
        foreach ($users as $user) {
            // get acl 
            $usersAcl = unserialize($user->acl);
            
            $usersAclComparable = \Helpers\Inflectors::flatten($usersAcl);            
            $addList = array_diff($validAclComparable, $usersAclComparable);
            $removeList = array_diff($usersAclComparable, $validAclComparable);
            
            $data = array(
                        'acl'               =>  serialize($this->_updateAcl($usersAcl, $addList, $removeList)),
                        'acl_fingerprint'   =>  $validFingerprint
            );
            
            if ($this->updateUser($data, $user->user_id)) {
                $res++;
            }
            
        }
        
        return $res;
        
        
        
    }
   

}