<?php

// +----------------------------------------------------------------------
// | NewMediaUserSrcModel
// +----------------------------------------------------------------------
namespace Home\Model\Db;

use Think\Model;

class NewMediaUserSrcModel extends Model {

    /**
     * 渠道标识发单量统计
     * @param $userids
     * @param $time_begin
     * @param $time_end
     * @param int $type 人员/渠道对应类型 : 1.一对一 2.多对多
     * @return array
     */
    public static function getOrderSendStatistics($userids, $time_begin, $time_end ,$field = 'send_orders'){
        if (empty($userids)) {
            return [];
        }

        $map = array(
            "us.assess_admin_id" => array("IN", $userids),
            "o.time_real" => array("BETWEEN", [$time_begin, $time_end])
        );

        return M("new_media_user_src")->alias("us")->where($map)
            ->join("inner join qz_yy_order_info as yy on yy.src = us.src")
            ->join("inner join qz_orders as o on o.id = yy.oid")
            ->field("us.assess_admin_id,count(us.assess_admin_id) " . $field . ",us.type,us.src")
            ->group("us.assess_admin_id,us.type,us.src")
            ->select();
    }

    /**
     * 渠道标识完成量统计(一对一)
     * @param  [type] $userids [description]
     * @return [type]          [description]
     */
    public static function getOrderFinishStatistics($userids, $time_begin, $time_end, $field = 'finish_orders')
    {
        if (empty($userids)) {
            return [];
        }

        $map = array(
            "us.assess_admin_id" => array("IN", $userids),
            "cn.lasttime" => array("BETWEEN", [$time_begin, $time_end]),
            "o.type_fw" => array("EQ", 1)
        );

        return M("new_media_user_src")->alias("us")->where($map)
            ->join("inner join qz_yy_order_info as yy on yy.src = us.src")
            ->join("inner join qz_orders as o on o.id = yy.oid and o.`on` = 4")
            ->join("inner join qz_order_csos_new as cn on cn.order_id = o.id")
            ->field("us.assess_admin_id,count(us.assess_admin_id) " . $field . ",us.type,us.src")
            ->group("us.assess_admin_id,us.type,us.src")
            ->select();
    }

    /**
     * 获取已分配渠道
     *
     * @return mixed
     */
    public function getUsedSrc($map = [], $filed = 'src')
    {
        return M('new_media_user_src')->where($map)->getField($filed, true);
    }

    /**
     * 获取用户分配的src
     *
     * @param $map
     * @return mixed
     */
    public function getUserSrc($map = [])
    {
        return M('new_media_user_src')->alias('us')
            ->where($map)
            ->join('qz_order_source source on source.src = us.src')
            ->field('us.assess_admin_id,us.src,source.name,source.alias')
            ->select();
    }

    /**
     * 添加数据
     *
     * @param $data
     * @param $map
     * @return bool|mixed
     */
    public function addAllUserSrc($data)
    {
        return M('new_media_user_src')->addAll($data);
    }

    /**
     * 删除数据
     *
     * @param $map
     * @return mixed
     */
    public function delUserSrc($map)
    {
        return M('new_media_user_src')->where($map)->delete();
    }

    public function updateUserSrc($where, $data)
    {
        return M('new_media_user_src')->where($where)->save($data);
    }
    public function getUserCommonSrc($where, $field = '*')
    {
        return M('new_media_user_src')->field($field)->where($where)->select();
    }

    public function addCommonSrc($save){
        return M('new_media_user_src')->addAll($save);
    }

    public function getUserCommonCount($where)
    {
        $buildSql = M('new_media_user_src')->alias('us')
            ->field('us.src')
            ->join('qz_adminuser u on u.id = us.assess_admin_id')
            ->join('qz_order_source s on s.src = us.src')
            ->where($where)
            ->group('us.src')
            ->buildSql();
        return M()->table($buildSql)->alias('t')->count();
    }

    public function getUserCommonList($where, $field = '*')
    {
        return M('new_media_user_src')->alias('us')
            ->field($field)
            ->join('qz_adminuser u on u.id = us.assess_admin_id')
            ->join('qz_order_source s on s.src = us.src')
            ->where($where)
            ->select();
    }
}
