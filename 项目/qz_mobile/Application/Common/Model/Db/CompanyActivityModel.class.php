<?php
namespace Common\Model\Db;

use Think\Model;

class CompanyActivityModel extends Model
{
	protected $tableName =  'company_activity';

    
    //根据装修公司id获取装修公司信息
    public function getCompanyActivity($field='*',$comid='',$id='')
    {
        if(!empty($comid)){
            $where['cid'] = ['eq',$comid];
        }
        if(!empty($id)){
            $where['id'] = ['eq',$id];
        }

        $where['end'] = ['egt',time()];
        $where['start'] = ['elt',time()];
        $where['types'] = ['eq',1];
        $where['check'] = ['eq',1];
        $where['del'] = ['eq',1];
        $where['state'] = ['eq',1];
        return $this->field($field)->where($where)->order('time desc')->find();
    }

    // 获取装修公司一个活动
    public function getCompanyFirstActivity($companyIds){
        $map = array(
            "cid" => array("IN", $companyIds),
            "start" => array("ELT", time()),
            "end" => array("EGT", time()),
            "check" => array("EQ", 1),
            "state" => array("EQ", 1),
            "types" => array("EQ", 1),
            "del" => array("EQ", 1),
        );

        $subSql = $this->where($map)
            ->field(["id", "cid", "title"])
            ->order("time desc")
            ->buildSql();

        return M()->table($subSql)->alias("t")
            ->group("t.cid")
            ->select();
    }

}