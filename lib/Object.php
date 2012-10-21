<?php

namespace redis\orm;

class Object {
    
    /**
     * The object mapper instance
     * @var Mapper
     */
    protected $_mapper;
    protected $_id = null;
    protected $_data = null;
    
    public function __construct( Mapper $mapper, $data = null ) {
        $this->_mapper = $mapper;
        $this->_data = $data;
    }
    
    /**
     * Gets the entry identifier
     * @return integer
     */
    public function getId() {
        if ( !$this->_id ) {
            $this->_id = $this->_mapper->getNextId();
        }
        return $this->_id;
    }
    
    public function __get( $property ) {
        if ( $method = $this->_mapper->getPropertyMethod($property) ) {
            return $this->$method();
        }
    }
}
