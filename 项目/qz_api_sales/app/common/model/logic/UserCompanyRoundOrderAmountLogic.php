<?php
// +----------------------------------------------------------------------
// | UserCompanyRoundOrderAmountLogic 多轮单单价
// +----------------------------------------------------------------------
namespace app\common\model\logic;

use app\common\model\db\UserCompanyRoundOrderAmount;

class UserCompanyRoundOrderAmountLogic
{
    protected $AmountDb;

    public function __construct()
    {
        $this->AmountDb = new UserCompanyRoundOrderAmount();
    }

    public function createSaveData($input)
    {
        $save = [];
        if (!empty($input['gz_price'])) {
            //公装价格
            $save[] = [
                'company_id' => $input['id'],
                'type' => 1,
                'price' => trim($input['gz_price']),
            ];
        }
        if (!empty($input['jb_price'])) {
            //局改价格
            $save[] = [
                'company_id' => $input['id'],
                'type' => 2,
                'price' => trim($input['jb_price']),
            ];
        }
        if (!empty($input['mjmin_price'])) {
            //面积小于等于的轮单单价
            $save[] = [
                'company_id' => $input['id'],
                'type' => 3,
                'mianji' => trim($input['mjmin_mianji']),
                'price' => trim($input['mjmin_price']),
            ];
        }
        if (!empty($input['mjmax_price'])) {
            //面积大于的轮单单价
            $save[] = [
                'company_id' => $input['id'],
                'type' => 4,
                'mianji' => trim($input['mjmax_mianji']),
                'price' => trim($input['mjmax_price']),
            ];
        }
        return $save;
    }

    public function delAmount($company_id)
    {
        return $this->AmountDb->delRoundOrderAmountInfo($company_id);
    }

    public function saveAmount($save)
    {
        return $this->AmountDb->addRoundOrderAmount($save);
    }
}