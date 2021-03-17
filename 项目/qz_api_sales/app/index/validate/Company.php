<?php

/**
 * 装修公司
 */

namespace app\index\validate;

use think\Validate;

class Company extends Validate
{

    protected $rule = [
        'tel' => 'number|length:11|/^1\d{10}$/',
    ];

    protected $field = [
        "tel"=>"手机号"
    ];

    protected $message = [
        "ssid" => "图形验证码标识不能为空",
        "verify" => "图形验证码不能为空",
        "account" => "用户名不能为空",
        "cs" => "城市不能为空",
        "qx" => "区县不能为空",
        "password" => "密码不能为空",
        "repassword" => "确认密码不能为空",
        "jc" => "公司简称不能为空",
        "qc" => "公司全称不能为空",
        "name" => "联系人不能为空",
//        "tel" => "手机号不能为空",
    ];

    public function sceneAddCompany()
    {
        return $this->only([
            "account", "cs", "qx", "password","repassword","jc","qc","name","tel"
        ])
            ->append("ssid", "require")
            ->append("verify", "require")
            ->append("account", "require")
            ->append("cs", "require")
            ->append("qx", "require")
            ->append("password", "require")
            ->append("repassword", "require")
            ->append("jc", "require")
            ->append("qc", "require")
            ->append("name", "require")
            ->append("tel", "require");
    }
}