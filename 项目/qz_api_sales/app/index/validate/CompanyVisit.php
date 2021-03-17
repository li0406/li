<?php

namespace app\index\validate;

use think\Validate;

class CompanyVisit extends Validate {

    protected $rule = [
    ];

    protected $message = [
        'order_id.require' => '订单ID参数非法',
        'company_ids.require' => '请选择要求回访的装修公司名称',
        'visit_step.require' => '请选择回访阶段',
        'visit_step.between' => '请选择回访阶段',
        'visit_need.require' => '请填写需要回访的内容',

        'id.require' => 'ID参数非法',
        'push_company_ids.require' => '请选择要推送的装修公司名称',
        'visit_content.require' => '请填写回访反馈内容',
    ];

    public function sceneAdd() {
        return $this->only([
                'order_id', 'company_ids', 'visit_step', 'visit_need'
            ])
            ->append('order_id', 'require')
            ->append('company_ids', 'require')
            ->append('visit_step', 'require|between:1,4')
            ->append('visit_need', 'require');
    }

    public function scenePush() {
        return $this->only([
            'id', 'push_company_ids', 'visit_content'
        ])
            ->append('id', 'require|number')
            ->append('push_company_ids', 'require')
            ->append('visit_content', 'require');
    }


    public function sceneNewPush() {
        return $this->only([
            'order_id', 'push_company_ids', 'visit_content'
        ])
            ->append('order_id', 'require|number')
            ->append('push_company_ids', 'require')
            ->append('visit_content', 'require');
    }

    // 验证查询时间
    public function validateSearchDate($input){
        if (empty($input["date_begin"]) && !empty($input["date_end"])) {
            return "请先选择开始时间";
        }

        if (!empty($input["date_begin"]) && empty($input["date_end"])) {
            return "请先选择结束时间";
        }

        if (!empty($input["date_begin"]) && !empty($input["date_end"])) {
            if ($input["date_begin"] > $input["date_end"]) {
                return "结束时间不能小于开始时间";
            }
        }

        return true;
    }
}