<?php

namespace Acl\Security;

use \Nette\Security as NS;

class CMSAuthenticator extends \Nette\Object implements NS\IAuthenticator {

    public $connection;

    private $salt = 'kUdv5p*&';
    
    public function __construct(\DibiConnection $connection) {
        $this->connection = $connection;
    }

    public function getSalt() {
        return $this->salt;
    }
    
    public function authenticate(array $credentials) {
        list($login, $password) = $credentials;

        $row = $this->connection->fetch("SELECT * FROM [:core:users] WHERE [login] = %s", $login);

        if (!$row) {
            throw new NS\AuthenticationException('Uživatel nenalezen.');
        }

        if ($row->password !== sha1($this->salt.$password)) {
            throw new NS\AuthenticationException('Chybné heslo.');
        }

        // get acl
        $acl = $row->acl;
        if (empty($acl)) {
            // get acl from role template
            $acl = $this->connection->fetchSingle('SELECT * FROM [:core:roles] WHERE [role] = %s', $row->role);
            if (empty($acl)) {
                throw new NS\AuthenticationException('Neznámá role');
            }
        }
        
        
        
        $data = array(
                'login'     => $row->login,
                'userData'  => $row,
                'role'      => new \Acl\Roles\Role($row->role, $row->user_id, \Utils\MultiValues::unserialize($acl))
                //'role'      => new \Acl\Roles\Role($row->role, $row->user_id, $fakeAcl)
            );
        
        return new NS\Identity($row->user_id, $row->role, $data);
    }

}