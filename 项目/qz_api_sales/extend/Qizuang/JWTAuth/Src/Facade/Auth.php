<?php
/**
 * Created by PhpStorm.
 * User: mcj
 * Date: 2018/8/9
 * Time: 10:10
 */
namespace Qizuang\JWTAuth\Src\Facade;

use Qizuang\JWTAuth\Src\Component\Auth as AuthCpt;
use Qizuang\JWTAuth\Src\Component\Singleton\SingletonInterface;
use Qizuang\JWTAuth\Src\Component\Singleton\Singleton;

/**
 * @see \Qizuang\JWTAuth\Src\Component\Auth
 * @method mixed check($token = '') static 验证Token
 * @method mixed getToken($data = []) static 设置Token
 * @method mixed killToken($data = []) static 注销token
 */

class Auth implements SingletonInterface {

    use Singleton;

    public static function getObj()
    {
        return  new AuthCpt();
    }
}
