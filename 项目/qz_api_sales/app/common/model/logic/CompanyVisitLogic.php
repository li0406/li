<?php

namespace app\common\model\logic;

use think\Db;
use think\Exception;
use think\facade\Request;
use app\common\model\db\Orders;
use app\common\model\db\OrderInfo;
use app\common\model\db\Adminuser;
use app\common\model\db\UmengRecord;
use app\common\model\db\CompanyVisit;
use app\common\model\db\OrderCsosNew;
use app\common\model\db\OrderReviewNew;
use app\common\model\db\OrderCompanyReview;
use app\common\model\db\OrderReviewNewPush;
use app\common\model\db\UserCompanyEmployee;

use app\common\model\service\MsgService;

use app\common\enum\BaseStatusCodeEnum;
use app\common\enum\MsgTemplateCodeEnum;
use app\index\enum\CompanyVisitCodeEnum;
use app\index\enum\OrderReviewNewCodeEnum;
use app\index\enum\OrderCodeEnum;
use Umeng\Umeng;

class CompanyVisitLogic {

    const CUSTOMER_ROLE = 2;

    /**
     * 获取订单回访记录
     * 默认查询有效状态
     * @param $order_id
     */
    public function getOrderVisit($order_id){
        $companyVisitModel = new CompanyVisit();
        $list = $companyVisitModel->getOrderVisitList($order_id);

        $list = array_map(function($item){
            $item["visit_date"] = date("Y-m-d H:i:s", $item["visit_time"]);
            $item["visit_step_name"] = CompanyVisitCodeEnum::getItem("visit_step", $item["visit_step"]);
            return $item;
        }, $list->toArray());

        return $list;
    }

    // 回访订单分页列表
    public function getPageList($input, $page = 1, $limit = 20){
        $companyVisitModel = new CompanyVisit();
        $count = $companyVisitModel->getPageCount($input);

        $list = [];
        if ($count > 0) {
            $offset = ($page - 1) * $limit;
            $list = $companyVisitModel->getPageList($input, $offset, $limit);
            $list = $this->setFormatter($list);
        }

        return ["count" => $count, "list" => $list];
    }

    // 获取导出数据
    public function getExportList($input){
        $companyVisitModel = new CompanyVisit();
        $list = $companyVisitModel->getPageList($input, 0, 0);
        $list = $this->setFormatter($list);

        return ["count" => count($list), "list" => $list];
    }

    // 格式化
    private function setFormatter($list){
        foreach ($list as $key => &$item) {
            $item["order_type"] = 2;
            $item["visit_type"] = 1; // 老回访回访类型强制改为被动（1125）
            $item["date_real"] = date("Y-m-d H:i:s", $item["time_real"]);
            $item["created_date"] = date("Y-m-d H:i:s", $item["created_at"]);
            $item["leixing_name"] = OrderCodeEnum::getItem("leixing", $item["lx"]);
            $item["type_fw_name"] = OrderCodeEnum::getItem("type_fw", $item["type_fw"]);
            $item["order_lf_name"] = CompanyVisitCodeEnum::getItem("order_lf_status", $item["order_lf_status"]);
            $item["visit_type_name"] = CompanyVisitCodeEnum::getItem("visit_type", $item["visit_type"]);
            $item["visit_step_name"] = CompanyVisitCodeEnum::getItem("visit_step", $item["visit_step"], "--");
            $item["visit_status_name"] = CompanyVisitCodeEnum::getItem("visit_status", $item["visit_status"], "--");
            $item["remark_type_name"] = CompanyVisitCodeEnum::getItem("review_remark", $item["remark_type"]);

            // 有效无效状态根据回访内容判断（有回访内容则为有效）
            if (!empty($item["visit_content"])) {
                $item["valid_status"] = 1;
                $item["valid_status_name"] = "已回访";
            } else {
                $item["valid_status"] = 2;
                $item["valid_status_name"] = "未回访";
            }

            $item["status_push_tag"] = $item["visit_status_name"];
            if ($item["valid_status"] == 1) {
                $item["status_push_tag"] .= CompanyVisitCodeEnum::getItem("visit_push", $item["visit_push"]);;
            }

            $item["visit_date"] = "--";
            if ($item["visit_time"] > 0) {
                $item["visit_date"] = date("Y-m-d H:i:s", $item["visit_time"]);
            }
        }

        return $list;
    }

