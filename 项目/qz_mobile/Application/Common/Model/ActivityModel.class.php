<?php

namespace Common\Model;

use Think\Model;

class ActivityModel extends Model{

    /**
     * 获取有效活动【即将进行和正在进行的】
     * @return array
     */
    public function getEffectiveActivity()
    {
        $map = array(
            'status' => array('IN', array('0', '1')),
            'activity_location' => array('eq', 1)
        );
        return M('activity')->field('name, mobile_url, mobile_cover_url, status,start,end')->where($map)->order('start')->select();
    }

    /**
     * 获取过期活动
     * @param  integer $limit 获取数量
     * @return array
     */
    public function getExpiredActivity($limit = 5)
    {
        $map = array(
            'status' => array('EQ', '2')
        );
        return M('activity')->field('name, mobile_url, mobile_cover_url')->where($map)->order('start DESC')->limit($limit)->select();
    }
}