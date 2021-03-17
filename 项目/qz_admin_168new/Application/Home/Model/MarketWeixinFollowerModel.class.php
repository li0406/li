<?php

namespace Home\Model;

Use Think\Model;

class MarketWeixinFollowerModel extends Model
{
    /**
     * 获取微信数据录入数量
     * @param  string  $add_time_start 开始时间
     * @param  string  $add_time_end   结束时间
     * @return integer                 数量
     */
    public function getCount($add_time_start = '', $add_time_end = '')
    {
        if (!empty($add_time_start)) {
            $map['add_time'][] = array('EGT', $add_time_start);
        }

        if (!empty($add_time_end)) {
            $map['add_time'][] = array('ELT', $add_time_end);
        }

        return M('market_weixin_follower')->where($map)->count();
    }

    /**
     * 获取微信数据录入列表
     * @param  string  $add_time_start 开始时间
     * @param  string  $add_time_end   结束时间
     * @param  integer $start          开始位置
     * @param  integer $end            结束位置
     * @param  string  $order          排序
     * @return array                   微信数据列表数组
     */
    public function getList($add_time_start = '', $add_time_end = '', $start = 0, $end = 20, $order = 'id DESC')
    {
        if (!empty($add_time_start)) {
            $map['w.add_time'][] = array('EGT', $add_time_start);
        }

        if (!empty($add_time_end)) {
            $map['w.add_time'][] = array('ELT', $add_time_end);
        }

        return M('market_weixin_follower')->alias('w')
                                          ->field('w.*, u.name')
                                          ->join('LEFT JOIN qz_adminuser AS u ON u.id = w.adminuser_id')
                                          ->where($map)
                                          ->order($order)
                                          ->limit($start, $end)
                                          ->select();
    }

    /**
     * 新增微信数据
     * @param array $save 存储的数组
     * @return  bool 是否成功
     */
    public function add($save = array())
    {
        if (!empty($save)) {
            return M('market_weixin_follower')->add($save);
        }
        return false;
    }
}