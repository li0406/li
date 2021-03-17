<?php

namespace app\common\model\logic;

use Util\Page;
use think\Exception;
use think\Collection;

use app\common\model\db\User;
use app\common\model\db\Orders;
use app\common\model\db\OrderInfo;
use app\common\model\db\SaleReportPayment;
use app\common\model\db\SaleReportPaymentImg;
use app\common\model\db\SaleReportPaymentPerson;
use app\common\model\db\UserVip;
use app\common\model\db\UserCompany;

use app\index\enum\ReportPaymentCodeEnum;

class StatisticsLogic
{
    /**
     * @param $request
     * @param $user
     * @return array|\PDOStatement|string|Collection
     */
    public function getCompanyList($request)
    {
        $citys = AdminuserLogic::getCityIds();
        if (isset($request['cs']) && !empty($request['cs'])) {
            if (in_array($request['cs'], $citys)) {
                $citys = [$request['cs']];
            } else {
                $citys = '';
            }
        }

        $company_id = [];
        if (!empty($citys)) {
            //会员状态  -1已过期会员 0代表注册用户 1代表认证状态 2是会员状态 -3是预约会员状态 -4会员暂停状态
            $map = [
                'on' => ['in', [-1, 2, -3, -4]],
                'classid' => array("in", ["3", "6"]),
                'cs' => ['in', $citys]
            ];

            $userDb = new User();
            $companyList = $userDb->getCompanyList($map, !empty($request['company_info']) ? $request['company_info'] : '', 'id,classid');

            $company_id = array_column($companyList, 'id');
        }

        return $company_id;
    }

    // 会员分单/签单统计
    public function getCompanyOrdersStatistics($input, $page = 1, $limit = 20){
        // 如果查询时间为空默认查询分单开始时间当月1号
        if (empty($input["start"]) || empty($input["end"])) {
            if (empty($input["qdstart"]) && empty($input["qdend"])) {
                $input["start"] = date("Y-m-01");
                $input["end"] = date("Y-m-d");
            }
        }

        $orderInfoModel = new OrderInfo();
        $countResult = $orderInfoModel->getOrderFenStatisticListCount($input);
        $count = $countResult["count"];

        $offset = 0;
        $listRows = 0;
        $pageshow = null;
        if (check_variate($input["down"]) != 1) {
            // 不是导出计算分页
            $pageobj = new Page($page, $limit, $count);
            $listRows = $pageobj->pageSize;
            $offset = $pageobj->firstrow;
            $pageshow = $pageobj->show();
        }

        // 是否需要统计数据
        $needAll = request()->isMobile();

        $list = [];
        if ($count > 0) {
            // 查询数据
            $list = $orderInfoModel->getOrderFenStatisticList($input, $offset, $listRows);
            if (count($list) > 0) {
                foreach ($list as $key => &$item) {
                    $item["fen"] = floatval($item["fen"]);
                    $item["zeng"] = floatval($item["zeng"]);
                    $item["qiang"] = floatval($item["qiang"]);
                    $item["zong"] = floatval($item["zong"]);
                    $item["qian"] = floatval($item["qian"]);
                    $item["qian_money"] = round($item["qian_money"], 2);

                    // 会员状态
                    $company_status = $item["classid"] == 6 ? "签返" : "";
                    switch ($item["on"]) {
                        case "-1":
                            $company_status .= "过期会员";
                            break;

                        case "-3":
                            $company_status .= "预约会员";
                            break;

                        case "-4":
                            $company_status .= "暂停会员";
                            break;

                        case "2":
                            $company_status .= "会员";
                            break;

                        default:
                            $company_status = "";
                    }

                    $item["company_status"] = $company_status;
                }

                // 需要统计数据增加合计
                if ($needAll == true) {
                    $total = [
                        "com" => "",
                        "company_name" => "总计",
                        "cname" => "总计",
                        "cid" => "",
                        "zeng" => intval($countResult["zeng"]),
                        "fen" => intval($countResult["fen"]),
                        "qiang" => intval($countResult["qiang"]),
                        "company_status" => intval($countResult["count"]),
                        "qian" => intval($countResult["qian"]),
                        "qian_money" => round($countResult["qian_money"], 2)
                    ];

                    array_unshift($list, $total);
                }
            }
        }

        return [
            "list" => $list,
            "page" => $pageshow
        ];
    }

