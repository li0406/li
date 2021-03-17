<?php
// +----------------------------------------------------------------------
// | SignbackuserController  签单返点会员
// +----------------------------------------------------------------------

namespace Home\Controller;

use Exception;
use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\RoundOrderApplyLogicModel;
use Home\Model\Logic\OrderReviewLogicModel;
use Home\Model\Logic\AdminAuthLogicModel;
use Home\Model\Logic\AdminuserLogicModel;
use Home\Model\Logic\QuyuLogicModel;
use Common\Enums\OrdersInfo as OrdersInfoEnum;
use Common\Enums\DepartmentEnum;
use Common\Enums\RbacRoleEnum;

class RoundorderapplyController extends HomeBaseController {

    // 签返补轮审核管理
    public function apply_list(){
        $input = I("get.");

        //默认 预受理
        if (!array_key_exists('exam_status', $input)) {
            $input['exam_status'] = 1;
        }

        // 默认查询时间
        if (empty($input['begin']) && empty($input['end']) && empty($input['check_begin']) && empty($input['check_end']) && !in_array($input['exam_status'], [RoundOrderApplyLogicModel::EXAM_STATUS_PASS, RoundOrderApplyLogicModel::EXAM_STATUS_FAIL])) {
            $input["begin"] = date("Y-m-d", strtotime("-1 year"));
            $input["end"] = date("Y-m-d");
        } else if (empty($input["begin"]) && !empty($input["end"])) {
            $input["begin"] = date("Y-m-d", strtotime("-1 year", strtotime($input["end"])));
        } else if (!empty($input["begin"]) && empty($input["end"])) {
            $input["end"] = date("Y-m-d", strtotime("+1 year", strtotime($input["begin"])));
        }


        //审核日期查询，默认最近1年
        if ((in_array($input['exam_status'], [RoundOrderApplyLogicModel::EXAM_STATUS_PASS,RoundOrderApplyLogicModel::EXAM_STATUS_FAIL])) && ((empty($input['check_begin']) && empty($input['check_end'])) && (empty($input['begin']) && empty($input['end'])))) {
            $input['check_begin'] = date("Y-m-d", strtotime("-1 year"));
            $input['check_end'] = date("Y-m-d");
        } else if ((!isset($input['check_begin']) || empty($input['check_begin'])) && isset($input['check_end']) && !empty($input["check_end"])) {
            $input["check_begin"] = date("Y-m-d", strtotime("-1 year", strtotime($input["check_end"])));
        } else if (isset($input['check_begin']) && !empty($input["check_begin"]) && (!isset($input['check_end']) || empty($input["check_end"]))) {
            $input["check_end"] = date("Y-m-d", strtotime("+1 year", strtotime($input["check_begin"])));
        }

        if (!empty($input["order"])) {
            unset($input["begin"], $input["end"], $input['check_begin'], $input['check_end']);
        }


        // 获取下拉城市
        $quyuLogic = new QuyuLogicModel();
        $cityList = $quyuLogic->getSimpleCitys();
        if ($this->User["uid"] != RbacRoleEnum::ROLE_ADMIN) {
            // 权限城市
            $adminAuthLogic = new AdminAuthLogicModel();
            $input["citys"] = $adminAuthLogic->getAuthCitys();

            foreach ($cityList as $key => $city) {
                if (!in_array($city["cid"], $input["citys"])) {
                    unset($cityList[$key]);
                }
            }

            // 如果查询了无权限城市设置查询城市为none
            if (!empty($input["city"]) && !in_array($input["city"], $input["citys"])) {
                $input["city"] = "none";
            }
        }
        
        // 获取所有装中客服
        $adminuserLogic = new AdminuserLogicModel();
        $kfList = $adminuserLogic->getAdminuserByDeptId(DepartmentEnum::DEPARTMENT_YCZZ_ZZKF);

        // 补轮列表
        $roundOrderApplyLogic = new RoundOrderApplyLogicModel();
        $data = $roundOrderApplyLogic->getRoundOrderApplyList($input);

        $exam_status_list = $roundOrderApplyLogic->exam_status_list;
        $exam_pre_status = $roundOrderApplyLogic->exam_status_pre;

        $show_export = 0;
        $prower = $this->getPrower();
        if ($input["exam_status"] == RoundOrderApplyLogicModel::EXAM_STATUS_PASS && !empty($prower["apply_export"])) {
            $show_export = 1;
        }

        $this->assign("data", $data);
        $this->assign("input", $input);
        $this->assign("total", $total);
        $this->assign("prower", $prower);
        $this->assign("citys", $cityList);
        $this->assign("show_export", $show_export);
        $this->assign("exam_status_list", $exam_status_list);
        $this->assign("exam_pre_status", $exam_pre_status);
        $this->assign('kfList', $kfList);
        $this->assign('month_begin', date("Y-m-01"));
        $this->assign('month_end', date("Y-m-d"));
        $this->display();
    }

