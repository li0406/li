<?php

//会员报备审核

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\AdminuserLogicModel;
use Home\Model\Logic\SalesReportLogicModel;
use Home\Model\Logic\SalesReportPaymentLogicModel;

class ReportcheckController extends HomeBaseController
{
    protected $header;
    protected $sales_api;

    public function _initialize() {
        parent::_initialize();
        $token = session('uc_userinfo.sales_token');
        if (empty($token)) {
            $this->error('请重新登陆后再试! ');
        }
        $this->header = [
            'Content-type:application/json',
            'token:' . $token,
        ];
        $this->sales_api = C('SALES_API');
        $this->assign('check_report_url', $this->sales_api . '/v1/sale_report/set_status');
    }

    // 大报备审核列表
    public function index() {
        $input = I("get.");
        $url = '/v1/sale_report/list';
        $saleLogic = new SalesReportLogicModel($this->header);
        $info = $saleLogic->getSalesReport(1, $input, $url, $this->header);

        //销售列表
        $admin = new AdminuserLogicModel();
        $sell = $admin->getAdminUserByDepartment(17);

        //合作类型
        $cooperation_type = $saleLogic->getCooperationType();

        $this->assign('sell', $sell);//销售列表
        $this->assign('admin_user', 1);//审核列表操作
        $this->assign('cooperation_type', $cooperation_type);
        $this->assign('list', $info['list']);
        $this->assign('page', $info['page']);
        $this->display();
    }

    // 大报备客服审核列表
    public function service_check() {
        $url = '/v1/sale_report/list';
        $saleLogic = new SalesReportLogicModel($this->header);
        $info = $saleLogic->getSalesReport(2, I('get.'), $url, $this->header);
        //销售列表
        $admin = new AdminuserLogicModel();
        $sell = $admin->getAdminUserByDepartment(17);
        //合作类型
        $cooperation_type = $saleLogic->getCooperationType();
        $this->assign('sell', $sell);//销售列表
        $this->assign('admin_user', 2);//审核列表操作
        $this->assign('cooperation_type', $cooperation_type);
        $this->assign('list', $info['list']);
        $this->assign('page', $info['page']);
        $this->display();
    }

    // 大报备审核操作
    public function check_report() {
        $url = '/v1/sale_report/set_status';
        $saleLogic = new SalesReportLogicModel($this->header);
        $info = $saleLogic->checkReport(I('post.'), $url, $this->header);
        if (empty($info)) {
            $info = ["error_code" => -1, "error_msg" => "请求超时"];
        }
        $this->ajaxReturn($info);
    }

    // 大报备驳回
    public function recall_report() {
        $report_id = I("post.report_id");
        $cooperation_type = I("post.cooperation_type");
        $recall_remark = I("post.recall_remark");

        if (!empty($report_id) && !empty($cooperation_type)) {
            $url = "/v1/sale_report/recall";
            $saleLogic = new SalesReportLogicModel($this->header);
            $info = $saleLogic->recallReport($url, $report_id, $cooperation_type, $recall_remark);
        } else {
            $info = ["error_code" => 4000002, "error_msg" => "缺少参数拒绝请求"];
        }

        $this->ajaxReturn($info);
    }
    
