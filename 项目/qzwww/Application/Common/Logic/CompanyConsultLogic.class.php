<?php
/**
 * 文章模块相关逻辑
 * Created by PhpStorm.
 * User: mcj
 * Date: 2018/6/22
 * Time: 13:27
 */

namespace Common\Logic;

use Think\Model;

class CompanyConsultLogic extends Model
{
    protected $autoCheckFields = false;

    public function touchUs($data, $ip_info_id)
    {
        $consult_key = [
            'name', 'linkman', 'tel', 'qx', 'cs', 'custom_address','cooperation_type'
        ];
        $insert_data = array_only($data, $consult_key);
        $insert_data['ip_id'] = $ip_info_id;
        $insert_data['add_time'] = time();
        return D("CompanyConsult")->insertConsult($insert_data);
    }

    public function zhanLve($data, $ip_info_id)
    {
        $consult_key = [
            'name', 'linkman', 'tel','remark','qx', 'cs',
        ];
        $insert_data = array_only($data, $consult_key);
        $insert_data['ip_id'] = $ip_info_id;
        $insert_data['add_time'] = time();
        return D("CompanyConsult")->insertConsult($insert_data);
    }

}