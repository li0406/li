<?php
// +----------------------------------------------------------------------
// | OrdersLogic 订单相关数据逻辑层
// +----------------------------------------------------------------------

namespace app\index\model\logic;

use app\index\model\adb\Orders;
use app\index\enum\OrderCodeEnum;

class OrdersLogic {

    protected $ordersModel;

    public function __construct() {
        $this->ordersModel = new Orders();
    }

    // 获取城市区域发单量统计
    public function getCityAreaFadanStatistic($date_begin, $date_end, $city_ids = [], $area_ids = []){
        $list = $this->ordersModel->getAreaFadanStatistic($date_begin, $date_end, $city_ids, $area_ids);
        return $list;
    }

    // 获取城市区域分单时间统计
    public function getCityAreaFendanStatistic($date_begin, $date_end, $city_ids = [], $area_ids = []){
        $list = $this->ordersModel->getAreaFendanStatistic($date_begin, $date_end, $city_ids, $area_ids);
        return $list;
    }

    // 按分单时间计算量房量
    public function getCityAreaFenLfStatistic($date_begin, $date_end, $city_ids = [], $area_ids = []){
        $list = $this->ordersModel->getAreaFenLfStatistic($date_begin, $date_end, $city_ids, $area_ids);
        return $list;
    }

    // 按用户点击量房时间计算量房量
    public function getCityAreaRealLfStatistic($date_begin, $date_end, $city_ids = [], $area_ids = []){
        $list = $this->ordersModel->getAreaRealLfStatistic($date_begin, $date_end, $city_ids, $area_ids);
        return $list;
    }

    // 按申请签单时间计算签单量
    public function getCityAreaQiandanStatistic($date_begin, $date_end, $city_ids = [], $area_ids = []){
        $list = $this->ordersModel->getAreaQiandanStatistic($date_begin, $date_end, $city_ids, $area_ids);
        return $list;
    }


    // 订单面积统计
    public function getCityAreaMianjiStatistic($date_begin, $date_end, $city_ids = [], $area_ids = []){
        $list = $this->ordersModel->getAreaFendanDetailed($date_begin, $date_end, $city_ids, $area_ids);

        $glist = [];
        if (count($list) > 0) {
            foreach ($list as $key => $item) {
                $gkey = sprintf("%s-%s", $item["city_id"], $item["area_id"]);
                if (!array_key_exists($gkey, $glist)) {
                    $glist[$gkey] = [
                        "city_id" => $item["city_id"],
                        "area_id" => $item["area_id"],
                        "mianji_40" => 0,
                        "mianji_60" => 0,
                        "mianji_80" => 0,
                        "mianji_100" => 0,
                        "mianji_120" => 0,
                        "mianji_150" => 0,
                        "mianji_200" => 0,
                        "mianji_max" => 0
                    ];
                }

                if (in_array($item["type_fw"], [OrderCodeEnum::TYPE_FW_FEN, OrderCodeEnum::TYPE_FW_ZEN])) {
                    $mianji = floatval($item["mianji"]);
                    if ($mianji < 40) {
                        $glist[$gkey]["mianji_40"] += 1;
                    } else if ($mianji >= 40 && $mianji < 60) {
                        $glist[$gkey]["mianji_60"] += 1;
                    } else if ($mianji >= 60 && $mianji < 80) {
                        $glist[$gkey]["mianji_80"] += 1;
                    } else if ($mianji >= 80 && $mianji < 100) {
                        $glist[$gkey]["mianji_100"] += 1;
                    } else if ($mianji >= 100 && $mianji < 120) {
                        $glist[$gkey]["mianji_120"] += 1;
                    } else if ($mianji >= 120 && $mianji < 150) {
                        $glist[$gkey]["mianji_150"] += 1;
                    } else if ($mianji >= 150 && $mianji < 200) {
                        $glist[$gkey]["mianji_200"] += 1;
                    } else if ($mianji >= 200) {
                        $glist[$gkey]["mianji_max"] += 1;
                    }
                }
            }
        }

        return $glist;
    }

    // 查询城市签单金额
    public function getCityUserQiandanAmount($date_begin, $date_end, $city_ids = []){
        // 1.0会员签单金额
        $userQiandanList = $this->ordersModel->getCityUserQiandanAmount(1, $date_begin, $date_end, $city_ids);
        $userQiandanList = array_column($userQiandanList, null, "city_id");
        $cityIds = array_keys($userQiandanList);

        // 2.0会员签单金额
        $sbackQiandanList = $this->ordersModel->getCityUserQiandanAmount(2, $date_begin, $date_end, $city_ids);
        $sbackQiandanList = array_column($sbackQiandanList, null, "city_id");
        $cityIds = array_merge($cityIds, array_keys($sbackQiandanList));

        $list = [];
        $cityIds = array_filter(array_unique($cityIds));
        foreach ($cityIds as $key => $city_id) {
            if (!array_key_exists($city_id, $list)) {
                $list[$city_id]["city_id"] = $city_id;
            }

            $list[$city_id]["user_qiandan_amount"] = floatval($userQiandanList[$city_id]["qiandan_jine"] ?? 0) * 10000;
            $list[$city_id]["signback_qiandan_amount"] = floatval($sbackQiandanList[$city_id]["qiandan_jine"] ?? 0) * 10000;
        }

        return $list;
    }

}