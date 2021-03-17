<?php
namespace app\common\model\db;

use think\Db;
use think\db\Query;
use think\Model;

class InvoiceImg extends Model
{
    protected $table = 'qz_sale_report_invoice_img';

    /**
     * 查看发票报备截图
     * @param int $id
     * @param int $type 图片类型 1.证件图片 2.其他说明图片
     */
    public function getInvoicePic($id, $type)
    {
        $map = new Query();
        $map->where('invoice_id', $id);
        $map->where('img_type', $type);

        // 查询字段
        $field = 'img_path as img_src';

        return $this->where($map)->field($field)->select();
    }


}