    // 大报备详情
    public function show() {
        $url = '/v1/sale_report/show';
        $saleLogic = new SalesReportLogicModel($this->header);
        $ret = $saleLogic->reportDetail(I('get.'), $url, $this->header);

        if (!isset($ret["error_code"]) || $ret['error_code'] != 0) {
            $this->error($ret['error_msg']);
        }

        $admin_user = I('get.admin_user', 1);
        $back_url = $_SERVER["HTTP_REFERER"];
        if ($admin_user == 1) {
            $check_url1 = "/reportcheck/payment_check";
            $check_url2 = "/reportcheck/service_check";
            $check_url3 = "/reportcheck/show";
            $check_url = "/reportcheck";
            
            if (
                strpos($back_url, $check_url1) !== false || 
                strpos($back_url, $check_url2) !== false ||
                strpos($back_url, $check_url3) !== false ||
                strpos($back_url, $check_url) === false
            ) {
                $back_url = $check_url;
            }
        } else {
            $check_url = '/reportcheck/service_check';
            if (strpos($back_url, $check_url) === false) {
                $back_url = $check_url;
            }
        }

        $info = $ret['data']['info'];
        if (!empty($info)) {
            if (!empty($info["city_quote"])) {
                $info["city_quote"]["grade_a_price"] = $info["city_quote"]["grade_a_price"] ? : '';
                $info["city_quote"]["grade_b_price"] = $info["city_quote"]["grade_b_price"] ? : '';
                $info["city_quote"]["grade_c_price"] = $info["city_quote"]["grade_c_price"] ? : '';
                $info["city_quote"]["grade_d_price"] = $info["city_quote"]["grade_d_price"] ? : '';
                $info["city_quote"]["user_order_limit"] = $info["city_quote"]["user_order_limit"] ? : '';
                $info["city_quote"]["update_date"] = date("Y-m-d", $info["city_quote"]["update_at"]);
            }
        }

        $this->assign('index', 1);
        $this->assign('info', $info);
        $this->assign('back_url', $back_url);
        $this->assign('admin_user', $admin_user);
        switch (I('get.cooperation_type')) {
            case SalesReportLogicModel::REPORT_COOPERATION_ZX:
            case SalesReportLogicModel::REPORT_COOPERATION_DJ:
            case SalesReportLogicModel::REPORT_COOPERATION_LD:
            case SalesReportLogicModel::REPORT_COOPERATION_QWU:
                $this->assign('rname', '会员费');
                $this->display('zhuangxiu');
                break;
            case SalesReportLogicModel::REPORT_COOPERATION_SBACK:
                $this->assign('rname', '轮单费');
                $this->display('zhuangxiu');
                break;
            case SalesReportLogicModel::REPORT_COOPERATION_SWJ:
                $this->display('sanweijia');
                break;
            case SalesReportLogicModel::REPORT_COOPERATION_ERP:
                $this->display('erp');
                break;
            case SalesReportLogicModel::REPORT_COOPERATION_DELAY:
                $this->display('delay');
                break;
            case SalesReportLogicModel::REPORT_COOPERATION_REGULAR_MODEL:
                $this->assign('rname', '会员费');
                $this->display('zhuangxiu');
                break;
            case SalesReportLogicModel::REPORT_COOPERATION_SBACK_MODEL:
                $this->assign('rname', '轮单费');
                $this->assign("round_amount_bg", true);
                $this->display('zhuangxiu');
                break;
        }
    }

    // 大报备客服传凭
    public function save_voucher_img() {
        $input = I('post.');
        if (empty($input['id']) || empty($input['cooperation_type']) || empty($input['img'])) {
            $this->ajaxReturn(['error_code' => 400, 'error_msg' => '缺少参数!']);
        }
        $url = '/v1/sale_report/kf_voucher_upload';
        $saleLogic = new SalesReportLogicModel($this->header);
        $info = $saleLogic->saveVoucher($input, $url, $this->header);
        $this->ajaxReturn($info);
    }

    // 大报备城市报价记录
    public function report_city_quote_log(){
        $city_name = I('get.city_name');
        if(empty($city_name)){
            $this->ajaxReturn(['error_code' => 400, 'error_msg' => '缺少参数!']);
        }
        $url = '/v1/quote/history_city_quote';
        $saleLogic = new SalesReportLogicModel($this->header);
        $info = $saleLogic->getReportCityLogByCity($city_name, $url, $this->header);
        $this->ajaxReturn($info);
    }



    // 小报备审核列表页
    public function payment_check(){
        $limit = 20;
        $reportPaymentLogic = new SalesReportPaymentLogicModel($this->header);
        $data = $reportPaymentLogic->getReportPaymentExamList(I("get."), I("get.p"), $limit);

        if (!empty($data)) {
            import('Library.Org.Util.Page');
            $pageobj = new \Page($data["page"]["total_number"], $data["page"]["page_size"]);
            $data["pageshow"] = $pageobj->show();
        }

        // 获取所有城市
        $citylist = getUserCitys(false);

        // 合作类型
        $cooperation_type = $reportPaymentLogic->getCooperationType();
        $this->assign("cooperation_type", $cooperation_type);

        // 显示返点比例的类型
        $back_ratio_type = $reportPaymentLogic->getCooperationBackRatioType();
        $this->assign("back_ratio_type", $back_ratio_type);

        // 显示viptype的类型
        $viptype_type = $reportPaymentLogic->getCooperationViptypeType();
        $this->assign("viptype_type", $viptype_type);

		// 不显示关联大报备的类型
        $norelated_type = $reportPaymentLogic->getCooperationNoRelatedType();
        $this->assign("norelated_type", $norelated_type);

        // 会员退费类型
        $refund_type = SalesReportPaymentLogicModel::COOPERATION_TYPE_USER_REFUND;
        $this->assign('refund_type', $refund_type);

        //显示标杆会员(保产值)类型
        $bao_chan_zhi_type = $reportPaymentLogic->getCooperationBaoChanZhiType();
        $this->assign("bao_chan_zhi_type", $bao_chan_zhi_type);
        
        $this->assign("citylist", $citylist);
        $this->assign("data", $data);
        $this->display();
    }

