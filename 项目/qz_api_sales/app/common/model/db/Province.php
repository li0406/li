<?php
// +----------------------------------------------------------------------
// | Area
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\Db;
use think\Model;

class Province extends Model
{
    protected $autoWriteTimestamp = false;
    protected $table = 'qz_area';

    // 获取所有省份
    public function getAllProvince()
    {
        return Db::table("qz_province")->field('qz_provinceid, qz_province')->select();
    }


}