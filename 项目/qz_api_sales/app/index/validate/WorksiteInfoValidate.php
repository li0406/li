<?php

namespace app\index\validate;

use think\Validate;

class WorksiteInfoValidate extends Validate {

    protected $rule = [
    ];

    protected $message = [
        "id.require" => "ID不能为空",
        "step.require" => "请选择施工阶段",
        "step.notIn" => "请选择施工阶段",
        "content.require" => "请填写施工详情",
    ];

    public function sceneEdit() {
        return $this->only([
                "id", "step", "content"
            ])
            ->append("id", "require")
            ->append("step", "require|notIn:0")
            ->append("content", "require");
    }
    public function sceneAdd() {
        return $this->only([
                 "step", "content"
            ])
            ->append("step", "require|notIn:0")
            ->append("content", "require");
    }
}