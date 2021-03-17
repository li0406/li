<?php

namespace Home\Model\Db;
use Think\Model;

class WorkSiteLiveModel extends Model
{
    protected $autoCheckFields = false;

    public function getWorkSiteInfo($where , $field = '*')
    {
        return M('worksite_live')->where($where)->find();
    }

    public function getWorkInfo($where, $field = 'i.*'){
        return M('worksite_live')->alias('l')
            ->field($field)
            ->join('qz_worksite_info i on l.id = i.live_id')
            ->where($where)
            ->select();
    }

    public function delWorkSiteMedia($where = []){
        return M('worksite_media')->where($where)->delete();
    }

    public function delWorkSiteInfo($where = []){
        return M('worksite_info')->where($where)->delete();
    }

    public function delWorkSiteUserRelate($where = []){
        return M('worksite_user_relate')->where($where)->delete();
    }

    public function delWorkSiteLive($where = []){
        return M('worksite_live')->where($where)->delete();
    }
}