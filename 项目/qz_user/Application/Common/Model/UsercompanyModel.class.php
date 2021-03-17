<?php
/**
 * 装修公司详细信息 qz_user_company
 */
namespace Common\Model;
use Think\Model;
class UsercompanyModel extends Model{
    protected $tableName ="user_company";
    /**
     * 添加装修公司详细信息
     */
    public function AddCompanyDetails($data){
        return M("user_company")->add($data);
    }

    /**
     * 编辑企业信息
     * @param  [type] $id   [企业ID]
     * @param  [type] $data [数据]
     * @return [type]       [description]
     */
    public function editUserCompany($id,$data)
    {
        $map = array(
            "userid" => array("EQ",$id)
        );
        return M("user_company")->where($map)->save($data);
    }

    //获取前30天数据
    public function getlastthirtyDays(){
        $firstday=date('Y-m-d',strtotime("-1 day"));
        $lastday=date('Y-m-d');
        return ['start'=>$firstday,'end'=>$lastday];
    }

    /**
     * 获取综合排行
     * @param string $condition
     * @param int $pagesize
     * @param int $pageRow
     * @return array
     */
    public function getShiliList($cs){
        $map["classid"] = array("EQ",3);

        if(!empty($cs)){
            $map["cs"] = array("EQ",$cs);
        }

        //获取上月开始结束时间
        $rand_time  = $this->getlastthirtyDays();
        $start_time = $rand_time["start"];
        $end_time = $rand_time["end"];

        $Db = M('user');
        $build = $Db->where($map)->alias("a")
            ->field("a.`id`,a.on,a.qc,a.pv,a.uv,a.cs,a.logo,a.uptime,a.info_time,a.case_count,q.team_count,q.comment_count,q.comment_score,q.fake,a.sales_count,a.activte_score,e.bm,q.id as comid,a.dz,q.img,a.register_time,e.cname as cityname,z.qz_area as area_name")
            ->join("INNER JOIN qz_user_company as q on q.userid = a.id")
            ->join("INNER JOIN qz_quyu as e on e.cid = a.cs")
            ->join("INNER JOIN qz_area as z on a.qx = z.qz_areaid")
            ->buildSql();

        //1*量房数+5*签单数+0.1*案例数+0.2*团队人数+0.2*好评数+0.5*问答回答数
        $buildSql1 = $Db->table($build)->alias('t')
            ->field('t.*, uc1.liangfang,uc1.haoping,uc1.qiandan,uc1.casesnum,uc1.answernum ,uc1.designnum,uc1.ping')
            ->join("left join qz_user_company_rank uc1 on uc1.comid = t.id and uc1.day between '".$start_time."' and  '".$end_time."'")
            ->order("day desc")
            ->buildSql();

        $buildSql2 =  $Db->table($buildSql1)->alias('t2')
            ->field('t2.*,sum(t2.liangfang) as liangfang_num,t2.haoping as haoping_num,t2.ping as ping_num,sum(t2.qiandan) as qiandan_num,t2.casesnum as cases_num,t2.answernum as answer_num')
            ->group('t2.id')
            ->buildSql();

        $result = $Db->table($buildSql2)->alias('t1')
            ->field('
            t1.*,
            (t1.liangfang_num+5*t1.qiandan_num+0.1*t1.cases_num+0.2*t1.designnum+0.2*t1.haoping_num+0.5*t1.answer_num) as order_fenshu,(t1.haoping_num/t1.ping_num) as haopinglv,
            case
            when t1.on = 2 and t1.fake=0 then 1
            when t1.on <> 2 and t1.fake=0  then 2
            else 3 end paixu
            ')
            ->order('paixu,t1.fake,order_fenshu desc,t1.register_time')
            ->select();
        return $result;
    }

    /**
     * 获取签单排行
     * @param $cs
     * @return mixed
     */
    public function getQiandanList($cs){

        $map["classid"] = array("EQ",3);

        if(!empty($cs)){
            $map["cs"] = array("EQ",$cs);
        }


        $Db = M('user');
        $build = $Db->where($map)->alias("a")
            ->field("a.`id`,a.on,a.qc,a.pv,a.uv,a.cs,a.logo,a.uptime,a.info_time,a.case_count,q.team_count,q.comment_count,q.comment_score,q.fake,a.sales_count,a.activte_score,e.bm,q.id as comid,a.dz,q.img,a.register_time,e.cname as cityname,z.qz_area as area_name")
            ->join("INNER JOIN qz_user_company as q on q.userid = a.id ")
            ->join("INNER JOIN qz_quyu as e on e.cid = a.cs")
            ->join("LEFT JOIN qz_area as z on a.qx = z.qz_areaid")
            ->buildSql();

        $buildSql1 = $Db->table($build)->alias('t')
            ->field('t.*,o.qiandan_addtime,o.xiaoqu')
            ->join("left join qz_orders o on o.qiandan_companyid = t.id and o.qiandan_status=1")
            ->order("o.qiandan_addtime desc")
            ->buildSql();
        // 先按照【签单时间排序，最新签单放最上面】再【店铺创建时间】
        $result = $Db->table($buildSql1)->alias('t1')
            ->group('t1.id')
            ->order('qiandan_addtime desc,t1.register_time')
            ->select();

        return $result;
    }

    /**
     * 获取量房排行
     * @param $cs
     * @return array
     */
    public function getLiangfangList($cs){
        $map["classid"] = array("EQ",3);

        if(!empty($cs)){
            $map["cs"] = array("EQ",$cs);
        }

        $Db = M('user');
        $buildSql = $Db->where($map)->alias("a")
            ->field("a.`id`,a.on,a.qc,a.pv,a.uv,a.cs,a.logo,a.uptime,a.info_time,a.case_count,q.team_count,q.comment_count,q.comment_score,q.fake,a.sales_count,a.activte_score,e.bm,q.id as comid,a.dz,q.team_num,q.img,a.register_time,e.cname as cityname,z.qz_area as area_name")
            ->join("INNER JOIN qz_user_company as q on q.userid = a.id")
            ->join("INNER JOIN qz_quyu as e on e.cid = a.cs")
            ->join("LEFT JOIN qz_area as z on a.qx = z.qz_areaid")
            ->buildSql();
        //先按照【量房时间排序，最新量房放最上面】再【店铺创建时间】
        $buildSql1 = $Db->table($buildSql)->alias('t')
            ->field('t.*,cr.time as liangfang_time,o.xiaoqu as liangfang_xiaoqu')
            ->join("left join qz_order_company_review cr on cr.comid = t.id and cr.status=1")
            ->join("left join qz_orders o on o.id = cr.orderid")
            ->order('cr.time desc')
            ->buildSql();

        $result = $Db->table($buildSql1)->alias('t1')
            ->group("t1.comid")
            ->order('liangfang_time desc,t1.register_time')
            ->select();

        return $result;
    }

    /**
     * 获取会员信息
     *
     * @param $company_id
     * @return mixed
     */
    public function getUserCompany($company_id)
    {
        $map['userid'] = array('EQ',$company_id);
        return M('user_company')->field('userid,viptype,chengli,text,mianji,lxs,lx')->where($map)->find();
    }
}