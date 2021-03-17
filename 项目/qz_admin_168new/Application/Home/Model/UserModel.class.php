<?php
/**
 * 注册用户表 对应qz_user表
 */
namespace Home\Model;
Use Think\Model;
class UserModel extends Model{
    protected $autoCheckFields = false;

    // 签返会员类型
    const CLASSID_SIGNBACK = 6;

   /**
    * 根据条件获取用户信息
    * @param  [type]  $query   [查询条件]
    * @param  [type]  $cs      [管辖城市]
    * @param  integer $limit   [获取数量]
    * @param  integer $classid [用户类别]
    * @return [type]           [description]
    */
    public function getUserInfoList($query,$cs,$limit = 10,$classid = 3){
        $map = array(
            "u.classid"=>array("EQ",$classid)
                     );
        if($classid == 3){
            //$map["on"] = array("EQ",2);
            $map["_complex"] = array(
                "u.id"=>array("like","%$query%"),
                "u.jc"=>array("like","%$query%"),
                "u.qc"=>array("like","%$query%"),
                "_logic"=>"OR"
            );
        }else{
            $map["u.name"] = array("like","%$query%");
        }

        $map['u.cs'] = array("IN",$cs);

        return M("user")->alias('u')
                        ->field('u.*,q.cname,q.bm')
                        ->join('LEFT JOIN qz_quyu AS q ON q.cid = u.cs')
                        ->where($map)
                        ->limit($limit)
                        ->order("u.id")->select();
    }

    /**
    * 根据 id 获取用户信息
    * @param  [type] $id [用户id]
    */
    public function getUserById($id){
        $map['id'] = $id;
        return M('user')->field('*')->where($map)->find();
    }

    //根据用户ID取用户信息
    public function getUserCompanyInfoById($id){
        $map['u.id'] = $id;
        return M('user')->alias('u')
            ->field('u.*,c.fake')
            ->join('qz_user_company c on c.userid = u.id')
            ->where($map)->find();
    }

    /**
     * 通过用户ID和分类查询嘻嘻你
     * @param  integer $id      用户ID
     * @param  integer $classid 分类
     */
    public function getUserByIdAndClassid($id = 0, $classid = 0)
    {
        if (empty($id)) {
            return false;
        }
        $map = array(
            'id' => $id
        );
        if (!empty($classid)) {
            $map['classid'] = $classid;
        }
        return M('user')->field('id, qc, jc, video')->where($map)->find();
    }

    /**
    * 根据 City Id 获取城市信息
    * @param  [type] $id [City Id]
    */
    public function getAreaByCityId($id){
        $map['cid'] = $id;
        return M('quyu')->field('*')->where($map)->find();
    }

    /**
    * 根据条件获取用户信息
    * @param  [type] $query [查询条件]
    * @param  [type] $limit [查询数量]
    * @return [type]        [description]
    */
    public function getVipDesignerList($query,$limit = 10){
        $map = array(
            "u.classid"=>array("EQ",'2')
                     );
        $map["_complex"] = array(
            "u.id"=>array("like","%$query%"),
            "u.name"=>array("like","%$query%"),
            "u.user"=>array("like","%$query%"),
            "_logic"=>"OR"
        );

        $cityids = getMyCityIds();
        if(empty($cityids)){
            return false;
        }
        $map['a.cs'] = array("IN",$cityids);

        return M('user')->alias('u')
                        ->field('u.*,a.qc AS company_name,q.cname,t.comid')
                        ->join('INNER JOIN qz_team AS t ON t.userid = u.id')
                        ->join('INNER JOIN qz_user AS a ON a.id = t.comid AND a.on = 2')
                        ->join('INNER JOIN qz_quyu AS q ON q.cid = u.cs')
                        ->where($map)
                        ->limit($limit)
                        ->order("u.id")->select();
    }

    /**
     * 获取城市VIP用户信息
     * @param  [type] $cs [城市]
     * @return [type]     [description]
     */
    public function getVipUserList($cs,$uid,$on) {
        //暂时注销误删
        // $end = strtotime("-1 day", strtotime(date("Y-m-d")));
        // $begin = strtotime("-3 month",$end);
        // $map = array(
        //     "a.login_time" => array("BETWEEN",array($begin,$end))
        //              );

        if (!empty($cs)) {
            $map["a.cs"] = array("IN",$cs);
        }

        if (!empty($uid)) {
           $map["a.classid"] = array("EQ",$uid);
           if ($uid == 3) {
               $map ["b.fake"] = array("EQ",0);
           }
        }

        return M("user")->where($map)->alias("a")
                        ->join("LEFT JOIN qz_user_company as b on b.userid = a.id and b.fake = 0")
                        ->field("a.id,classid,on,name,jc")->order("`on` desc,id")->select();
    }

    /**
     * [getOpenCityHaveRealVipCids 获取已开站且有真会员的城市ID数组]
     * @return [type] [description]
     */
    public function getOpenCityHaveRealVipCids(){
        $map = array(
            'u.on'   => '2',
            'c.fake' => '0'
        );
        $arrays = M('user')->alias('u')
                           ->field('u.cs')
                           ->join('INNER JOIN qz_user_company AS c ON c.userid = u.id')
                           ->group('u.cs')
                           ->where($map)
                           ->select();
        if (!empty($arrays)) {
            $result = array();
            foreach ($arrays as $key => $value) {
                $result[] = $value['cs'];
            }
            return $result;
        }
        return false;
    }

    /**
     * 修改用户视频
     * @param integer $id  用户ID
     * @param string  $url 修改后的地址
     */
    public function setUserVideoByUserId($id = 0, $url = '')
    {
        if (empty($id)) {
            return false;
        }
        $map = array(
            'id' => $id
        );
        return M('user')->where($map)->setField('video', $url);
    }

    /**
     * 获取城市的用户信息
     * @param [type] $citys [所属城市]
     * @param [type] $classId [用户类别]
     * @param [type] $ids     [用户编号的数组]
     */
    public function getUserInfoListByCity($citys,$classId,$fake)
    {
        if (!empty($citys)) {
            $map['a.cs'] = array("IN",$citys);
        }

        if (!empty($classId)) {
            $map['a.classid'] = array("EQ",$classId);
        }

        if (!empty($fake)) {
            $map['b.fake'] = array("EQ",0);
            $map['a.on'] = array("EQ",2);
        }

        return  M("user")->where($map)->alias("a")
                        ->join("join qz_user_company b on a.id = b.userid")
                        ->field("a.id")->select();
    }

    /**
     * 根据ID获取装修公司
     * @param  integer $id 装修公司ID
     * @return array
     */
    public function getCompanyInfoById($id = 0)
    {
        if (empty($id)) {
            return false;
        }
        $map = array(
            'u.id' => $id,
            'u.classid' => array("IN",array(3,6))
        );
        //注意此处连表qz_user_company的ID问题
        return M('user')->alias('u')
                        ->field('u.*, q.cname, a.qz_area')
                        ->join('join qz_quyu q on q.cid = u.cs')
                        ->join('join qz_area a on a.qz_areaid = u.qx')
                        ->where($map)
                        ->find();
    }

    /**
     * 获取有会员的城市
     * @param  [type] $begin [description]
     * @param  [type] $end   [description]
     * @return [type]        [description]
     */
    public function getCitys($begin,$end)
    {
        $map = array(
            "a.time" => array(
                array("EGT",$begin),
                array("ELT",$end)
            )
        );

        return M("log_user_real_company")->where($map)->alias("a")
                                        ->join("join qz_quyu as q on q.cid = a.city_id")
                                        ->field("q.cid,q.cname")->group("a.city_id")->select();
    }

    /**
     * 获取真实会员情况
     * @return [type] [description]
     */
    public function getRealVipCity()
    {
        $map = array(
            "a.on" => array("EQ",2),
            "a.classid" => array('IN',array(3,6))
        );

        return M("user")->where($map)->alias('a')
                 ->join("join qz_user_company b on b.userid = a.id and b.fake = 0")
                 ->join("join qz_quyu q on q.cid = a.cs")
                 ->field("q.cid,q.cname")
                 ->order("q.cid")
                 ->group("a.cs")->select();

    }

    /**
     * 无会员城市列表
     * @return [type] [description]
     */
    public function nomembercity()
    {
        $sql = "select
                q.cname,
                t1.*
                from (
                        select
                        t.cs,
                        count(if(t.on = 2,1,null)) as real_count,
                        count(if(t.on = -1,1,null)) as later_count,
                        max(t.end) as `end`
                        from (
                                    select a.id,a.cs,a.on,a.end from qz_user a
                                    join qz_user_company b on a.id = b.userid and b.fake = 0
                                    where cs <> ''
                        ) t group by t.cs
                ) t1
                join qz_quyu q on q.cid = t1.cs
                where real_count = 0 and later_count > 0 order by t1.end desc";
        return M()->query($sql);
    }


    /**
     * 从未有过会员城市列表
     * @return [type] [description]
     */
    public function nevermembercity()
    {

        $map = array(
            "q.cid" => array("NEQ", "000001")
        );

        $buildSql = M("user")->alias("u")
                        ->join("join qz_user_company c on u.id = c.userid and c.fake = 0")
                        ->field("u.id,u.cs")
                        ->buildSql();

        return M()->table($buildSql)->alias("t")->where($map)
                    ->join("right join qz_quyu q on t.cs = q.cid")
                    ->field("q.id,q.cid cs,q.cname")
                    ->group("q.id")->having("count(t.id) = 0")
                    ->select();
    }

    /**
     * 无会员城市详细
     * @param  [type] $cs [description]
     * @return [type]     [description]
     */
    public function nomembercitydetails($cs)
    {
        $map = array(
            "a.cs" => array("EQ",$cs),
            "a.on" => array("EQ",-1)
        );

        return M("user")->alias('a')->where($map)
                 ->join("join qz_user_company b on b.userid = a.id and b.fake = 0")
                 ->join("join qz_quyu q on q.cid = a.cs")
                 ->field("a.id,a.jc,a.end,q.cname")
                 ->order("a.end desc,a.id")
                 ->select();
    }

    /**
     * 获取VIP用户列表
     * @param  [type] $cs    [description]
     * @param  [type] $param [description]
     * @return [type]        [description]
     */
    public function getVipListCount($cs,$param)
    {
        $map = array(
            "a.cs" => array("IN",$cs),
            "a.classid" => array('IN',array(3,6)),
            "a.on" => array("IN",array(0,-1,2,-4))
        );

        if (!empty($param["uid"])) {
            $map["a.id"] = array("EQ",$param["uid"]);
        }

        if (!empty($param["name"])) {
            $map["jc|qc"] = array("LIKE",'%'.$param["name"].'%');
        }

        if (!empty($param["sell"])) {
            $map["b.saler"] = array("EQ",$param["sell"]);
        }

        if (!empty($param["viptype"])) {
            $map["b.viptype"] = array("EQ",$param["viptype"]);
        }

        if ($param["fake"] != "") {
            $map["b.fake"] = array("EQ",$param["fake"]);
        }

        if (!empty($param["begin"])) {
            $map["a.start"] = array("EGT",$param["begin"]);
        }

        if (!empty($param["end"])) {
            $map["a.end"] = array("ELT",$param["end"]);
        }

        if (!empty($param["city"]) && !empty($param["city"])) {
            $map["a.cs"] = array("EQ",$param["city"]);
        }

        if (empty(!$param["cooperation"])) {
            $map["b.cooperation_type"] = array("EQ",$param["cooperation"]);
        }

        if (empty(!$param["cooperate_mode"])) {
            $map["b.cooperate_mode"] = array("EQ", $param["cooperate_mode"]);
        }

        if(!empty($param['is_show'])){
            $map["b.is_show"] = array("EQ",$param['is_show']);
        }

        return M('user')->alias("a")->where($map)
            ->join("join qz_user_company b on a.id = b.userid")
            ->count();
    }

