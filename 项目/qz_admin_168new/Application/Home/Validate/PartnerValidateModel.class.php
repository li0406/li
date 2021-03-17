<?php
/**
 * @des:合作商管理校验
 */

namespace Home\Validate;


use Home\Model\PartnerModel;
use Think\Model;

class PartnerValidateModel extends Model
{
    protected $autoCheckFields = false;

    public static function checkAddHzs(&$input)
    {
        $model = new PartnerModel();
        $existPartner = $model->isExistAccount($input['account'],false);
        if (!empty($existPartner) && (!isset($input['id']) || empty($input['id']))) {
            return '账户已存在!';
        }
        if (!in_array(isset($input['status']), [0, 1])) {
            return '开启状态未定义!';
        }
        return true;
    }
}