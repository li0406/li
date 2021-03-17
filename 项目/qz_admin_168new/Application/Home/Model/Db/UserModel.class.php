<?php

namespace Home\Model\Db;

use Think\Model;

class UserModel extends Model
{
    protected $autoCheckFields = false;
    protected $table = 'qz_user';

    public function getUserCompanyInfo($id){
        $map = array(
            "u.id" => array("EQ", $id)
        );

        return $this->alias("u")->where($map)
            ->join("inner join qz_user_company as c on c.userid = u.id")
            ->find();
    }

    public function getUserInfoById($field = '*', $id)
    {
        $where['id'] = $id;
        return $this->where($where)->field($field)->find();
    }

    public function getUserList($map, $field = 'a.*')
    {
        return $this->where($map)->alias('a')
            ->field($field)
            ->join('join qz_user_company c on c.userid = a.id')
            ->select();
    }

    public function getUserCompanyInfoById($where, $field = '*')
    {
        return M('user_company')->where($where)->field($field)->find();
    }

    public function saveUserInfo($where, $save)
    {
        return $this->where($where)->save($save);
    }

    public function saveUserCompanyInfo($where, $save)
    {
        return M('user_company')->where($where)->save($save);
    }

    public function getQianDanUserList($city_id, $user_type)
    {
        $map = array(
            "u.classid" => array("EQ", 6),
            "u.on" => array("EQ", $user_type),
            "b.order_stop_status" => array(
                array("EQ", 1),
                array("exp", "is null"),
                "OR"
            ),
            "p.use_status" => array("EQ",2)
        );

        if (is_array($city_id)) {
            $map["u.cs"] = array("IN", $city_id);
        } else {
            $map["u.cs"] = array("EQ", $city_id);
        }

        $buildSql = $this->where($map)->alias("u")
            ->join("join qz_company_package p on p.company_id = u.id")
            ->join('left join qz_user_company c on u.id = c.userid')
            ->join('left join qz_user_company_signback b on u.id = b.user_id')
            ->field("u.id,u.classid,u.jc,u.cs,u.qx,c.lng,c.lat, if(b.order_stop_status is null,1,b.order_stop_status) as order_stop_status,c.lx,c.lxs,c.mianji,c.contract,c.fen_mianji,c.other_id")->buildSql();

        return $this->table($buildSql)->alias("t")
             ->join("left join qz_order_wechat w on w.comid = t.id and w.employee_id = 0")
             ->join("join qz_quyu q on q.cid = t.cs")
             ->join("join qz_area a on a.qz_areaid = t.qx")
             ->field("t.*,q.cname,a.qz_area,count(if(w.id is not null,1,null)) as wxcount")
             ->group("t.id")
             ->select();
    }

