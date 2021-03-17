<?php

namespace app\common\model\db;

use think\Model;
use think\db\Query;

class SaleReportPaymentLog extends Model {

    protected $autoWriteTimestamp = false;

    const ACTION_TYPE_SAVE = 1; // 保存（添加、编辑）
    const ACTION_TYPE_SUBMIT = 2; // 提交
    const ACTION_TYPE_EXAMPASS = 3; // 审核通过
    const ACTION_TYPE_EXAMFAIL = 4; // 审核不通过
    const ACTION_TYPE_DELETE = 5; // 删除
    const ACTION_TYPE_RECALL = 6; // 提交撤回
    const ACTION_TYPE_RELATED = 7; // 报备
    const ACTION_TYPE_RELEASE = 8; // 解除报备
    const ACTION_TYPE_ROLLBACK = 9; // 审核撤回
    const ACTION_TYPE_EXAM_TO_KF = 10; // 审核通过/待客服上传
    const ACTION_TYPE_EXAMFAIL_KF = 11; // 客服审核不通过
    const ACTION_TYPE_EXAMPASS_KF = 12; // 审核通过/客服完成上传

    public static $action_type = [
        self::ACTION_TYPE_SAVE => '保存',
        self::ACTION_TYPE_SUBMIT => '提交',
        self::ACTION_TYPE_EXAMPASS => '审核通过',
        self::ACTION_TYPE_EXAMFAIL => '审核不通过',
        self::ACTION_TYPE_DELETE => '删除',
        self::ACTION_TYPE_RECALL => '提交撤回',
        self::ACTION_TYPE_RELATED => '报备',
        self::ACTION_TYPE_RELEASE => '解除报备',
        self::ACTION_TYPE_ROLLBACK => '审核撤回',
        self::ACTION_TYPE_EXAM_TO_KF => '审核通过/待客服上传',
        self::ACTION_TYPE_EXAMFAIL_KF => '客服审核不通过',
        self::ACTION_TYPE_EXAMPASS_KF => '审核通过/客服完成上传',
    ];

    public static function getActionType($key)
    {
        return self::$action_type[$key] ?: '';
    }

    public static function addLog($data){
        return static::insert($data);
    }

    public function getCheckPaymentLogList($ids)
    {
        if (empty($ids)) {
            return [];
        }
        $where = [
            ['report_payment_id', 'in', $ids],
            ['action_type', 'in', [
                    self::ACTION_TYPE_EXAMPASS,
                    self::ACTION_TYPE_EXAMFAIL,
                    self::ACTION_TYPE_ROLLBACK,
                    self::ACTION_TYPE_EXAM_TO_KF,
                    self::ACTION_TYPE_EXAM_TO_KF,
                    self::ACTION_TYPE_EXAMFAIL_KF,
                    self::ACTION_TYPE_EXAMPASS_KF
                ]
            ]
        ];
        return $this->where($where)->order('id desc')->select();
    }
}