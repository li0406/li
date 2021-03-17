<?php
/**
 * voip电话表
 */
namespace Home\Model;
use Think\Model;

class AdminVoipTelsModel extends Model{
    protected $autoCheckFields = false;
    /**
     * [editVoipInfo description]
     * @param  [type] $voip  [voip电话号码]
     * @param  [type] $data  [description]
     * @return [type]        [description]
     */
    public function editVoipInfo($voip,$data){
        $map = array(
            "voipAccount"=>array("EQ",$voip)
                     );
        return M("admin_voip_tels")->where($map)->save($data);
    }

    public function findVoipInfo($voip){
        $map = array(
            "voipAccount"=>array("EQ",$voip)
                     );
        return M("admin_voip_tels")->where($map)->find();
    }

    public function findVoipInfoByUid($id){
        $map = array(
            "use_id"=>array("EQ",$id)
                     );
        return M("admin_voip_tels")->where($map)->find();
    }

}