<?php
/**
 * 家具商用户信息
 */

namespace Common\Model\Logic;

class CpaUserInformationLogicModel
{
    /**
     * 编辑用户用户信息表额外信息
     * @param [int]$id 用户ID
     * @param [array]$data 用户地址/联系人/联系手机
     * @return mixed
     */
    public function editUserInfomation($id,$data)
    {
        $saveData['address'] = $data['dz'];
        $saveData['contact_user'] = $data['name'];
        $saveData['contact_phone'] = $data['tel'];
        $saveData['update_time'] = time();
        return D("Common/Db/CpaUserInformation")->editUserInfomation($id,$saveData);
    }


    /**
     * 获取用户钱包信息
     * @param [int]$id 用户ID
     * @return mixed
     */
    public function getUserWallet($id)
    {
        return D("Common/Db/CpaUserWallet")->getUserWalletByUid($id);
    }

    /**
     * 获取家具公司基本信息
     * @param  [int] $id [用户ID]
     * @param  [int] $cs [城市ID]
     * @return [array]用户所有信息
     */
    public function getCompanyInfoByAdmin($id, $cs)
    {
        $map = array(
            "id" => array("EQ", $id)
        );
        if (!empty($cs)) {
            $map['cs'] = array("EQ", $cs);
        }
        return D("Common/Db/User")->getUser($map);
    }

    /**
     * 获取家具公司基本信息
     * @param  [int] $id [用户ID]
     * @return [array]用户最近10条操作日志
     */
    public function getUserLogByUid($id)
    {
        $map = array(
            "userid" => array("EQ", $id)
        );
        return D("Common/Db/LogUser")->getLogList($map,10);
    }

    public function getUserInfo($user_id)
    {
        // return D("Common/Db/CpaUserInformation")->getUserInfo($user_id);
    }
}