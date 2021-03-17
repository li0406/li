<?php

namespace app\index\validate;

use think\Validate;

class UserCompany extends Validate {

    protected $rule = [
    ];

    protected $message = [
        "id.require" => "会员公司参数非法",
        "cs.require" => "请先选择城市",
        "cs.notIn" => "请先选择城市",
        "qx.require" => "请先选择区县",
        "qx.notIn" => "请先选择区县",
        "mianji.number" => "赠单面积格式错误：请填写数字",
        "fen_mianji.number" => "分单面积格式错误：请填写数字",
        "lxs.between" => "新房/旧房格式错误",
        "contract.between" => "半包/全包格式错误",
        "lx.between" => "公装/家装格式错误",
        "get_gift_order.between" => "公装/家装格式错误"
    ];

    public function sceneEdit() {
        return $this->only([
                "id", "cs", "qx", "latlng", "lxs", "contract", "lx", "get_gift_order", "mianji", "fen_mianji"
            ])
            ->append("id", "require")
            ->append("cs", "require|notIn:0")
            ->append("qx", "require|notIn:0")
            ->append("latlng", function($value){
                if (!empty($value)) {
                    $latlng = explode(",", $value);
                    if (count($latlng) == 2) {
                        $match_lng = '/^(\-|\+)?(((\d|[1-9]\d|1[0-7]\d|0{1,3})\.\d{0,6})|(\d|[1-9]\d|1[0-7]\d|0{1,3})|180\.0{0,6}|180)$/';
                        $match_lat = '/^(\-|\+)?([0-8]?\d{1}\.\d{0,6}|90\.0{0,6}|[0-8]?\d{1}|90)$/';
                        if (preg_match($match_lng, $latlng[0]) && preg_match($match_lat, $latlng[1])) {
                            return true;
                        }
                    }

                    return "坐标填写错误";
                }

                return true;
            })
            ->append('lxs', 'between:0,3')
            ->append('contract', 'between:0,3')
            ->append('lx', 'between:0,3')
            ->append('get_gift_order', 'between:1,2')
            ->append('mianji', 'number')
            ->append('fen_mianji', 'number');
    }

    /**
     * 对立公司ID验证
     * @return [type] [description]
     */
    public function otherIdValidate($id, $other_id){
        if (!empty($other_id)) {
            $otherIds = explode(",", $other_id);
            if (in_array($id, $otherIds)) {
                return "对立公司ID错误：不能输入本公司ID";
            }

            $count = model("db.userCompany")->getVipCompanyCount($otherIds);
            if (count($otherIds) > $count) {
                return "对立公司ID错误：请输入已存在的真会员公司ID";
            }
        }

        return true;
    }
    
}