<?php
/**
 * 注册用户表 user
 */

namespace Common\Model;

use Think\Model;
use Common\Model\CompanyModel;

class DesignerModel extends Model
{
    protected $tableName = "user";
    /**
     * 获取某城市的设计师列表
     * @param  [type] $cs [所在城市]
     * @return [type] [description]
     */
    /*public function getDesignerList($cs ='',$pageIndex = 0,$pageCount = 10)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $map = array(
                "a.cs"=>array("EQ",$cs)
                     );
        return M("cases")->where($map)->alias("a")
                         ->join("LEFT JOIN qz_team as b on a.userid = b.userid")
                         ->join("LEFT JOIN qz_user_des as f on f.userid = a.userid")
                         ->join("INNER JOIN qz_user as c on c.id = a.userid")
                         ->join("LEFT JOIN qz_user_company as g on g.userid = a.uid")
                         ->join("LEFT JOIN qz_user as h on h.id = b.comid")
                         ->field("count(a.id) as count,a.userid,a.uid as cid,c.name,b.zw,c.logo,f.text as jianjie,f.jobtime,f.fengge")
                         ->limit($pageIndex.",".$pageCount)
                         ->order("h.on desc,g.fake,count desc")->group("a.userid")->select();
    }*/
    /**
     * 获取某城市的设计师列表---修改版
     * @param  [type] $cs [所在城市]
     * @return [type] [description]
     */
    public function getDesignerList($cs = '', $map, $pageIndex = 0, $pageCount = 10, $order='')
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $mapcs = array(
            "a.cs" => array("EQ", "$cs"),
            "a.classid" => array("EQ", '2')
        );
        //查询当前城市的所有设计师
        $buildSql = M("user")->where($mapcs)->alias("a")
            ->join("INNER JOIN qz_team as m on m.userid = a.id and zt = 2 and xs = 1")
            ->field("a.id,a.name,a.logo,m.comid,m.zw,a.cs")
            ->buildSql();
        //关联案例表，查询对应设计师的案例时间，并且按照id desc排序
        $buildSql = M("user")->table($buildSql)->alias("t")
            ->join("LEFT JOIN qz_cases as c on c.userid = t.id and c.cs = t.cs and c.isdelete = 1 and c.status=2")
            ->field("t.id,t.comid,t.name,t.logo,t.zw,c.time")
            ->order("c.id desc")
            ->buildSql();