    /**
     * 获取城市对应vip数字
     * @param  [type] $cs    [description]
     * @param  [type] $param [description]
     * @return [type]        [description]
     */
    public function getVipCityCount($cs)
    {
        $map = array(
            "a.cs" => array("IN",$cs),
            "a.classid" => array('IN',array(3,6)),
            "a.on" => array("NEQ",0),
            "b.fake" => array("EQ",0)
        );
        return M('user')->alias("a")
            ->field('count(*) as huiyuan,a.cs,c.manage_id')
            ->join("join qz_user_company b on a.id = b.userid")
            ->join("join qz_sales_city_ratio c on c.cid = a.cs")
            ->where($map)
            ->group('cs')
            ->select();
    }
    /**
     * 获取所有VIP用户表
     * @param  [type] $cs  [description]
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public function getVipList($cs,$param,$pageIndex,$pageCount)
    {
        $map = array(
            "a.cs" => array("IN",$cs),
            "a.classid" => array('IN',array(3,6)),
            "a.on" => array("in",array(0,-1,2,-4))
        );

        if (!empty($param["uid"])) {
            $map["a.id"] = array("EQ",$param["uid"]);
        }

        if (!empty($param["name"])) {
            $map["jc|qc"] = array("LIKE",'%'.$param["name"].'%');
        }

        if (!empty($param["sell"])) {
            $map["b.saler"] = array("EQ",$param["sell"]);
        }

        if (!empty($param["viptype"])) {
            $map["b.viptype"] = array("EQ",$param["viptype"]);
        }

        if ($param["fake"] != "") {
            $map["b.fake"] = array("EQ",$param["fake"]);
        }

        if (!empty($param["begin"])) {
            $map["a.start"] = array("EGT",$param["begin"]);
        }

        if (!empty($param["end"])) {
            $map["a.end"] = array("ELT",$param["end"]);
        }

        if (!empty($param["city"]) && $param["city"] != -1 ) {
            $map["a.cs"] = array("EQ",$param["city"]);
        }

        if (empty(!$param["cooperation"])) {
            $map["b.cooperation_type"] = array("EQ",$param["cooperation"]);
        }

        if (empty(!$param["cooperate_mode"])) {
            $map["b.cooperate_mode"] = array("EQ", $param["cooperate_mode"]);
        }

        if(!empty($param['is_show'])){
            $map["b.is_show"] = array("EQ",$param['is_show']);
        }
        return M('user')->where($map)->alias("a")
            ->join("join qz_user_company b on a.id = b.userid")
            ->join("join qz_quyu q on q.cid = a.cs")
            ->join("left JOIN qz_company_tags c ON c.company_id = a.id")
            ->field("a.id,a.jc,a.on,q.cname,b.saler,b.fake,a.start,a.end,b.is_show,FROM_UNIXTIME(b.contract_start,'%Y-%m-%d') as allstart,FROM_UNIXTIME(b.contract_end,'%Y-%m-%d') as allend,q.bm,GROUP_CONCAT(c.tag) as tags,b.cooperate_mode")
            ->group('a.id')
            ->order("b.fake,a.on desc,a.end,a.id")
            ->limit($pageIndex . "," . $pageCount)->select();

    }

    /**
     * 获取装修公司查询信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getCompanyList($id, $classid = 3)
    {
        $map = array(
            "a.id" => array("LIKE","%$id%"),
        );

        if (is_array($classid)) {
            $map["a.classid"] = array("IN", $classid);
        } else {
            $map["a.classid"] = array("EQ", $classid);
        }

        $buildSql = M('user')->where($map)->alias("a")
            ->join("join qz_quyu q on q.cid = a.cs")
            ->join("join qz_user_company c on c.userid = a.id")
            ->field("a.id,a.user,a.jc,a.qc,q.cname,c.jd_tel_1,c.jd_tel_2,jd_tel_name_1,jd_tel_name_2,c.saler,a.end,c.saler_id,c.fake")
            ->limit(10)->buildSql();

        return  M('user')->table($buildSql)->alias("t")
            ->join("left join qz_sale_business_licence b on b.company_id = t.id and b.type = 1")
            ->join("left join qz_sale_business_licence c on c.company_id = t.id and c.type = 4")
            ->field("t.*,b.state,c.img1 as gx_img,b.img1,b.img2,b.img3,b.img4")
            ->group("t.id")
            ->select();
    }

    /**
     * 查询会员公司是否有日志记录
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getVipHistoryCount($id)
    {
        $map = array(
            "comid" => array("EQ",$id)
        );
        return M('log_vip')->where($map)->count();
    }

    /**
     * 编辑VIP装修公司信息
     * @param  [type] $id   [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function editCompanyInfo($id,$data)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("user")->where($map)->save($data);
    }

    /**
     * 获取装修公司基础信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function findCompanyInfo($id,$cs)
    {
        $map = array(
            "a.id" => array("EQ",$id),
            "a.classid" => array('IN', [3,6])
        );

        if (count($cs) > 0) {
            $map["a.cs"]  = array("in",$cs);
        }

        return M("user")->where($map)->alias("a")
                  ->join("join qz_user_company c on c.userid = a.id")
                  ->join("join qz_quyu q on q.cid = a.cs")
                  ->field("a.id,a.on,a.jc,q.cname,c.jd_tel_1,c.jd_tel_2,jd_tel_name_1,jd_tel_name_2,c.saler,c.fake,a.start,a.end,c.viptype,a.case_count,a.uptime,a.pj,a.uv,a.register_admin_id,a.user_type,a.ip,a.register_time,a.cs,a.qx,a.pass,a.user,a.classid,a.on")
                  ->find();
    }

    /**
     * 查询接单报备信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getJdbb($id)
    {
        $map = array(
            "jdbb_comid" => array("EQ",$id)
        );

        return M("user_company_jdbblog")->where($map)->find();
    }

    public function getJdbbList($companys)
    {
        if (empty($companys)) {
            return [];
        }

        $map['a.id'] = ["IN", $companys];

        $buildSql = M("user")->where($map)->alias("a")
            ->join("left join qz_user_company_jdbblog b on b.jdbb_comid = a.id")
            ->order("jdbb_time desc")->field("a.tel,a.cal,a.cals,a.jc,b.*,a.id as comid")
            ->buildSql();
        return M("user")->table($buildSql)->alias("t")
            ->group("t.jdbb_comid")->select();
    }

    /**
     * 新增报备
     * @param [type] $data [description]
     */
    public function setJdbb($data)
    {
        return M('user_company_jdbblog')->add($data);
    }

    /**
     * 修改报备
     * @param [type] $data [description]
     */
    public function upJdbb($id,$data)
    {
        $map = array('jdbb_comid' => $id);
        return M('user_company_jdbblog')->where($map)->save($data);
    }

