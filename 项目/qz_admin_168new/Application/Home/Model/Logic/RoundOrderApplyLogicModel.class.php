<?php
// +----------------------------------------------------------------------
// | SignbacknewLogicModel  签单返点会员逻辑层
// +----------------------------------------------------------------------

namespace Home\Model\Logic;

use Exception;
use Home\Model\Db\OrderInfoModel;
use Home\Model\Db\UserCompanyRoundOrderApplyModel;
use Common\Enums\OrderStatus as OrderStatusEnum;
use Home\Model\Logic\OrderReviewNewHistoryLogicModel;

use Home\Model\Service\MsgServiceModel;
use Common\Enums\MsgTemplateCodeEnum;

class RoundOrderApplyLogicModel {

    const EXAM_STATUS_PREPARE = 1; //预受理
    const EXAM_STATUS_CHECK = 11; // 待定单

    // const EXAM_STATUS_APPLY = 1; // 待审核
    const EXAM_STATUS_PASS = 2; // 已通过
    const EXAM_STATUS_FAIL = 3; // 未通过

    // 状态值列表
    public $exam_status_list = [
        self::EXAM_STATUS_PREPARE => "预受理",
        self::EXAM_STATUS_CHECK => "待定单",

        // self::EXAM_STATUS_APPLY => "待审核",
        self::EXAM_STATUS_PASS => "已通过",
        self::EXAM_STATUS_FAIL => "未通过"
    ];

    // 审核操作合法状态值
    public $exam_status_submit = [
        self::EXAM_STATUS_PASS,
        self::EXAM_STATUS_FAIL
    ];

    // 审核前置状态
    public $exam_status_pre = [
        self::EXAM_STATUS_PREPARE,
        self::EXAM_STATUS_CHECK
    ];

    // 申请原因
    private $apply_reason_list = array(
        1 => "业主75天内无法量房",
        2 => "业主表示不装修了",
        3 => "业主已动工",
        4 => "同行、中介等无装修意向",
        99 => "其它"
    );

    //合法的跟进状态
    private $round_track_status = [
        1 => '一次跟进',
        2 => '二次跟进',
        3 => '多次跟进'
    ];

    // 跟进内容
    private $round_track_content = [
        1 => '开场挂',
        2 => '未接通',
        3 => '关机/停机/空号',
        4 => '不配合回访',
        5 => '拒绝服务',
        99 => '其它'
    ];


    // 审核备注
    public $exam_remark_list = [
        "pass" => [
            "未量房/到店",
            "未接通/拒接",
            "拒绝服务",
            "无有效沟通",
            "只做单项改动",
            "只要设计",
            "预算低于5000"
        ],
        "fail" => [
            "已量房/到店",
            "已签约",
            "撞单",
            "电话沟通报价高",
            "还有装企正在跟进中",
            "再约量房/到店成功"
        ]
    ];

    private $roundOrderApplyModel;

    public function __construct(){
        $this->roundOrderApplyModel = new UserCompanyRoundOrderApplyModel();
        $this->orderReviewNewLogic = new OrderReviewNewLogicModel();
    }

    // 轮单申请审核列表
    public function getRoundOrderApplyList($input, $limit = 20){
        $count = $this->roundOrderApplyModel->getRoundOrderApplyCount($input);

        import("Library.Org.Util.Page");
        $pageClass = new \Page($count, $limit);
        $pageshow = $pageClass->show();

        if ($count > 0) {
            $list = $this->roundOrderApplyModel->getRoundOrderApplyList($input, $pageClass->firstRow, $pageClass->listRows);
            $list = $this->setFormatter($list);
        }

        return [
            "list" => $list,
            "count" => $count,
            "page" => $pageshow
        ];
    }


    // 轮单申请审核导出列表
    public function getRoundOrderApplyExportList($input){
        $list = $this->roundOrderApplyModel->getRoundOrderApplyList($input, $pageClass->firstRow, $pageClass->listRows);
        $list = $this->setFormatter($list);

        return [
            "list" => $list,
            "count" => count($list)
        ];
    }

    // 补轮审核统计
    public function getRoundOrderApplyTotal($exam_begin, $exam_end){
        $total = $this->roundOrderApplyModel->getRoundOrderApplyTotal();
        $examTotal = $this->roundOrderApplyModel->getRoundOrderApplyExamTotal($exam_begin, $exam_end);

        $statistic = [
            "apply_prepare_num" => intval($total["apply_prepare_num"]),
            "apply_check_num" => intval($total["apply_check_num"]),
            "apply_pass_num" => intval($examTotal["apply_pass_num"]),
            "apply_unpass_num" => intval($examTotal["apply_unpass_num"])
        ];

        return $statistic;
    }

