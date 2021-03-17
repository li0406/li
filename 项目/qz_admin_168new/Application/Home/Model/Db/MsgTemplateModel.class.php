<?php

namespace Home\Model\Db;

use Think\Model;

class MsgTemplateModel extends Model {


    /**
     * 查询消息链接
     * @param  [type] $app_slot     [description]
     * @param  [type] $template_ids [description]
     * @return [type]               [description]
     */
    public function getMsgLink($app_slot, $template_ids){
        $map = array(
            "app_slot" => array("EQ", $app_slot),
            "template_id" => array("IN", $template_ids),
        );

        return M("msg_template_link")->where($map)->select();
    }

}