    /**
     * 获取装修公司详细信息
     * @return [type] [description]
     */
    public function getCompanyDetailsList($cs,$on = 2,$id,$getGiftOrder = '',$lx = '',$little = '')
    {
        $time = strtotime("-2 month",strtotime(date("Y-m-d")));
        $monthStart = mktime(0,0,0,date("m"),1,date("Y"));
        $monthEnd = mktime(23,59,59,date("m"),date("t"),date("Y"));

        $map = array(
            "a.classid" => array("EQ",3)
        );

        if (!empty($cs)) {
            $map['a.cs'] = array('IN',$cs);
        }

        if (!empty($on)) {
            $map["a.on"] = array("EQ",$on);
        }

        if (!empty($id)) {
            $map['a.id'] = array('IN',$id);
        }

        if (!empty($getGiftOrder)) {
            $map['b.get_gift_order'] = array('eq',$getGiftOrder);
        }

        if (!empty($lx)) {
            if ($lx == 1) {
                $map['b.lx'] = ['in', [1, 3]];
            }
            if ($lx == 2) {
                $map['b.lx'] = ['in', [2, 3]];
            }
        }

        if($little === 0){
            $map['q.little'] = ['eq', 0];
        }

        $buildSql = M("user")->where($map)->alias("a")
                             ->join("join qz_user_company b on a.id = b.userid and b.fake = 0")
                             ->join("left join qz_user_company_jdbblog c on c.jdbb_comid = a.id")
                             ->join("join qz_quyu q on q.cid = a.cs")
                             ->join("join qz_area d on d.qz_areaid = a.qx")
                             ->field("a.id,a.classid,a.on,a.jc,a.start,a.end,a.cs,a.qx,q.cname,d.qz_area,
                             b.viptype,b.get_gift_order,b.lng,b.lat,b.cooperation_type,
                             b.lx,b.lxs,b.mianji,b.contract,b.fen_mianji,b.fen_special_needs,b.other_id,b.contract_start,b.contract_end,
                             c.jdbb_gzzz,c.jdbb_mj,c.jdbb_gm,c.jdbb_time,c.jdbb_remarks,c.jdbb_sjsrs,b.cooperate_mode")
                             ->order("c.id desc")
                             ->buildSql();
        $buildSql = M("user")->table($buildSql)->alias("t")->group("t.id")->buildSql();

        $companys = M("user")->table($buildSql)->alias("t")
                             ->join("left join qz_order_wechat w on w.comid = t.id and w.employee_id = 0 and w.is_delete = 0")
                             ->field("t.*,count(if(w.id is not null,1,null)) as wxcount")
                             ->group("t.id")
                             ->select();

        $company_order_count = $company_tags = [];
        if(count($companys) > 0){
            //获取公司 , 分/抢/赠
            $companyIds = array_column($companys,'id');
            $infoMap = [
                'i.addtime'=>['gt',$time],
                'i.com'=>['in',$companyIds]
            ];
            $buildSql = M('order_info')->alias('i')
                ->field('i.com id,i.addtime,i.type_fw as fw,i.allot_source,p.order_status')
                ->join('LEFT JOIN qz_order_rob_pool p on p.order_id = i.`order`')
                ->where($infoMap)
                ->buildSql();
            $company_order_count = M()->table($buildSql)->alias("t1")
                        ->field("t1.id company_id,count(if(t1.addtime >= '$monthStart' and t1.addtime <= '$monthEnd' and t1.fw = 1 and t1.allot_source <> 3,1,null)) as fen,count(if(t1.addtime >= '$monthStart' and t1.addtime <= '$monthEnd' and t1.fw = 2,1,null)) as zen,count(if(t1.addtime >= '$monthStart' and t1.addtime <= '$monthEnd' and t1.allot_source = 3 and  t1.order_status = 1 ,1,null)) as qiang")
                        ->group("t1.id")
                        ->select();
            if (count($company_order_count) > 0) {
                foreach ($company_order_count as $k => $v) {
                    $company_order_count[$v['company_id']] = $v;
                    unset($company_order_count[$k]);
                }
            }
            //获取公司 , 标签
            $tagMap = [
                'ct.company_id' => ['in', $companyIds]
            ];
            $company_tags = M('company_tags')->alias('ct')
                ->field('ct.company_id,GROUP_CONCAT(crt.tag) as tags')
                ->join('LEFT JOIN qz_company_relation_tag crt ON crt.id = ct.tag')
                ->where($tagMap)
                ->group('ct.company_id')
                ->select();
            if(count($company_tags) > 0){
                foreach ($company_tags as $k=>$v){
                    $company_tags[$v['company_id']] = $v;
                    unset($company_tags[$k]);
                }
            }
        }
        //将数据反塞
        foreach ($companys as $k=>$v){
            if(isset($company_order_count[$v['id']])){
                $companys[$k]['fen'] = $company_order_count[$v['id']]['fen'];
                $companys[$k]['qiang'] = $company_order_count[$v['id']]['qiang'];
                $companys[$k]['zen'] = $company_order_count[$v['id']]['zen'];
            }else{
                $companys[$k]['fen'] = 0;
                $companys[$k]['qiang'] = 0;
                $companys[$k]['zen'] = 0;
            }
            if(isset($company_tags[$v['id']])){
                $companys[$k]['tags'] = $company_tags[$v['id']]['tags'];
            }else{
                $companys[$k]['tags'] = '';
            }
        }
        return $companys;
    }

    /**
     * 获取装修公司微信绑定信息
     * @param  [type] $companys [description]
     * @return [type]           [description]
     */
    public function getCompanysWexinInfo($companys)
    {
        if(count($companys) == 0){
            return [];
        }
        $map = array(
            "comid" => array("IN",$companys),
            "employee_id" => array("EQ", 0),
            "is_delete" => array("EQ", 0)
        );
        return M("order_wechat")->where($map)->select();
    }

    /**
     * [getLastOutCompanyList description]
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getLastExpireCompanyList($cs,$date)
    {
            $begin = date("Y-m-d", strtotime("-30 day",strtotime($date)));
            $end = $date;
            $map = array(
                    "a.cs" => array("EQ",$cs),
                    "a.end" => array(
                                array("EGT",$begin),
                                array("ELT",$end)
                    ),
                    "a.on" => array("EQ",-1),
                    "b.cooperate_mode" => array("EQ",1)
            );
            return M("user")->where($map)->alias("a")
                                    ->join("join qz_user_company b on a.id = b.userid and b.fake = 0")
                                    ->field("a.jc,a.id,a.end,b.cooperate_mode")
                                    ->select();
    }

    public function getQianLastExpireCompanyList($cs,$date)
    {
        $map = [
            "t.vip_status" => ["EQ",-1],
            "t.datetime" => [
                ["EGT",strtotime("-30 day",strtotime($date))],
                ["ELT",strtotime($date)]
            ],
        ];

        $buildSql = M("user_company_vip_status")->alias("a")->where(["u.cs" => ["eq",$cs], "a.cooperate_mode" => ["EQ",2],])
                            ->join("join qz_user u on u.id = a.company_id")
                            ->field("a.company_id,a.vip_status,a.datetime,u.jc")->order("datetime desc")
                            ->buildSql();

        $buildSql = M("user_company_vip_status")->table($buildSql)->alias("t")->field("company_id,vip_status,datetime,jc")
                    ->group("company_id")->buildSql();

        return  M("user_company_vip_status")->table($buildSql)->alias("t")
                                ->where($map)->field("company_id as id,jc,datetime as `end`,2 as cooperate_mode")->select();
    }

    /**
     * 获取设计师信息
     * @param  [type] $str [description]
     * @return [type]      [description]
     */
    public function getDesignerInfoList($str,$limit){
        $map = array("a.classid"=>array("EQ",2));
        $map["_complex"] =  array(
            "a.name"=>array("LIKE","%$str%"),
            "a.id"=>array("IN",$str),
            "_logic"=>"OR"
        );
        return M("user")->where($map)->alias("a")
                        ->join("inner join qz_quyu as b on b.cid = a.cs")
                        ->join("INNER join qz_team as c on c.userid = a.id")
                        ->join("INNER join qz_user as d on d.id = c.comid")
                        ->field("a.*,b.bm,b.cname,d.jc,d.id as comid")
                        ->limit($limit)->select();
    }

    //禁封用户
    public function blockedUser($uid,$time){
        $map['id'] = $uid;
        $data['blocked'] = $time;
        M("user")->where($map)->save($data);
    }

    /**
     * 获取有会员的城市信息
     * @return [type] [description]
     */
    public function getVipCity()
    {
        $map = array(
            "a.on" => array("EQ",2),
            "a.classid" => array('IN',array(3,6))
        );

        return M("user")->where($map)->alias("a")
                        ->join("join qz_user_company b on a.id = b.userid  and b.fake = 0")
                        ->join("join qz_quyu q on q.cid = a.cs")
                        ->field("q.cid,q.cname")
                        ->group("a.cs")->having("count(a.id) > 0")
                        ->select();
    }

    /**
     * 根据ID获取后台用户名称
     * @return [ids] [id拼接的字符串]
     */
    public function getAdminNamesById($ids)
    {
        $map = "id in ($ids)";
        $name = M("adminuser")->where($map)->field("id,name")->select();
        return $name;
    }

    /*
    *   根据城市ID查询是否有真会员
    *   @param  [type] $cs  [城市ID]
    *   @param  [type] $qx  [区县ID]
    *   @return [type]      [description]
    */
    public function getRealVipCompanys($cs,$qx)
    {
        $map['u.cs'] = $cs;
        $map['u.on'] = 2;
        $map['c.fake'] = 0;
        $com = M('user')->alias('u')
                        ->join('qz_user_company as c on u.id = c.userid')
                        ->where($map)
                        ->field('u.id,u.user')
                        ->select();
        return $com;
    }

    /**
    * 批量获取城市会员
    *
    * @param  [type]  $cid      []
    * @return [type]  $result   [company数组]
    */
    public function getCompanysByCids($cids){
        //->where('u.on = 2 AND c.viptype > 1 AND c.fake = 0 and u.classid = 3')  城市 -》cs
        $users = '';
        foreach ($cids as $k => $v) {
            if (!empty($cid)) {
                $map["u.cs"] = array("EQ",$cid);
            }
            $map["u.on"] = '2';
            $map["u.classid"] = array('IN',array(3,6));
            $map["u.cs"] = $v;

            $user = M("user")->alias("u")
                            ->join("join qz_user_company c on c.userid = u.id and c.fake = 0")
                            ->field("u.id")
                            ->where($map)
                            ->order("c.viptype desc")
                            ->select();
            if(!empty($user)){
                foreach ($user as $key => $value) {
                    $u[] = $value['id'];
                }
                $users[$k]['city'] = $v;
                $users[$k]['companys'] = $u;
                unset($u);
            }
        }
        return $users;

    }

    /**
     * 统计装修公司会员信息列表(按城市ID)
     * @param  [type] $cityId [城市ID]
     * @param  [type]
     * @param  [type]
     * @return [type]        [description]
     */
    public function getUserNumByCityId($cityId)
    {

        if (!empty($cityId)) {
            $map["u.cs"] = array("EQ",$cityId);
        }
        $map["u.on"] = '2';
        $map["u.classid"] = array('IN',array(3,6));

        $num = M("user")->alias("u")
                        ->join("join qz_user_company c on c.userid = u.id and c.fake = 0")
                        ->where($map)
                        ->count();

        return $num;
    }

    /**
     * 统计公司会员分单指标数量(按城市ID)
     * @param  [type] $cityId [城市ID]
     * @param  [type]
     * @param  [type]
     * @return [type]        [description]
     */
    public function getPointsNumByCityId($cityId)
    {

        if (!empty($cityId)) {
            $map["cityid"] = array("EQ",$cityId);
        }
        $map["status"] = 1;

        $num = M("sales_order_points")->where($map)->count();

        return $num;
    }

    /**
     * 统计公司会员分单指标的ID数组(按城市ID)
     * @param  [type] $cityId [城市ID]
     * @param  [type]
     * @param  [type]
     * @return [type]        [description]
     */
    public function getPointsIdsByCityId($cityId)
    {

        if (!empty($cityId)) {
            $map["cityid"] = array("EQ",$cityId);
        }
        $map["status"] = 1;

        $ids = M("sales_order_points")->where($map)->field('userid')->select();
        return $ids;
    }


    /**
     * 统计单个城市装修公司会员数量和多倍会员数量(分页时使用)
     * @param  [type]
     * @return [type]
     */
    public function getAllCityUsersCount($map)
    {
        //var_dump($map);
        if(!empty($map['city'])){
            $where = "where q.cid = ".$map['city'];
        }
        //统计所有数量，如果有部门数据传入，需要判断查询的城市是否在部门内，不在返回空
        //如果传入城市在管辖范围则返回管辖范围所有城市
        if(!empty($map['department'])){
            $citys = $this->searchDepartmentCitys($map['department']);
            //var_dump($citys);
            $cityarr = [];
            foreach ($citys as $k => $v) {
                $cityarr[$k] = $v['cid'];
            }
            if(in_array($map['city'], $cityarr) || empty($map['city'])){
                foreach ($cityarr as $k => $v) {
                    $cityids .= $v.',';
                }
                $cityids = substr($cityids, 0, -1);//查询到的经理ID拼接成字符串

                $where = "where q.cid in (".$cityids.")";
            }else{
                $where = "where q.cid = 0";
            }
        }
        $starttime = date("Y-m-d");
        if(!empty($map['time'])){
            $starttime = $map['time'];
        }
        $sql = "select q.*,(select v.point from qz_sales_setting_value as v where v.cid = q.cid and v.typeid = 1 and v.module = 1 and v.status = 1 and v.start <= '$starttime' ORDER BY v.`start` DESC limit 1 ) as point,s.gname,s.gnumber,s.manager as qqmanager from qz_quyu as q LEFT JOIN qz_sales_city_qqgroup as s ON q.cid = s.cityid $where";
        $Model = new Model();
        $quyu = $Model->query($sql);
        //var_dump($sql);
        //var_dump(M()->getLastSql());
        $num = count($quyu);
        return $num;
    }
    /**
     * 统计单个城市装修公司会员数量和多倍会员数量
     * @param  [map]    $map['city'] 查询的城市ID
     *                  $map['department'] 查询的部门ID
     *                  $map['time1'] 开始时间
     *                  $map['time2'] 对比时间
     * @param  [start]  分页起始
     * @param  [end]    分页截止
     * @return [type]
     */
    public function getAllCityUsers($map, $start, $end)
    {
        //var_dump($map);
        if(!empty($map['city'])){
            $where = "where q.cid = ".$map['city'];
        }
        $starttime = date("Y-m-d");
        if(!empty($map['time'])){
            $starttime = $map['time'];
        }
        //统计所有数量，如果有部门数据传入，需要判断查询的城市是否在部门内，不在返回空
        //如果传入城市在管辖范围则返回管辖范围所有城市
        if(!empty($map['department'])){
            $citys = $this->searchDepartmentCitys($map['department']);
            $cityarr = [];
            foreach ($citys as $k => $v) {
                $cityarr[$k] = $v['cid'];
            }
            if(in_array($map['city'], $cityarr) || empty($map['city'])){
                foreach ($cityarr as $k => $v) {
                    $cityids .= $v.',';
                }
                $cityids = substr($cityids, 0, -1);//查询到的经理ID拼接成字符串

                $where = "where q.cid in (".$cityids.")";
            }else{
                $where = "where q.cid = 0";
            }
        }
        if($start == 0 && $end == 0){
            $limit = '';
        }else{
            $limit = 'limit '.$start.','.$end;
        }
        $sql = "select q.*,(select v.point from qz_sales_setting_value as v where v.cid = q.cid and v.typeid = 1 and v.module = 1 and v.status = 1 and start <= '$starttime' ORDER BY `start` DESC limit 1 ) as point,s.gname,s.gnumber,s.manager as qqmanager from qz_quyu as q LEFT JOIN qz_sales_city_qqgroup as s ON q.cid = s.cityid $where $limit";

        //var_dump($sql);
        $Model = new Model();
        $quyu = $Model->query($sql);
        //var_dump($quyu);
        /*$arr['v.typeid'] = '1';
        $arr['v.module'] = '1';
        $arr['v.status'] = '1';
        $quyu = M('quyu')->alias("q")
                        ->join("left join qz_sales_setting_value as v on q.cid = v.cid")
                        ->where($arr)
                        ->order("q.id,q.bm")
                        ->field("q.*,v.point,v.start,v.end")
                        ->select();*/
        //var_dump(M()->getLastSql());
        if(empty($map['time1'])){
            //如果没有传入查询时间，查询实时会员
            //获取所有城市的vip数量
            $map = array(
                    "a.classid" => array('IN',array(3,6)),
                    "b.fake" => array("EQ",0),
                    "a.on" => '2'
                );
            $users = M("user")->where($map)->alias("a")
                     ->join("inner join qz_user_company b on a.id = b.userid")
                     ->field("a.cs,sum(if(a.on = 2,b.viptype,null)) as vipcnt,sum(if(b.viptype > 1,(b.viptype-1),null)) as doublecnt,count(if(a.on = 2,a.id,null)) as vipnum")
                     ->order("vipcnt desc")
                     ->group("a.cs")
                     ->select();
            //做vipcnt的关联数组 cs=>vipcnt
            $users_vipcnt = array();
            $vipcntsum = 0;
            foreach ($users as $k => $val) {
                $users_vipcnt[$val['cs']] = $val['vipcnt'];
                $vipcntsum += $val["vipcnt"];
            }
            //同理添加多倍会员数量
            $users_doublecnt = array();
            foreach ($users as $k => $val) {
                $users_doublecnt[$val['cs']] = $val['doublecnt'];
            }

            //vip数量放入城市列表quyu
            $edition = array();
            unset($value);
            foreach ($quyu as $key => &$value) {
                $nowCityvipcnt = $users_vipcnt[$value['cid']];
                if (!empty($nowCityvipcnt)) {
                    $value['vipcnt'] = $nowCityvipcnt; //把会员总数存入vipcnt（包含多倍折算的数量 ，普通会员 = 总数 - 多倍）
                } else {
                    $value['vipcnt'] = "0";
                }

                $edition[] = $value['vipcnt'];
            }
            unset($value);
            //多倍会员放入城市列表
            foreach ($quyu as $key => &$value) {
                $nowCityvipcnt = $users_doublecnt[$value['cid']];
                if (!empty($nowCityvipcnt)) {
                    $value['doublecnt'] = $nowCityvipcnt; //把统计多倍会员数存入vipcnt
                } else {
                    $value['doublecnt'] = "0";
                }
            }
            unset($value);
        }else{
            //如果传入查询时间，查询log_user_real_company表按日统计的城市会员数量
            $user_company = $this->getOldCompanyData($starttime,$map['city']);
            foreach ($user_company as $k => $v) {
                $user_num[$v['city_id']]['vipcnt'] =  $v['vip_num'];
                $user_num[$v['city_id']]['doublecnt'] =  ($v['vip_num']-$v['realnum']);
            }
            foreach ($quyu as $key => $value) {
                $nowCityvipcnt = $user_num[$value['cid']];
                if (!empty($nowCityvipcnt)) {
                    $quyu[$key]['vipcnt'] = $nowCityvipcnt['vipcnt']; //把统计综合会员数存入vipcnt
                    $quyu[$key]['doublecnt'] = $nowCityvipcnt['doublecnt']; //把统计多倍会员数存入vipcnt
                } else {
                    $quyu[$key]['doublecnt'] = "0";
                    $quyu[$key]['vipcnt'] = "0";
                }
            }
            unset($value);
            //var_dump($user_num);
        }

        //添加到期会员数,放入城市数据
        $daoqi = $this->getCompanyChangeList($starttime);
        $users_daoqi = array();
        foreach ($daoqi as $k => $val) {
            $users_daoqi[$val['cs']] = $val['daoqi'];
        }
        foreach ($quyu as $key => &$value) {
            $nowCityvipcnt = $users_daoqi[$value['cid']];
            if (!empty($nowCityvipcnt)) {
                $value['daoqi'] = $nowCityvipcnt; //把到期会员数存入vipcnt
            } else {
                $value['daoqi'] = "0";
            }
        }
        unset($value);
        //添加暂停会员数,放入城市数据
        $zanting = $this->getCompanyChangeZt($starttime);
        $users_zhanting = array();
        foreach ($zanting as $k => $val) {
            $users_zhanting[$val['cs']] = $val['zhanting'];
        }
        foreach ($quyu as $key => &$value) {
            $nowCityvipcnt = $users_zhanting[$value['cid']];
            if (!empty($nowCityvipcnt)) {
                $value['zhanting'] = $nowCityvipcnt; //把暂停会员数存入vipcnt
            } else {
                $value['zhanting'] = "0";
            }
        }
        unset($value);
        //添加退费会员数,放入城市数据
        $tuifei = $this->getCompanyChangeTf($starttime);
        $users_tuifei = array();
        foreach ($tuifei as $k => $val) {
            $users_tuifei[$val['cs']] = $val['tuifei'];
        }
        foreach ($quyu as $key => &$value) {
            $nowCityvipcnt = $users_tuifei[$value['cid']];
            if (!empty($nowCityvipcnt)) {
                $value['tuifei'] = $nowCityvipcnt; //把退费会员数存入vipcnt
            } else {
                $value['tuifei'] = "0";
            }
        }
        unset($value);
        //添加续费会员数,放入城市数据
        $xufei = $this->getCompanyChangeXf($starttime);
        //var_dump($xufei);
        $users_xufei = array();
        foreach ($xufei as $k => $val) {
            $users_xufei[$val['cs']] = $val['xufei'];
        }
        foreach ($quyu as $key => &$value) {
            $nowCityvipcnt = $users_xufei[$value['cid']];
            if (!empty($nowCityvipcnt)) {
                $value['xufei'] = $nowCityvipcnt; //把续费会员数存入vipcnt
            } else {
                $value['xufei'] = "0";
            }
        }
        unset($value);
        //添加提前续费会员数,放入城市数据
        $tqxufei = $this->getCompanyChangeTqXf($starttime);
        //var_dump($xufei);
        $users_tqxufei = array();
        foreach ($tqxufei as $k => $val) {
            $users_tqxufei[$val['cs']] = $val['tqxufei'];
        }
        foreach ($quyu as $key => &$value) {
            $nowCityvipcnt = $users_tqxufei[$value['cid']];
            if (!empty($nowCityvipcnt)) {
                $value['tqxufei'] = $nowCityvipcnt; //把提前续费会员数存入vipcnt
            } else {
                $value['tqxufei'] = "0";
            }
        }
        unset($value);
        //添加滞后续费会员数,放入城市数据
        $zhxufei = $this->getCompanyChangeZhXf($starttime);
        //var_dump($xufei);
        $users_zhxufei = array();
        foreach ($zhxufei as $k => $val) {
            $users_zhxufei[$val['cs']] = $val['zhxufei'];
        }
        foreach ($quyu as $key => &$value) {
            $nowCityvipcnt = $users_zhxufei[$value['cid']];
            if (!empty($nowCityvipcnt)) {
                $value['zhxufei'] = $nowCityvipcnt; //把滞后续费会员数存入vipcnt
            } else {
                $value['zhxufei'] = "0";
            }
        }
        unset($value);
        //添加续费月数,放入城市数据
        $xfyue = $this->getCompanyChangeXfYue($starttime);
        //var_dump($xfyue);
        $users_xfyue = array();
        foreach ($xfyue as $k => $val) {
            $users_zhxufei[$val['cs']] = $val['xfyue'];
        }
        foreach ($quyu as $key => &$value) {
            $nowCityvipcnt = $users_xfyue[$value['cid']];
            if (!empty($nowCityvipcnt)) {
                $value['xfyue'] = $nowCityvipcnt; //把续费月数存入vipcnt
            } else {
                $value['xfyue'] = "0";
            }
        }
        unset($value);
        //添加城市管理职能,放入城市数据
        $citymanagers = $this->getCitymanagers();
        //var_dump($citymanagers);
        $users_manager = array();
        foreach ($citymanagers as $k => $val) {
            $users_citymanagers[$val['cid']][$val['module']]['cid'] = $val['cid'];
            $users_citymanagers[$val['cid']][$val['module']]['jingli'] = $val['jingli'];
            $users_citymanagers[$val['cid']][$val['module']]['tuan'] = $val['tuan'];
            $users_citymanagers[$val['cid']][$val['module']]['tuanzhang'] = $val['tuanzhang'];
            $users_citymanagers[$val['cid']][$val['module']]['shi'] = $val['shi'];
            $users_citymanagers[$val['cid']][$val['module']]['shizhang'] = $val['shizhang'];
            $users_citymanagers[$val['cid']][$val['module']]['bumen'] = $val['bumen'];
        }
        //var_dump($users_citymanagers);
        foreach ($quyu as $key => &$value) {
            $nowCityvipcnt = $users_citymanagers[$value['cid']];
            if (!empty($nowCityvipcnt)) {
                $value['managers'] = $nowCityvipcnt; //城市管理人员
            } else {
                $value['managers'] = ''; //城市管理人员
            }
        }
        unset($value);
        //var_dump($quyu);
        //会员时间小于90天的数量与占比
        $usercount1 = $this->getUserDateCount(90);
        $users_usercount1 = array();
        foreach ($usercount1 as $k => $val) {
            $users_usercount1[$val['cs']] = $val['num'];
        }
        foreach ($quyu as $key => &$value) {
            $nowCityvipcnt = $users_usercount1[$value['cid']];
            if (!empty($nowCityvipcnt)) {
                $value['usercount1'] = $nowCityvipcnt; //把会员数存入vipcnt
            } else {
                $value['usercount1'] = "0";
            }
        }
        unset($value);
        //会员时间大于90天，小于177天的数量与占比
        $usercount2 = $this->getUserDateCount(177);
        $users_usercount2 = array();
        foreach ($usercount2 as $k => $val) {
            $users_usercount2[$val['cs']] = $val['num'];
        }
        //var_dump($users_usercount1);
        foreach ($quyu as $key => &$value) {
            $nowCityvipcnt = $users_usercount2[$value['cid']];
            if (!empty($nowCityvipcnt)) {
                $value['usercount2'] = $nowCityvipcnt; //把会员数存入vipcnt
            } else {
                $value['usercount2'] = "0";
            }
        }
        unset($value);
        //会员时间大于177天，小于396天的数量与占比
        $usercount3 = $this->getUserDateCount(178);
        $users_usercount3 = array();
        foreach ($usercount3 as $k => $val) {
            $users_usercount3[$val['cs']] = $val['num'];
        }
        //var_dump($users_usercount1);
        foreach ($quyu as $key => &$value) {
            $nowCityvipcnt = $users_usercount3[$value['cid']];
            if (!empty($nowCityvipcnt)) {
                $value['usercount3'] = $nowCityvipcnt; //把会员数存入vipcnt
            } else {
                $value['usercount3'] = "0";
            }
        }
        unset($value);
        //会员时间大于396天的数量与占比
        $usercount4 = $this->getUserDateCount(396);
        $users_usercount4 = array();
        foreach ($usercount4 as $k => $val) {
            $users_usercount4[$val['cs']] = $val['num'];
        }
        //var_dump($users_usercount1);
        foreach ($quyu as $key => &$value) {
            $nowCityvipcnt = $users_usercount4[$value['cid']];
            if (!empty($nowCityvipcnt)) {
                $value['usercount4'] = $nowCityvipcnt; //把会员数存入vipcnt
            } else {
                $value['usercount4'] = "0";
            }
        }
        unset($value);



        array_multisort($edition, SORT_DESC, $quyu);
        import('Library.Org.Util.App');
        $app = new \app();
        foreach ($quyu as $key => $value) {
            $str = $app->getFirstCharter($value["cname"]);
            $cityInfo[$key] = array(
                    "id"            => $value["cid"],//城市CID
                    "text"          => $str." ".$value["cname"],//拼接城市名称
                    "vipcnt"        => $value["vipcnt"],//会员总数
                    'doublecnt'     => $value['doublecnt'],//多倍会员数
                    'manager'       => $value['manager'],//
                    'cname'         => $value["cname"],//城市名称
                    'point'         => $value['point'],//城市会员指标
                    'daoqi'         => $value['daoqi'],//到期会员数
                    'zhanting'      => $value['zhanting'],//暂停会员
                    'tuifei'        => $value['tuifei'],//退费会员
                    'xufei'         => $value['xufei'],//续费会员数
                    'tqxufei'       => $value['tqxufei'],//提前续费会员
                    'zhxufei'       => $value['zhxufei'],//滞后续费会员
                    'xfyue'         => round(($value['xfyue']/(3600*24*30)),1),//续费月数
                    'xfjidu'        => round(($value['xfyue']/(3600*24*30*3)),1),//续费折算季度
                    'qqname'        => $value['gname'],//城市QQ群名称
                    'qqnumber'      => $value['gnumber'],//城市QQ群
                    'qqmanager'     => $value['qqmanager'],//管理员
                    'managers'      => $value['managers'],//职能管辖关系
                    'usercount1'    => $value['usercount1'],//时间少于90天的会员数
                    'usercount2'    => $value['usercount2'],//时间大于90天，小于177天的会员数
                    'usercount3'    => $value['usercount3'],//时间大于177天，小于396天的会员数
                    'usercount4'    => $value['usercount4']//时间大于396天的会员数
            );
            if($value['daoqi'] == 0){
                $cityInfo[$key]['xufeilv'] = ($value['xufei']*100).'%';
            }else{
                $cityInfo[$key]['xufeilv'] = (round($value['xufei']/$value['daoqi'],2)*100)."%";
            }
            if($value["vipcnt"] == 0){
                $cityInfo[$key]['userconpersent1'] = 0;//少于90天的会员占比
                $cityInfo[$key]['userconpersent2'] = 0;//时间大于90天，小于177天的会员占比
                $cityInfo[$key]['userconpersent3'] = 0;//时间大于177天，小于396天的会员占比
                $cityInfo[$key]['userconpersent4'] = 0;//时间大于396天的会员占比
            }else{
                $cityInfo[$key]['userconpersent1'] = (round($value['usercount1']/($value["vipcnt"] - $value['doublecnt']),2)*100).'%';//少于90天的会员占比
                $cityInfo[$key]['userconpersent2'] = (round($value['usercount2']/($value["vipcnt"] - $value['doublecnt']),2)*100).'%';//时间大于90天，小于177天的会员占比
                $cityInfo[$key]['userconpersent3'] = (round($value['usercount3']/($value["vipcnt"] - $value['doublecnt']),2)*100).'%';//时间大于177天，小于396天的会员占比
                $cityInfo[$key]['userconpersent4'] = (round($value['usercount4']/($value["vipcnt"] - $value['doublecnt']),2)*100).'%';//时间大于396天的会员占比
            }

            if($value['point']){
                $cityInfo[$key]['realvip'] = ($value["vipcnt"] - $value['doublecnt']);//实际会员数
                $cityInfo[$key]['wanchenglv'] = (round(($cityInfo[$key]['realvip']/$value['point']),4)*100).'%';
            }else{
                $cityInfo[$key]['realvip'] = ($value["vipcnt"] - $value['doublecnt']);//实际会员数
                if($cityInfo[$key]['realvip'] == 0){
                    $cityInfo[$key]['wanchenglv'] = '0%';
                }else{
                    $cityInfo[$key]['wanchenglv'] = $cityInfo[$key]['wanchenglv'].'00%';
                }
            }

            $allvipcnt += $value["vipcnt"];//所有会员总数（classid=3）
            $alldoublecnt += $value["doublecnt"];//多倍会员总数（classid=3）
        }
        $content = $cityInfo;
        //var_dump($cityInfo);
        //$content['allvipcnt'] = $allvipcnt;//当前全部会员
        return $content;
    }

    /*
    *   按城市查询，会员变更数
    *   到期会员数 ，续费会员数 ，暂停会员数 ，退费会员数 ，提前续费数 ，滞后续费数 ，续费率 ，续费月数 ，季度折算
    *   @param  [firsttime]     要查询的时间
    *   @param  []
    *
    */
    public function getCompanyChangeList($firsttime= '')
    {
        //如果$firsttime 为本月，则统计1号到今天的，如果为本月之前，则统计整月
        //结束时间，统计整个月份的数据
        $firstday = strtotime(date("Y-m-01",strtotime($firsttime)));//传入时间月份的第一天
        $now = strtotime(date("Y-m-01",time()));
        if($firsttime < $now){
            //非本月
            $starttime  = date('Y-m-01', $firstday);
            //$endtime    = date('Y-m-d', strtotime(date("Y-m-d",$firsttime) . ' +1 month -1 day'));
            $endtime    = $firsttime;
        }else{
            //本月
            $starttime  = date('Y-m-01', $firstday);
            $endtime    = date('Y-m-d',$now);
        }
        $daoqi = D("user_vip")->alias("v")
                            ->join("qz_user as u on u.id = v.company_id")
                            ->where("v.end_time >= '$starttime' and v.end_time <= '$endtime' and u.on = 2 and v.type = 2")
                            ->field("u.cs,count(v.id) as daoqi")
                            ->group("u.cs")
                            ->select();
        return $daoqi;
    }

    /*
    *   按城市查询，会员变更数
    *   暂停会员数
    *   @param  [firsttime]     要查询的时间
    *   @param  []
    *
    */
    public function getCompanyChangeZt($firsttime= '')
    {
        //如果$firsttime 为本月，则统计1号到今天的，如果为本月之前，则统计整月
        //结束时间，统计整个月份的数据
        $firstday = strtotime(date("Y-m-01",strtotime($firsttime)));//传入时间月份的第一天
        $now = strtotime(date("Y-m-01",time()));
        if($firsttime < $now){
            //非本月
            $starttime  = date('Y-m-01', $firstday);
            //$endtime    = date('Y-m-d', strtotime(date("Y-m-d",$firsttime) . ' +1 month -1 day'));
            $endtime    = $firsttime;
        }else{
            //本月
            $starttime  = date('Y-m-01', $firstday);
            $endtime    = date('Y-m-d',$now);
        }
        $zhanting = D("user_vip")->alias("v")
                            ->join("qz_user as u on u.id = v.company_id")
                            ->where("v.end_time >= '$starttime' and v.end_time <= '$endtime' and u.on = 2 and v.type = 4")
                            ->field("u.cs,count(v.id) as zhanting")
                            ->group("u.cs")
                            ->select();
        return $zhanting;
    }

    /*
    *   按城市查询，会员变更数
    *  退费会员数
    *   @param  [firsttime]     要查询的时间
    *   @param  []
    *
    */
    public function getCompanyChangeTf($firsttime= '')
    {
        //如果$firsttime 为本月，则统计1号到今天的，如果为本月之前，则统计整月
        //结束时间，统计整个月份的数据
        $firstday = strtotime(date("Y-m-01",strtotime($firsttime)));//传入时间月份的第一天
        $now = strtotime(date("Y-m-01",time()));
        if($firsttime < $now){
            //非本月
            $starttime  = date('Y-m-01', $firstday);
            //$endtime    = date('Y-m-d', strtotime(date("Y-m-d",$firsttime) . ' +1 month -1 day'));
            $endtime    = $firsttime;
        }else{
            //本月
            $starttime  = date('Y-m-01', $firstday);
            $endtime    = date('Y-m-d',$now);
        }
        $tuifei = D("user_vip")->alias("v")
                            ->join("qz_user as u on u.id = v.company_id")
                            ->where("v.end_time >= '$starttime' and v.end_time <= '$endtime' and u.on = 2 and v.type = 7")
                            ->field("u.cs,count(v.id) as tuifei")
                            ->group("u.cs")
                            ->select();
        return $tuifei;
    }

    /*
    *   按城市查询，会员变更数
    *   续费会员数：外销部的续费会员定义：本月发生续费行为的会员。
    *               限制条件：会员的到期日须在往前至60天内的到期会员，在本月续费的会员。
    *   续费会员定义：本月发生续费行为的所有会员数。
    *   @param  [firsttime]     要查询的时间
    *   @param  []
    *
    */
    public function getCompanyChangeXf($firsttime= '')
    {
        //如果$firsttime 为本月，则统计1号到今天的，如果为本月之前，则统计整月
        //结束时间，统计整个月份的数据
        $firstday = strtotime(date("Y-m-01",strtotime($firsttime)));//传入时间月份的第一天
        $now = strtotime(date("Y-m-01",time()));
        if($firsttime < $now){
            //非本月
            $starttime  = date('Y-m-01', $firstday);
            //$endtime    = date('Y-m-d', strtotime(date("Y-m-d",$firsttime) . ' +1 month -1 day'));
            $endtime    = $firsttime;
        }else{
            //本月
            $starttime  = date('Y-m-01', $firstday);
            $endtime    = date('Y-m-d',$now);
        }
        $daoqistart = date("Y-m-d",strtotime($firsttime.'-60day'));
        $daoqiend = date("Y-m-d",strtotime($firsttime));

        $sql = "select p.company_id from qz_user_vip as p where p.type = 2 and p.end_time >= '$daoqistart' and p.end_time <= '$daoqiend' group by p.company_id";
        if(empty($sql)){
            $sql = '';
        }
        $xufei = D("user_vip")->alias("v")
                            ->join("qz_user as u on u.id = v.company_id")
                            ->where("v.start_time >= '$starttime' and v.start_time <= '$endtime' and u.`on` = 2 and v.type = 8"." and v.company_id in (".$sql.")")
                            ->field("u.cs,count(v.id) as xufei")
                            ->group("u.cs")
                            ->select();
        return $xufei;
    }

    /*
    *   按城市查询，会员变更数
    *   提前续费数：到期日在本月之后，在本月续费的会员个数
    *
    *   @param  [firsttime]     要查询的时间
    *   @param  []
    *
    */
    public function getCompanyChangeTqXf($firsttime= '')
    {
        //如果$firsttime 为本月，则统计1号到今天的，如果为本月之前，则统计整月
        //结束时间，统计整个月份的数据
        $firstday = strtotime(date("Y-m-01",strtotime($firsttime)));//传入时间月份的第一天
        $now = strtotime(date("Y-m-01",time()));
        if($firsttime < $now){
            //非本月
            $starttime  = date('Y-m-01', $firstday);
            //$endtime    = date('Y-m-d', strtotime(date("Y-m-d",$firsttime) . ' +1 month -1 day'));
            $endtime    = $firsttime;
        }else{
            //本月
            $starttime  = date('Y-m-01', $firstday);
            $endtime    = date('Y-m-d',$now);
        }
        $daoqistart = date("Y-m-d",strtotime($firsttime.'+1day'));//到期时间

        $sql = "select p.company_id from qz_user_vip as p where p.type = 2 and p.end_time >= '$daoqistart' group by p.company_id";//查询到期时间在今天之后的
        if(empty($sql)){
            $sql = '';
        }
        $tqxufei = D("user_vip")->alias("v")
                            ->join("qz_user as u on u.id = v.company_id")
                            ->where("v.start_time >= '$starttime' and v.start_time <= '$endtime' and u.`on` = 2 and v.type = 8"." and v.company_id in (".$sql.")")
                            ->field("u.cs,count(v.id) as tqxufei")
                            ->group("u.cs")
                            ->select();
        //var_dump(M()->getLastSql());
        return $tqxufei;
    }

    /*
    *   按城市查询，会员变更数
    *   滞后续费数：到期日在本月之前，在本月续费的会员个数
    *
    *   @param  [firsttime]     要查询的时间
    *   @param  []
    *
    */
    public function getCompanyChangeZhXf($firsttime= '')
    {
        //如果$firsttime 为本月，则统计1号到今天的，如果为本月之前，则统计整月
        //结束时间，统计整个月份的数据
        $firstday = strtotime(date("Y-m-01",strtotime($firsttime)));//传入时间月份的第一天
        $now = strtotime(date("Y-m-01",time()));
        if($firsttime < $now){
            //非本月
            $starttime  = date('Y-m-01', $firstday);
            //$endtime    = date('Y-m-d', strtotime(date("Y-m-d",$firsttime) . ' +1 month -1 day'));
            $endtime    = $firsttime;
        }else{
            //本月
            $starttime  = date('Y-m-01', $firstday);
            $endtime    = date('Y-m-d',$now);
        }
        $daoqistart = date("Y-m-d",$firstday);//到期时间

        $sql = "select p.company_id from qz_user_vip as p where p.type = 2 and p.end_time <= '$daoqistart' group by p.company_id";//查询到期时间在今天之后的
        if(empty($sql)){
            $sql = '';
        }
        $zhxufei = D("user_vip")->alias("v")
                            ->join("qz_user as u on u.id = v.company_id")
                            ->where("v.start_time >= '$starttime' and v.start_time <= '$endtime' and u.`on` = 2 and v.type = 8"." and v.company_id in (".$sql.")")
                            ->field("u.cs,count(v.id) as zhxufei")
                            ->group("u.cs")
                            ->select();
        //var_dump(M()->getLastSql());
        return $zhxufei;
    }

    /*
    *   按城市查询，会员变更数
    *   续费月数：总计续费时长（月）
    *
    *   @param  [firsttime]     要查询的时间
    *   @param  []
    *
    */
    public function getCompanyChangeXfYue($firsttime= '')
    {
        //如果$firsttime 为本月，则统计1号到今天的，如果为本月之前，则统计整月
        //结束时间，统计整个月份的数据
        $firstday = date("Y-m-01",strtotime($firsttime));//传入时间月份的第一天
        /*$now = strtotime(date("Y-m-01",time()));
        if($firsttime < $now){
            //非本月
            $starttime  = date('Y-m-01', $firstday);
            //$endtime    = date('Y-m-d', strtotime(date("Y-m-d",$firsttime) . ' +1 month -1 day'));
            $endtime    = $firsttime;
        }else{
            //本月
            $starttime  = date('Y-m-01', $firstday);
            $endtime    = date('Y-m-d',$now);
        }*/
        //$daoqistart = date("Y-m-d",$firstday);//到期时间

        //$sql = "select p.company_id from qz_user_vip as p where p.type = 2 and p.end_time <= '$daoqistart' group by p.company_id";//查询到期时间在今天之后的
        $xfyue = D("user_vip")->alias("v")
                            ->join("qz_user as u on u.id = v.company_id")
                            ->where("v.start_time >= '$firstday' and v.start_time <= '$firsttime' and u.`on` = 2 and v.type = 8")
                            ->field("u.cs,sum(UNIX_TIMESTAMP(v.end_time) - UNIX_TIMESTAMP(v.start_time)) as xfyue,v.company_id")
                            ->group("u.cs")
                            ->select();
        //var_dump(M()->getLastSql());
        return $xfyue;
    }

    /*
    *   按城市查询，城市管理人员
    *
    *   @param  []
    *   @param  []
    *
    */
    public function getCitymanagers()
    {
        $sql = "
        SELECT v.typeid,v.cid,v.pid,c.pid AS tid,c.`name` AS jingli,(
            SELECT v1.`name` FROM `qz_sales_category` AS v1 WHERE v1.id = tid
        ) as tuan,(
            SELECT v1.info FROM `qz_sales_category` AS v1 WHERE v1.id = tid
        ) as tuanzhang,(
            SELECT v1.pid FROM `qz_sales_category` AS v1 WHERE v1.id = tid
        ) as tpid,(
            SELECT v2.`name` FROM `qz_sales_category` AS v2 WHERE v2.id = tpid
        ) as shi,(
            SELECT v2.`module` FROM `qz_sales_category` AS v2 WHERE v2.id = tpid
        ) as module,(
            SELECT v2.info FROM `qz_sales_category` AS v2 WHERE v2.id = tpid
        ) as shizhang,(
            SELECT v2.pid FROM `qz_sales_category` AS v2 WHERE v2.id = tpid
        ) as spid,(
            SELECT v3.`name` FROM `qz_sales_category` AS v3 WHERE v3.id = spid
        ) as bumen FROM `qz_sales_setting_value` AS v

        LEFT JOIN `qz_sales_category` AS c ON v.pid = c.id WHERE v.`typeid` = '1' AND v.`module` = '2'
        ";
        $Model = new Model();
        $managers = $Model->query($sql);

        return $managers;

    }

    /*
    *   按城市查询，会员到期时间（小于90 ，90-177 ，178-396 ， 大于396）
    *
    *   @param  [num]       传入查询的会员数量：90 =》小于     177 =》 90~177    178 =》 178~396   396 =》 大于396
    *   @param  []
    *
    */
    public function getUserDateCount($num = 90)
    {
        if($num == 90){
            $time = 90*24*3600;
            //$sql = "SELECT id FROM qz_user_vip WHERE `type` = 8 AND (UNIX_TIMESTAMP(`end_time`) - UNIX_TIMESTAMP(`start_time`)) <= $time GROUP BY `company_id` ORDER BY `id` desc";
            $usernum = M("user_vip")->alias('v')
                                    ->join("left join qz_user as u on v.company_id = u.id")
                                    ->where("v.`type` = 8 and (UNIX_TIMESTAMP(v.`end_time`) - UNIX_TIMESTAMP(v.`start_time`)) <= $time")
                                    ->field("count(v.`id`) as num,u.cs")
                                    ->group("u.`cs`")->select();

        }elseif($num == 177){
            $start = 90*24*3600;
            $end = 177*24*3600;
            //$sql = "SELECT id FROM qz_user_vip WHERE `type` = 8 AND (UNIX_TIMESTAMP(`end_time`) - UNIX_TIMESTAMP(`start_time`)) >= $start and (UNIX_TIMESTAMP(`end_time`) - UNIX_TIMESTAMP(`start_time`)) < $end GROUP BY `company_id` ORDER BY `id` desc";
            $usernum = M("user_vip")->alias('v')
                                    ->join("left join qz_user as u on v.company_id = u.id")
                                    ->where("v.`type` = 8 and (UNIX_TIMESTAMP(v.`end_time`) - UNIX_TIMESTAMP(v.`start_time`)) >= $start and (UNIX_TIMESTAMP(v.`end_time`) - UNIX_TIMESTAMP(v.`start_time`)) < $end")
                                    ->field("count(v.`id`) as num,u.cs")
                                    ->group("u.`cs`")->select();
        }elseif($num == 178){
            $start = 178*24*3600;
            $end = 396*24*3600;
            //$sql = "SELECT id FROM qz_user_vip WHERE `type` = 8 AND (UNIX_TIMESTAMP(`end_time`) - UNIX_TIMESTAMP(`start_time`)) >= $start and (UNIX_TIMESTAMP(`end_time`) - UNIX_TIMESTAMP(`start_time`)) < $end GROUP BY `company_id` ORDER BY `id` desc";
            $usernum = M("user_vip")->alias('v')
                                    ->join("left join qz_user as u on v.company_id = u.id")
                                    ->where("v.`type` = 8 and (UNIX_TIMESTAMP(v.`end_time`) - UNIX_TIMESTAMP(v.`start_time`)) >= $start and (UNIX_TIMESTAMP(v.`end_time`) - UNIX_TIMESTAMP(v.`start_time`)) < $end")
                                    ->field("count(v.`id`) as num,u.cs")
                                    ->group("u.`cs`")->select();
        }else{
            $time = 396*24*3600;
            //$sql = "SELECT id FROM qz_user_vip WHERE `type` = 8 AND (UNIX_TIMESTAMP(`end_time`) - UNIX_TIMESTAMP(`start_time`)) >= $time GROUP BY `company_id` ORDER BY `id` desc";
            $usernum = M("user_vip")->alias('v')
                                    ->join("left join qz_user as u on v.company_id = u.id")
                                    ->where("v.`type` = 8 and (UNIX_TIMESTAMP(v.`end_time`) - UNIX_TIMESTAMP(v.`start_time`)) >= $time")
                                    ->field("count(v.`id`) as num,u.cs")
                                    ->group("u.`cs`")->select();
        }
        //$Model = new Model();
        //$usernum = $Model->query($sql);

        return $usernum;
    }

    /*
    *   根据师团，查询所有的管理城市
    *
    *   @param  [$department]   传入部门
    *   @param  []
    *
    */
    public function getDepartmentCitys($department)
    {
        if(!empty($department['city'])){
            $map['city'] = " and cid = ".$department['city'];
        }
        //查询部门 type = 1 , status = 0 ,pid = 0
        $bumen_ids = M("sales_category")->where("type = 1 and status = 0 and pid = 0")->field("id")->select();
        foreach ($bumen_ids as $k => $v) {
            $bumenids .= $v['id'].',';
        }
        $bumenids = substr($bumenids, 0, -1);
        //查询师 type = 1 , status = 0 ,pid in ($bumenids)
        $shi_ids = M("sales_category")->where("type = 1 and status = 0 and pid in (".$bumenids.")")->field("id")->select();
        foreach ($shi_ids as $k => $v) {
            $shiids .= $v['id'].',';
        }
        $shiids = substr($shiids, 0, -1);
        //查询团 type = 1 ,status = 0 ,pid in ($shiids)
        $tuan_ids = M("sales_category")->where("type = 1 and status = 0 and pid in (".$shiids.")")->field("id")->select();
        foreach ($tuan_ids as $k => $v) {
            $tuanids .= $v['id'].',';
        }
        $tuanids = substr($tuanids, 0, -1);
        //查询团下属经理 type = 1,status = 0,pid in ($tuanids)
        $jingli_ids = M("sales_category")->where("type = 1 and status = 0 and pid in (".$tuanids.")")->field("id")->select();
        //var_dump(M()->getLastSql());
        foreach ($jingli_ids as $k => $v) {
            $jingliids .= $v['id'].',';
        }
        $jingliids = substr($jingliids, 0, -1);
        //根据城市经理ID拼接的字符串，查询出所有管理城市
        $citys = M("sales_setting_value")->where("typeid = 1 and module = 2 and pid in (".$jingliids.")".$map['city'])->field("id,cid")->group("cid")->select();
        //var_dump(M()->getLastSql());
        //var_dump($citys);
        return $citys;
    }

     /**
     * 根据部门，查询城市经理管理的城市会员数量
     * @param  [map] 城市、部门ID、时间、对比时间
     * @return [type]
     */
    /*public function getDepartmentUsersCount($map)
    {
        //var_dump($map);
        //统计所有数量，如果有部门数据传入，需要判断查询的城市是否在部门内，不在返回空
        //如果传入城市在管辖范围则返回管辖范围所有城市
        if(!empty($map['department'])){
            $citys = $this->searchDepartmentCitys($map['department']);

            $cityarr = [];
            foreach ($citys as $k => $v) {
                $cityarr[$k] = $v['cid'];
            }
            //var_dump($cityarr);
            if(in_array($map['city'], $cityarr) || empty($map['city'])){
                $ids = $cityarr;
            }else{
                $ids = [];
            }
        }else{
            $ids = $this->getDepartmentCitys($map);
        }
        //var_dump($ids);
        $num = count($ids);
        return $num;
    }*/
    /**
     * 根据部门，查询城市经理管理的城市会员详情
     * @param  [department]   传入部门名称
     * @return [start]  分页获取数据开始位置
     * @return [end]    分页获取数据结束位置
     * @return [type]
     */
    public function getDepartmentUsers($map = '')
    {
        //var_dump($map);
        //统计所有数量，如果有部门数据传入，需要判断查询的城市是否在部门内，不在返回空
        //如果传入城市在管辖范围则返回管辖范围所有城市
        if(!empty($map['department'])){
            $citys = $this->searchDepartmentCitys($map['department']);

            $cityarr = [];
            foreach ($citys as $k => $v) {
                $cityarr[$k] = $v['cid'];
            }

            //var_dump($cityarr);
            if(in_array($map['city'], $cityarr) || empty($map['city'])){
                $ids = $cityarr;
            }else{
                $ids = [0];
            }
            foreach ($ids as $k => $v) {
                $cityids .= $v.',';
            }
        }else{
            $ids = $this->getDepartmentCitys($map);
            foreach ($ids as $k => $v) {
                $cityids .= $v['cid'].',';
            }
        }

        $cityids = substr($cityids, 0, -1);
        //根据城市ID字符串，获取城市信息
        $starttime = date("Y-m-d");
        if(!empty($map['time'])){
            $starttime = $map['time'];
        }
        $sql = "select q.*,(select v.point from qz_sales_setting_value as v where v.cid = q.cid and v.typeid = 1 and v.module = 1 and v.status = 1 and start <= '$starttime' ORDER BY `start` DESC limit 1 ) as point,s.gname,s.gnumber,s.manager as qqmanager from qz_quyu as q LEFT JOIN qz_sales_city_qqgroup as s ON q.cid = s.cityid where q.cid in (".$cityids.")";
        $Model = new Model();
        $quyu = $Model->query($sql);
        //var_dump($sql);
        if(empty($map['time1'])){
            //如果没有传入查询时间，查询实时会员
            //获取所有城市的vip数量
            $map = array(
                    "a.classid" =>array("IN",array(3,6)),
                    "b.fake" => array("EQ",0),
                    "a.on" => '2'
                );
            $users = M("user")->where($map)->alias("a")
                     ->join("inner join qz_user_company b on a.id = b.userid")
                     ->field("a.cs,sum(if(a.on = 2,b.viptype,null)) as vipcnt,sum(if(b.viptype > 1,(b.viptype-1),null)) as doublecnt,count(if(a.on = 2,a.id,null)) as vipnum")
                     ->order("vipcnt desc")
                     ->group("a.cs")
                     ->select();
            //做vipcnt的关联数组 cs=>vipcnt
            $users_vipcnt = array();
            $vipcntsum = 0;
            foreach ($users as $k => $val) {
                $users_vipcnt[$val['cs']] = $val['vipcnt'];
                $vipcntsum += $val["vipcnt"];
            }
            //同理添加多倍会员数量
            $users_doublecnt = array();
            foreach ($users as $k => $val) {
                $users_doublecnt[$val['cs']] = $val['doublecnt'];
            }

            //vip数量放入城市列表quyu
            $edition = array();
            unset($value);
            foreach ($quyu as $key => &$value) {
                $nowCityvipcnt = $users_vipcnt[$value['cid']];
                if (!empty($nowCityvipcnt)) {
                    $value['vipcnt'] = $nowCityvipcnt; //把会员总数存入vipcnt（包含多倍折算的数量 ，普通会员 = 总数 - 多倍）
                } else {
                    $value['vipcnt'] = "0";
                }

                $edition[] = $value['vipcnt'];
            }
            unset($value);
            //多倍会员放入城市列表
            foreach ($quyu as $key => &$value) {
                $nowCityvipcnt = $users_doublecnt[$value['cid']];
                if (!empty($nowCityvipcnt)) {
                    $value['doublecnt'] = $nowCityvipcnt; //把统计多倍会员数存入vipcnt
                } else {
                    $value['doublecnt'] = "0";
                }
            }
            unset($value);
        }else{
            //如果传入查询时间，查询log_user_real_company表按日统计的城市会员数量
            $user_company = $this->getOldCompanyData($starttime,$map['city']);
            //var_dump(M()->getLastSql());
            //var_dump($user_company);
            foreach ($user_company as $k => $v) {
                $user_num[$v['city_id']]['vipcnt'] =  $v['vip_num'];
                $user_num[$v['city_id']]['doublecnt'] =  ($v['vip_num']-$v['realnum']);
            }
            foreach ($quyu as $key => $value) {
                $nowCityvipcnt = $user_num[$value['cid']];
                if (!empty($nowCityvipcnt)) {
                    $quyu[$key]['vipcnt'] = $nowCityvipcnt['vipcnt']; //把统计综合会员数存入vipcnt
                    $quyu[$key]['doublecnt'] = $nowCityvipcnt['doublecnt']; //把统计多倍会员数存入vipcnt
                } else {
                    $quyu[$key]['doublecnt'] = "0";
                    $quyu[$key]['vipcnt'] = "0";
                }
            }
            unset($user_num);
            unset($value);
            //var_dump($user_num);
            //var_dump($quyu);
        }

        unset($value);
        //添加到期会员数,放入城市数据
        $daoqi = $this->getCompanyChangeList($starttime);
        $users_daoqi = array();
        foreach ($daoqi as $k => $val) {
            $users_daoqi[$val['cs']] = $val['daoqi'];
        }
        foreach ($quyu as $key => &$value) {
            $nowCityvipcnt = $users_daoqi[$value['cid']];
            if (!empty($nowCityvipcnt)) {
                $value['daoqi'] = $nowCityvipcnt; //把到期会员数存入vipcnt
            } else {
                $value['daoqi'] = "0";
            }
        }
        unset($value);
        //添加暂停会员数,放入城市数据
        $zanting = $this->getCompanyChangeZt($starttime);
        $users_zhanting = array();
        foreach ($zanting as $k => $val) {
            $users_zhanting[$val['cs']] = $val['zhanting'];
        }
        foreach ($quyu as $key => &$value) {
            $nowCityvipcnt = $users_zhanting[$value['cid']];
            if (!empty($nowCityvipcnt)) {
                $value['zhanting'] = $nowCityvipcnt; //把暂停会员数存入vipcnt
            } else {
                $value['zhanting'] = "0";
            }
        }
        unset($value);
        //添加退费会员数,放入城市数据
        $tuifei = $this->getCompanyChangeTf($starttime);
        $users_tuifei = array();
        foreach ($tuifei as $k => $val) {
            $users_tuifei[$val['cs']] = $val['tuifei'];
        }
        foreach ($quyu as $key => &$value) {
            $nowCityvipcnt = $users_tuifei[$value['cid']];
            if (!empty($nowCityvipcnt)) {
                $value['tuifei'] = $nowCityvipcnt; //把退费会员数存入vipcnt
            } else {
                $value['tuifei'] = "0";
            }
        }
        unset($value);
        //添加续费会员数,放入城市数据
        $xufei = $this->getCompanyChangeXf($starttime);
        //var_dump($xufei);
        $users_xufei = array();
        foreach ($xufei as $k => $val) {
            $users_xufei[$val['cs']] = $val['xufei'];
        }
        foreach ($quyu as $key => &$value) {
            $nowCityvipcnt = $users_xufei[$value['cid']];
            if (!empty($nowCityvipcnt)) {
                $value['xufei'] = $nowCityvipcnt; //把续费会员数存入vipcnt
            } else {
                $value['xufei'] = "0";
            }
        }
        unset($value);
        //添加提前续费会员数,放入城市数据
        $tqxufei = $this->getCompanyChangeTqXf($starttime);
        //var_dump($xufei);
        $users_tqxufei = array();
        foreach ($tqxufei as $k => $val) {
            $users_tqxufei[$val['cs']] = $val['tqxufei'];
        }
        foreach ($quyu as $key => &$value) {
            $nowCityvipcnt = $users_tqxufei[$value['cid']];
            if (!empty($nowCityvipcnt)) {
                $value['tqxufei'] = $nowCityvipcnt; //把提前续费会员数存入vipcnt
            } else {
                $value['tqxufei'] = "0";
            }
        }
        unset($value);
        //添加滞后续费会员数,放入城市数据
        $zhxufei = $this->getCompanyChangeZhXf($starttime);
        //var_dump($xufei);
        $users_zhxufei = array();
        foreach ($zhxufei as $k => $val) {
            $users_zhxufei[$val['cs']] = $val['zhxufei'];
        }
        foreach ($quyu as $key => &$value) {
            $nowCityvipcnt = $users_zhxufei[$value['cid']];
            if (!empty($nowCityvipcnt)) {
                $value['zhxufei'] = $nowCityvipcnt; //把滞后续费会员数存入vipcnt
            } else {
                $value['zhxufei'] = "0";
            }
        }
        unset($value);
        //添加续费月数,放入城市数据
        $xfyue = $this->getCompanyChangeXfYue($starttime);
        //var_dump($xfyue);
        $users_xfyue = array();
        foreach ($xfyue as $k => $val) {
            $users_zhxufei[$val['cs']] = $val['xfyue'];
        }
        foreach ($quyu as $key => &$value) {
            $nowCityvipcnt = $users_xfyue[$value['cid']];
            if (!empty($nowCityvipcnt)) {
                $value['xfyue'] = $nowCityvipcnt; //把续费月数存入vipcnt
            } else {
                $value['xfyue'] = "0";
            }
        }
        unset($value);
        //添加城市管理职能,放入城市数据
        $citymanagers = $this->getCitymanagers();
        //var_dump($citymanagers);
        $users_manager = array();
        foreach ($citymanagers as $k => $val) {
            $users_citymanagers[$val['cid']][$val['module']]['cid'] = $val['cid'];
            $users_citymanagers[$val['cid']][$val['module']]['jingli'] = $val['jingli'];
            $users_citymanagers[$val['cid']][$val['module']]['tuan'] = $val['tuan'];
            $users_citymanagers[$val['cid']][$val['module']]['tuanzhang'] = $val['tuanzhang'];
            $users_citymanagers[$val['cid']][$val['module']]['shi'] = $val['shi'];
            $users_citymanagers[$val['cid']][$val['module']]['shizhang'] = $val['shizhang'];
            $users_citymanagers[$val['cid']][$val['module']]['bumen'] = $val['bumen'];
        }
        //var_dump($users_citymanagers);
        foreach ($quyu as $key => &$value) {
            $nowCityvipcnt = $users_citymanagers[$value['cid']];
            if (!empty($nowCityvipcnt)) {
                $value['managers'] = $nowCityvipcnt; //城市管理人员
            } else {
                $value['managers'] = ''; //城市管理人员
            }
        }
        unset($value);
        //var_dump($quyu);
        //会员时间小于90天的数量与占比
        $usercount1 = $this->getUserDateCount(90);
        $users_usercount1 = array();
        foreach ($usercount1 as $k => $val) {
            $users_usercount1[$val['cs']] = $val['num'];
        }
        foreach ($quyu as $key => &$value) {
            $nowCityvipcnt = $users_usercount1[$value['cid']];
            if (!empty($nowCityvipcnt)) {
                $value['usercount1'] = $nowCityvipcnt; //把会员数存入vipcnt
            } else {
                $value['usercount1'] = "0";
            }
        }
        unset($value);
        //会员时间大于90天，小于177天的数量与占比
        $usercount2 = $this->getUserDateCount(177);
        $users_usercount2 = array();
        foreach ($usercount2 as $k => $val) {
            $users_usercount2[$val['cs']] = $val['num'];
        }
        //var_dump($users_usercount1);
        foreach ($quyu as $key => &$value) {
            $nowCityvipcnt = $users_usercount2[$value['cid']];
            if (!empty($nowCityvipcnt)) {
                $value['usercount2'] = $nowCityvipcnt; //把会员数存入vipcnt
            } else {
                $value['usercount2'] = "0";
            }
        }
        unset($value);
        //会员时间大于177天，小于396天的数量与占比
        $usercount3 = $this->getUserDateCount(178);
        $users_usercount3 = array();
        foreach ($usercount3 as $k => $val) {
            $users_usercount3[$val['cs']] = $val['num'];
        }
        //var_dump($users_usercount1);
        foreach ($quyu as $key => &$value) {
            $nowCityvipcnt = $users_usercount3[$value['cid']];
            if (!empty($nowCityvipcnt)) {
                $value['usercount3'] = $nowCityvipcnt; //把会员数存入vipcnt
            } else {
                $value['usercount3'] = "0";
            }
        }
        unset($value);
        //会员时间大于396天的数量与占比
        $usercount4 = $this->getUserDateCount(396);
        $users_usercount4 = array();
        foreach ($usercount4 as $k => $val) {
            $users_usercount4[$val['cs']] = $val['num'];
        }
        //var_dump($users_usercount1);
        foreach ($quyu as $key => &$value) {
            $nowCityvipcnt = $users_usercount4[$value['cid']];
            if (!empty($nowCityvipcnt)) {
                $value['usercount4'] = $nowCityvipcnt; //把会员数存入vipcnt
            } else {
                $value['usercount4'] = "0";
            }
        }
        unset($value);
        //var_dump($quyu[4]);
        array_multisort($edition, SORT_DESC, $quyu);
        import('Library.Org.Util.App');
        $app = new \app();
        foreach ($quyu as $key => $value) {
            $str = $app->getFirstCharter($value["cname"]);
            $cityInfo[$key] = array(
                    "id"            => $value["cid"],//城市CID
                    "text"          => $str." ".$value["cname"],//拼接城市名称
                    "vipcnt"        => $value["vipcnt"],//会员总数
                    'doublecnt'     => $value['doublecnt'],//多倍会员数
                    'manager'       => $value['manager'],//
                    'cname'         => $value["cname"],//城市名称
                    'point'         => $value['point'],//城市会员指标
                    'daoqi'         => $value['daoqi'],//到期会员数
                    'zhanting'      => $value['zhanting'],//暂停会员
                    'tuifei'        => $value['tuifei'],//退费会员
                    'xufei'         => $value['xufei'],//续费会员数
                    'tqxufei'       => $value['tqxufei'],//提前续费会员
                    'zhxufei'       => $value['zhxufei'],//滞后续费会员zhxufei
                    'xfyue'         => round(($value['xfyue']/(3600*24*30)),1),//续费月数
                    'xfjidu'        => round(($value['xfyue']/(3600*24*30*3)),1),//续费折算季度
                    'qqname'        => $value['gname'],//城市QQ群名称
                    'qqnumber'      => $value['gnumber'],//城市QQ群
                    'qqmanager'     => $value['qqmanager'],//管理员
                    'managers'      => $value['managers'],//职能管辖关系
                    'usercount1'    => $value['usercount1'],//时间少于90天的会员数
                    'usercount2'    => $value['usercount2'],//时间大于90天，小于177天的会员数
                    'usercount3'    => $value['usercount3'],//时间大于177天，小于396天的会员数
                    'usercount4'    => $value['usercount4']//时间大于396天的会员数
            );
            if($value['daoqi'] == 0){
                $cityInfo[$key]['xufeilv'] = ($value['xufei']*100).'%';
            }else{
                $cityInfo[$key]['xufeilv'] = (round($value['xufei']/$value['daoqi'],2)*100)."%";
            }
            if($value["vipcnt"] == 0){
                $cityInfo[$key]['userconpersent1'] = 0;//少于90天的会员占比
                $cityInfo[$key]['userconpersent2'] = 0;//时间大于90天，小于177天的会员占比
                $cityInfo[$key]['userconpersent3'] = 0;//时间大于177天，小于396天的会员占比
                $cityInfo[$key]['userconpersent4'] = 0;//时间大于396天的会员占比
            }else{
                $cityInfo[$key]['userconpersent1'] = (round($value['usercount1']/($value["vipcnt"] - $value['doublecnt']),2)*100).'%';//少于90天的会员占比
                $cityInfo[$key]['userconpersent2'] = (round($value['usercount2']/($value["vipcnt"] - $value['doublecnt']),2)*100).'%';//时间大于90天，小于177天的会员占比
                $cityInfo[$key]['userconpersent3'] = (round($value['usercount3']/($value["vipcnt"] - $value['doublecnt']),2)*100).'%';//时间大于177天，小于396天的会员占比
                $cityInfo[$key]['userconpersent4'] = (round($value['usercount4']/($value["vipcnt"] - $value['doublecnt']),2)*100).'%';//时间大于396天的会员占比
            }

            if($value['point']){
                $cityInfo[$key]['realvip'] = ($value["vipcnt"] - $value['doublecnt']);//实际会员数
                $cityInfo[$key]['wanchenglv'] = (round(($cityInfo[$key]['realvip']/$value['point']),4)*100).'%';
            }else{
                $cityInfo[$key]['realvip'] = ($value["vipcnt"] - $value['doublecnt']);//实际会员数
                if($cityInfo[$key]['realvip'] == 0){
                    $cityInfo[$key]['wanchenglv'] = '0%';
                }else{
                    $cityInfo[$key]['wanchenglv'] = $cityInfo[$key]['wanchenglv'].'00%';
                }

            }

            $allvipcnt += $value["vipcnt"];//所有会员总数（classid=3）
            $alldoublecnt += $value["doublecnt"];//多倍会员总数（classid=3）
        }
        $content = $cityInfo;

        //var_dump($content);
        //$users = M("")
        //$starttime = date("Y-m-d");
        //根据返回的ID字符串，查询城市列表
        //$num = count($quyu);
        return $content;
    }

    /**
     * 根据时间，查询log_user_real_company表中记录的会员数量数据
     * @param  [time]       查询时间
     * @return [city]       查询城市
     * @return [type]
     * @return [type]
     */
    public function getOldCompanyData($time = '',$city = ''){
        if(!empty($time)){
            $map['time'] = $time." 00:00:00";
        }
        if(!empty($city)){
            $map['city_id'] = $city;
        }
        $comnum = M("log_user_real_company")->where($map)->field("city_id,vip_count as realnum,vip_num")->select();
        //var_dump(M()->getLastSql());
        return $comnum;
    }

    /*
    *   根据传入的部门，查询所管理的城市
    *   TODO ：生成结果缓存
    *   @param  [$department]   传入部门ID
    *   @param  []
    *
    */
    public function searchDepartmentCitys($departmentid)
    {
        $map['id'] = $departmentid;
        $data = M("sales_category")->where($map)->find();
        //var_dump($data);
        if($data['level'] == 0){
            //查询部门（商务，外销，品牌）
            //根据部门查询师 type = 1 , status = 0 ,pid = $data['id']
            $shiids = $this->searchShiByDeartment($data['id']);
            //查询团 type = 1 ,status = 0 ,pid in ($shiids)
            $tuanids = $this->searchTuanByDeartment($shiids);
            //查询团下属经理 type = 1,status = 0,pid in ($tuanids)
            $jingliids = $this->searchTuanByDeartment($tuanids);
            //根据城市经理ID拼接的字符串，查询出所有管理城市
            $citys = $this->searchCitysByDeartment($jingliids);
            //var_dump(M()->getLastSql());
        }elseif($data['level'] == 1){
            //根据师查询管辖城市
            //查询团 type = 1 ,status = 0 ,pid in ($shiids)
            $tuanids = $this->searchTuanByDeartment($data['id']);
            //查询团下属经理 type = 1,status = 0,pid in ($tuanids)
            $jingliids = $this->searchTuanByDeartment($tuanids);
            //根据城市经理ID拼接的字符串，查询出所有管理城市
            $citys = $this->searchCitysByDeartment($jingliids);
        }elseif($data['level'] == 2){
            //根据团查询管辖城市
            //查询团下属经理 type = 1,status = 0,pid in ($tuanids)
            $jingliids = $this->searchTuanByDeartment($data['id']);
            //根据城市经理ID拼接的字符串，查询出所有管理城市
            $citys = $this->searchCitysByDeartment($jingliids);
        }else{
            //根据城市经理查询城市
            //根据城市经理ID拼接的字符串，查询出所有管理城市
            $citys = $this->searchCitysByDeartment($data['id']);
        }

        return $citys;
    }

    /*
    *   根据传入的部门，查询师
    *
    *   @param  [$department]   传入部门ID
    *   @param  []
    *
    */
    public function searchShiByDeartment($departmentid)
    {
        //查询部门（商务，外销，品牌）
        //根据部门查询师 type = 1 , status = 0 ,pid = $data['id']
        $shi_ids = M("sales_category")->where("type = 1 and status = 0 and pid = ".$departmentid)->field("id")->select();
        foreach ($shi_ids as $k => $v) {
            $shiids .= $v['id'].',';
        }
        $shiids = substr($shiids, 0, -1);//查询到的师的ID拼接成字符串

        return $shiids;
    }

    /*
    *   根据传入的师，查询团
    *
    *   @param  [$department]   传入师ID
    *   @param  []
    *
    */
    public function searchTuanByDeartment($departmentid)
    {
        //查询团 type = 1 ,status = 0 ,pid in ($shiids)
        $tuan_ids = M("sales_category")->where("type = 1 and status = 0 and pid in (".$departmentid.")")->field("id")->select();
        foreach ($tuan_ids as $k => $v) {
            $tuanids .= $v['id'].',';
        }
        $tuanids = substr($tuanids, 0, -1);//查询到的团的ID拼接成字符串

        return $tuanids;
    }

    /*
    *   根据传入的团，查询城市经理/品牌师
    *
    *   @param  [$department]   传入团ID
    *   @param  []
    *
    */
    public function searchJingLiByDeartment($departmentid)
    {
        //查询团下属经理 type = 1,status = 0,pid in ($tuanids)
        $jingli_ids = M("sales_category")->where("type = 1 and status = 0 and pid in (".$departmentid.")")->field("id")->select();
        //var_dump(M()->getLastSql());
        foreach ($jingli_ids as $k => $v) {
            $jingliids .= $v['id'].',';
        }
        $jingliids = substr($jingliids, 0, -1);//查询到的经理ID拼接成字符串

        return $jingliids;
    }

    /*
    *   根据传入的经理ID，查询管辖城市
    *
    *   @param  [$department]   传入团ID
    *   @param  []
    *
    */
    public function searchCitysByDeartment($departmentid)
    {
        //根据城市经理ID拼接的字符串，查询出所有管理城市
        $citys = M("sales_setting_value")->where("typeid = 1 and module = 2 and pid in (".$departmentid.")")->field("id,cid")->group("cid")->select();
        //var_dump(M()->getLastSql());
        //var_dump($citys);
        return $citys;
    }

    /*
    *   根据传入的level，查询部门
    *
    *   @param  [$level]   传入部门level
    *   @param  []
    *
    */
    public function getAllDepartments($level,$search='')
    {

        $map["level"] = $level;
        $map["status"] = 0;
        $map["type"] = 1;
        if(!empty($search['department']) && !empty($search['city'])){
            //有部门ID，查询部门ID
            $map['id'] = $search['department'];
            $citys = $this->searchDepartmentCitys($search['department']);
            //var_dump($citys);
            foreach ($citys as $k => $v) {
                $cityarr[$k] = $v['cid'];
            }

            if(!in_array($search['city'],$cityarr)){
                //不在部门管辖内
                return [];
                die();
            }
        }else{
            if(!empty($search['department'])){
                $map['id'] = $search['department'];
            }
            if(!empty($search['city'])){
                //通过城市逆向查询部门(师/团)
                $bumen = $this->getDepartmentByCity($level,$search['city']);
                $map['id'] = $bumen;
            }

        }
        $department = M("sales_category")->where($map)->field("id,name,info,module")->order("pid asc")->select();
        return $department;
    }

    /*
    *   根据传入的level和cityid，查询部门(师/团)
    *
    *   @param  [$level]   传入部门level
    *   @param  [cityid]   传入的城市ID
    */
    public function getDepartmentByCity($level,$cityid)
    {
        //根据Cityid查询城市经理
        $map['typeid'] = 1;
        $map['module'] = 2;
        $map['cid']    = $cityid;
        $jingli  = M("sales_setting_value")->where($map)->field("pid")->find();//经理ID
        //var_dump($jingli);
        if($level == 1){
            //查询师
            $tuan = M("sales_category")->where("type = 1 and level = 3 and id = ".$jingli['pid'])->field("id,pid,name,info")->find();
            $arr = M("sales_category")->where("type = 1 and level = 2 and id = ".$tuan['pid'])->field("id,pid,name,info")->find();
            $shi = M("sales_category")->where("type = 1 and level = 1 and id = ".$arr['pid'])->field("id,pid,name,info")->find();
            $id = $shi['id'];
        }else{
            //查询团
            $tuan = M("sales_category")->where("type = 1 and level = 3 and id = ".$jingli['pid'])->field("id,pid,name,info")->find();
            //var_dump();
            $arr = M("sales_category")->where("type = 1 and level = 2 and id = ".$tuan['pid'])->field("id,pid,name,info")->find();
            //var_dump($arr);
            $id = $arr['id'];
        }
        return $id;
    }

    /**
     * 获取没有会员城市列表
     * @param  [type] $cs [description]
     * @return [type]     [description]
     */
    public function getLostVipList($citys)
    {
        if (!empty($citys)) {
            $where = " and a.cs in ($citys)";
        }
        $sql = 'SELECT
                t1.cs,t1.end,q.cname,q.little,q.manager
                from (
                        select
                        t.cs,t.end,count(if(t.on = 2,1,null)) as vipcount
                        from (
                            select a.id,a.on,a.cs,a.`end`from qz_user a
                            join qz_user_company b on a.id = b.userid and b.fake = 0
                            where classid in (3,6) and `on`in (2,-1) and a.end <> "" '.$where.'  order by `end` desc
                        ) t group by t.cs
                ) t1
                join qz_quyu q on q.cid = t1.cs
                where vipcount = 0 order by t1.end desc';
        $result = M("user")->query($sql);
        return $result;
    }

    /**
     * 获取城市的VIP历史记录
     * @param  [type] $cs        [城市ID]
     * @param  [type] $startYear [开始时间]
     * @param  [type] $endYear   [结束时间]
     * @return [type]            [description]
     */
    public function getCityVipHistory($cs,$startYear,$endYear)
    {
        $map = array(
            "city_id" => array("EQ",$cs),
            "time" => array(
                array("EGT",$startYear),
                array("ELT",$endYear)
            )
        );
        return M("log_user_real_company")->where($map)->field("date_format(time,'%Y-%m-%d') as time")->select();
    }

    /**
     * 获取每个城市最近的有会员时间
     * @return [type] [description]
     */
    public function getVipLastTime($begin,$end,$citys)
    {
        if (!empty($citys)) {
            $where = " and city_id in ($citys)";
        }

        $sql = 'select * from (
                    select city_id,DATE_FORMAT(time,"%Y%m%d") as time from qz_log_user_real_company where time >= "'.$begin.'" and time <= "'.$end.'" '.$where.' ORDER BY time desc
                ) t group by t.city_id';
        $result = M("log_user_real_company")->query($sql);
        return $result;
    }

