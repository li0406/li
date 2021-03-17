<?php
/**
 * IP地址模块相关逻辑
 * Created by PhpStorm.
 * User: mcj
 * Date: 2018/6/22
 * Time: 13:27
 */

namespace Common\Logic;

use Think\Model;

class CompanyIPInfoLogic extends Model
{
    protected $autoCheckFields = false;

    public function getIPInfo($type = 1)
    {
        $ip = get_client_ip();
        $ip_info_model = D("CompanyIpInfo");
        $ip_info = $ip_info_model->getInfo(['type' => $type, 'ip' => $ip]);
        if (!$ip_info) {
            $data = [
                'ip' => $ip,
                'type' => $type,
                'time' => 1,
            ];
            $ip_id = D("CompanyIpInfo")->insertInfo($data);
            return $ip_id;
        } else {
            $ip_info_model->setInc(['id' => $ip_info['id']],'time',1); //IP重复数量加一
            return $ip_info['id'];
        }
    }

}