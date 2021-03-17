<?php

/**
 * @Author: lovenLiu
 * @Email: lypeng9205@163.com
 * @Date:   2019-05-15 10:57:30
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2019-05-15 13:42:48
 */

namespace app\index\validate;

use think\Validate;

class CompanyConsultValidate extends Validate {

    protected $rule = [
    ];

    protected $message = [
        "consult_id.require" => "ID不能为空",
        "deal_man.require" => "请输入跟踪人员",
        "deal_type.require" => "请选择跟踪方式",
        "success_level.require" => "请选择意向等级",
        "communication.require" => "请输入谈话内容",
        "status.require" => "请选择合作状态",
        "status.gt" => "请选择合作状态",
    ];

    public function sceneAddRecord() {
        return $this->only([
                "consult_id", "deal_man", "deal_type", "success_level", "communication", "status"
            ])
            ->append("consult_id", "require")
            ->append("deal_man", "require")
            ->append("deal_type", "require")
            ->append("success_level", "require")
            ->append("communication", "require")
            ->append("status", "require|gt:1");
    }
}