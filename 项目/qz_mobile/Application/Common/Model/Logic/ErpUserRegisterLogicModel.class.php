<?php
// +----------------------------------------------------------------------
// |  ERP营销用户注册逻辑
// +----------------------------------------------------------------------
namespace Common\Model\Logic;

use Common\Model\Db\ErpUserRegisterModel;

class ErpUserRegisterLogicModel
{
    /**
     * 用户提交信息
     *
     * @param array $data 用户提交信息
     * @return mixed
     */
    public function register($data)
    {
        $model = new ErpUserRegisterModel();
        return $model->addInfo($data);
    }
}