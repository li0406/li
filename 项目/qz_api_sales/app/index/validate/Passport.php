<?php
// +----------------------------------------------------------------------
// | Passport 管理员登录验证
// +----------------------------------------------------------------------
namespace app\index\validate;

use think\Validate;

class Passport extends Validate
{
    protected $rule = [
        'user_name' => 'require|length:1,25',
        'user_pwd' => 'require|length:1,25',
    ];

    protected $message = [
        'user_name.require' => '账号不能为空',
        'user_name.length' => '账号长度为1-25字',
        'user_pwd.require' => '密码不能为空',
        'user_pwd.length' => '密码长度为1-25字',
    ];

    protected $scene = [
        'account_login' => ['user_name', 'user_pwd'],
    ];
}