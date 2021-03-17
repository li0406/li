<?php
/**
 * 注册用户表 user
 */
namespace Common\Model;
use Think\Model;
class UserModel extends Model{
    protected $autoCheckFields = false; //设置autoCheckFields属性为false后，就会关闭字段信息的自动检测，因为ThinkPHP采用的是惰性数据库连接，只要你不进行数据库查询操作，是不会连接数据库的。

    //最后一个参数 验证范围 4 注册验证 5 登录验证 6 取回密码 7装修公司注册 8绑定微信/微博/QQ帐号 9微博/微信/QQ授权注册
    protected $_validate = array(
        array('user','require','请输入正确的手机/邮箱！',1,"",4),//新增的时候手机/邮箱不能为空
        array('pass','require','请输入密码',1,"",4),//新增的时候密码不能为空
        array('user','','帐号名称已经存在！',1,'unique',4), // 在新增的时候验证name字段是否唯一
        array('tel_safe','checkTel','请使用手机/邮箱注册',0,'callback',4),//在新增的时候验证用户名是否是邮箱或者电话
        array('mail_safe','checkMail','请使用手机/邮箱注册',0,'callback',4),//在新增的时候验证用户名是否是邮箱或者电话
        array('pass',"6,20",'请输入最少6位密码1',1,'length',4), //新增的时候验证密码格式
        array('cs','require','请选择城市',0,'',4),//新增的时候验证公司的城市是否选择
        array('quyu','require','请选择所在区域',0,'',4),//新增的时候验证公司的区域是否选择
        array('pass','require','请输入密码',1,"",6),//新增的时候密码不能为空
        array('confirmpwd','pass','两次密码不匹配',0,"confirm",6),
        array('user','require','无效的用户帐号！',1,"",5),//登陆时用户名非空验证
        array('pass','require','无效的用户密码',1,"",5),//登陆时时候密码不能为空

        array('user','require','请输入正确的手机/邮箱！',1,"",7),//新增的时候手机/邮箱不能为空
        array('user','','帐号名称已经存在！',1,'unique',7), // 在新增的时候验证name字段是否唯一
        array('tel_safe','checkTel','请使用手机/邮箱注册',0,'callback',7),//在新增的时候验证用户名是否是邮箱或者电话
        array('cs','require','请选择城市',0,'',7),//新增的时候验证公司的城市是否选择
        array('quyu','require','请选择所在区域',0,'',7),//新增的时候验证公司的区域是否选择

        array('user','require','请输入正确的手机/邮箱！',1,"",8),//绑定的帐号不能为空

        array('user','','帐号已经存在！',1,'unique',9), // 在微博/微信/QQ授权注册的时候验证name字段是否唯一
        array('weibo_account','','微博帐号已绑定！',2,'unique',9), //在微博/微信/QQ授权注册的时候验证weibo_account字段是否唯一
    );

    //获取装修公司活跃度排行
    public function getActiveCompany($cs,$num = 5){
        $map['a.on'] = array("EQ",2);
        //$map['b.fake'] = array("EQ",0);
        !empty($cs) && $map['a.cs'] = array('eq',$cs);
        return M("user")->where($map)->alias("a")
                 ->join("LEFT JOIN qz_user_company as b on b.userid = a.id")
                 ->join("LEFT JOIN qz_quyu as q on a.cs = q.cid " )
                 ->field("a.id,a.qc,a.jc,a.logo,a.activte_score,q.bm")
                 ->order('activte_score DESC')
                 ->limit("0,".$num)
                 ->select();
    }

    /**
     * 验证用户名是否是手机
     * @return [type] [description]
     */
    protected function checkTel($str){
        $reg = '/^([0-9]{3,4}\-)?([0-9]{7,8})$|^[0-9]{11}$/';
        if(!preg_match($reg, $str)){
            return false;
        }
        return true;
    }

    /**
     * 验证用户名是否是邮箱
     * @return [type] [description]
     */
    protected function checkMail($str){
        $reg ='/^[A-Za-z0-9]+([-_.][A-Za-z\d]+)*@([A-Za-z0-9]+[-.])+[A-Za-z0-9]{2,5}$/';
        if(!preg_match($reg, $str)){
            return false;
        }
        return true;
    }

    /**
     * 注册用户数量
     * @$classid  用户类型
     * @return [type] [description]
     */
    public function getRegisterCount($classid,$cs=''){
        $map = array(
                "classid" =>array("EQ",$classid)
                     );
        if(!empty($cs)){
            $map["cs"] = array("EQ",$cs);
        }
        return M("user")->where($map)->count();
    }

    /**
     * 获取用户信息列表数量
     * @param  string $classid [用户类型]
     * @param  string $keyword [关键字]
     * @param  string $cs      [所在城市]
     * @return [type]          [description]
     */
    public function getUserInfoListCount($classid = '',$keyword = "",$cs = '',$fw ='',$fg ='',$gm = ''){
        $map["classid"] = array("EQ",$classid);
        //如果是装修公司根据关键字查询公司全称,否则根据用户昵称查询
        if(!empty($keyword)){
            if($classid == 3){
                $map["qc"] = array("LIKE","%$keyword%");
            }else{
                $map["name"] = array("LIKE","%$keyword%");
            }
        }
        if(!empty($cs)){
            $map["cs"] = array("EQ",$cs);
        }
        if($classid == 3){
            if(!empty($fw)){
                $fw = (int)$fw;
                $where["_string"] = "FIND_IN_SET($fw,quyu)";
            }

            if(!empty($fg)){
                $fg = (int)$fg;
                $where["_string"] = "FIND_IN_SET($fg,fengge)";
            }

            if(!empty($gm)){
                $where["guimo"] = array("EQ",$gm);
            }

            $buildSql = M("user")->where($map)->buildSql();

            $result = M("user")->table($buildSql)->alias("a")->where($where)
                        ->join("INNER JOIN qz_user_company as b on a.id = b.userid")
                        ->count();
        }

        return $result;
    }

    /**
     * 获取用户信息列表
     * @param  string $classid [用户类型]
     * @param  string $cs [所在城市]
     * @param  string $keyword [关键字]
     * @return [type]          [description]
     */
    public function getUserInfoList($pagesize= 1,$pageRow = 10,$classid = '',$keyword = "",$cs = '',$fw='',$fg='',$gm=''){
        $map["classid"] = array("EQ",$classid);
        if(!empty($cs)){
            $map["cs"] = array("EQ",$cs);
        }

        //如果是装修公司根据关键字查询公司全称,否则根据用户昵称查询
        if(!empty($keyword)){
            if($classid == 3){
                $map["qc"] = array("LIKE","%$keyword%");
            }else{
                $map["name"] = array("LIKE","%$keyword%");
            }
        }

        if($classid == 3){
            if(!empty($fw)){
                $fw = (int)$fw;
                $map["_string"] = "FIND_IN_SET($fw,quyu)";
            }

            if(!empty($fg)){
                $fg = (int)$fg;
                $map["_string"] = "FIND_IN_SET($fg,fengge)";
            }

            if(!empty($gm)){
                $map["guimo"] = array("EQ",$gm);
            }

            $buildSql = M("user")->where($map)->alias("a")
                          ->join("inner join qz_user_company as q on q.userid = a.id")
                          ->field("a.`id`,`on`,`qc`,`dz`,`pv`,`uv`,`cs`,`logo`,uptime,info_time,case_count as count,q.fake")
                          ->order("`on` desc,q.fake,info_time desc")
                          ->limit($pagesize.",".$pageRow)
                          ->buildSql();

            $buildSql = M("user")->table($buildSql." as t")
                         ->join("left join (select count(id) as dcount,comid from qz_team where zt = 2 group by comid) as c on c.comid = t.id")
                         ->join("left join (select count(id) as ccount,comid from qz_comment group by comid) as d on d.comid = t.id")
                         ->join("left join qz_orders as f on f.qiandan_companyid = t.id")
                         ->join("inner join qz_quyu as e on e.cid = t.cs")
                         ->field("t.*,c.dcount,d.ccount,e.bm,count(f.id) as qdcount")
                         ->group("t.id")
                         ->buildSql();

            //获取最新的签单活动
            $buildSql = M("user")->table($buildSql)->alias("t2")
                                 ->join("LEFT JOIN qz_info as i on i.uid = t2.id and i.type = 1 and i.on = 1")
                                 ->field("t2.*,i.title,i.time as infotime,i.id as infoid")
                                 ->order("infotime desc")
                                 ->buildSql();

            $buildSql = M("user")->table($buildSql)->alias("t3")
                                 ->group("t3.id")
                                 ->field("t3.*")
                                 ->buildSql();

            $result = M("user")->table($buildSql)->alias("t1")
                               ->join("left join qz_orders_company_report as g on g.order_company_id = t1.id")
                               ->field("t1.*,count(g.id) as zzqd")
                               ->group("t1.id")
                               ->order("t1.`on` desc,t1.fake,t1.info_time desc")
                               ->select();
        }
        return $result;
    }

