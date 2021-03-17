<?php
/**
 * Created by PhpStorm.
 * User: mcj
 * Date: 2018/8/13
 * Time: 9:45
 */

namespace Qizuang\JWTAuth\Src\Component;



class Think
{

    protected $version = 5;


    /**
     * 缓存方法
     * @param $name
     * @param string $value
     * @param null $options
     * @return mixed
     */
    public function cache($name, $value = '', $options = null)
    {
        $param = func_get_args();
        switch ($this->version) {
            case 3 :
                return call_user_func_array('S', $param);
            case 5:
                return call_user_func_array('cache', $param);
        }
    }


    /**
     * 配置方法
     * @param null $name
     * @param null $value
     * @param null $default
     * @return mixed
     */
    public function config($name = null, $value = null, $default = null)
    {
        $param = func_get_args();
        switch ($this->version) {
            case 3 :
                return call_user_func_array('C', $param);
            case 5:
                return call_user_func_array('config', $param);
        }
    }

}