    /**
     * @param $companys
     * @param $request
     * @param int $page
     * @param int $page_count
     * @return array
     */
    public function getCompanyUnreadOrders($companys, $request, $page = 1, $page_count = 20)
    {
        $page = $page <= 0 ? 1 : $page;
        $pageCount = $page_count <= 0 ? 20 : $page_count;

        $startime = strtotime("-3 month");
        $endtime = time();
        if (!empty($request['start']) && !empty($request['end'])) {
            $startime = strtotime($request['start']);
            $endtime = strtotime($request['end']) + 86399;
        }

        $count = 0;
        $list = [];
        if (!empty($companys)) {
            $map = [
                'i.isread' => ['eq', 0],
                'i.com' => ['IN', $companys],
                'i.addtime' => ['BETWEEN', [$startime, $endtime]]
            ];
            $orderDb = new OrderInfo();
            $count = $orderDb->getUnreadOrdersListCount($map);
            $list = [];
            if ($count > 0) {
                $list = $orderDb->getUnreadOrdersList($map, ($page - 1) * $pageCount, $pageCount);
            }
        }

        return ["count" => $count, "list" => $list, "options" => [
            "date_begin" => date("Y-m-d", $startime),
            "date_end" => date("Y-m-d", $endtime)
        ]];
    }


    /**
     * 小报备业绩明细导出
     * @param  [type] $input [description]
     * @return [type]        [description]
     */
    public function getStatisticPaymentDetailedExport($input){
        if (!empty($input["top_team_id"])) {
            $teamLogic = new TeamLogic();
            $top_user_list = $teamLogic->getTeamUserList($input["top_team_id"], 1);
            $input["user_ids"] = array_column($top_user_list, "current_id");
            $input["user_ids"] = $input["user_ids"] ? : [-1];
        }
        
        $reportPaymentModel = new SaleReportPayment();
        $list = $reportPaymentModel->getStatisticDetailedList($input, 0, 0);
        $list = $this->setStatisticPaymentDetailedFormatter($list);
        return $list;
    }

    /**
     * 获取小报备业绩明细
     * @param  [type] $input [description]
     * @param  [type] $page  [description]
     * @param  [type] $limit [description]
     * @return [type]        [description]
     */
    public function getStatisticPaymentDetailed($input, $page = 1, $limit = 20){
        if (!empty($input["top_team_id"])) {
            $teamLogic = new TeamLogic();
            $top_user_list = $teamLogic->getTeamUserList($input["top_team_id"], 1);
            $input["user_ids"] = array_column($top_user_list, "current_id");
            $input["user_ids"] = $input["user_ids"] ? : [-1];
        }

        $reportPaymentModel = new SaleReportPayment();
        $count = $reportPaymentModel->getStatisticDetailedCount($input);
        $pageobj = new Page($page, $limit, $count);

        if ($count > 0) {
            $list = $reportPaymentModel->getStatisticDetailedList($input, $pageobj->firstrow, $pageobj->pageSize);
            $list = $this->setStatisticPaymentDetailedFormatter($list);
        }

        return [
            "count" => $count,
            "list" => $list ?? [],
            "page" => $pageobj->show()
        ];
    }

    public function getStatisticPaymentSum($input){
        if (!empty($input["top_team_id"])) {
            $teamLogic = new TeamLogic();
            $top_user_list = $teamLogic->getTeamUserList($input["top_team_id"], 1);
            $input["user_ids"] = array_column($top_user_list, "current_id");
            $input["user_ids"] = $input["user_ids"] ? : [-1];
        }

        $reportPaymentModel = new SaleReportPayment();
        $data = $reportPaymentModel->getStatisticSumCount($input);

        $data['payment_total_money'] = floatval($data['payment_total_money']?? 0);
        $data['payment_money'] = floatval($data['payment_money'] ?? 0);
        $data['refund_money'] = floatval($data['refund_money'] ?? 0) * -1;

        return $data;
    }

