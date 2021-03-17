<?php

/**
 * 装修公司列表页
 */

namespace Common\Model;
use Think\Model;

class CompanyModel extends Model{

    protected $autoCheckFields = false;


    public function getList($condition='',$pagesize= 1,$pageRow = 10){
        $map["classid"] = array("EQ",3);

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
            $fw = (int)$condition['fw'];
            $map["_string"] = "FIND_IN_SET($fw,q.quyu)";
        }
        if(!empty($condition['fg'])){
            $fg = (int)$condition['fg'];
            $map["_string"] = "FIND_IN_SET($fg,q.fengge)";
        }
        if(!empty($condition['bz'])){
            $bz = (int)$condition['bz'];
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
        $isSale = !empty($condition['sale']) ? "AND a.sales_count > '0'  " : '';
        if (!empty($condition['sale'])) {
            //点击优惠时 筛除假会员
            $map['a.on'] = array('eq', 2);
            $map['q.fake'] = array('eq', 0);
        }
        //dump($map);

        $orderby = $condition['orderby'];
        unset($condition['orderby']);

        $Db = M('user');
        $result = $Db->where($map)->alias("a")
            ->field("a.`id`,a.on,a.jc,a.qc,a.pv,a.uv,a.cs,a.logo,a.uptime,a.info_time,a.case_count,q.team_count,q.comment_count,q.comment_score,q.fake,a.sales_count,a.activte_score,e.bm,q.id as comid,a.dz,q.team_num,q.img")
            ->join("INNER JOIN qz_user_company as q on q.userid = a.id $isSale ")
            ->join("INNER JOIN qz_quyu as e on e.cid = a.cs")
            ->order("`on` desc,q.fake,".$orderby." desc")
            ->limit($pagesize.",".$pageRow)
            ->select();
        return array("result"=>$result);
    }

