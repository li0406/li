<?php
// +----------------------------------------------------------------------
// | Fengge 风格
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\Model;

class Fengge extends Model
{
    protected $autoWriteTimestamp = false;
    protected $table = 'qz_fengge';

    public static function getFengge($id = '', $field = '*')
    {
        return self::field($field)->where('id', $id)->find();
    }
}