    /**
     * 修改订单查看密码
     * @param  [type] $company_id   [装修公司ID]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function editOrderPass($company_id,$data)
    {
        $map = array(
            "comid" => array("EQ",$company_id)
        );
        return M("order_pass")->where($map)->save($data);
    }

    /**
     * 查询用户帐号是否存在
     * @param  [type] $user [description]
     * @return [type]       [description]
     */
    public function findCompanyAccountByName($user)
    {
        $map = array(
            "user" => array("EQ",$user)
        );
        return M("user")->where($map)->count();
    }

    /**
     * 获取装修公司扩展信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function findCompanyExpandInfo($id)
    {
        $map = array(
            "userid" => array("EQ",$id)
        );
        return M("user_company")->where($map)->find();
    }

    /**
     * 删除装修公司
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delCompany($company_id)
    {
        $map = array(
            "id" => array("EQ",$company_id)
        );
        return M("user")->where($map)->delete();
    }

    /**
     * 删除装修公司
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delCompanyExpand($company_id)
    {
        $map = array(
            "userid" => array("EQ",$company_id)
        );
        return M("user_company")->where($map)->delete();
    }

    /**
     * 添加装修公司
     * @param [type] $data [description]
     */
    public function addCompany($data)
    {
        return M("user")->add($data);
    }

