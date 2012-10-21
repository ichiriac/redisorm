<?php

namespace redis\orm;

/**
 * Defines a specific class mapper
 */
class Mapper
{
    protected $_class;
    protected $_layer;
    
    /**
     * 
     * @param type $class
     * @param \redis\orm\Layer $layer
     */
    public function __construct( $class, Layer $layer ) {
        $this->_layer = $layer;
        $this->_class = $class;
    }
    
    /**
     * Allocate and increment an id
     * @return integer
     */
    public function getNextId() {
        return (int)$this->_layer->getClient()->incr('seq.' . $this->_class);
    }
    
    /**
     * 
     * @param array $data
     */
    public function create( array $data ) {
        
    }
}