    // 获取回访单详情
    public function getDetail($id, $order_id, $order_type){
        //区分是新回访还是老回访
        if ($order_type == 1) {
            // 新回访
            $reviewNewModel = new OrderReviewNew();
            $detail = $reviewNewModel->getVisitInfo($id, $order_id);
        } else {
            // 老回访
            $companyVisitModel = new CompanyVisit();
            $detail = $companyVisitModel->getById($id);
        }

        if (empty($detail)) {
            return false;
        }

        //1.新回访 2.老回访
        switch ($order_type){
            case 1:
                if(!empty($detail)) {
                    $detail["created_date"] = date("Y-m-d H:i:s", $detail["created_at"]);
                    $detail["visit_type_name"] = '主动';
                    $detail["visit_step_name"] = CompanyVisitCodeEnum::getItem("visit_step", $detail["visit_step"], "--");
                    $detail["visit_status_name"] = CompanyVisitCodeEnum::getItem("review_type", $detail["visit_status"]);

                    // 有效无效状态根据回访内容判断（有回访内容则为有效）
                    if (!empty($detail["visit_content"])) {
                        $detail["valid_status"] = 1;
                        $detail["valid_status_name"] = "已回访";
                    } else {
                        $detail["valid_status"] = 2;
                        $detail["valid_status_name"] = "未回访";
                    }

                    // 有回访操作
                    if (!in_array($detail['visit_status'], [1])) {
                        $detail["visit_time"] = $detail["updated_at"];
                        $detail["visit_date"] = date("Y-m-d H:i:s", $detail["updated_at"]);
                        if (!empty($detail["visit_content_sales"])) {
                            $detail["visit_content"] = $detail["visit_content_sales"];
                        }
                    }
                    
                    // 获取订单量房状态
                    $ocrModel = new OrderCompanyReview();
                    $order_lf_status = 1;
                    $orders = $ocrModel->getOrderLiangfangStatus(["orderid" => $detail["order_id"]]);
                    if (count($orders) == 1 && $orders[0]["choose_liangfang"] >= 1) {
                        $order_lf_status = 2;
                    }
                    $detail["order_lf_name"] = CompanyVisitCodeEnum::getItem("order_lf_status", $order_lf_status);

                    // 标记为已读
                    if ($detail["read_mark"] != 2) {
                        $reviewNewModel->editReviewInfo($order_id, ["read_mark" => 2]);
                    }
                }
                break;
            case 2:
                if (!empty($detail)) {
                    //老回访
                    $detail["created_date"] = date("Y-m-d H:i:s", $detail["created_at"]);
                    $detail["visit_type_name"] = CompanyVisitCodeEnum::getItem("visit_type", $detail["visit_type"]);
                    $detail["visit_step_name"] = CompanyVisitCodeEnum::getItem("visit_step", $detail["visit_step"], "--");
                    $detail["visit_status_name"] = CompanyVisitCodeEnum::getItem("visit_status", $detail["visit_status"]);
                    $detail["order_lf_name"] = CompanyVisitCodeEnum::getItem("order_lf_status", $detail["order_lf_status"]);
                    $detail["visit_date"] = $detail["visit_time"] ? date("Y-m-d H:i:s", $detail["visit_time"]) : "";
                    $detail["remark_type_name"] = CompanyVisitCodeEnum::getItem("review_remark", $detail["review_remark"]);
                    $detail["remark_type"] = $detail["review_remark"];

                    // 有效无效状态根据回访内容判断（有回访内容则为有效）
                    if (!empty($detail["visit_content"])) {
                        $detail["valid_status"] = 1;
                        $detail["valid_status_name"] = "已回访";
                    } else {
                        $detail["valid_status"] = 2;
                        $detail["valid_status_name"] = "未回访";
                    }

                    // 如果有销售回访反馈内容，显示销售回访反馈内容
                    if (!empty($detail["visit_content_sales"])) {
                        $detail["visit_content"] = $detail["visit_content_sales"];
                    }

                    // 要求回访的装修公司
                    $detail["visit_companys"] = $companyVisitModel->getVisitCompanyRelated($detail->id, true);
                    $visitCompanyIds = array_column($detail["visit_companys"], "company_id");

                    // 标记为已读
                    if ($detail["read_mark"] != 2) {
                        $companyVisitModel->saveData($id, ["read_mark" => 2]);
                    }
                }
                break;
        }

        // 添加订单回访位置
        $detail['order_type'] = intval($order_type);

        // 订单部分
        $orderModel = new Orders;
        $orderinfo = $orderModel->getOrderInfo($order_id, ["companys", "companyReview"]);

        if (!empty($orderinfo)) {
            $orderinfo = $orderinfo->toArray();

            $orderinfo['date_real'] = date('Y-m-d H:i:s', $orderinfo['time_real']);
            $orderinfo['type_fw_name'] = OrderCodeEnum::getItem('type_fw', $orderinfo['type_fw']);
            $orderinfo['leixing_name'] = OrderCodeEnum::getItem('leixing', $orderinfo['lx']);
            $orderinfo['lxs_name'] = OrderCodeEnum::getItem('lxs', $orderinfo['lxs']);
            $orderinfo['xiaoqu_type_name'] = OrderCodeEnum::getItem('xiaoqu_type', $orderinfo['xiaoqu_type']);
            $orderinfo['keys_name'] = OrderCodeEnum::getItem('keys', $orderinfo['keys']);
            $orderinfo["latlng"] = $orderinfo["lng"] .",". $orderinfo["lat"];

            $orderinfo['lxs_full_name'] = $orderinfo["leixing_name"]. "，" .$orderinfo["lxs_name"];
            $orderinfo['huxing_full_name'] = $orderinfo["huxing_name"]. "，" .$orderinfo["shi"]. "室，" .$orderinfo["mianji"] ."㎡";

            // IP_CITY
            $orderinfo["ip_city"] = "--";
            $ipCitys = get_city_by_ip($orderinfo["ip"]);
            if (count($ipCitys) >= 3 && !empty($ipCitys[2])) {
                $orderinfo["ip_city"] = $ipCitys[2];
            }

            $pushCompanyIds = [];
            // 推送装修公司默认选中
            if($order_type == 2){
                $pushCompanyIds = $visitCompanyIds;
            }

            if ($detail["visit_push"] == 2) {
                // 查询推送装修公司
                if ($order_type == 1) {
                    //新签返的公司
                    $pushCompanyIds = explode(',', $detail['push_company']);
                }
                if ($order_type == 2) {
                    $pushCompanys = $companyVisitModel->getVisitCompanyRelated($detail->id, false, true);
                    $pushCompanyIds = array_column($pushCompanys, "company_id");
                }
            }

            // 分配公司
            foreach ($orderinfo["companys"] as $key => &$company) {
                $company["push_checked"] = 0;
                $company["company_jc"] = $company["jc"];
                $company["company_qc"] = $company["qc"];
                if (in_array($company["company_id"], $pushCompanyIds)) {
                    $company["push_checked"] = 1;
                }

                unset($company["jc"], $company["qc"]);
            }

            // 量房公司
            $orderinfo["lf_companys"] = [];
            foreach ($orderinfo["company_review"] as $key => $item) {
                if (in_array($item["status"], [1,2,4])) {
                    $orderinfo["lf_companys"][] = [
                        "company_id" => $item["comid"],
                        "company_qc" => $item["qc"],
                        "company_jc" => $item["jc"],
                    ];
                }
            }

            unset($orderinfo["company_review"]);
        }

        return ["detail" => $detail, "orderinfo" => $orderinfo];
    }

