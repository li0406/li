<?php
// +----------------------------------------------------------------------
// | Huxing 户型结构
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\Model;

class Huxing extends Model
{
    protected $autoWriteTimestamp = false;
    protected $table = 'qz_huxing';

    public static function getHuxing($id = '', $field = '*')
    {
        return static::field($field)->where('id', $id)->find();
    }
}