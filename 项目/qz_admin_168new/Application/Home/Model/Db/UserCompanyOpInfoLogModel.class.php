<?php
// +----------------------------------------------------------------------
// | UserCompanyOpInfoLogModel  后台用户完善状态修改
// +----------------------------------------------------------------------

namespace Home\Model\Db;

use Think\Model;

class UserCompanyOpInfoLogModel extends Model
{
    protected $autoCheckFields = false;
    protected $table = 'qz_user_company_op_info_log';
    /**
     * 新增编辑操作
     *
     * @param $data
     * @param array $map
     */
    public function addLog($data,$map = [])
    {
        if (empty($map)){
            //新增操作
            $result = $this->add($data);
        } else {
            //修改操作
            $result = $this->where($map)->save($data);
        }
        return $result;
    }
}