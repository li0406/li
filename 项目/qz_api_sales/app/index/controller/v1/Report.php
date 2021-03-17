<?php
// +----------------------------------------------------------------------
// | Report 销售报备新流程
// +----------------------------------------------------------------------
namespace app\index\controller\v1;

use think\Request;
use think\Controller;
use app\common\model\logic\ReportLogic;
use app\common\model\logic\ReportDelayLogic;
use app\common\model\logic\CompanyLogic;
use app\common\model\logic\AdminuserLogic;
use app\common\model\logic\ReportPaymentLogic;
use app\common\model\logic\ReportUnpassLogLogic;
use app\common\enum\BaseStatusCodeEnum;
use app\index\enum\SalesReportCodeEnum;

class Report extends Controller
{
    /**
     * 获取：销售报备列表
     *
     * @param ReportLogic $reportLogic
     * @return array
     */
    public function index(ReportLogic $reportLogic)
    {
        $p = $this->request->get('p', 1, 'intval');
        $psize = $this->request->get('psize', 20, 'intval');
        $get = $this->request->get();
        return $reportLogic->getSaleReportList($get, $p, $psize);
    }

    public function save(ReportLogic $reportLogic)
    {
        $cooperationZxAll = SalesReportCodeEnum::getCooperationZx(true);

        $post = $this->request->post();
        if (empty($post['cooperation_type'])) {
            return sys_response(4000800, '请选择合作类型', []);
        }

        if (in_array($post['cooperation_type'], $cooperationZxAll)) {
            $sence = 'app\index\validate\SaleReport.Addzx';
        } elseif ($post['cooperation_type'] == SalesReportCodeEnum::REPORT_COOPERATION_SWJ) {
            $sence = 'app\index\validate\SaleSwjReport.Addswj';
        } elseif ($post['cooperation_type'] == SalesReportCodeEnum::REPORT_COOPERATION_ERP) {
            $sence = 'app\index\validate\SaleReport.Adderp';
        } elseif ($post['cooperation_type'] == SalesReportCodeEnum::REPORT_COOPERATION_DELAY) {
            $sence = 'app\index\validate\SaleDelayReport.AddDelay';
        } else {
            return sys_response(4000800, '', []);
        }

        if (($error_msg = $this->validate($post, $sence)) !== true) {
            return sys_response(4000800, $error_msg, []);
        }

        if (in_array($post['cooperation_type'], $cooperationZxAll)) {
            // 验证小报备是否已经关联大报备
            if (empty($post["id"]) && !empty($post["report_payment_id"])) {
                $reportPaymentLogic = new ReportPaymentLogic();
                $paymentInfo = $reportPaymentLogic->getPaymentDetail($post["report_payment_id"], false);
                if (empty($paymentInfo) || $paymentInfo["is_delete"] == 2) {
                    return sys_response(4000001, '小报备不存在');
                } else if ($paymentInfo->is_related == 2) {
                    return sys_response(4000001, '小报备已经关联大报备');
                }
            }

            // 如果是编辑并且提交审核则需要验证小报备总金额和本次到账金额
            // if (!empty($post["id"]) && $this->request->post("status") == 2) {
            //     $reportPaymentLogic = new ReportPaymentLogic();
            //     $statistic = $reportPaymentLogic->getPaymentRelatedReport($post["id"], $post['cooperation_type']);
            //     if ($statistic["report_payment_number"] > 0) {
            //         if ($statistic["report_payment_money"] != $this->request->post("current_contract_amount")) {
            //             return sys_response(1000001, '小报备总金额与本次到账金额不一致');
            //         }
            //     }
            // }
        }

        // 会员延期数据补充
        if ($post['cooperation_type'] == SalesReportCodeEnum::REPORT_COOPERATION_DELAY) {
            $reportDelayLogic = new ReportDelayLogic();
            $post = $reportDelayLogic->reportDelayPostData($post);
            if (empty($post)) {
                return sys_response(4000001, '会员公司不存在');
            }
        }

        return $reportLogic->saveSaleReport($post,$this->request->param('user'));
    }

