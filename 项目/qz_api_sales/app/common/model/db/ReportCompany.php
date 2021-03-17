<?php

namespace app\common\model\db;

use app\index\enum\VisitCodeEnum;
use app\index\enum\CompanyCodeEnum;
use think\db\Where;
use think\Model;

class ReportCompany extends Model
{
    protected $table = 'qz_report_company';

    public function opuser()
    {
        return $this->hasOne('Adminuser', 'id', 'uid')->bind(['admin_id' => 'id', 'admin_name' => 'name']);
    }

    public function contacts(){
        return $this->hasMany('Contacts','company_id','id');
    }

    public function city(){
        return $this->hasOne('Quyu','cid','cs')->bind(['cname' => 'cname']);
    }

    public function area(){
        return $this->hasOne('Area','qz_areaid','qx')->bind(['area' => 'qz_area']);
    }

    public function areaname(){
        return $this->hasOne('Area','qz_areaid','qx')->bind(['aname' => 'qz_area']);
    }

    public function insertReportCompany($data)
    {
        return $this->insertGetId($data);
    }
    public function saveReportCompany($data)
    {
        return $this->saveAll($data);
    }

    public function logList()
    {
        return $this->hasMany('ReportLogTelcenterOrdercall', 'record_id', 'visit_id')->field('id,record_id,callSid as call_sid');
    }

    public function user(){
        return $this->hasOne('User','id','user_id')->field('id,start,end');
    }

    public function hetong(){
        return $this->hasOne('UserCompany','userid','user_id')->field('userid,FROM_UNIXTIME( contract_start, "%Y-%m-%d") AS allstart, FROM_UNIXTIME(contract_end, "%Y-%m-%d") AS allend');
    }

    public function userCompany(){
        return $this->hasOne('User','id','user_id')->bind('qc');
    }

    public function visit(){
        return $this->hasMany('ReportVisit','company_id','id');
    }

    /**
     * 获取列表
     *
     * @param array $map 查询条件
     * @param array $with 关联信息
     * @param null $page 分页
     * @param null $pageSize 分页
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getList($map, $with = [], $page = null, $pageSize = null)
    {
        return $this->with($with)->where($map)->page($page, $pageSize)->select();
    }

    /**
     * 获取客户维护列表数量
     *
     * @param array $map 查询条件
     * @param array $with 关联信息
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getAllListCount($map)
    {
        return $this
            ->join('qz_quyu','qz_quyu.cid = qz_report_company.cs','INNER')
            ->join('qz_area','qz_area.qz_areaid = qz_report_company.qx','INNER')
            ->join('qz_report_user','qz_report_user.company_id = qz_report_company.id and qz_report_user.created_at = (select max(created_at) from qz_report_user where company_id = qz_report_company.id )','LEFT')
            ->join('qz_report_visit','qz_report_visit.company_id = qz_report_company.id and qz_report_visit.created_at = (select max(created_at) from qz_report_visit where company_id = qz_report_company.id )','LEFT')
            ->join('qz_adminuser','qz_adminuser.id = qz_report_visit.uid','INNER')
            ->where($map)
            ->count();
    }

    /**
     * 获取详情
     *
     * @param array $map 查询条件
     * @param array $with 关联信息
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getInfo($map, $with = [])
    {
        return $this->with($with)->where($map)->find();
    }
    /**
     * 获取相关会员数量
     *
     * @param array $map 查询条件
     * @param array $with 关联信息
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getCount($map, $with = [])
    {
        return $this->with($with)->where($map)->count();
    }
}
