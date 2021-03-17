<?php

namespace app\index\enum;

class CompanyVisitCodeEnum {

    // 回访类型
    private static $code_visit_step = [
        "1" => "促量房",
        "2" => "促到店",
        "3" => "促签单",
        "4" => "推荐服务"
    ];

    // 回访阶段
    private static $code_visit_type = [
        "1" => "被动",
        "2" => "主动"
    ];

    // 回访状态
    private static $code_visit_status = [
        "1" => "未回访",
        "2" => "有效单",
        "3" => "无效",
        "4" => "次新单",
        "5" => "待定单",
    ];

    // 推送状态
    private static $code_visit_push = [
        "1" => "未推送",
        "2" => "已推送"
    ];

    // 订单量房状态
    private static $code_order_lf_status = array(
        "0" => "未知",
        "1" => "未量房",
        "2" => "已量房"
    );

    //新回访订单-回访状态-列表展示
    private static $code_review_type = [
        // 1 => '预处理',
        // 2 => '新单',
        // 3 => '已量房',
        // 4 => '未量房',
        // 5 => '已签单',
        // 6 => '待定单',
        // 7 => '终结单',
        1 => '未回访',
        2 => '未回访',
        3 => '有效单',
        4 => '无效',
        5 => '有效单',
        6 => '未回访',
        7 => '无效',
        8 => '未回访',
    ];

    // 回访备注
    private static $code_review_remark = [
        1 => '未接通',
        2 => '开场挂',
        3 => '不配合回访',
        4 => '晚点联系',
        5 => '回访中断',
        6 => '拒接/未接通',
        7 => '拒绝服务',
        8 => '装修公司原因未量房',
        9 => '已量房/已到店',
        10 => '再约量房/到店成功',
        11 => '签单',
        12 => '再约签单成功',
        13 => '推荐服务',
        14 =>'再约推荐服务成功',
        15 => '不配合回访'
    ];

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
}