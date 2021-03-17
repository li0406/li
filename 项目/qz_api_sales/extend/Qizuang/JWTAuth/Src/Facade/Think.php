<?php
/**
 * Created by PhpStorm.
 * User: mcj
 * Date: 2018/8/13
 * Time: 9:43
 */
namespace Qizuang\JWTAuth\Src\Facade;

use Qizuang\JWTAuth\Src\Component\Think as ThinkCpt;
use Qizuang\JWTAuth\Src\Component\Singleton\SingletonInterface;
use Qizuang\JWTAuth\Src\Component\Singleton\Singleton;

/**
 * @see \Qizuang\JWTAuth\Src\Component\Auth
 * @method mixed config($token = '') static 配置方法
 */

class Think implements SingletonInterface{

    use Singleton;

    public static function getObj()
    {
        return  new ThinkCpt();
    }
}