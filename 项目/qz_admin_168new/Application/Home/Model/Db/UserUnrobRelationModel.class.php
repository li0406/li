<?php
namespace Home\Model\Db;
use Think\Model;
class UserUnrobRelationModel extends Model
{
    protected $autoCheckFields = false;

    protected $tableName = 'user_unrob_relation';

    public function getUnRobCompanyById($order_id, $field = "*")
    {
        return $this->field($field)->where(['order_id' => ['eq', $order_id]])->select();
    }
}
