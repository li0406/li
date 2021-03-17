<?php
namespace app\common\model\logic;

use app\common\model\db\Invoice;

use app\common\model\db\InvoiceImg;
use app\common\model\db\SaleReportInvoiceWithPayment;
use app\common\model\logic\ReportPaymentLogic;
use think\facade\Request;
use Util\Page;
use app\index\enum\InvoiceEnum;
use app\index\enum\ReportPaymentCodeEnum;
use think\Exception;
use think\Db;

class InvoiceLogic 
{
    /*
    * 发票报备保存/提交/编辑
    */
    public function saveInvoiceInfo($input){
        try {
            Db::startTrans();
            $invoiceModel = new Invoice();

            // 验证小报备
            if($input['report_payment_ids'] ?? ''){
                $input = $this->checkReportPaymentStatus($invoiceModel, $input);
            }

            $time = $input['nowtime'] = time();
            $input['updated_at'] = $time;

            if($input['is_submit'] == 2){
                $input['status'] = InvoiceEnum::INVOICE_STATUS_SUBMIT;
                $input['submit_time'] = $time;
            }else{
                $input['status'] = InvoiceEnum::INVOICE_STATUS_PRE;
            }
            
            if(!empty($input['id'])){
                $info = $invoiceModel->getInfo($input['id']);
                $lock = [
                    InvoiceEnum::INVOICE_STATUS_SUBMIT,
                    InvoiceEnum::INVOICE_STATUS_ONGOING,
                    InvoiceEnum::INVOICE_STATUS_NORMAL
                ];
                if(empty($info) || in_array($info['status'], $lock) || $info['is_delete'] == InvoiceEnum::INVOICE_STATUS_DELETE){
                    throw new Exception('无法提交或保存', 5000002);   
                }

                $data = $input;
                unset($data['id']);
                $res = $invoiceModel->updateInfo($input['id'], $data);
                $res && $res = $input['id'];
            }else{
                $input['created_at'] = $time;
                $res = $invoiceModel->addInfo($input);
                $res && $input['id'] = $res;
            }

            // 处理发票报备相关图片
            $insertImgs = $this->setInvoiceImgs($invoiceModel, $input);

            // 处理发票与小报备关联
            $linkReportPayment = $this->setLinkReportPayment($invoiceModel, $input);

            if(!$res || !$insertImgs || !$linkReportPayment){
                Db::rollback();
                throw new Exception('操作失败', 5000002);
            }

            // 记录操作日志
            $this->recordOperateLog($invoiceModel, $input, $input['status']);

            Db::commit();
            return [0, '', $res];
        } catch (Exception $e) {
            Db::rollback();
            $code = $e->getCode() ?: 5000002;
            return [$code, $e->getMessage(), null];
        }
    }

    /*
    * 校验小报备
    */
    private function checkReportPaymentStatus($invoiceModel, $input){
        $input['rp_id_arr'] = [];
        if(!is_array($input['report_payment_ids'])){
            $input['rp_id_arr'] = explode(',', $input['report_payment_ids']);    
        }else{
            $input['rp_id_arr'] = $input['report_payment_ids'];
        }
        
        foreach ($input['rp_id_arr'] as $v) {
            if(!preg_match("/^[1-9][0-9]*$/" , $v)){
                throw new Exception('小报备ID不合法', 4000019);
            }
        }

        $existUnpass = $this->isExistUnpasReportPayment($invoiceModel, $input['rp_id_arr']);
        if($existUnpass){
            throw new Exception('小报备已驳回，请核实后重新提交', 4000019);
        }

        return $input;
    }

    private function isExistUnpasReportPayment($invoiceModel, $report_payment_ids){
        $res = false;

        $reportPayment = $invoiceModel->getReportPaymentInfo($report_payment_ids);
        foreach ($reportPayment as $k => $v) {
            $pass_status =  [
                ReportPaymentLogic::EXAM_STATUS_PASS,
                ReportPaymentLogic::EXAM_STATUS_PASS_TO_KF
            ];
            if(!in_array($v['exam_status'], $pass_status)){
                $res = true;
                break;
            }
        }

        return $res;
    }

