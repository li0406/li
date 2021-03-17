<?php

/**
 * 所有订单逻辑层
 * @Author: liuyupeng
 * @Email: lypeng9205@163.com
 * @Date:   2019-06-04 17:35:40
 * @Last Modified by:   liuyupeng
 * @Last Modified time: 2019-06-13 08:32:50
 */

namespace app\common\model\logic;

use think\Collection;
use think\db\Query;
use think\facade\Request;
use app\common\model\db\User;
use app\common\model\db\Orders;
use app\common\model\db\OrderInfo;
use app\common\model\db\LogTelcenterOrdercall;
use app\common\model\db\LogHollyOrderTelcenter;
use app\common\model\db\OrderCompanyReview;
use app\common\model\db\Phonelocation;
use app\index\enum\OrderCodeEnum;
use Util\Page;

class OrderLogic
{

    /**
     * 获取全部订单数据
     *
     * @param  [type]  $options   [description]
     * @param integer $export [description]
     * @param integer $page [description]
     * @param integer $page_size [description]
     * @return [type]             [description]
     */
    public function getAllOrdersList($input, $export = 0, $page = 1, $page_size = 20, $superAdmin = false) {
        $ordersModel = new Orders();

        // 不传日期时默认查询90天
        if (empty($input["date_begin"]) && empty($input["date_end"])) {
            $input["date_begin"] = date("Y-m-d", strtotime("-30 days"));
            $input["date_end"] = date("Y-m-d");
        } else if (!empty($input["date_begin"]) && empty($input["date_end"])) {
            $input["date_end"] = date("Y-m-t", strtotime($input["date_begin"]));
        } else if (empty($input["date_begin"]) && !empty($input["date_end"])) {
            $input["date_begin"] = date("Y-m-01", strtotime($input["date_end"]));
        }


        //搜索订单号时取消默认时间
        if (!empty($input["id"])) {
            unset($input["date_begin"]);
            unset($input["date_end"]);
        }

        $count = 0;
        $offset = 0;
        $pageSize = 0;
        $pageshow = null;
        if ($export != 1) {
            $count = $ordersModel->getAllOrdersListCount($input, $superAdmin);
            $pageobj = new Page($page, $page_size, $count);
            $offset = $pageobj->firstrow;
            $pageSize = $pageobj->pageSize;
            $pageshow = $pageobj->show();
        }

        $list = [];
        if ($export == 1 || $count > 0) {
            $list = $ordersModel->getAllOrdersList($input, $superAdmin, $offset, $pageSize);
            if (count($list) > 0) {
                $list = $this->setFormatter($list->toArray());
            }
        }

        return [
            "list" => $list,
            "page" => $pageshow,
            "search" => [
                "time_begin" => check_variate($input["date_begin"]),
                "time_end" => check_variate($input["date_end"])
            ]
        ];
    }

    /**
     * @param $orderResultList
     * @return mixed
     */
    public function getOrdersLiangfangStatus($orderResultList)
    {
        $orderIds = array_column($orderResultList, 'id');
        $orderCompanyReviewModel = new OrderCompanyReview();
        return $orderCompanyReviewModel->getOrderLiangfangStatus(['orderid' => ['in', $orderIds]]);
    }

