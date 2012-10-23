<?php

require_once('../autoload.php');

use redis\orm;

/**
 * @repository users
 */
class User extends orm\Object
{

    /**
     * @property string     
     */
    protected $name;

    /**
     * @property string
     * @index
     */
    protected $email;

    /**
     * @property string
     */
    protected $password;

    public function setPassword($password)
    {
        $this->password = md5($password);
    }

}

class UserMapper extends orm\Mapper
{
    /**
     * Find a user from the specified email
     * @param string $email
     * @return User
     */
    public function findByEmail( $email ) {
        return $this->findFirst(
            'users', array(
                'email' => $email
            )
        );
    }
    
    /**
     * Find a list of users
     * @param char $letter
     * @param int $page
     * @param int $size
     * @return ???collection
     */
    public function listByName( $letter, $page = 0, $size = 32 ) {
        return $this->find(
            'users', array(
                'name' => $letter . '%'
            )
        )->limit($page, $size)->orderAsc('name');
    }
}