    /*
    * 处理发票图片
    */
    private function setInvoiceImgs($invoiceModel, $input){
        $res = true;
        $imgs = [];

        //删除原有发票报备图片
        $invoiceModel->deleteImgs($input['id']);

        if($input['license_imgs'] ?? ''){
            if(!is_array($input['license_imgs'])){
                $input['license_imgs'] = explode(',', $input['license_imgs']);
            }

            foreach ($input['license_imgs'] as $k => $v) {
                $imgs[] = [
                    'invoice_id' => $input['id'],
                    'img_path' => $v,
                    'img_type' => 1,
                    'created_at' => $input['nowtime']
                ];
            }
        }

        if($input['other_imgs'] ?? ''){
            if(!is_array($input['other_imgs'])){
                $input['other_imgs'] = explode(',', $input['other_imgs']);
            }

            foreach ($input['other_imgs'] as $k => $v) {
                $imgs[] = [
                    'invoice_id' => $input['id'],
                    'img_path' => $v,
                    'img_type' => 2,
                    'created_at' => $input['nowtime']
                ];
            }
        }

        if(!empty($imgs)){
            $res = $invoiceModel->addImgs($imgs);
        }

        return $res;
    }

    /*
    * 审核发票
    */
    public function verifyInvoice($input){
        try {
            $allow = InvoiceEnum::getCheckStatus();
            if(!in_array($input['status'], $allow)){
                throw new Exception('审核状态不合法', 5000002);
            }

            if($input['status'] == InvoiceEnum::INVOICE_STATUS_REJECT){
                // if(empty($input['remark'])) throw new Exception('', 4000002);
            }

            $input['nowtime'] = time();
            $data = [
                'status' => $input['status'],
                'updated_at' => $input['nowtime'],
                'check_reason' => $input['remark'] ?? ''
            ];

            $invoiceModel = new Invoice();
            $info = $invoiceModel->getInfo($input['id'])->toArray();
            if(empty($info) || $info['is_delete'] == InvoiceEnum::INVOICE_STATUS_DELETE){
                throw new Exception('该条发票信息不存在', 5000002);
            }

            // 验证权限
            $userIds = AdminuserLogic::getAuthUserids();
            // $userIds = TeamLogic::getAccessAuthUsersIDs();
            if (!empty($userIds) && !in_array($info['creator_id'], $userIds)) {
                throw new Exception('您没有权限进行此操作', 1000001);
            }

            if($info['status'] == $input['status']){
                throw new Exception('当前报备已是'.InvoiceEnum::$status[$input['status']].'状态', 5000002);
            }

            $reportPayment = $invoiceModel->getReportPaymentByInvoiceId($info['id']);
            if(!empty($reportPayment)){
                $report_payment_ids = array_column($reportPayment->toArray(), 'report_payment_id');
                if(!empty($report_payment_ids)){
                    $existUnpass = $this->isExistUnpasReportPayment($invoiceModel, $report_payment_ids);
                    if($existUnpass && $input['status'] != InvoiceEnum::INVOICE_STATUS_REJECT){
                        throw new Exception('该发票关联的小报备状态异常，当前只可驳回', 1000001);
                    }
                }    
            }
            
            $res = $invoiceModel->updateInfo($input['id'], $data);
            if(!$res){
                throw new Exception('操作失败', 5000002);
            }

            // 记录操作日志
            $this->recordOperateLog($invoiceModel, $input, $input['status']);
            if($input['status'] == InvoiceEnum::INVOICE_STATUS_REJECT){
                $this->recordCommonOperateLog(array_merge($info, $input));
            }

            return [0, '', $res];
        } catch (Exception $e) {
            $code = $e->getCode() ?: 5000002;
            return [$code, $e->getMessage(), null];
        }
    }

