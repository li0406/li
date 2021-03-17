<?php
/**
 * 统计管理验证
 */

namespace app\index\validate;

use think\Validate;

class Statistics extends Validate
{
    protected $rule = [
        'cs' => 'number',
    ];

    protected $field = [
        'cs' => '城市',
        'company_id' => '装修公司id',
    ];

    protected $message = [
        "city.require" => '批量查询城市用英文“,”隔开',
        "begin.require" => '请选择开始时间',
        "begin.date" => '请选择开始时间',
        "end.require" => '请选择结束时间',
        "end.date" => '请选择结束时间'
    ];

    protected $scene = [
        'getUnreadList' => ['cs'],
    ];

    public function sceneCityOrder() {
        return $this->append("city", "require")
            ->append("begin", "require")
            ->append("begin", "date")
            ->append("end", "require");
    }

    public function sceneGetList() {
        return $this->only([
                "cs", "qdstart", "qdend"
            ])
            ->append("cs", "number")
            ->append("qdstart", function($qdstart, $data){
                $qdend = check_variate($data["qdend"]);
                if (!empty($qdstart) && !empty($qdend)) {
                    if (strtotime($qdstart) < strtotime("-1 year", strtotime($qdend))) {
                        return "请选择一年以内时间段";
                    }
                }

                return true;
            });
    }
}