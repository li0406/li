<?php

namespace app\index\controller\v1;

use think\Request;
use app\index\enum\InvoiceEnum;
use app\common\enum\BaseStatusCodeEnum;
use app\index\validate\Invoice as InvoiceValidate;
use app\common\model\logic\InvoiceLogic;
use app\common\model\logic\InvoiceLogLogic;
/**
 * 发票报备
 */
class Invoice 
{
    // 获取列表查询选项
    public function getOptions(){
        $options = [
            "status" => InvoiceEnum::getList("status"),
            "type" => InvoiceEnum::getList("type"),
            "billing_company" => InvoiceEnum::getList("billing_company"),
            "is_in_account" => InvoiceEnum::getList('is_in_account'),
        ];

        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, $options);

        return json($response);
    }

    // 发票报备列表
    public function getList(Request $request)
    {
        // 搜索条件
        $input = $request->get();
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        // 查询数据
        $invoiceLogic = new InvoiceLogic();
        $data = $invoiceLogic->getInvoicePageList($input, $page, $limit);

        return sys_response(0, '数据查询成功', $data);
    }

    // 查看发票报备
    public function getInfo(Request $request)
    {
        $invoice_id = $request->get('id');
        if (empty($invoice_id)) {
            return json(sys_response(4000001));
        }

        // 查询发票报备数据
        $invoiceLogic = new InvoiceLogic();
        $invoiceDetails = $invoiceLogic->getInvoiceDetails($invoice_id);
        if (empty($invoiceDetails)) {
            return json(sys_response(4000001));
        }

        // 查询小报备数据
        $paymentDetails = $invoiceLogic->getPaymentDetails($invoice_id);
        // 查询小报备截图
        $paymentPics = $invoiceLogic->getPaymentPic($invoice_id);
        // 查询发票报备证件图片
        $invoicePics = $invoiceLogic->getInvoicePic($invoice_id, 1);
        // 查询发票报备其他图片
        $invoiceOtherPics = $invoiceLogic->getInvoicePic($invoice_id, 2);
        // 查询发票审核日志
        $invoiceLogs = (new InvoiceLogLogic())->getLogList($invoice_id);

        // 返回数据
        $response = sys_response(0, "", [
            "invoiceDetails" => $invoiceDetails,
            "paymentDetails" => $paymentDetails,
            "paymentPics" => $paymentPics,
            "invoicePics" => $invoicePics,
            "invoiceOtherPics" => $invoiceOtherPics,
            "invoiceLogs" => $invoiceLogs
        ]);

        return json($response);
    }

    // 小报备多次开票关联发票列表
    public function getPaymentInvoiceList(Request $request)
    {
        $payment_id = $request->get('id');
        $invoice_id = $request->get('invoice_id');

        // 查询数据
        $invoiceLogic = new InvoiceLogic();
        $list = $invoiceLogic->getPaymentInvoiceList($payment_id, $invoice_id);

        $response = sys_response(0, "", [
            "list" => $list
        ]);

        return json($response);
    }

    /*
    * 发票保存/编辑
    */
    public function saveInfo(Request $request, InvoiceValidate $validate)
    {
        $input = $request->post();
        $input['creator_id'] = $request->user["user_id"];

        if (!$validate->check($input)) {
            return json(sys_response(4000002, $validate->getError()));
        }
        
        $invoiceLogic = new InvoiceLogic();
        $res = $invoiceLogic->saveInvoiceInfo($input);
        
        list($code, $message, $data) = $res;
        return json(sys_response($code, $message, $data));
    }

    /*
    * 发票提交
    */
    public function submitInfo(Request $request){
        $input = $request->post();
        if(empty($input['id'])){
            return json(sys_response(4000002));
        }

        $invoiceLogic = new InvoiceLogic();
        $res = $invoiceLogic->submitInvoiceInfo($input);

        list($code, $message, $data) = $res;
        return json(sys_response($code, $message, $data));
    }

    /*
    * 发票删除
    */
    public function deleteInfo(Request $request){
        $input = $request->post();
        if(empty($input['id'])){
            return json(sys_response(4000002));
        }

        $invoiceLogic = new InvoiceLogic();
        $res = $invoiceLogic->deleteInvoiceInfo($input);

        list($code, $message, $data) = $res;
        return json(sys_response($code, $message, $data));
    }

    /*
    * 发票撤回
    */
    public function recallInfo(Request $request){
        $input = $request->post();
        if(empty($input['id'])){
            return json(sys_response(4000002));
        }

        $invoiceLogic = new InvoiceLogic();
        $res = $invoiceLogic->recallInvoiceInfo($input);

        list($code, $message, $data) = $res;
        return json(sys_response($code, $message, $data));
    }

    /*
    * 发票审核
    */
    public function verify(Request $request){
        $input = $request->post();
        if(empty($input['id']) || empty($input['status'])){
            return json(sys_response(4000002));
        }

        $input['user_id'] = $request->user["user_id"];

        $invoiceLogic = new InvoiceLogic();
        $res = $invoiceLogic->verifyInvoice($input);

        list($code, $message, $data) = $res;
        return json(sys_response($code, $message, $data));
    }
    
}