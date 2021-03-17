<?php

/**
 * 录音相关状态码
 * @Author: lovenLiu
 * @Email: lypeng9205@163.com
 * @Date:   2019-05-20 15:14:13
 * @Last Modified by:   liuyupeng
 * @Last Modified time: 2019-06-14 14:59:21
 */

namespace app\index\enum;

class VoipCodeEnum {

    // 呼叫动作状态码
    private static $code_action = [
        "callauth" => "开始",
        "callestablish" => "接通",
        "hangup" => "挂断",
        "callestablish_sub" => "主叫接通",
        "hangup_sub" => "主叫挂断"
    ];

    // 拨打方式
    private static $code_calltype = [
        "callback" => "回拨拨打"
    ];

    // 挂断方式
    private static $code_byetype = [
        "0" => "呼叫挂机",
        "1" => "通话失败"
    ];

    // 合力通话记录状态（挂断状态）
    private static $code_holly_state = [
        'dealing' => '已接听',
        'notDeal' => '振铃未接听',
        'voicemail' => '已留言',
        'blackList' => '黑名单',
        'queueLeak' => '排队放弃',
        'leak' => 'ivr'
    ];


    /**
     * 获取状态值
     * @Author   liuyupeng
     * @DateTime 2019-05-20T15:28:28+0800
     */
    public static function getItem($module, $code, $default = ''){
        $key = "code_" . strtolower($module);
        $code = strtolower($code);

        if (isset(static::$$key)) {
            $modules = static::$$key;
            if (array_key_exists($code, $modules)) {
                return $modules[$code];
            }
        }

        return $default;
    }

    // 获取合力事件状态
    public static function getHollyAction($hanguper, $call_state, $default = ''){
        $action = $default;
        if ($call_state == "Hangup" || $call_state == "Unlink") {
            if ($hanguper == "caller") {
                $action = "主叫挂断";
            } elseif ($hanguper == "callee"){
                $action = "被叫挂断";
            }
        }

        return $action;
    }
}