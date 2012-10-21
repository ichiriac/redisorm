<?php
/**
 * Register redis\orm namespace autoload
 */
spl_autoload_register(function($class) {
    if (substr_compare($class, 'redis\\orm\\', 0, 10, false) === 0 ) {
        $filename = __DIR__ . '/lib/' . strtr(substr($class, 10), '\\', '/') . '.php';
        $result = require_once( $filename );
        return ($result !== false);
    }
    return false;
});