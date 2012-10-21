<?php

namespace redis\orm;

class Reference {
    protected $_parent;
    
    public function __construct( Object $parent, $class, $id ) {
        $this->_parent = $parent;
    }
    
    /**
     * @return Object
     */
    public function getInstance() {
        
    }
}
