<?php
// +----------------------------------------------------------------------
// |  ERP营销用户注册验证器
// +----------------------------------------------------------------------
namespace Common\Validate;

use Think\Model;

class ErpUserValidateModel extends Model
{
    protected $autoCheckFields = false;
    protected $tableName = 'erp_user_register';
}