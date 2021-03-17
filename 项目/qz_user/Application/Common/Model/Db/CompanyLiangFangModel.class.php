<?php
/**
 * Created by PhpStorm.
 * author: mcj
 * Date: 2018/8/22
 * Time: 13:16
 * 未量房订单/二次回访订单
 */

namespace Common\Model\Db;

use Think\Model;

class CompanyLiangFangModel extends Model
{
    protected $tableName = 'company_liangfang';

    public function getTwiceBackCountSimple($map = [])
    {
        return M('company_liangfang')
            ->strict(true)
            ->where($map)
            ->count();
    }
}