    /*
    * 发票提交
    */
    public function submitInvoiceInfo($input){
        try {
            $invoiceModel = new Invoice();

            $info = $invoiceModel->getInfo($input['id'])->toArray();
            if(empty($info) || $info['is_delete'] == InvoiceEnum::INVOICE_STATUS_DELETE){
                throw new Exception('发票报备信息不存在', 4000002);
            }

            // 验证权限
            $userIds = AdminuserLogic::getAuthUserids();
            // $userIds = TeamLogic::getAccessAuthUsersIDs();
            if (!empty($userIds) && !in_array($info['creator_id'], $userIds)) {
                throw new Exception("您没有权限进行此操作", 1000001);
            }

            $allow = [
                InvoiceEnum::INVOICE_STATUS_PRE,
                InvoiceEnum::INVOICE_STATUS_REJECT
            ];

            if(!in_array($info['status'], $allow)){
                throw new Exception('当前发票无法提交', 5000002);
            }

            // 验证小报备
            $reportPayment = $invoiceModel->getReportPaymentByInvoiceId($input['id']);
            if(!empty($reportPayment)){
                $input['report_payment_ids'] = array_column($reportPayment->toArray(), 'report_payment_id');
                if(!empty($input['report_payment_ids'])){
                    $input = $this->checkReportPaymentStatus($invoiceModel, $input);
                }    
            }

            $time = $input['nowtime'] = time();
            $input['status'] = InvoiceEnum::INVOICE_STATUS_SUBMIT;
            $input['submit_time'] = $time;
            $input['updated_at'] = $time;
            $input['check_reason'] = ''; //清空已审核的备注

            $data = $input;
            unset($data['id']);
            $res = $invoiceModel->updateInfo($input['id'], $data);

            if(!$res){
                throw new Exception('操作失败', 5000002);
            }

            // 记录操作日志
            $this->recordOperateLog($invoiceModel, $input, $input['status']);

            return [0, '', $res];
        } catch (Exception $e) {
            $code = $e->getCode() ?: 5000002;
            return [$code, $e->getMessage(), null];
        }
    }

    /*
    * 发票删除
    */
    public function deleteInvoiceInfo($input){
        try {
            $invoiceModel = new Invoice();

            $info = $invoiceModel->getInfo($input['id'])->toArray();
            if(empty($info) || $info['is_delete'] == InvoiceEnum::INVOICE_STATUS_DELETE){
                throw new Exception('发票报备信息不存在', 4000002);
            }

            // 验证权限
            $userIds = AdminuserLogic::getAuthUserids();
            // $userIds = TeamLogic::getAccessAuthUsersIDs();
            if (!empty($userIds) && !in_array($info['creator_id'], $userIds)) {
                throw new Exception("您没有权限进行此操作", 1000001);
            }

            $allow = [
                InvoiceEnum::INVOICE_STATUS_PRE,
                InvoiceEnum::INVOICE_STATUS_REJECT
            ];

            if(!in_array($info['status'], $allow)){
                throw new Exception('该条发票报备不可删除', 5000002);
            }

            $time = $input['nowtime'] = time();
            $input['is_delete'] = InvoiceEnum::INVOICE_STATUS_DELETE;
            $input['updated_at'] = $time;

            $data = $input;
            unset($data['id']);
            $res = $invoiceModel->updateInfo($input['id'], $data);

            if(!$res){
                throw new Exception('操作失败', 5000002);
            }

            // 记录操作日志
            $this->recordOperateLog($invoiceModel, $input, InvoiceEnum::INVOICE_STATUS_DELETE);
            
            return [0, '', $res];
        } catch (Exception $e) {
            $code = $e->getCode() ?: 5000002;
            return [$code, $e->getMessage(), null];
        }
    }

