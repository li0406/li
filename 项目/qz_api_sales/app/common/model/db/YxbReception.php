<?php
// +----------------------------------------------------------------------
// | YxbReception
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\Model;

class YxbReception extends Model
{
    protected $autoWriteTimestamp = false;
    protected $table = 'qz_yxb_reception';

    public function addReceptionLog($data)
    {
        return $this->saveAll($data);
    }
}