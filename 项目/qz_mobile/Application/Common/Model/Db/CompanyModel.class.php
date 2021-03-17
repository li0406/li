<?php
namespace Common\Model\Db;

use Think\Model;

class CompanyModel extends Model
{
    protected $autoCheckFields = false;


    //根据装修公司id获取装修公司信息
    public function getCompanyInfoByComId($comid)
    {
        $where['user.id'] = $comid;
        return M('user')->alias('user')
            ->where($where)
            ->field('is_show')
            ->join('INNER JOIN qz_user_company as company on company.userid = user.id')
            ->find();
    }

    //根据装修公司id获取装修公司基本信息
    public function getCompanyBaseInfoById($id)
    {
        $map = [
            'u.id' => $id
        ];

        return M('user')->alias('u')
            ->field('u.user,u.classid,com.cooperate_mode,ac.deposit_money')
            ->join('INNER JOIN qz_user_company as com ON com.userid=u.id')
            ->join('LEFT JOIN qz_user_company_account AS ac ON ac.user_id=u.id')
            ->where($map)
            ->find();
    }

    //获取公司营业执照
    public function getBusinessLicence($id){
        $where["company_id"] = ['eq',$id];
        $where["type"] = ['eq',1];
        $where["state"] = ['eq',4];
        return M('sale_business_licence')->field('img1,img2,img3,img4')->where($where)->find();
    }

    //获取公司标签
    public function getCompanyTags($id, $mode=1){
        $where['t.company_id'] = ['eq', $id];
        if($mode == 2){
            $where['r.tag_mode'] = 2;
            $limit = 3;
        }else{
            $where['r.tag_mode'] = 1;
            $limit = 0;
        }
        return M('company_tags')->alias('t')->field('r.tag')->join('qz_company_relation_tag as r on r.id = t.tag')->where($where)->limit($limit)->order('r.id DESC')->select();
    }

    //获3条公司标签
    public function getCompanyTagsAtLimit($id, $limit=3){
        $where['t.company_id'] = ['eq', $id];

        return M('company_tags')->alias('t')->field('r.tag')->join('qz_company_relation_tag as r on r.id = t.tag')->where($where)->limit($limit)->order('r.tag_mode DESC,r.id DESC')->select();
    }


    public function getlastthirtyDays(){
        $firstday=date('Y-m-d',strtotime("-1 month"));
        $lastday=date('Y-m-d');
        return ['start'=>$firstday,'end'=>$lastday];
    }

