<?php

/**
 * 工作汇报模型
 * @Author: lovenLiu
 * @Email: lypeng9205@163.com
 * @Date:   2019-05-13 14:09:22
 */

namespace app\common\model\db;

use think\Model;

class ReportWork extends Model{

    protected $autoWriteTimestamp = false;

    /**
     * 获取工作汇报数据
     * @Author   liuyupeng
     * @DateTime 2019-05-15T10:20:47+0800
     */
    public static function getDetail($id){
        return static::alias('w')
                ->join('report_work_reply r', 'r.rw_id = w.id', 'left')
                ->where([
                    ['w.id', 'EQ', $id],
                    ['w.enabled', 'EQ', 1]
                ])->field([
                    'w.id', 'w.uid', 'w.date', 'w.content', 'w.question', 'w.solution', 'w.plan', 'w.assist',
                    'IF (r.content IS NULL, "", r.content) reply_text', 'w.reply_status'
                ])->find();
    }
}