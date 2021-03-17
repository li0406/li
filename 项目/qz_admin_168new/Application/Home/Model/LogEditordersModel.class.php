<?php

namespace Home\Model;
Use Think\Model;
/**
*
*/
class LogEditordersModel extends Model
{
    /**
     * 添加日志
     * @param [type] $data [description]
     */
    public function addLog($data)
    {
        return M("log_editorders")->add($data);
    }

    /**
     * 查询转单数量
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function findOrderTurnInfo($id)
    {
        $map = array(
            "type" => array("EQ",1),
            "orderid" => array("EQ",$id)
        );
        return M("log_editorders")->where($map)->find();
    }
}