<?php
/**
 *
 */

namespace app\index\validate;

use think\Validate;

class ReportCompany extends Validate
{
	protected $rule = [

	];

	protected $field = [
		'company_id' => '公司id',
		'company_name' => '公司名称',
		'user_id' => '旧公司会员ID',
		'cs' => '城市',
		'qx' => '区县',
		'address' => '地址',
		'is_new' => '是否新用户',
		'intention' => '意向',
	];

    public function sceneAllReportCompany()
    {
        return $this->only(
            [
                'company','op_uid','cs','is_sign','visit_start','visit_end','next_start','next_end','level'
            ])
            ->append('company', 'chsAlphaNum')
            ->append('op_uid', 'number')
            ->append('cs', 'number')
            ->append('is_sign', 'number')
            ->append('visit_start', 'date')
            ->append('visit_end', 'date')
            ->append('next_start', 'date')
            ->append('next_end', 'date')
            ->append('level', 'alphaNum');
    }

    public function sceneFirstReportCompany()
    {
        return $this->only(
            [
                 'company_name','cs','qx','address','is_new','intention','customer_source'
            ])
            ->append('company_name', 'require')
            ->append('cs', 'require')
            ->append('qx', 'require')
            ->append('address', 'require')
            ->append('is_new', 'require')
            ->append('intention', 'require')
            ->append('customer_source', 'require');
    }

    public function sceneEditReportCompany()
    {
        return $this->only(
            [
                'company_id','company_name','user_id','cs','qx','address','is_new','intention'
            ])
            ->append('  ', 'require')
            ->append('company_name', 'require')
            ->append('user_id', 'require')
            ->append('cs', 'require')
            ->append('qx', 'require')
            ->append('address', 'require')
            ->append('is_new', 'require')
            ->append('intention', 'require');
    }
}