    /*
    * 发票撤回
    */
    public function recallInvoiceInfo($input){
        try {
            $invoiceModel = new Invoice();

            $info = $invoiceModel->getInfo($input['id'])->toArray();
            if(empty($info) || $info['is_delete'] == InvoiceEnum::INVOICE_STATUS_DELETE){
                throw new Exception('发票报备信息不存在', 4000002);
            }

            // 验证权限
            $userIds = AdminuserLogic::getAuthUserids();
            // $userIds = TeamLogic::getAccessAuthUsersIDs();
            if (!empty($userIds) && !in_array($info['creator_id'], $userIds)) {
                throw new Exception("您没有权限进行此操作", 1000001);
            }

            if($info['status'] != InvoiceEnum::INVOICE_STATUS_SUBMIT){
                throw new Exception('该条发票不可撤回', 5000002);
            }

            $time = $input['nowtime'] = time();
            $input['status'] = InvoiceEnum::INVOICE_STATUS_PRE;
            $input['updated_at'] = $time;
            $input['submit_time'] = 0;

            $data = $input;
            unset($data['id']);
            $res = $invoiceModel->updateInfo($input['id'], $data);

            if(!$res){
                throw new Exception('操作失败', 5000002);
            }

            // 记录操作日志
            $this->recordOperateLog($invoiceModel, $input, InvoiceEnum::INVOICE_STATUS_RECALL);
            
            return [0, '', $res];
        } catch (Exception $e) {
            $code = $e->getCode() ?: 5000002;
            return [$code, $e->getMessage(), null];
        }
    }

