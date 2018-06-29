<?php

namespace library\db;

use library\util\Conf;

class Redis {

    private static $redis;

    private $_alias = '';
    private $_count = 1;
    private $_poolConf = [];
    private $_redisPool = [];

    public static function getInstance(){
        if(!(self::$redis instanceof self)){
            self::$redis = new self;
        }
        return self::$redis;
    }

    /**
     * 
     * @param string  $alias   [description]
     * @param integer $master  [description]
     */
    public function setPoolConnect($alias, $master = 1, $connect) {
        $newAlias = $alias . '_' . $master;
        if (isset($this->_redisPool[$newAlias])) {
            $this->_redisPool[$newAlias] = $connect;
        }
    }

    /**
     * 
     * @param  string  $alias  [description]
     * @param  integer $master [description]
     * @return object          [description]
     */
    public function connect($alias, $master = 1){
        $newAlias = $alias.'_'.$master;
        if(!isset($this->_redisPool[$newAlias])) {
            if($master) {
                $conf = Conf::get('redis.' . $alias . '.write');
            }else{
                $conf = Conf::get('redis.' . $alias . '.read');
            }
            if(empty($conf)){
                return NULL;
            }

            $this->_poolConf = $conf;
            $this->_alias = $newAlias;
            $this->_count = count($conf);
            if (isset($conf['host']) && isset($conf['port'])) {
                $this->_redisPool[$newAlias] = $this->_connect($conf['host'], $conf['port']);
            } else {
                $this->_redisPool[$newAlias] = [];
            }
        }

        return is_array($this->_redisPool[$newAlias]) ? $this : $this->_redisPool[$newAlias];
    }

    /**
     * 
     * @param  int $hashId [description]
     * @return resource         [description]
     */
    public function getShardingSingle($hashId) {
        if (!isset($this->_redisPool[$this->_alias][$hashId])) {
            if (isset($this->_poolConf[$hashId])) {
                $this->_redisPool[$this->_alias][$hashId] = $this->_connect($this->_poolConf[$hashId]['host'], $this->_poolConf[$hashId]['port']);
            } else {
                return null;
            }
        }
        return $this->_redisPool[$this->_alias][$hashId];
    }

    /**
     * 
     * @param  string  $key   [description]
     * @param  boolean $isSub [description]
     * @return int         [description]
     */
    public function getShardId($key, $isSub = true) {
        if($isSub) {
            $key = substr($key, -1);
        }
        if (!is_numeric($key)) $key = base_convert($key, 16, 10);
        return ($key % $this->_count);
    }

    /**
     * 
     * @param  string $key [description]
     * @return resource      [description]
     */
    public function getRedis($key) {
        $shardId = $this->getShardId($key, false);
        return $this->getShardingSingle($shardId);
    }
    
    /**
     * 
     * @param  string $method [description]
     * @param  array $args   [description]
     * @return bool|string|array         [description]
     */
    public function __call($method, $args) {
        $redis = $this->_redisPool[$this->_alias];
        if (is_array($redis)) {
            $hashId = $this->getShardId($args[0]);
            $redis = $this->getShardingSingle($hashId);
        }
        
        $ret = call_user_func_array(array($redis, $method), $args);
        return $ret;
    }
    
    /**
     * 
     * @param  string  $alias  [description]
     * @param  integer $master [description]
     * @return resource          [description]
     */
    private function _connect($host, $port){
        try {
            $redis = new \Redis();
            $ret = $redis->connect($host, $port, 0.2);
            if (false === $ret) {
                return null;
            }
            $redis->setOption(\Redis::OPT_READ_TIMEOUT, 0.1);
            return $redis;
        } catch(Exception $e) {
            return null;
        }
    }
}
