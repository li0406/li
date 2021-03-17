<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/5
 * Time: 16:27
 */

/**
 * 每日真会员记录模块
 */

namespace Home\Model\Logic;

class LogUserRealCompanyModel {


    /**
     * 获取 城市平均会员统计  列表带翻页
     * @param  string $cs 城市id
     * @param  string $begin 开始时间
     * @param  string $begin 结束时间
     * @return mixed
     */
    public function cityAvgVip($cs, $begin, $end)
    {

        if (!empty($begin) && !empty($end)) {
            $begin = strtotime($begin);
            $end = strtotime(date('Y-m-d 23:59:59', strtotime($end)));
        } else {
            $begin = strtotime(date('Y-m-01', strtotime(date("Y-m-d")) ) ); //当月第一天
            $end = time();
        }

        $count = self::cityAvgVipListCount($cs,$begin,$end);

        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $p->setConfig('prev', "上一页");
            $p->setConfig('next', '下一页');
            $show    = $p->show();

            $list = self::cityAvgVipList($cs,$begin,$end,$p->firstRow,$p->listRows);
            //$list = array_slice($list, $p->firstRow ,$p->listRows);

        }
        return array("list"=>$list,"page"=>$show);
    }

    /**
     * 获取 城市平均会员统计  统计个数
     * @param  string $cs 城市id
     * @param  string $begin 开始时间
     * @param  string $begin 结束时间
     * @return mixed
     */
    public function cityAvgVipListCount($cs,$begin,$end) {
        return D("Home/Db/LogUserRealCompany")->cityAvgVipListCount($cs,$begin,$end);
    }

    /**
     * 获取 城市平均会员统计  列表
     * @param  string $cs 城市id
     * @param  string $begin 开始时间
     * @param  string $begin 结束时间
     * @param  string $pageIndex 页数
     * @param  string $pageCount 条数
     * @return mixed
     */
    public function cityAvgVipList($cs,$begin,$end, $pageIndex, $pageCount) {
        return D("Home/Db/LogUserRealCompany")->cityAvgVipList($cs,$begin,$end, $pageIndex, $pageCount);
     }
}