<?php
namespace app\common\model\db;

use think\Db;
use think\db\Query;
use think\Model;

class Invoice extends Model
{
    protected $table = 'qz_sale_report_invoice';

    public function getInfo($id){
        $map = [
            ['id', '=', (int)$id]
        ];
        $field = [
            'id','type','status','invoice_title','invoice_price','is_delete','creator_id' // ...
        ];
        return $this->field($field)->where($map)->find();
    }

    public function addInfo($data)
    {
        return $this->insertGetId($data);
    }

    public function updateInfo($id, $data){
        $map = [
            'id' => (int)$id
        ];
        return $this->where($map)->update($data);
    }

    public function addImgs($data){
        return $this->table('qz_sale_report_invoice_img')->insertAll($data);
    }

    public function deleteImgs($id, $type=0){
        $map = [
            ['invoice_id', '=', (int)$id]
        ];

        if($type){
            $map[] = ['img_type', '=', (int)$type];
        }else{
            $map[] = ['img_type', 'IN', '1,2'];
        }

        return $this->table('qz_sale_report_invoice_img')->where($map)->delete();
    }

    public function addLog($data){
        return $this->table('qz_sale_report_invoice_log')->insert($data);
    }

    /*
    * 小报备关联
    */
    public function linkReportPayment($data){
        return $this->table('qz_sale_report_invoice_with_payment')->insertAll($data);
    }

    /*
    * 删除小报备关联
    */
    public function deletelinkReportPayment($id){
        $map = [
            ['invoice_id', '=', (int)$id]
        ];
        return $this->table('qz_sale_report_invoice_with_payment')->where($map)->delete();
    }

    /**
    * 根据发票ID查询关联映射
    */
    public function getReportPaymentByInvoiceId($id){
        $map = [
            ['invoice_id', '=', $id]
        ];

        return $this->table('qz_sale_report_invoice_with_payment')->where($map)->select();
    }

    /**
    * 根据小报备ID查询关联映射
    */
    public function getInvoiceByReportPaymentId($report_payment_ids){
        $map = [
            ['a.is_delete', '=', 1],
            ['report_payment_id', 'IN', $report_payment_ids]
        ];

        return $this->alias('a')->field('b.*')->join('qz_sale_report_invoice_with_payment b', 'a.id=b.invoice_id')->where($map)->select();
    }

    /**
     * 查询发票报备列表数量
     * @param array $input 用户输入的搜索条件
     */
    public function getInvoicePageCount($input, $userIds)
    {
        $map = $this->getInvoicePageMap($input, $userIds);
        
        return Db::name('sale_report_invoice_with_payment')->alias('p')->where($map)
            ->join('qz_sale_report_invoice a', 'a.id = p.invoice_id', 'right')
            ->join('qz_adminuser c', 'c.id = a.creator_id', 'left')
            ->group('a.id')
            ->count('a.id');

    }

