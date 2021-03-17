<?php
/**
 * Created by PhpStorm.
 * author: mcj
 * Date: 2018/8/22
 * Time: 13:16
 * 二次回访推送订单
 */

namespace Common\Model\Db;

use Think\Model;

class CompanyLiangFangCorrModel extends Model
{
    protected $tableName = 'company_liangfang_correlation';

    public function countTwiceBackSimple($map = [])
    {
        return M('company_liangfang_correlation')
            ->strict(true)
            ->where($map)
            ->count();
    }

    /**
     * 修改二次回访推送消息记录
     * author: mcj
     * @param array $map
     * @param $data
     * @return bool
     */
    public function updateCorr($map = [], $data)
    {
        return M('company_liangfang_correlation')->where($map)->save($data);
    }

    /**
     * 查询二次回访记录数量
     * author: mcj
     * @param $map
     * @return int
     */
    public function getBackReplyCount($map)
    {
        return M('company_liangfang_correlation')
            ->where($map)
            ->alias("t")
            ->join("inner join qz_orders as b on t.order_id = b.id")
            ->count();
    }

    /**
     * 查询二次回访记录数量
     * author: mcj
     * @param $map
     * @return int
     */
    public function getBackReplyCountSimple($map)
    {
        return M('company_liangfang_correlation')
            ->strict(true)
            ->where($map)
            ->count();
    }

    /**
     * 查询二次回访记录
     * author: mcj
     * @param $map
     * @param int $skip
     * @param int $p_size
     * @return array
     */
    public function getBackReply($map, $skip = 0, $p_size = 20)
    {
        $buildSql = M("company_liangfang_correlation")->where($map)->alias("t")
            ->join("inner join qz_orders as b on t.order_id = b.id")
            ->limit($skip . "," . $p_size)
            ->field("t.*,b.qiandan_status,b.type_fw,b.sex,b.qx,b.yusuan,b.qiandan_companyid,b.time as ordertime,b.name as ordername,b.xiaoqu,b.mianji")
            ->buildSql();
        return M("company_liangfang_correlation")->table($buildSql)->alias("a")
            ->join("left join qz_area as c on c.qz_areaid = a.qx")
            ->join("left join qz_jiage as d on d.id = a.yusuan")
            ->join("left join qz_order_company_review as e on e.orderid = a.order_id and e.comid=a.company_id ")
            ->order("a.ordertime desc")
            ->field("a.*,c.qz_area as qx,d.name as jiage,e.status as review_status")
            ->select();
    }
}