    public function reportList(Request $request)
    {
        $report = new ReportLogic();
        $data = $report->getCheckReportList($request->param());
        return $data;
    }
    
    public function del(ReportLogic $reportLogic)
    {
        $post = $this->request->post();
        $cooperationAll = SalesReportCodeEnum::getCooperationAll();
        if (empty($post['id']) || empty($post['cooperation_type']) || !in_array($post['cooperation_type'], $cooperationAll)) {
            return sys_response(4000800, '', []);
        }
        return $reportLogic->delSaleReport($post);
    }

    public function set_status(ReportLogic $reportLogic)
    {
        $post = $this->request->post();
        if (empty($post['status']) || empty($post['id']) || empty($post['cooperation_type'])) {
            return sys_response(4000800, '', []);
        }

        $cooperationAll = SalesReportCodeEnum::getCooperationAll();
        if (!in_array($post['cooperation_type'], $cooperationAll) || !in_array($post['status'], SalesReportCodeEnum::getAllStatus())) {
            return sys_response(4000800, '', []);
        }
        return $reportLogic->changeSaleReportStatus($post);
    }

    /**
     * 报备详情页
     */
    public function show_report(ReportLogic $reportLogic)
    {
        return $reportLogic->getSalesReportDetail($this->request->get());
    }

    public function show_log(ReportLogic $reportLogic)
    {
        $get = $this->request->only(['report_id','cooperation_type']);
        $cooperationAll = SalesReportCodeEnum::getCooperationAll();
        if (empty($get['report_id']) || empty($get['cooperation_type']) || !in_array($get['cooperation_type'], $cooperationAll)) {
            return sys_response(4000002, '', []);
        }
        $p = $this->request->get('p', null, 'intval');
        $psize = $this->request->get('psize', null, 'intval');
        return $reportLogic->checkLog($get, $p, $psize);
    }

    /**
     * @param CompanyLogic $userLogic
     * @return array
     */
    public function test_company(CompanyLogic $userLogic)
    {
        $id = $this->request->get('uid', '');
        if (empty($id) || !ctype_digit((string)$id)) {
            return sys_response(4000002, '抱歉未匹配到公司名称', []);
        }
        $pageSize = $this->request->get('psize', 10, 'intval');
        $lists = $userLogic->getListUser($id, $pageSize);
        if (empty($lists)) {
            return sys_response(4000002, '抱歉未匹配到公司名称', []);
        } else {
            return sys_response(0, '', [$lists]);
        }
    }

    /**
     * 需要客服上传凭证
     * @param ReportLogic $reportLogic
     * @return array
     */
    public function kf_voucher(ReportLogic $reportLogic)
    {
        $input = $this->request->post();
        if (empty($input['id']) || empty($input['cooperation_type'])) {
            return sys_response(4000002);
        }
        return $reportLogic->setKfVoucher($input);
    }

    /**
     * 客服上传凭证
     * @param ReportLogic $reportLogic
     * @return array
     */
    public function kf_voucher_upload(ReportLogic $reportLogic)
    {
        $input = $this->request->post();
        if (empty($input['id']) || empty($input['cooperation_type']) || empty($input['img'])) {
            return sys_response(4000002);
        }
        return $reportLogic->uploadKfVoucher($input);
    }

    public function checkContract(ReportLogic $reportLogic)
    {
        $input = $this->request->post();
        if (empty($input['cooperation_type']) || empty($input['city_name']) || empty($input['current_contract_amount']) || empty($input['current_contract_end']) || empty($input['current_contract_start']) || empty($input['viptype'])) {
            return sys_response(4000002);
        }
        $status = $reportLogic->getContractStatus($input);
        return sys_response(0, '', ['info' => ['is_abnormal' => $status]]);
    }

