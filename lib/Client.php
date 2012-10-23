<?php
namespace redis\orm;

defined('CRLF') OR define('CRLF', "\r\n");

class ClientError extends Exception
{
    
}

class ClientConnectionError extends ClientError
{
    
}

class ClientIOError extends ClientError
{
    
}

class ClientRedisError extends ClientError
{
    
}

class Client
{

    protected $_socket;
    protected $_responses = 0;
    protected $_stack = array();

    /**
     * Initialize a redis connection
     * @param string $dsn
     * @throws ClientConnectionError
     */
    public function __construct($dsn = 'tcp://localhost:6379')
    {
        $this->_socket = stream_socket_client(
            $dsn, $code, $error, 2, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT
        );
        if ($this->_socket === false) {
            throw new ClientConnectionError(
                $error, $code
            );
        }
    }

    /**
     * Close the redis connection
     */
    public function __destruct()
    {
        fclose($this->_socket);
    }

    /**
     * Reads the redis response
     * @return mixed
     * @throws ClientError
     */
    public function read()
    {
        if (!empty($this->_stack))
            $this->flush();
        if ($this->_responses === 0) {
            throw new ClientError(
                'No pending response found'
            );
        }
        if ($this->_responses === 0) {
            $response = $this->_read();
        } else {
            $response = new \SplFixedArray($this->_responses);
            for ($i = 0; $i < $this->_responses; $i++) {
                $response->offsetSet($i, $this->_read());
            }
        }
        $this->_responses = 0;
        return $response;
    }

    /**
     * Flushing pending requests
     * @return \redis\orm\Client
     * @throws ClientError
     * @throws ClientIOError
     */
    public function flush()
    {
        $size = count($this->_stack);
        if ($size === 0) {
            throw new ClientError(
                'No pending requests'
            );
        }
        $this->_responses += $size;
        $buffer = implode(null, $this->_stack);
        $blen = strlen($buffer);
        for ($written = 0; $written < $blen; $written += $fwrite) {
            $fwrite = fwrite($this->_socket, substr($buffer, $written));
            if ($fwrite === false || $fwrite <= 0) {
                throw new ClientIOError('Failed to write entire command to stream');
            }
        }
        return $this;
    }

    /**
     * Run a redis command
     * @param string $method
     * @param array $args
     * @return \redis\orm\Client
     */
    public function __call($method, $args)
    {
        $this->_stack[] = $this->_cmd($method, $args);
        return $this;
    }

    /**
     * Sends the specified command to Redis
     * @param type $command
     * @return \redis\orm\Client
     * @throws ClientIOError
     */
    protected function _write($command)
    {
        for ($written = 0; $written < strlen($command); $written += $fwrite) {
            $fwrite = fwrite($this->_socket, substr($command, $written));
            if ($fwrite === FALSE || $fwrite <= 0) {
                throw new ClientIOError(
                    'Failed to write entire command to stream'
                );
            }
        }
        return $this;
    }

    /**
     * Reads the redis pending response
     * @return string|integer|boolean|array
     * @throws ClientIOError
     * @throws ClientRedisError
     */
    protected function _read()
    {
        $reply = trim(fgets($this->_socket, 512));
        if ($reply === false) {
            throw new ClientIOError(
                'Network error - unable to read header response'
            );
        }
        switch ($reply[0]) {
            case '+': // inline reply
                $reply = substr($reply, 1);
                return ( strcasecmp($reply, 'OK') === 0 ) ?
                    true : $reply
                ;
                break;
            case '-': // error
                throw new ClientRedisError(trim(substr($reply, 4)));
                break;
            case ':': // inline numeric
                return intval(substr($reply, 1));
                break;
            case '$': // bulk reply
                $size = intval(substr($reply, 1));
                if ($size === -1)
                    return null;
                $reply = '';
                if ($size > 0) {
                    $read = 0;
                    do {
                        $block_size = ($size - $read) > 1024 ? 1024 : ($size - $read);
                        $r = fread($this->_socket, $block_size);
                        if ($r === FALSE) {
                            throw new ClientIOError(
                                'Failed to read bulk response from stream'
                            );
                        } else {
                            $read += strlen($r);
                            $reply .= $r;
                        }
                    } while ($read < $size);
                }
                fread($this->_socket, 2); /* discard crlf */
                return $reply;
                break;
            case '*': // multi-bulk reply
                $size = intval(substr($reply, 1));
                if ($size === -1)
                    return null;
                if ($size === 0)
                    return array();
                $reply = new \SplFixedArray($size);
                for ($i = 0; $i < $size; $i++) {
                    $reply->offsetSet(
                        $i, $this->_read()
                    );
                }
                break;
        }
        throw new ClientRedisError(
            'Undefined protocol response type'
        );
    }

    /**
     * Builds the specified command
     * @param string $method
     * @param array $args
     * @return string
     */
    protected function _cmd($method, $args)
    {
        $response =
            '*' . (count($args) + 1) . CRLF
            . '$' . strlen($method)
            . strtoupper($method);
        foreach ($args as $arg) {
            $response .= CRLF . '$' . strlen($arg) . CRLF . $arg;
        }
        return $response . CRLF;
    }

}