    /**
     * 添加装修公司
     * @param [type] $data [description]
     */
    public function addCompanyExpand($data)
    {
        return M("user_company")->add($data);
    }

    /**
     * 删除装修公司其他信息
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function delCompanyCase($company_id)
    {
        $map = array(
            "uid" => array("EQ",$company_id)
        );
        return M("cases")->where($map)->delete();
    }

        /**
     * 删除装修公司其他信息
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function delCompanyComment($company_id)
    {
        $map = array(
            "comid" => array("EQ",$company_id)
        );
        return M("comment")->where($map)->delete();
    }

    /**
     * 删除分店信息
     * @param  [type] $company_id [description]
     * @return [type]             [description]
     */
    public function delCompanyBranchstore($company_id)
    {
        $map = array(
            "comid" => array("EQ",$company_id)
        );
        return M("company_branchstore")->where($map)->delete();
    }

    /**
     * 获取到期会员列表数量
     * @param  string  $day           [description]
     * @param  integer $date_maturity [description]
     * @return [type]                 [description]
     */
    public function getExpiringMemberListCount($day="",$date_maturity = 0,$city)
    {
        $map = array(
            "a.on" => array("EQ",2),
            "a.end" => array("EGT",$day)
        );

        if (!empty($city)) {
            $map['a.cs'] = array("EQ",$city);
        }

        $buildSql = M("user")->where($map)->alias("a")
                        ->join("join qz_user_company b on a.id = b.userid and b.fake = 0")
                        ->join("join qz_quyu q on q.cid = a.cs")
                        ->field("q.cname,q.cid,DATEDIFF(a.end,'$day')+1 as date_diff")
                        ->buildSql();

        if (!empty($date_maturity)) {
            $where = array(
                "t.date_diff" => array("EGT",$date_maturity)
            );
        }

        $buildSql = M("user")->table($buildSql)->where($where)->alias("t")
                        ->field("t.cname,count(t.cid) as count")
                        ->group("t.cid")->order("t.cid")
                        ->limit($pageIndex.",".$pageCount)
                        ->buildSql();
        return  M("user")->table($buildSql)->alias('t1')->count();
    }