    // 获取新增时下拉选项
    public function getAddOptions($order_id){
        $orderInfoModel = new OrderInfo();
        $companyList = $orderInfoModel->getOrderCompany($order_id);

        $companyList = array_map(function($val){
            return [
                "company_id" => $val["com"],
                "company_qc" => $val["qc"],
                "company_jc" => $val["jc"],
            ];
        }, $companyList->toArray());

        $options = [
            "visit_step_list" => CompanyVisitCodeEnum::getList("visit_step"),
            "company_list" => $companyList
        ];

        return $options;
    }

    /**
     * 新增回访单
     * @param $input
     * @return array
     */
    public function addInfo($input){
        $user = Request::instance()->user;

        try {
            // 进一步验证选择的装修公司
            $companyIds = explode(",", $input["company_ids"]);
            $companyIds = array_filter(array_unique($companyIds));
            if (count($companyIds) == 0) {
                throw new Exception("请选择要求回访的装修公司名称", 4000002);
            }

            // 获取订单未回访单
            $companyVisitModel = new CompanyVisit();
            $visitInfo = $companyVisitModel->getOrderNoVisit($input["order_id"]);
            if ($visitInfo && $visitInfo->visit_type == 1) { // 如果是被动回访单提示正在回访
                throw new Exception("该订单正在回访，请回访完后，再创建新的回访工单", 1000001);
            }

            $data = array(
                "visit_need" => $input["visit_need"],
                "visit_user_need" => $input["visit_user_need"],
                "updated_at" => time(),
            );

            if ($visitInfo && $visitInfo->visit_type == 2) {
                // 如果是主动回访单更改回访单数据
                if ($visitInfo->visit_step == 0) {
                    $data["visit_step"] = $input["visit_step"];
                }

                $visit_id = $visitInfo->id;
                $result = $companyVisitModel->saveData($visit_id, $data);
                $order_lf_status = $visitInfo->order_lf_status;
            } else {
                // 获取订单量房状态
                $order_lf_status = 1;
                $ocrModel = new OrderCompanyReview();
                $orders = $ocrModel->getOrderLiangfangStatus(["orderid" => $input["order_id"]]);
                if (count($orders) == 1 && $orders[0]["choose_liangfang"] >= 1) {
                    $order_lf_status = 2;
                }

                $data["order_id"] = $input["order_id"];
                $data["visit_step"] = $input["visit_step"];
                $data["order_lf_status"] = $order_lf_status;
                $data["feedbacker"] = $user["user_name"];
                $data["creator"] = $user["user_id"];
                $data["created_at"] = time();

                $visit_id = $result = $companyVisitModel->insertGetId($data);
            }

            if (empty($result)) {
                throw new Exception(BaseStatusCodeEnum::CODE_5000002, 5000002);
            }

            // 生成回访单装修公司关联数据
            $companyVisitModel->deleteVisitRelated($visit_id);
            foreach ($companyIds as $company_id) {
                $companyVisitModel->addVisitRelated($visit_id, $company_id);
            }

            // 更改客服回访订单
            $orderReviewLogic = new OrderReviewLogic();
            $orderReviewLogic->updateOrderReview($input["order_id"], $order_lf_status);

            // 查询订单归属人
            $orderCsosNewModel = new OrderCsosNew();
            $csosInfo = $orderCsosNewModel->findByOrderId($input["order_id"]);
            if (count($csosInfo) > 0 && !empty($csosInfo["user_id"])) {
                // 给客服发送消息通知
                $msgService = new MsgService();
                $msgService->sendSystemMsg("201911251011", $csosInfo["user_id"], "?", $visit_id, "回访单");
            }

            // // 给客服发送消息通知
            // $adminuserModel = new Adminuser();
            // $customerList = $adminuserModel->getListByUids(static::CUSTOMER_ROLE);
            // if (count($customerList) > 0) {
            //     $msgService = new MsgService();
            //     $sendusers = array_column($customerList, "id");
            //     $msgService->sendSystemMsg("201911251011", $sendusers, "?", $visit_id, "回访单");
            // }

            $error_code = 0;
            $error_msg = BaseStatusCodeEnum::CODE_0;
        } catch (Exception $e) {
            $error_code = $e->getCode();
            $error_msg = $e->getMessage();
        }

        return sys_response($error_code, $error_msg);
    }