    // 查询申请补轮统计数据
    public function apply_statistic(){
        $exam_begin = I("get.exam_begin");
        $exam_end = I("get.exam_end");

        if (empty($exam_begin) && empty($exam_end)) {
            $exam_begin = date("Y-m-01");
            $exam_end = date("Y-m-d");
        }

        $roundOrderApplyLogic = new RoundOrderApplyLogicModel();
        $total = $roundOrderApplyLogic->getRoundOrderApplyTotal($exam_begin, $exam_end);

        $this->ajaxReturn([
            "error_code" => 0,
            "error_msg" => "请求成功",
            "data" => [
                "total" => $total
            ]
        ]);
    }

    // 签返补轮审核详情
    public function apply_detail_bak(){
        $round_id = I("get.round_id");

        $roundOrderApplyLogic = new RoundOrderApplyLogicModel();
        $detail = $roundOrderApplyLogic->getRoundOrderApplyDetail($round_id);

        $lxs = OrdersInfoEnum::getLxs();

        $this->assign("lxs", $lxs);
        $this->assign("info", $detail);
        $this->assign("prower", $this->getPrower());
        $template = $this->fetch("apply_detail");
        $this->ajaxReturn([
            "error_code" => 0,
            "error_msg" => "请求成功",
            "data" => [
                "template" => $template
            ]
        ]);
    }

    public function apply_detail($value='')
    {
        $round_id = I("get.round_id");
        if(empty($round_id)){
            throw new Exception('不被允许的访问');
        }

        $roundOrderApplyLogic = new RoundOrderApplyLogicModel();
        $detail = $roundOrderApplyLogic->getRoundOrderApplyDetail($round_id);
        
        $exam_pre_status = $roundOrderApplyLogic->exam_status_pre;
        $this->assign('exam_pre_status', $exam_pre_status);

        $this->assign("info", $detail);
        $this->assign("prower", $this->getPrower());
        $this->assign("exam_remark_list", $roundOrderApplyLogic->exam_remark_list);
        $this->display();
    }

    // 签返补轮审核操作
    public function apply_exam(){
        if (IS_AJAX) {
            // 审核权限验证
            $prower = $this->getPrower();
            if ($prower["apply_exam"] == false) {
                $this->ajaxReturn([
                    "error_code" => 404,
                    "error_msg" => "你没有权限进行此操作",
                ]);
            }

            $order_id = I("post.order_id");
            $round_ids = I("post.round_ids");
            $exam_status = I("post.exam_status");
            $exam_remark = I("post.exam_remark");

            $roundOrderApplyLogic = new RoundOrderApplyLogicModel();

            if (empty($round_ids)) {
                $this->ajaxReturn(["error_code" => 4000001, "error_msg" => "请先选择审核公司"]);
            } else if (!in_array($exam_status, $roundOrderApplyLogic->exam_status_submit)) {
                $this->ajaxReturn(["error_code" => 4000001, "error_msg" => "请先选择审核状态"]);
            } else if (empty($exam_remark)) {
                $this->ajaxReturn(["error_code" => 4000001, "error_msg" => "请选择审核通过/不通过原因"]);
            }

            $ret = $roundOrderApplyLogic->setRoundOrderApplyExam($order_id, $round_ids, $exam_status, $exam_remark);

            $this->ajaxReturn([
                "error_code" => $ret["errcode"],
                "error_msg" => $ret["errmsg"]
            ]);
        }
    }

