<?php
/**
 * 注册用户表 对应qz_user表
 */
namespace Home\Model;
Use Think\Model;
class SaleShowModel extends Model{
    protected $autoCheckFields = false;
   /**
    * 查询城市重点系数
    * @param  
    * @param  [type]  $cityid     [城市ID]
    * @return [type]           [description]
    */
    public function getCityPointByCid($cityid,$type) {
        $map['cid']         = $cityid;
        $map['typeid']      = $type;
        $citypoint = M("sales_setting_value")->alias("v")
                                        ->join("left join qz_sales_category c on c.id = v.pid and v.status = 1")
                                        ->field("c.name as citypoint,v.start,v.end")
                                        ->where($map)
                                        ->select();
        return $citypoint;
    }

    /**
    * 查询城市职能管辖关系
    * @param  
    * @param  [type]  $cityid     [城市ID]
    * @return [type]           [description]
    */
    public function getCityDepartment($cityid) {
        $map['cid']         = $cityid;
        $map['typeid']      = array('IN',['1','5','6']);
        //
        $department = M("sales_setting_value")->alias("v")
                                        ->join("left join qz_sales_category c on c.id = v.pid and v.status = 1 and c.status = 0")
                                        ->field("c.name as quyujingli,v.start,v.end,c.pid,c.type,v.cid")
                                        ->where($map)
                                        ->limit(1)
                                        ->select();
        if($department[0]['pid'] == 0){
            //直属师长管理
            $department[0]['shizhang'] = $department[0]['quyujingli'];
            $department[0]['quyujingli'] = '';
        }else{
            //查询上级
            $fmap['id'] = $department[0]['pid'];
            $fmap['status'] = 0;
            $arr = M('sales_category')->where($fmap)->select();
            if($arr[0]['pid'] == 0){
                $department[0]['shizhang'] = $arr[0]['name'];
            }else{
                $smap['id']         = $arr[0]['pid'];
                $smap['status']     = 0;
                $sArr = M("sales_category")->where($smap)->select();
                $department[0]['shizhang'] = $sArr[0]['name'];
            }
        }


        return $department;

    }

    /**
    * 根据时间，查询会员数量(月的最后一天)
    * @param  
    * @param  [type]  $cityid     [城市ID]
    * @return [type]           [description]
    */
    public function getUserRealCompany($cityid,$time) {
        $time = date('Y-m-d H:i:s', strtotime(date('Y-m-01', strtotime($time)) . ' +1 month -1 day'));
        $map['city_id']     = $cityid;
        $map['time']        = $time; 

        $company = M("log_user_real_company")->where($map)->select();

        return $company;
    }

    


}