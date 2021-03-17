<?php

namespace Home\Model\Logic;


use Redis;

class RedisLogicModel
{
    protected static $redis_obj = null;

    public function __construct()
    {
        if (self::$redis_obj == null) {
            self::$redis_obj = new Redis();
            $host = C('REDIS_HOST', null, '127.0.0.1');
            $port = C('REDIS_PORT', null, '6379');
            $timeout = C('REDIS_TIMEOUT', null, 0);

            if (self::$redis_obj->connect($host, $port, $timeout) === false) {
                throw new \Think\Exception('redis 连接失败');
            }
        }
    }

    public function key_get($key)
    {
        return self::$redis_obj->keys($key);
    }

    public function get($key)
    {
        return self::$redis_obj->get($key);
    }

    public function del($key)
    {
        return self::$redis_obj->del($key);
    }

	public function hDel( $key, $hashKey1) {
		return self::$redis_obj->hDel($key, $hashKey1);
	}


}