    /**
     * 获取到期会员列表
     * @param  string $day           [description]
     * @param  string $date_maturity [description]
     * @return [type]                [description]
     */
    public function getExpiringMemberList($day="",$date_maturity = 0,$city,$sort,$pageIndex,$pageCount)
    {
        $map = array(
            "a.on" => array("EQ",2),
            "a.end" => array("EGT",$day)
        );

        if (!empty($city)) {
            $map['a.cs'] = array("EQ",$city);
        }

        $buildSql = M("user")->where($map)->alias("a")
                        ->join("join qz_user_company b on a.id = b.userid and b.fake = 0")
                        ->join("join qz_quyu q on q.cid = a.cs")
                        ->field("q.cname,q.cid,DATEDIFF(a.end,'$day') as date_diff")
                        ->buildSql();

        if (!empty($date_maturity)) {
            $where = array(
                "t.date_diff" => array("EGT",$date_maturity)
            );
        }

        $order ='t.cid';
        if (!empty($sort)) {
            if ($sort == "asc") {
                $order = "count asc";
            } else {
                $order = "count desc";
            }

        }

        return M("user")->table($buildSql)->where($where)->alias("t")
                        ->field("t.cname,count(t.cid) as count")
                        ->group("t.cid")->order($order)
                        ->limit($pageIndex.",".$pageCount)
                        ->select();
    }

