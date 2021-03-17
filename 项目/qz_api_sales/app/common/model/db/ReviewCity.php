<?php
// +----------------------------------------------------------------------
// | ReviewCity
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\Db;
use think\Model;
use think\db\Query;
use think\db\Where;

class ReviewCity extends Model
{
    protected $autoWriteTimestamp = false;

    public function getReviewCityInfo($cs)
    {
        return $this->where('cs', $cs)->find();
    }
}