    /**
     * 获取装修公司签单数量
     * @param  integer $limit [显示数量]
     * @param  string  $cs    [所在城市]
     * @return [type]         [description]
     */
    public function getQiandanList($limit = 10,$cs=''){
        //特殊需求 逻辑判断放入Model 中 签单数量分为 分配签单 自主咨询签单 如果数量不够10 去查询热门装修公司补上来
        $qiandan_list= $this->getQianDanfpandzz($limit,$cs);//获取分配签单和自助咨询签单
        $limit_company_count=$limit-count($qiandan_list);//看是不是不够10个 还需要补多少个
        if ($limit_company_count>0)
        {
            #说明取的不够10个 剩下的继续取热门装修公司
            $hot_company_list=$this->getHotUserInfoList($limit,$cs);
            foreach ($qiandan_list as $k => $v)
            {
                $company_id_list[]=$v['comid'];//获取已经查到的装修公司id 列表
            }
            foreach ($hot_company_list as $key => $value)
            {
                if($limit_company_count==0)
                {
                    break;//如果已经减到0了说明补够了 就不需要再补了
                }
                if (!in_array($value['id'],$company_id_list)) {
                    //为了怕添加的热门装修公司已经在查到的列表中
                    $limit_company['comid'] =$value['id'];
                    $limit_company['qc']    =$value['qc'];
                    $limit_company['jc']    =$value['jc'];
                    $limit_company['logo']  =$value['logo'];
                    $qiandan_list[]=$limit_company;
                    $limit_company_count--;//压入一个就将所需数量减1
                }
            }
        }
        return $qiandan_list;//热门签单公司最终形成的列表
        // $map = array(
        //         "classid" => array("EQ",3)
        //              );
        // if(!empty($cs)){
        //     $map["a.cs"] = array("EQ",$cs);
        // }

        // return M("user")->where($map)->alias("a")
        //                 ->join("inner join qz_orders as b on a.id = b.qiandan_companyid")
        //                 ->group("a.id")->order("count desc")
        //                 ->field("count(*) as count ,a.id,a.jc,a.qc")->limit($limit)
        //                 ->select();
    }

    // 取出该城市的签单公司  (分配订单签单 + 自主咨询签单 联合)
    public  function getQianDanfpandzz($cnt, $city=0)
    {
        if ($cnt < 1) {
            $cnt= 1;
        }

        $sql    = sprintf('
            SELECT oo.comid,oo.xiaoqu,oo.qc,oo.jc,oo.logo,oo.addtime,oo.lytype,qz_quyu.bm FROM(
            (SELECT
                o.qiandan_companyid as comid,o.xiaoqu,u.qc,u.jc,u.logo,o.qiandan_addtime as addtime,"fpqd" AS lytype, x.cs
            FROM (
                SELECT
                    o.qiandan_companyid, max(o.qiandan_addtime) as qiandan_addtime,u.cs
                FROM qz_orders o
                INNER JOIN qz_user u ON o.qiandan_companyid = u.id AND u.`on` = 2
                INNER JOIN qz_user_company uc ON uc.userid = u.id AND uc.fake = 0
                WHERE ( o.qiandan_companyid > 0 ) AND ( o.qiandan_status = 1 )
                     AND ( o.cs = %d OR 0 = %d )
                GROUP BY o.qiandan_companyid
                ORDER BY qiandan_addtime DESC
            ) x
            INNER JOIN qz_orders o
                on x.qiandan_companyid = o.qiandan_companyid
                and x.qiandan_addtime = o.qiandan_addtime
            INNER JOIN qz_user u
                ON o.qiandan_companyid = u.id
                and (u.cs = %d OR 0 = %d)
            ORDER BY o.qiandan_addtime DESC)
            UNION
            (SELECT
                o.order_company_id as comid,o.xiaoqu,u.qc,u.jc,u.logo,o.time_add as addtime,"zxqd" AS lytype,x.cs
            FROM (
                SELECT
                    o.order_company_id, max(o.time_add) as time_add,u.cs
                FROM qz_orders_company_report o
                INNER JOIN qz_user u ON o.order_company_id = u.id AND u.`on` = 2
                INNER JOIN qz_user_company uc ON uc.userid = u.id AND uc.fake = 0
                WHERE ( o.order_company_id > 0 ) AND ( o.order_on = 1 )
                     AND ( o.deleted = 0) AND ( o.cs = %d OR 0 = %d )
                GROUP BY o.order_company_id
                ORDER BY time_add DESC
            ) x
            INNER JOIN qz_orders_company_report o
                on x.order_company_id = o.order_company_id
                and x.time_add = o.time_add
            INNER JOIN qz_user u
                ON o.order_company_id = u.id
                and (u.cs = %d OR 0 = %d)
            ORDER BY o.time_add DESC)
            ORDER BY addtime DESC LIMIT %d
            ) AS oo
            inner join qz_quyu   on oo.cs=qz_quyu.cid
            GROUP BY oo.comid
            ORDER BY oo.addtime DESC
            ', $city, $city, $city, $city,$city, $city, $city, $city, $cnt);

        if ($list = M()->query($sql)) {
            foreach ($list as &$row){
                $row['title'] = $row['qc'];
            }
            unset($row);
        }

        return  $list;
    }
    /**
     * 获取热门装修公司信息
     * @param  integer $limit [description]
     * @param  string  $cs    [description]
     * @return [type]         [description]
     */
    public function getHotUserInfoList($limit = 10,$cs = ''){
        $map = array(
                "case_count" =>array("NEQ",0),
                array(
                    array(
                        "a.on" =>array("EQ",2),
                        "q.fake"=>array("EQ",0)
                          ),
                    array(
                        "a.on"=>array("EQ",2),
                        "q.fake"=>array("EQ",1)
                        ),
                    "_logic"=>"OR"
                ),
                "classid"=>array("EQ",3),
                "a.id"=>array("NOT IN",array(1401,24))
                     );
        if(!empty($cs)){
            $map["cs"] = array("EQ",$cs);
        }
        //1.筛选符合条件的用户数据
        $buildSql = M("user")->where($map)->alias("a")
                             ->join("LEFT join qz_user_company as q on q.userid = a.id")
                             ->field("a.id,a.on,a.uptime,a.jc,a.qc,a.logo,a.case_count,a.pv,a.qq,a.qq1,q.guimo,q.quyu,q.fuwu,q.jiawei,q.fake")
                             ->order("q.fake,uptime DESC")
                             ->limit($limit)->buildSql();

        //2.取出用户的服务类型及服务区域
        $buildSql =M("user")->table($buildSql)->alias("t")
                            ->join("LEFT JOIN qz_area as b on FIND_IN_SET(b.qz_areaid,t.quyu)")
                            ->join("LEFT JOIN (select * from qz_leixing ORDER BY id desc) as c on FIND_IN_SET(c.id,t.fuwu)")
                            ->join("LEFT JOIN qz_guimo as f on f.id = t.guimo")
                            ->field("t.*,GROUP_CONCAT(DISTINCT  b.qz_area ORDER BY b.qz_areaid) as area,GROUP_CONCAT(DISTINCT c.`name` ORDER BY c.id desc) as fw,f.`name` as gname")
                            ->group("t.id")->order("c.id desc")->buildSql();

        //3.取出用户的案例及案例数
        $buildSql = M("user")->table($buildSql)->alias("t1")
                             ->join("LEFT JOIN (select count(id) as commentcount,comid, avg(IF(sj<>0,sj,null)) avgsj,avg(IF(fw<>0,fw,null)) avgfw,avg(IF(sg<>0,sg,null)) avgsg,avg(IF(sj=0,count,null)) avgcount from qz_comment group by comid) as e on e.comid = t1.id")
                             ->group("t1.id")->field("t1.*,e.*")->buildSql();

        //4.取出相应的数据
        $buildSql = M("user")->table($buildSql)->alias("t2")
                        ->join("LEFT JOIN qz_cases as d on d.uid = t2.id and d.classid in (1,2) and d.isdelete =1")
                        ->field("t2.*,d.id as bid")
                        ->order("d.id desc")
                        ->buildSql();

        return M("user")->table($buildSql)->alias("t3")
                        ->group("t3.id")
                        ->order("t3.fake,t3.uptime DESC")
                        ->select();
    }

    /**
     * 获取装修公司用户信息
     * @return [type] [description]
     */
    public function getUserInfoById($id = '',$cs=''){
        $map = array(
            "a.id"=>array("EQ",$id),
            "a.classid"=>array("IN",array(3,6))
                     );
        if(!empty($cs)){
            $map["a.cs"] = array("EQ",$cs);
        }

		//1.查询相关的用户信息
        $buildSql = M("user")->where($map)->alias("a")
                             ->join("left join qz_user_company as b on a.id = b.userid")
                             ->join("inner join qz_quyu as e on e.cid = a.cs")
                             ->join("left join qz_area as z on a.qx = z.qz_areaid")
                             ->field("a.id,a.classid,a.on,a.cs,a.qx,e.cname as cityname,z.qz_area as area_name,a.qc,a.jc,a.kouhao,a.logo,a.pv,a.video,a.qq,a.qq1,a.cal,a.cals,a.dz,a.tel,a.case_count as casecount,b.text as jianjie,b.jiawei,b.fuwu,b.fengge,b.quyu,b.fake,b.nickname,b.nickname1,b.hengfu,b.guimo,b.chengli,b.img_host,b.cooperate_mode as cooperate_mode2")
                             ->buildSql();
        //2.查询出风格，区域，风格的信息
        $buildSql = M("user")->table($buildSql)->alias("t")
                             ->join("LEFT JOIN qz_area as c on FIND_IN_SET(c.qz_areaid,t.quyu)")
                             ->join("LEFT JOIN qz_leixing as d on FIND_IN_SET(d.id,t.fuwu)")
                             ->join("LEFT JOIN qz_fengge as e on FIND_IN_SET(e.id,t.fengge)")
                             ->join("LEFT JOIN qz_guimo as f on f.id = t.guimo")
                             ->field("t.id,t.classid,t.on,t.chengli,t.cs,t.qx,t.cityname,t.area_name,t.qc,t.jc,t.kouhao,t.img_host,t.logo,t.video,t.pv,t.jianjie,t.jiawei,t.fake,t.nickname,t.nickname1 ,t.hengfu,t.qq,t.qq1,t.cal,t.cals,t.dz,t.tel,t.casecount, GROUP_CONCAT(DISTINCT  c.qz_area ORDER BY c.qz_areaid) as area,GROUP_CONCAT(DISTINCT d.name order by d.px DESC) as fw,GROUP_CONCAT(DISTINCT e.name order by e.px ) as fg,f.name as gm,t.cooperate_mode2")
                             ->group("t.id")->buildSql();
        //3.取出设计师的数量
        $buildSql = M("user")->table($buildSql)->alias("t1")
                             ->join("LEFT JOIN qz_team as f on f.comid = t1.id and f.zt = 2")
                             ->field("t1.*,count(f.id) as dcount")
                             ->buildSql();

        // 关联 qz_user_company_employee表， 有数据，表示设计师是洗过数据了
        $buildSql = M("user")->table($buildSql)->alias('tt')
                             ->join('LEFT JOIN qz_user_company_employee ce on ce.company_id = tt.id and ce.state = 1 and ce.position in (2,3,4)')
                             ->field('tt.*,count(ce.id) as dcount2, case when count(ce.id) > 0 or tt.cooperate_mode2 = 2 then 2 else 1 end as cooperate_mode')
                             ->buildSql();

        //4.取出评论数量
        return M("user")->table($buildSql)->alias("t2")
                             ->join("LEFT JOIN qz_comment as g on g.comid = t2.id and g.isveritfy = 0")
                             ->field("t2.*,count(g.id) as ccount,count(IF(g.sj <>0,g.id,null)) as newcount,count(IF(g.sj =0,g.id,null)) as oldcount, count(IF(g.count>=9 and g.sj = 0,g.count,null)) as oldgood, count(IF(g.sj>=9 and g.fw>=9 and g.sg >=9,g.id,null)) as good, avg(IF(g.sj<>0,g.sj,null)) avgsj,avg(IF(g.fw<>0,g.fw,null)) avgfw,avg(IF(g.sg<>0,g.sg,null)) avgsg,avg(IF(g.sj=0,g.count,null)) avgcount")
                             ->select();
    }

    /**
     * 获取装修公司用户分店信息
     * @return [type] [description]
     */
    public function getUserFendianInfoById($id = ''){
        $map = array(
            "comid"=>array("EQ",$id),

        );
        return M("company_branchstore")->where($map)
            ->field(" id as hid ,shortname,tel as htel,addr,qq as qq3,qq1 as qq4,nickname as nickname2,nickname1 as nickname3")
            ->find();
    }

    /**
     * 获取装修公司用户评论信息
     * @return [type] [description]
     */
    public function getUserPinlunInfoById($id = ''){
        $firstday=date('Y-m-d',strtotime("-1 month"));
        $lastday=date('Y-m-d');
        $map = array(
            "comid"=>array("EQ",$id),
            "day"=>array("between",array($firstday,$lastday))
        );
        return M("user_company_rank")->where($map)
            ->field("ping,haoping")
            ->order('day desc')
            ->find();
    }

    /**
     * 获取装修公司首页设计师列表
     * @return [type] [description]
     */
    public function getDesignerListByCompany($comid ='',$limit = 10){
        $map = array(
                "b.comid"=>array("EQ",$comid)
                     );
        $buildSql = M("user")->where($map)->alias("a")
                        ->join("INNER JOIN qz_team as b on b.userid = a.id and b.zt = 2")
                        ->field("a.*,b.zw,b.px,b.xs,b.zt")->limit($limit)
                        ->order("b.px desc")
                        ->buildSql();
        return M("user")->table($buildSql)->alias("t")
                        ->join("LEFT JOIN qz_cases as b on t.id = b.userid")
                        ->field("count(b.id) as casecount,t.*")
                        ->order("t.px desc")
                        ->group("t.id ")
                        ->select();

    }

    /**
     * 获取装修公司团队设计师
     * @return [type] [description]
     */
    public function getTeamDesignerList($comid='',$zw='',$zt ="",$pageIndex = 0,$pageCount=10)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $map = array(
                "b.comid"=>array("EQ",$comid)
                     );
        if(!empty($zw)){
            $map["zw"] = array("EQ",$zw);
        }

        if(!empty($zt)){
            $map["zt"] = array("EQ",$zt);
        }

        //查询前5位设计师
        $buildSql = M("user")->where($map)->alias("a")
                        ->join("INNER JOIN qz_team as b on b.userid = a.id ")
                        ->field("a.*,b.zw,b.px,b.zt,b.xs")->limit($pageIndex.",".$pageCount)
                        ->order("b.px desc")
                        ->buildSql();
        //查询出设计师的简介信息
        return  M("user")->table($buildSql)->alias("t")
                             ->join("LEFT JOIN qz_user_des as b on t.id = b.userid")
                             ->field("t.id as uid,t.logo,t.name,t.zw,t.px,t.zt,t.xs,b.*")
                             ->order("t.px desc")
                             ->select();

    }
     /**
     * 获取装修公司团队设计师数
     * @return [type] [description]
     */
    public function getTeamDesignerListCount($comid='',$zw='',$zt =""){
        $map = array(
                "b.comid"=>array("EQ",$comid)
                     );
        if(!empty($zw)){
            $map["zw"] = array("EQ",$zw);
        }

        if(!empty($zt)){
            $map["zt"] = array("EQ",$zt);
        }
       return M("user")->where($map)->alias("a")
                        ->join("INNER JOIN qz_team as b on b.userid = a.id and b.zt = 2")
                        ->count();
    }

