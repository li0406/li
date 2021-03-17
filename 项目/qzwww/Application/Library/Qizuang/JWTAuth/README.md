#tp-jwt-auth
## 安装 
使用composer管理依赖方式安装
```
composer require machengjun/php-jwt
```
## 环境要求
php:>=5.4
thinkphp:>=5.0
## 配置
```
    'jwt_secret' => 'rolling in the deep'//加密秘钥
    'jwt_use_limit' => '3600'//token过期时间
    'jwt_refresh_limit' => '604800'//token可以用时间，（可刷新）
  ```  
## 使用案例

```
<?php
/**
 * Created by PhpStorm.
 * User: mcj
 * Date: 2018/8/10
 * Time: 11:46
 */
namespace app\index\controller;

use Machengjun\JWTAuth\Src\Facade\Auth;
use Machengjun\JWTAuth\Src\JWTAuthException;

class Hello
{
    //获取token,data为用户自定义数据
    public function getToken(){
        $data = [
            'user_id'=>12
        ];
        try{
            $res = Auth::getToken($data);
        }catch (JWTAuthCode $e){
            echo json_encode(['error_msg'=>'加密出错']);
        }
        dump($res);exit;
    }
    //权限认证
    public function checkToken(){
        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImtpZCI6IlFzMkdJaVRnVldSVUZSV3MifQ.eyJuYmYiOjE1MzQyMzQyNDksImV4cCI6MTUzNDgzOTA0OSwiand0X2lkZSI6IlFzMkdJaVRnVldSVUZSV3MiLCJkYXRhIjp7InVzZXJfaWQiOjEyfX0.pond6EJ59yH9k3MJusVugg7W6hHx1Y_lLGawJBctflY';
        try{
            $res =  Auth::check($token);
        }catch (JWTAuthException $e){
            //token暂时失效，请刷新令牌
            if($e->getCode() === 20001){
                echo json_encode(['error_msg'=>'请刷新token']);
            }else{
                echo json_encode(['error_msg'=>'登录过期，请重新登录']);
            }
        }
        dump($res);
    }

    //刷新令牌
    public function refreshToken(){
        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImtpZCI6IlFzMkdJaVRnVldSVUZSV3MifQ.eyJuYmYiOjE1MzQyMzQyNDksImV4cCI6MTUzNDgzOTA0OSwiand0X2lkZSI6IlFzMkdJaVRnVldSVUZSV3MiLCJkYXRhIjp7InVzZXJfaWQiOjEyfX0.pond6EJ59yH9k3MJusVugg7W6hHx1Y_lLGawJBctflY';
        try{
            $res =  Auth::refreshToken($token);
        }catch (JWTAuthException $e){
            echo json_encode(['error_msg'=>'token不合法']);
        }
        dump($res);
    }
    //注销令牌，账号登出
    public function killToken(){
        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImtpZCI6IlFzMkdJaVRnVldSVUZSV3MifQ.eyJuYmYiOjE1MzQyMzQyNDksImV4cCI6MTUzNDgzOTA0OSwiand0X2lkZSI6IlFzMkdJaVRnVldSVUZSV3MiLCJkYXRhIjp7InVzZXJfaWQiOjEyfX0.pond6EJ59yH9k3MJusVugg7W6hHx1Y_lLGawJBctflY';
        try{
            Auth::killToken($token);
        }catch (JWTAuthException $e){
            echo json_encode(['error_msg'=>'token不合法']);
        }
        echo('logout success');
    }


}
   ```