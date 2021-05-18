<?php

namespace src\lib;

// constants
define('WITHSCORES', true);
define('REDIS_CONNECTION', 'localhost'); 
define('REDIS_NAMESPACE', 'db:'); 
define('REDIS_DB', 1);
define('REDIS_AUTH', '');

class RRedis extends \Redis {

    CONST TIME_SHORT = 60;
    CONST TIME_MEDIUM = 120;
    CONST TIME_LONG = 300;

    /**
     *
     * @var Boolean 
     */
    private $connStatus = false;

    /**
     * 
     * @param String $db
     * @return boolean
     */
    public function __construct($db = 0) {
        if (class_exists('Redis')) {
            try {
                $this->connect(REDIS_CONNECTION);
                if (defined('REDIS_AUTH')) {
                    $this->auth(REDIS_AUTH);
                }
                $this->select($db === 0 ? REDIS_DB : $db);
                $this->connStatus = true;
            } catch (\RedisException $e) {
                $this->connStatus = false;
            }
        }
    }

    /**
     * 
     * @param String $method
     * @param Mixed $arguments
     * @return Function Call
     */
    public function __call($method, $arguments) {
        if (method_exists($this, $method) && $this->connStatus === true) {
            return call_user_func_array([$this, $method], $arguments);
        } else {
            return false;
        }
    }

    /**
     * 
     * @param String $key
     * @param Mixed | Array | String $val
     * @param Integer $timeout in Seconds
     * @return type
     */
    public function set($key, $val, $timeout = NULL, $opt = NULL) {
        $value = (is_array($val)) ? json_encode($val) : $val;
        if ($timeout === NULL) {
            $timeout = self::TIME_SHORT;
        }
        return parent::set($key, $value, ['nx', 'ex' => $timeout]);
    }

    /**
     * 
     * @param String $key
     * @return Mixed | Array | String
     */
    public function get($key) {
        $parent = parent::get($key);
        $data = json_decode($parent, true);
        return (json_last_error() === JSON_ERROR_NONE) ? $data : $parent;
    }

    /**
     * 
     * @param String $key
     * @return Boolean
     */
    public function exists($key, ...$other_keys) {
        if ($this->connStatus === true) {
            return parent::exists($key, ...$other_keys);
        }
        return false;
    }

    /**
     * 
     * @param String $key
     * @return Boolean
     */
    public function del($key, ...$other_keys) {
        if ($this->connStatus === true) {
            return parent::del($key, ...$other_keys);
        }
        return false;
    }

    /**
     * Close the connection when class closure 
     */
    function __destruct() {
        $this->close();
    }

}

?>