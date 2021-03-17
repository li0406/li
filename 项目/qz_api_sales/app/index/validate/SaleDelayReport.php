<?php

namespace app\index\validate;

use think\Validate;

class SaleDelayReport extends Validate
{
    protected $rule = [
        
    ];

    protected $message = [
        "company_id.require" => "请输入公司ID",
        "company_name.require" => "请输入公司名称",
        "cs.require" => "城市不能为空",
        "city_name.require" => "城市名称不能为空",
        "current_vip_id.require" => "请先选择本次会员时间",
        "current_vip_start.require" => "请先选择本次会员时间",
        "current_vip_end.require" => "请先选择本次会员时间",
        "report_id.require" => "请先选择本次报备合同",
        "report_cooperation_type.require" => "请先选择本次报备合同",
        "current_contract_start.require" => "请先选择本次报备合同",
        "current_contract_end.require" => "请先选择本次报备合同",
        "delay_promise_orders.require" => "请先填写延期承诺单量",
        "delay_real_days.require" => "请先填写实际延期天数",
        "delay_contract_start.require" => "请先选择延期合同开始时间",
        "delay_contract_end.require" => "请先选择延期合同结束时间",
    ];

    /**
     * 添加三维家报备记录
     * @return $this
     */
    public function sceneAddDelay()
    {
        return $this->only([
                "company_id", "company_name", "cs", "city_name",
                "current_vip_id", "current_vip_start", "current_vip_end",
                "report_id", "report_cooperation_type", "current_contract_start", "current_contract_end",
                "delay_promise_orders", "delay_real_days", "delay_contract_start", "delay_contract_end"
            ])
            ->append("company_id", "require")
            ->append("company_name", "require")
            ->append("current_vip_id", "require")
            ->append("current_vip_start", "require")
            ->append("current_vip_end", "require")
            ->append("report_id", "require")
            ->append("report_cooperation_type", "require")
            ->append("current_contract_start", "require")
            ->append("current_contract_end", "require")
            ->append("delay_promise_orders", "require")
            ->append("delay_real_days", "require")
            ->append("delay_contract_start", "require")
            ->append("delay_contract_end", "require");
    }
}
