<?php

namespace app\common\model\logic;

use app\common\enum\BaseStatusCodeEnum;
use app\common\model\db\Contacts;
use app\common\model\db\ReportCompany;
use think\Db;
use think\Exception;

class ContactsLogic
{
    /**
     * 新增用户
     * @param $info
     * @param $user
     * @return array
     */
    public function saveUser($info, $user)
    {
        $save = [
            'company_id' => $info['company_id'],
            'uid' => $user['user_id'],
            'name' => $info['user_name'],
            'tel' => isset($info['user_tel']) ? $info['user_tel'] : '',
            'wx' => isset($info['user_wx']) ? $info['user_wx'] : '',
            'qq' => isset($info['user_qq']) ? $info['user_qq'] : '',
            'job' => isset($info['user_job']) ? $info['user_job'] : '',
            'updated_at' => time()
        ];

        $conDb = new Contacts();
        $save['created_at'] = time();
        $status = $conDb->insertContacts($save);
        if ($status) {
            return sys_response(0);
        } else {
            return sys_response(5000002);
        }
    }

    /**
     * 更新联系人信息
     * @param $info
     * @param $user
     * @return array
     */
    public function updateUser($info, $user)
    {
        if (empty($info['users'])) {
            return sys_response(4000002);
        }
        //验证日报公司和会员公司的关联
        $comLogic = new CompanyLogic();
        $check = $comLogic->getCompanyRepeat($info['company_user_id'], $info['company_id']);
        if ($check['is_repeat'] == 1) {
            return ['error_code' => 4000003, 'error_msg' => BaseStatusCodeEnum::CODE_4000003];
        }
        $contactsSave = [];
        $ids = [];//过滤不删除的联系人id
        foreach ($info['users'] as $k => $v) {
//            if (empty($v['user_id']) || empty($v['user_name'])) {
//                return sys_response(4000002);
//            }
            $contactsSave[] = [
                'company_id' => $info['company_id'],
                'uid' => $user['user_id'],
                'id' => $v['user_id'],
                'name' => $v['user_name'],
                'tel' => isset($v['user_tel']) ? $v['user_tel'] : '',
                'wx' => isset($v['user_wx']) ? $v['user_wx'] : '',
                'qq' => isset($v['user_qq']) ? $v['user_qq'] : '',
                'job' => isset($v['user_job']) ? $v['user_job'] : '',
                'updated_at' => time()
            ];
            $ids[] = $v['user_id'];
        }
        $companySave = [
            'user_id' => empty($info['company_user_id'])?0:$info['company_user_id'],
            'id' => $info['company_id'],
            'address' => $info['user_dz'],
            'intention' => $info['user_intention'],
            'is_new' => $info['user_style'],
            'updated_at' => time(),
            'op_uid' => $user['user_id']
        ];
        Db::startTrans();
        $status = 1;
        try {
            $conDb = new Contacts();
            //删除已有联系人
            $conDb->delContacts($info['company_id'],$ids);
            //修改联系人
            $conDb->saveContacts($contactsSave);
            //修改公司数据
            $companyDb = new ReportCompany();
            $companyDb->saveReportCompany([$companySave]);
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $status = 0;
        }
        if (!empty($status)) {
            return sys_response(0);
        } else {
            return sys_response(5000002);
        }
    }

    public function getContacts($company_id)
    {
        $conDb = new ReportCompany();
        return $contacts = $conDb
            ->where(['id'=>$company_id])
            ->with(['contacts','city','areaname','userCompany'])
            ->find();
    }
}