    // 小报备统计数据处理
    private function setStatisticPaymentDetailedFormatter($list){
        $paymentIds = array_column($list->toArray(), "id");
        
        $reportPaymentLogic = new ReportPaymentLogic();
        $paymentImgList = $reportPaymentLogic->getPaymentAuthImgList($paymentIds); // 查询凭证
        $paymentPersonList = $reportPaymentLogic->getPaymentPersonList($paymentIds); // 查询业绩人
        $paymentPayeeList = $reportPaymentLogic->getPaymentPayeeList($paymentIds); // 查询收款方

        foreach ($list as $key => &$item) {
            $item["payment_total_money"] = floatval($item["payment_total_money"]);

            if($item["cooperation_type"] == ReportPaymentCodeEnum::COOPERATION_TYPE_USER_REFUND){
                $item["payment_total_money"] = floatval($item['refund_money']) * -1;
            }
            
            $item["payment_type_name"] = ReportPaymentCodeEnum::getItem("payment_type", $item["payment_type"]);
            $item["cooperation_type_name"] = ReportPaymentCodeEnum::getItem("cooperation_type", $item["cooperation_type"]);

            $item["deposit_money"] = floatval($item["deposit_money"]);
            $item["round_order_money"] = floatval($item["round_order_money"]);
            $item["payment_money"] = floatval($item["payment_money"]);
            $item["deposit_to_round_money"] = floatval($item["deposit_to_round_money"]);
            $item["deposit_to_platform_money"] = floatval($item["deposit_to_platform_money"]);
            $item["other_money"] = floatval($item["other_money"]);
            $item["payment_date"] = date("Y-m-d", $item["payment_time"]);
            $item['platform_money'] = floatval($item['platform_money']);
            $item['refund_money'] = floatval($item['refund_money']);

            $item["auth_imgs"] = $paymentImgList[$item->id] ?? [];
            $item["person_list"] = $paymentPersonList[$item->id] ?? [];
            $item["payee_list"] = $paymentPayeeList[$item->id] ?? [];
        }

        return $list;
    }

    /**
     * 个人业绩统计
     * @param  [type]  $input [description]
     * @param  integer $page  [description]
     * @param  integer $limit [description]
     * @return [type]         [description]
     */
    public function getStatisticPaymentSaler($input, $page = 1, $limit = 20){
        try {
            $adminuserLogic = new AdminuserLogic();
            $departmentList = $adminuserLogic->department(AdminuserLogic::SALER_DEPARTMENT_ID);
            $input["department_id"] = array_column($departmentList, "id");
            $input["department_id"][] = AdminuserLogic::SALER_DEPARTMENT_ID;
            if (!empty($input["top_team_id"])) {
                $teamLogic = new TeamLogic();
                $top_user_list = $teamLogic->getTeamUserList($input["top_team_id"], 1);
                $input["user_ids"] = array_column($top_user_list, "current_id");
                if (empty($top_user_list)) {
                    throw new Exception("团队下没有用户", 1);
                }
            }

            $reportPaymentModel = new SaleReportPayment();
            $count = $reportPaymentModel->getStatisticPaymentSalerCount($input);
            $pageobj = new Page($page, $limit, $count);

            if ($count > 0) {
                $list = $reportPaymentModel->getStatisticPaymentSalerList($input, $pageobj->firstrow, $pageobj->pageSize);
                $list = $this->setStatisticPaymentSalerFormatter($list);
            }

        } catch (Exception $e) {
            $count = 0;
            $pageobj = new Page($page, $limit, $count);
            // echo $e->getMessage();exit();
        }

        return [
            "count" => $count,
            "list" => $list ?? [],
            "page" => $pageobj->show()
        ];
    }



    /**
     * 个人业绩统计导出
     * @param  [type]  $input [description]
     * @param  integer $page  [description]
     * @param  integer $limit [description]
     * @return [type]         [description]
     */
    public function getStatisticPaymentSalerExport($input){
        try {
            $adminuserLogic = new AdminuserLogic();
            $departmentList = $adminuserLogic->department(AdminuserLogic::SALER_DEPARTMENT_ID);
            $input["department_id"] = array_column($departmentList, "id");
            $input["department_id"][] = AdminuserLogic::SALER_DEPARTMENT_ID;

            if (!empty($input["top_team_id"])) {
                $teamLogic = new TeamLogic();
                $top_user_list = $teamLogic->getTeamUserList($input["top_team_id"], 1);
                $input["user_ids"] = array_column($top_user_list, "current_id");
                if (empty($top_user_list)) {
                    throw new Exception("团队下没有用户", 1);
                }
            }

            $reportPaymentModel = new SaleReportPayment();
            $list = $reportPaymentModel->getStatisticPaymentSalerList($input, 0, 0);
            $list = $this->setStatisticPaymentSalerFormatter($list);
            
        } catch (Exception $e) {
            // echo $e->getMessage();exit();
            $list = [];
        }

        return $list;
    }

