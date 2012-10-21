<?php
namespace redis\orm;

/**
 * Main ORM entry point : contains all mappers
 */
class Layer
{

    /**
     * List of mappers instances
     * @var array
     */
    protected $_mappers;

    /**
     * The Redis clientF
     * @var Predis\Client
     */
    protected $_client;

    /**
     * Initialize the ORM layer
     * @param \Predis\Client $client
     */
    public function __construct(\Predis\Client $client, array $mappers = NULL)
    {
        $this->_client = $client;
        if (!empty($mappers)) {
            $this->_mappers = $mappers;
        } else {
            $this->_mappers = array();
        }
    }

    /**
     * Gets the Redis client
     * @return Predis\Client
     */
    public function getClient()
    {
        return $this->_client;
    }

    /**
     * Gets the specified mapper
     * @param string $name
     * @return Mapper
     * @throws \OutOfBoundsException
     */
    public function getMapper($name)
    {
        if (isset($this->_mappers[$name])) {
            if (!($this->_mappers[$name] instanceof Mapper)) {
                $this->_mappers[$name] = new Mapper(
                        $this->_mappers[$name], $this
                );
            }
            return $this->_mappers[$name];
        } else {
            throw new \OutOfBoundsException(
                'Undefined mapper : ' . $name
            );
        }
    }

    /**
     * Sets a mapper configuration
     * @param string $name
     * @param string|Mapper $definition
     * @return Layer
     */
    public function setMapper($name, $definition)
    {
        $this->_mappers[$name] = $definition;
        return $this;
    }

}