    // 处理数据
    public function setFormatter($list){
        if (count($list) > 0) {
            foreach ($list as $key => &$item) {
                $item["round_money"] = floatval($item["round_money"]);
                $item["allot_date"] = date("Y-m-d H:i:s", $item["allot_time"]);
                $item["created_date"] = date("Y-m-d H:i:s", $item["created_at"]);
                $item["exam_date"] = $item["exam_time"] ? date("Y-m-d H:i:s", $item["exam_time"]) : "";

                $exam_status = $item["exam_status"];
                $item["exam_status_name"] = $this->exam_status_list[$exam_status];

                $item['review_type_name'] = '';
                if (!empty($item['review_type'])) {
                    $item['review_type_name'] = $this->orderReviewNewLogic->reviewType[$item['review_type']];
                }
            }
        }

        return $list;
    }


    // 轮单申请审核详情
    public function getRoundOrderApplyDetail($round_id){
        $detail = $this->roundOrderApplyModel->getRoundOrderApplyDetail($round_id);

        if (!empty($detail)) {
            $detail["date_real"] = date("Y-m-d H:i:s", $detail["time_real"]);
            $detail["apply_reason_text"] = $this->apply_reason_list[$detail["apply_reason"]];
            $detail["exam_date"] = $detail["exam_time"] ? date("Y-m-d H:i:s", $detail["exam_time"]) : "";

            //查询跟进记录列表
            //$track = $this->roundOrderApplyModel->roundApplyTrackList($detail['order_id']);
            $orderReviewNewHistoryLogic = new OrderReviewNewHistoryLogicModel();
            $track = $orderReviewNewHistoryLogic->getAll(['order_id' => $detail['order_id']]);

            $detail["exam_status_name"] = $this->exam_status_list[$detail["exam_status"]];
            
            foreach ($track as $k => &$v) {
                $v['track_status_str'] = $this->round_track_status[$v['track_status']];
                $v['track_type_content'] = $this->round_track_content[$v['track_type']];
            }

            $detail['trackDetail'] = $track;

            //查询审核记录列表
            $detail['checkDetail'] = $this->roundOrderApplyModel->roundApplyCheckList($detail['order_id'], $detail['company_id']);
            
            foreach ($detail['checkDetail'] as $k => &$v) {
                $v['exam_status_name'] = $this->exam_status_list[$v["exam_status"]];
            }

            // 查询分配公司补轮情况
            $detail["company_list"] = $this->roundOrderApplyModel->getOrderCompanyRoundApplyLog($detail["order_id"]);

            $allStatus = OrderStatusEnum::getAllStatus2();
            foreach ($detail["company_list"] as $key => &$item) {
                $item["status"] = intval($item["status"]);
                $item["status_name"] = $allStatus[$item["status"]];
                $item["disabled"] =  in_array($item["exam_status"], $this->exam_status_pre) ? "" : "disabled";

                $item["exam_status_name"] = $item['apply_id'] ? $this->exam_status_list[$item["exam_status"]] : "";
                $item["apply_reason_text"] = $item['apply_id'] ? $this->apply_reason_list[$item["apply_reason"]] : "";
                $item["apply_date"] = $item['apply_id'] ? date("Y-m-d H:i:s", $item["created_at"]) : "";

                $item["checked"] = "";
                if ($item["round_id"] && $item["apply_id"]) {
                    if ($detail["company_id"] == $item["com"] || in_array($item["exam_status"], $this->exam_status_submit)) {
                        $item["checked"] = "checked";
                    }
                }
            }

            // 是否满足审核条件
            $detail["can_exam"] = $this->checkExamRet($detail["company_list"]);

            $detail['allTrackStatus'] = $this->round_track_status;
            $detail['allTrackContent'] = $this->round_track_content;
        }

        return $detail;
    }

