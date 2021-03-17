<?php
/**
 * Created by PhpStorm.
 * User: mcj
 * Date: 2018/8/13
 * Time: 9:43
 */
namespace Library\Qizuang\JWTAuth\Src\Facade;

use Library\Qizuang\JWTAuth\Src\Component\Think as ThinkCpt;
use Library\Qizuang\JWTAuth\Src\Component\Singleton\SingletonInterface;
use Library\Qizuang\JWTAuth\Src\Component\Singleton\Singleton;

class Think implements SingletonInterface{

    use Singleton;

    public static function getObj()
    {
        return  new ThinkCpt();
    }
}