    /**
     * 获取订单查看页面数据
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getDetail($id)
    {
        $map = [['o.id', 'EQ', $id]];
        $with = ["companys"];
        if (request()->isMobile()) {
            $with[] = "liangfanglist";
        }

        $orders = new Orders();
        $info = $orders->orderinfo($map, $with);

        if (empty($info)) {
            return sys_response(4000001, '订单不存在');
        }

        $info['date'] = date('Y-m-d H:i', $info['time']);
        $info['type_fw_name'] = OrderCodeEnum::getItem('type_fw', $info['type_fw']);
        $info['leixing_name'] = OrderCodeEnum::getItem('leixing', $info['lx'])."-".OrderCodeEnum::getLxs($info["lxs"]);
        $info['yz_name'] = $info['name'] . $info['sex'];
        $info['qy_name'] = $info['cs'] . $info['area'];
        $info['huxing_name'] = $info['huxing'];
        $info['yusuan_name'] = $info['yusuan'];
        $info['fengge_name'] = $info['fengge'];
        unset($info['huxing'], $info['fengge'], $info['yusuan']);

        $info = $info->toArray();
        // 处理null数据为空
        array_walk($info, function (&$val) {
            $val = is_null($val) ? '' : $val;
        });

        // 齐装回访
        $companyVisitLogic = new CompanyVisitLogic();
        $info["visit_list"] = $companyVisitLogic->getOrderVisit($info["id"]);
        $info['is_new_review'] = 0;
        //新回访历史订单数据
        $orderReviewHistoryLogic = new OrderReviewNewHistoryLogic();
        $newHistory = $orderReviewHistoryLogic->getAll($info['id']);
        if (!empty($newHistory)) {
            $info['visit_list'] = $newHistory;
            $info['is_new_review'] = 1;
        }

        // 订单跟进情况
        $companyTrackLogic = new CompanyTrackLogic();
        $info["company_track_list"] = $companyTrackLogic->getOrderTrackGroupByCompany($info["id"]);

        return sys_response(0, '', [
            'detail' => $info
        ]);
    }

    // 处理返回格式和录音总数
    private function setFormatter($list)
    {
        if (!empty($list)) {
            $orderIds = array_column($list, "id");

            // 获取订单是否有新回访纪录
            $newReviewOrders = new OrderReviewNewLogic();
            $hasNewReviewOrders = $newReviewOrders->hasOrders($orderIds);
            $formattedNewReviewOrders = array_column($hasNewReviewOrders, "count", "order_id");

            // 订单录音次数统计
            // $logTelcenterOrdercall = new LogTelcenterOrdercall();
            // $logList = $logTelcenterOrdercall->getOrderCallCountByOrderIds($orderIds);
            // $logList = array_column($logList->toArray(), null, "id");

            // 订单合力录音次数统计
            // $logHollyOrderTelcenter = new LogHollyOrderTelcenter();
            // $logHollyList = $logHollyOrderTelcenter->getOrderCallCountByOrderIds($orderIds);
            // $logHollyList = array_column($logHollyList->toArray(), null, "id");

            // 处理数据
            foreach ($list as $key => &$item) {
                $order_id = $item["id"];

                $item["lxs"] = OrderCodeEnum::getLxs($item["lxs"]);
                $item["leixing_name"] = OrderCodeEnum::getItem("leixing", $item["lx"]);
                $item["type_fw_name"] = OrderCodeEnum::getItem("type_fw", $item["type_fw"]);

                $item["date"] = date("Y-m-d H:i", $item["time_real"]);
                $item["yz_name"] = $item["name"] . $item["sex"];
                $item["qy_name"] = $item["city_name"] . $item["area_name"];
                $item["track_count"] = intval($item["track_count"]);
                $item["lf_status"] = intval($item["lf_status"]);

                switch ($item["lf_status"]) {
                    case 3:
                        $item["lf_status_name"] = "未量房";
                        break;
                    case 2:
                        $item["lf_status_name"] = "已量房";
                        break;
                    default:
                        $item["lf_status_name"] = "未知";
                        break;
                }

                // 呼叫次数
                // $def_repeat_count = $logList[$order_id]["repeat_count"] ?? 0;
                // $holly_repeat_count = $logHollyList[$order_id]["repeat_count"] ?? 0;
                // $item["call_repeat_count"] = $def_repeat_count + $holly_repeat_count;

                // 新回访判断
                if(isset($formattedNewReviewOrders[$order_id])){
                    $item['is_new_review'] = 1;
                } else {
                    $item['is_new_review'] = 0;
                }

                // 转化null值
                foreach ($item as $k => $value) {
                    if (is_null($value)) {
                        $item[$k] = strval($value);
                    }
                }
            }
        }

        return $list;
    }


    /**
     * 获取订单列表
     *
     * @param $param
     * @param int $page
     * @param int $page_size
     * @return array
     */
    public function getOrdersList($param, $export, $page = 1, $page_size = 20, $hasNoVip = false)
    {
        $where = [];
        // 发单时间
        if (!empty($param['start']) && !empty($param['end'])) {
            if (strtotime($param['start']) < strtotime('-6 month', strtotime($param['end']))) {
                return sys_response(4000002, '请选择六个月以内时间段', []);
            }
            $where['o.time_real'] = ['between', [strtotime($param['start']), strtotime($param['end'] . ' 23:59:59')]];
        } elseif (!empty($param['start'])) {
            $where['o.time_real'] = ['between', [strtotime($param['start']), time()]];
        } elseif (!empty($param['end'])) {
            $where['o.time_real'] = ['between', [strtotime('-6 month'), strtotime($param['end'] . ' 23:59:59')]];
        } else {
            $where['o.time_real'] = ['between', [strtotime('-6 month'), time()]];
        }

        // 订单号
        if (!empty($param['id'])) {
            $where['o.id'] = $param['id'];
        }

        // 分/赠送
        if (!empty($param['type_fw'])) {
            $where['o.type_fw'] = $param['type_fw'];
        }

        // 城市
        if (!empty($param['cid'])) {
            if ($hasNoVip === true) {
                $cityModel = new \app\common\model\db\Quyu();
                $citysids = $cityModel->getNoVipCitys([]);
                if (!in_array($param['cid'], array_column(Collection::make($citysids)->toArray(), 'cid'))) {
                    return sys_response(0, '', ['list' => [], 'page' => sys_paginate(0, $page_size, $page)]);
                }
            }
            $where['o.cs'] = $param['cid'];
        }

        if ($export == 1) {
            // 导出
            $orders = new Orders();
            $list = $orders->getOrdersList($where, [], 0, 0);
        } else {
            // 分页查询
            $orders = new Orders();
            $count = $orders->getOrdersCount($where);
            $list = [];
            if ($count > 0) {
                $list = $orders->getOrdersList($where, [], $page, $page_size);
            }

            $respData['page'] = sys_paginate($count, $page_size, $page);
            $respData['timestamp'] = $where['o.time_real'][1];
        }
        $list = Collection::make($list)->toArray();

        //获取每个订单的电话号码的归属地
        $telLocation = Phonelocation::getOrderTelLocationByOrderIds(array_column($list, 'id'));
        $phoneLocation = array_column(Collection::make($telLocation)->toArray(), 'cname', 'id');

        $respData['list'] = array_map(function ($value) use ($phoneLocation) {
            array_walk($value, function (&$val) {
                $val = is_null($val) ? '' : $val;
            });
            $value['date'] = date('Y-m-d H:i:s', $value['time']);
            $value['yz_name'] = $value['name'] . $value['sex'];
            $value['qy_name'] = $value['cname'] . $value['qz_area'];
            if (isset($value['on']) && isset($value['on_sub']) && isset($value['type_fw'])) {
                $value['status'] = OrderCodeEnum::getOrderStatus($value['on'], $value['on_sub'], $value['type_fw']);
            }

            //电话号码归属地处理，如果和发单填写的城市不同则显示电话号码归属地
            $value['tel_location'] = '';
            if (!empty($phoneLocation[$value['id']])) {
                if (strpos($phoneLocation[$value['id']], $value['cname']) === false && $value['cname'] != '总站') {
                    $value['tel_location'] = $phoneLocation[$value['id']];
                }
            }

            return $value;
        }, $list);
        return sys_response(0, '', $respData);
    }

