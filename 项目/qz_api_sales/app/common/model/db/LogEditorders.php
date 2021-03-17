<?php
// +----------------------------------------------------------------------
// | LogEditorders
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\Model;

class LogEditorders extends Model
{
    protected $autoWriteTimestamp = false;
    protected $table = 'qz_log_editorders';

    public function addLog($data){
        return $this->save($data);
    }
}