<?php


namespace app\common\model\logic;


use app\common\model\db\CompanyVisit;
use app\common\model\db\OrderCompanyReview;
use app\common\model\db\OrderReviewNew;
use app\common\model\db\OrderReviewNewHistory;
use app\index\enum\CompanyVisitCodeEnum;
use app\index\enum\OrderCodeEnum;

class OrderReviewNewLogic
{
    protected $model;

    public function __construct()
    {
        $this->model = new OrderReviewNew();
    }


    /**
     * 根据订单id查询新回访中是否有该订单
     * @param $ordersId
     * @return array
     */
    public function hasOrders($ordersId)
    {
        return $this->model->hasOrders($ordersId);
    }

    public function getExportList($input)
    {
        $list = $this->model->getPageList($input, 0, 0);
        $list = $this->setFormatter($list);

        return ["count" => count($list), "list" => $list];
    }

    /**
     * 将 company_vist 表中的 visit_status 转化为 order_review_new 表中的 review_type
     * @param $status
     * @return array
     */
    private function convertStatusToType($status)
    {
        // 回访状态:1.预处理 2.新单 3.已量房 4.未量房 5.已签单 6.待定单 7.终结单
        if ($status == 1) {
            $type = [1, 2];
        } else if ($status == 2) {
            $type = [3, 5];
        } else {
            $type = [4, 6,];
        }

        return $type;
    }

    public function getPageList($input, $page = 1, $limit = 20)
    {
        //设置 visit_status = '';
        //主动中回访状态分为未回访（对应预处理，新单），已量房，未量房，已签单，待定单，无效单（对应终结单）
        if (isset($input['visit_status']) && $input['visit_status']) {
            $input['review_type'] = $this->convertStatusToType($input['visit_status']);
        }
        $count = $this->model->getPageCount($input);

        $list = [];
        if ($count > 0) {
            $offset = ($page - 1) * $limit;
            $list = $this->model->getPageList($input, $offset, $limit);
            $list = $this->setFormatter($list);
        }

        return ["count" => $count, "list" => $list];
    }

    private function setFormatter($list)
    {
        $ocrModel = new OrderCompanyReview();
        foreach ($list as $key => $item) {
            // 获取订单量房状态
            $order_lf_status = 1;
            $orders = $ocrModel->getOrderLiangfangStatus(["orderid" => $item["order_id"]]);
            if (count($orders) == 1 && $orders[0]["choose_liangfang"] >= 1) {
                $order_lf_status = 2;
            }
            $list[$key]["date_real"] = date("Y-m-d H:i:s", $item["time_real"]);
            $list[$key]["created_date"] = date("Y-m-d H:i:s", $item["created_at"]);
            $list[$key]["leixing_name"] = OrderCodeEnum::getItem("leixing", $item["lx"]);
            $list[$key]["type_fw_name"] = OrderCodeEnum::getItem("type_fw", $item["type_fw"]);
            $list[$key]["order_lf_status"] = $order_lf_status;
            $list[$key]["order_lf_name"] = CompanyVisitCodeEnum::getItem("order_lf_status", $order_lf_status);
            $list[$key]["visit_type_name"] = '主动';
            $list[$key]["visit_step_name"] = CompanyVisitCodeEnum::getItem("visit_step", $item["visit_step"], "--");
            $list[$key]["visit_status_name"] = CompanyVisitCodeEnum::getItem("review_type", $item["visit_status"]);
            $list[$key]["status_push_tag"] = $list[$key]["visit_status_name"];
            $list[$key]["remark_type_name"] = strval($item["remark_type_name"]);
            $list[$key]["order_type"] = 1;

            // 有效无效状态根据回访内容判断（有回访内容则为有效）
            if (!empty($item["visit_content"])) {
                $list[$key]["valid_status"] = 1;
                $list[$key]["valid_status_name"] = "已回访";
            } else {
                $list[$key]["valid_status"] = 2;
                $list[$key]["valid_status_name"] = "未回访";
            }

            //回访状态:1.预处理 2.新单 3.已量房 4.未量房 5.已签单 6.待定单 7.终结单
            //其中已量房、已签约的订单在销售系统-回访订单中回访状态记为有效，未量房、待定单、终结单记为无效/
            if (in_array($item["visit_status"], [3, 5])) {
                $item["visit_status"] = 2;
            } elseif (in_array($item["visit_status"], [4, 6, 7])) {
                $item["visit_status"] = 3;
            }else {
                $item["visit_status"] = 1;
            }
            if ($item["visit_status"] == 2) {
                $list[$key]["status_push_tag"] .= CompanyVisitCodeEnum::getItem("visit_push", $item["visit_push"]);;
            }
            $list[$key]["visit_status"] = $item["visit_status"];

            $list[$key]["visit_date"] = "--";
            if ($list[$key]["visit_time"] > 0) {
                $list[$key]["visit_date"] = date("Y-m-d H:i:s", $item["visit_time"]);
            }
        }

        return $list;
    }
}