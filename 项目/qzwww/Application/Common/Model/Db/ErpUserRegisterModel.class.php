<?php
// +----------------------------------------------------------------------
// |  ERP营销用户注册表
// +----------------------------------------------------------------------
namespace Common\Model\Db;

use Think\Model;

class ErpUserRegisterModel extends Model
{
    protected $tableName = 'erp_user_register';

    /**
     * 添加用户信息
     *
     * @param array $data 用户提交信息
     * @return mixed
     */
    public function addInfo($data)
    {
        $telfind = $this->where(['tel' => $data['tel']])->find();
        if ($telfind) {
            return $this->where(['id' => $telfind['id']])->save($data);
        } else{
            return $this->add($data);
        }
    }
}