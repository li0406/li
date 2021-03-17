<?php

namespace app\common\model\db;

use think\db\Query;
use think\db\Where;
use think\Model;

class AdminAuditCitys extends Model {

    protected $autoWriteTimestamp = false;

    /**
     * 根据城市ID删除关联
     * @param  [type] $cids [description]
     * @return [type]       [description]
     */
    public static function deleteByCids($cids){
        return static::where('cid', 'in', $cids)->delete();
    }

}