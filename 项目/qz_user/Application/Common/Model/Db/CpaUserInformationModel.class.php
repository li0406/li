<?php
/**
 * 家具商用户额外信息
 */

namespace Common\Model\Db;

use Think\Model;

class CpaUserInformationModel extends Model
{
    protected $tableName = 'cpa_user_information';

    /**
     * 编辑用户额外信息
     * @return [type] [description]
     */
    public function editUserInfomation($id, $data)
    {
        $map = array(
            "user_id" => $id
        );
        return $this->where($map)->save($data);
    }

       public function getUserInfo($user_id = '')
    {
        $map = array(
            "user_id" => array("EQ",$user_id)
        );
        return M("cpa_user_information")->where($map)->find();
    }
}