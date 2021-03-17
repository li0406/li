<?php
/**
 *
 */

namespace app\index\validate;

use think\Validate;

class Contacts extends Validate
{
	protected $rule = [
//		'user_style' => 'number',
	];

	protected $field = [
		'company_id' => '装修公司id',
		'user_name' => '联系人姓名',
		'user_dz' => '联系人地址',
		'user_intention' => '意向',
		'user_style' => '客户类型',
		'company_user_id' => '会员ID',
	];

	public function sceneContacts()
	{
		return $this->only(
			[
				'company_id', 'user_name','user_dz','user_intention','user_style',
			])
			->append('company_id', 'require')
			->append('user_name', 'require');
	}

    public function sceneEditContacts()
    {
        return $this->only(
            [
                'company_id', 'user_dz','user_intention','user_style'
            ])
            ->append('company_id', 'require')
            ->append('user_dz', 'require')
            ->append('user_intention', 'require')
//            ->append('company_user_id', 'require')
            ->append('user_style', 'require');
    }

    public function sceneFirstContacts()
    {
        return $this->only(
            [
                'user_name','user_dz','user_intention','user_style','job'
            ])
            ->append('user_name', 'require');
    }
}