<?php

/**
 * @Author: lovenLiu
 * @Email: lypeng9205@163.com
 * @Date:   2019-05-15 10:57:30
 * @Last Modified by:   lovenLiu
 * @Last Modified time: 2019-05-15 13:42:48
 */

namespace app\index\validate;

use think\Validate;

class ReportWork extends Validate {

    protected $rule = [
    ];

    protected $message = [
        'uid.require' => '汇报人参数非法',
        'date.require' => '请选择日期',
        'content.require' => '请填写工作内容',
        'reply_text.require' => '请填写回复内容',
        'id.require' => '工作汇报不存在'
    ];

    public function sceneAdd() {
        return $this->only([
                'uid', 'date', 'content', 'question', 'solution', 'plan', 'assist'
            ])
            ->append('uid', 'require')
            ->append('date', 'require')
            ->append('content', 'require');
    }

    public function sceneReply() {
        return $this->only([
                'id', 'reply_text'
            ])
            ->append('id', 'require')
            ->append('reply_text', 'require');
    }
    
}