    // 回访单撤回（删除）
    public function deleteInfo($id){
        try {
            $companyVisitModel = new CompanyVisit();
            $info = $companyVisitModel->getById($id);
            if (empty($info)) {
                throw new Exception("回访单不存在", 4000001);
            } else if ($info->visit_status != 1) {
                throw new Exception("该单已回访不能撤回", 1000001);
            }

            $result = $info->delete();
            if ($result == false) {
                throw new Exception("撤回失败", 5000002);
            }

            // 删除装修公司关联数据
            $companyVisitModel->deleteVisitRelated($id);

            $error_code = 0;
            $error_msg = BaseStatusCodeEnum::CODE_0;
        } catch (Exception $e) {
            $error_code = $e->getCode();
            $error_msg = $e->getMessage();
        }

        return sys_response($error_code, $error_msg);
    }

    // 回访单推送
    public function pushInfo($input){
        Db::startTrans();

        try {
            // 二次验证装修公司
            $pushCompanyIds = explode(",", $input["push_company_ids"]);
            $pushCompanyIds = array_filter(array_unique($pushCompanyIds));
            if (count($pushCompanyIds) == 0) {
                throw new Exception("请选择要推送的装修公司名称", 4000002);
            }

            $companyVisitModel = new CompanyVisit();
            $info = $companyVisitModel->getById($input["id"]);
            if (empty($info)) {
                throw new Exception("回访单不存在", 4000001);
            } else if ($info->visit_status == 1) {
                throw new Exception("该回访单未回访不能推送", 1000001);
            }

            // 1. 修改销售提交的反馈内容
            $data = [
                "visit_content_sales" => $input["visit_content"],
                "visit_push" => 2,
                "updated_at" => time()
            ];
            $result = $companyVisitModel->saveData($input["id"], $data);
            if ($result == false) {
                throw new Exception("保存失败", 5000002);
            }

            // 2. 清除原推送装修公司数据关联
            $companyVisitModel->cleanVisitPushRelated($input["id"]);
            $companyVisitModel->cleanVisitEmptyRelated($input["id"]);

            // 3. 生成新的装修公司推送数据
            $relatedList = $companyVisitModel->getVisitRelated($input["id"]);
            foreach ($pushCompanyIds as $company_id) {
                $related = [
                    "visit_id" => $input["id"],
                    "company_id" => $company_id,
                    "created_at" => time(),
                    "updated_at" => time(),
                    "related_push" => 1,
                    "related_push_isread" => 0
                ];
                foreach ($relatedList as $item) {
                    if ($company_id == $item["company_id"]) {
                        $related["id"] = $item["id"];
                        break;
                    }
                }

                $companyVisitModel->saveVisitRelated($related);
                unset($related);
            }

            // 通知装修公司订单已回访
            $this->sendQzMsg($pushCompanyIds, $info["order_id"]);

            // 友盟发送APP通知
            $this->sendUmengMsg($pushCompanyIds, $info["order_id"]);

            Db::commit();
            $error_code = 0;
            $error_msg = BaseStatusCodeEnum::CODE_0;
        } catch (Exception $e) {
            Db::rollback();
            $error_msg = $e->getMessage();
            $error_code = $e->getCode() ? : 1000001;
        }

        return sys_response($error_code, $error_msg);
    }

