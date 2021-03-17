<?php
namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\InvoiceLogicModel;

/**
 * 
 */
class InvoiceController extends HomeBaseController
{
    protected $header;
    protected $sales_api;

    public function _initialize()
    {
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

    // 列表页
    public function index()
    {
        $limit = 20;
        $invoiceLogicModel = new InvoiceLogicModel($this->header);
        $data = $invoiceLogicModel->getInvoiceList(I("get."), I("get.p"), $limit);

        // 获取搜索options
        $options = $invoiceLogicModel->getOptions();
        // 获取部门
        $dept = $invoiceLogicModel->getDeptList();

        if (!empty($data)) {
            import('Library.Org.Util.Page');
            $pageobj = new \Page($data["page"]["total_number"], $data["page"]["page_size"]);
            $data["pageshow"] = $pageobj->show();
        }

        $this->assign("status", $options['status']);
        $this->assign("type", $options['type']);
        $this->assign("billing_company", $options['billing_company']);
        $this->assign("is_in_account", $options['is_in_account']);
        $this->assign('dept', $dept);
        $this->assign("data", $data);
        $this->display();
    }

    // 详情
    public function detail()
    {
        $back_url = $_SERVER["HTTP_REFERER"];
        $check_url = "/invoice/index";
        $ret = strpos($back_url, $check_url);
        if ($ret == false) {
            $back_url = $check_url;
        }

        // 详情数据
        $invoiceLogicModel = new InvoiceLogicModel($this->header);
        $data = $invoiceLogicModel->getInvoiceDetails(I("get.id"));

        $this->assign("back_url", $back_url);
        $this->assign("invoiceDetails", $data["invoiceDetails"]);
        $this->assign("paymentDetails", $data["paymentDetails"]);
        $this->assign("paymentPics", $data["paymentPics"]);
        $this->assign("invoicePics", $data["invoicePics"]);
        $this->assign("invoiceOtherPics", $data["invoiceOtherPics"]);
        $this->assign("invoiceLogs", $data["invoiceLogs"]);
        $this->display();
    }

    // 小报备多次开票关联发票列表
    public function payment_invoice_list(){
        if (IS_AJAX) {
            $payment_id = I("get.payment_id");
            $invoice_id = I("get.invoice_id");
            $invoiceLogicModel = new InvoiceLogicModel($this->header);
            $data = $invoiceLogicModel->getPaymentInvoiceList($payment_id, $invoice_id);

            $this->assign("data", $data);
            $template = $this->fetch("payment_invoice_list");

            $this->ajaxReturn([
                "error_code" => 0,
                "error_msg" => "",
                "data" => [
                    "template" => $template
                ]
            ]);
        }
    }

    // 发票审核
    public function check_invoice(){
        if (IS_AJAX) {
            $id = I("post.id");
            $remark = I("post.remark");
            $status = I("post.status");
            $invoiceLogicModel = new InvoiceLogicModel($this->header);
            $response = $invoiceLogicModel->checkInvoice($id, $remark, $status);

            $this->ajaxReturn($response);
        }
    }

}
