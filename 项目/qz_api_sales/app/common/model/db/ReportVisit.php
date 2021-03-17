<?php

namespace app\common\model\db;

use app\index\enum\CompanyCodeEnum;
use app\index\enum\VisitCodeEnum;
use think\Model;
use think\Db;

class ReportVisit extends Model
{
    protected $table = 'qz_report_visit';

    public function logListold()
    {
        return $this->hasMany('ReportLogTelcenterOrdercall', 'record_id', 'visit_id')->field('id,record_id,callSid as call_sid,channel');
    }

    public function logList()
    {
        return $this->hasMany('LogHollyReportTelcenter', 'record_id', 'visit_id')->field('id,record_id,call_sid,"holly" as channel');
    }

    public function opuser()
    {
        return $this->hasOne('Adminuser', 'id', 'uid')->bind(['user_id' => 'id', 'uname' => 'name']);
    }

    public function hetong(){
        return $this->hasOne('UserCompany','userid','user_id')->field('userid,FROM_UNIXTIME( contract_start, "%Y-%m-%d") AS allstart, FROM_UNIXTIME(contract_end, "%Y-%m-%d") AS allend');
    }

    public function user(){
        return $this->hasOne('User','id','user_id')->field('id,start,end');
    }

    public function reportCompany(){
        return $this->hasOne('ReportCompany','id','company_id')->bind('intention');
    }

    public function saveVisit($data)
    {
        return $this->insertGetId($data);
    }

    public function visitListCount($where)
    {
        return $this->alias('v')
            ->field('v.id')
            ->where($where)
            ->join('qz_adminuser u', 'u.id = v.uid')
            ->count();
    }

    public function visitList($where,$page,$pageCount)
    {
        return $this->alias('v')
            ->field('v.id visit_id,company_id,start_time visit_start_time,end_time visit_end_time,style visit_style,style visit_style_code,status qianyue_status,status,next_time visit_next_time,desc,u.name uname,v.created_at created_time,v.pre_money,v.retainage_time')
            ->where($where)
            ->join('qz_adminuser u', 'u.id = v.uid')
            ->withAttr('visit_style', function ($a) {
                return VisitCodeEnum::getVisitStyle($a);
            })
            ->withAttr('qianyue_status', function ($a) {
                return VisitCodeEnum::getVisitStatus($a);
            })
            ->with(['logList'])
            ->limit($page,$pageCount)
            ->order('v.created_at desc')
            ->select()->toArray();
    }

    /**
     * 获取公司最新的一条拜访记录
     * @Author   liuyupeng
     * @DateTime 2019-05-20T14:59:30+0800
     */
    public function getNewInfo($user_id){
        return $this->alias('v')
            ->join('report_company c', 'c.id = v.company_id')
            ->where('c.user_id', $user_id)
            ->order('v.retainage_time desc')->find();
    }

    /**
     * 获取客户维护列表数量
     *
     * @param array $map 查询条件
     * @param array $with 关联信息
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getAllListCount($map,$map1)
    {
        $buildsql = $this->alias('a')
            ->field('company_id')
            ->join('qz_report_company b','a.company_id = b.id','INNER')
            ->where($map)
            ->group('a.company_id')
            ->buildSql();
        $buildsql = ReportVisit::table($buildsql)->alias('c')
            ->join('qz_report_company e','c.company_id = e.id','INNER')
            ->join('qz_report_visit d','c.company_id = d.company_id','INNER')
            ->join('qz_quyu','qz_quyu.cid = e.cs','INNER')
            ->join('qz_area','qz_area.qz_areaid = e.qx','INNER')
            ->join('qz_report_user','qz_report_user.company_id = e.id and qz_report_user.created_at = (select max(created_at) from qz_report_user where company_id = c.company_id )','LEFT')
            ->join('qz_adminuser','qz_adminuser.id = d.uid','LEFT')
            ->order('d.updated_at desc')
            ->field('c.company_id,d.uid,d.status,d.start_time,d.end_time,d.created_at,d.next_time,d.next_time,d.style')
            ->buildSql();
        $buildsql = ReportVisit::table($buildsql)->alias('c')->group('c.company_id')->buildSql();
        return ReportVisit::table($buildsql)->alias('d')->where($map1)->count(1);

    }

    /**
     * 获取客户维护列表
     *
     * @param array $map 查询条件
     * @param array $with 关联信息
     * @param null $page 分页
     * @param null $pageSize 分页
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getAllList($field,$map,$map1, $page = null, $pageSize = null,$order)
    {
        $buildsql = $this->alias('a')
            ->field('company_id')
            ->join('qz_report_company b','a.company_id = b.id','INNER')
            ->where($map)
            ->group('a.company_id')
            ->buildSql();
        $buildsql =  ReportVisit::table($buildsql)->alias('c')
            ->field($field)
            ->join('qz_report_company e','c.company_id = e.id','INNER')
            ->join('qz_report_visit d','c.company_id = d.company_id','INNER')
            ->join('qz_quyu','qz_quyu.cid = e.cs','INNER')
            ->join('qz_area','qz_area.qz_areaid = e.qx','INNER')
            ->join('qz_report_user','qz_report_user.company_id = e.id and qz_report_user.created_at = (select max(created_at) from qz_report_user where company_id = e.id )','LEFT')
            ->join('qz_adminuser','qz_adminuser.id = d.uid','LEFT')
            ->order($order['order1'])
            ->buildSql();
        $buildsql = ReportVisit::table($buildsql)->alias('f')
            ->field('f.*,count(f.id) as contact_num')
            ->group('f.id')
            ->buildSql();
        return ReportVisit::table($buildsql)->alias('d')
            ->withAttr('start_time', function ($a) {
                return date("Y-m-d H:i", $a);
            })
            ->withAttr('end_time', function ($a) {
                return date("Y-m-d H:i", $a);
            })
            ->withAttr('next_time', function ($a) {
                return date("Y-m-d H:i", $a);
            })
            ->withAttr('retainage_time', function ($a) {
                return date("Y-m-d H:i:s", $a);
            })
            ->withAttr('created_at', function ($a) {
                return date("Y-m-d H:i:s", $a);
            })
            ->withAttr('status', function ($a) {
                return VisitCodeEnum::getVisitStatus($a);
            })
            ->withAttr('style', function ($a) {
                return VisitCodeEnum::getVisitStyle($a);
            })
            ->withAttr('intention', function ($a) {
                return CompanyCodeEnum::getCompanyIntention($a);
            })
            ->withAttr('customer_source', function ($a) {
                return CompanyCodeEnum::getCompanySource($a);
            })
            ->where($map1)
            ->with(['logList','user','hetong'])
            ->order($order['order2'])
            ->page($page,$pageSize)
            ->select();
    }
}