    public function getRecentlyDays(){
        $firstday = $lastday = date('Y-m-d',strtotime('-1 day'));
        return ['start'=>$firstday, 'end'=>$lastday];
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
        // $rand_time  = $this->getRecentlyDays();
        $start_time = $rand_time["start"];
        $end_time = $rand_time["end"];

        $Db = M('user');
        $buildSql = $Db->where($map)->alias("a")
            ->field("a.`id`,a.on,a.qc,a.jc,a.pv,a.uv,a.cs,a.kouhao,a.logo,a.uptime,a.register_time,a.info_time,a.case_count,q.team_count,q.img,q.img_host,q.comment_count,q.comment_score,q.fake,a.sales_count,a.activte_score,e.bm,q.id as comid,a.dz,
            q.team_num,case when a.on = 2 and q.fake = 0 then 1 when a.`on` != 2 and q.fake = 0 then 2 else 3 end paixu, if(q.img != '', 1, 0) com_img_count,count(com_ac.id) activity_count,count(card_com.id) card_count,a.classid,q.cooperate_mode,ac.deposit_money")
            ->join("INNER JOIN qz_user_company as q on q.userid = a.id ")
            ->join("INNER JOIN qz_quyu as e on e.cid = a.cs")
            ->join('LEFT JOIN qz_user_company_account AS ac ON ac.user_id=a.id')
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

        // 获取在建工地数量?
        $buildSql = $Db->table($buildSql)->alias('r')
            ->field('r.*,count(cases.id) case_des_count')
            ->join('LEFT JOIN qz_cases as cases on cases.uid = r.id and cases.on = 1 and cases.classid = 3 and cases.isdelete in (1, 3)')
            ->group('r.id')
            ->buildSql();

        //1*量房数+5*签单数+0.1*好评数+10*店铺完成度
        $rankWhere = sprintf(" and day = '%s'", date('Y-m-d', strtotime('-1 day')));
        $buildSql1 = $Db->table($buildSql)->alias('t')
            ->field('t.*, uc1.liangfang,uc1.haoping,uc1.qiandan,uc1.wanzhengdu,uc1.ping')
            ->join("left join qz_user_company_rank uc1 on uc1.comid = t.id" . $rankWhere)
            ->order("day desc")
            ->buildSql();

        $buildSql2 =  $Db->table($buildSql1)->alias('t2')
            ->field('t2.*,sum( t2.liangfang) as liangfang_num,t2.haoping as haoping_num,t2.ping as ping_num,sum( t2.qiandan) as qiandan_num')
            ->group('t2.id')
            ->buildSql();

        $result = $Db->table($buildSql2)->alias('t1')
            ->field('t1.*,
            (t1.liangfang_num+5*t1.qiandan_num+0.1*t1.haoping_num+10*t1.wanzhengdu) as order_fenshu,(t1.haoping_num/t1.ping_num) as haopinglv,
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
        $map["q.is_show"] = array("EQ",1);
        $map["classid"] = array("IN",array(3,6));
        $map['a.qc'] = array('exp', 'IS NOT NULL');
        $map['a.qc'] = array('NEQ', '');
        if(!empty($condition['cs'])){
            $map["cs"] = array("EQ",$condition['cs']);

        }

        // feature-1009    合肥首页/分站排除会员40165
        if (isset($condition['hiddenid']) && ($condition['hiddenid'] > 0)) {
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
        $Db = M('user');
        $buildSql = $Db->where($map)->alias("a")
            ->field("a.`id`,a.on,a.qc,a.jc,a.pv,a.uv,a.cs,a.kouhao,a.logo,a.uptime,a.register_time,a.info_time,a.case_count,q.team_count,q.img,q.img_host,q.comment_count,q.comment_score,q.fake,a.sales_count,a.activte_score,e.bm,q.id as comid,a.dz,
            q.team_num,case when a.on = 2 and q.fake = 0 then 1 when a.`on` != 2 and q.fake = 0 then 2 else 3 end paixu, if(q.img != '', 1, 0) com_img_count,count(com_ac.id) activity_count,count(card_com.id) card_count,a.classid,q.cooperate_mode,ac.deposit_money")
            ->join("INNER JOIN qz_user_company as q on q.userid = a.id ")
            ->join("INNER JOIN qz_quyu as e on e.cid = a.cs")
            ->join('LEFT JOIN qz_user_company_account AS ac ON ac.user_id=a.id')
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

        // 获取在建工地数量?
        $buildSql = $Db->table($buildSql)->alias('r')
            ->field('r.*,count(cases.id) case_des_count')
            ->join('LEFT JOIN qz_cases as cases on cases.uid = r.id and cases.on = 1 and cases.classid = 3 and cases.isdelete in (1, 3)')
            ->group('r.id')
            ->buildSql();

        $rankWhere = sprintf(" and day = '%s'", date('Y-m-d', strtotime('-1 day')));
        //1*量房数+5*签单数+0.1*好评数+10*店铺完成度
        $buildSql1 = $Db->table($buildSql)->alias('t')
            ->field('t.*, uc1.liangfang,uc1.haoping,uc1.qiandan,uc1.wanzhengdu,uc1.ping')
            ->join("left join qz_user_company_rank uc1 on uc1.comid = t.id" . $rankWhere)
            ->order("uc1.id desc")
            ->buildSql();

        //先按照【量房时间排序，最新量房放最上面】再【店铺创建时间】
        $buildSql1 = $Db->table($buildSql1)->alias('t')
            ->field('t.*,cr.time as liangfang_time,o.xiaoqu as liangfang_xiaoqu')
            ->join("left join qz_order_company_review cr on cr.comid = t.id and cr.status=1")
            ->join("left join qz_orders o on o.id = cr.orderid")
            ->order('cr.time desc')
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
        $map["q.is_show"] = array("EQ",1);
        $map["classid"] = array("IN",array(3,6));
        $map['a.qc'] = array('exp', 'IS NOT NULL');
        $map['a.qc'] = array('NEQ', '');
        if(!empty($condition['cs'])){
            $map["cs"] = array("EQ",$condition['cs']);
        }

        // feature-1009    合肥首页/分站排除会员40165
        if (isset($condition['hiddenid']) && ($condition['hiddenid'] > 0)) {
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
        $Db = M('user');
        $build = $Db->where($map)->alias("a")
            ->field("a.`id`,a.on,a.qc,a.jc,a.pv,a.uv,a.cs,a.kouhao,a.logo,a.uptime,a.register_time,a.info_time,a.case_count,q.team_count,q.img,q.img_host,q.comment_count,q.comment_score,q.fake,a.sales_count,a.activte_score,e.bm,q.id as comid,a.dz,
            q.team_num,case when a.on = 2 and q.fake = 0 then 1 when a.`on` != 2 and q.fake = 0 then 2 else 3 end paixu, if(q.img != '', 1, 0) com_img_count,count(com_ac.id) activity_count,count(card_com.id) card_count,a.classid,q.cooperate_mode,ac.deposit_money")
            ->join("INNER JOIN qz_user_company as q on q.userid = a.id ")
            ->join("INNER JOIN qz_quyu as e on e.cid = a.cs")
            ->join('LEFT JOIN qz_user_company_account AS ac ON ac.user_id=a.id')
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


        $buildSql = $Db->table($build)->alias('t11')
            ->field('t11.*,count(c.id) as case_des_count')
            ->join("LEFT JOIN qz_cases as c on c.uid=t11.id and c.isdelete in(1,3) and c.on=1 and c.classid=3")
            ->group('t11.id')
            ->buildSql();

        $buildSql1 = $Db->table($buildSql)->alias('t')
            ->field('t.*,o.qiandan_addtime,o.xiaoqu,o.qiandan_chktime')
            ->join("left join qz_orders o on o.qiandan_companyid = t.id and o.qiandan_status=1")
            ->order("o.qiandan_addtime desc")
            ->buildSql();

        //1*量房数+5*签单数+0.1*好评数+10*店铺完成度
        $rankWhere = sprintf(" and day = '%s'", date('Y-m-d', strtotime('-1 day')));
        $buildSql1 = $Db->table($buildSql1)->alias('t')
            ->field('t.*, uc1.liangfang,uc1.haoping,uc1.qiandan,uc1.wanzhengdu,uc1.ping')
            ->join("left join qz_user_company_rank uc1 on uc1.comid = t.id" . $rankWhere)
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


    /**
     * 获取综合排行
     * @param string $condition
     * @param int $pagesize
     * @param int $pageRow
     * @return array
     */
    public function getShiliList($condition=[],$pagesize= 1,$pageRow = 10){
        $map["q.is_show"] = array("EQ",1);
        $map["classid"] = array("IN",array(3,6));
        $map['a.qc'] = array('exp', 'IS NOT NULL');
        $map['a.qc'] = array('NEQ', '');
        if(!empty($condition['cs'])){
            $map["cs"] = array("EQ",$condition['cs']);
        }

        // feature-1009    合肥首页/分站排除会员40165
        if (isset($condition['hiddenid']) && ($condition['hiddenid'] > 0)) {
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
        $Db = M('user');

        $build = $Db->where($map)->alias("a")
            ->field("a.`id`,a.on,a.qc,a.jc,a.pv,a.uv,a.cs,a.kouhao,a.logo,a.uptime,a.register_time,a.info_time,a.case_count,q.team_count,q.img,q.img_host,q.comment_count,q.comment_score,q.fake,a.sales_count,a.activte_score,e.bm,q.id as comid,a.dz,
            q.team_num,case when a.on = 2 and q.fake = 0 then 1 when a.`on` != 2 and q.fake = 0 then 2 else 3 end paixu, if(q.img != '', 1, 0) com_img_count,count(com_ac.id) activity_count,count(card_com.id) card_count,a.classid,q.cooperate_mode,ac.deposit_money")
            ->join("INNER JOIN qz_user_company as q on q.userid = a.id ")
            ->join("INNER JOIN qz_quyu as e on e.cid = a.cs")
            ->join('LEFT JOIN qz_user_company_account AS ac ON ac.user_id=a.id')
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

        $buildSql = $Db->table($build)->alias('t11')
            ->field('t11.*,count(c.id) as case_des_count')
            ->join("LEFT JOIN qz_cases as c on c.uid=t11.id and c.isdelete in(1,3) and c.on=1 and c.classid=3")
            ->group('t11.id')
            ->buildSql();


        //1*量房数+5*签单数+0.1*案例数+0.2*团队人数+0.2*好评数+0.5*问答回答数
        $rankWhere = sprintf(" and day = '%s'", date('Y-m-d', strtotime('-1 day')));
        $buildSql1 = $Db->table($buildSql)->alias('t')
            ->field('t.*, uc1.liangfang,uc1.haoping,uc1.qiandan,uc1.casesnum,uc1.answernum ,uc1.designnum,uc1.ping')
            ->join("left join qz_user_company_rank uc1 on uc1.comid = t.id" . $rankWhere)
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
     * 获取口碑列表
     * @param $cs
     * @param int $num
     * @return mixed
     */
    public function getSubKoubeiRank($cs, $num = 5)
    {
        !empty($cs) && $map['a.cs'] = array('eq', $cs);
        if(!empty($cs)){
            //分站
            $where = [
                'a.classid' => ['in', [3, 6]],
                'a.`cs`' => ['in', $cs],
                //排除不显示的公司
                'b.`is_show`' => ['eq', 1],
            ];
        }else{
            //全国
            $where = [
                'a.classid' => ['in', [3, 6]],
                'a.on' => ['eq', 2],
                'b.fake' => ['eq', 0],
                //排除不显示的公司
                'b.`is_show`' => ['eq', 1],
            ];
        }
        $rankWhere = [
            'c.`day`' => ['eq', date('Y-m-d', strtotime('-1 day'))]
        ];

        $buildSql = M('user')->alias('a')
            ->field('a.id,a.qc,a.jc,a.register_time,a.logo,a.activte_score,a.case_count,a.cs,a.on,b.fake')
            ->where($where)
            ->join('INNER JOIN qz_user_company b on a.id = b.userid and b.fake = 0')
            ->buildSql();

        $buildSql = M()->table($buildSql)->alias("t")
            ->where($rankWhere)
            ->field('t.*,`day`,comid,liangfang,qiandan,haoping,wanzhengdu,ping,casesnum,loginnum,answernum,designnum')
            ->join('qz_user_company_rank c on c.comid = t.id')
            ->order('c.day desc')
            ->buildSql();

        $buildSql = M()->table($buildSql)->alias("r")
            ->group("comid")
            ->buildSql();

        $field = 'a.*,q.bm,((a.haoping / a.ping) *10) as comment_score,
        (1 * a.liangfang + 5 * a.qiandan + 0.1 * a.haoping + 0.1 * a.wanzhengdu) AS `rank`,
        case when a.on = 2 and a.fake=0 then 1 when a.on <> 2 and a.fake=0  then 2 else 3 end paixu';
        return M()->table($buildSql)->alias('a')
            ->join("qz_quyu as q on a.cs = q.cid ")
            ->field($field)
            ->order('paixu,rank DESC,a.register_time')
            ->limit($num)
            ->select();
    }

    /**
     * 根据装修公司id获取评论星星
     * @param $comId
     * @return mixed
     */
    public function getCompanyScoreInfo($comId)
    {
        $map = [];
        $map["g.comid"] = $comId;
        $info = M("comment")->alias("g")
            ->where($map)
            ->field("count(g.id) as ccount,count(IF(g.sj <>0,g.id,null)) as newcount,count(IF(g.sj =0,g.id,null)) as oldcount, count(IF(g.count>=9 and g.sj = 0,g.count,null)) as oldgood, count(IF(g.sj>=9 and g.fw>=9 and g.sg >=9,g.id,null)) as good, avg(IF(g.sj<>0,g.sj,null)) avgsj,avg(IF(g.fw<>0,g.fw,null)) avgfw,avg(IF(g.sg<>0,g.sg,null)) avgsg,avg(IF(g.sj=0,g.count,null)) avgcount")
            ->find();
        return $info;
    }

    /**
     * 根据装修公司id获取评论星星
     * @param $comId
     * @return mixed
     */
    public function getCompanyAllScoreInfo($comIds)
    {
        $map = [
            "g.comid" => array("IN", $comIds)
        ];

        return M("comment")->alias("g")->where($map)
            ->field("g.comid,count(g.id) as ccount,count(IF(g.sj <>0,g.id,null)) as newcount,count(IF(g.sj =0,g.id,null)) as oldcount, count(IF(g.count>=9 and g.sj = 0,g.count,null)) as oldgood, count(IF(g.sj>=9 and g.fw>=9 and g.sg >=9,g.id,null)) as good, avg(IF(g.sj<>0,g.sj,null)) avgsj,avg(IF(g.fw<>0,g.fw,null)) avgfw,avg(IF(g.sg<>0,g.sg,null)) avgsg,avg(IF(g.sj=0,g.count,null)) avgcount")
            ->group("g.comid")
            ->select();
    }


    public function getCompanyBannersById($companyId, $limit = 5)
    {
        $map = [
            'userid' => $companyId,
            'status' => 1
        ];

        return M('company_banners')->field('id,img_path')->order('sorted,id DESC')->where($map)->limit($limit)->select();
    }

    public function getActivityByComId($companyId, $limit=4)
    {
        $map = [
            'cid' => $companyId,
            'check' => 1,
            'types' => 1,
            'del' => 1,
            'state' => 1,
            'end' =>  ['egt', time()],
            'start' => ['elt', time()]
        ];

        return M('company_activity')->field('id,title,start AS stime,2 AS mark')
            ->where($map)
            ->limit($limit)
            ->order('`time` desc')
            ->select();
    }


    // 获取推荐装修公司
    public function getRecommendCompanyList($city_id, $limit = 20, $offset = 0){
        $map = array(
            "u.cs" => array("EQ", $city_id),
            "u.classid" => array("IN", [3, 6]),
            "c.is_show" => array("EQ", 1)
        );

        $date = date("Y-m-d", strtotime("-7 days"));

        $sql = M("user")->alias("u")->where($map)
            ->join("inner join qz_user_company as c on c.userid = u.id")
            ->join("left join qz_user_company_rank as r on r.comid = u.id and r.day >=" . $date)
            ->field([
                "u.id", "u.cs", "u.jc", "u.qc", "u.logo", "c.fake", "c.comment_score", "u.case_count", "c.team_count",
                "r.liangfang", "r.qiandan", "r.haoping", "r.ping", "r.wanzhengdu", "r.loginnum", "r.answernum"
            ])
            ->order("r.day desc")
            ->buildSql();

        $sql2 = M()->table($sql)->alias("t")
            ->group("t.id")
            ->buildSql();

        // 获取在建工地数量
        $sql3 = M()->table($sql2)->alias("t3")
            ->field([
                "t3.*",
                "count(cases.id) case_des_count"
            ])
            ->join('left join qz_cases as cases on cases.uid = t3.id and cases.on = 1 and cases.classid = 3 and cases.isdelete in (1, 3)')
            ->group("t3.id")
            ->buildSql();

        return M()->table($sql3)->alias("t2")
            ->join("left join qz_quyu as q on q.cid = t2.cs")
            ->field([
                "t2.*", "q.bm", "q.cname",
                "(t2.liangfang + t2.qiandan * 5 + t2.haoping * 0.1 + t2.wanzhengdu * 10) * 10 as koubei",
                "(t2.liangfang * 1.5 + t2.wanzhengdu * 10 + t2.loginnum * 0.5 + t2.answernum * 0.5) * 10 as hot"
            ])
            ->order("t2.fake asc,koubei desc,hot desc")
            ->limit($offset, $limit)
            ->select();
    }

    public function getCompanyOtherInfo($id)
    {
        $map = [
            'comid' => $id
        ];
        return M('user_company_rank')->where($map)->order('day desc')->find();
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

    
    public function getZongheShiliCount($condition){
        $map["q.is_show"] = array("EQ",1);
        $map["classid"] = array("IN",array(3,6));
        $map['a.qc'] = array('exp', 'IS NOT NULL');
        $map['a.qc'] = array('NEQ', '');

        if(!empty($condition['cs'])){
            $map["cs"] = array("EQ",$condition['cs']);
        }

        if(!empty($condition['true_user'])){
            $map['a.on'] = array('eq', 2);
            $map['q.fake'] = array('eq', 0);
        }

        //获取前30天数据
        $rand_time  = $this->getlastthirtyDays();
        $start_time = $rand_time["start"];
        $end_time = $rand_time["end"];
        $Db = M('user');

        $build = $Db->where($map)->alias("a")
            ->field("a.`id`,a.on,a.qc,a.jc,a.cs,a.logo,a.register_time,a.case_count,q.comment_count,q.fake,q.id as comid,e.bm,
            case when a.on = 2 and q.fake = 0 then 1 when a.`on` != 2 and q.fake = 0 then 2 else 3 end paixu,a.classid,q.cooperate_mode")
            ->join("INNER JOIN qz_user_company as q on q.userid = a.id ")
            ->join("INNER JOIN qz_quyu as e on e.cid = a.cs")
            ->group('a.id')
            ->buildSql();

        //1*量房数+5*签单数+0.1*案例数+0.2*团队人数+0.2*好评数+0.5*问答回答数
        $rankWhere = sprintf(" and day = '%s'", date('Y-m-d', strtotime('-1 day')));
        $buildSql1 = $Db->table($build)->alias('t')
            ->field('t.*, uc1.liangfang,uc1.haoping,uc1.qiandan,uc1.casesnum,uc1.answernum ,uc1.designnum,uc1.ping')
            ->join("left join qz_user_company_rank uc1 on uc1.comid = t.id" . $rankWhere)
            ->order("day desc")
            ->buildSql();

        $buildSql2 =  $Db->table($buildSql1)->alias('t2')
            ->field('t2.*,sum(t2.liangfang) as liangfang_num,t2.haoping as haoping_num,t2.ping as ping_num,sum(t2.qiandan) as qiandan_num,t2.casesnum as cases_num,t2.answernum as answer_num')
            ->group('t2.id')
            ->buildSql();


        $result = $Db->table($buildSql2)->alias('t1')->count();
        return $result;
    }

    public function getZongheShiliList($condition=[],$pagesize= 1,$pageRow = 10){
        $map["q.is_show"] = array("EQ",1);
        $map["classid"] = array("IN",array(3,6));
        $map['a.qc'] = array('exp', 'IS NOT NULL');
        $map['a.qc'] = array('NEQ', '');

        if(!empty($condition['cs'])){
            $map["cs"] = array("EQ",$condition['cs']);
        }
        
        //获取前30天数据
        $rand_time  = $this->getlastthirtyDays();
        $start_time = $rand_time["start"];
        $end_time = $rand_time["end"];
        $Db = M('user');

        $build = $Db->where($map)->alias("a")
            ->field("a.`id`,a.on,a.qc,a.jc,a.cs,a.logo,a.register_time,a.case_count,q.comment_count,q.fake,q.id as comid,e.bm,
            case when a.on = 2 and q.fake = 0 then 1 when a.`on` != 2 and q.fake = 0 then 2 else 3 end paixu,a.classid,q.cooperate_mode")
            ->join("INNER JOIN qz_user_company as q on q.userid = a.id ")
            ->join("INNER JOIN qz_quyu as e on e.cid = a.cs")
            ->group('a.id')
            ->buildSql();

        //1*量房数+5*签单数+0.1*案例数+0.2*团队人数+0.2*好评数+0.5*问答回答数
        $rankWhere = sprintf(" and day = '%s'", date('Y-m-d', strtotime('-1 day')));
        $buildSql1 = $Db->table($build)->alias('t')
            ->field('t.*, uc1.liangfang,uc1.haoping,uc1.qiandan,uc1.casesnum,uc1.answernum ,uc1.designnum,uc1.ping')
            ->join("left join qz_user_company_rank uc1 on uc1.comid = t.id" . $rankWhere)
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
        return $result;
    }
}