<?php
/**
 *  活动注册用户
 */
namespace Common\Model;
use Think\Model;
class ActivityprizeModel extends Model{
    protected $tableName = "activity_prize";

    /**
     * 获取奖品列表
     * @return [type] [description]
     */
    public function getPrizeList($type){
        $map = array(
            "type"=>array("EQ",$type),
            "enabled"=>array("EQ",1)
                     );
        return M("activity_prize")->where($map)->select();
    }


    /**
     * 编辑奖品
     */
    public function editPrize($id,$data){
        $map = array(
            "id"=>array("EQ",$id)
                     );
        return M("activity_prize")->where($map)->save($data);
    }

    /**
     * 奖品数量自动加1
     */
    public function setPrizeCount($id){
        $map = array(
            "id"=>array("EQ",$id)
                     );
        return M("activity_prize")->where($map)->setInc("use_count");
    }

}