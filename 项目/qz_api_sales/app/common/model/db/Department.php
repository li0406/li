<?php
// +----------------------------------------------------------------------
// | Department
// +----------------------------------------------------------------------

namespace app\common\model\db;

use think\Model;

class Department extends Model
{
    protected $table = 'qz_department';
    protected $autoWriteTimestamp = false;
}