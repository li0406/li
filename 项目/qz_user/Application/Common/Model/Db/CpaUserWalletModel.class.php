<?php
/**
 * 家具商用户额外信息
 */

namespace Common\Model\Db;

use Think\Model;

class CpaUserWalletModel extends Model
{
    protected $tableName = 'cpa_user_wallet';

    /**
     * 获取用户钱包信息
     * @return [type] [description]
     */
    public function getUserWalletByUid($uid)
    {
        $map = array(
            "user_id" => $uid
        );
        return $this->where($map)->find();
    }
}