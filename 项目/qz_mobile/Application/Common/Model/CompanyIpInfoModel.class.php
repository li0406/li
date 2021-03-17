<?php
/**
 * Ip地址信息表
 * Created by PhpStorm.
 * User: mcj
 * Date: 2018/6/22
 * Time: 16:07
 */
namespace Common\Model;
use Think\Model;
class CompanyIpInfoModel extends Model{
    public function getInfo($map=[]){
      return M('company_ip_info')->where($map)->find();
    }

    public function insertInfo($data){
        return M("company_ip_info")->add($data);
    }

    public function setInc($where=[],$field,$number=1){
        return M("company_ip_info")->where($where)->setInc($field, $number);
    }

}