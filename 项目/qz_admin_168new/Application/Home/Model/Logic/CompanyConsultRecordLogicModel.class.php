<?php
/**
 * 装修公司咨询处理记录
 * Created by PhpStorm.
 * User: mcj
 * Date: 2019/1/16
 * Time: 13:27
 */

namespace Home\Model\Logic;


class CompanyConsultRecordLogicModel
{

    public function insertRecord($data, $user = [])
    {
        $record_key = [
            'consult_id', 'deal_man', 'address', 'deal_type', 'success_level', 'communication', 'status'
        ];
        $insert_data = array_only($data, $record_key);
        $insert_data['user_id'] = empty($user['id']) ? 0 : $user['id'];
        $insert_data['dept'] = empty($user['department_id']) ? 0 : $user['department_id'];
        $insert_data['time'] = time();
        D("CompanyConsultRecord")->insertRecord($insert_data);
        M("company_consult")->where(['id' => $insert_data['consult_id']])->setField('record_status', $insert_data['status']);
        return true;

    }

    public function selectConsultDealRecord($consult_ids = [])
    {
        if (!$consult_ids) {
            return [];
        }
        $record = D("CompanyConsultRecord")->selectRecord(['a.consult_id' => ['in', $consult_ids]]);
        $data = [];
        foreach ($record as $value) {
            $data[$value['consult_id']][] = $value;
        }
        return $data;
    }

}