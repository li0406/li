<?php
// +----------------------------------------------------------------------
// | LogOrderThirdparty
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\Model;

class LogOrderThirdparty extends Model
{
    protected $autoWriteTimestamp = false;
    protected $table = 'qz_order_thirdparty_log';

    public function addLog($data){
        return $this->saveAll($data);
    }
}