<?php
/**
 * 家具商用户额外信息
 */

namespace Common\Model\Db;

use Think\Model;

class JiajuUserCompanyModel extends Model
{
    protected $tableName = 'jiaju_user_company';

    /**
     * 编辑用户额外信息
     * @return [type] [description]
     */
    public function editJiajuCompany($id, $data)
    {
        $map = array(
            'company_id' => $id
        );
        return $this->where($map)->save($data);
    }

    /**
     * 获取用户额外信息
     * @param string $user_id
     * @return mixed
     */
    public function getJiajuCompany($user_id = '')
    {
        $map = array(
            'company_id' => array('EQ',$user_id)
        );
        return $this->where($map)->find();
    }

    /**
     * 添加用户额外信息
     * @return [type] [description]
     */
    public function addJiajuCompany($id, $data)
    {
        $data['company_id'] = $id;
        return $this->add($data);
    }
}