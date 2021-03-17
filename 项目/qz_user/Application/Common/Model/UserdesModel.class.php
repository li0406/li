<?php
/**
 *  设计师详细信息表
 */
namespace Common\Model;
use Think\Model;
class UserdesModel extends Model{
    protected $tableName ="user_des";

    /**
     * 添加详细信息
     */
    public function addDes($data)
    {
        return M("user_des")->add($data);
    }

    /**
     * 编辑详细信息
     */
    public function editDes($id, $data)
    {
        $map = array(
            "userid" => $id
        );
        $desc = M("user_des")->where($map)->find();
        if (empty($desc)) {
            $data['userid'] = $id;
            return M("user_des")->add($data);
        } else {
            return M("user_des")->where($map)->save($data);
        }
    }
}