    /**
    * 记录发票报备记录
    */
    private function recordOperateLog($invoiceModel, $input, $action=1){
        $user = request()->user;
        $data = [
            'invoice_id' => $input['id'],
            'operator_id' => $user['user_id'],
            'operator_name' => $user['user_name'],
            'action_type' => $action,
            'paramter' => json_encode($input, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
            'remark' => $input['remark'] ?? '',
            'created_at' => $input['nowtime']
        ];
        return $invoiceModel->addLog($data);
    }

    /**
    * 记录发票报备审核不通过记录
    */
    public function recordCommonOperateLog($input){
        $reportUnpassLogModel =  new \app\common\model\db\SaleReportUnpassLog();
        $log = [
            'report_type' => \app\index\enum\ReportUnpassCodeEnum::REPORT_TYPE_INVOICE,
            'report_id' => $input['id'],
            'cooperation_type' => 0,
            'city_id' => 0,
            'city_name' => '',
            'company_id' => '',
            'company_name' => $input['invoice_title'],
            'report_time' => $input['nowtime'],
            'report_saler' => $input['creator_id'],
            'exam_operator' => $input['user_id'],
            'exam_remark' => $input['remark'] ?? '',
            'exam_status' => $input['status'],
            'exam_time' => $input['nowtime'],
            'created_at' => $input['nowtime']
        ];

        return $reportUnpassLogModel->addLog($log);
    }

    /*
    * 处理发票报备和小报备关联
    */
    private function setLinkReportPayment($invoiceModel, $input){
        $res = true;

        //删除已有关联
        $input['id'] && $invoiceModel->deletelinkReportPayment((int)$input['id']);

        if(!($input['rp_id_arr'] ?? '')){
            return $res;
        }
        
        $data = [];

        foreach ($input['rp_id_arr'] as $k => $v) {
            $data[] = [
                'invoice_id' => $input['id'],
                'report_payment_id' => $v,
                'created_at' => $input['nowtime']
            ];
        }

        if(!empty($data)){
            $res = $invoiceModel->linkReportPayment($data);
        }

        return $res;
    }


    /**
     * 发票报备列表
     * @param array $input 用户搜索条件
     * @param int $page 页码
     * @param int $limit 每页个数
     */
    public function getInvoicePageList($input, $page = 1, $limit = 10)
    {
        // 获取总数据个数以及分页数据
        $invoiceModel = new Invoice();

        // 权限-可以查看的id
        $userIds = AdminuserLogic::getAuthUserids();
        // $userIds = TeamLogic::getAccessAuthUsersIDs();

        $teamLogic = new TeamLogic();
        if (!empty($input['top_team_id'])) {
            $top_user_list = $teamLogic->getTeamUserList($input['top_team_id'], 1);
            $input['user_ids'] = array_column($top_user_list, 'current_id');
            $input['user_ids'] = $input['user_ids'] ? : [-1];
        }
        
        // count及分页
        $count = $invoiceModel->getInvoicePageCount($input, $userIds);
        $pageobj = new Page($page, $limit, $count);
        $pageshow = $pageobj->show();
        if ($count > 0) {
            $list = $invoiceModel->getInvoicePageList($input, $pageobj->firstrow, $pageobj->pageSize, $userIds);

            // 查询小报备实际到账金额
            $invoiceIds = array_column($list, 'id');
            $saleReportInvoiceWithPayment = new SaleReportInvoiceWithPayment();
            $accountMoney = $saleReportInvoiceWithPayment->getAccountMoney($invoiceIds);

            $allow = [
                InvoiceEnum::INVOICE_STATUS_ONGOING,
                InvoiceEnum::INVOICE_STATUS_NORMAL,
                InvoiceEnum::INVOICE_STATUS_REJECT
            ];

            // 返回数据处理
            if (count($list) > 0) {
                foreach ($list as $key => &$value) {
                    // 查询小报备状态
                    $paymentInfo = $saleReportInvoiceWithPayment->getPaymentInfo($value['id']);
                    $exam_status = array_column($paymentInfo, 'exam_status');
                    if (empty($exam_status)) {
                        array_push($exam_status, 0);
                    }

                    $value['created_date'] = date('Y-m-d H:i', $value['created_at']);
                    $value['submit_date'] = date('Y-m-d H:i', $value['submit_time']);
                    $value['type_name'] = InvoiceEnum::getItem("type", $value['type']);
                    $value['status_name'] = InvoiceEnum::getItem("status", $value['status']);
                    $value['is_in_account_name'] = InvoiceEnum::getItem("is_in_account", $value['is_in_account']);
                    $value['billing_company_name'] = InvoiceEnum::getItem("billing_company", $value['billing_company']);
                    $value["account_money"] = intval($value["payment_total_money"]); // 实际到账金额
                    $value["invoice_price"] = intval($value["invoice_price"]); // 开票金额取整
                    if (in_array(ReportPaymentLogic::EXAM_STATUS_FAIL, $exam_status)  || in_array(ReportPaymentLogic::EXAM_STATUS_FAIL_KF, $exam_status)) {
                        $value['warning_info'] = '发票中相关小报备已被驳回，请核实后重新提交';
                    }

                    $value['check_time'] = ''; //审核时间
                    if(in_array($value['status'], $allow)){
                        $value['check_time'] = date('Y-m-d H:i', $value['updated_at']);
                    }
                }
            }
        }

        return [
            "list" => $list ?? [],
            "page" => $pageshow
        ];
    }

    /**
     * 查看发票报备详情
     * @param int $id 发票报备列表id
     */
    public function getInvoiceDetails($id)
    {
        // 发票报备主数据
        $invoiceModel = new Invoice();
        // 权限-获取可查看的id
        $userIds = AdminuserLogic::getAuthUserids();
        // $userIds = TeamLogic::getAccessAuthUsersIDs();
        $invoiceDetails = $invoiceModel->getInvoiceDetails($id, $userIds);

        // 处理返回数据
        if (count($invoiceDetails) > 0) {
            $invoiceDetails['is_in_account_name'] = InvoiceEnum::getItem("is_in_account", $invoiceDetails['is_in_account']);
            $invoiceDetails['billing_company_name'] = InvoiceEnum::getItem("billing_company", $invoiceDetails['billing_company']);
            $invoiceDetails['status_name'] = InvoiceEnum::getItem("status", $invoiceDetails['status']);
            $invoiceDetails['type_name'] = InvoiceEnum::getItem("type", $invoiceDetails['type']);
            $invoiceDetails['invoice_price'] = intval($invoiceDetails['invoice_price']);
        }

        // 报备人所属顶级团队
        $teamLogic = new TeamLogic();
        $topTeamList = $teamLogic->getUserTopTeamId([$invoiceDetails['creator_id']]);

        if (array_key_exists($invoiceDetails['creator_id'], $topTeamList)) {
            $topTeam = $topTeamList[$invoiceDetails['creator_id']];
            $invoiceDetails["top_team_id"] = $topTeam["team_id"];
            $invoiceDetails["top_team_name"] = $topTeam["team_name"];
        } else {
            $invoiceDetails["top_team_id"] = "";
            $invoiceDetails["top_team_name"] = "";
        }

        return $invoiceDetails;
    }

    /**
     * 查看小报备详情
     * @param int $id 发票报备列表id
     */
    public function getPaymentDetails($id)
    {
        // 小报备数据
        $paymentModel = new SaleReportInvoiceWithPayment();
        $paymentDetails = $paymentModel->getPaymentDetails($id);

        // 查询小报备是否被多个发票关联
        $payment_ids = array_column($paymentDetails, 'id');
        $invoice_ids = $paymentModel->getPaymentInvoiceIds($payment_ids);

        // 返回数据处理
        if (count($paymentDetails) > 0) {
            foreach ($paymentDetails as $key => &$value) {
                $value['cooperation_type_name'] = ReportPaymentCodeEnum::getItem("cooperation_type", $value['cooperation_type']);
                $value['exam_status_name'] = ReportPaymentCodeEnum::getItem("exam_status", $value['exam_status']);
                $value['payment_date'] = date('Y-m-d', $value['payment_time']);
                $value['payment_total_money'] = intval($value['payment_total_money']);
                $value["invoice_id_num"] = $invoice_ids[$key]['invoice_id_num'] ?? 0; // 小报备关联发票数量
                // 小报备预警信息
                if ($value['exam_status'] != ReportPaymentLogic::EXAM_STATUS_PASS && $value['exam_status'] != ReportPaymentLogic::EXAM_STATUS_PASS_TO_KF) {
                    $value['warning_info'] = '发票中相关小报备已被驳回，请核实后重新提交';
                    $value['sort'] = 2;
                }else if ($value["invoice_id_num"] > 1) {
                    $value['warning_info'] = '该发票已有发票记录';
                    $value['sort'] = 1;
                } else {
                    $value['warning_info'] = '';
                    $value['sort'] = 0;
                }
            }
            // 小报备置顶操作
            $paymentDetails = multi_array_sort($paymentDetails, 'sort',SORT_DESC);
        }

        return $paymentDetails;
    }

    /**
     * 查看小报备图片
     * @param int $id 发票报备列表id
     */
    public function getPaymentPic($id)
    {
        // 小报备数据
        $paymentModel = new SaleReportInvoiceWithPayment();
        $paymentPics = $paymentModel->getPaymentPic($id);
        if (count($paymentPics) > 0) {
            foreach ($paymentPics as $key => &$value) {
                $value['img_full'] = changeImgUrl($value['img_src']);
            }
        }

        return $paymentPics;
    }

    /**
     * 查看发票报备图片
     * @param int $id 发票报备列表id
     * @param int $type 图片类型 1.证件图片 2.其他说明图片
     */
    public function getInvoicePic($id, $type)
    {
        // 小报备数据
        $invoiceImgModel = new InvoiceImg();
        $invoicePics = $invoiceImgModel->getInvoicePic($id, $type);
        if (count($invoicePics) > 0) {
            foreach ($invoicePics as $key => &$value) {
                $value['img_full'] = changeImgUrl($value['img_src']);
            }
        }

        return $invoicePics;
    }

    /**
     * 小报备多次开票关联发票列表
     * @param int $payment_id 小报备id
     */
    public function getPaymentInvoiceList($payment_id, $invoice_id)
    {
        $saleReportInvoiceWithPayment = new SaleReportInvoiceWithPayment();
        $datas = $saleReportInvoiceWithPayment->getPaymentInvoiceList($payment_id, $invoice_id);

        if (count($datas) > 0) {
            foreach ($datas as $key => &$value) {
                $value['is_in_account_name'] = InvoiceEnum::getItem("is_in_account", $value['is_in_account']);
                $value['billing_company_name'] = InvoiceEnum::getItem("billing_company", $value['billing_company']);
                $value['status_name'] = InvoiceEnum::getItem("status", $value['status']);
                if (empty($value['submit_time'])) {
                    $value['submit_date'] = '';
                } else {
                    $value['submit_date'] = date('Y-m-d H:i', $value['submit_time']);
                }
            }
        }

        return $datas;
    }

}