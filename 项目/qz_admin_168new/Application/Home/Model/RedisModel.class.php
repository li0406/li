<?php
/**
 *  配置表
 */
namespace Common\Model;
use Think\Model;
class RedisModel extends Model
{
    protected $autoCheckFields = false;

    /**
     * 连接redis
     * @param  str $group 分组 nosql1
     * @return $this->redis redis连接对象
     */
    public function connect($group)
    {
        if (empty($group)) {
            return '必要参数不能为空';
        }

        $config = array();
        if ('nosql1' == $group) {
            /*$config['host'] = OPNC('redis_host_nosql1');
            $config['port'] = OPNC('redis_port_nosql1');*/
            $config['host'] = '127.0.0.1';
            $config['port'] = '6379';
        }
        //连接redis
        $Redis  = new \Redis();
        if ($Redis->connect($config['host'], $config['port'], 1)) {
            $Redis->select(0); //选择库
        }else{
            return $group.':redis连接失败';
        }
        $this->redis = $Redis;
    }

    /**
     * redis 获取建列表
     * @param  str $key   支持的方式 "key" 一个 "key*"" 开始匹配   "*"" 所有
     * @return result
     */
    public function key_get($key){
        if (empty($key)) {
            return '必要参数不能为空!';
        }
        if (empty($this->redis)) {
            return '未连接redis,请用connect方法连接!';
        }
        return $this->redis->keys($key);
    }

    public function get($key){
        if (empty($key)) {
            return '必要参数不能为空!';
        }
        return $this->redis->get($key);
    }

    public function del($key){
        if (empty($key)) {
            return '必要参数不能为空!';
        }
        return $this->redis->del($key);
    }

    public function set($key,$value,$expire = null){
        if (empty($key)) {
            return '必要参数不能为空!';
        }
        return $this->redis->set($key,$value,$expire);
    }
}