    /**
     * 获取口碑排行
     * @param string $condition
     * @param int $pagesize
     * @param int $pageRow
     * @return array
     */
    public function getStarList($condition=[],$pagesize= 1,$pageRow = 10){
		$map["q.is_show"] = array("EQ",1);
        $map["classid"] = array("IN",array(3,6));
        $map['a.qc'] = array('exp', 'IS NOT NULL');
        $map['a.qc'] = array('NEQ', '');
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
            $fw = (int)$condition['fw'];
            $map["_string"] = "a.qx  = $fw";
        }
        if(!empty($condition['fg'])){
            $fg = (int)$condition['fg'];
            $map["_string"] = "FIND_IN_SET($fg,q.fengge)";
        }
        if(!empty($condition['bz'])){
            $bz = (int)$condition['bz'];
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
        if(!empty($condition['sale'])){
            if($condition['sale'] == 1){
                $isSale = "AND a.sales_count > '0'  ";
            }elseif($condition['sale'] == 2){
                $isSale = '';
            }
        }else{
            $isSale = '';
        }
        if (!empty($condition['sale'])) {
            //点击优惠时 筛除假会员
            $map['a.on'] = array('eq', 2);
            $map['q.fake'] = array('eq', 0);
        }

        //获取前30天开始结束时间
        $rand_time  = $this->getlastthirtyDays();
        $start_time = $rand_time["start"];
        $end_time = $rand_time["end"];

        $yesterday = date('Y-m-d',strtotime("-1 day"));

        $Db = M('user');
        if(!empty($condition['sale']) && $condition['sale'] == 2){          //获取有优惠券的公司列表
            $map['d1.start'] = array('ELT',time());
            $map['d1.end'] = array('EGT',time());
            $map['d1.check'] = array('EQ',2);
            $map['d1.apply_state'] = array('EQ',1);
            $map['_string'] = '(c1.enable =1 or (c1.enable = 2 and c1.disable_time >'.time().'))'; //未禁用或禁用时间未到

            $buildSql = $Db->where($map)->alias("a")
                ->field("a.`id`,a.classid,a.on,a.qc,a.jc,a.pv,a.uv,a.cs,a.logo,a.uptime,a.info_time,a.case_count,q.team_count,q.comment_count,q.comment_score,q.fake,a.sales_count,a.activte_score,e.bm,q.id as comid,a.dz,q.team_num,q.img,a.register_time,e.cname as cityname,z.qz_area as area_name")
                ->join("INNER JOIN qz_user_company as q on q.userid = a.id $isSale ")
                ->join("INNER JOIN qz_quyu as e on e.cid = a.cs")
                ->join("LEFT JOIN qz_area as z on a.qx = z.qz_areaid")
                ->join("INNER JOIN qz_card_com  b1 on b1.com_id = a.id")
                ->join("INNER JOIN qz_card c1 on c1.id = b1.card_id")
                ->join("INNER JOIN qz_card_com_record d1 on d1.card_com_id = b1.id")
                ->buildSql();
        }else{
            $buildSql = $Db->where($map)->alias("a")
                ->field("a.`id`,a.classid,a.on,a.qc,a.jc,a.pv,a.uv,a.cs,a.logo,a.uptime,a.info_time,a.case_count,q.team_count,q.comment_count,q.comment_score,q.fake,a.sales_count,a.activte_score,e.bm,q.id as comid,a.dz,q.team_num,q.img,a.register_time,e.cname as cityname,z.qz_area as area_name")
                ->join("INNER JOIN qz_user_company as q on q.userid = a.id $isSale ")
                ->join("INNER JOIN qz_quyu as e on e.cid = a.cs")
                ->join("LEFT JOIN qz_area as z on a.qx = z.qz_areaid")
                ->buildSql();
        }
        //1*量房数+5*签单数+0.1*好评数+10*店铺完成度
        $buildSql1 = $Db->table($buildSql)->alias('t')
            ->field('t.*, uc1.liangfang,uc1.haoping,uc1.qiandan,uc1.wanzhengdu,uc1.ping')
//            ->join("left join qz_user_company_rank uc1 on uc1.comid = t.id and uc1.day between '".$start_time."' and  '".$end_time."'")
            ->join("left join qz_user_company_rank uc1 on uc1.comid = t.id and uc1.day = '{$yesterday}'")
            ->order("day desc")
            ->buildSql();

        $buildSql2 =  $Db->table($buildSql1)->alias('t2')
            ->field('t2.*,sum( t2.liangfang) as liangfang_num,t2.haoping as haoping_num,t2.ping as ping_num,sum( t2.qiandan) as qiandan_num')
            ->group('t2.id')
            ->buildSql();

        $result = $Db->table($buildSql2)->alias('t1')
            ->field('t1.*,
            (t1.liangfang_num+5*t1.qiandan_num+0.1*t1.haoping_num+0.1*t1.wanzhengdu) as order_fenshu,(t1.haoping_num/t1.ping_num) as haopinglv,
              case
            when t1.on = 2 and t1.fake=0 then 1
            when t1.on <> 2 and t1.fake=0  then 2
            else 3 end paixu
            ')
            ->order('paixu,t1.fake,order_fenshu desc,t1.register_time')
            ->limit($pagesize.",".$pageRow)
            ->select();
       
        return array("result"=>$result);
    }

    /**
     * 获取综合排行
     * @param string $condition
     * @param int $pagesize
     * @param int $pageRow
     * @return array
     */
    public function getShiliList($condition=[],$pagesize= 1,$pageRow = 10){
        if(!empty($condition['cs'])){
            $map["cs"] = array("EQ",$condition['cs']);
        }
        $map["q.is_show"] = array("EQ",1);
        $map["classid"] = array("IN",array(3,6));
        $map['a.qc'] = array('exp', 'IS NOT NULL');
        $map['a.qc'] = array('NEQ', '');

        if(!empty($condition['keyword'])){
            $map['qc']  = array('LIKE','%'.$condition['keyword'].'%');
        }

        // feature-1009    合肥首页/分站排除会员40165
        if (isset($condition['hiddenid']) && (count($condition['hiddenid']) > 0)) {
            $map['a.id'] = array('not in', $condition['hiddenid']);
        }

        //认证
        if(!empty($condition['vip'])){
            $map["on"] = array("EQ",2);
            //$map['fake'] = array('EQ',0);
        }
        if(!empty($condition['fw'])){
            $fw = (int)$condition['fw'];
            $map["_string"] = "a.qx = $fw";
        }
        if(!empty($condition['fg'])){
            $fg = (int)$condition['fg'];
            $map["_string"] = "FIND_IN_SET($fg,q.fengge)";
        }
        if(!empty($condition['bz'])){
            $bz = (int)$condition['bz'];
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

        if(!empty($condition['sale'])){
            if($condition['sale'] == 1){
                $isSale = "AND a.sales_count > '0'  ";
            }elseif($condition['sale'] == 2){
                $isSale = '';
            }
        }else{
            $isSale = '';
        }
        if (!empty($condition['sale'])) {
            //点击优惠时 筛除假会员
            $map['a.on'] = array('eq', 2);
            $map['q.fake'] = array('eq', 0);
        }
        //获取前30天数据
        $rand_time  = $this->getlastthirtyDays();
        $start_time = $rand_time["start"];
        $end_time = $rand_time["end"];

        $yesterday = date('Y-m-d',strtotime("-1 day"));

        $Db = M('user');

        if(!empty($condition['sale']) && $condition['sale'] == 2){          //获取有优惠券的公司列表
            $map['d1.start'] = array('ELT',time());
            $map['d1.end'] = array('EGT',time());
            $map['d1.check'] = array('EQ',2);
            $map['d1.apply_state'] = array('EQ',1);
            $map['_string'] = '(c1.enable =1 or (c1.enable = 2 and c1.disable_time >'.time().'))'; //未禁用或禁用时间未到

            $build = $Db->where($map)->alias("a")
                ->field("a.`id`,a.classid,a.on,a.qc,a.jc,a.pv,a.uv,a.cs,a.logo,a.uptime,a.info_time,a.case_count,q.team_count,q.comment_count,q.comment_score,q.fake,a.sales_count,a.activte_score,e.bm,q.id as comid,a.dz,q.img,a.register_time,e.cname as cityname,z.qz_area as area_name,c1.name cardname,d1.id cardid,q.cooperate_mode")
                ->join("INNER JOIN qz_user_company as q on q.userid = a.id $isSale ")
                ->join("INNER JOIN qz_quyu as e on e.cid = a.cs")
                ->join("LEFT JOIN qz_area as z on a.qx = z.qz_areaid")
                ->join("INNER JOIN qz_card_com  b1 on b1.com_id = a.id")
                ->join("INNER JOIN qz_card c1 on c1.id = b1.card_id")
                ->join("INNER JOIN qz_card_com_record d1 on d1.card_com_id = b1.id")
                ->buildSql();

        }else{
            $build = $Db->where($map)->alias("a")
                ->field("a.`id`,a.classid,a.on,a.qc,a.jc,a.pv,a.uv,a.cs,a.logo,a.uptime,a.info_time,a.case_count,q.team_count,q.comment_count,q.comment_score,q.fake,a.sales_count,a.activte_score,e.bm,q.id as comid,a.dz,q.img,a.register_time,e.cname as cityname,z.qz_area as area_name,q.cooperate_mode")
                ->join("INNER JOIN qz_user_company as q on q.userid = a.id $isSale ")
                ->join("INNER JOIN qz_quyu as e on e.cid = a.cs")
                ->join("LEFT JOIN qz_area as z on a.qx = z.qz_areaid")
                ->buildSql();
        }

        $buildSql = $Db->table($build)->alias('t11')
            ->field('t11.*,count(c.id) as team_num')
            ->join("LEFT JOIN qz_cases as c on c.uid=t11.id and c.isdelete in(1,3) and c.on=1 and c.classid=3")
            ->group('t11.id')
            ->buildSql();


        //1*量房数+5*签单数+0.1*案例数+0.2*团队人数+0.2*好评数+0.5*问答回答数
        $buildSql1 = $Db->table($buildSql)->alias('t')
            ->field('t.*, uc1.liangfang,uc1.haoping,uc1.qiandan,uc1.casesnum,uc1.answernum ,uc1.designnum,uc1.ping')
//            ->join("left join qz_user_company_rank uc1 on uc1.comid = t.id and uc1.day between '".$start_time."' and  '".$end_time."'")
            ->join("left join qz_user_company_rank uc1 on uc1.comid = t.id and uc1.day = '{$yesterday}'")
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
            ->limit($pagesize.",".$pageRow)
            ->select();
        return array("result"=>$result);
    }

    /**
     * 获取量房排行
     * @param string $condition
     * @param int $pagesize
     * @param int $pageRow
     * @return array
     */
    public function getLiangfangList($condition=[],$pagesize= 1,$pageRow = 10){
        if(!empty($condition['cs'])){
            $map["cs"] = array("EQ",$condition['cs']);

        }
        $map["q.is_show"] = array("EQ",1);
        $map["classid"] = array("IN",array(3,6));
        $map['a.qc'] = array('exp', 'IS NOT NULL');
        $map['a.qc'] = array('NEQ', '');



        // feature-1009    合肥首页/分站排除会员40165
        if (isset($condition['hiddenid']) && (count($condition['hiddenid']) > 0)) {
            $map['a.id'] = array('not in', $condition['hiddenid']);
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
            $fw = (int)$condition['fw'];
            $map["_string"] = "a.qx = $fw";
        }
        if(!empty($condition['fg'])){
            $fg = (int)$condition['fg'];
            $map["_string"] = "FIND_IN_SET($fg,q.fengge)";
        }
        if(!empty($condition['bz'])){
            $bz = (int)$condition['bz'];
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
        if(!empty($condition['sale'])){
            if($condition['sale'] == 1){
                $isSale = "AND a.sales_count > '0'  ";
            }elseif($condition['sale'] == 2){
                $isSale = '';
            }
        }else{
            $isSale = '';
        }
        if (!empty($condition['sale'])) {
            //点击优惠时 筛除假会员
            $map['a.on'] = array('eq', 2);
            $map['q.fake'] = array('eq', 0);
        }

        //获取前30天数据
        $rand_time  = $this->getlastthirtyDays();
        $start_time = $rand_time["start"];
        $end_time = $rand_time["end"];

        $yesterday = date('Y-m-d',strtotime("-1 day"));

        $Db = M('user');
        if(!empty($condition['sale']) && $condition['sale'] == 2){          //获取有优惠券的公司列表
            $map['d1.start'] = array('ELT',time());
            $map['d1.end'] = array('EGT',time());
            $map['d1.check'] = array('EQ',2);
            $map['d1.apply_state'] = array('EQ',1);
            $map['_string'] = '(c1.enable =1 or (c1.enable = 2 and c1.disable_time >'.time().'))'; //未禁用或禁用时间未到
            $buildSql = $Db->where($map)->alias("a")
                ->field("a.`id`,a.classid,a.on,a.qc,a.jc,a.pv,a.uv,a.cs,a.logo,a.uptime,a.info_time,a.case_count,q.team_count,q.comment_count,q.comment_score,q.fake,a.sales_count,a.activte_score,e.bm,q.id as comid,a.dz,q.team_num,q.img,a.register_time,e.cname as cityname,z.qz_area as area_name,q.cooperate_mode")
                ->join("INNER JOIN qz_user_company as q on q.userid = a.id $isSale ")
                ->join("INNER JOIN qz_quyu as e on e.cid = a.cs")
                ->join("LEFT JOIN qz_area as z on a.qx = z.qz_areaid")
                ->join("INNER JOIN qz_card_com  b1 on b1.com_id = a.id")
                ->join("INNER JOIN qz_card c1 on c1.id = b1.card_id")
                ->join("INNER JOIN qz_card_com_record d1 on d1.card_com_id = b1.id")
                ->buildSql();
        }else{
            $buildSql = $Db->where($map)->alias("a")
                ->field("a.`id`,a.classid,a.on,a.qc,a.jc,a.pv,a.uv,a.cs,a.logo,a.uptime,a.info_time,a.case_count,q.team_count,q.comment_count,q.comment_score,q.fake,a.sales_count,a.activte_score,e.bm,q.id as comid,a.dz,q.team_num,q.img,a.register_time,e.cname as cityname,z.qz_area as area_name,q.cooperate_mode")
                ->join("INNER JOIN qz_user_company as q on q.userid = a.id $isSale ")
                ->join("INNER JOIN qz_quyu as e on e.cid = a.cs")
                ->join("LEFT JOIN qz_area as z on a.qx = z.qz_areaid")
                ->buildSql();
        }
        //先按照【量房时间排序，最新量房放最上面】再【店铺创建时间】
        $buildSql1 = $Db->table($buildSql)->alias('t')
            ->field('t.*,cr.time as liangfang_time,o.xiaoqu as liangfang_xiaoqu')
            ->join("left join qz_order_company_review cr on cr.comid = t.id and cr.status=1")
            ->join("left join qz_orders o on o.id = cr.orderid")
            ->order('cr.time desc')
            ->buildSql();

        //1*量房数+5*签单数+0.1*好评数+10*店铺完成度
        $buildSql1 = $Db->table($buildSql1)->alias('t')
            ->field('t.*, uc1.liangfang,uc1.haoping,uc1.qiandan,uc1.wanzhengdu,uc1.ping')
//            ->join("left join qz_user_company_rank uc1 on uc1.comid = t.id and uc1.day between '".$start_time."' and  '".$end_time."'")
            ->join("left join qz_user_company_rank uc1 on uc1.comid = t.id and uc1.day = '{$yesterday}'")
            ->order("day desc")
            ->buildSql();

        $buildSql2 = $Db->table($buildSql1)->alias('t1')
            ->group("t1.comid")
            ->buildSql();

        $result = $Db->table($buildSql2)->alias('t2')
            ->field('
            t2.*,(t2.haoping/t2.ping) as haopinglv,
            case
            when t2.on = 2 and t2.fake=0 then 1
            when t2.on <> 2 and t2.fake=0  then 2
            else 3 end paixu
            ')
            ->order('paixu,liangfang_time desc,t2.register_time')
            ->limit($pagesize.",".$pageRow)
            ->select();

        return array("result"=>$result);
    }

    /**
     * 获取签单排行
     * @param string $condition
     * @param int $pagesize
     * @param int $pageRow
     * @return array
     */
    public function getQiandanList($condition=[],$pagesize= 1,$pageRow = 10, $from = ""){
        if(!empty($condition['cs'])){
            $map["cs"] = array("EQ",$condition['cs']);
        }
        $map["q.is_show"] = array("EQ",1);
        $map["classid"] = array("IN",array(3,6));
        $map['a.qc'] = array('exp', 'IS NOT NULL');
        $map['a.qc'] = array('NEQ', '');

        if(!empty($condition['keyword'])){
            $map['qc']  = array('LIKE','%'.$condition['keyword'].'%');
        }

        // feature-1009    合肥首页/分站排除会员40165
        if (isset($condition['hiddenid']) && (count($condition['hiddenid']) > 0)) {
            $map['a.id'] = array('not in', $condition['hiddenid']);
        }

        //认证
        if(!empty($condition['vip'])){
            $map["on"] = array("EQ",2);
            //$map['fake'] = array('EQ',0);
        }
        if(!empty($condition['fw'])){
            $fw = (int)$condition['fw'];
            $map["_string"] = "a.qx = $fw";
        }
        if(!empty($condition['fg'])){
            $fg = (int)$condition['fg'];
            $map["_string"] = "FIND_IN_SET($fg,q.fengge)";
        }
        if(!empty($condition['bz'])){
            $bz = (int)$condition['bz'];
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
        if(!empty($condition['sale'])){
            if($condition['sale'] == 1){
                $isSale = "AND a.sales_count > '0'  ";
            }elseif($condition['sale'] == 2){
                $isSale = '';
            }
        }else{
            $isSale = '';
        }
        if (!empty($condition['sale'])) {
            //点击优惠时 筛除假会员
            $map['a.on'] = array('eq', 2);
            $map['q.fake'] = array('eq', 0);
        }

        //获取前30天数据
        $rand_time  = $this->getlastthirtyDays();
        $start_time = $rand_time["start"];
        $end_time = $rand_time["end"];

        $yesterday = date('Y-m-d',strtotime("-1 day"));

        $Db = M('user');
        if(!empty($condition['sale']) && $condition['sale'] == 2){          //获取有优惠券的公司列表
            $map['d1.start'] = array('ELT',time());
            $map['d1.end'] = array('EGT',time());
            $map['d1.check'] = array('EQ',2);
            $map['d1.apply_state'] = array('EQ',1);
            $map['_string'] = '(c1.enable =1 or (c1.enable = 2 and c1.disable_time >'.time().'))'; //未禁用或禁用时间未到
            $build = $Db->where($map)->alias("a")
                ->field("a.`id`,a.classid,a.on,a.qc,a.jc,a.pv,a.uv,a.cs,a.logo,a.uptime,a.info_time,a.case_count,q.team_count,q.comment_count,q.comment_score,q.fake,a.sales_count,a.activte_score,e.bm,q.id as comid,a.dz,q.img,a.register_time,e.cname as cityname,z.qz_area as area_name,q.cooperate_mode")
                ->join("INNER JOIN qz_user_company as q on q.userid = a.id $isSale ")
                ->join("INNER JOIN qz_quyu as e on e.cid = a.cs")
                ->join("LEFT JOIN qz_area as z on a.qx = z.qz_areaid")
                ->join("INNER JOIN qz_card_com  b1 on b1.com_id = a.id")
                ->join("INNER JOIN qz_card c1 on c1.id = b1.card_id")
                ->join("INNER JOIN qz_card_com_record d1 on d1.card_com_id = b1.id")
                ->buildSql();
        }else{
            $build = $Db->where($map)->alias("a")
                ->field("a.`id`,a.classid,a.on,a.qc,a.jc,a.pv,a.uv,a.cs,a.logo,a.uptime,a.info_time,a.case_count,q.team_count,q.comment_count,q.comment_score,q.fake,a.sales_count,a.activte_score,e.bm,q.id as comid,a.dz,q.img,a.register_time,e.cname as cityname,z.qz_area as area_name,q.cooperate_mode")
                ->join("INNER JOIN qz_user_company as q on q.userid = a.id $isSale ")
                ->join("INNER JOIN qz_quyu as e on e.cid = a.cs")
                ->join("LEFT JOIN qz_area as z on a.qx = z.qz_areaid")
                ->buildSql();
        }

        $buildSql = $Db->table($build)->alias('t11')
            ->field('t11.*,count(c.id) as team_num')
            ->join("LEFT JOIN qz_cases as c on c.uid=t11.id and c.isdelete in(1,3) and c.on=1 and c.classid=3")
            ->group('t11.id')
            ->buildSql();

        $buildSql1 = $Db->table($buildSql)->alias('t')
            ->field('t.*,o.qiandan_addtime,o.xiaoqu,o.qiandan_chktime')
            ->join("left join qz_orders o on o.qiandan_companyid = t.id and o.qiandan_status=1")
            ->order("o.qiandan_addtime desc")
            ->buildSql();

        //1*量房数+5*签单数+0.1*好评数+10*店铺完成度
        $buildSql1 = $Db->table($buildSql1)->alias('t')
            ->field('t.*, uc1.liangfang,uc1.haoping,uc1.qiandan,uc1.wanzhengdu,uc1.ping')
//            ->join("left join qz_user_company_rank uc1 on uc1.comid = t.id and uc1.day between '".$start_time."' and  '".$end_time."'")
            ->join("left join qz_user_company_rank uc1 on uc1.comid = t.id and uc1.day = '{$yesterday}'")
            ->order("day desc,qiandan_addtime desc")
            ->buildSql();

       // 先按照【签单时间排序，最新签单放最上面】再【店铺创建时间】
        $buildSql2 = $Db->table($buildSql1)->alias('t1')
            ->group('t1.id')
            ->buildSql();

        // 分站首页最新签单排行按签单审核时间倒叙
        if (!empty($from) && $from == "subIndex") {
            $order = 'paixu,qiandan_chktime desc,qiandan_addtime desc';
        } else {
            $order = 'paixu,qiandan_addtime desc,t2.register_time';
        }

        $result = $Db->table($buildSql2)->alias('t2')
            ->field('
            t2.*,(t2.haoping/t2.ping) as haopinglv,
            case
            when t2.on = 2 and t2.fake=0 then 1
            when t2.on <> 2 and t2.fake=0  then 2
            else 3 end paixu
            ')
            ->order($order)
            ->limit($pagesize.",".$pageRow)
            ->select();

        return array("result"=>$result);
    }

    public function getlastthirtyDays(){
        $firstday=date('Y-m-d',strtotime("-1 month"));
        $lastday=date('Y-m-d');
        return ['start'=>$firstday,'end'=>$lastday];
    }

    public function getCompanys($map=[],$order_by='id desc'){
        return M('user')->alias("a")
            ->field("a.id,a.case_count,a.qc")
            ->where($map)
            ->order($order_by)
            ->select();
    }

    /**
     * 获取用户信息列表数量
     * @param  string $keyword [关键字]
     * @param  string $cs      [所在城市]
     * @return [type]          [description]
     */
    public function getCompanyCount($condition){
        $map["classid"] = array("IN",array(3,6));

        if(!empty($condition['cs'])){
            $map["cs"] = array("EQ",$condition['cs']);
        }
        if(!empty($condition['keyword'])){
            $map['qc']  = array('LIKE','%'.$condition['keyword'].'%');
        }
        //认证
        if(!empty($condition['vip'])){
            $map["on"] = array("EQ",2);
            $map['fake'] = array('EQ',0);
        }
        if(!empty($condition['fw'])){
            $fw = $condition['fw'];
            $map["a.qx"] = array("EQ",$fw);
        }
        if(!empty($condition['fg'])){
            $fg = (int)$condition['fg'];
            $map["_string"] = "FIND_IN_SET($fg,q.fengge)";
        }
        if(!empty($condition['bz'])){
            $bz = (int)$condition['bz'];
            $map["_string"] = "FIND_IN_SET($bz,q.baozhang)";
        }
        if(!empty($condition['gm'])){
            $map["q.guimo"] = array("EQ",$condition['gm']);
        }

        $isSale = !empty($condition['sale']) ? "AND a.sales_count > '0'  " : '';

        $result = M('user')->where($map)->alias("a")
                    ->field("a.`id`,a.on,a.qc,a.pv,a.uv,a.cs,a.logo,a.uptime,a.info_time,a.case_count,q.team_count,q.comment_count,q.comment_score,q.fake,a.sales_count")
                    ->join("INNER JOIN qz_user_company as q on q.userid = a.id $isSale ")
                    ->count();

        return $result;
    }


    //642  根据条件查询所有公司？
    /**
     * 获取用户信息列表数量
     * @param  string $keyword [关键字]
     * @param  string $cs      [所在城市]
     * @return [type]          [description]
     */
    public function getCompanyCount2($condition, $keyword='',$classid=3){
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

    // rank取整
    public function getIntData($data){
        if(empty($data) || !isset($data)){
            return false;
        }
        foreach ($data as $key => $val) {
            $data[$key]['rank'] = $val['rank']?(int)$val['rank']:'0';
        }
        return $data;
    }

    /**
     * getCompanySafeTelByComId  根据装修公司id获取安全手机号
     * @param $comid
     */
    public function getCompanySafeTelByComId($comid){
        if(!$comid){
            return false;
        }
        return M('user')->where(array('id'=>$comid ,'tel_safe_chk'=>1))->getfield('tel_safe');
    }


    /**
     * signLiangFang    标记量房
     * @param $map
     * @param $savedata
     */
    public function signLiangFang($map,$savedata){
        $return = M('order_company_review')->where($map)->Save($savedata);
        if($return === false){
            return false;
        }else{
            return true;
        }
    }

    //p.2.17.3
    public function getHaopingById($id){
        $rand_time  = $this->getlastthirtyDays();
        $start_time = $rand_time["start"];
        $end_time = $rand_time["end"];

        $yesterday = date('Y-m-d',strtotime("-1 day"));

        $Db = M('user');
        $map['uc1.comid'] = array("EQ",$id);
        $buildSql1 = $Db->alias('t')->where($map)
            ->field('t.id,uc1.haoping,uc1.casesnum,uc1.designnum,uc1.ping')
//            ->join("left join qz_user_company_rank uc1 on uc1.comid = t.id and uc1.day between '" . $start_time . "' and  '" . $end_time . "'")
            ->join("left join qz_user_company_rank uc1 on uc1.comid = t.id and uc1.day = '{$yesterday}'")
            ->order("day desc")
            ->buildSql();

        return  $Db->table($buildSql1)->alias('t2')
            ->field('t2.*')
            ->group('t2.id')
            ->select();
    }

    public function getStarById($id){
        $map['uc.userid'] = array("EQ",$id);
        $Db = M('user');
        return $Db->where($map)->alias('t')
            ->field("comment_score,comment_count")
            ->join("left join qz_user_company uc on uc.userid = t.id")
            ->find();
    }

    public function getZjcountByID($id){
        $map['c.uid'] = array("EQ",$id);
        $Db = M('user');
        return $Db->alias('t')->where($map)
            ->field('count(c.id) as zj_num')
            ->join("LEFT JOIN qz_cases as c on c.uid=t.id and c.isdelete in(1,3) and c.on=1 and c.classid=3")
            ->group('t.id')
            ->select();
    }

    public function getFiveCompany($id,$qx)
    {
        $rand_time = $this->getlastthirtyDays();
        $start_time = $rand_time["start"];
        $end_time = $rand_time["end"];
        $yesterday = date('Y-m-d',strtotime("-1 day"));

        $map["t.classid"] = array("EQ", 3);
        $map["t.qx"] = array("EQ", $qx);
        $map['t.id'] = array("NEQ", $id);
        $Db = M('user');
        $buildSql1 = $Db->alias('t')->where($map)
            ->field('t.id,t.jc,(uc1.haoping/uc1.ping) as haopinglv')
//            ->join("left join qz_user_company_rank uc1 on uc1.comid = t.id and uc1.day between '" . $start_time . "' and  '" . $end_time . "'")
            ->join("left join qz_user_company_rank uc1 on uc1.comid = t.id and uc1.day = '{$yesterday}'")
            ->order("uc1.day desc")
            ->limit(5)
            ->buildSql();

        return $Db->table($buildSql1)->alias('t2')
            ->field('t2.*')
            ->group('t2.id')
            ->select();
    }


    public function getKaopu($id,$cs,$on='',$qx='',$fake=''){
        $map["cs"] = array("EQ",$cs);
        $map["a.id"] = array("not in",$id);
        $map["u.is_show"] = array("EQ",1);
        if($on == 2){
            $map["on"] = array("EQ",2);
        }
        if($qx != 0){
            $map["qx"] = array("EQ",$qx);
        }
        if($fake !== ''){
            $map["u.fake"] = array("EQ",$fake);
        }
        return M('user a')->where($map)
            ->join("inner join qz_user_company u on u.userid = a.id")
            ->field('a.id,jc')
            ->select();
    }

    public function getNewDesignerByCompanyId($company_id = [])
    {
        if(count($company_id) == 0){
            return [];
        }
        $where = [
            'u.company_id' => ['in', $company_id],
            'u.position' => ['in', [2, 3, 4]],
            'u.state' => ['eq', 1],
        ];
        return M('user_company_employee')->alias('u')
            ->where($where)
            ->field("count(u.id) num,u.company_id")
            ->group('u.company_id')
            ->select();
    }

    /**
     * @des:获取当前企业所在区域的最新20个装企
     * @param $cs
     * @return array|false|mixed|string|null
     */
    public function getNewCompanyByCid($cs)
    {
        $map['user.cs'] = array("EQ", $cs);
        $map['user.classid'] = array("EQ", 3);
        $map['user.account_chk'] = array("EQ", 1);
        $map['company.is_show'] = array("EQ", 1);
        $Db = M('user');
        return $Db->alias('user')->where($map)
            ->join('INNER JOIN qz_user_company as company on company.userid = user.id')
            ->field('user.jc, user.qc, user.id')
            ->order('user.register_time DESC')
            ->limit(20)
            ->select();
    }

    /**
     *  @des:获取装企宣传广告图
     * @param $map
     * @return mixed
     */
    public function getCompanyBanner($map)
    {
        return M('company_banners')->field('id,userid,img_path')->order('sorted,id DESC')->where($map)->select();
    }

    /**
     *  @des:根据装修公司id获取轮播图
     * @param $map
     * @param int $limit
     * @return mixed
     */
    public function getCompanyBannerInfo($map, $limit = 5)
    {
        return M('company_banners')->field('id,userid,img_path')->order('sorted,id DESC')->where($map)->limit($limit)->select();
    }
}