    /**
     * 获取CPA城市会员信息
     * @param  [type] $city_id [description]
     * @return [type]          [description]
     */
    public function getCapCityCompany($city_id)
    {
        if (is_array($city_id)) {
            $map =  array(
                "a.cs" => array("IN",$city_id)
            );
        } else {
            $map =  array(
                "a.cs" => array("EQ",$city_id)
            );
        }
        $map["a.classid"] = array("EQ",4);
        $map["b.amount"] = array("GT",0);

        return M("user")->where($map)->alias("a")
                               ->join("join qz_cpa_user_wallet b on a.id = b.user_id")
                               ->join("join qz_cpa_user_information u on u.user_id = a.id")
                               ->join("join qz_quyu q on q.cid = a.cs")
                               ->join("join qz_area c on c.qz_areaid = a.qx")
                               ->field("a.id,a.qc,a.cs,q.cname,c.qz_area,a.qx,u.lng,u.lat")
                               ->select();
    }

    /**
     * 根据登录名获取用户信息
     * @param  [type] $user [用户名]
     * @return [type] [description]
     */
    public function findUserInfoByUser($user){
        $map = array(
            "a.id"=>array("EQ",$user)
        );
        return M("user")->where($map)->alias("a")
            ->join("LEFT JOIN qz_quyu as b on b.cid = a.cs")
            ->field("a.*,b.bm,b.cname ")
            ->find();
    }

