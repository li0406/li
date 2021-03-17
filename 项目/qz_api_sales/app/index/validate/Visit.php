<?php
/**
 *
 */

namespace app\index\validate;

use think\Validate;

class Visit extends Validate
{
	protected $rule = [
		'company_id' => 'number',
//		'visit_start_time' => 'number',
//		'visit_end_time' => 'number',
		'visit_style' => 'number',
		'qianyue_status' => 'number',
//		'visit_next_time' => 'number',
//		'retainage_time' => 'number',
	];

	protected $field = [
		'company_id' => '装修公司id',
		'visit_start_time' => '拜访开始时间',
		'visit_end_time' => '拜访结束时间',
		'visit_style' => '谈判方式',
		'qianyue_status' => '签约状态',
		'visit_next_time' => '下次联系时间',
		'desc' => '谈话内容',
		'retainage_time' => '尾款时间',
	];

	public function sceneVisit()
	{
		return $this->only(
			[
				'company_id', 'visit_start_time', 'visit_end_time', 'visit_style', 'qianyue_status','visit_next_time','desc','retainage_time',
			])
			->append('company_id', 'require')
			->append('visit_start_time', 'require')
			->append('visit_end_time', 'require')
			->append('visit_style', 'require')
			->append('qianyue_status', 'require')
			->append('visit_next_time', 'require')
			->append('desc', 'require');
//			->append('retainage_time', 'require');
	}

    public function sceneFirstVisit()
    {
        return $this->only(
            [
                'visit_start_time', 'visit_end_time', 'visit_style','visit_next_time','desc','retainage_time','pre_money'
            ])
            ->append('visit_start_time', 'require')
            ->append('visit_end_time', 'require')
            ->append('visit_style', 'require')
            ->append('visit_next_time', 'require')
            ->append('desc', 'require');
    }
}