    // 详情页
    public function payment_show(){
        $reportPaymentLogic = new SalesReportPaymentLogicModel($this->header);
        $data = $reportPaymentLogic->getReportPaymentDetail(I("get.id"));

        $back_url = $_SERVER["HTTP_REFERER"];
        $check_url = "/reportcheck/payment_check";
        $ret = strpos($back_url, $check_url);
        if ($ret == false) {
            $back_url = $check_url;
        }

        // 显示返点比例的类型
        $back_ratio_type = $reportPaymentLogic->getCooperationBackRatioType();
        $this->assign("back_ratio_type", $back_ratio_type);

        // 显示viptype的类型
        $viptype_type = $reportPaymentLogic->getCooperationViptypeType();
        $this->assign("viptype_type", $viptype_type);

		// 不显示关联大报备的类型
        $norelated_type = $reportPaymentLogic->getCooperationNoRelatedType();
        $this->assign("norelated_type", $norelated_type);

        // 显示会员ID的合作类型
        $comid_type = $reportPaymentLogic->getCooperationCompanyIdType();
        $this->assign("comid_type", $comid_type);

        // 不显示其他金额的合作类型
        $no_other_money_type = $reportPaymentLogic->getCooperationNoOtherMoneyType();
        $this->assign("no_other_money_type", $no_other_money_type);

        // 显示平台使用费的合作类型
        $platform_date_type = $reportPaymentLogic->getCooperationPlatformDateType();
        $this->assign("platform_date_type", $platform_date_type);

        // 签返会员类型
        $signback_type = SalesReportPaymentLogicModel::COOPERATION_TYPE_SBACK;
        $this->assign("signback_type", $signback_type);

        // 会员返点类型
        $uback_type = SalesReportPaymentLogicModel::COOPERATION_TYPE_UBACK;
        $this->assign("uback_type", $uback_type);

        // 签返会员（保证金转轮单费）
        $back_account = SalesReportPaymentLogicModel::COOPERATION_TYPE_SBACK_ACCOUNT;
        $this->assign("back_account", $back_account);

        // 平台使用费
        $plat_use = SalesReportPaymentLogicModel::COOPERATION_TYPE_PLATFORM_USE;
        $this->assign("plat_use", $plat_use);

        // 平台使用费（保证金转）
        $platform_turn_type = SalesReportPaymentLogicModel::COOPERATION_TYPE_PLATFORM_USE_TURN;
        $this->assign("platform_turn_type", $platform_turn_type);

        //标杆会员(保产值)类型
        $bao_chan_zhi_type = $reportPaymentLogic->getCooperationBaoChanZhiType();
        $this->assign("bao_chan_zhi_type", $bao_chan_zhi_type);
        if (in_array($data['detail']['cooperation_type'], $viptype_type) || $data['detail']['cooperation_type'] == SalesReportPaymentLogicModel::COOPERATION_TYPE_REGULAR_MODEL) {
            $this->assign('rname', '会员费');
        } else {
            $this->assign('rname', '轮单费');
        }

        $refund_type = SalesReportPaymentLogicModel::COOPERATION_TYPE_USER_REFUND;
        $this->assign('refund_type', $refund_type);

        $this->assign("info", $data["detail"]);
        $this->assign("company_account", $data["company_account"]);
        $this->assign("back_url", $back_url);
        $this->assign("index", 1);
        $this->display();
    }

    // 小报备客服列表页
    public function payment_service_check(){
        $limit = 20;
        $reportPaymentLogic = new SalesReportPaymentLogicModel($this->header);
        $data = $reportPaymentLogic->getReportPaymentKfExamList(I("get."), I("get.p"), $limit);

        if (!empty($data)) {
            import('Library.Org.Util.Page');
            $pageobj = new \Page($data["page"]["total_number"], $data["page"]["page_size"]);
            $data["pageshow"] = $pageobj->show();
        }

        // 获取所有城市
        $citylist = getUserCitys(false);

        // 审核筛选合作类型
        $cooperation_type_list = $reportPaymentLogic->getCooperationKfExamType();

        // 平台使用费（保证金转）
        $platform_turn_type = SalesReportPaymentLogicModel::COOPERATION_TYPE_PLATFORM_USE_TURN;
        $this->assign("platform_turn_type", $platform_turn_type);

        $this->assign("citylist", $citylist);
        $this->assign("cooperation_type_list", $cooperation_type_list);
        $this->assign("data", $data);
        $this->display();
    }