    //根据公司名模糊搜索装修公司
    public function getCompanyListByName($companyname){
        $map = [];
        $map['qc'] = array('like','%'.$companyname.'%');
        $map['jc'] = array('like','%'.$companyname.'%');
        $map['_logic'] = 'or';
        return M('user')->where($map)->field('id')->select();
    }

    //根据条件获取满足条件的设计师数量
    public function getDesignListCount($map){
        return M('user')->alias('u')
            ->where($map)
            ->join('qz_team t on t.userid = u.id and t.zt = 2','left')
            ->join('qz_user u2 on u2.id = t.comid','left')
            ->join('qz_user_company c on c.userid = u2.id','left')
            ->count('DISTINCT u.id');
    }

    //根据条件获取满足条件的设计师列表
    public function getDesignList($map,$pageIndex,$pageCount){
        return M('user')->alias('u')
            ->where($map)
            ->field('DISTINCT u.id,u.logo,u.name,u2.id comid,u2.on,u2.jc,c.fake,q.cname,q2.qz_area,t.xs,t.zt,u.register_time,l.username addname')
            ->join('qz_team t on t.userid = u.id and t.zt = 2','left')
            ->join('qz_user u2 on u2.id = t.comid','left')
            ->join('qz_user_company c on c.userid = u2.id','left')
            ->join('qz_quyu q on q.cid = u.cs','left')
            ->join('qz_area q2 on q2.qz_areaid = u.qx','left')
            ->join('qz_log_admin_design l on l.designid = u.id and l.type = 2','left')
            ->group('u.id')
            ->order('u.register_time desc')
            ->limit($pageIndex,$pageCount)
            ->select();
    }

    //根据条件获取设计师信息
    public function getDesignInfoById($map){
        return M("user")->where($map)->alias("u")
            ->join("left join qz_team as t on t.userid = u.id and t.zt = 2")
            ->join("left join qz_quyu as b on u.cs = b.cid")
            ->join("left join qz_user_des as c on c.userid = u.id")
            ->join("left join qz_area as d on u.qx = d.qz_areaid")
            ->field("u.id,u.user,u.name,u.tel,u.cs,u.qx,b.cname,d.qz_area,u.logo,b.bm,c.jobtime,c.fengge,c.lingyu,c.linian,c.text,c.school,c.cost,c.cases,t.zw,t.comid,t.px,xs")
            ->find();
    }

    //根据id获取装修公司信息（判断是否是假会员）
    public function getCompanyVipInfo($comid){
        if(empty($comid)){
            return false;
        }else{
            $map = [];
            $map['u.classid'] = 3;
            $map['u.id'] = $comid;
            return M('user')->alias('u')
                ->where($map)
                ->field('u.id,u.on,c.fake,u.jc')
                ->join('qz_user_company c on c.userid = u.id')
                ->find();
        }
    }

    //根据userid位$id的信息
    public function searchUserDesInfo($id){
        $map = [];
        $map['userid'] = $id;
        return M('user_des')->where($map)->find();
    }

    //删除user_des表的数据
    public function deleteUserdesInfo($id){
        $map = [];
        $map['userid'] = $id;
        return M('user_des')->where($map)->delete();
    }

    //添加user_des表数据
    public function addUserdesInfo($info){
        return M('user_des')->add($info);
    }

    //添加qz_team表数据
    public function addTeamInfo($info){
        return M('team')->add($info);
    }

    //更改user表信息
    public function editUserInfo($userinfo){
        return M('user')->save($userinfo);
    }

    //更改user_des 表数据
    public function editUserdesInfo($userid,$desinfo){
        return M('user_des')->where(array('userid'=>$userid))->save($desinfo);
    }
    //更改team表数据
    public function editTeamInfo($teaminfo){
        return M('team')->save($teaminfo);
    }

    //根据设计师id查询qz_team表中已入驻的信息
    public function getTeamInfoByMap($map){

        return M('team')->where($map)->find();
    }

    //删除qz_team中对应数据
    public function deleteTeamInfo($info){
        if(empty($info)){
            return false;
        }else{
            return M('team')->where($info)->delete();
        }
    }

    //修改userid为designid的所有zt不为2的数据的zt为1
    public function changeTeamInfoToRefuse($designid){
        $where = [];
        $where['userid'] = $designid;
        $where['zt'] = array('NEQ',2);
        $save = [];
        $save['zt'] = 1;
        return M('team')->where($where)->save($save);
    }

    /**
     * 根据条件获取非会员和假会员装修公司
     * @param  [type]  $query   [查询条件]
     * @param  [type]  $cs      [管辖城市]
     * @param  integer $limit   [获取数量]
     * @param  integer $classid [用户类别]
     * @return [type]           [description]
     */
    public function getNotVipUserList($query,$limit = 10,$classid = 3){
        $map = array(
            "u.classid"=>array("EQ",$classid)
        );
        $map["_complex"] = array(
            "u.jc"=>array("like","%$query%"),
            "u.qc"=>array("like","%$query%"),
            "_logic"=>"OR"
        );
        $map['_string'] = 'u.`on` = 0 or (u.`on` = 2 and c.fake = 1)';

        return M("user")->alias('u')
            ->field('u.*,c.fake')
            ->join('LEFT JOIN qz_user_company AS c ON c.userid = u.id')
            ->where($map)
            ->limit($limit)
            ->order("u.id")->select();
    }

    //获取会员列表
    public function getComidList($map){
        return M('User')->where($map)->field('id,`on`,jc')->select();
    }

    public function getCompanyByJc($jc)
    {
        $map['jc'] = $jc;
        return M('User')->where($map)->find();
    }

    public function getCompanyListByPage($page = 1, $pageSize = 20)
    {
        return M('user')->alias('u')
            ->join('LEFT JOIN qz_user_company c on c.userid = u.id')
            ->where(['u.classid' => 3])
            ->field('u.id,u.tel_safe,u.tel_safe_chk,u.`tel`,c.jd_tel_1,c.jd_tel_2,c.jd_tel_3,c.jd_tel_4')
            ->page($page, $pageSize)
            ->select();
    }
}