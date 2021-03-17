<?php


namespace app\common\model\db;


use think\Model;

class OrderReviewNewPush extends Model
{
    protected $table = 'qz_order_review_new_push';

    public function addPushInfo($save)
    {
        return $this->insertAll($save);
    }
}