<?php

namespace Common\Behavior;
use Think\Behavior;
use Think\Storage;

/**
 *
 */
class IPLimitBehavior extends Behavior
{
    # ....
    /**
     * @inheritDoc
     */
    public function run(&$params)
    {

        $ip = get_client_ip(0, true);

        $write = C("IP_WHITE_LIST");
        if (!in_array($ip,$write)) {
            $host = C("REDIS_HOST");
            $port = C("REDIS_PORT");
            $lua = self::luaScript();
            $redis = new \Redis();
            $redis->connect($host,$port);
            $result = $redis->eval($lua,array($ip,10,60),1);
            $redis->close();
            if (!$result) {
                header("HTTP/1.1 429 Too Many Requests ");
                $tmp = T('Common@Public/verify');
                echo Storage::read($tmp,'html');
                die();
            }
        }
    }

    public function luaScript()
    {
        $lua = '
                local key = "rate.limit:" .. KEYS[1]
                local limit = tonumber(ARGV[1])
                local expire_time = ARGV[2]

                local is_exists = redis.call("EXISTS", key)
                if is_exists == 1 then
                    if redis.call("INCR", key) > limit then
                        return 0
                    else
                        return 1
                    end
                else
                    redis.call("SET", key, 1)
                    redis.call("EXPIRE", key, expire_time)
                    return 1
                end
            ';

        return $lua;
    }
}