    /**
     * 获取公司信息
     * @param  [type] $id [公司编号]
     * @param  [type] $cs [所在城市]
     * @return [type]     [description]
     */
    public function getCompanyInfo($id,$cs){
        $map = array(
            "a.id"=>array("EQ",$id),
            "a.cs"=>array("EQ",$cs)
                     );
        //1.查询相关的用户信息
        $buildSql = M("user")->where($map)->alias("a")
                             ->join("inner join qz_user_company as b on a.id = b.userid")
                             ->field("a.id,a.on,a.cs,a.qc,a.kouhao,a.logo,a.pv,a.video,a.qq,a.qq1,a.cal,a.cals,a.dz,a.tel,a.case_count as casecount,b.text as jianjie,b.jiawei,b.baozhang,b.fuwu,b.fengge,b.quyu,b.fake,b.nickname,b.nickname1,b.hengfu,b.guimo,b.chengli,b.luxian,b.map_address,b.lng,b.lat,b.map_info")
                             ->buildSql();
        //2.查询出风格，区域，风格的信息
        $buildSql = M("user")->table($buildSql)->alias("t")
                             ->join("LEFT JOIN qz_area as c on FIND_IN_SET(c.qz_areaid,t.quyu)")
                             ->join("LEFT JOIN qz_leixing as d on FIND_IN_SET(d.id,t.fuwu)")
                             ->join("LEFT JOIN qz_leixing as d2 on FIND_IN_SET(d2.id,t.baozhang)")
                             ->join("LEFT JOIN qz_fengge as e on FIND_IN_SET(e.id,t.fengge)")
                             ->join("LEFT JOIN qz_guimo as f on f.id = t.guimo")
                             ->field("t.map_address,t.lng,t.lat,t.map_info,t.id,t.on,t.luxian,t.chengli,t.cs,t.qc,t.kouhao,t.logo,t.video,t.pv,t.jianjie,t.jiawei,t.fake,t.nickname,t.nickname1 ,t.hengfu,t.qq,t.qq1,t.cal,t.cals,t.dz,t.tel,t.casecount, GROUP_CONCAT(DISTINCT  c.qz_area ORDER BY c.qz_areaid) as area,GROUP_CONCAT(DISTINCT d.name order by d.px DESC) as fw,GROUP_CONCAT(DISTINCT d2.name order by d.px DESC) as baozhang,GROUP_CONCAT(DISTINCT e.name order by e.px ) as fg,f.name as gm")
                             ->group("t.id")->buildSql();

        return M("user")->table($buildSql)->alias("t")
                             ->join("LEFT JOIN qz_company_img as c on c.userid = t.id")
                             ->field("t.*,c.img,c.img_host")
                             ->order('c.id desc')
                             ->select();

    }

  /**
   * 获取当前城市的设计师信息
   * @param  string $cs [description]
   * @return [type]     [description]
   */
    public function getDesingersByCity($cs ='',$limit = 10){
        $map = array(
                "a.cs"=>array("EQ",$cs),
                //因为设计师是手动推荐的 所以这里就不限制假会员了 为了新城市占位美观 "e.fake"=>array("EQ",0)
                     );
        return M("desadv")->where($map)->alias("a")
                             ->join("INNER JOIN qz_team as b on b.userid = a.comid and b.zt = 2")
                             ->join("INNER JOIN qz_user as c on b.userid = c.id")
                             ->join("INNER JOIN qz_user as d on b.comid = d.id")
                             ->join("INNER JOIN qz_user_company as e on e.userid = d.id")
                             ->field("a.*,d.id as comid,d.jc,c.name,a.comid as userid,c.logo,b.zw")
                             ->group("userid")
                             ->order("orders desc,id desc")
                             ->limit($limit)
                             ->select();
    }

    /**
     * 获取公司设计师信息
     * @param  string $id  [设计师编号]
     * @param  string $cid [公司编号]
     * @return [type]      [description]
     */
    public function getDesingerInfo($id='',$cs =''){
        $map = array(
                "a.id"=>array("EQ",$id),
                "a.cs"=>array("EQ",$cs),
                "a.classid"=>array("EQ",2)
                     );
        $buildSql =  M("user")->where($map)->alias("a")
                 ->join("LEFT join qz_team as b on a.id = b.userid and b.zt = 2")
                 ->join("LEFT JOIN qz_user_des as c on c.userid = a.id")
                 ->join("left join qz_user as d on d.id = b.comid")
                 ->join("left join qz_quyu as e on e.cid = d.cs")
                 ->field("a.id as uid,a.name,c.*, a.logo,d.qc,a.pv,d.id as comid,e.bm,b.zw")->buildSql();
        return  M("user")->table($buildSql)->alias("t")
                    ->join("left join qz_article as e on e.userid = t.userid")
                    ->field("count(e.id) as acount,t.*")
                    ->find();
    }