    public function pre_apply_exam(){
        if (IS_AJAX) {
            // 审核权限验证
            $prower = $this->getPrower();
            if ($prower["apply_exam"] == false) {
                $this->ajaxReturn([
                    "error_code" => 404,
                    "error_msg" => "你没有权限进行此操作",
                ]);
            }

            $order_id = I("post.order_id");
            $round_ids = I("post.round_ids");
            $exam_status = RoundOrderApplyLogicModel::EXAM_STATUS_CHECK;

            $roundOrderApplyLogic = new RoundOrderApplyLogicModel();

            $ret = $roundOrderApplyLogic->setRoundOrderApplyExam($order_id, $round_ids, $exam_status);

            $this->ajaxReturn([
                "error_code" => $ret["errcode"],
                "error_msg" => $ret["errmsg"]
            ]);
        }
    }


    public function apply_track()
    {
        if(IS_AJAX){
            // 审核权限验证
            $prower = $this->getPrower();
            if ($prower["apply_exam"] == false) {
                $this->ajaxReturn([
                    "error_code" => 404,
                    "error_msg" => "你没有权限进行此操作",
                ]);
            }

            $post = I('post.');
            $roundOrderApplyLogic = new RoundOrderApplyLogicModel();
            $res = $roundOrderApplyLogic->makeRoundOrderTrack($post);
            if(!$res){
                $this->ajaxReturn([
                    "error_code" => 4000001,
                    "error_msg" => "操作失败",
                ]);
            }

            $this->ajaxReturn([
                "error_code" => 0,
                "error_msg" => "操作成功",
            ]);
        }
    }

    // 补轮导出
    public function apply_export(){
        $prower = $this->getPrower();
        if (empty($prower["apply_export"])) {
            return $this->_error();
        }

        $input = I("get.");
        $roundOrderApplyLogic = new RoundOrderApplyLogicModel();
        $data = $roundOrderApplyLogic->getRoundOrderApplyExportList($input);

        try {
            // 标题
            $header = array(
                "订单编号",
                "城市/区县",
                "小区名称",
                "业主",
                "申请公司",
                "公司id",
                "订单金额",
                "订单分配时间",
                "回访状态",
                "回访内容",
                "下次回访时间",
                "回访人",
                "订单审核状态",
                "审核人",
                "审核时间",
                "补轮申请时间"
            );

            import("Library.Org.PHP_XLSXWriter.xlsxwriter");
            $writer = new \XLSXWriter();
            $writer->writeSheetRow("Sheet1", $header);

            foreach ($data["list"] as $key => $item) {
                $row = [
                    $item["order_id"] . " ", // 拼接空格防止格式转换
                    $item["city_name"] . "-" . $item["area_name"],
                    $item["xiaoqu"],
                    $item["yz_name"],
                    $item["company_qc"],
                    $item["company_id"],
                    $item["round_money"],
                    $item["allot_date"],
                    $item["review_type_name"],
                    $item["remark_type_name"],
                    $item["next_visit_time"],
                    $item["username"],
                    $item["exam_status_name"],
                    $item["exam_operator_user"],
                    $item["exam_date"],
                    $item["created_date"]
                ];

                $writer->writeSheetRow("Sheet1", $row);
            }

            ob_end_clean();
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");;
            header('Content-Disposition:attachment;filename="签返补轮审核列表.xls"');
            header("Content-Transfer-Encoding:binary");
            $writer->writeToStdOut("php://output");
        } catch (Exception $e) {

        }

        exit();
    }

    // 权限验证
    private function getPrower(){
        $userinfo = session("uc_userinfo");

        // 权限验证
        $prower = [];
        if ($userinfo["department_top_id"] != DepartmentEnum::DEPARTMENT_SALE_CENTER) {
            $prower["order_review"] = true;
            $prower["apply_exam"] = true;
        }

        if ($userinfo["uid"] != RbacRoleEnum::ROLE_ZZKF_KF) {
            $prower["apply_export"] = true;
        }

        return $prower;
    }
}