        //获取最新的案例时间，并且统计所有的案例数目
        $buildSql = M("user")->table($buildSql)->alias("t")
            ->field("t.id,t.comid,t.name,t.logo,t.zw,max(t.time) as time ,count(t.id) as count")
            ->group("t.id")
            ->buildSql();
        //判断最新案例数，如果在一个月之内则为1，否则为0
        $buildSql = M("user")->table($buildSql)->alias("t")
            ->field("t.id,t.comid,t.name,t.logo,t.zw,t.count,if(t.time >= " . strtotime("-1 month") . ",'1','0') as time")
            ->order("count desc,t.id desc")
            ->buildSql();
        //
        $defaultOrder = $order ?: 'u.on desc, fake,t1.time desc,count desc,t1.id desc';
        return M("user")->table($buildSql)->alias("t1")
            ->where($map)
            ->join("INNER JOIN qz_user as u on u.id = t1.comid")
            ->join("INNER JOIN qz_user_company as uc on uc.userid = u.id ")
            ->join("INNER JOIN qz_user_des as ud on ud.userid = t1.id")
            ->field("t1.id as userid,t1.comid,t1.zw,count,u.pv,u.qc,t1.name,t1.logo,uc.fake,if(ud.fengge,ud.fengge,''),ud.text as jianjie,ud.cost,ud.jobtime,if(ud.lingyu,ud.lingyu,''),ud.linian,u.on,t1.time")
            ->limit($pageIndex . "," . $pageCount)
            ->order($defaultOrder)
            ->select();

    }

    /**
     * 获取某城市的设计师列表
     * @param string $cs
     * @param $map
     * @param int $pageIndex
     * @param int $pageCount
     * @param string $order
     * @return mixed
     */
    public function getDesignerList2($cs = '', $map, $pageIndex = 0, $pageCount = 10, $order='')
    {
        $map['a.cs'] = ['eq',"$cs"];
        $map['a.classid'] = ['eq',2];
        $map['uc.is_show'] = ['eq',1];
        $defaultOrder = $order ?: 'a.on desc, fake asc, count desc, a.id desc';
        $ret = M('user')->alias('a')
            ->where($map)
            ->join("INNER JOIN qz_team as m on m.userid = a.id and m.zt = 2 and m.xs = 1")
            ->join("INNER JOIN qz_user_company as uc on uc.userid = m.comid")
            ->join("INNER JOIN qz_user_des as ud on ud.userid = a.id")
            ->join("left JOIN qz_cases as ca on ca.userid = a.id and ca.uid= m.comid and ca.isdelete = 1 and ca.status in (1,2) and ca.classid<3")
            ->field("a.id as userid,m.comid,m.zw,count(ca.id) count,a.pv,a.qc,a.jc, a.name,a.logo, uc.fake,ud.fengge, ud.text as jianjie,ud.cost, ud.jobtime,ud.lingyu,ud.linian,a.on")
            ->group('a.id')
            ->limit($pageIndex . "," . $pageCount)
            ->order($defaultOrder)
            ->select();
        return $ret;
    }

    /**
     * 获取数量
     * @param string $cs
     * @param $map
     * @return mixed
     */
    public function getDesignerListCount2($cs='',$map)
    {
        $map['a.cs'] = ['eq',"$cs"];
        $map['a.classid'] = ['eq',2];
        $map['uc.is_show'] = ['eq',1];
        $buildSql = M('user')->alias('a')
            ->where($map)
            ->join("INNER JOIN qz_team as m on m.userid = a.id and m.zt = 2 and m.xs = 1")
            ->join("INNER JOIN qz_user_company as uc on uc.userid = m.comid")
            ->join("INNER JOIN qz_user_des as ud on ud.userid = a.id")
            ->join("left JOIN qz_cases as ca on ca.userid = a.id and ca.uid= m.comid and ca.isdelete = 1 and ca.status=2 and ca.classid<3")
            ->field("a.id as userid,m.comid,m.zw,count(ca.id) count,a.pv,a.qc,a.jc, a.name,a.logo, uc.fake,if(ud.fengge, ud.fengge, '') fengge, ud.text as jianjie,ud.cost, ud.jobtime, if(ud.lingyu, ud.lingyu, '') lingyu,ud.linian,a.on")
            ->group('a.id')
            ->buildSql();
        $ret = $this->table($buildSql)->alias('t1')->count();
        return $ret;
    }


    /**
     * 获取数量
     * @param $cs
     * @param $map1
     * @param $map2
     * @return int
     */
    public function getDesignerListCount3($cs, $map1, $map2)
    {
        $map1['a.cs'] = ['eq',"$cs"];
        $map1['a.classid'] = ['eq',2];
        $map1['uc.is_show'] = ['eq',1];

        $buildSql1 = M('user')->alias('a')
            ->where($map1)
            ->join("INNER JOIN qz_team as m on m.userid = a.id and m.zt = 2 and m.xs = 1")
            ->join("INNER JOIN qz_user_company as uc on uc.userid = m.comid")
            ->join("INNER JOIN qz_user_des as ud on ud.userid = a.id")
            ->join("left JOIN qz_cases as ca on ca.userid = a.id and ca.uid= m.comid and ca.isdelete = 1 and ca.status=2 and ca.classid<3")
            ->field("a.id,m.comid")
            ->group('a.id')
            ->buildSql();

        $buildSql1 = M('user')->table($buildSql1)->alias('a1')
            ->join("LEFT JOIN qz_user_company_employee ce on ce.company_id = a1.comid")
            ->field('a1.*')
            ->where("ce.id is null")
            ->group('a1.id')
            ->buildSql();


        $map2['ce.position'] = array('in', [2,3,4]);
        $map2['ce.state'] = array('eq', 1);
        $map2['u.cs'] = array('eq', "$cs");

        $buildSql2 = M('user_company_employee')->alias('ce')
            ->where($map2)
            ->join('INNER JOIN qz_user_company as uc on uc.userid = ce.company_id')
            ->join('INNER JOIN qz_user as u on u.id = ce.company_id')
            ->join('LEFT JOIN qz_user_des as  ud on ud.userid = ce.id and ud.company_id = ce.company_id')
            ->join("left JOIN qz_cases as ca on ca.userid = ce.id and ca.uid= ce.company_id and ca.isdelete = 1 and ca.status=2 and ca.classid<3")
            ->field('ce.id,ce.company_id comid')
            ->group('ce.id')
            ->buildSql();

        $buildSql = M()->table($buildSql1)->alias('t')
            ->union($buildSql2)
            ->buildSql();

        $count = M()->table($buildSql)->alias('t2')->count();
        return $count ? $count : 0;

    }


    // 获取设计师列表
    public function getDesignerList3($cs, $map1, $map2, $sortValue, $pageIndex, $pageCount)
    {

        $map1['a.cs'] = ['eq',"$cs"];
        $map1['a.classid'] = ['eq',2];
        $map1['uc.is_show'] = ['eq',1];

        $buildSql1 = M('user')->alias('a')
            ->where($map1)
            ->join("INNER JOIN qz_team as m on m.userid = a.id and m.zt = 2 and m.xs = 1")
            ->join("INNER JOIN qz_user_company as uc on uc.userid = m.comid")
            ->join("INNER JOIN qz_user_des as ud on ud.userid = a.id")
            ->join("left JOIN qz_cases as ca on ca.userid = a.id and ca.uid= m.comid and ca.isdelete = 1 and ca.`status`=2 and ca.classid<3")
            ->field("a.id as userid,m.comid,m.zw,count(ca.id) count,a.pv,a.qc,a.jc, a.`name`,a.logo, uc.fake,ud.fengge, ud.text as jianjie,ud.cost,
            ud.jobtime,ud.lingyu,ud.linian,a.`on`,1 as cooperate_mode,(count(ca.id) + a.pv) as zonghe")
            ->group('a.id')
            ->buildSql();

        $buildSql1 = M('user')->table($buildSql1)->alias('a1')
            ->join("LEFT JOIN qz_user_company_employee ce on ce.company_id = a1.comid")
            ->field('a1.*')
            ->where("ce.id is null")
            ->group('a1.userid')
            ->buildSql();

        // $map2['ce.position'] = array('in', [2,3,4]);
        $map2['ce.state'] = array('eq', 1);
        $map2['u.cs'] = array('eq', "$cs");

        $buildSql2 = M('user_company_employee')->alias('ce')
            ->where($map2)
            ->join('INNER JOIN qz_user_company as uc on uc.userid = ce.company_id')
            ->join('INNER JOIN qz_user as u on u.id = ce.company_id')
            ->join('LEFT JOIN qz_user_des as ud on ud.userid = ce.id and ud.company_id = ce.company_id')
            ->join("left JOIN qz_cases as ca on ca.userid = ce.id and ca.uid= ce.company_id and ca.isdelete = 1 and ca.status=2 and ca.classid<3")
            ->field('ce.id as userid,ce.company_id as comid,
            case when ce.position = 2 then "设计师" when ce.position = 3 then "首席设计师" when ce.position = 4 then "设计总监" else "" end as zw,
             count(ca.id) count,ud.popularity as pv,"" as qc,"" as jc,ce.nick_name as `name`,ce.logo,uc.fake,ud.fengge, ud.text as jianjie,ud.cost,
             ud.jobtime,ud.lingyu,ud.linian,"" as `on`,2 as cooperate_mode,(count(ca.id) + ud.popularity) as zonghe')
            ->group('ce.id')
            ->buildSql();

        $buildSql = M()->table($buildSql1)->alias('t')
            ->union($buildSql2)
            ->buildSql();


        //$sortValue=1,作品量,sort=2,人气值
        if ($sortValue == 1) {
            $order = 't2.count desc,convert(t2.pv,UNSIGNED) desc,t2.userid desc';
        } elseif ($sortValue == 2) {
            $order = 'convert(t2.pv,UNSIGNED) desc,t2.count desc,t2.userid desc';
        } else {
            $order = 't2.zonghe desc,convert(t2.pv,UNSIGNED) desc,t2.count desc,t2.userid desc';
        }


        $list = M()->table($buildSql)->alias('t2')
            ->field('t2.*')
            ->order($order)
            ->limit($pageIndex, $pageCount)
            ->select();
        return $list ? $list : [];

    }


    /**
     * 获取某城市的设计师榜
     * @param  [type] $cs [所在城市]
     * @return [type] [description]
     */
    public function getDesignerListFive($cs = '')
    {
        $mapcs = array(
            "a.cs" => array("EQ", "$cs"),
            "a.classid" => array("EQ", '2')
        );
        //查询出该城市所有设计师
        $buildSql = M("user")->where($mapcs)->alias("a")
            ->join("INNER JOIN qz_team as m on m.userid = a.id")
            ->field("a.id,a.name,m.comid,a.cs")
            ->buildSql();
        //关联案例，查询案例数总数
        $buildSql = M("user")->table($buildSql)->alias("t")
            ->join("INNER JOIN qz_cases as c on c.userid = t.id and c.cs = t.cs and c.isdelete = 1")
            ->field("t.id,t.comid,t.name,count(t.id) as count")
            ->group("c.userid")
            ->buildSql();

        return M("user")->table($buildSql)->alias("t1")
            ->join("INNER JOIN qz_user as u on u.id = t1.comid")
            ->join("INNER JOIN qz_user_company as uc on uc.userid = u.id ")
            ->join("INNER JOIN qz_user_des as ud on ud.userid = t1.id")
            ->field("t1.id as userid,t1.comid,t1.name,count,uc.fake,u.on")
            ->limit('0' . "," . '5')
            ->order("u.on DESC,fake,count DESC")
            ->select();
    }

    /**
     * 获取某城市的设计师列数量
     * @param  [type] $cs [所在城市]
     * @return [type]     [description]
     */
    public function getDesignerListCount($cs, $map)
    {
        $mapcs = array(
            "a.cs" => array("EQ", "$cs"),
            "a.classid" => array("EQ", '2')
        );

        //查询当前城市的所有设计师
        $buildSql = M("user")->where($mapcs)->alias("a")
            ->join("INNER JOIN qz_team as m on m.userid = a.id and zt = 2 and xs = 1")
            ->field("a.id,a.name,a.logo,m.comid,m.zw,a.cs")
            ->buildSql();

        //
        return M("user")->table($buildSql)->alias("t1")
            ->where($map)
            ->join("INNER JOIN qz_user as u on u.id = t1.comid")
            ->join("INNER JOIN qz_user_company as uc on uc.userid = u.id ")
            ->join("INNER JOIN qz_user_des as ud on ud.userid = t1.id")
            ->field("t1.id as userid,t1.comid,t1.zw,count,u.qc,t1.name,t1.logo,uc.fake,ud.fengge,ud.text as jianjie,ud.jobtime,u.on")
            ->count();


        /*$buildSql = M("user")->where($mapcs)->alias("a")
                        ->join("INNER JOIN qz_team as m on m.userid = a.id")
                        ->field("a.id,m.comid,a.cs,m.zw")
                        ->buildSql();

        $buildSql = M("user")->table($buildSql)->alias("t")
                        ->join("INNER JOIN qz_cases as c on c.userid = t.id and c.cs = t.cs")
                        ->field("t.id,t.comid,count(t.id) as count,t.zw")
                        ->group("c.userid")
                        ->buildSql();

        return M("user")->table($buildSql)->alias("t1")
                        ->where($map)
                        ->join("INNER JOIN qz_user as u on u.id = t1.comid")
                        ->join("INNER JOIN qz_user_company as uc on uc.userid = u.id ")
                        ->join("INNER JOIN qz_user_des as ud on ud.userid = t1.id")
                        ->field("t1.id as userid,t1.comid,count,t1.zw,ud.fengge,ud.jobtime")
                        ->count();*/
    }

    /**
     * 获取设计师的最新3个案例信息
     * @param string $ids [description]
     * @return [type]      [description]
     */
    public function getDesinerCase($ids = "")
    {
        $map = array(
            "isdelete" => array("EQ", 1),
            "userid" => array("IN", $ids),
            //因为考虑到数据量的问题,添加一个最新案例的最早时间
//            "time" => array("EGT", strtotime("-1 month"))
        );
        //1.查询出用户的案例编号
        $buildSql = M("cases")->where($map)
            ->buildSql();
        //2.条件子查询,查询每个设计师的最新3个案例
        $map["_string"] = "a.userid = b.userid AND a.id < b.id ";
        $sql = M("cases")->where($map)->alias("b")
            ->field("count(id)")
            ->buildSql();
        $map = array(
            $sql => array("LT", 3)
        );
        $buildSql = M("cases")->table($buildSql)->alias("a")
            ->field("a.id,a.userid,a.title,a.mianji,a.fengge")
            ->where($map)
            ->buildSql();

        //3.查询出案例的封面图片
        $buildSql = M("case_img")->alias("m")
            ->join("INNER JOIN $buildSql as t on t.id = m.caseid")
            ->join("INNER JOIN qz_fengge as fg on fg.id = t.fengge")
            ->order("m.caseid desc,m.img_on DESC")
            ->field("m.*,t.userid,t.title,t.mianji,fg.name fengge")
            ->buildSql();
        $ret = M("case_img")->table($buildSql)->alias("t1")
            ->group("caseid")
            ->select();
        return $ret;
    }

    /**
     * 调用会员公司中人气值最高的4位设计师
     * @param $cs
     * @return mixed
     */
    public function getCityDesigner($cs)
    {
        //
        //若站点没有会员公司，则从假公司设计师调用人气值最高四位
        $mapcs = array(
            "a.cs" => array("EQ", "$cs"),
            "a.classid" => array("EQ", '2')
        );
        //查询当前城市的所有设计师
        $buildSql = M("user")->where($mapcs)->alias("a")
            ->join("INNER JOIN qz_team as m on m.userid = a.id and zt = 2 and xs = 1")
            ->field("a.id,a.name,a.logo,a.pv,m.comid,m.zw,a.cs")
            ->buildSql();
        $ret1 = M("user")->table($buildSql)->alias("t1")
            ->join("INNER JOIN qz_user as u on u.id = t1.comid")
            ->join("INNER JOIN qz_user_company as uc on uc.userid = u.id ")
            ->join("INNER JOIN qz_user_des as ud on ud.userid = t1.id")
            ->join("LEFT JOIN qz_quyu as qy on t1.cs = qy.cid")
            ->join("LEFT JOIN qz_user_company_employee ce on ce.company_id = uc.userid")
            ->where("ce.id is null")
            ->field("t1.id as userid,t1.comid,if(t1.zw<>'0',t1.zw,'') zw,t1.pv,u.qc,u.jc,if(t1.`name`<>'0',t1.`name`,'') 'name',t1.logo,uc.fake,1 as cooperate_mode,ud.fengge,ud.text as jianjie,ud.jobtime,ud.lingyu,ud.linian,u.`on`,qy.cname")
            ->buildSql();

        $map2 = [
            'u.cs' => array('EQ', $cs),
            'ce.position' => array('in', [2,3,4]),
            'ce.state' => array('eq', 1)
        ];
        $buildSql2 = M('user_company_employee')->alias('ce')
            ->where($map2)
            ->field('ce.id,ce.nick_name as "name",ce.logo,ce.company_id as comid,
            case when ce.position = 2 then "设计师" when ce.position = 3 then "首席设计师" when ce.position = 4 then "设计总监" else "" end as zw,u.cs,u.`on`,u.jc,u.qc')
            ->join("INNER JOIN qz_user u on u.id = ce.company_id")
            ->buildSql();

        $ret2 = M()->table($buildSql2)->alias('z')
            ->join("INNER JOIN qz_user_company as uc on uc.userid = z.comid")
            ->join("INNER JOIN qz_user_des as ud on ud.userid = z.id and ud.company_id = z.comid")
            ->join("LEFT JOIN qz_quyu as qy on qy.cid = z.cs")
            ->field("z.id as userid,z.comid,if(z.zw<>'0',z.zw,'') as zw,ud.popularity as pv,z.qc,z.jc,if(z.`name`<>'0',z.`name`,'') as 'name',z.logo,uc.fake,2 as cooperate_mode,ud.fengge,ud.text as jianjie,ud.jobtime,ud.lingyu,ud.linian,z.`on`,qy.cname")
            ->buildSql();

        $buildSql3 = M()->table($ret1)->alias('t2')
            ->union($ret2)
            ->buildSql();


        $ret = M()->table($buildSql3)->alias('t3')
            ->field('t3.*')
            ->order('t3.fake,convert(t3.pv, UNSIGNED) desc,t3.userid desc')
            ->limit(0,4)
            ->select();


        return $ret;
    }


    /**
     * 获取最新的通过审核的案例
     * @param $cs
     * @return mixed
     */
    public function getTopCaseDesigner($cs)
    {
        $mapcs = array(
            "u.cs" => array("EQ", $cs),
            "u.classid" => array("EQ", '2'),
            "ss.status" => array("EQ", '2'),
            "ss.classid" => array("LT", '3'),
        );

        //查询当前城市案例数最多的设计师
        $ret = M("user")->where($mapcs)->alias("u")
            ->join("INNER JOIN qz_team as m on m.userid = u.id and m.zt = 2 and m.xs = 1")
            ->join("left join qz_cases ss on u.id = ss.userid and ss.cs = u.cs and ss.uid=m.comid and ss.isdelete = 1")
            ->field("u.id,u.name,count(ss.id) count")
            ->group('u.id')
            ->order('count desc,u.id desc')
            ->limit("0,4")
            ->select();
        $caseMap = [
            "ca.status" => array("EQ", '2'),
            "ca.classid" => array("LT", '3'),
            "ca.cs" => array("EQ", $cs),
            "ca.isdelete" => array("EQ", 1),
        ];
        foreach ($ret as $key => $value) {
            $caseMap["ca.userid"] = [
                "EQ", $value['id'],
            ];
            $caseMap['ca.status'] = ['eq', '2'];
            $caseMap['ca.classid'] = ["LT", '3'];
            $buildSql = M('cases')
                ->alias('ca')
                ->where($caseMap)
                ->field("ca.id,substring_index(ca.zaojia, '-', 1) zaojia,ca.title,ca.time")
                ->order('ca.id desc')
                ->limit(1)
                ->buildSql();
            $buildSql = $this->table($buildSql)->alias('t1')
                ->join("left join qz_case_img ci on ci.caseid=t1.id")
                ->field('t1.*,ci.img,ci.img_path,ci.img_host')
//            ->group('t1.caseid')
                ->order('ci.img_on desc,ci.px asc')
                ->buildSql();
            $case = $this->table($buildSql)->alias('t1')
                ->field('t1.*')
                ->order('t1.time desc')
                ->group('t1.id')
                ->find();
            //最多显示6个中文，超出部分以“...”做结尾
//            if (mb_strlen($case['title']) > 6) {
//                $case['title'] = mb_substr($case['title'], 0, 6) . '...';
//            }
//            //最多显示3个中文，超出部分以“...”做结尾
//            if (mb_strlen($value['name']) > 3) {
//                $ret[$key]['name'] = mb_substr($value['name'], 0, 3) . '...';
//            }
            if ($case['zaojia'] > 100) {
                $case['zaojia'] = '100万';
            } elseif (empty($case['zaojia'])) {
                $case['zaojia'] = '面议';
            } else {
                $case['zaojia'] = $case['zaojia'] . '万';
            }
            $ret[$key]['case'] = $case;
//            $caseImgMap['ci.caseid'] = ['eq', $case['id']];
//            $caseImgMap['ci.status'] = ['eq', 2];
//            $ret[$key]['case_img'] = M('case_img')->alias('ci')
//                ->where($caseImgMap)
//                ->field('ci.img_host,ci.img_path,ci.img')
//                ->order('ci.img_on DESC,ci.px asc')
//                ->limit(1)
//                ->find();
        }
        return $ret;
    }

    //获取最新预约
    public function getLastAppointment($cs, $count)
    {
        $key = 'Cache:DesignersInfo:LastAppointment' . $cs;
        $ret = S($key);
        if (empty($ret)) {
            $orderInfo = $this->getOrderInfo($cs, $count);
            $orderName = array_column($orderInfo, 'name');
            $orderXiaoQu = array_column($orderInfo, 'xiaoqu');
            $designerInfo = $this->getVipDesigner($cs, $count);
            for ($i = 0; $i < $count; $i++) {
                $ret[$i]['name'] = $orderName[array_rand($orderName)];
                $ret[$i]['xiaoqu'] = $orderXiaoQu[array_rand($orderXiaoQu)];
                $ret[$i]['designer'] = $designerInfo[array_rand($designerInfo)];
                $ret[$i]['date'] = date('m.d');
            }
            S($key, $ret, 60 * 60 * 24);
        }
        return $ret;
    }

    //业主、小区：该站点最新的10条状态为“分单”、“赠单”状态的订单数据中业主小区名，若站点数据不足则调取全站最新订单数据补充
    private function getOrderInfo($cs, $count)
    {
        $mapBase = [
            'on' => ['eq', 4],
            'type_fw' => ['in', '1,2'],
            'xiaoqu' => ['neq', ''],
            'name' => ['neq', ''],
        ];
        $mapCs = $mapBase;
        $mapCs['cs'] = ['eq', "$cs"];
        $ret1 = M('orders')->where($mapCs)->field('DISTINCT xiaoqu,name')
            ->order('time desc')->limit($count)->select();
        //剩余数量
        $leftCount = (int)($count - count($ret1));
        $ret2 = [];
        if ($leftCount > 0) {
            //获取全站
            $ret2 = M('orders')->where($mapBase)->field('DISTINCT xiaoqu,name')
                ->order('time desc')->limit($leftCount)->select();
        }
        $ret = array_merge($ret1, $ret2);
        return $ret;
    }

    //设计师：调取站点会员公司的设计师与业主、小区随机匹配，若会员的设计师数据不足则以假会员设计师数据填充
    public function getVipDesigner($cs, $count)
    {
        $mapBase = [
            "a.cs" => array("EQ", "$cs"),
            "a.classid" => array("EQ", '2')
        ];
        $ret = M('user')->alias('a')
            ->join("INNER JOIN qz_team as m on m.userid = a.id and zt = 2 and xs = 1")
            ->where($mapBase)
            ->limit($count)
            ->order('a.on desc')
            ->getField('a.id, a.name as designer_name');
        return $ret;
    }

//        设计师通过审核的最新上传的三个作品
    public function getLastDesigner($userId,$comId, $count)
    {
        $mapCase = [
            "c.userid" => array("EQ", $userId),
            "c.uid" => array("EQ", $comId),
            "c.status" => array("EQ", 2),
            "c.isdelete" => array("EQ", '1'),
            "c.classid" => array("LT", '3'),
        ];
        $buildSql = M('cases')
            ->alias('c')
            ->join("INNER JOIN qz_fengge as fg on fg.id = c.fengge")
            ->where($mapCase)
            ->field('c.id, c.time,c.title,c.mianji,fg.name fengge')
            ->order('time desc,id desc')
            ->limit($count)
            ->buildSql();
        $buildSql = $this->table($buildSql)->alias('t1')
            ->join("left join qz_case_img ci on ci.caseid=t1.id")
            ->field('t1.*,ci.img,ci.img_path,ci.img_host')
//            ->group('t1.caseid')
            ->order('ci.img_on desc,ci.px asc')
            ->buildSql();
        $case = $this->table($buildSql)->alias('t1')
            ->field('t1.*')
            ->order('t1.time desc')
            ->group('t1.id')
            ->select();
        return $case;
    }
    public function getCaseDesigner($cityCode = [],$row)
    {
        $res = [];
        $map = [
            'c.on' => array('EQ',1),
            'c.userid'=> array('GT',0),
            'c.isdelete' => array('EQ',1),
            'c.classid' => array('EQ',1),
            'c.status' => array('EQ',2)
        ];

        if(!empty($cityCode)){
            $submap = $map;
            $submap['co.fake'] = ['EQ',0];
            $submap['co.is_show'] = ['EQ',1];
            $submap['t.zt'] = ['EQ',2];
            $submap['c.cs'] = array('in',$cityCode);
            
            $sub = M('cases')->alias('c')
                    ->join('INNER JOIN qz_team t ON c.userid = t.userid')
                    ->join('INNER JOIN qz_user_company co ON t.comid = co.id')
                    ->join('LEFT JOIN qz_user_company_employee ce on ce.company_id = t.comid')
                    ->field('DISTINCT c.userid,1 as cooperate_mode')
                    ->where("ce.id is null")
                    ->where($submap)
                    ->where('c.zaojia = 25 OR CONVERT ( c.mianji, SIGNED ) BETWEEN 190 AND 10000')
                    ->order('c.id DESC')
                    ->buildSql();

                    
            $res = M()->table($sub)->alias('c')
                    ->join('INNER JOIN qz_user u ON u.id = c.userid')
                    ->join('INNER JOIN qz_quyu qy ON u.cs = qy.cid')
                    ->field('u.id,u.name,u.logo,CONVERT ( u.pv, SIGNED ) as pv,u.cs,qy.bm,c.cooperate_mode')
                    ->where(['u.cs'=>['EQ',$cityCode[0]]])
                    ->order('pv DESC')
                    ->buildSql();


            // 新签返会员？
            $map2 =  $map;
            $map2['ce.position'] = array("in", array(2,3,4));
            $map2['ce.state'] = array('eq', 1);
            $map2['uc.is_show'] = array('eq', 1);
            $map2['c.cs'] = array('in', $cityCode);

            $buildSql1 = M('cases')->alias('c')
                ->join('INner JOIN qz_user_company_employee ce on ce.id = c.userid and ce.company_id = c.uid')
                ->join('INNER JOIN qz_user_company uc on uc.userid = ce.company_id')
                ->field('DISTINCT c.userid,2 AS cooperate_mode,ce.nick_name as "name",ce.logo,ce.company_id')
                ->where($map2)
                ->where('c.zaojia = 25 OR CONVERT ( c.mianji, SIGNED ) BETWEEN 190 AND 10000')
                ->order('c.id DESC')
                ->buildSql();

            $buildSql2 = M()->table($buildSql1)->alias('c')
                ->join('INNER JOIN qz_user_des ud ON ud.userid = c.userid and ud.company_id = c.company_id')
                ->join('INNER JOIN qz_user u on u.id = c.company_id')
                ->join('INNER JOIN qz_quyu qy ON u.cs = qy.cid')
                ->field('c.userid as id,c.`name`,c.logo,CONVERT ( ud.popularity, SIGNED ) as pv,u.cs,qy.bm,c.cooperate_mode')
                ->where(['u.cs'=>['EQ',$cityCode[0]]])
                ->order('CONVERT ( ud.popularity, SIGNED ) DESC')
                ->buildSql();

            $buildSql3 = M()->table($res)->alias('t')
                ->union($buildSql2)
                ->buildSql();

            $res = M()->table($buildSql3)->alias('t1')
                ->limit($row)
                ->order('CONVERT ( t1.pv, SIGNED ) DESC')
                ->select();


            $no_ids = array_column($res,'id');
            $attres = [];
            if($row > count($res))
            {
                $no_ids = empty($no_ids) ? [0]:$no_ids;
                $submap['c.userid'] = array('NOT IN',$no_ids);
                $attsub = M('cases')->alias('c')
                    ->join('INNER JOIN qz_team t ON c.userid = t.userid')
                    ->join('INNER JOIN qz_user_company co ON t.comid = co.id')
                    ->join('LEFT JOIN qz_user_company_employee ce on ce.company_id = t.comid')
                    ->field('DISTINCT c.userid,1 as cooperate_mode')
                    ->where("ce.id is null")
                    ->where($submap)
                    ->buildSql();

                $attres = M()->table($attsub)->alias('c')
                    ->join('INNER JOIN qz_user u ON u.id = c.userid')
                    ->join('INNER JOIN qz_quyu qy ON u.cs = qy.cid')
                    ->field('u.id,u.name,u.logo,CONVERT ( u.pv, SIGNED ) as pv,u.cs,qy.bm,c.cooperate_mode')
                    ->where(['u.cs'=>['EQ',$cityCode[0]]])
                    ->buildSql();

                // 新签返会员？
                $map2 =  $map;
                $map2['ce.position'] = array("in", array(2,3,4));
                $map2['ce.state'] = array('eq', 1);
                $map2['ce.id'] = array('not in',$no_ids);

                $buildSql1 = M('cases')->alias('c')
                    ->join('INner JOIN qz_user_company_employee ce on ce.id = c.userid and ce.company_id = c.uid')
                    ->join('INNER JOIN qz_user_company uc on uc.userid = ce.company_id')
                    ->field('DISTINCT c.userid,2 AS cooperate_mode,ce.nick_name as "name",ce.logo,ce.company_id')
                    ->where($map2)
                    ->where('c.zaojia = 25 OR CONVERT ( c.mianji, SIGNED ) BETWEEN 190 AND 10000')
                    ->buildSql();

                $buildSql2 = M()->table($buildSql1)->alias('c')
                    ->join('INNER JOIN qz_user_des ud ON ud.userid = c.userid and ud.company_id = c.company_id')
                    ->join('INNER JOIN qz_user u on u.id = c.company_id')
                    ->join('INNER JOIN qz_quyu qy ON u.cs = qy.cid')
                    ->field('c.userid as id,c.`name`,c.logo,CONVERT ( ud.popularity, SIGNED ) as pv,u.cs,qy.bm,c.cooperate_mode')
                    ->where(['u.cs'=>['EQ',$cityCode[0]]])
                    ->buildSql();


                $buildSql3 = M()->table($attres)->alias('t')
                    ->union($buildSql2)
                    ->buildSql();

                $attres = M()->table($buildSql3)->alias('t1')
                    ->limit($row - count($res))
                    ->order('CASE WHEN t1.cs = '.$cityCode[0].' THEN 1 ELSE 0 END DESC,CONVERT ( t1.pv, SIGNED ) DESC')
                    ->select();

            }
            $res = array_merge($res,$attres);
        }

        
        $no_ids = array_column($res,'id');
        $finalres = [];
        // 分站如果不够，从主站取
        if($row > count($res)){
            $no_ids = empty($no_ids) ? [0]:$no_ids;
            $map['c.userid'] = array('NOT IN',$no_ids);
            if(!empty($cityCode)){
                $map['c.cs'] = array('NOT IN',$cityCode[0]); 
            }
            unset($map['co.fake']);
            $finalsub = $sub = M('cases')->alias('c')
                    ->join('LEFT JOIN qz_user_company_employee ce on ce.company_id = c.uid')
                    ->field('DISTINCT c.userid,1 as cooperate_mode')
                    ->where("ce.id is null")
                    ->where($map)
                    ->where('c.zaojia = 25 OR CONVERT ( c.mianji, SIGNED ) BETWEEN 190 AND 10000')
                    ->order('c.id DESC')
                    ->limit(40*$row)
                    ->buildSql();
            $finalres = M()->table($finalsub)->alias('c')
                ->join('INNER JOIN qz_user u ON u.id = c.userid')
                ->join('INNER JOIN qz_quyu qy ON u.cs = qy.cid')
                ->join('INNER JOIN qz_team t ON c.userid = t.userid')
                ->join('INNER JOIN qz_user_company co ON t.comid = co.id')
                ->field('u.id,u.name,u.logo,CONVERT ( u.pv, SIGNED ) as pv,u.cs,qy.bm,co.cooperate_mode')
                ->where(['u.cs'=>['EXP','IS NOT NULL'],'co.fake'=>['EQ',0],'co.is_show'=>['EQ',1],'t.zt'=>['EQ',2]])
                ->limit($row-count($res))
                ->buildSql();

            //
            unset($map['c.userid']);
            $map2 = $map;
            $map2['uc.is_show'] = array('eq',1);
            $map2['uc.fake'] = array('eq',0);
            $buildSql1 = M('cases')->alias('c')
                ->join('INNER JOIN qz_user_company_employee ce on ce.id = c.userid and ce.company_id = c.uid')
                ->join('INNER JOIN qz_user_company uc on uc.userid = ce.company_id')
                ->field('DISTINCT c.userid,2 AS cooperate_mode,ce.nick_name as "name",ce.logo,ce.company_id')
                ->where($map2)
                ->where('c.zaojia = 25 OR CONVERT ( c.mianji, SIGNED ) BETWEEN 190 AND 10000')
                ->limit(20*$row)
                ->buildSql();


            $buildSql2 = M()->table($buildSql1)->alias('c')
                ->join('INNER JOIN qz_user_des ud ON ud.userid = c.userid and ud.company_id = c.company_id')
                ->join('INNER JOIN qz_user u on u.id = c.company_id')
                ->join('INNER JOIN qz_quyu qy ON u.cs = qy.cid')
                ->field('c.userid as id,c.`name`,c.logo,CONVERT ( ud.popularity, SIGNED ) as pv,u.cs,qy.bm,c.cooperate_mode')
                ->where(['u.cs'=>['EXP','IS NOT NULL']])
                ->limit($row-count($res))
                ->buildSql();


            $buildSql3 = M()->table($finalres)->alias('t')
                ->union($buildSql2)
                ->buildSql();

//            $finalres = M()->table($buildSql3)->alias('t1')
            $finalres = M()->table($buildSql2)->alias('t1')
                ->limit($row - count($res))
                ->order('t1.id desc')
                ->select();

        }
        $res = array_merge($res,$finalres);
        array_multisort(array_column($res,'pv'),SORT_DESC,$res);
        return $res;
    }
    // 根据装修公司id获取有案例的设计师
    public function getDesignerByComids($ids = [],$row){
        if (empty($ids)) {
            return [];
        }
        $where = [
            'c.userid' => ['IN', $ids],
            'ca.status' => ['eq', 2],
            'ca.isdelete' => ['eq', 1]
        ];
        $usql = M('user_company')->alias('c')
            ->join('JOIN qz_team t ON c.userid = t.comid')
            ->join('JOIN qz_user u ON t.userid = u.id')
            ->join('JOIN qz_cases ca ON u.id = ca.userid')
            ->join('LEFT JOIN qz_user_company_employee ce on ce.company_id = c.userid')
            ->field('u.id,u.logo,u.name,u.cs,ca.id as caseid,ca.mianji,ca.fengge as fg,ca.title,CONVERT ( u.pv, SIGNED ) AS pv,t.zw,1 as cooperate_mode,c.userid as comid')
            ->where($where)
            ->where('ce.id is null')
            ->order('ca.id DESC')
            ->buildSql();


        //新签返
        $usql2 = M('user_company')->alias('c')
            ->join('INNER JOIN qz_user_company_employee ce on ce.company_id = c.userid')
            ->join('INNER JOIN qz_cases ca ON ce.id = ca.userid and ca.uid = ce.company_id')
            ->join("INNER join qz_user_des ud on ud.userid = ce.id and ud.company_id = ce.company_id")
            ->join("INNER JOIN qz_user u on u.id = c.userid")
            ->field('ce.id,ce.logo,ce.nick_name as "name",u.cs,ca.id as caseid,ca.mianji,ca.fengge as fg,ca.title,CONVERT ( ud.popularity, SIGNED ) AS pv,case when ce.position = 2 then "设计师" when ce.position = 3 then "首席设计师" when ce.position = 4 then "设计总监" else "" end as zw,2 as cooperate_mode,c.userid as comid')
            ->where($where)
            ->order('ca.id DESC')
            ->buildSql();

        $usql = M()->table($usql)->alias('t')
            ->union($usql2)
            ->buildSql();


        $ucsql = M()->table($usql)->alias('c')
                ->order('c.id DESC')
                ->group('c.id')
                ->buildSql();
        $csql  = M()->table($ucsql)->alias('c')
                ->join('LEFT JOIN qz_case_img ci ON ci.caseid = c.caseid')
                ->field('c.*,ci.img_host,ci.img_path,ci.img')
                ->where(['ci.status'=>['LT',3]])
                ->order('ci.img_on DESC,ci.px,ci.id ASC')
                ->buildSql();
        $res = M()->table($csql)->alias('u')
                ->join('INNER JOIN qz_quyu AS qy ON u.cs = qy.cid')
                ->join('INNER JOIN qz_user_des AS ud ON ud.userid = u.id')
                ->join('INNER JOIN qz_fengge fg ON u.fg = fg.id')
                ->field('u.*,fg.name as fengge,ud.jobtime,qy.bm,u.id as userid,ud.cost')
                ->group('u.caseid')
                ->limit($row)
                ->order('u.pv DESC')
                ->select();
        return $res;
    }
    // 获取城市有案例设计师
    public function getCityCaseDesigner($cs,$row,$no_ids){

        $map1 = [
            'u.cs'=>['EQ',$cs],
            'u.classid'=>['EQ',2],
            'uc.fake'=>['EQ',0],
            'uc.is_show'=>['EQ',1]
        ];
        if(!empty($no_ids))
        {
            $map1['u.id'] = ['NOT IN',$no_ids];
        }
        $sql1 = M('user')->alias('u')
                ->join('INNER JOIN qz_team t ON u.id = t.userid')
                ->join('INNER JOIN qz_user_company uc ON uc.userid = t.comid')
                ->where($map1)
                ->field('u.id,u.logo,u.name,u.cs,CONVERT ( u.pv, SIGNED ) AS pv')
                ->order('pv DESC')
                ->buildSql();
        $map2 = [
            'c.on' => ['EQ',1],
            'c.isdelete'=>['EQ',1],
            'c.status'=>['EQ',2]
        ];
        $sql2 = M('cases')->alias('c')
                ->join('INNER JOIN '.$sql1.'u ON c.userid = u.id')
                ->join('INNER JOIN qz_quyu qy ON u.cs = qy.cid')
                ->join('INNER JOIN qz_user_des ud ON ud.userid = u.id')
                ->join('INNER JOIN qz_fengge fg ON c.fengge = fg.id')
                ->where($map2)
                ->field('u.*,c.id as caseid,c.title,c.mianji,ud.jobtime,fg.name as fengge,qy.bm')
                ->group('c.userid')
                ->limit($row)
                ->buildSql();
        return M()->table($sql2)->alias('c')
                ->join('INNER JOIN qz_case_img ci on c.caseid = ci.caseid')
                ->field('c.*,ci.img_path,ci.img_host,c.id as userid')
                ->group('ci.caseid')
                ->order('c.pv DESC')
                ->select();
    }

}