    // 新回访单推送
    public function pushNewInfo($input, $user)
    {
        Db::startTrans();

        try {
            // 二次验证装修公司
            $pushCompanyIds = explode(",", $input["push_company_ids"]);
            $pushCompanyIds = array_filter(array_unique($pushCompanyIds));
            if (count($pushCompanyIds) == 0) {
                throw new Exception("请选择要推送的装修公司名称", 4000002);
            }

            $reviewNewDb = new OrderReviewNew();
            $info = $reviewNewDb->getInfoByOrder($input["order_id"]);

            // 1. 修改销售提交的反馈内容
            $data = [
                "visit_content_sales" => $input["visit_content"],
                "visit_push" => 2,
                "push_company" => $input["push_company_ids"], //最新推送的装修公司
                "updated_at" => time()
            ];
            $result = $reviewNewDb->editReviewInfo($input["order_id"], $data);
            if ($result == false) {
                throw new Exception("保存失败", 5000002);
            }
            //2.添加销售推送记录
            $save = [];
            foreach ($pushCompanyIds as $v) {
                $save[] = [
                    'company_id' => $v,
                    'order_id' => $info["order_id"],
                    'visit_step' => $info["next_visit_step"],
                    'visit_content' => $info["review_feedback"],
                    'visit_content_sales' => $input["visit_content"],
                    'visit_push_isread' => 0,
                    'visit_push' => 2,
                    'op_uid' => $user['user_id'],
                    'op_name' => $user['user_name'],
                    'created_at' => time(),
                    'updated_at' => time(),
                ];
            }
            $pushDb = new OrderReviewNewPush();
            $status = $pushDb->addPushInfo($save);
            if ($status == false) {
                throw new Exception("保存失败", 5000002);
            }

            // 通知装修公司订单已回访
            $this->sendQzMsg($pushCompanyIds, $info["order_id"]);

            // 友盟发送APP通知
            $this->sendUmengMsg($pushCompanyIds, $info["order_id"]);

            Db::commit();
            $error_code = 0;
            $error_msg = BaseStatusCodeEnum::CODE_0;
        } catch (Exception $e) {
            Db::rollback();
            $error_msg = $e->getMessage();
            $error_code = $e->getCode() ? : 1000001;
        }

        return sys_response($error_code, $error_msg);
    }

