<?php
/**
 *  活动配置表
 */
namespace Common\Model;
use Think\Model;
class ActivityconfigModel extends Model{
    protected $tableName = "activity_config";

    /**
     * 获取活动配置信息
     * @return [type] [description]
     */
    public function getConfig($type){
        $map = array(
            "type"=>array("EQ",$type)
                     );
        return  M("activity_config")->where($map)->find();
    }
}