    /**
     * 获取指定公司设计师信息
     */
    public function getDesingerInfoByCompany($company_id = [], $qianfancompanyIds = [],$page = 0,$page_count = 40)
    {

        if ($company_id) {
            $map = array(
                "u.id" => array("in", $company_id),
                "design.classid" => array("EQ", 2)
            );
            $buildSql = M("user")->alias("u")
                ->field('design.id, design.user,design.name,design.logo,t.comid,CONVERT(design.pv, SIGNED ) AS pv,c.jobtime,c.cost,design.cs,t.zw,0 as sort,1 as company_type')
                ->join("qz_team t on t.comid = u.id")
                ->join("qz_user design on design.id = t.userid")
                ->join("left join qz_user_des as c on c.userid = design.id")
                ->where($map)
                ->buildSql();

            $buildSql = M()->table($buildSql)->alias("u2")
                ->join('LEFT JOIN qz_user_company_employee ce on ce.company_id = u2.comid')
                ->where("ce.id is null")
                ->field("u2.*")
                ->buildSql();
        }

        //
        if (!empty($qianfancompanyIds)) {
            $maop2 = array(
                'ce.company_id' => array('in', $qianfancompanyIds),
                'ce.position' => array('in', [2, 3, 4]),     // 2设计师 3首席设计师  4设计总监
                'ce.state' => array('eq', 1) // 在职
            );
            $buildsql2 = M('user_company_employee')->alias('ce')
                ->field('ce.id,ce.account as `user`,ce.nick_name as `name`,ce.logo,ce.company_id comid,CONVERT(c.popularity, SIGNED ) AS pv,c.jobtime,c.cost,com.cs,case when ce.position = 2 then "设计师" when ce.position = 3 then "首席设计师" when ce.position = 4 then "设计总监" else "" end as zw,cds.sort,2 as company_type')
                ->where($maop2)
                ->join('inner join qz_user as com on com.id = ce.company_id')
                ->join('left join qz_user_company_designer_sorting as cds on cds.employee_id = ce.id and cds.company_id = ce.company_id')
                ->join("left join qz_user_des as c on c.userid = ce.id and c.company_id = ce.company_id")
                ->buildSql();
        }


        //4.合并SQL
        if (!empty($company_id) && !empty($qianfancompanyIds)) {
            $sql = M()->table($buildSql)->alias("k")
                ->union($buildsql2, true)
                ->buildSql();
        } elseif (!empty($company_id) && empty($qianfancompanyIds)) {
            $sql = $buildSql;
        } elseif (empty($company_id) && !empty($qianfancompanyIds)) {
            $sql = $buildsql2;
        } else {
            return [];
        }

        return M()->table($sql)->alias('t')
            ->limit($page, $page_count)
            ->order('CONVERT(t.pv, SIGNED ) desc,t.sort')
            ->select();
    }

    /**
     * 更新用户的点击量或者咨询量
     * @return [type] [description]
     */
    public function editUvAndPv($id,$data){
        $map = array(
                    "id"=>$id
                          );
        M("user")->where($map)->setInc($data);
    }