    /**
     * 小报备关联选择大报备列表
     * @param  Request     $request     [description]
     * @param  ReportLogic $reportLogic [description]
     * @return [type]                   [description]
     */
    public function getSelectList(Request $request, ReportLogic $reportLogic){
        $input = $request->get();
        $page = sys_page_format($request->page, 1);
        $limit = sys_page_format($request->limit, 20);

        if ($request->cooperation_type == SalesReportCodeEnum::REPORT_COOPERATION_SWJ) {
            $data = $reportLogic->getSelectSwjPageList($input, $page, $limit);
        } else {
            $data = $reportLogic->getSelectPageList($input, $page, $limit);
        }

        $response = sys_response(0, "", [
            "list" => $data["list"],
            "page" => sys_paginate($data["count"], $limit, $page)
        ]);

        return json($response);
    }

    /**
     * 会员公司大报备列表
     * @param  Request     $request     [description]
     * @param  ReportLogic $reportLogic [description]
     * @return [type]                   [description]
     */
    public function getCompanyReportList(Request $request, ReportLogic $reportLogic){
        $company_id = $request->get("company_id");
        if (empty($company_id)) {
            return json(sys_response(4000002));
        }

        $list = $reportLogic->getCompanyReportList($company_id);
        
        $response = sys_response(0, "", [
            "list" => $list
        ]);

        return json($response);
    }

    /**
     * 获取编辑筛选项
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function getEditOptions(Request $request){
        $back_ratio_list = SalesReportCodeEnum::getBackRatioList();
        $cooperation_type_list = SalesReportCodeEnum::getCooperationTypeList();
        $year_month_list = SalesReportCodeEnum::getYearMonthList();

        $response = sys_response(0, "", [
            "back_ratio_list" => $back_ratio_list,
            "cooperation_type_list" => $cooperation_type_list,
            "year_month_list" => $year_month_list,
        ]);

        return json($response);
    }


    // 报备不通过记录列表
    public function getUnpassLogList(Request $request){
        $input = $request->get();
        $page = $request->get("page", 1);
        $limit = $request->get("limit", 20);

        $reportUnpassLogLogic = new ReportUnpassLogLogic();
        $data = $reportUnpassLogLogic->getUnpassLogPageList($input, $page, $limit);

        $response = sys_response(0, "", $data);
        return json($response);
    }

    // 查找报备装修公司信息
    public function findDelayCompanyInfo(Request $request, ReportDelayLogic $reportDelayLogic){
        $company_id = $request->get("company_id");
        if (empty($company_id)) {
            return json(sys_response(4000002));
        }

        $data = $reportDelayLogic->findDelayCompanyInfo($company_id);
        if (empty($data["company_info"])) {
            return json(sys_response(4000001, "会员公司不存在"));
        }

        $response = sys_response(0, "", $data);
        return json($response);
    }

    // 大报备驳回
    public function setRecall(Request $request, ReportLogic $reportLogic){
        $id = $request->post("id");
        $cooperation_type = $request->post("cooperation_type");
        $remark = $request->post("remark", "");

        $cooperationAll = SalesReportCodeEnum::getCooperationAll();
        if (empty($id) || empty($cooperation_type) || !in_array($cooperation_type, $cooperationAll)) {
            return sys_response(4000002, BaseStatusCodeEnum::CODE_4000002);
        }

        $response = $reportLogic->setReportRecall($id, $cooperation_type, $remark);
        return json($response);
    }

	
    // public function modifyHistory(Request $request)
    // {
    //     $reportId = $request->get('report_id');

    //     if (empty($reportId)) {
    //         return json(sys_response(4000002));
    //     }

    //     $reportLogic = new ReportLogic();
    //     $result = $reportLogic->getHistroyModifyList($reportId);
        
    //     $response = sys_response(0, "", array_merge([
    //         "count" => count($result['list'])
    //     ], $result));

    //     return json($response);
    // }
}