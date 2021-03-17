<?php

namespace Home\Model\Db;

use Think\Model;

class UmengRecordModel extends Model
{
    protected $tableName = "umeng_record";

    //根据comid获取device_token
    public function getDeviceTokenByComidlist($comidlist){
        if(!empty($comidlist)){
            $where = [];
            $nowtime = time();
            $where['employee_id'] = array('eq', 0);
            $where['comid'] = array('in',$comidlist);
            $where['start_time'] = array('elt',$nowtime);
            $where['end_time'] = array('egt',$nowtime);
            return M('umeng_record')->where($where)->field('device_token,comid')->select();
        }else{
            return false;
        }
    }

    public function getCompanyDeviceTokenById($company_id)
    {
        $where['comid'] = array('EQ',$company_id);
        $where['end_time'] = array('egt',time());
        $where['employee_id'] = array('EQ', 0);
        
        return M('umeng_record')->where($where)->field('device_token')->select();
    }
}