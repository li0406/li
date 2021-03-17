<?php
/**
 * voip电话表
 */
namespace Common\Model;
use Think\Model;
class AdminvoiptelsModel extends Model{
    protected $tableName = "admin_voip_tels";

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
}