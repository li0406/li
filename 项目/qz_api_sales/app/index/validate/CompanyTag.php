<?php

/**
 * 装修公司标识
 */

namespace app\index\validate;

use think\Validate;

class CompanyTag extends Validate
{

    protected $rule = [
    ];

    protected $message = [
        "tag.require" => "请选择标识",
        "company_id.require" => "缺少装修公司",
        "city_name.require" => "缺少城市名",
        "cs.require" => "缺少城市编号",
    ];

    public function sceneAdd()
    {
        return $this->only([
            "tag", "company_id", "city_name", "cs",
        ])
            ->append("tag", "require")
            ->append("company_id", "require")
            ->append("city_name", "require")
            ->append("cs", "require");
    }
}