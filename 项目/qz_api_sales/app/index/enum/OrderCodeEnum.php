<?php

/**
 * @Author: liuyupeng
 * @Email: lypeng9205@163.com
 * @Date:   2019-06-04 13:40:30
 * @Last Modified by:   liuyupeng
 * @Last Modified time: 2019-06-05 12:07:53
 */

namespace app\index\enum;

class OrderCodeEnum {

    // 装修类型
    private static $code_leixing = [
        "1" => "家装",
        "2" => "公装"
    ];

    // 装修类型
    private static $code_lxs = [
        "1" => "新房装修",
        "2" => "整体翻新",
        "3" => "局部改造"
    ];

    // 分/赠送
    private static $code_type_fw = [
        "1" => "分单",
        "2" => "赠单",
        "3" => "分没人跟",
        "4" => "赠没人跟"
    ];

    private static $code_xiaoqu_type = [
        "1" => "小区",
        "2" => "道路",
        "3" => "其他",
    ];

    private static $code_keys = array(
        "1" => "有钥匙",
        "0" => "无钥匙",
        "3" => "其他",
        "2" => "未填写"
    );

    private static $code_lf_time = array(
        "随时,下班,今明" => "随时,下班,今明",
        "1周内,本周末" => "1周内,本周末",
        "2周内,下周末" => "2周内,下周末",
        "3周内" => "3周内",
        "4周内" => "4周内",
        "1个月以上" => "1个月以上",
        "其他" => "其他"
    );

    private static $code_cooperation_type = array(
        "1" => "常规",
        "2" => "独",
        "3" => "垄"
    );

    // 面积
    private static $code_mianji = [
        [
            "id"=> 1,
            "name" => "0-50㎡",
            "value_min" => 0,
            "value_max" => 50
        ],
        [
            "id"=> 2,
            "name" => "51-80㎡",
            "value_min" => 51,
            "value_max" => 80
        ],
        [
            "id"=> 3,
            "name" => "81-120㎡",
            "value_min" => 81,
            "value_max" => 120
        ],
        [
            "id"=> 4,
            "name" => "121-150㎡",
            "value_min" => 121,
            "value_max" => 150
        ],
        [
            "id"=> 5,
            "name" => "151-200㎡",
            "value_min" => 151,
            "value_max" => 200
        ],
        [
            "id"=> 6,
            "name" => "200㎡以上",
            "value_min" => 200,
            "value_max" => 0
        ]
    ];

    public static function getMianji(){
        return static::$code_mianji;
    }

    // 获取状态列表
    public static function getList($module){
        $key = "code_" . strtolower($module);
        
        $list = [];
        if (isset(static::$$key)) {
            foreach (static::$$key as $k => $val) {
                $list[] = [
                    'id' => $k,
                    'name' => $val
                ];
            }
        }

        return $list;
    }

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

    /**
     * 订单状态
     * @param int $on 订单主状态
     * @param int $on_sub 订单子状态
     * @param int $type_fw 订单分赠状态
     * @return string
     */
    public static function getOrderStatus($on = 0, $on_sub = 0, $type_fw = 0)
    {
        switch ($on) {
            case '0':
                switch ($on_sub) {
                    case '8':
                        $status = '扫单';
                        break;
                    case '9':
                        $status = '次新单';
                        break;
                    case '10':
                        $status = '新单';
                        break;
                    default:
                        $status = '未审核(子状态未知)';
                        break;
                }
                break;
            case '4':
                switch ($type_fw) {
                    case '0':
                        $status = '有效未分配';
                        break;
                    case '1':
                        $status = '分单';
                        break;
                    case '2':
                        $status = '赠单';
                        break;
                    case '3':
                        $status = '分没人跟';
                        break;
                    case '4':
                        $status = '赠没人跟';
                        break;
                    case '5':
                        $status = '有效(待分配)';
                        break;
                    case '6':
                        $status = '有效(待分配)';
                        break;
                    default:
                        $status = '有效(子状态未知)';
                        break;
                }
                break;
            case '2':
                $status = '待定';
                break;
            case '5':
                $status = '无效';
                break;
            case '6':
                $status = '无效(空号)';
                break;
            case '7':
                $status = '无效(装修公司)';
                break;
            case '8':
                $status = '无效(无效咨询)';
                break;
            case '9':
                $status = '无效(重复订单)';
                break;
            case '98':
                $status = '暂时无效';
                break;
            case '99':
                $status = '撤回单';
                break;
            default:
                $status = '未知状态';
                break;
        }
        return $status;
    }

    public static function getXiaoquType($key)
    {
        return isset(self::$code_xiaoqu_type[$key]) ? self::$code_xiaoqu_type[$key] : '';
    }

    public static function getLx($key)
    {
        return isset(self::$code_leixing[$key]) ? self::$code_leixing[$key] : '';
    }

    public static function getLxs($key)
    {
        return isset(self::$code_lxs[$key]) ? self::$code_lxs[$key] : '';
    }

    public static function getKeys($key)
    {
        return isset(self::$code_keys[$key]) ? self::$code_keys[$key] : '';
    }

    public static function getLfTime($key)
    {
        return isset(self::$code_lf_time[$key]) ? self::$code_lf_time[$key] : '';
    }

    public static function getCooperationType($key)
    {
        return isset(self::$code_cooperation_type[$key]) ? self::$code_cooperation_type[$key] : '';
    }
}