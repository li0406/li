<?php

namespace app\index\enum;

class WorkSiteLiveStepCodeEnum {

    // 定义竣工阶段值
    const END_STEP = 17;
    //定义列表页搜索订单状态
    const LIST_STATUS_CODE_F = 1; //分单
    const LIST_STATUS_CODE_Z = 2; //赠单
    const LIST_STATUS_CODE_FZ = 3; //分/赠
    const LIST_STATUS_CODE_ZX = 4; //主动咨询

    private static $code_step = array(
        "2" => "开工大吉",
        "3" => "主体拆改",
        "4" => "水电整改",
        "5" => "防水施工",
        "6" => "泥瓦工程",
        "7" => "木工进场",
        "8" => "厨卫吊顶",
        "9" => "油漆粉刷",
        "10" => "铺贴壁纸",
        "11" => "成品安装",
        "12" => "保洁收尾",
        "13" => "家具进场",
        "14" => "家电进场",
        "15" => "家居配饰",
        "16" => "交付工程",
        "17" => "竣工"
    );

    // 获取状态列表
    public static function getList($module = "step", $filter_end = false){
        $key = "code_" . strtolower($module);

        $list = [];
        if (isset(static::$$key)) {
            $module_list = static::$$key;
            if ($filter_end == true) {
                unset($module_list[static::END_STEP]);
            }

            foreach ($module_list as $k => $val) {
                $list[] = [
                    'id' => $k,
                    'name' => $val
                ];
            }
        }

        return $list;
    }

    // 根据ID获取状态
    public static function getListByIds($module = "step", $ids = []){
        $key = "code_" . strtolower($module);

        $list = [];
        if (isset(static::$$key)) {
            foreach (static::$$key as $k => $val) {
                if (in_array($k, $ids)) {
                    $list[] = [
                        'id' => $k,
                        'name' => $val
                    ];
                }
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