<?php

namespace Model;

final class UserModel extends BaseModel {

    public function isLoginUnique($login, $userId = NULL) {
        $c = $this->connection->fetchSingle('SELECT COUNT(*) FROM [:core:users] WHERE [login] = %s %if AND [user_id] != %i', $login, ($userId !== NULL), $userId);
        return ($c == 0);
    }
    
    
    public function registerUser($data) {
        $role = $data['role'];
        
        // get acl from template table
        $acl = $this->getModelAcl()->getRolesAcl($role);
        
        $data['acl'] = $acl;
        return $this->connection->query('INSERT INTO [:core:users]', $data);
        
    }
    
    public function updateUser($data, $userId) {
        return $this->connection->query('UPDATE [:core:users] SET', $data, 'WHERE [user_id] = %i', $userId);
    }
    
    public function getUser($userId) {
        return $this->connection->fetch('SELECT * FROM [:core:users] WHERE [user_id] = %i', $userId);
    }
    
    public function getUserDefaults($userId) {
        return $this->connection->fetch('SELECT [login],[email],[role] FROM [:core:users] WHERE [user_id] = %i', $userId);
    }
    
    public function updateUsersAcl($acl, $userId) {
        $data = array(
                    'acl%s'   =>  $acl
        );
        return $this->connection->query('UPDATE [:core:users] SET', $data,' WHERE [user_id] = %i', $userId);
    }
    
    public function deleteUser($userId) {
        return $this->connection->query('DELETE FROM [:core:users] WHERE [user_id] = %i', $userId);
    }

}