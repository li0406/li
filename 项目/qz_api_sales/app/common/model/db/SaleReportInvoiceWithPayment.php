<?php
namespace app\common\model\db;

use think\Db;
use think\db\Query;
use think\Model;

class SaleReportInvoiceWithPayment extends Model
{
    protected $table = 'sale_report_invoice_with_payment';

    /**
     * 查询小报备表收款金额
     * @paran array $invoiceId 发票报备表id
     */
    public function getAccountMoney($invoiceId)
    {
        $map = new Query();
        $map->where('b.is_delete','=',1);

        if (is_array($invoiceId)) {
            $map->where("a.invoice_id", "in", $invoiceId);
        } else {
            $map->where("a.invoice_id", $invoiceId);
        }

        return Db::name('sale_report_invoice_with_payment')->alias('a')->where($map)
            ->join('qz_sale_report_payment b', 'b.id = a.report_payment_id', 'left')
            ->field('coalesce(sum(b.payment_money),0) as account_money, a.invoice_id')
            ->group("a.invoice_id")
            ->select();
    }

    /**
     * 查询小报备信息
     * @paran array $invoiceId 发票报备表id
     */
    public function getPaymentInfo($invoiceId)
    {
        $map = new Query();
        $map->where('b.is_delete', '=', 1);
        if (is_array($invoiceId)) {
            $map->where("a.invoice_id", "in", $invoiceId);
        } else {
            $map->where("a.invoice_id", $invoiceId);
        }

        return Db::name('sale_report_invoice_with_payment')->alias('a')->where($map)
            ->join('qz_sale_report_payment b', 'b.id = a.report_payment_id', 'left')
            ->field('b.exam_status, b.exam_reason, b.id')
            ->order('b.created_at desc')
            ->select();
    }

    /**
     * 查看小报备详情
     * @param int $id
     */
    public function getPaymentDetails($id)
    {
        $map = new Query();
        $map->where("a.invoice_id", $id);
        $map->where("b.is_delete", '=', 1);

        // 查询字段
        $field = 'b.id, b.company_name, b.cooperation_type, b.payment_money,b.payment_total_money, b.payment_time, b.exam_status, ';
        $field .= 'b.exam_reason, c.cname as city_name, d.user as creator_name';

        return Db::name('sale_report_invoice_with_payment')->alias('a')->where($map)
            ->join('qz_sale_report_payment b', 'b.id = a.report_payment_id', 'left')
            ->join('qz_quyu c', 'c.cid = b.city_id', 'left')
            ->join('qz_adminuser d', 'd.id = b.creator', 'left')
            ->field($field)
            ->select();
    }

    /**
     * 查看小报备截图
     * @param int $id
     */
    public function getPaymentPic($id)
    {
        $map = new Query();
        $map->where("a.invoice_id", $id);
        $map->where("c.img_src", 'NEQ',  'NULL');

        // 查询字段
        $field = "c.img_src";

        return Db::name('sale_report_invoice_with_payment')->alias('a')->where($map)
            ->join('qz_sale_report_payment b', 'b.id = a.report_payment_id', 'left')
            ->join('qz_sale_report_payment_img c', 'c.report_payment_id = b.id', 'left')
            ->field($field)
            ->select();
    }

    /**
     * 查看小报备关联发票的个数
     */
    public function getPaymentInvoiceIds($payment_ids)
    {
        $map = new Query();

        if (is_array($payment_ids)) {
            $map->where("a.report_payment_id", "in", $payment_ids);
        } else {
            $map->where("a.report_payment_id", $payment_ids);
        }
        $map->where('b.is_delete', '=', 1);

        return Db::name('sale_report_invoice_with_payment')->alias('a')->where($map)
            ->join('qz_sale_report_invoice b', 'b.id = a.invoice_id', 'left')
            ->field('count(a.invoice_id) as invoice_id_num')
            ->group('a.report_payment_id')
            ->select();
    }

    /**
     * 小报备多次开票关联发票列表
     * * @param int $payment_id 小报备id
     */
    public function getPaymentInvoiceList($payment_id, $invoice_id)
    {
        $map = new Query();
        $map->where('m.report_payment_id', $payment_id);
        $map->where('b.id','NEQ', $invoice_id);
        $map->where('b.is_delete', '=', 1);

        // 查询字段
        $field = 'b.invoice_title, b.taxpayer_identification_number, b.invoice_price, b.is_in_account, b.billing_company, ';
        $field .= 'b.status, b.creator_id, b.submit_time, c.user as invoice_report_username, b.id as invoice_id, ';
        $field .= 'IF (b.status = 1, 1, 2) AS paixu, m.account_money';

        $buildSql = Db::table('qz_sale_report_invoice_with_payment')->alias('a')
            ->join('qz_sale_report_payment p', 'a.report_payment_id = p.id')
            ->field('a.invoice_id, a.report_payment_id, SUM(p.payment_money) as account_money')
            ->group('a.invoice_id')
            ->buildSql();

        return Db::table($buildSql)->alias('m')->where($map)
            ->join('qz_sale_report_invoice b', 'b.id = m.invoice_id', 'left')
            ->join('qz_adminuser c', 'c.id = b.creator_id', 'left')
            ->field($field)
            ->order('paixu asc,b.submit_time desc')
            ->group('b.id')
            ->select();
    }

}