    /**
     * 发票报备列表查询条件
     * @param array $input 用户输入的搜索条件
     * @return  $map 搜索条件
     */
    public function getInvoicePageMap($input, $userIds)
    {
        $map = new Query();

        // 未删除数据
        $map->where('a.is_delete', 1);
        // 发票抬头
        if (!empty($input["invoice_title"])) {
            $map->where('a.invoice_title', 'like', '%' . $input["invoice_title"] . '%');
        }
        // 开票状态
        if (!empty($input["status"])) {
            if ($input["status"] == '168new') {
                $map->where("a.status", '<>', 1);
            } else {
                $map->where("a.status", $input["status"]);
            }
        }
        // 168后台开票状态
        if (!empty($input["status168"])) {
            $map->where("a.status",'<>' , 1);
        }
        // 开票类型
        if (!empty($input["type"])) {
            $map->where("a.type", $input["type"]);
        }
        // 开票公司
        if (!empty($input["billing_company"])) {
            $map->where("a.billing_company", $input["billing_company"]);
        }
        // 是否到账
        if (!empty($input["is_in_account"])) {
            $map->where("a.is_in_account", $input["is_in_account"]);
        }
        // 报备人
        if (!empty($input["invoice_report_username"])) {
            $map->where("c.user", $input["invoice_report_username"]);
        }
        // 小报备id
        if (!empty($input["payment_id"])) {
            $map->where("p.report_payment_id", $input["payment_id"]);
        }
        // 权限
        if (!empty($userIds)) {
            $map->where("a.creator_id",'in', $userIds);
        }

        // 部门搜索
        if(!empty($input['user_ids'])){
            $map->where("a.creator_id",'in', $input['user_ids']);   
        }

        // 保存开始时间
        if(!empty($input['created_start_date'])){
            $map->where('a.created_at', '>=', strtotime($input['created_start_date']));
        }

        // 保存结束时间
        if(!empty($input['created_end_date'])){
            $map->where('a.created_at', '<=', strtotime($input['created_end_date'].' 23:59:59'));
        }

        // 保存开始时间
        if(!empty($input['submit_start_date'])){
            $map->where('a.submit_time', '>=', strtotime($input['submit_start_date']));
        }

        // 保存结束时间
        if(!empty($input['submit_end_date'])){
            $map->where('a.submit_time', '<=', strtotime($input['submit_end_date'].' 23:59:59'));
        }

        return $map;
    }

    /**
     * 发票报备列表数据
     * @param array $input 用户输入的搜索条件
     * @param int $offset 默认页码
     * @param int $limit 默认每页个数
     */
    public function getInvoicePageList($input, $offset = 0, $limit = 0, $userIds)
    {
        $map = $this->getInvoicePageMap($input, $userIds);

        $field = 'a.id, a.creator_id, a.invoice_title, a.taxpayer_identification_number, a.type, a.invoice_price, ';
        $field .= 'a.is_in_account, a.billing_company, a.status, a.created_at,a.updated_at, c.user as invoice_report_username, ';
        $field .= 'a.check_reason, IFNULL(sum(d.payment_money), 0) as account_money,IFNULL(sum(d.payment_total_money), 0) as payment_total_money, a.submit_time';

        return Db::name('sale_report_invoice_with_payment')->alias('p')->where($map)
            ->join('qz_sale_report_invoice a', 'a.id = p.invoice_id', 'right')
            ->join('qz_adminuser c', 'c.id = a.creator_id', 'left')
            ->join('qz_sale_report_payment d', 'd.id = p.report_payment_id', 'left')
            ->field($field)
            ->order('a.updated_at desc')
            ->limit($offset, $limit)
            ->group('a.id')
            ->select();
    }


    public function getReportPaymentInfo($id, $onlyOne=false){
        $query = $this->table('qz_sale_report_payment')->alias('r')->field('r.id,r.payment_total_money,r.exam_status,r.exam_reason');
        $map = [
            ['r.exam_status', '<>', 1],
            ['r.is_delete', '=', 1],
        ];

        if(is_array($id) || false !== strpos($id, ',')){
            $map[] = ['r.id', 'IN', $id];
        }else{
            $map[] = ['r.id', '=', $id];
        }

        $res = $query->where($map)->select();

        if(!$res) return [];
        return $onlyOne ? $res[0] : $res;
    }

    /**
     * 查看发票报备详情
     * @param int $id
     */
    public function getInvoiceDetails($id, $userIds)
    {
        $map = new Query();
        $map->where("a.id", $id);
        if (!empty($userIds)) {
            $map->where("a.creator_id", 'in', $userIds);
        }

        // 查询字段
        $field = 'a.status, a.type, a.billing_company, a.is_in_account, a.invoice_price, a.invoice_title, a.taxpayer_identification_number, ';
        $field .= 'a.apply_email, a.receiver_name, a.receiver_phone, a.receiver_address, a.company_bank, a.company_bank_account,a.company_address, ';
        $field .= 'a.id as invoice_id, a.check_reason, a.other_remarks, a.company_phone, a.creator_id, b.user as invoice_report_username';

        return $this->alias('a')->where($map)
            ->join('qz_adminuser b', 'b.id = a.creator_id', 'left')
            ->field($field)
            ->find();
    }

}
