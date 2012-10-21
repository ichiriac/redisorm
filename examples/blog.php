<?php

require_once('../autoload.php');

use redis\orm;

class User extends orm\Object {
    protected $name;
    protected $email;
    protected $password;
    
    public function setPassword( $password ) {
        $this->password = md5( $password );
    }
}
$start = microtime(true);
for($i = 0; $i < 10; $i++) {
$ref = new ReflectionClass( 'User' );
}
echo round(microtime(true) - $start, 3);