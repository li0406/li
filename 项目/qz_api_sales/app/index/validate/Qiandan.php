<?php
// +----------------------------------------------------------------------
// | Qiandan 签单验证
// +----------------------------------------------------------------------
namespace app\index\validate;

use think\Validate;

class Qiandan extends Validate
{
    protected $rule = [
        'id' => 'require',
        'qiandan_status' => 'require|in:0,1,2',
        'fw' => 'in:null,"",0,1,2',
        'qdstatus' => 'in:null,"",0,1,2',
        'start' => 'dateFormat:Y-m-d',
        'end' => 'dateFormat:Y-m-d',
        'cs' => 'integer',
    ];

    protected $message = [
        'id.require' => '订单编号不能为空',
        'qiandan_status.require' => '请选择签单状态',
        'qiandan_status.in' => '请选择签单状态',
        'fw.in' => '分/赠单错误',
        'qdstatus.in' => '签单状态错误',
        'start.dateFormat' => '开始时间错误',
        'end.dateFormat' => '结束时间错误',
        'cs.integer' => '城市选择错误',
    ];

    protected $scene = [
        'qdup' => ['id', 'qiandan_status'],
        'qdinfo' => ['id'],
        'qdlist' => ['fw', 'qdstatus', 'start', 'end', 'cs'],
    ];
}