    /**
     * 获取质检订单列表（最近7天）
     * @return [type] [description]
     */
    public function getAuditOrderList($page = 1, $page_size = 20)
    {
        $time_begin = strtotime(date("Y-m-d", strtotime("-7 days")));
        $time_end = strtotime(date("Y-m-d")) + 86399;

        $citys = AdminuserLogic::getCityIds(); // 权限城市
        $auditCitys = QuyuLogic::getAuditCitys(); // 质检操作城市

        $cityIds = array_intersect($citys, $auditCitys);
        if (empty($cityIds)) {
            return ["count" => 0, "list" => []];
        }

        $list = [];
        $ordersModel = new Orders();
        $count = $ordersModel->getAuditOrderList("count", $cityIds, $time_begin, $time_end);
        if ($count > 0) {
            $page = intval($page) <= 0 ? 1 : intval($page);
            $page_size = intval($page_size) <= 0 ? 20 : intval($page_size);
            $offset = ($page - 1) * $page_size;
            $list = $ordersModel->getAuditOrderList("list", $cityIds, $time_begin, $time_end, $offset, $page_size);

            foreach ($list as $key => $item) {
                $list[$key]["yz_name"] = $item["name"] . $item["sex"];
                $list[$key]["qy_name"] = $item["city_name"] . $item["area_name"];

                $list[$key]["date"] = date("Y-m-d H:i:s", $item["time"]);
                $list[$key]["date_real"] = date("Y-m-d H:i:s", $item["time_real"]);
                $list[$key]["leixing_name"] = OrderCodeEnum::getItem("leixing", $item["lx"]);
                $list[$key]["type_fw_name"] = OrderCodeEnum::getItem("type_fw", $item["type_fw"]);
            }
        }

        return ["count" => $count, "list" => $list];
    }

