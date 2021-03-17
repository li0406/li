<?php

namespace app\common\model\db;

use think\Db;
use think\Model;
use think\db\Query;

class MsgTemplateLink extends Model {

    protected $autoWriteTimestamp = false;

    /**
     * 查询消息链接
     * @param  [type] $app_slot     [description]
     * @param  [type] $template_ids [description]
     * @return [type]               [description]
     */
    public function getMsgLink($app_slot, $template_ids){
        $map = new Query();
        $map->where("app_slot", $app_slot);
        $map->where("template_id", "in", $template_ids);

        return $this->where($map)->select();
    }
}