    // 小报备客服详情页
    public function payment_service_show(){
        $reportPaymentLogic = new SalesReportPaymentLogicModel($this->header);
        $data = $reportPaymentLogic->getReportPaymentDetail(I("get.id"));

        $back_url = $_SERVER["HTTP_REFERER"];
        $check_url = "/reportcheck/payment_service_check";
        $ret = strpos($back_url, $check_url);
        if ($ret == false) {
            $back_url = $check_url;
        }

        // 平台使用费（保证金转）
        $platform_turn_type = SalesReportPaymentLogicModel::COOPERATION_TYPE_PLATFORM_USE_TURN;
        $this->assign("platform_turn_type", $platform_turn_type);

        // 会员返点
        $uback_type = SalesReportPaymentLogicModel::COOPERATION_TYPE_UBACK;
        $this->assign("uback_type", $uback_type);

        $this->assign("info", $data["detail"]);
        $this->assign("company_account", $data["company_account"]);
        $this->assign("back_url", $back_url);
        $this->assign("index", 1);
        $this->display();
    }


    // 小报备审核
    public function check_payment_report(){
        if (IS_AJAX) {
            $id = I("post.id");
            $remark = I("post.remark");
            $actfrom = I("post.actfrom", 1);
            $exam_status = I("post.exam_status");
            $isconfirm = I("post.isconfirm", 1);
            $reportPaymentLogic = new SalesReportPaymentLogicModel($this->header);
            $response = $reportPaymentLogic->checkReportPayment($id, $exam_status, $remark, $actfrom, $isconfirm);

            $this->ajaxReturn($response);
        }
    }

    // 小报备驳回
    public function rollback_payment_report(){
        if (IS_AJAX) {
            $id = I("post.id");
            $reportPaymentLogic = new SalesReportPaymentLogicModel($this->header);
            $response = $reportPaymentLogic->rollbackReportPayment($id);

            $this->ajaxReturn($response);
        }
    }

    // 大报备关联小报备列表
    public function report_related_payment_list(){
        if (IS_AJAX) {
            $report_id = I("get.report_id");
            $report_cooperation_type = I("get.report_cooperation_type");
            $reportPaymentLogic = new SalesReportPaymentLogicModel($this->header);
            $data = $reportPaymentLogic->getReportRelatedPaymentList($report_id, $report_cooperation_type);

            switch (I('get.report_cooperation_type')) {
                case SalesReportLogicModel::REPORT_COOPERATION_ZX:
                case SalesReportLogicModel::REPORT_COOPERATION_DJ:
                case SalesReportLogicModel::REPORT_COOPERATION_LD:
                case SalesReportLogicModel::REPORT_COOPERATION_QWU:
                    $this->assign('tempMark', 1);
                    break;
                case SalesReportLogicModel::REPORT_COOPERATION_SBACK:
                    $this->assign('tempMark', 2);
                    break;
                case SalesReportLogicModel::REPORT_COOPERATION_SWJ:
                    $this->assign('tempMark', 3);
                    break;
                case SalesReportLogicModel::REPORT_COOPERATION_ERP:
                    $this->assign('tempMark', 4);
                    break;
                case SalesReportLogicModel::REPORT_COOPERATION_DELAY:
                    $this->assign('tempMark', 5);
                    break;
            }

            $this->assign("data", $data);
            $template = $this->fetch("report_related_payment_list");

            $this->ajaxReturn([
                "error_code" => 0,
                "error_msg" => "",
                "data" => [
                    "template" => $template
                ]
            ]);
        }
    }

    // 装修公司大报备列表
    public function company_report_list(){
        if (IS_AJAX) {
            $admin_user = I("get.admin_user");
            $company_id = I("get.company_id");
            $salesReportLogic = new SalesReportLogicModel($this->header);
            $data = $salesReportLogic->getCompanyReportList($company_id);

            $this->assign("data", $data);
            $this->assign("admin_user", $admin_user);
            $template = $this->fetch("company_report_list");

            $this->ajaxReturn([
                "error_code" => 0,
                "error_msg" => "",
                "data" => [
                    "template" => $template
                ]
            ]);
        }
    }

	public function view_modify_history()
    {
        $report_id = I("get.report_id");
        $salesReportLogic = new SalesReportLogicModel($this->header);
        $data = $salesReportLogic->getReportModifyHistoryList($report_id);

        $this->assign("data", $data);
        $template = $this->fetch("view_modify_history");

        $this->ajaxReturn([
            "error_code" => 0,
            "error_msg" => "",
            "data" => [
                "template" => $template
            ]
        ]);
    }


    // 装修公司历史会员时间列表
    public function company_vip_history_list(){
        if (IS_AJAX) {
            $admin_user = I("get.admin_user");
            $company_id = I("get.company_id");
            $salesReportLogic = new SalesReportLogicModel($this->header);
            $data = $salesReportLogic->getCompanyVipHistoryList($company_id);

            $this->assign("data", $data);
            $this->assign("admin_user", $admin_user);
            $template = $this->fetch("company_vip_history_list");

            $this->ajaxReturn([
                "error_code" => 0,
                "error_msg" => "",
                "data" => [
                    "template" => $template
                ]
            ]);
        }
    }
}