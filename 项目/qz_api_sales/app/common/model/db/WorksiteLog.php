<?php

namespace app\common\model\db;

use think\Model;

class WorksiteLog extends Model {

    protected $autoWriteTimestamp = false;

    public static function addLog($type, $info_id, $operator, $from_app, $param = []){
        $data = array(
            "type" => $type,
            "info_id" => $info_id,
            "operator" => $operator,
            "from_app" => $from_app,
            "param" => json_encode($param),
            "created_at" => time()
        );

        return static::insert($data);
    }

    /**
     * 根据施工信息删除
     * @param $infoIds
     * @return bool
     */
    public static function deleteByInfoIds($infoIds){
        return static::where("info_id", "IN", $infoIds)->delete();
    }
}