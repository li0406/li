<?php

namespace Home\Model\Logic;

use Think\Exception;
use Common\Enums\PnpCodeEnum;
use Home\Model\Db\OrderInfoModel;
use Home\Model\Db\PnpOrderMapModel;
use Home\Model\Db\PnpTelephoneModel;
use Home\Model\Db\PnpOrderUnbindModel;
use Home\Model\Service\AdminApiService;
use Home\Model\Service\PnpServiceModel;

class PnpLogicModel
{
    public function getPnpList($tel,$city,$status,$page)
    {
        $service = new AdminApiService();
        $result = $service->getPnpList($tel,$city,$status,$page);
        $list["list"] = $result["list"];
        $list["page"] = "";
        if ($result["page"]["total_number"] > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($result["page"]["total_number"],$result["page"]["page_size"]);
            $show = $p->show();
            $list["page"] = $show;
        }
        return $list;
    }

    public function getDropDowCity()
    {
        $service = new AdminApiService();
        $result = $service->getDropDowCity();
        return $result["list"];
    }

    public function getHistoryList($tel,$order_id,$page)
    {
        $service = new AdminApiService();
        $result = $service->getHistoryList($tel,$order_id,$page);
        $list["list"] = $result["list"];
        $list["page"] = "";
        if ($result["page"]["total_number"] > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($result["page"]["total_number"],$result["page"]["page_size"]);
            $show = $p->show();
            $list["page"] = $show;
        }
        return $list;
    }

    public function getVoliceList($order_id)
    {
        $service = new AdminApiService();
        $result = $service->getVoliceList($order_id);
        return $result["list"];
    }

    public function unBindTelByTelx($tel)
    {
        $model = new PnpTelephoneModel();
        $result = $model->getTelInfoByTelx($tel);

        if (count($result) > 0) {
            $service = new PnpServiceModel();
            $result = $service->unBindTelByTel($result["tel_a"]);
            if ($result["error_code"] == 0) {
                return true;
            }
        }
        return false;
    }

    // 虚拟号管理-订单列表
    public function getOrderMapStatsList($input, $limit = 20){
        $orderMapModel = new PnpOrderMapModel();
        $count = $orderMapModel->getOrderMapStatsCount($input);

        import('Library.Org.Util.Page');
        $pageClass = new \Page($count, $limit);
        $page = $pageClass->show();

        if ($count > 0) {
            $list = $orderMapModel->getOrderMapStatsList($input, $pageClass->firstRow, $pageClass->listRows);
            if (count($list) > 0) {
                $orderIds = array_column($list, "order_id");

                // 查询订单通话统计
                $callList = $orderMapModel->getOrderCallStats($orderIds);
                $callList = array_column($callList, null, "order_id");

                // 查询订单分配次数统计
                $orderInfoModel = new OrderInfoModel();
                $allotList = $orderInfoModel->getOrderAllotStats($orderIds);
                $allotList = array_column($allotList, null, "order_id");

                foreach ($list as $key => &$item) {
                    $order_id = $item["order_id"];
                    $item["tel"] = substr_replace($item["tel"], "*****", 3, 5);
                    $item["csos_date"] = date("Y-m-d H:i:s", $item["lasttime"]);
                    $item["type_fw_name"] = $item["type_fw"] == 1 ? "分单" : "赠单";

                    // 订单分配次数
                    $item["allot_num"] = $allotList[$order_id]["allot_num"] ?? 0;

                    // 通话统计
                    $item["call_num"] = $callList[$order_id]["call_num"] ?? 0;
                    $item["call_on_num"] = $callList[$order_id]["call_on_num"] ?? 0;
                    $item["call_un_num"] = $item["call_num"] - $item["call_on_num"];
                }
            }
        }

        return [
            "list" => $list,
            "page" => $page
        ];
    }