    // 个人业绩统计结果格式化
    private function setStatisticPaymentSalerFormatter($list){
        if (count($list) > 0) {
            $teamLogic = new TeamLogic();
            $userIds = array_column($list, "saler_id");
            $userTopTeamIds = $teamLogic->getUserTopTeamId($userIds);

            foreach ($list as $key => &$item) {
                $saler_id = $item["saler_id"];
                $item["team_id"] = intval($item["team_id"]);
                $item["team_name"] = strval($item["team_name"]);
                $item["team_leader"] = intval($item["team_leader"]);
                $item["team_leader_name"] = strval($item["team_leader_name"]);
                $item["team_achievement"] = round($item["team_achievement"], 2);
                $item["saler_indicators"] = round($item["saler_indicators"], 2);
                $item["saler_achievement"] = round($item["saler_achievement"], 2);
                $item["platmoney_achievement"] = round($item["platmoney_achievement"], 2);
                $item["saler_noplat_achievement"] = round($item["saler_noplat_achievement"], 2);
                $item["saler_finish_ratio"] = round($item["saler_finish_ratio"] * 100, 2);

                $item["top_team_id"] = 0;
                $item["top_team_name"] = "";
                if (array_key_exists($saler_id, $userTopTeamIds)) {
                    $item["top_team_id"] = $userTopTeamIds[$saler_id]["team_id"];
                    $item["top_team_name"] = $userTopTeamIds[$saler_id]["team_name"];
                }
            }
        }

        return $list;
    }

    public function getVipCountReport($input)
    {
        $model = new UserVip();
        $cityVipCount = $model -> getCityVipCountList($input);
        
        $list = [];
        $vipfour_num = $vipthree_num = $vip_multiple_count = $vipone_num = $vipone_multiple_count = $viptwo_multiple_count = $old_vip_count = 0;

        foreach ($cityVipCount as $k => $v) {
            $list[$k]['cs'] = $v['city_id'];
            $list[$k]['cname'] = $v['cname'];
            $list[$k]['vipone_multiple_count'] = floatval($v['vipone_multiple_count']);
            $list[$k]['viptwo_multiple_count'] = floatval($v['viptwo_multiple_count']);
            $list[$k]['vip_multiple_count'] = $v['vipone_multiple_count'] + $v['viptwo_multiple_count'] + $v['vipthree_num'] + $v['vipfour_num'];
            $list[$k]['vipthree_num'] = floatval($v['vipthree_num']);
            $list[$k]['vipfour_num'] = floatval($v['vipfour_num']);

            $old_vip_count += $v['old_vip_count'];

            if($list[$k]['vip_multiple_count'] == 0){
                unset($list[$k]);
                continue;
            }

            if(!empty($input['level']) && $list[$k]['vip_multiple_count'] < (int)$input['level']){
                unset($list[$k]);
                continue;
            }

            if(!empty($input['dept'])){
                if($input['dept'] == 1){
                    $manager = [0, 2];
                }else{
                    $manager = [1];
                }

                if(!in_array($v['manager'], $manager)){
                    unset($list[$k]);
                    continue;
                }
            }

            $vip_multiple_count += $list[$k]['vip_multiple_count'];
            $vipone_num += $v['vipone_num'];
            $vipone_multiple_count += $v['vipone_multiple_count'];
            $viptwo_multiple_count += $v['viptwo_multiple_count'];
            $vipthree_num +=  $v["vipthree_num"];
            $vipfour_num += $v['vipfour_num'];
        }

        !empty($list) && $list = multi_array_sort($list, 'vip_multiple_count', SORT_DESC);

        $userCompany = new UserCompany();
        $twoCount = $userCompany->getAllTwoUserCount(2);
        $fourCount = $userCompany->getAllTwoUserCount(4);
        return [
            'city_num' => count($list),
            'vip_sum_num' => $vipone_num + $viptwo_multiple_count + $vipthree_num + $vipfour_num,
            'vip_multiple_count' => $vip_multiple_count,
            'vipone_num' => $vipone_num,
            'vipone_multiple_count' => $vipone_multiple_count,
            'viptwo_multiple_count' => $viptwo_multiple_count,
            'viptwo_multiple_count_invalid' => (int)$twoCount-$viptwo_multiple_count,
            'old_vip_count' => $old_vip_count,
            "vipthree_num" => $vipthree_num,
            "vipfour_num" => $vipfour_num,
            'vipfour_multiple_count_invalid' => (int)$fourCount - $vipfour_num,

            'list' => $list
        ];
    }
}