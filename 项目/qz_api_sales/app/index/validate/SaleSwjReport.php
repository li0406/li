<?php
// +----------------------------------------------------------------------
// | SaleSwjReport 报备三维家验证
// +----------------------------------------------------------------------
namespace app\index\validate;

use think\Validate;

class SaleSwjReport extends Validate
{
    protected $rule = [
        'company_name' => 'require|length:1,30',
        'company_contact' => 'require|length:1,20',
        'company_contact_phone' => ['require','regex' => '/^(13|14|15|16|17|18|19)[0-9]{9}$/'],
        'current_contract_start' => 'require|dateFormat:Y-m-d',
        'current_contract_end' => 'require|dateFormat:Y-m-d',
        'current_contract_amount' => 'require|integer|egt:0',
        'status' => 'require|in:1,2,3,4,5,6,7,8',
        'account' => 'require|length:1,50',
        'company_rolers' => 'require|length:1,50',
        'company_tag' => 'require|length:1,50',
        'section' => 'require|length:1,50',
    ];

    protected $message = [
        'company_name.require' => '请输入公司名称',
        'company_name.length' => '公司名称过长',
        'company_contact.require' => '请输入姓名',
        'company_contact.length' => '联系人姓名过长',
        'company_contact_phone.require' => '请输入电话',
        'company_contact_phone.regex' => '请输入正确的手机号',
        'current_contract_start.require' => '请选择时间',
        'current_contract_start.dateFormat' => '请选择时间',
        'current_contract_end.require' => '请选择时间',
        'current_contract_end.dateFormat' => '请选择时间',
        'current_contract_amount.require' => '请输入金额',
        'current_contract_amount.integer' => '金额输入错误',
        'current_contract_amount.egt' => '金额输入错误',
        'status.require' => '未选择审核状态',
        'status.in' => '未选择审核状态',
        'account.require' => '请输入账号',
        'account.length' => '账号过长',
        'company_tag.require' => '请输入标签',
        'company_tag.length' => '标签内容过长',
        'company_rolers.require' => '请输入角色',
        'company_rolers.length' => '角色过长',
        'section.require' => '请输入部门',
        'section.length' => '部门过长',
    ];

    /**
     * 添加三维家报备记录
     * @return $this
     */
    public function sceneAddswj()
    {
        return $this->only(
            ['company_name', 'section', 'company_contact', 'company_contact_phone', 'account', 'current_contract_amount', 'current_contract_start', 'current_contract_end', 'company_id', 'status']
        );
    }
}
