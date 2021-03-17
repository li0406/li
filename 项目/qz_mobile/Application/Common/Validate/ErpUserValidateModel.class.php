<?php
/**
 * Created by PhpStorm.
 * User: qzjsb
 * Date: 2019/3/25
 * Time: 15:03
 */

namespace Common\Validate;


use Think\Model;

class ErpUserValidateModel extends Model
{
    protected $autoCheckFields = false;
    protected $tableName = 'erp_user_register';
}