    /**
     * @param $company_id [装修公司ID]
     * @param $type [订单包类型]
     * @return mixed
     */
    public function getUserOrderPackageInfo($company_id, $type)
    {
        $map["a.company_id"] = array("IN", $company_id);

        $buildSql = M("company_package")->where($map)->alias("a")
            ->field("a.id,a.company_id,a.use_status as package_status")
            ->buildSql();
        $map = array(
            "b.package_type" => array("EQ", $type)
        );
        $buildSql = M("company_package")->table($buildSql)->where($map)->alias("a")
            ->join("LEFT JOIN qz_company_package_info b ON b.package_id = a.id ")
            ->field("a.id,a.company_id, b.send_number,b.use_status,a.package_status,b.total_number,b.id as package_info_id,case 
                                        when a.package_status = 3 then 0
                                        when a.package_status = 2 and b.use_status = 3 then 0
                                        else 1
                                end 
                                as 'package_order'")
            ->order("package_order desc,field(a.package_status,2,1,3),a.id,b.use_status,b.id desc")
            ->buildSql();
        return M("company_package")->table($buildSql)->alias("t")
            ->group("t.company_id")->select();

    }

    public function getNowPackageInfo($company_id, $package_id, $package_type)
    {
        $map = array(
            "a.company_id" => array("EQ", $company_id),
            "a.id" => array("EQ", $package_id)
        );
        $buildSql = M("company_package")->where($map)->alias("a")->field("a.id as package_id")->limit(1)
            ->buildSql();
        $map = array(
            "b.package_type" => array("EQ", $package_type)
        );
        return M("company_package")->table($buildSql)->alias("a")->where($map)
            ->join("left JOIN qz_company_package_info b ON b.package_id = a.package_id ")
            ->field("a.package_id,b.id as package_info_id,b.use_status as package_info_status")->find();
    }


    public function getNextPackageInfo($company_id, $package_id, $package_type)
    {
        $map = array(
            "a.company_id" => array("EQ", $company_id),
            "a.id" => array("GT", $package_id)
        );
        $buildSql = M("company_package")->where($map)->alias("a")->field("a.id as package_id")->limit(1)
            ->buildSql();
        $map = array(
            "b.package_type" => array("EQ", $package_type)
        );
        return M("company_package")->table($buildSql)->alias("a")->where($map)
            ->join("left JOIN qz_company_package_info b ON b.package_id = a.package_id ")
            ->field("a.package_id,b.id as package_info_id")->find();
    }

    public function editPackage($id, $data)
    {
        $map = array(
            "id" => array("EQ", $id)
        );
        return M("company_package")->where($map)->save($data);
    }

    public function editPackageInfo($id, $data)
    {
        $map = array(
            "id" => array("EQ", $id)
        );
        return M("company_package_info")->where($map)->save($data);
    }

    public function findCompanyInfo($id)
    {
        $map = array(
            "a.id" => array("EQ", $id),
            "a.classid" => array("IN", [3, 6])
        );

        return M("user")->where($map)->alias("a")
            ->join("join qz_user_company c on c.userid = a.id")
            ->join("join qz_quyu q on q.cid = a.cs")
            ->field("a.id,a.on,a.jc,q.cname,c.jd_tel_1,c.jd_tel_2,jd_tel_name_1,jd_tel_name_2,c.saler,c.fake,a.start,a.end,c.viptype,a.case_count,a.uptime,a.pj,a.uv,a.register_admin_id,a.user_type,a.ip,a.register_time,a.cs,a.qx,a.pass,a.user,a.classid,a.on")
            ->find();
    }

    // 签单返点会员查询条件
    public function getSignBackUserMap($input)
    {
        $map = array(
            "u.classid" => array("EQ", 6),
            "c.fake" => array("EQ", 0)
        );

        // 会员状态
        if (!empty($input["on_status"])) {
            $map["u.on"] = array("EQ", $input["on_status"]);
        } else {
            $map["u.on"] = array("IN", [0, 2, -1, -4]);
        }

        // 会员ID
        if (!empty($input["uid"])) {
            $map["u.id"] = array("EQ", $input["uid"]);
        }

        // 所属城市
        if (!empty($input["city"])) {
            $map["u.cs"] = array("EQ", $input["city"]);
        }

        // 会员名称
        if (!empty($input["jc"])) {
            $map["u.jc"] = array("LIKE", "%" . $input["jc"] . "%");
        }

        // 所属销售
        if (!empty($input["saler"])) {
            $map["c.saler"] = array("EQ", $input["saler"]);
        }

        // 本次合同时间-开始时间
        if (!empty($input["begin"])) {
            // $map["u.end"] = array("EGT", $input["begin"]);
            $map["u.start"] = array("EGT", $input["begin"]);
        }

        // 本次合同时间-结束时间
        if (!empty($input["end"])) {
            // $map["u.start"] = array("ELT", $input["end"]);
            $map["u.end"] = array("ELT", $input["end"]);
        }

        return $map;
    }

    // 签单返点会员数量查询
    public function getSignBackUserCount($input)
    {
        $map = $this->getSignBackUserMap($input);

        return $this->alias("u")->where($map)
            ->join("inner join qz_user_company as c on u.id = c.userid")
            ->count("u.id");
    }

    // 签单返点会员列表查询
    public function getSignBackUserList($input, $offset = 0, $limit = 0)
    {
        $map = $this->getSignBackUserMap($input);

        $subSql = $this->alias("u")->where($map)
            ->join("inner join qz_user_company as c on u.id = c.userid")
            ->field([
                "u.id", "u.qc", "u.jc", "u.`on`", "u.cs", "c.fake", "c.saler",
                "u.`start`", "u.`end`", "c.contract_start", "c.contract_end",
                "IF(u.`on` = -4, 1, u.`on`) as on_sort"
            ])
            ->order("on_sort desc,u.`end`,u.id desc")
            ->limit($offset, $limit)
            ->buildSql();

        return M()->table($subSql)->alias("t")
            ->join("left join qz_quyu as q on q.cid = t.cs")
            ->join("left join qz_user_company_signback as s on t.id = s.user_id")
            ->field("t.*,q.cname as city_name,q.bm,IF(s.order_stop_status is null, 1, s.order_stop_status) as order_stop_status")
            ->order("t.on_sort desc,t.`end` asc,t.id desc")
            ->select();
    }

    public function getUserSignbackInfo($user_id)
    {
        $map = array(
            "user_id" => array("EQ", $user_id)
        );

        return M("user_company_signback")->where($map)->find();
    }

    public function addUserSignbackInfo($data)
    {
        return M("user_company_signback")->add($data);
    }

    public function editUserSignbackInfo($user_id, $data)
    {
        $map = array(
            "user_id" => array("EQ", $user_id)
        );

        return M("user_company_signback")->where($map)->save($data);
    }


    /**
     * 根据条件获取满足条件的公司总数量
     * @param $map
     * @return mixed
     */
    public function companyRegisterListCount($map)
    {
        return $this->alias('u')
            ->where($map)
            ->join('inner join qz_user_company as c on c.userid = u.id')
            ->count();

    }


    /**
     * 公司注册页列表查询
     * @param $map
     * @param $pageIndex
     * @param $pageCount
     * @return mixed
     */
    public function companyRegisterList($map, $pageIndex, $pageCount)
    {
        $buildsql = $this->alias('u')
            ->field('u.id as company_id,u.logo,u.jc,u.on,u.cs,u.qx,u.ip,u.name,u.tel,u.register_time,u.start contract_start,u.end contract_end,c.fake')
            ->where($map)
            ->join('inner join qz_user_company as c on c.userid = u.id')
            ->order('u.register_time desc,u.id desc')
            ->limit($pageIndex, $pageCount)
            ->buildSql();

        $buildsql2 = $this->table($buildsql)->alias('t')
            ->field('t.*,q.cname city_name,a.qz_area area_name')
            ->join('left join qz_quyu as q on q.cid = t.cs')
            ->join('left join qz_area as a on a.qz_areaid = t.qx')
            ->order('t.register_time desc,t.company_id desc')
            ->buildSql();

        return $this->table($buildsql2)->alias('t2')
            ->field('t2.*, count(u2.ip) as count_ip')
            ->join('left join qz_user as u2 on u2.ip = t2.ip and u2.classid = 3')
            ->group('t2.company_id')
            ->order('t2.register_time desc,t2.company_id desc')
            ->select();

    }


    public function getCompanyDetailById($id)
    {
        $map = [];
        $map['u.id'] = array('eq', $id);
        return $this->alias("u")
            ->field('u.id,u.jc,u.qc,u.dz,u.logo,u.register_time,u.ip,u.cs,u.qx,u.`name` as contacts_name,u.tel,u.cals,u.qq,u.qq1,q.cname city_name,a.qz_area area_name')
            ->where($map)
            ->join('left join qz_quyu as q on q.cid = u.cs')
            ->join('left join qz_area as a on a.qz_areaid = u.qx')
            ->select();

    }


    public function getSignBackNewInfo($id){
        $map = array(
            "u.id" => array("EQ", $id)
        );

        return $this->alias("u")->where($map)
            ->join("inner join qz_user_company as c on u.id = c.userid")
            ->join("left join qz_user_company_signback as s on u.id = s.user_id")
            ->join("left join qz_user_company_account as a on a.user_id = u.id")
            ->join("left join qz_quyu as q on q.cid = u.cs")
            ->field([
                "u.id", "u.qc", "u.jc", "u.`on`", "u.cs", "c.saler",
                "u.`start`", "u.`end`", "c.contract_start", "c.contract_end",
                "s.order_round_amount", "s.back_ratio",
                "a.account_amount", "a.deposit_money", "a.last_recharge_time",
                "q.cname as city_name"
            ])
            ->find();
    }


    // 新签返会员查询条件
    public function getSignBackNewMap($input) {
        $map = array(
            "c.cooperate_mode" => array("IN", [2,4]),
            "u.classid" => array("EQ", 3),
            "c.fake" => array("EQ", 0)
        );

        // 会员ID
        if (!empty($input["user_id"])) {
            $map["u.id"] = array("LIKE", "%" . $input["user_id"] . "%");
        }

        // 所属城市
        if (!empty($input["city"])) {
            $map["u.cs"] = array("EQ", $input["city"]);
        }

        // 会员名称
        if (!empty($input["jc"])) {
            $map["u.jc"] = array("LIKE", "%" . $input["jc"] . "%");
        }

        // 所属销售
        if (!empty($input["saler"])) {
            $map["c.saler"] = array("LIKE", "%" . $input["saler"] . "%");
        }

        // 会员状态查询
        if (!empty($input["on_status"])) {
            $map["u.on"] = array("EQ", $input["on_status"]);
        }

        // 本次合同时间区间查询
        if (!empty($input["begin"]) && !empty($input["end"])) {
            $map["u.start"] = array("between", [$input["begin"], $input["end"]]);
        } else if (!empty($input["begin"])) {
            $map["u.start"] = array("EGT", $input["begin"]);
        } else if (!empty($input["end"])) {
            $map["u.start"] = array("ELT", $input["end"]);
        }

        // 余额区间查询
        if (!empty($input["amount_max"]) && !empty($input["amount_min"])) {
            $map["a.account_amount"] = array("between", [$input["amount_min"], $input["amount_max"]]);
        } else if (!empty($input["amount_max"])) {
            $map["a.account_amount"] = array("ELT", $input["amount_max"]);
        } else if (!empty($input["amount_min"])) {
            $map["a.account_amount"] = array("EGT", $input["amount_min"]);
        }

        if (!empty($input["mode"])) {
            $map["c.cooperate_mode"] = array("EQ", $input["mode"]);
        }

        return $map;
    }

    // 新签返会员数量查询
    public function getSignBackNewCount($input) {
        $map = $this->getSignBackNewMap($input);

        return $this->alias("u")->where($map)
            ->join("inner join qz_user_company as c on c.userid = u.id")
            ->join("left join qz_user_company_account as a on a.user_id = u.id")
            ->count("u.id");
    }

    // 新签返会员列表查询
    public function getSignBackNewList($input, $sort, $offset = 0, $limit = 0) {
        $map = $this->getSignBackNewMap($input);

        if (empty($sort)) {
            $sort = "a.account_amount desc,u.id desc";
        } else {
            $sort .= ",a.account_amount desc,u.id desc";
        }

        return $this->alias("u")->where($map)
            ->join("inner join qz_user_company as c on c.userid = u.id")
            ->join("left join qz_user_company_account as a on a.user_id = u.id")
            ->join("left join qz_user_company_signback as s on u.id = s.user_id")
            ->join("left join qz_quyu as q on q.cid = u.cs")
            ->field([
                "u.id", "u.qc", "u.jc", "u.`on`", "u.cs", "c.saler",
                "u.`start`", "u.`end`", "c.contract_start", "c.contract_end","c.cooperate_mode",
                "a.account_amount", "a.deposit_money", "a.round_order_number", "a.last_recharge_time",
                "s.order_round_amount", "s.back_ratio","IF(s.order_stop_status is null, 1, s.order_stop_status) as order_stop_status",
                "q.cname as city_name", "q.bm"
            ])
            ->limit($offset, $limit)
            ->order($sort)
            ->select();
    }

    public function getAccountStat($params)
    {
        $map = $this->getSignBackNewMap($params);

        $subSql = $this->alias("u")->where($map)
            ->join("inner join qz_user_company as c on c.userid = u.id")
            ->join("left join qz_user_company_account as a on a.user_id = u.id")
            ->join("left join qz_user_company_signback as s on u.id = s.user_id")
            ->field([
                "u.id","a.account_amount","a.round_order_number","a.deposit_money"
            ])
            ->buildSql();

        return $this->table($subSql)->alias("t")
            ->join("left join qz_user_company_account_log c on c.user_id = t.id")
            ->field([
                "t.*",
                "sum(if(trade_type = 1, trade_amount, 0)) as trade_recharge_amount",
                "sum(if(trade_type = 2, trade_amount, 0)) as trade_deduction_amount"
            ])
            ->group("t.id")
            ->select();
    }


    public function getUserAccountList($new_qian)
    {
        $map = array(
            "a.user_id" => array("IN",$new_qian)
        );
        return M("user_company_account")->where($map)->alias("a")
            ->join("join qz_user u on u.id = a.user_id")
            ->join("left join qz_user_company_signback b on a.user_id = b.user_id ")
            ->field("a.user_id,a.account_amount,a.round_order_number,b.order_round_amount,u.jc,b.back_ratio")->select();
    }

    /**
     * 获取现有真会员
     * @return mixed
     */
    public function getNowCompanyList($fake = 0,$company_id = [])
    {
        switch ($fake){
            case 0:
                //真会员
                $where2 = [
                    'u.on' => ['eq', 2],
                    'c.fake' => ['eq', 0],
                ];
                break;
            case 1:
                //非真会员(假会员,过期...)
                $where2 = [
                    'u.on' => ['neq', 2],
                    '_complex'  => [
                        "u.on" => 2,
                        "c.fake" => 1,
                    ],
                    '_logic' => 'or',
                ];

                break;
        }
        $where['_complex'] = $where2;
        $where['u.classid'] = ['in', [3, 6]];
        if (!empty($company_id)) {
            $where['u.id'] = ['in', $company_id];
        }
        return $this->alias('u')
            ->field('u.id,u.qc,u.logo,u.cs,c.lat,c.lng,c.team_count designer_num,c.team_num site_num,u.dz,u.on,c.text jianjie')
            ->join('qz_user_company c on c.userid = u.id')
            ->where($where)
            ->select();
    }

    /**
     * 获取现有真会员
     * @return mixed
     */
    public function getFakeCompanyList($company_id = [])
    {

        $where['u.classid'] = ['in', [3, 6]];
        if (!empty($company_id)) {
            $where['u.id'] = ['in', $company_id];
        }
        return $this->alias('u')
            ->field('u.id,u.qc,u.logo,u.cs,c.lat,c.lng,c.team_count designer_num,c.team_num site_num,u.dz,u.on,c.text jianjie,c.fake')
            ->join('qz_user_company c on c.userid = u.id')
            ->where($where)
            ->select();
    }

    public function getCompanyTags($city_id)
    {
        $map = [
            "a.id" => ["IN",$city_id]
        ];
        return $this->where($map)->alias("a")
                    ->join("left join qz_company_tags b on b.company_id = a.id")
                    ->join("left join qz_company_relation_tag c on c.id = b.tag")
                    ->field("a.id,GROUP_CONCAT(c.tag) as tags")
                    ->group("a.id")->select();

    }
}