    // 轮单申请审核
    public function setRoundOrderApplyExam($order_id, $round_ids, $exam_status, $exam_remark){
        $userinfo = session("uc_userinfo");

        try {
            $this->roundOrderApplyModel->startTrans();
            $fen_companys = $this->roundOrderApplyModel->getOrderCompanyRoundApplyLog($order_id);
            if (empty($fen_companys)) {
                throw new Exception("轮单申请不存在", 4000001);
            }

            //是否满足审核条件
            $checked = $this->checkExamRet($fen_companys);
            if ($checked == 2) {
                throw new Exception("该单不满足审核条件", 4000001);
            }

            $list = [];
            foreach ($fen_companys as $key => $item) {
                $pre_status = $this->exam_status_pre;
                if ($item["apply_id"] && in_array($item["exam_status"], $pre_status)) {
                    if (in_array($item["round_id"], $round_ids)) {
                        $list[] = [
                            "apply_id" => $item["apply_id"],
                            "round_id" => $item["round_id"],
                            "company_id" => $item["com"],
                        ];
                    }
                }
            }

            // 检测是否有审核记录
            if (count($list) == 0) {
                throw new Exception("未找到需要审核的数据", 4000001);
            }

            $data = array(
                "exam_status" => $exam_status,
                "exam_operator" => $userinfo["id"],
                "exam_time" => time(),
                "exam_remark" => $exam_remark
            );

            $apply_ids = array_column($list, "apply_id");
            $this->roundOrderApplyModel->updateAll($apply_ids, $data);

            // 审核通过轮单次数+1
            if ($exam_status == self::EXAM_STATUS_PASS) {
                $companyAccountLogic = new CompanyAccountLogicModel();
                foreach ($list as $item) {
                    $ret = $companyAccountLogic->setAccountRoundOrderNumberInc($item["company_id"], $order_id);
                    if ($ret["errcode"] != 0) {
                        throw new Exception($ret["errmsg"], 1000001);
                    }
                }
            }

            // 操作日志类型
            switch ($exam_status) {
                case self::EXAM_STATUS_CHECK:
                    $act_type = UserCompanyRoundOrderApplyModel::LOG_ACT_TYPE_EXAM_GJ;
                    $act_name = "待定单";
                    break;

                case self::EXAM_STATUS_PASS:
                    $act_type = UserCompanyRoundOrderApplyModel::LOG_ACT_TYPE_EXAM_PASS;
                    $act_name = "审核通过";

                    $msg_template_id = MsgTemplateCodeEnum::ROUND_APPLY_CHECK_PASS;
                    $msg_params = [$order_id];
                    break;

                case self::EXAM_STATUS_FAIL:
                    $act_type = UserCompanyRoundOrderApplyModel::LOG_ACT_TYPE_EXAM_FAIL;
                    $act_name = "审核不通过";

                    $msg_template_id = MsgTemplateCodeEnum::ROUND_APPLY_CHECK_FAIL;
                    $msg_params = [$order_id, $exam_remark];
                    break;
            }

            $logAll = [];
            foreach ($list as $item) {
                $logAll[] = [
                    "apply_id" => $item["apply_id"],
                    "company_id" => $item["company_id"],
                    "order_id" => $order_id,
                    "act_type" => $act_type,
                    "act_name" => $act_name,
                    "act_remark" => "审核操作",
                    "paramter" => json_encode($data),
                    "operator" => $userinfo["id"],
                    "operator_name" => $userinfo["name"],
                    "created_at" => time(),
                    "updated_at" => time()
                ];
            }

            // 增加操作日志
            $this->roundOrderApplyModel->addLogAll($logAll);
            
            // 补轮审核通知
            if ($exam_status != self::EXAM_STATUS_CHECK) {
                $ygjNoticeLogic = new YgjNoticeLogicModel();
                foreach ($list as $item) {
                    $ret = $ygjNoticeLogic->setRoundApplyNotice($item["apply_id"], $item["company_id"]);
                }
            }

            // QZMSG 消息通知
            if (!empty($msg_template_id)) {
                $companyIds = array_column($list, "company_id");
                $linkparam = "?order_id=" . $order_id;

                $msgService = new MsgServiceModel();
                $msgService->sendCompanyMsg($msg_template_id, $companyIds, $linkparam, $order_id, "补轮审核", $msg_params);
            }

            $errcode = 0;
            $errmsg = "操作成功";
            $this->roundOrderApplyModel->commit();
        } catch (Exception $e) {
            $errmsg = $e->getMessage();
            $errcode = $e->getCode() ? : 1000001;
            $this->roundOrderApplyModel->rollback();
        }

        return ["errcode" => $errcode, "errmsg" => $errmsg];
    }

    // 验证是否可以审核
    private function checkExamRet($fen_companys){
        $fen_num = 0;
        $apply_num = 0;
        foreach ($fen_companys as $key => $item) {
            // 有效单才计算数量
            if ($item["round_id"] && $item["type_fw"] == 1) {
                $fen_num++;
                if ($item["apply_id"]) {
                    $apply_num++;
                }
            }
        }

        // 是否满足审核条件
        $retio = UserCompanyRoundOrderApplyModel::CAN_EXAM_RATIO;
        return ($apply_num / $fen_num) >= $retio ? 1 : 2;
    }

    //记录跟进信息
    public function makeRoundOrderTrack($data)
    {
        $userinfo = session("uc_userinfo");
        $time = time();
        $date = date('Y-m-d H:i:s', $time);

        $insert = [
            'order_id' => $data['order_id'],
            'track_type' => $data['track_type'],
            'track_info' => $data['track_info'],
            'track_time' => $date,
            'track_status' => $data['track_status'],
            'next_time' => $data['next_time'],
            'track_operator' => $userinfo["id"],
            'created_at' => $time,
            'updated_at' => $time
        ];

        return $this->roundOrderApplyModel->addTrackData($insert);
    }

    // 获取订单申请补轮情况
    public function getOrderRoundApplyList($order_id, $is_apply = true){
        $list = $this->roundOrderApplyModel->getOrderCompanyRoundApplyLog($order_id);

        foreach ($list as $key => $item) {
            $list[$key]["apply_exam"] = in_array($item["exam_status"], $this->exam_status_submit);
            if ($is_apply == true && empty($item["apply_id"])) {
                unset($list[$key]);
            }
        }

        return $list;
    }


}