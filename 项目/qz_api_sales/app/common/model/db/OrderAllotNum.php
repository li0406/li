<?php
// +----------------------------------------------------------------------
// | OrderAllotNum  装修订单最大分配数
// +----------------------------------------------------------------------

/**
 * Created by PhpStorm.
 * User: user
 * Date: 2019/6/6
 * Time: 14:14
 */

namespace app\common\model\db;


use think\Model;

class OrderAllotNum extends Model
{
    protected $autoWriteTimestamp = false;
    protected $table = 'qz_order_allot_num';
}