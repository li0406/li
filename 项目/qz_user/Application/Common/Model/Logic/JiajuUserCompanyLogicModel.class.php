<?php
/**
 * 家具商用户信息
 */

namespace Common\Model\Logic;

class JiajuUserCompanyLogicModel
{
    /**
     * 编辑用户用户信息表额外信息
     * @param [int]$id 用户ID
     * @param [array]$data 用户地址/联系人/联系手机
     * @return mixed
     */
    public function editUserCompany($id,$data)
    {
        $saveData['address'] = $data['dz'];
        $saveData['contact_user'] = $data['name'];
        $saveData['contact_phone'] = $data['tel'];
        $saveData['sale_type'] = $data['sale_type'];
        $saveData['sale_range'] = $data['sale_range'];
        $saveData['furniture_category'] = $data['furniture_category'];
        $saveData['furniture_style'] = $data['furniture_style'];
        $saveData['furniture_level'] = $data['furniture_level'];
        $saveData['furniture_brand'] = $data['furniture_brand'];
        $saveData['update_time'] = time();
        $company_infomation = D('Common/Db/JiajuUserCompany')->getJiajuCompany($id);
        if (!empty($company_infomation)){
            return D('Common/Db/JiajuUserCompany')->editJiajuCompany($id,$saveData);
        } else {
            return D('Common/Db/JiajuUserCompany')->addJiajuCompany($id,$saveData);
        }
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
            'id' => array('EQ', $id)
        );
        if (!empty($cs)) {
            $map['cs'] = array('EQ', $cs);
        }
        return D('Common/Db/User')->getJiajuUserInfo($map);
    }

    /**
     * 获取家具公司基本信息
     * @param  [int] $id [用户ID]
     * @return [array]用户最近10条操作日志
     */
    public function getUserLogByUid($id)
    {
        $map = array(
            'userid' => array('EQ', $id)
        );
        return D('Common/Db/LogUser')->getLogList($map,10);
    }
}