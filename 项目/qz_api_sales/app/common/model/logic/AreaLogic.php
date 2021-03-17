<?php

namespace app\common\model\logic;

use Util\App;
use app\common\model\db\Quyu;
use app\common\enum\CacheKeyCodeEnum;

class AreaLogic
{
    protected $citys;

    public function __construct()
    {
        $app = new App();
        $quyuModel = new Quyu();

        $cache_key = CacheKeyCodeEnum::CITY_AREA_ALL;
        $this->citys = cache($cache_key);

        $list = $quyuModel->getquyuList();
        if (count($list) > 0) {
            $citys = [];
            foreach ($list as $key => $value) {
                if(!array_key_exists($value['cid'], $citys)){
                    $citys[$value['cid']] = array();
                    $citys[$value['cid']]["cid"] = $value["cid"];
                    $str = strtoupper(mb_substr( $value["px_abc"],0,1,"utf-8"));
                    //增加首字母大写
                    $citys[$value['cid']]["key"]          = $str;
                    $citys[$value['cid']]["bm"]           = $value["bm"];
                    $citys[$value['cid']]["uid"]          = $value["uid"];
                    $citys[$value['cid']]["cname"]        = $str." ".$value["cname"];
                    $citys[$value['cid']]["oldname"]      = $value["cname"];
                    $citys[$value['cid']]["type"]         = $value["type"];
                    $citys[$value['cid']]["is_open_city"] = $value["is_open_city"];
                    $citys[$value['cid']]["mark_red"]     = $value["mark_red"];
                    $citys[$value['cid']]["px"]           = $value["px"];
                    $citys[$value['cid']]["px_abc"]       = $value["px_abc"];
                    $citys[$value['cid']]["child"]        = array();
                    $citys[$value['cid']]["parent_city"]  = $value["parent_city"];
                    $citys[$value['cid']]["parent_city1"] = $value["parent_city1"];
                    $citys[$value['cid']]["parent_city2"] = $value["parent_city2"];
                    $citys[$value['cid']]["parent_city3"] = $value["parent_city3"];
                    $citys[$value['cid']]["parent_city4"] = $value["parent_city4"];
                    $citys[$value['cid']]["other_city"]   = $value["other_city"];
                    $citys[$value['cid']]["qz_province"]     = $value["qz_province"];
                    $citys[$value['cid']]["qz_provinceid"]     = $value["qz_provinceid"];
                    $citys[$value['cid']]["little"]        = $value["little"];
                }
                $str = $app->getFirstCharter($value["qz_area"]);
                $quyu = array(
                    "key"=>$str,
                    "qz_areaid"=>$value["qz_areaid"],
                    "qz_area" =>$str." ".$value["qz_area"],
                    "orders" => $value["orders"],
                    "oldname" =>$value["qz_area"]
                );
                $citys[$value['cid']]["child"][]= $quyu;
            }
            $edition = array();
            foreach ($citys as $key => $value) {
                // 准备要排序的数组
                $edition[] = $value["key"];
            }
            array_multisort($edition, SORT_ASC, $citys);
            foreach ($citys as $key => $value) {
                // 准备要排序的数组
                $edition = array();
                foreach ($value["child"] as $k => $v) {
                    $edition[] = $v["key"];
                }
                array_multisort($edition, SORT_ASC, $citys[$key]["child"]);
            }

            //因为排序,重新替换数组的键
            foreach ($citys as $key => $value) {
                $citys[$value["cid"]] = $value;
                unset($citys[$key]);
            }

            $this->citys = $citys;
            cache($cache_key, $citys, 30);
        }
    }


    /**
     * 根据城市ID获取城市,区县信息
     * @return [type] [description]
     */
    public function getCityById($id, $prefix = true)
    {
        $citys = [];
        foreach ($this->citys as $key => $value) {
            if ($key == $id) {
                $citys = $value;
                break;
            }
        }

        if(!$prefix){
            $exp = explode(' ',$citys["cname"]);
            //$citys["cname"] = $exp[1];
            foreach ($citys["child"] as $key => $value) {
                $exp = explode(' ',$value["qz_area"]);
                $citys["child"][$key]["qz_area"] = $exp[1];
            }
            $edition = array();
            foreach ($citys["child"] as $k => $v) {
                $edition[] = $v["orders"];
            }
            array_multisort($edition, SORT_ASC, $citys["child"]);
        }
        return $citys;
    }

}