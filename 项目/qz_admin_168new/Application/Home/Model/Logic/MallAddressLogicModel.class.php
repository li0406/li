<?php


namespace Home\Model\Logic;


use Home\Model\Db\MallAddressModel;

class MallAddressLogicModel
{
    public function getAllAddressListByuuid($uuid)
    {
        $malladdressmodel = new MallAddressModel();
        $addresslist = $malladdressmodel->getAllAddressListByuuid($uuid);
        if (count($addresslist) > 0) {
            foreach ($addresslist as $key => $val) {
                $addresslist[$key]['tel'] = substr_replace($val['tel'], "*****", 3, 5); //做一个中间为星号的号码
            }
        }
        return $addresslist ? $addresslist : [];
    }

}