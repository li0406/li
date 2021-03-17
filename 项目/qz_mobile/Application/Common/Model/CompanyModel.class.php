<?php

/**
 * 装修公司列表页
 */

namespace Common\Model;
use Think\Model;

class CompanyModel extends Model{

    protected $autoCheckFields = false;

    public function getRecommendList(array $condition,$count=0)
    {
        //案例数+设计师数合计数字由大到小排序，数字相同根据公司ID倒序排列
        $orderby = "(COALESCE(t1.case_count,0) + COALESCE(t1.team_count,0)) desc";
        $map["q.is_show"] = array("EQ",1);
        $map["classid"] = array("EQ",3);
        if(!empty($condition['cs'])){
            $map["cs"] = array("EQ",$condition['cs']);
        }
        if(!empty($condition['comid'])){
            $map['q.userid'] = array('not in', $condition['comid']);
        }
        $Db = M('user');

        $buildSql = $Db->where($map)->alias("a")
            ->field("a.`id`,a.`on`,a.qc,a.jc,a.pv,a.uv,a.cs,a.kouhao,a.logo,a.uptime,a.register_time,a.info_time,a.case_count,q.team_count,q.img,q.img_host,q.comment_count,q.comment_score,q.fake,a.sales_count,a.activte_score,e.bm,q.id as comid,a.dz,
            q.team_num,case when a.`on` = 2 and q.fake = 0 then 1 when a.`on` != 2 and q.fake = 0 then 2 else 3 end paixu, if(q.img != '', 1, 0) com_img_count,count(com_ac.id) activity_count,count(card_com.id) card_count")
            ->join("INNER JOIN qz_user_company as q on q.userid = a.id ")
            ->join("INNER JOIN qz_quyu as e on e.cid = a.cs")
            ->join("left JOIN qz_company_activity com_ac
                                            on com_ac.cid = a.id and com_ac.`check` = 1 and
                                               com_ac.types = 1 and
                                               com_ac.state = 1 and
                                               com_ac.`start` <= unix_timestamp(now()) and
                                               com_ac.`end` >= unix_timestamp(now())")
            ->join("LEFT JOIN qz_card_com card_com on card_com.com_id = a.id")
            ->join("LEFT JOIN qz_card card on card.id = card_com.card_id and card.type = 2")
            ->join("LEFT JOIN qz_card_com_record card_record
                                            on card_record.card_com_id = card.id AND
                                               card_record.apply_state = 1 AND
                                               card_record.`check` = '2' AND
                                               card_record.`start` <= unix_timestamp(now()) AND
                                               card_record.`end` >= unix_timestamp(now())")
            ->group('a.id')
            ->buildSql();
        $buildSql2 = $this->table($buildSql)->alias('t2')
            ->field('t2.*,count(cases.id) case_des_count')
            ->join('LEFT JOIN qz_cases as cases on cases.uid = t2.id and cases.`on` = 1 and cases.classid = 3 and cases.isdelete in (1, 3)')
            ->group('t2.id')
            ->buildSql();
        $result = $this->table($buildSql2)->alias('t1')
            ->field('*')
            ->group('t1.id')
            ->order("t1.paixu asc,t1.fake asc,".$orderby.",t1.register_time desc,t1.id desc")
            ->limit($count)
            ->select();
        return $result;
    }

    public function getList($condition='',$pagesize= 1,$pageRow = 10, $keyword='', $classid=3){
        $map["q.is_show"] = array("EQ",1);

        $map["classid"] = array("EQ",3);

        //如果是装修公司根据关键字查询公司全称,否则根据用户昵称查询
        if(!empty($keyword)){
            if($classid == 3){
                $map["qc"] = array("LIKE","%$keyword%");
            }else{
                $map["name"] = array("LIKE","%$keyword%");
            }
        }

        if(!empty($condition['cs'])){
            $map["cs"] = array("EQ",$condition['cs']);
        }

        if(!empty($condition['keyword'])){
            $map['qc']  = array('LIKE','%'.$condition['keyword'].'%');
        }
        //认证
        if(!empty($condition['vip'])){
            $map["on"] = array("EQ",2);
            //$map['fake'] = array('EQ',0);
        }
        if(!empty($condition['fw'])){
            $fw = $condition['fw'];
//            $map["_string"] = "FIND_IN_SET($fw,q.quyu)";
            $map["_string"] = "FIND_IN_SET($fw,a.qx)";
        }
        if(!empty($condition['fg'])){
            $fg = $condition['fg'];
            $map["_string"] = "FIND_IN_SET($fg,q.fengge)";
        }
        if(!empty($condition['bz'])){
            $bz = $condition['bz'];
            $map["_string"] = "FIND_IN_SET($bz,q.baozhang)";
        }

        if(!empty($condition['gm'])){
            $map["q.guimo"] = array("EQ",$condition['gm']);
        }

        if(!empty($condition['comids'])){
            $map['q.userid'] = array('in', $condition['comids']);
        }

        if(!empty($condition['comid'])){
            $map['q.userid'] = array('not in', $condition['comid']);
        }

//        $isSale = !empty($condition['sale']) ? "AND a.sales_count > '0'  " : '';
        if (!empty($condition['sale'])) {
            //点击优惠时 筛除假会员
            $map['a.on'] = array('eq', 2);
            $map['q.fake'] = array('eq', 0);
        }
        $orderby = $condition['orderby'];
        unset($condition['orderby']);
//dump($condition);
        $Db = M('user');

        $buildSql = $Db->where($map)->alias("a")
            ->field("a.`id`,a.on,a.qc,a.jc,a.pv,a.uv,a.cs,a.kouhao,a.logo,a.uptime,a.register_time,a.info_time,a.case_count,q.team_count,q.img,q.img_host,q.comment_count,q.comment_score,q.fake,a.sales_count,a.activte_score,e.bm,q.id as comid,a.dz,
            q.team_num,case when a.on = 2 and q.fake = 0 then 1 when a.`on` != 2 and q.fake = 0 then 2 else 3 end paixu, if(q.img != '', 1, 0) com_img_count,count(com_ac.id) activity_count,count(card_com.id) card_count")
            ->join("INNER JOIN qz_user_company as q on q.userid = a.id ")
            ->join("INNER JOIN qz_quyu as e on e.cid = a.cs")
            ->join("left JOIN qz_company_activity com_ac
                                            on com_ac.cid = a.id and com_ac.check = 1 and
                                               com_ac.types = 1 and
                                               com_ac.state = 1 and
                                               com_ac.start <= unix_timestamp(now()) and
                                               com_ac.end >= unix_timestamp(now())")
            ->join("LEFT JOIN qz_card_com card_com on card_com.com_id = a.id")
            ->join("LEFT JOIN qz_card card on card.id = card_com.card_id and card.type = 2")
            ->join("LEFT JOIN qz_card_com_record card_record
                                            on card_record.card_com_id = card.id AND
                                               card_record.apply_state = 1 AND
                                               card_record.check = '2' AND
                                               card_record.start <= unix_timestamp(now()) AND
                                               card_record.end >= unix_timestamp(now())")
            ->group('a.id')
            ->buildSql();
        $buildSql2 = $this->table($buildSql)->alias('t2')
                    ->field('t2.*,count(cases.id) case_des_count')
                    ->join('LEFT JOIN qz_cases as cases on cases.uid = t2.id and cases.on = 1 and cases.classid = 3 and cases.isdelete in (1, 3)')
                     ->group('t2.id')
                    ->buildSql();
        if($condition['cond']=='issale'){
            $result = $this->table($buildSql2)->alias('t1')
                ->field('*')
                ->where('t1.activity_count>0 or t1.card_count>0')
                ->limit($pagesize.",".$pageRow)
                ->group('t1.id')
                ->order("t1.paixu asc,t1.fake asc ,t1.com_img_count desc,t1.comment_score,(COALESCE(t1.case_count,0) + COALESCE(t1.team_count,0)) desc,t1.register_time desc,t1.id desc")
                ->select();

        }else {
            $result = $this->table($buildSql2)->alias('t1')
                ->field('*')
                ->limit($pagesize.",".$pageRow)
                ->group('t1.id')
                ->order("t1.paixu asc,t1.fake asc,".$orderby.",t1.com_img_count desc,t1.comment_score,(COALESCE(t1.case_count,0) + COALESCE(t1.team_count,0)) desc,t1.register_time desc,t1.id desc")
                ->select();
        }
        return array("result"=>$result);
    }

    /**
     * 获取用户信息列表数量
     * @param  string $keyword [关键字]
     * @param  string $cs      [所在城市]
     * @return [type]          [description]
     */
    public function getCompanyCount($condition, $keyword='',$classid=3){
        $map["q.is_show"] = array("EQ",1);

        $map["classid"] = array("in",array(3,6));
        $map['qc'] = array("neq","");

        //如果是装修公司根据关键字查询公司全称,否则根据用户昵称查询
        if(!empty($keyword)){
            if(in_array($classid,array(3,6))){
                $map["qc"] = array("LIKE","%$keyword%");
            }else{
                $map["name"] = array("LIKE","%$keyword%");
            }
        }

        if(!empty($condition['cs'])){
            $map["cs"] = array("EQ",$condition['cs']);
        }

        if(!empty($condition['keyword'])){
            $map['qc']  = array('LIKE','%'.$condition['keyword'].'%');
        }
        //认证
        if(!empty($condition['vip'])){
            $map["on"] = array("EQ",2);
            //$map['fake'] = array('EQ',0);
        }
        if(!empty($condition['fw'])){
            $fw = $condition['fw'];
//            $map["_string"] = "FIND_IN_SET($fw,q.quyu)";
            $map["_string"] = "FIND_IN_SET($fw,a.qx)";
        }
        if(!empty($condition['fg'])){
            $fg = $condition['fg'];
            $map["_string"] = "FIND_IN_SET($fg,q.fengge)";
        }
        if(!empty($condition['bz'])){
            $bz = $condition['bz'];
            $map["_string"] = "FIND_IN_SET($bz,q.baozhang)";
        }

        if(!empty($condition['gm'])){
            $map["q.guimo"] = array("EQ",$condition['gm']);
        }

        if(!empty($condition['comids'])){
            $map['q.userid'] = array('in', $condition['comids']);
        }

        if(!empty($condition['comid'])){
            $map['q.userid'] = array('not in', $condition['comid']);
        }

//        $isSale = !empty($condition['sale']) ? "AND a.sales_count > '0'  " : '';
        if (!empty($condition['sale'])) {
            //点击优惠时 筛除假会员
            $map['a.on'] = array('eq', 2);
            $map['q.fake'] = array('eq', 0);
        }
        $orderby = $condition['orderby'];
        unset($condition['orderby']);
//dump($condition);
        $Db = M('user');

        $buildSql = $Db->where($map)->alias("a")
            ->field("a.`id`,a.on,a.qc,a.jc,a.pv,a.uv,a.cs,a.kouhao,a.logo,a.uptime,a.register_time,a.info_time,a.case_count,q.team_count,q.img,q.img_host,q.comment_count,q.comment_score,q.fake,a.sales_count,a.activte_score,e.bm,q.id as comid,a.dz,
            q.team_num,case when a.on = 2 and q.fake = 0 then 1 when a.`on` != 2 and q.fake = 0 then 2 else 3 end paixu, if(q.img != '', 1, 0) com_img_count,count(com_ac.id) activity_count,count(card_com.id) card_count")
            ->join("INNER JOIN qz_user_company as q on q.userid = a.id ")
            ->join("INNER JOIN qz_quyu as e on e.cid = a.cs")
            ->join("left JOIN qz_company_activity com_ac
                                            on com_ac.cid = a.id and com_ac.check = 1 and
                                               com_ac.types = 1 and
                                               com_ac.state = 1 and
                                               com_ac.start <= unix_timestamp(now()) and
                                               com_ac.end >= unix_timestamp(now())")
            ->join("LEFT JOIN qz_card_com card_com on card_com.com_id = a.id")
            ->join("LEFT JOIN qz_card card on card.id = card_com.card_id and card.type = 2")
            ->join("LEFT JOIN qz_card_com_record card_record
                                            on card_record.card_com_id = card.id AND
                                               card_record.apply_state = 1 AND
                                               card_record.check = '2' AND
                                               card_record.start <= unix_timestamp(now()) AND
                                               card_record.end >= unix_timestamp(now())")
            ->group('a.id')
            ->buildSql();
        if($condition['cond']=='issale'){
            $result = $this->table($buildSql)->alias('t1')
                ->field('*')
                ->where('t1.activity_count>0 or t1.card_count>0')
//                ->having('t1.activity_count>0 or t1.card_count>0')
                ->count();

        }else {
            $result = $this->table($buildSql)->alias('t1')
                ->field('*')
                ->count();
        }
        return (int)$result;
    }

    //取最新优惠活动
    public function getSaleInfo($row='5',$cityid = ''){
        if(!empty($cityid)){
            $map["cs"] = array("EQ",$cityid);
        }
        $map['type'] = '1';
        //$map['time'] = array('EGT',strtotime(date('Y-m-d',time()))  - 86400 * 30); //30天内的装修公司活动
        $buildSql = M('info')->field('id,title,cs')->order('time DESC')->limit("0,".$row)->where($map)->buildSql();
        $result = M('info')->table($buildSql)->alias("t")
                    ->field("t.*,q.bm")
                    ->join("LEFT JOIN qz_quyu as q on q.cid = t.cs")
                    ->select();
        return $result;
    }

    //获取优惠券
    public function getSpecialCardById($comid, $limit = 2){
        if(!$comid){
            return array();
        }
        $map = [];
        $map['b.com_id'] = array('EQ',$comid);
        $map['c.start'] = array('ELT',time());
        $map['c.end'] = array('EGT',time());
        $map['c.check'] = array('EQ',2);
        $map['c.apply_state'] = array('EQ',1);
//        $map['a.enable'] = array('EQ',1);
        $map['_string'] = '(a.enable =1 or (a.enable = 2 and a.disable_time >'.time().'))'; //未禁用或禁用时间未到

        $list = M('card_com')->alias('b')
            ->field('a.id,a.`name`,c.start stime,1 AS mark')
            ->where($map)
            ->join('qz_card a on a.id = b.card_id')
            ->join('qz_card_com_record c on c.card_com_id = b.id')
            ->order('a.`add_time` desc')
            ->limit($limit)
            ->group('a.id')
            ->select();
        return $list ? $list : array();
    }

    /**
     * getCardInfoById  根据优惠券id获取优惠券信息
     * @param $map
     */
    public function getCardInfoById($map){
        return M('card_com')->alias('b')
            ->where($map)
            ->field('a.id,a.`name`,a.money1,a.money2,a.service,a.add_time,a.`rule`,a.module,a.`enable`,c.activity_start,c.activity_end,c.`start`,c.`end`,c.amount,c.`check`,c.apply_state,c.check_reason')
            ->join('qz_card a on a.id = b.card_id')
            ->join('qz_card_com_record c on c.card_com_id = b.id')
            ->find();
    }

    /**
     * 根据公司id获取公司正在使用的礼券总数量
     * @param $comid
     */
    public function getSpecialCardCountById($comid){
        if(!$comid){
            return 0;
        }
        $map = [];
        $map['b.com_id'] = array('EQ',$comid);
        $map['c.start'] = array('ELT',time());
        $map['c.end'] = array('EGT',time());
        $map['c.check'] = array('EQ',2);
        $map['c.apply_state'] = array('EQ',1);
//        $map['a.enable'] = array('EQ',1);
        $map['_string'] = '(a.enable =1 or (a.enable = 2 and a.disable_time >'.time().'))'; //未禁用或禁用时间未到

        $count = M('card_com')->alias('b')
            ->field('a.id,a.`name`')
            ->where($map)
            ->join('qz_card a on a.id = b.card_id')
            ->join('qz_card_com_record c on c.card_com_id = b.id')
            ->group('a.id')
            ->select();
        return count($count);

    }


    /**
     * getMoreCompanyData  获取假会员数据
     * @param $neednum   需要的数据数量
     * @param $cityid    城市
     */
    public function getMoreCompanyData($neednum,$cityid){
        $map = [];
        $map['u.on'] = array('EQ',2);
        $map['u.classid'] = array('EQ',3);
        $map['c.fake'] = array('EQ',1);
        if($cityid){
            $map['u.cs'] = array('EQ',$cityid);
        }

        $buildSql = M('user')->alias('u')
            ->where($map)
            ->field('u.id,u.`user`,round(r.ping/r.haoping,2) hpl,r.`day`,u.cs,u.jc,u.logo')
            ->join('left join qz_user_company c on c.userid = u.id')
            ->join('qz_user_company_rank r on r.comid = u.id')
            ->group('u.id')
            ->buildSql();
        $list =  M('user')->table($buildSql)->alias('t')
            ->field('t.*,count(a.id) casenum')
            ->join('qz_cases  a on a.uid = t.id')
            ->group('t.id')
            ->order('t.hpl desc,casenum desc')
            ->limit($neednum)
            ->select();
        return $list;
    }

    /**
     * 获取装修公司首页设计师列表
     * @return [type] [description]
     */
    public function getDesignerListByCompany($comid ='',$zw='',$zt ="",$pageIndex = 0,$pageCount=10){
        $map = array(
            "b.comid"=>array("EQ",$comid)
        );
        $buildSql = M("user")->where($map)->alias("a")
            ->join("INNER JOIN qz_team as b on b.userid = a.id and b.zt = 2")
            ->field("a.*,b.zw,b.px,b.xs,b.zt")
            ->order("b.px desc")
            ->buildSql();
        return M("user")->table($buildSql)->alias("t")
            ->join("LEFT JOIN qz_cases as b on t.id = b.userid and b.isdelete = 1 and b.on=1 and b.classid<3")
            ->join("LEFT JOIN qz_user_des as d on t.id = d.userid and d.company_id = 0")
            ->field("count(b.id) as case_counts,d.jobtime,d.linian,d.cost,t.*")
            ->group("t.id ")
            ->order("t.px desc,case_counts desc,t.pv desc,t.id desc")
            ->limit($pageIndex.",".$pageCount)
            ->select();

    }


    /*******    1059  首页改版  获取装修公司列表**********/

    public function getHomeCompanyRank($cs = 0,$num = 12){
        !empty($cs) && $map['a.cs'] = array('eq',$cs);
        $map1 = [];
        $yesterday = date('Y-m-d',strtotime('-1days'));
        $map1['_string'] = "c.`day` = '{$yesterday}'";

        $where = [];
        $where['a.classid'] = 3;
        $where['a.`on`'] = 2;
        $where['b.`is_show`'] = 1;  //排除不显示的公司
        $where['b.`fake`'] = 0;  //真会员

        $buildSql1 = M('user')->alias('a')
            ->field('a.id')
            ->where($where)
            ->join('INNER JOIN qz_user_company b on a.id = b.userid and b.fake = 0')
            ->buildSql();

        $buildSql2 = M('user')->table($buildSql1)->alias("t")
            ->where($map1)
            ->field('t.id,`day`,comid,liangfang,qiandan,haoping,wanzhengdu,ping,casesnum,loginnum,answernum')
            ->join('qz_user_company_rank c on c.comid = t.id')
            ->order('c.day desc')
            ->buildSql();

        $buildSql =  M('user_company_rank')->table($buildSql2)->alias("r")
            ->field("r.`day`,r.comid,sum(r.liangfang) as liangfang,sum(r.qiandan) as qiandan,r.haoping as haoping,r.wanzhengdu,r.ping,r.casesnum,r.loginnum,r.answernum")
            ->group("comid")
            ->buildSql();

        if ($cs == 0) {
            $buildSql3 = M("user")->alias("a")
                ->where($map)
                ->join("JOIN qz_user_company as b on b.userid = a.id")
                ->join("LEFT JOIN qz_quyu as q on a.cs = q.cid " )
                ->join("INNER JOIN".$buildSql.'as r on r.comid = a.id')
                ->join("LEFT JOIN qz_cases c on c.uid = a.id and c.`on` = 1 and c.isdelete = 1 and c.`status` = 2")
                ->field("a.id,a.qc,a.jc,a.register_time,a.logo,a.activte_score,a.case_count,((r.haoping / r.ping) *10) as comment_score,q.bm,(1.5 * r.liangfang + 0.1 * r.wanzhengdu + 0.5 * r.loginnum + 0.5 * r.answernum) AS `huoyuerank`,(1 * r.liangfang + 5 * r.qiandan + 0.1 * r.haoping + 0.1 * r.wanzhengdu) AS `koubeirank`,r.haoping,r.ping as comment_count,count(c.id) as cases_num")
                ->group('a.id')
                ->order('cases_num desc,koubeirank DESC,huoyuerank desc,register_time')
                ->limit("0,".$num)
                ->buildSql();
        } else {
            $buildSql3 =  M("user")->alias("a")
                ->where($map)
                ->join("JOIN qz_user_company as b on b.userid = a.id")
                ->join("LEFT JOIN qz_quyu as q on a.cs = q.cid " )
                ->join("INNER JOIN".$buildSql.'as r on r.comid = a.id')
                ->join("LEFT JOIN qz_cases c on c.uid = a.id and c.`on` = 1 and c.isdelete = 1 and c.`status` = 2")
                ->field("a.id,a.qc,a.jc,a.register_time,a.logo,a.activte_score,a.case_count,((r.haoping / r.ping) *10) as comment_score,q.bm,(1.5 * r.liangfang + 0.1 * r.wanzhengdu + 0.5 * r.loginnum + 0.5 * r.answernum) AS `huoyuerank`,(1 * r.liangfang + 5 * r.qiandan + 0.1 * r.haoping + 0.1 * r.wanzhengdu) AS `koubeirank`,r.haoping,r.ping as comment_count,count(c.id) as cases_num")
                ->group('a.id')
                ->order('koubeirank DESC,huoyuerank desc,register_time')
                ->limit("0,".$num)
                ->buildSql();
        }

        return $this->table($buildSql3)->alias("a")
            ->field("a.*,count(com_ac.id) activity_count,count(card_com.id) card_count")
            ->join("left JOIN qz_company_activity com_ac
                                            on com_ac.cid = a.id and com_ac.check = 1 and
                                               com_ac.types = 1 and
                                               com_ac.state = 1 and
                                               com_ac.start <= unix_timestamp(now()) and
                                               com_ac.end >= unix_timestamp(now())")
            ->join("LEFT JOIN qz_card_com card_com on card_com.com_id = a.id")
            ->join("LEFT JOIN qz_card card on card.id = card_com.card_id and card.type = 2")
            ->join("LEFT JOIN qz_card_com_record card_record
                                            on card_record.card_com_id = card.id AND
                                               card_record.apply_state = 1 AND
                                               card_record.check = '2' AND
                                               card_record.start <= unix_timestamp(now()) AND
                                               card_record.end >= unix_timestamp(now())")
            ->group("a.id")
            ->order("a.koubeirank DESC,a.huoyuerank desc,a.register_time")
            ->select();



    }


    // 获取假会员/过期会员/注册会员填充数据
    public function getHomeOtherCompany($cs = "", $limit = 1)
    {
        !empty($cs) && $map['a.cs'] = array('eq',$cs);

        $where2["a.`on`"] = array("eq", "2");
        $where2["b.fake"] = array("eq", "1");

        $map2["a.`on`"] = array("in", array(0,-1));
        $map2["_complex"] = $where2;
        $map2['_logic'] = 'or';

        $where["a.classid"] = array("eq", "3");
        $where["_complex"] = $map2;

        $map1 = [];
        $yesterday = date('Y-m-d',strtotime('-1days'));
        $map1['_string'] = "c.`day` = '{$yesterday}'";

        $buildSql1 = M('user')->alias('a')
            ->field('a.id')
            ->where($where)
            ->join('INNER JOIN qz_user_company b on a.id = b.userid')
            ->buildSql();

        $buildSql2 = M('user')->table($buildSql1)->alias("t")
            ->where($map1)
            ->field('t.id,`day`,comid,liangfang,qiandan,haoping,wanzhengdu,ping,casesnum,loginnum,answernum')
            ->join('qz_user_company_rank c on c.comid = t.id')
            ->order('c.day desc')
            ->buildSql();

        $buildSql =  M('user_company_rank')->table($buildSql2)->alias("r")
            ->field("r.`day`,r.comid,sum(r.liangfang) as liangfang,sum(r.qiandan) as qiandan,r.haoping as haoping,r.wanzhengdu,r.ping,r.casesnum,r.loginnum,r.answernum")
            ->group("comid")
            ->buildSql();

        if ($cs == 0) {
            $buildSql3 = M("user")->alias("a")
                ->where($map)
                ->join("JOIN qz_user_company as b on b.userid = a.id")
                ->join("LEFT JOIN qz_quyu as q on a.cs = q.cid " )
                ->join("INNER JOIN".$buildSql.'as r on r.comid = a.id')
                ->join("LEFT JOIN qz_cases c on c.uid = a.id and c.`on` = 1 and c.isdelete = 1 and c.`status` = 2")
                ->field("a.id,a.qc,a.jc,a.register_time,a.logo,a.activte_score,a.case_count,((r.haoping / r.ping) *10) as comment_score,q.bm,(1.5 * r.liangfang + 0.1 * r.wanzhengdu + 0.5 * r.loginnum + 0.5 * r.answernum) AS `huoyuerank`,(1 * r.liangfang + 5 * r.qiandan + 0.1 * r.haoping + 0.1 * r.wanzhengdu) AS `koubeirank`,r.haoping,r.ping as comment_count,count(c.id) as cases_num")
                ->group('a.id')
                ->order('cases_num desc,koubeirank DESC,huoyuerank desc,register_time')
                ->limit($limit)
                ->buildSql();
        } else {
            $buildSql3 =  M("user")->alias("a")
                ->where($map)
                ->join("JOIN qz_user_company as b on b.userid = a.id")
                ->join("LEFT JOIN qz_quyu as q on a.cs = q.cid " )
                ->join("INNER JOIN".$buildSql.'as r on r.comid = a.id')
                ->join("LEFT JOIN qz_cases c on c.uid = a.id and c.`on` = 1 and c.isdelete = 1 and c.`status` = 2")
                ->field("a.id,a.qc,a.jc,a.register_time,a.logo,a.activte_score,a.case_count,((r.haoping / r.ping) *10) as comment_score,q.bm,(1.5 * r.liangfang + 0.1 * r.wanzhengdu + 0.5 * r.loginnum + 0.5 * r.answernum) AS `huoyuerank`,(1 * r.liangfang + 5 * r.qiandan + 0.1 * r.haoping + 0.1 * r.wanzhengdu) AS `koubeirank`,r.haoping,r.ping as comment_count,count(c.id) as cases_num")
                ->group('a.id')
                ->order('koubeirank DESC,huoyuerank desc,register_time')
                ->limit($limit)
                ->buildSql();
        }

        return $this->table($buildSql3)->alias("a")
            ->field("a.*,count(com_ac.id) activity_count,count(card_com.id) card_count")
            ->join("left JOIN qz_company_activity com_ac
                                            on com_ac.cid = a.id and com_ac.check = 1 and
                                               com_ac.types = 1 and
                                               com_ac.state = 1 and
                                               com_ac.start <= unix_timestamp(now()) and
                                               com_ac.end >= unix_timestamp(now())")
            ->join("LEFT JOIN qz_card_com card_com on card_com.com_id = a.id")
            ->join("LEFT JOIN qz_card card on card.id = card_com.card_id and card.type = 2")
            ->join("LEFT JOIN qz_card_com_record card_record
                                            on card_record.card_com_id = card.id AND
                                               card_record.apply_state = 1 AND
                                               card_record.check = '2' AND
                                               card_record.start <= unix_timestamp(now()) AND
                                               card_record.end >= unix_timestamp(now())")
            ->group("a.id")
            ->order("a.koubeirank DESC,a.huoyuerank desc,a.register_time")
            ->select();

    }

}