    // 发送消息提醒
    public function sendQzMsg(array $companyIds, $order_id){
        // 查询公司员工
        $companyEmployeeLogic = new CompanyEmployeeLogic();
        $companyEmployees = $companyEmployeeLogic->getOrderCompanyEmployees($companyIds, $order_id);

        $msgService = new MsgService();
        $template_id = MsgTemplateCodeEnum::ORDER_REVIEW_PUSH;
        $linkparam = "?righttabname=feedback&order_id=" . $order_id;

        // 向公司发送消息提醒
        $msgService->sendCompanyMsg($template_id, $companyIds, $linkparam, $order_id, "订单回访");

        // 向公司员工发送消息提醒
        if (!empty($companyEmployees)) {
            foreach ($companyEmployees as $company_id => $employees) {
                $employeeIds = array_column($employees, "employee_id");
                $msgService->sendCompanyMsg($template_id, $company_id, $linkparam, $order_id, "订单回访", [], $employeeIds);
            }
        }

        return true;
    }


    /**
     * 发送友盟消息
     * @param array $companyIds
     */
    public function sendUmengMsg(array $companyIds, $order_id){
        // 查询装修公司友盟token
        $umengRecordModel = new UmengRecord();
        $comTokenList = $umengRecordModel->getCompanyDeviceToken($companyIds);

        if (count($comTokenList) > 0) {
            // 查询未读回访订单数
            $companyVisitModel = new CompanyVisit();
            $unreadList = $companyVisitModel->getCompanyUnReadOrderCount($companyIds);
            $unreadList = array_column($unreadList, null, "company_id");

            // 读取配置
            $iosConfig = config("app.umeng.ios");
            $androidConfig = config("app.umeng.android");

            $umengtype = Umeng::TYPE_VISIT_OREDER;
            $umengIos = new Umeng($iosConfig["appkey"], $iosConfig["appsecret"]);
            $umengAndroid = new Umeng($androidConfig["appkey"], $androidConfig["appsecret"]);

            $temp = "您有%d条订单已回访，请及时查看>";
            foreach ($comTokenList as $item) {
                $company_id = $item["comid"];
                $device_token = $item["device_tokens"];
                if (array_key_exists($company_id, $unreadList)) {
                    $unread_num = $unreadList[$company_id]["order_count"];
                    $msg = sprintf($temp, $unread_num);

                    $umengIos->sendIOSListcast($device_token, $order_id, $umengtype, $msg, 1);
                    $umengAndroid->sendAndroidListcast($device_token, $order_id, $umengtype, $msg, $msg, $msg, 'go_app',
                        1, true, $androidConfig["mi_activity"]);
                }
            }
        }

        return true;
    }

}
