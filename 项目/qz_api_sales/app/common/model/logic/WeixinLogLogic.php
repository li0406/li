<?php

namespace app\common\model\logic;

use think\Db;

use app\common\model\db\OrderWechat;
use app\common\model\db\LogWxOrdersend;

class WeixinLogLogic {

    // 类型枚举
    public $typeEnum = [
        "1" => "抢单通知",
        "2" => "分单通知",
        "3" => "赠单通知"
    ];

    // 获取类型
    public function getEnumTypeName($type){
        if (array_key_exists($type, $this->typeEnum)) {
            return $this->typeEnum[$type];
        }

        return "其他";
    }

    /**
     * 获取微信通知发送记录分页列表
     * @param  [type]  $input     [description]
     * @param  integer $page      [description]
     * @param  integer $page_size [description]
     * @return [type]             [description]
     */
    public function getOrdersendPageList($input, $page = 1, $page_size = 10){
        // 默认查询三个月数据
        if (empty($input["date_begin"]) || empty($input["date_end"])) {
            $input["date_begin"] = date("Y-m-d", strtotime("-3month"));
            $input["date_end"] = date("Y-m-d");
        }

        // 日期转化为时间
        $input["time_begin"] = strtotime($input["date_begin"]);
        $input["time_end"] = strtotime($input["date_end"]) + 86399;
        unset($input["date_begin"], $input["date_end"]);

        // 查询总数
        $logModel = new LogWxOrdersend();
        $count = $logModel->getList("count", $input);
        
        $list = [];
        if ($count > 0) {
            // 查询列表
            $listObj = $logModel->getList("list", $input, ($page - 1) * $page_size, $page_size);
            $list = $listObj->toArray();

            $wx_openids = [];
            foreach ($list as $key => $item) {
                $list[$key]["leixing"] = $this->getEnumTypeName($item["type"]);
                $list[$key]["send_date"] = date("Y-m-d H:i:s", $item["time_add"]);
                $list[$key]["wx_openids"] = json_decode($item["wx_openids"], true);

                // 取出列表中所有的wx_openids
                if ($list[$key]["wx_openids"]) {
                    $wx_openids = array_merge($wx_openids, $list[$key]["wx_openids"]);
                }
            }

            // wx_openids去重、去空、重置索引
            $wx_openids = array_values(array_filter(array_unique($wx_openids)));

            // 根据openid查询公司信息
            $orderWechatModel = new OrderWechat();
            $com_list = $orderWechatModel->getComByOpenids($wx_openids)->toArray();
            $openid_coms = array_column($com_list, "id", "wx_openid"); // openid和公司ID对应

            // 公司列表-索引为公司ID
            $comList = [];
            foreach ($com_list as $key => $com) {
                if (array_key_exists($com["id"], $comList)) {
                    $comList[$com["id"]]["wx_openid"] .= "," . $com["wx_openid"];
                    $comList[$com["id"]]["wx_numbers"] += 1;
                } else {
                    $comList[$com["id"]] = $com;
                    $comList[$com["id"]]["wx_numbers"] = 1;
                }
            }

            foreach ($list as $key => $item) {
                $list[$key]["company_list"] = [];
                $list[$key]["result_msg"] = "成功";

                // 匹配公司信息
                $userIds = explode(",", $item["userid"]);
                foreach ($userIds as $uid) {
                    if (array_key_exists($uid, $comList)) {
                        $wx_openids = explode(",", $comList[$uid]["wx_openid"]);
                        if ($item["wx_openids"] && array_intersect($wx_openids, $item["wx_openids"])) {
                            $list[$key]["company_list"][] = $comList[$uid];
                        }
                    }
                }

                $failed = 1;
                $fail_coms = [];
                $wx_back_result = json_decode($item["wx_back_result"], true);
                if (!empty($wx_back_result)) {
                    $failed = 0; // 验证发送结果失败取出ID
                    foreach ($wx_back_result as $k => $result) {
                        if (empty($result) || $result["errcode"] != 0) {
                            $failed = 1;
                            if (!empty($item["wx_openids"])) {
                                $openid = $item["wx_openids"][$k];
                                if (array_key_exists($openid, $openid_coms)) {
                                    $fail_coms[] = $openid_coms[$openid];
                                }
                            }
                        }
                    }
                }

                if ($failed == 1) {
                    $list[$key]["result_msg"] = "失败";
                    if (count($fail_coms) > 0) {
                        $fail_coms = array_unique($fail_coms);
                        $list[$key]["result_msg"] .= sprintf("（%s）", implode(",", $fail_coms));
                    }
                }

                unset($list[$key]["wx_back_result"], $list[$key]["wx_openids"]);
            }
        }

        $search = [
            'time_begin'=>$input["time_begin"],
            'time_end'=>$input["time_end"],
        ];
        return ["list" => $list, "count" => $count, 'search' => $search];
    }

}