    // 获取装修公司分单数量
    public function getCompanyOrderAllotCount($company_id, $date_begin, $date_end){
        $orderInfoModel = new OrderInfo();
        $orders = $orderInfoModel->getCompanyAllotOrders($company_id, $date_begin, $date_end);


        $end = $date_end;
        $datetime = strtotime(date("Y-m-d"));
        if ($datetime >= strtotime($date_begin) && $datetime <= strtotime($date_end)) {
            $end = date("Y-m-d"); // 如果合同在有效期则结束时间为当天
        }

        // 开始时间为结束时间往前推三个月，如果超出合同开始时间则以合同开始时间为准
        $begin = date("Y-m-d", strtotime("-3 months", strtotime($end)));
        if (strtotime($begin) < strtotime($date_begin)) {
            $begin = $date_begin;
        }

        // 近三个月平均每日分单
        $dateBetween = $this->getDayBetween($begin, $end);
        $fenorders = $orderInfoModel->getCompanyAllotOrders($company_id, $dateBetween['begin'], $dateBetween['end']);
        //$days = getTwoDateDays($begin, $end);
        $days = $dateBetween['days'];
        $orders["order_fen_avg"] = round($fenorders["fen_orders"] / $days, 2);

        $orders["company_id"] = $company_id;
        return $orders;
    }

    private function getDayBetween($begin, $end){
        $daysCalc = getTwoDateDays($begin, $end);
        $res_data = ['begin' => &$begin, 'end' => &$end, 'days' => &$daysCalc];

        if(strtotime($begin) > strtotime($end)){
            return $res_data;
        }

        $year = date('Y');
        $specialDays = $this->getSpecialDays($year);

        if(empty($specialDays)){
            return $res_data;
        }

        if(strtotime($begin) > strtotime($specialDays[1]) || strtotime($end) < strtotime($specialDays[0])){
            return $res_data;
        }

        if(strtotime($begin) < strtotime($specialDays[0])){
            $dayHas = (strtotime($specialDays[1]) - strtotime($end)) / 86400;
            if($dayHas < 0){
                $begin = date('Y-m-d', strtotime("-30 days", strtotime($begin)));
            }else if($dayHas > 0 && $dayHas < 30){
                $day_need = 30-$dayHas;
                $begin = date('Y-m-d', strtotime("-{$day_need} days", strtotime($begin)));
            }

            return $res_data;
        }

        if(strtotime($begin) >= strtotime($specialDays[0])){
            $dayHas = (strtotime($specialDays[1]) - strtotime($begin)) / 86400;
            if($dayHas > 0){
                $daysCalc = $daysCalc - $dayHas;
            }

            return $res_data;
        }

        return $res_data;
    }

    private function getSpecialDays($year){
        return [
            "2021" => ["2021-01-21", "2021-02-20"]
        ][$year] ?? '';
    }
}