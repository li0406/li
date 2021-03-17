<?php

namespace Home\Model;

Use Think\Model;

class LogMoveMemberCityModel extends Model{

    /**
     * 新增转移会员公司日志
     * @param array $save 值
     */
    public function addLogMoveMemberCity($save = array())
    {
        if (empty($save)) {
            return false;
        }
        return M('log_move_member_city')->add($save);
    }

    /**
     * 获取转移会员公司日志
     */
    public function getLogMoveMemberCityList()
    {
        return M("log_move_member_city")->alias('l')
                                        ->field('l.operate_member_id, l.remark, l.add_time, u.jc, a.name AS operate_adminuser, w.cname AS origin_city, x.qz_area AS origin_area, y.cname AS target_city, z.qz_area AS target_area')
                                        ->join('LEFT JOIN qz_user AS u ON u.id = l.operate_member_id')
                                        ->join('LEFT JOIN qz_adminuser AS a ON a.id = l.operate_adminuser_id')
                                        ->join('LEFT JOIN qz_quyu AS w ON w.cid = l.origin_city_id')
                                        ->join('LEFT JOIN qz_area AS x ON x.qz_areaid = l.origin_area_id')
                                        ->join('LEFT JOIN qz_quyu AS y ON y.cid = l.target_city_id')
                                        ->join('LEFT JOIN qz_area AS z ON z.qz_areaid = l.target_area_id')
                                        ->order('l.id DESC')
                                        ->select();
    }
}