    // 获取订单装修公司绑定记录
    public function getOrderCompanyMapList($order_id){
        $orderMapModel = new PnpOrderMapModel();
        $list = $orderMapModel->getOrderMapBindList($order_id);

        $stats = [
            "call_num" => 0,
            "call_on_num" => 0,
            "call_un_num" => 0
        ];

        $glist = [];
        foreach ($list as $key => $item) {
            $company_id = $item["company_id"];
            $item["account_type"] = $item["employee_id"] ? "子账号" : "主账号";
            $item["expire_date"] = date("Y-m-d H:i:s", $item["expire_time"]);
            $item["tel_b"] = substr_replace($item["tel_b"], "*****", 3, 5);

            // 未拨通数量
            $item["call_un_num"] = $item["call_num"] - $item["call_on_num"];

            // 呼叫数量统计
            $stats["call_num"] += $item["call_num"];
            $stats["call_on_num"] += $item["call_on_num"];
            $stats["call_un_num"] += $item["call_un_num"];

            if (!array_key_exists($company_id, $glist)) {
                $glist[$company_id] = [
                    "company_id" => $company_id,
                    "company_jc" => $item["company_jc"],
                    "company_qc" => $item["company_qc"],
                    "list" => []
                ];
            }

            $glist[$company_id]["list"][] = $item;
        }

        return [
            "glist" => array_values($glist),
            "stats" => $stats,
        ];
    }

    // 录音记录
    public function getOrderVoliceLog($order_id, $company_id, $sub_id){
        $orderMapModel = new PnpOrderMapModel();
        $list = $orderMapModel->getOrderMapVoliceList($order_id, $company_id, $sub_id);

        foreach ($list as $key => &$item) {
            $item["tel_a"] = substr_replace($item["tel_a"], "*****", 3, 5);
            $item["tel_b"] = substr_replace($item["tel_b"], "*****", 3, 5);
            $item["created_date"] = date("Y-m-d H:i:s", $item["created_at"]);
            $item["record_url"] = $item["record_url_save"] ? $item["record_url_save"] : $item["record_url"];

            $item["call_action"] = $item["releasedir"];
            $item["call_action_name"] = PnpCodeEnum::getItem("releasedir", $item["releasedir"]);

            if ($item["calltype"] == PnpCodeEnum::CODE_CALLTYPE_CALLING) {
                $item["tel_main"] = $item["tel_a"];
                $item["tel_called"] = $item["tel_b"];
            } else {
                $item["tel_main"] = $item["tel_b"];
                $item["tel_called"] = $item["tel_a"];
            }

            unset($item["tel_a"], $item["tel_b"]);
            unset($item["releasedir"], $item["record_url_save"]);
        }

        return $list;
    }

    // 订单解绑
    public function unbindOrderMap($order_id){
        try {
            $pnpService = new PnpServiceModel();
            $result = $pnpService->unbindOrderTel($order_id);
            if ($result["error_code"] != 0) {
                throw new Exception($result["error_msg"], $result["error_code"]);
            }

            $orderUnbindModel = new PnpOrderUnbindModel();
            $info = $orderUnbindModel->getOrderUnbindLog($order_id);
            if (empty($info)) {
                $data = [
                    "order_id" => $order_id,
                    "op_uid" => session("uc_userinfo.id"),
                    "created_at" => time()
                ];

                $result = $orderUnbindModel->addLogInfo($data);
                if ($result === false) {
                    throw new Exception("数据库处理失败");
                }
            }

            $log = array(
                "remark" => "管理员解绑订单虚拟号绑定",
                "logtype" => "axb_order_unbind",
                "action_id" => $order_id,
                "info" => $_POST
            );
            D("LogAdmin")->addLog($log);
            
            $errcode = 0;
            $errmsg = "请求成功";
        } catch (Exception $e) {
            $errmsg = $e->getMessage();
            $errcode = $e->getCode() ? : 1000001;
        }

        return [
            "errcode" => $errcode,
            "errmsg" => $errmsg
        ];
    }
}