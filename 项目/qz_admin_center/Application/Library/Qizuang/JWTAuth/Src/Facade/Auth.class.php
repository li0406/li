<?php
/**
 * Created by PhpStorm.
 * User: mcj
 * Date: 2018/8/9
 * Time: 10:10
 */
namespace Library\Qizuang\JWTAuth\Src\Facade;

use Library\Qizuang\JWTAuth\Src\Component\Auth as AuthCpt;
use Library\Qizuang\JWTAuth\Src\Component\Singleton\SingletonInterface;
use Library\Qizuang\JWTAuth\Src\Component\Singleton\Singleton;

class Auth implements SingletonInterface {

    use Singleton;

    public static function getObj()
    {
        return  new AuthCpt();
    }
}