    /**
     * 获取城市的设计师列表
     * @param  [type] $cs [所在城市]
     * @return [type] [description]
     */
    public function getDesignerList($cs ='',$pageIndex = 0,$pageCount = 10)
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
                         ->field("count(a.id) as count,a.userid,a.uid as cid,c.name,b.zw,c.logo,f.text as jianjie")
                         ->limit($pageIndex.",".$pageCount)
                         ->order("h.on desc,g.fake,count desc")->group("a.userid")->select();
    }

    /**
     * 获取城市的设计师列数量
     * @param  [type] $cs [所在城市]
     * @return [type]     [description]
     */
    public function getDesignerListCount($cs){
        $map = array(
                "a.cs"=>array("EQ",$cs)
                     );
        $buildSql = M("cases")->where($map)->alias("a")
                         ->join("LEFT JOIN qz_team as b on a.userid = b.userid")
                         ->join("LEFT JOIN qz_user_des as f on f.userid = a.userid")
                         ->join("INNER JOIN qz_user as c on c.id = a.userid")
                         ->join("LEFT JOIN qz_user_company as g on g.userid = a.uid")
                         ->group("a.userid")
                         ->field("a.id")
                         ->buildSql();
        return M("cases")->table($buildSql)->alias("t")->count();
    }

    /**
     * 添加用户
     */
    public function addUser($data){
        return M("user")->add($data);
    }

    /**
     * 根据登录名获取用户信息
     * @param  [type] $user [用户名]
     * @return [type] [description]
     */
    public function findUserInfoByUser($user){
        $map = array(
                "a.user"=>array("EQ",$user)
                     );
        return M("user")->where($map)->alias("a")
                        ->join("LEFT JOIN qz_quyu as b on b.cid = a.cs")
                        ->field("a.*,b.bm,b.cname ")
                        ->find();
    }

    //根据手机号取用户信息
    public function findUserInfoByTel($tel){
        $map = array(
                "a.tel_safe"=>array("EQ",$tel),
                "a.tel_safe_chk"=>array("EQ",1)
        );
        return M("user")->where($map)->alias("a")
                        ->join("LEFT JOIN qz_quyu as b on b.cid = a.cs")
                        ->field("a.*,b.bm,b.cname ")
                        ->find();
    }

    /**
     * 编辑用户信息
     * @return [type] [description]
     */
    public function edtiUserInfo($id,$data){
        $map = array(
                "id"=>$id
                     );
        return M("user")->where($map)->save($data);
    }

    /**
     * 根据编号获取企业单个用户的信息
     * @return [type] [description]
     */
    public function getSingleUserInfoById($id){
        $map = array(
                "a.id"=>array("EQ",$id)
                     );
        return M("user")->where($map)->alias("a")
                        ->join("left join qz_quyu as b on a.cs = b.cid")
                        ->join("left join qz_user_company as c on c.userid = a.id")
                        ->field("a.*,b.bm,c.fake,b.cname")
                        ->find();
    }

    /**
     * 获取设计师的详细用户信息
     * @return [type] [description]
     */
    public function getSingeleDesInfoById($id,$comid){
        $map = array(
                "a.id"=>array("EQ",$id)
                     );
        if(!empty($comid)){
            $map["t.comid"] = array("EQ",$comid);
        }
        return M("user")->where($map)->alias("a")
                        ->join("left join qz_team as t on t.userid = a.id and t.zt = 2")
                        ->join("left join qz_quyu as b on a.cs = b.cid")
                        ->join("left join qz_user_des as c on c.userid = a.id")
                        ->field("a.*,b.bm,c.jobtime,c.fengge,c.lingyu,c.linian,c.text,c.school,c.cost,c.cases,t.zw,t.px,xs")
                        ->find();
    }

    /**
     * 获取装修公司 效果图 文章 点评 数量统计
     * @param  array(1,2,3) or '1,2,3'  公司id
     * @return false or 查询结果集
     */
    public function getCompanysCount($comids){
        if (empty($comids)) {
            return false;
        }
        if (!is_array($comids)) {
            $comids = array_filter(explode(',', trim($comids, ',')));
        }
        $map = array(
                "u.id"=>array("IN",$comids)
                     );

        //1.获取当前所有用户信息
        $u_buildSql = M("user")->where($map)->alias("u")
                 ->field("u.id,u.jc,u.on")
                 ->buildSql();

        //2.查询会员的案例数
        $cases_buildSql = M("")->table($u_buildSql)->alias("t")
                             ->join("INNER JOIN qz_cases as c on c.uid = t.id")
                             ->group("t.id")
                             ->field("t.*,count(c.id) as casecount")
                             ->buildSql();

        //3.查询公司的发布文章数
        $info_buildSql = M("")->table($cases_buildSql)->alias("t1")
                             ->join("LEFT JOIN qz_info as info on info.uid = t1.id")
                             ->group("t1.id")
                             ->field("t1.*,count(info.id) as infocount")
                             ->buildSql();

        //4.查询公司的业主点评数
        $comment_buildSql = M("")->table($info_buildSql)->alias("t3")
                             ->join("LEFT join qz_comment as d on d.comid = t3.id")
                             ->group("t3.id")
                             ->field("t3.*,count(d.id) as commentcount")
                             ->buildSql();

        return M("")->table($comment_buildSql)->alias("a")
                             ->field("a.*")
                             ->select();
    }


    /**
     * 获取装修公司基本信息
     * @param  [type] $comid [description]
     * @return [type]        [description]
     */
    public function getCompanyInfoByAdmin($comid,$cs){
        $map = array(
                "a.id"=>array("EQ",$comid),
                "a.cs"=>array("EQ",$cs)
                     );
        //1.获取用户的基本信息
        $buildSql = M("user")->where($map)->alias("a")
                             ->buildSql();
        //2.查询用户的未读订单信息
        $buildSql = M("user")->table($buildSql)->alias("t")
                             ->join("LEFT JOIN qz_order_info as b on b.com = t.id and b.isread = 0 and b.type_fw not in(11,22)")
                             ->join("INNER JOIN qz_quyu as q on q.cid = t.cs")
                             ->group("b.com")
                             ->field("t.*,count(b.id) as unreadcount,q.bm")
                             ->buildSql();
        //3.查询用户未读系统信息信息
        return M("user")->table($buildSql)->alias("t1")
                        ->join("left join qz_user_company as c on t1.id = c.userid")
                        ->field("t1.*,c.nickname,c.nickname1,c.quyu,c.fuwu,c.fengge,c.jiawei,c.chengli,c.guimo,c.luxian,c.text as about_jianjie,c.hengfu")
                        ->find();
    }

    /**
     * 获取装修公司评分信息【管理后台使用】
     * @return [type] [description]
     */
    public function getCompanyStatusByAdmin($comid,$cs){
        $map = array(
                "a.id"=>array("EQ",$comid),
                "a.cs"=>array("EQ",$cs)
                    );
        //1.获取用户的基本信息
        $buildSql = M("user")->where($map)->alias("a")
                             ->join("LEFT JOIN qz_user_company as b on b.userid = a.id")
                             ->join("INNER JOIN qz_quyu as c on c.cid = a.cs")
                             ->field("a.id as uid,a.name,a.cal,a.cals,a.sex,a.tel,a.qq,a.qq1,a.qc,a.jc,a.on,a.logo,a.check_score,a.check_time,a.mail_safe_chk,a.tel_safe_chk,a.wx_unionid,b.chengli,a.kouhao,b.text as jianjie,guimo,b.fuwu,b.quyu,b.fengge,b.current_contact,b.jiawei,b.fake,c.cname,c.bm")
                             ->buildSql();

        //2. 获取设计师数量信息
        $buildSql = M("user")->table($buildSql)->alias("t5")
                             ->join("LEFT JOIN qz_team as team on team.comid = t5.uid and team.zt = 2")
                             ->field("t5.*,count(team.id) as dcount")
                             ->buildSql();

        //3.获取公司上传的资质证书
        $buildSql = M("user")->table($buildSql)->alias("t3")
                             ->join("LEFT JOIN qz_company_img as f on f.userid = t3.uid")
                             ->field("t3.*,count(f.id) as fcount")
                             ->buildSql();
        //4.获取公司的资讯数量
        $buildSql = M("user")->table($buildSql)->alias("t4")
                             ->join("LEFT JOIN qz_info as g on g.uid = t4.uid")
                             ->group("t4.uid")
                             ->field("t4.*,g.type,g.id as infoid,g.time,count(g.id) as infocount,count(IF(g.type=1,1,null)) as yhcount,max(IF(g.type=1,`time`,null)) as lastinfotime")
                             ->buildSql();

        // $buildSql = M("user")->table($buildSql)->alias("t6")
        //                     ->field("t6.*")
        //                     ->group("t6.uid")
        //                     ->buildSql();

        //5.获取案例信息
        $buildSql = M("user")->table($buildSql)->alias("t1")
                             ->join("LEFT JOIN qz_cases as c on c.uid = t1.uid")
                             ->field("t1.*,c.classid as caseclassid,c.time as casetime")
                             ->buildSql();
         $buildSql = M("user")->table($buildSql)->alias("t7")
                              ->group("t7.uid")
                              ->field("t7.*, max(casetime) as lasttime,count(IF(caseclassid = 1,1,null)) as jcount,count(IF(caseclassid = 2,1,null)) as gcount,count(IF(caseclassid = 3,1,null)) as zcount")
                              ->buildSql();
        //6.获取装修公司评论数
        $buildSql =  M("user")->table($buildSql)->alias("t2")
                             ->join("LEFT JOIN  qz_comment  as d on d.comid = t2.uid")
                             ->field("t2.*,d.id as commentid")
                             ->buildSql();
        return  M("user")->table($buildSql)->alias("t8")
                        ->field("t8.*,count(commentid) as ccount")
                        ->group("t8.uid")
                        ->find();
    }

    /**
     * 查询该城市的真实会员数量
     * @param  [type] $cs [description]
     * @return [type]     [description]
     */
    public function getRealComapnyCount($cs){
        $map = array(
                "a.cs"=>array("EQ",$cs),
                "a.on"=>array("EQ",2),
                "b.fake"=>array("EQ",0)
                     );
        return M("user")->where($map)->alias("a")
                        ->join("LEFT JOIN qz_user_company as b on b.userid = a.id")
                        ->count();
    }

    /**
     * 根据公司编号获取设计师名称列表
     * @param  [type] $comid [公司编号]
     * @param  [type] $cs    [所在城市]
     * @return [type]        [description]
     */
    public function getDesignerNameList($comid,$cs){
        $map = array(
                "a.cs"=>array("EQ",$cs),
                "a.id"=>array("EQ",$comid)
                     );
        //1.查询出公司的所有设计师编号
        $buildSql = M("User")->where($map)->alias("a")
                             ->join("inner join qz_team as b on a.id = b.comid and b.zt = 2")
                             ->field("b.userid,b.px")
                             ->buildSql();
        //2.查询出设计师的名称
        return M("User")->table($buildSql)->alias("t")
                        ->join("inner join qz_user as c on c.id = t.userid")
                        ->field("c.name,c.id")
                        ->order("t.px desc")
                        ->select();
    }

    /**
     * 获取没有装修公司设计师的数量
     * @return [type] [description]
     */
    public function getDesignerListNoCompanyCount($cs = "",$name =""){
        $map =array(
                "classid"=>array("EQ",2)
                    );
        if(!empty($cs)){
            $map["cs"] = array("EQ",$cs);
        }

        if(!empty($cs)){
            $map["name"] = array("LIKE","%$name%");
        }

        //1.查询出所有已有装修公司的设计师
        $buildSql = M("User")->alias("a")
                             ->join("INNER JOIN qz_team as b on a.id = b.userid and b.zt = 2")
                             ->field("a.id")
                             ->buildSql();
        //2.查询出没有装修公司的设计师
        $map["id"] = array("exp","not in (".$buildSql.")");
        return M("user")->where($map)->alias("t")->count();
    }

    /**
     * 获取没有装修公司设计师列表
     * @param  string $cs        [所在城市]
     * @param  string $name      [设计师姓名]
     * @param  [type] $pageIndex [description]
     * @param  [type] $pageCount [description]
     * @return [type]            [description]
     */
    public function getDesignerListNoCompany($cs = "",$name ="",$pageIndex = 0,$pageCount=10)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $map =array(
                "classid"=>array("EQ",2)
                    );
        if(!empty($cs)){
            $map["cs"] = array("EQ",$cs);
        }

        if(!empty($name)){
            $map["name"] = array("LIKE","%$name%");
        }

        //1.查询出所有已有装修公司的设计师
        $buildSql = M("User")->alias("a")
                             ->join("INNER JOIN qz_team as b on a.id = b.userid and b.zt = 2")
                             ->field("a.id")
                             ->buildSql();
        //2.查询出没有装修公司的设计师
        $map["id"] = array("exp","not in (".$buildSql.")");
        $buildSql = M("user")->where($map)->alias("t")
                             ->order("t.id desc")
                             ->limit($pageIndex.",".$pageCount)
                             ->field("t.*")
                             ->buildSql();
        return M("user")->table($buildSql)->alias("t1")
                        ->join("INNER JOIN qz_quyu as c on c.cid = t1.cs")
                        ->join("LEFT JOIN qz_team as team on team.userid = t1.id and team.zt = 0")
                        ->field("t1.*,c.cname,c.bm,GROUP_CONCAT(team.comid) as comgroup")
                        ->order("t1.id desc")
                        ->group("t1.id")
                        ->select();
    }

    /**
     * 获取用户的基本信息（后台管理用）
     * @param  [type] $id [用户编号]
     * @param  [type] $cs [所在城市]
     * @return [type]     [description]
     */
    public function getUserInfoByAdmin($id,$cs,$classid = 1){
        $map = array(
                "a.id"=>array("EQ",$id),
                "a.cs"=>array("EQ",$cs),
                "a.classid"=>array("EQ",$classid)
                     );
        //1.查询业主信息详细
        return M("user")->where($map)->alias("a")
                        ->join("INNER JOIN qz_quyu as b on b.cid = a.cs")
                        ->field("a.*,b.bm")
                        ->find();
    }

     /**
     * 获取用户的基本信息（后台管理用）
     * @param  [type] $id [用户编号]
     * @param  [type] $cs [所在城市]
     * @return [type]     [description]
     */
    public function getDesingerInfoByAdmin($id,$cs){
        $map = array(
                "a.id"=>array("EQ",$id),
                "a.cs"=>array("EQ",$cs),
                "a.classid"=>array("EQ",2)
                     );
        //1.查询业主信息详细
        return M("user")->where($map)->alias("a")
                        ->join("INNER JOIN qz_quyu as b on b.cid = a.cs")
                        ->join("LEFT JOIN qz_team as c on c.userid = a.id AND c.zw = 2")
                        ->join("LEFT JOIN qz_user as d on d.id = c.comid")
                        ->join("LEFT JOIN qz_quyu as e on e.cid = d.cs")
                        ->field("a.*,b.bm,d.id as comid,d.qc,e.bm as combm")
                        ->find();
    }

    /**
     * 获取公司阅读排行榜信息（真实会员）
     * @param  [type] $cs [所属城市]
     * @return [type]     [description]
     */
    public function getRanksList($cs = '',$limit = 5){
        //当月的第一天
        $start = mktime(0,0,0,date("m"),1,date("Y"));
        //当天的最后一分钟
        $end = mktime(23,59,59,date("m"),date("d"),date("Y"));

        $map['a.on'] = array("EQ",2);
        $map['b.fake'] = array("EQ",0);
        !empty($cs) && $map['a.cs'] = array('eq',$cs);

        //1.获取当前城市会员信息
        $buildSql = M("user")->where($map)->alias("a")
                 ->join("LEFT JOIN qz_user_company as b on b.userid = a.id")
                 ->join("LEFT JOIN qz_quyu as q on a.cs = q.cid " )
                 ->field("a.*,q.bm")
                 ->buildSql();

        //2.查询会员公司的案例数
        $buildSql = M("user")->table($buildSql)->alias("t")
                             ->join("LEFT JOIN qz_cases as c on c.uid = t.id and c.time >= $start and c.time <=$end  and c.cs = '$cs'")
                             ->group("t.id")
                             ->field("t.*,count(c.id) as casecount")
                             ->buildSql();
        //3.查询会员公司的发布文章数
        $buildSql = M("user")->table($buildSql)->alias("t1")
                             ->join("LEFT JOIN qz_info as c on c.uid = t1.id and c.time >=$start and c.time <=$end   and c.cs = '$cs'")
                             ->group("t1.id")
                             ->field("t1.*,count(c.id) as infocount")
                             ->buildSql();
/*        //4.查询会员的评论数量
        $buildSql = M("user")->table($buildSql)->alias("t3")
                             ->join("LEFT join qz_comment as d on d.comid = t3.id and d.time >=$start and d.time <= $end")
                             ->field("t3.*,count(d.id) as commentcount")
                             ->group("t3.id")
                             ->buildSql();*/

        return M("user")->table($buildSql)->alias("t2")
                        ->order("allcount desc")
                        ->field("t2.*,(infocount+casecount) as allcount")
                        ->limit($limit)
                        ->select();
    }
    /**
     * [check_user_compare_password ]   [比对新老密码是否一致]
     * @param  [type] $userid           [用户id]
     * @param  [type] $compare_password [要比对的新密码]
     * @return [type]                   [返回值]
     * false        =>'用户非法或者密码为空'
     * 'same'       =>'密码一致'
     * 'not_same'   =>'不一致'
     */
    public function check_user_compare_password($userid,$compare_password)
    {
        //根据id查询用户password然后比对要更改的密码是否一致 如果一致 说明是新密码和老密码一样 直接返回了
        $userid+=0;
        if ($userid<1||$compare_password=="")
        {
            return false;
        }
        $user_info=M('user')->field('pass')->find($userid);//获取用户password信息
        if ($user_info['pass']==$compare_password)
        {
            #新密码与老密码一致 建议不重复 返回
            return 'same';
        }else
        {
            return 'not_same';
        }
    }

    /**
     * 获取绑定帐号信息
     * @param  string $type    [绑定帐号类型]
     * @param  [type] $account [登录帐号]
     * @return [type]          [description]
     */
    public function getBangAccountInfo($type="",$account){
        //限制只有业主和设计师
        $map = array(
                "a.classid"=>array("IN",array(1,2,3))
                     );
        switch ($type) {
            case 'weibo':
                $map["a.weibo_account"] = array("EQ",$account);
                break;
            case "qq":
                 $map["a.qq_account"] = array("EQ",$account);
                break;
            case "weixin":
                 $map["a.wx_unionid"] = array("EQ",$account);
                break;
            default:
                return false;
                break;
        }

        return M("user")->where($map)->alias("a")
                        ->join("LEFT JOIN qz_quyu as b on b.cid = a.cs")
                        ->field("a.*,b.bm")
                        ->find();
    }

    /**
     * 获取装修公司的公司文化图片信息
     * @return [type] [description]
     */
    public function getCompanyImg($comid){
        $map = array(
                "userid"=>$comid
                    );
        return M("company_img")->where($map)
                               ->field("img,img_host")
                               ->select();

    }

    /**
     * 获取城市会员数量
     * @return [type] [description]
     */
    public function getRealMemberCount($cs,$ids = ''){
        $map = array(
                "a.cs"=>array("EQ",$cs),
                "a.classid"=>array("EQ",3),
                "a.on"=>array("EQ",2),
                "b.fake"=>array("EQ",0)
                     );
        //限定Uid
        if(!empty($ids)){
            $map['a.id'] = array('IN',$ids);
        }
        return M("user")->where($map)->alias("a")
                        ->join("INNER JOIN qz_user_company as b on b.userid = a.id")
                        ->count();
    }

    /**
     * 检查帐号是否被使用
     * @return [type] [description]
     */
    public function checkAccount($account){
        $map = array(
                "user"=>array("EQ",$account)
                     );
        return M("user")->where($map)->count();
    }

    /**
     * 获取当前城市用户信息列表
     * @param  [type] $cs [description]
     * @return [type]     [description]
     */
    public function getVipUserListByCity($cs){
        $map = array(
            "a.cs" => array("EQ",$cs),
            "a.on" => array("EQ",2)
                     );
        return M("user")->where($map)->alias("a")
                 ->join("INNER JOIN qz_user_company b on a.id = b.userid")
                 ->order("a.on desc,b.fake,a.check_score desc")
                 ->field("a.jc as company_name,a.id as company_id,a.kouhao as value,a.logo as img_url,check_score")
                 ->select();
    }

    /**
     * 获取用户的信息
     * @return [type] [description]
     */
    public function getVipUserInfoById($id){
        $map = array(
            "id" => array("EQ",$id)
                     );
        return  M("user")->where($map)->field("check_score")->find();
    }

    /**
     * 获取装修公司的设计水平
     * @return [type] [description]
     */
    public function getVipUserDesignScoreGrading($city_id,$limit){
        $map = array(
            "a.on" => array("EQ",2),
            "a.cs" => array("EQ",$city_id)
                     );
        $buildSql = M("user")->where($map)->alias("a")
                             ->join("inner join qz_user_company b on a.id = b.userid")
                             ->order("a.on,b.fake")
                             ->field("a.id,a.jc,b.fake,a.logo")
                             ->buildSql();
        //获取设计师数量
        $buildSql = M("user")->table($buildSql)->alias("t")
                             ->join("LEFT join qz_team as team on team.comid = t.id and zt = 2")
                             ->group("t.id")
                             ->field("t.*,count(team.id) as teamCount")
                             ->buildSql();
        //获取设计评分
        $buildSql = M("user")->table($buildSql)->alias("t1")
                             ->join("Left join (select * from qz_comment where cs = $city_id) as c on c.comid = t1.id and c.isveritfy = 0")
                             ->group("t1.id")
                             ->field("t1.*,sum(if(c.sj != 0, c.sj,null)) as sum,count(if(c.sj != 0,c.id,null)) as count")
                             ->buildSql();
        //计算平均值
        $buildSql = M("user")->table($buildSql)->alias("t2")
                             ->field("t2.id,t2.jc,t2.teamCount,sum,count,round((sum/count),2) as avg,t2.fake,t2.logo")
                             ->group("t2.id")
                             ->buildSql();

        return M("user")->table($buildSql)->alias("t3")
                        ->order("t3.fake,t3.avg desc,teamCount desc")
                        ->limit($limit)
                        ->select();

    }

     /**
     * 获取装修公司的施工水平评分信息
     * @return [type] [description]
     */
    public function getVipUserBuildScoreGrading($city_id,$limit){
        $end = strtotime(date("Y-m-d"))+86400-1;
        $start = strtotime("-30 day",$end);
        $map = array(
            "a.on" => array("EQ",2),
            "a.cs" => array("EQ",$city_id)
                     );
        $buildSql = M("user")->where($map)->alias("a")
                             ->join("inner join qz_user_company b on a.id = b.userid")
                             ->order("a.on,b.fake")
                             ->field("a.id,a.jc,b.fake,a.logo")
                             ->buildSql();
        //获取设计师数量
        $buildSql = M("user")->table($buildSql)->alias("t")
                             ->join("LEFT join qz_team as team on team.comid = t.id and zt = 2")
                             ->group("t.id")
                             ->field("t.*,count(team.id) as teamCount")
                             ->buildSql();

        //获取最近30天的案例数量
        $buildSql = M("user")->table($buildSql)->alias("t")
                             ->join("LEFT join qz_cases as `case` on case.uid = t.id and case.time >= '$start' and case.time <= '$end' and case.cs = $city_id")
                             ->group("t.id")
                             ->field("t.*,count(case.id) as casecount")
                             ->buildSql();

        //获取施工评分
        $buildSql = M("user")->table($buildSql)->alias("t1")
                             ->join("Left join (select * from qz_comment where cs = $city_id) as c on c.comid = t1.id and c.isveritfy = 0")
                             ->group("t1.id")
                             ->field("t1.*,sum(if(c.sg != 0, c.sg,null)) as sum,count(if(c.sg != 0,c.id,null)) as count")
                             ->buildSql();
        //计算平均值
        $buildSql = M("user")->table($buildSql)->alias("t2")
                             ->field("t2.id,t2.jc,t2.casecount,sum,count,round((sum/count),2) as avg,t2.fake,t2.logo,t2.teamCount")
                             ->group("t2.id")
                             ->buildSql();

        return M("user")->table($buildSql)->alias("t3")
                        ->order("t3.fake,t3.avg desc,casecount desc")
                        ->limit($limit)
                        ->select();
    }

    /**
     * 获取装修公司的服务态度评分信息
     * @return [type] [description]
     */
    public function getVipUserServingScoreGrading($city_id,$limit){
        $end = strtotime(date("Y-m-d"))+86400-1;
        $start = strtotime("-30 day",$end)+1;
        $map = array(
            "a.on" => array("EQ",2),
            "a.cs" => array("EQ",$city_id)
                     );
        $buildSql = M("user")->where($map)->alias("a")
                             ->join("inner join qz_user_company b on a.id = b.userid")
                             ->order("a.on,b.fake")
                             ->field("a.id,a.jc,b.fake,a.logo")
                             ->buildSql();
        //获取设计师数量
        $buildSql = M("user")->table($buildSql)->alias("t")
                             ->join("LEFT join qz_team as team on team.comid = t.id and zt = 2")
                             ->group("t.id")
                             ->field("t.*,count(team.id) as teamCount")
                             ->buildSql();

        //获取回复数量
        $buildSql = M("user")->table($buildSql)->alias("t")
                             ->join("Left join (select * from qz_comment where cs = $city_id) as c on c.comid = t.id and c.isveritfy = 0")
                             ->join("Left join qz_reply as r on r.commid = c.comid and r.time >= $start and r.time <= $end")
                             ->field("t.*,count(r.id) as replycount")
                             ->group("t.id")
                             ->buildSql();

        //获取施工评分
        $buildSql = M("user")->table($buildSql)->alias("t1")
                             ->join("LEFT join (select * from qz_comment where cs = $city_id) as c on c.comid = t1.id and c.isveritfy = 0")
                             ->group("t1.id")
                             ->field("t1.*,sum(if(c.fw != 0, c.fw,null)) as sum,count(if(c.fw != 0,c.id,null)) as count")
                             ->buildSql();


        //计算平均值
        $buildSql = M("user")->table($buildSql)->alias("t2")
                             ->field("t2.id,t2.jc,sum,count,round((sum/count),2) as avg,t2.fake,t2.logo,t2.teamCount,t2.replycount")
                             ->group("t2.id")
                             ->buildSql();

        return M("user")->table($buildSql)->alias("t3")
                        ->order("t3.fake,t3.avg desc,t3.replycount desc")
                        ->limit($limit)
                        ->select();
    }

    /**
     * 装修公司信赖榜列表
     * @param  [type] $city_id [description]
     * @param  [type] $limit   [description]
     * @return [type]          [description]
     */
    public function getVipUserTrustList($city_id,$limit){
        $end = strtotime(date("Y-m-d"))+86400-1;
        $start = strtotime("-30 day",$end)+1;
        $map = array(
            "a.on" => array("EQ",2),
            "a.cs" => array("EQ",$city_id)
                     );
        $buildSql = M("user")->where($map)->alias("a")
                             ->join("inner join qz_user_company b on a.id = b.userid")
                             ->order("a.on,b.fake")
                             ->field("a.id,a.jc,b.fake,a.logo")
                             ->buildSql();
        //获取设计师数量
        $buildSql = M("user")->table($buildSql)->alias("t")
                             ->join("LEFT join qz_team as team on team.comid = t.id and zt = 2")
                             ->group("t.id")
                             ->field("t.*,count(team.id) as teamCount")
                             ->buildSql();

        //获取评论
        $buildSql = M("user")->table($buildSql)->alias("t1")
                             ->join("LEFT join (select * from qz_comment where cs = $city_id) as c on c.comid = t1.id and c.isveritfy = 0")
                             ->group("t1.id")
                             ->field("t1.*,count(if(c.fw != 0,c.id,null)) as count,avg(if(c.fw != 0, ((c.fw+c.sg+c.sj)/3),null)) as avg")
                             ->buildSql();

        //获取签单数量
        $buildSql = M("user")->table($buildSql)->alias("t1")
                             ->join("LEFT JOIN qz_orders as o on o.qiandan_companyid = t1.id and o.qiandan_addtime >= $start and o.qiandan_addtime <= $end and o.cs = $city_id")
                             ->field("t1.*,count(o.id) as qdcount")
                             ->group("t1.id")
                             ->buildSql();

        //获取自主签单的数量
        $buildSql = M("user")->table($buildSql)->alias("t1")
                             ->join("LEFT JOIN qz_orders_company_report as r on r.order_company_id = t1.id and r.time_qd >= FROM_UNIXTIME($start) and r.time_qd <= FROM_UNIXTIME($end)")
                             ->field("t1.*,count(r.id) as qdcount_zz")
                             ->group("t1.id")
                             ->buildSql();
        return  M("user")->table($buildSql)->alias("t2")
                             ->field("t2.id,t2.jc,t2.fake,t2.logo,(qdcount+qdcount_zz) as allcount,t2.teamCount,t2.count,t2.avg")
                             ->order("t2.fake,allcount desc")
                             ->limit($limit)
                             ->select();
    }

    /**
     * 装修公司活跃榜列表
     * @param  [type] $city_id [description]
     * @param  [type] $limit   [description]
     * @return [type]          [description]
     */
    public function getVipUserActivityList($city_id,$limit){
        $end = strtotime(date("Y-m-d"))+86400-1;
        $start = strtotime("-30 day",$end)+1;

        $map = array(
            "a.on" => array("EQ",2),
            "a.cs" => array("EQ",$city_id)
                     );
        $buildSql = M("user")->where($map)->alias("a")
                             ->join("inner join qz_user_company b on a.id = b.userid")
                             ->order("a.on,b.fake")
                             ->field("a.id,a.jc,b.fake,a.logo")
                             ->buildSql();
        //获取设计师数量
        $buildSql = M("user")->table($buildSql)->alias("t")
                             ->join("LEFT join qz_team as team on team.comid = t.id and zt = 2")
                             ->group("t.id")
                             ->field("t.*,count(team.id) as teamCount")
                             ->buildSql();

        //获取评论
        $buildSql = M("user")->table($buildSql)->alias("t1")
                             ->join("LEFT join (select * from qz_comment where cs = $city_id) as c on c.comid = t1.id and c.isveritfy = 0")
                             ->group("t1.id")
                             ->field("t1.*,count(if(c.fw != 0,c.id,null)) as count,avg(if(c.fw != 0, ((c.fw+c.sg+c.sj)/3),null)) as avg,count(if(c.time >= $start,1,null)) as commentcount")
                             ->buildSql();

        //获取最近30天的案例数量
        $buildSql = M("user")->table($buildSql)->alias("t")
                             ->join("LEFT join qz_cases as `case` on case.uid = t.id and case.time >= '$start' and case.time <= '$end' and case.cs = $city_id")
                             ->group("t.id")
                             ->field("t.*,count(case.id) as casecount")
                             ->buildSql();

        //获取最近30发布的文章数量
        $buildSql = M("user")->table($buildSql)->alias("t")
                             ->join("LEFT JOIN  qz_info as info on info.uid = t.id and info.time >= $start and info.time <= $end and info.cs = $city_id")
                             ->field("t.*,count(info.id) as infocount")
                             ->group("t.id")
                             ->buildSql();
        return  M("user")->table($buildSql)->alias("t1")
                         ->field("t1.id,t1.jc,t1.fake,t1.logo,t1.count,t1.avg,(t1.casecount+t1.infocount+t1.commentcount) as allcount,t1.teamcount")
                         ->order("t1.fake,allcount desc")
                         ->limit($limit)
                         ->select();
    }

    /**
     * 装修公司人气榜列表
     * @return [type] [description]
     */
    public function getVipUserPopularityList($city_id,$limit){
        $end = strtotime(date("Y-m-d"))+86400-1;
        $start = strtotime("-30 day",$end)+1;

        $map = array(
            "a.on" => array("EQ",2),
            "a.cs" => array("EQ",$city_id)
                     );
        $buildSql = M("user")->where($map)->alias("a")
                             ->join("inner join qz_user_company b on a.id = b.userid")
                             ->order("a.on,b.fake")
                             ->field("a.id,a.jc,b.fake,a.logo,a.pv")
                             ->buildSql();
        //获取设计师数量
        $buildSql = M("user")->table($buildSql)->alias("t")
                             ->join("LEFT join qz_team as team on team.comid = t.id and zt = 2")
                             ->group("t.id")
                             ->field("t.*,count(team.id) as teamCount")
                             ->buildSql();

        //获取评论
        $buildSql = M("user")->table($buildSql)->alias("t1")
                             ->join("LEFT join (select * from qz_comment where cs = $city_id) as c on c.comid = t1.id and c.isveritfy = 0")
                             ->group("t1.id")
                             ->field("t1.*,count(if(c.fw != 0,c.id,null)) as count,avg(if(c.fw != 0, ((c.fw+c.sg+c.sj)/3),null)) as avg")
                             ->buildSql();
        return  M("user")->table($buildSql)->alias("t2")
                         ->order("t2.fake,t2.pv desc")
                         ->limit($limit)
                         ->select();
    }

    /**
     * 最热心装修公司
     * @param  [type] $city_id [description]
     * @param  [type] $limit   [description]
     * @return [type]          [description]
     */
    public function getVipUserEnthusiasticList($city_id,$limit){
        $end = strtotime(date("Y-m-d"))+86400-1;
        $start = strtotime("-30 day",$end)+1;

        $map = array(
            "a.on" => array("EQ",2),
            "a.cs" => array("EQ",$city_id)
                     );
        $buildSql = M("user")->where($map)->alias("a")
                             ->join("inner join qz_user_company b on a.id = b.userid")
                             ->order("a.on,b.fake")
                             ->field("a.id,a.jc,b.fake,a.logo")
                             ->buildSql();
        //获取最近30天回答的问题
        $buildSql = M("user")->table($buildSql)->alias("t")
                             ->join("left join qz_ask_anwser as answer on answer.uid = t.id and post_time >= $start and post_time <= $end")
                             ->field("t.*,answer.qid")
                             ->group("t.id,answer.qid")
                             ->buildSql();
        $buildSql = M("user")->table($buildSql)->alias("t2")
                             ->join("left join qz_ask as ask on ask.id = t2.qid")
                             ->field("t2.*,ask.views,count(t2.qid) as answercount")
                             ->group("t2.id,t2.qid")
                             ->buildSql();

        return M("user")->table($buildSql)->alias("t3")
                             ->field("t3.id,t3.jc,t3.fake,t3.logo,count(answercount) as count,sum(views) as views")
                             ->group("t3.id")
                             ->order("t3.fake,count desc,views desc")
                             ->limit($limit)
                             ->select();
    }

    /**
     * 最新签单公司
     * @param  [type] $city_id [description]
     * @param  [type] $limit   [description]
     * @return [type]          [description]
     */
    public function getVipUserLatestList($city_id,$limit){
        $map = array(
            "a.on" => array("EQ",2),
            "a.cs" => array("EQ",$city_id)
                     );
        $baseSql = M("user")->where($map)->alias("a")
                             ->join("inner join qz_user_company b on a.id = b.userid")
                             ->order("a.on,b.fake")
                             ->field("a.id,a.jc,b.fake,a.logo,check_score")
                             ->buildSql();

        //获取最新的咨询签单的最新签单时间
        $buildSql = M("user")->table($baseSql)->alias("t")
                             ->join("left join qz_orders o on o.qiandan_companyid = t.id")
                             ->order("o.qiandan_addtime desc,t.id")
                             ->field("t.*,o.qiandan_addtime")
                             ->buildSql();
        $zxSql = M("user")->table($buildSql)->alias("t1")
                             ->group("t1.id")
                             ->order("t1.qiandan_addtime desc,t1.id")
                             ->buildSql();

        //获取自主签单的最新签单时间
        $buildSql = M("user")->table($baseSql)->alias("t")
                             ->join("left join qz_orders_company_report as o on o.order_company_id = t.id")
                             ->field("t.*,UNIX_TIMESTAMP(time_qd) as qiandan_addtime")
                             ->order("qiandan_addtime desc,t.id")
                             ->buildSql();
        $buildSql = M("user")->table($buildSql)->alias("t1")
                             ->order("t1.qiandan_addtime desc,t1.id")
                             ->group("t1.id")
                             ->buildSql();

        $buildSql = M("user")->table($zxSql)->alias("t1")
                             ->union($buildSql,true)
                             ->buildSql();

        $buildSql = M("user")->table($buildSql)->alias("t2")
                             ->order("qiandan_addtime desc")
                             ->buildSql();
        $buildSql = M("user")->table($buildSql)->alias("t3")
                             ->group("t3.id")
                             ->order("qiandan_addtime desc")
                             ->limit($limit)
                             ->buildSql();
        //获取评论
        return M("user")->table($buildSql)->alias("t1")
                             ->join("LEFT join (select * from qz_comment where cs = $city_id) as c on c.comid = t1.id and c.isveritfy = 0")
                             ->group("t1.id")
                             ->field("t1.*,count(if(c.fw >= 9 and c.sj >=9 and c.sg >= 9,c.id,null)) as count")
                             ->select();

    }


   /**
    * 查询该城市的会员
    * @param  [type]  $cs      [城市]
    * @param  [type]  $orderby [排序]
    * @param  integer $limit   [description]
    * @param  boolean $fake    [真假会员]
    * @param  [type]  $ids     [用户编号]
    * @return [type]           [description]
    */
    public function getComapnys($cs,$orderby,$limit = 10,$fake = false,$ids){
        $map = array(
            "a.on"=>array("EQ",2),
        );
        if(!empty($cs)){
            $map['a.cs'] = array("EQ",$cs);
        }
        if($fake == true){
            $map['b.fake'] = array("EQ",'0');
        }

        if (count($ids) > 0) {
            $map["a.id"] = array("NOT IN",$ids);
        }
        return M("user")->where($map)->alias("a")
                        ->field('a.id,a.on,a.qc,a.jc,a.logo,a.case_count,b.team_count,b.comment_count,b.fake,q.bm')
                        ->join("LEFT JOIN qz_user_company as b on b.userid = a.id")
                        ->join("left join qz_quyu as q on q.cid = a.cs")
                        ->order($orderby)
                        ->limit($limit)
                        ->select();
    }

    /**
     * [getCompanyInfoForIndexBrand 获取补充首页品牌榜数据信息]
     * @param  integer $city_id    [城市ID]
     * @param  array   $remove_ids [需要排除的装修公司的ID]
     * @return [array]             [用户信息]
     */
    public function getCompanyInfoForIndexBrand($city_id = 0, $remove_ids = array())
    {
        if (!empty($city_id)) {
            $map['u.cs'] = $city_id;
        }

        if (!empty($remove_ids)) {
            if (!is_array($remove_ids)) {
                $remove_ids = array(intval($remove_ids));
            }
            $map['u.id'] = array('NOT IN', $remove_ids);
        }

        //限制为假会员
        $map['c.fake'] = 1;

        return M('user')->alias('u')
                        ->field('u.id, q.bm, u.logo, u.jc')
                        ->join('qz_user_company AS c ON c.userid = u.id')
                        ->join('qz_quyu AS q ON q.cid = u.cs')
                        ->where($map)
                        ->order('u.id ASC')
                        ->find();
    }

    /**
     * [getCompanyInfoForIndexBrand 获取补充首页品牌榜数据信息 的个数]
     * @param  integer $city_id    [城市ID]
     * @param  array   $remove_ids [需要排除的装修公司的ID]
     * @param  integer   $limit [需要的个数]
     * @return [array]             [用户信息]
     */
    public function getCompanyInfoForIndexBrandNum($city_id = 0, $remove_ids = array(),$limit = 1)
    {
        if (!empty($city_id)) {
            $map['u.cs'] = $city_id;
        }

        if (!empty($remove_ids)) {
            if (!is_array($remove_ids)) {
                $remove_ids = array(intval($remove_ids));
            }
            $map['u.id'] = array('NOT IN', $remove_ids);
        }

        //限制为假会员
        $map['c.fake'] = 1;
        $map['u.logo'] = ['NEQ',''];

        return M('user')->alias('u')
            ->field('u.id as company_id, q.bm, u.logo, u.jc,u.case_count,c.comment_count')
            ->join('qz_user_company AS c ON c.userid = u.id')
            ->join('qz_quyu AS q ON q.cid = u.cs')
            ->where($map)
            ->order('u.id desc')
            ->limit($limit)
            ->select();
    }

    /**
     * 查询所有会员
     * @param  [type] $cs [description]
     * @return [type]     [description]
     */
    public function getAllComapnys($orderby,$limit = 10,$fake = false){
        $map = array(
            "a.on"=>array("EQ",2),
        );
        if($fake == true){
            $map['b.fake'] = array("EQ",'0');
        }
        return M("user")->where($map)->alias("a")
                        ->field('a.id,a.on,a.qc,a.jc,a.logo,a.case_count,b.team_count,b.comment_count,b.fake,q.bm')
                        ->join("LEFT JOIN qz_user_company as b on b.userid = a.id")
                        ->join("LEFT JOIN qz_quyu as q on a.cs = q.cid")
                        ->order($orderby)
                        ->limit($limit)
                        ->select();
    }


    /**
     * 获取当前城市的设计师信息
     *
     * @param  string $cs [description]
     * @return [type]     [description]
     */
    public function getDesingerListByCity($condition,$page = 1,$pageCount = 10,$order = 'orders desc,id desc' )
    {
        $map['a.`on`'] = ["NOT IN",'1,4'];
        if (!empty($condition['cs'])) {
            $map["a.cs"] = array("EQ", $condition['cs']);
        }
        if (isset($condition['classid'])) {
            $map["a.classid"] = array("EQ", $condition['classid']);
        } else {
            $map["a.classid"] = array("EQ", 2);
        }
        if (isset($condition['orderby'])) {
            $order = $condition['orderby'];
        }

        $buildSql1 = M("user")->where($map)->alias("a")
            ->join("LEFT JOIN qz_team as b ON a.id = b.userid AND b.zt = 2")
            ->join("LEFT JOIN qz_user_des as c ON c.userid = a.id")
            ->join("LEFT JOIN qz_user as d ON d.id = b.comid")
            ->join("LEFT JOIN qz_quyu as e ON e.cid = a.cs")
            ->join("LEFT JOIN qz_user_company_employee ce on ce.company_id = b.comid")
            ->where("ce.id is null")
            ->field("a.id as uid,a.name,a.cs,a.pv,a.logo,c.*,d.qc,d.id as comid,e.bm,b.zw,1 as company_type")
            ->order($order)
            ->buildSql();


        $buildSql1 = M()->table($buildSql1)->alias('a1')
            ->field('a1.*')
            ->join("LEFT JOIN qz_user_company_employee ce on ce.company_id = a1.comid")
            ->where("ce.id is null")
            ->limit(3 * $pageCount)
            ->buildSql();


        //城市
        $map2 = [];
        if (!empty($condition['cs'])) {
            $map2["d.cs"] = array("EQ", $condition['cs']);
        }

        $buildSql2 = M("user_company_employee")->alias("ce")
            ->where($map2)
            ->join("LEFT JOIN qz_user_des as c ON c.userid = ce.id and c.company_id = ce.company_id")
            ->join("LEFT JOIN qz_user as d ON d.id = ce.company_id")
            ->join("LEFT JOIN qz_quyu as e ON e.cid = d.cs")
            ->field("ce.id as uid,ce.nick_name as 'name',d.cs,c.popularity as pv,ce.logo,c.*,d.qc,d.id as comid,e.bm,case when ce.position = 2 then '设计师' when ce.position = 3 then '首席设计师' when ce.position = 4 then '总设计总监' else '' end as zw,2 as company_type")
            ->limit(3 * $pageCount)
            ->order("convert(c.popularity , UNSIGNED) desc,ce.id desc")
            ->buildSql();

        $buildSql = M()->table($buildSql1)->alias('t')
            ->union($buildSql2)
            ->buildSql();

        $order = 'convert(t2.pv , UNSIGNED) desc,t2.id desc';

        $list = M()->table($buildSql)->alias('t2')
            ->field('t2.*')
            ->order($order)
            ->page("$page,$pageCount")
            ->select();

        return $list ? $list : [];

    }

    public function getUserinfoListByIds($ids)
    {
        if(empty($ids)){
            return array();
        }
        $map = [];
        $map['id'] = array('in',$ids);
        return M('user')->where($map)->field("id,`name`,logo")->select();
    }

    public function getPackageInfo($company_id)
    {
        $map = array(
            "company_id" => array("IN",$company_id),
            "back_status" => array("NEQ",3)
        );
        $map["_complex"] = array(
            "use_status" => array("IN",array(1,2)),
            array(
                "use_status" => array("EQ",3),
                "back_status" => array("NEQ",3)
            ),
            "_logic" => "OR"
        );
        return M("company_package")->where($map)->field("company_id,sum(deposit_money) as deposit_money")
                                   ->group("company_id")->select();
    }
}