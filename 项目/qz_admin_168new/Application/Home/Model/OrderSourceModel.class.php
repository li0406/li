<?php


namespace Home\Model;
Use Think\Model;

/**
*
*/
class OrderSourceModel extends Model
{
    protected $autoCheckFields = false;

    public function addSource($data)
    {
        return M("order_source")->add($data);
    }

    public function addAllSource($data)
    {
        return M("order_source")->addAll($data);
    }

    public function editSource($id,$data)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("order_source")->where($map)->save($data);
    }

    public function getAllSource($type = 1)
    {
        $map = array(
            "type" => array("EQ",$type),
            "alias" => array("EQ","")
        );
        return M("order_source")->where($map)->field("id,name")->select();
    }

    /**
     * 获取渠道来源组信息
     * @param int $type [渠道标识]
     * @param int $category [渠道分类1.装修2.家具]
     * @param bool $is_all [是否查询所有渠道组,不区分渠道分类]
     * @return mixed
     */
    public function getAllGroup($type = 1,$category = 1,$is_all = false,$parentid = "")
    {
        $map = array(
            "type" => array("EQ",$type)
        );

        if($is_all == false){
            $map['category'] = ['eq',$category];
        }

        if ($parentid !== "") {
            $map['parentid'] = ['eq',$parentid];
        }

        return M("order_source_group")->where($map)->order("parentid")->field("id,name,parentid,category")->select();
    }

    public function addGroup($data)
    {
        return M("order_source_group")->add($data);
    }

    public function editGroup($id,$data)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("order_source_group")->where($map)->save($data);
    }

    public function findGroupInfo($id)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("order_source_group")->where($map)->find();
    }

    public function findSourceByGroupIdCount($id)
    {
        $map = array(
            "a.id" => array("EQ",$id),
            "b.visible" => array("EQ",0)
        );

        $buildSql = M("order_source_group")->where($map)->alias("a")
                                      ->join("join qz_order_source b on a.id = b.groupid")
                                      ->field("a.id")
                                      ->buildSql();
        return  M("order_source_group")->table($buildSql)->alias("t")->count();
    }

    public function delsourcegroup($id)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("order_source_group")->where($map)->delete();
    }

    public function delOrderSource($id)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("order_source")->where($map)->delete();
    }

    //取单个
    public function getById($id){
        $map = array(
            'a.id' => array("EQ",$id)
        );
        return M('order_source')->where($map)->alias("a")
                                ->join("join qz_order_source_group b on b.id = a.groupid")
                                ->join("left join qz_order_source_group c on c.id = b.parentid")
                                ->join("LEFT JOIN qz_order_source_in_relation AS r ON r.src = a.src")
                                ->field('a.*,b.name as group_name,c.id as parentid,c.name as parent_name,b.category,r.source_in')
                                ->find();
    }

    //通过标识来获取数据
    public function getBySrc($src){
        $map = array(
            'alias' => array("EQ",$src)
        );
        return M('order_source')->field('*')->where($map)->find();
    }

    //通过标识来获取数据
    public function getInfoBySrc($src){
        $map = array(
            'src' => array("EQ",$src)
        );
        return M('order_source')->field('*')->where($map)->find();
    }

    public function getLocationListCount($src,$source,$group,$visible,$start,$end)
    {
        $map = array(
            "a.type" => array("EQ",2)
        );

        if (!empty($src)) {
            $map["a.src"] = array("EQ",$src);
        }

        if (!empty($source)) {
            $map["a.id"] = array("EQ",$source);
        }

        if (!empty($group)) {
            $map["a.groupid"] = array("EQ",$group);
        }

        if ($visible !== "") {
            $map["a.visible"] = array("EQ",$visible);
        }

        if (!empty($start) && !empty($end)) {
            $map["a.addtime"] = array(
                array("EGT",$start),
                array("ELT",$end)
            );
        }

        return M('order_source')->where($map)->alias("a")
                                ->join("left join qz_order_source_group b on a.groupid = b.id")
                                ->count();
    }

    public function getLocationList($src,$source,$group,$visible,$start,$end,$pageIndex,$pageCount)
    {
        $map = array(
            "a.type" => array("EQ",2)
        );

        if (!empty($src)) {
            $map["a.src"] = array("EQ",$src);
        }

        if (!empty($source)) {
            $map["a.id"] = array("EQ",$source);
        }

        if (!empty($group)) {
            $map["b.id"] = array("EQ",$group);
        }

        if ($visible !== "") {
            $map["a.visible"] = array("EQ",$visible);
        }

        if (!empty($start) && !empty($end)) {
            $map["a.addtime"] = array(
                array("EGT",$start),
                array("ELT",$end)
            );
        }

        return M('order_source')->alias("a")->where($map)
                                ->join("left join qz_order_source_group b on a.groupid = b.id")
                                ->join("left join qz_order_source_group c on c.id = b.parentid")
                                ->field("a.*,b.parentid, b.name as groupname,c.name as three_name")
                                ->limit($pageIndex.",".$pageCount)
                                ->order("a.id desc")->select();
    }

    //获取列表
    public function getList($condition,$pageIndex,$pageCount){

        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $Db = M('order_source');
        $count  = $Db->alias("a")->where($condition)
            ->join("left join qz_order_source_group as g on a.groupid = g.id")
            ->join("left join qz_order_source_group as d on d.id = g.parentid")
            ->join("LEFT JOIN qz_order_source_in_relation AS r ON r.src = a.src")
            ->count();

        $buildSql = $Db->alias("a")->where($condition)
            ->join("left join qz_order_source_group as g on a.groupid = g.id")
            ->join("left join qz_order_source_group as d on d.id = g.parentid")
            ->join("LEFT JOIN qz_order_source_in_relation AS r ON r.src = a.src")
            ->field([
                "a.id", "a.src", "a.name", "a.groupid", "a.dept", "a.charge", "a.isshow", "a.alias",
                "g.name as groupname", "d.name as parentname", "r.source_in", "a.account_id"
            ])
            ->order('a.id DESC')
            ->limit($pageIndex, $pageCount)
            ->buildSql();

        $result = $Db->table($buildSql)->alias("a")
            ->join("left join qz_department_identify c on c.id = a.dept")
            ->join("left join qz_order_source_account d on d.id = a.account_id")
            ->field(['a.*', 'c.name as deptname', 'd.account_name'])
            ->select();

        return array("result"=>$result,"count" => $count);
    }

    /**
     * 获取推广来源组
     * @param $type [类型]
     * @param $dept [部门ID]
     * @param string $category [渠道分类 1.装修2.家具]
     * @return mixed
     */
    public function getSourceGroup($type,$dept,$category = '1',$is_all = false){
        if (!empty($dept)) {
            $map["_complex"] = array(
                "c.dept" => array("IN",$dept),
                "d.dept" =>array("IN",$dept),
                "_logic" => "OR"
            );
        }
        $where = array(
            "a.parentid" => array("EQ",0),
        );

        //如果是获取所有 , 就不区分 家具/装修 渠道
        if($is_all == false){
            $where["a.category"] = array("EQ",$category);
        }

        if (!empty($type)) {
            $where["a.type"] = array("EQ",$type);
        }

        $buildSql =  M("order_source_group")->alias("a")->where($where)
                                     ->join("left join qz_order_source_group b on a.id = b.parentid")
                                     ->field("a.id as parentid,a.name as parent_name,b.id,b.name,b.category")
                                     ->buildSql();

        return M("order_source_group")->table($buildSql)->alias("t")->where($map)
                                       ->join("left join qz_order_source c on c.groupid = t.id")
                                       ->join("left join qz_order_source d on d.groupid = t.parentid")
                                       ->field("t.*")
                                       ->group("t.id,t.parentid")
                                       ->select();
    }

    //获取 部门
    public function getDept($depts){
        if (count($depts) > 0) {
            $map["id"] = array("IN",$depts);
        }
        return M('department_identify')->where($map)->order('id')->select();
    }

    /**
     * Gets the dept rbac.
     *
     * @return     <type>  The dept rbac.
     */
    public function getDeptRbac(){
        return M('role_identify')->alias('r')
                ->field('r.dept_identify_id,r.role_id,d.name,d.dept_belong')
                ->join('LEFT JOIN qz_department_identify as d ON d.id = r.dept_identify_id')
                ->order('r.dept_identify_id desc')
                ->select();
    }




    //编辑
    public function edit($id,$data){
        $map = array(
            'id' => array("EQ",$id),
        );
        return  M('order_source')->where($map)->save($data);
    }


    /**
     * 发单填写统计
     * @param  [type] $start [开始时间]
     * @param  [type] $end   [结束时间]
     * @return [type]        [description]
     */
    public function getOrderFormStatistics($start,$end)
    {
        $map = array(
            "addtime" => array(
                    array("EGT",$start),
                    array("ELT",$end)
            )
        );
        $buildSql = M("orders_source")->where($map)->buildSql();
        $buildSql = M("orders_source")->table($buildSql)->alias("t")
                                      ->join("inner join qz_orders o on o.id = t.orderid")
                                      ->field('t.orderid,t.have_name,t.have_xiaoqu,t.have_mianji,t.have_start,t.have_yusuan,o.on,o.type_fw')
                                      ->buildSql();
        $buildSql = M("orders_source")->table($buildSql)->alias("t2")
                                      ->field("count(t2.orderid) as count,count(if(t2.on = 4,1,null)) as yx_count,count(if(t2.on = 4 and (t2.type_fw = 1 or t2.type_fw = 3),1,null)) as yx_fen_count,count(if(t2.on = 4 and (t2.type_fw = 2 and t2.type_fw = 4),1,null)) as yx_wen_count,count(if(t2.on not in(0,2,4),1,null)) as wx_count,count(if(t2.have_name <> 0,1,null)) as name_count,count(if(t2.have_name = 0,1,null)) as un_name_count,count(if(t2.have_name <> 0 and t2.on = 4,1,null)) as  yx_name_count,
                                        count(if(t2.have_name = 0 and t2.on = 4,1,null)) as   yx_un_name_count,count(if(t2.have_name <> 0 and t2.on not in(0,2,4),1,null)) as wx_name_count,count(if(t2.have_xiaoqu <> 0,1,null)) as xiaoqu_count,count(if(t2.have_xiaoqu = 0,1,null)) as  un_xiaoqu_count,count(if(t2.have_xiaoqu <> 0 and t2.on = 4,1,null)) as  yx_xiaoqu_count,
                                        count(if(t2.have_xiaoqu = 0 and t2.on = 4,1,null)) as   yx_un_xiaoqu_count,count(if(t2.have_xiaoqu <> 0 and t2.on not in(0,2,4),1,null)) as wx_xiaoqu_count,
                                        count(if(t2.have_mianji <> 0,1,null)) as mianji_count,count(if(t2.have_mianji = 0,1,null)) as un_mianji_count,count(if(t2.have_mianji <> 0 and t2.on = 4,1,null)) as  yx_mianji_count,
                                        count(if(t2.have_mianji = 0 and t2.on = 4,1,null)) as   yx_un_mianji_count,count(if(t2.have_mianji <> 0 and t2.on not in(0,2,4),1,null)) as wx_mianji_count,count(if(t2.have_yusuan <> 0,1,null)) as yusuan_count,count(if(t2.have_yusuan = 0,1,null)) as un_yusuan_count,count(if(t2.have_yusuan <> 0 and t2.on = 4,1,null)) as  yx_yusuan_count,
                                        count(if(t2.have_yusuan = 0 and t2.on = 4,1,null)) as   yx_un_yusuan_count,count(if(t2.have_name <> 0 and t2.on not in(0,2,4),1,null)) as wx_yusuan_count,count(if(t2.have_start <> 0,1,null)) as start_count,count(if(t2.have_start = 0,1,null)) as un_start_count,count(if(t2.have_start <> 0 and t2.on = 4,1,null)) as  yx_start_count,
                                        count(if(t2.have_start = 0 and t2.on = 4,1,null)) as   yx_un_start_count,count(if(t2.have_start <> 0 and t2.on not in(0,2,4),1,null)) as wx_start_count,count(if(t2.have_name <> 0 and t2.on = 4 and (t2.type_fw = 1 OR t2.type_fw = 3),1,null)) as  yx_fen_name_count,count(if(t2.have_name = 0 and t2.on = 4 and (t2.type_fw = 1 OR t2.type_fw = 3),1,null)) as  yx_wen_name_count,count(if(t2.have_xiaoqu <> 0 and t2.on = 4 and (t2.type_fw = 1 OR t2.type_fw = 3),1,null)) as  yx_fen_xiaoqu_count,count(if(t2.have_xiaoqu = 0 and t2.on = 4 and (t2.type_fw = 1 OR t2.type_fw = 3),1,null)) as  yx_wen_xiaoqu_count,count(if(t2.have_mianji <> 0 and t2.on = 4 and (t2.type_fw = 1 OR t2.type_fw = 3),1,null)) as  yx_fen_mianji_count,count(if(t2.have_mianji = 0 and t2.on = 4 and (t2.type_fw = 1 OR t2.type_fw = 3),1,null)) as  yx_wen_mianji_count,count(if(t2.have_yusuan <> 0 and t2.on = 4 and (t2.type_fw = 1 OR t2.type_fw = 3),1,null)) as  yx_fen_yusuan_count,count(if(t2.have_yusuan = 0 and t2.on = 4 and (t2.type_fw = 1 OR t2.type_fw = 3),1,null)) as  yx_wen_yusuan_count,count(if(t2.have_start <> 0 and t2.on = 4 and (t2.type_fw = 1 OR t2.type_fw = 3),1,null)) as  yx_fen_start_count,count(if(t2.have_start = 0 and t2.on = 4 and (t2.type_fw = 1 OR t2.type_fw = 3),1,null)) as  yx_wen_start_count")
                                    ->buildSql();

        return M("orders_source")->table($buildSql)->alias("t3")
                                      ->field("count,yx_count,wx_count,round(yx_count/count,3)*100 as rate,yx_fen_count,yx_wen_count,name_count,xiaoqu_count,mianji_count,yusuan_count,start_count,un_name_count,un_xiaoqu_count,un_mianji_count,un_yusuan_count,un_start_count,round(yx_name_count/yx_count,3)*100 as yx_rate_name_count,round(wx_name_count/wx_count,3)*100 as wx_rate_name_count,round(yx_name_count/count,3)*100 as yx_name_count,round(yx_un_name_count/count,3)*100 as yx_un_name_count,round(yx_xiaoqu_count/yx_count,3)*100 as yx_rate_xiaoqu_count,round(wx_xiaoqu_count/wx_count,3)*100 as wx_rate_xiaoqu_count,round(yx_xiaoqu_count/count,3)*100 as yx_xiaoqu_count,round(yx_un_xiaoqu_count/count,3)*100 as yx_un_xiaoqu_count,round(yx_mianji_count/yx_count,3)*100 as yx_rate_mianji_count,round(wx_mianji_count/wx_count,3)*100 as wx_rate_mianji_count,round(yx_mianji_count/count,3)*100 as yx_mianji_count,round(yx_un_mianji_count/count,3)*100 as yx_un_mianji_count,round(yx_yusuan_count/yx_count,3)*100 as yx_rate_yusuan_count,round(wx_yusuan_count/wx_count,3)*100 as wx_rate_yusuan_count,round(yx_yusuan_count/count,3)*100 as yx_yusuan_count,round(yx_un_yusuan_count/count,3)*100 as yx_un_yusuan_count,round(yx_start_count/yx_count,3)*100 as yx_rate_start_count,round(wx_start_count/wx_count,3)*100 as wx_rate_start_count,round(yx_start_count/count,3)*100 as yx_start_count,round(yx_un_start_count/count,3)*100 as yx_un_start_count,round(yx_fen_name_count/count,3)*100 as yx_fen_name_count,round(yx_fen_xiaoqu_count/count,3)*100 as yx_fen_xiaoqu_count,round(yx_fen_mianji_count/count,3)*100 as yx_fen_mianji_count,round(yx_fen_yusuan_count/count,3)*100 as yx_fen_yusuan_count,round(yx_fen_start_count/count,3)*100 as yx_fen_start_count,round(yx_wen_name_count/count,3)*100 as un_yx_fen_name_count,round(yx_wen_xiaoqu_count/count,3)*100 as un_yx_fen_xiaoqu_count,round(yx_wen_mianji_count/count,3)*100 as un_yx_fen_mianji_count,round(yx_wen_yusuan_count/count,3)*100 as un_yx_fen_yusuan_count,round(yx_wen_start_count/count,3)*100 as un_yx_fen_start_count")
                                      ->find();
    }


    //部门
    public  function  getDepartments($departments){

         $map['d.name'] = array('in', $departments);
          $adtotals = M('department')->alias("d")
            ->join("INNER JOIN qz_role_department as s on d.id = s.department_id")
            ->join("INNER JOIN qz_rbac_role as p on p.id = s.role_id")
            ->where($map)
            ->field('d.`name`,p.role_name,p.id')
            ->select();
        return $adtotals;
    }

    /**
     * 获取渠道位置列表
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    public function getSourceList($type = 1,$where = '')
    {
        $map = array(
            "type" => array("EQ", $type),
            "visible" => array("EQ", 0)
        );

        if ($where['groupid']) {
            $map['groupid'] = ['eq', $where['groupid']];
        }
        return M("order_source")->where($map)->select();
    }

    /**
     * 获取渠道位置列表
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    public function getAllSourceList($groups = '')
    {
        $map = array(
            "type" => array("EQ", 1),
            "visible" => array("EQ", 0)
        );

        if ($groups) {
            $map['groupid'] = ['in', $groups];
        }
        return M("order_source")->where($map)->select();
    }

    public function getSourceBySrc($src,$field = '*'){
        $map = [];
        if ($src) {
            $map["src"] = array("IN", $src);
        }
        return M("order_source")->field($field)->where($map)->select();
    }


    //根据id值,获取一行记录
    public function getOne($id){
        return M("order_source")->find($id);
    }

    //获取所属组
    public function getGroup($id){
        return M("order_source_group")->find($id);
    }



    /**
     * 获取部门渠道标识
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function findDeptSourceCount($id)
    {
        $map = array(
            "dept" => array("EQ",$id),
            "visible" => array("EQ",0)
        );
        return M("order_source")->where($map)->count();
    }


    /**
     * 查询渠道信息 alias 别称
     * @param  [type] $src [description]
     * @return [type]      [description]
     */
    public function findSrcList($src)
    {
        $map = array(
            "a.visible" => array("EQ",0)
        );

        $map["_complex"] = array(
            "a.alias" => array("LIKE","%$src%"),
            "_logic" => "OR"
        );

        return M("order_source")->where($map)->alias("a")
                                ->field("a.alias as text,a.alias as id")->limit(10)->select();
    }

    /**
     * 查询渠道信息
     * @param  [type] $src [description]
     * @return [type]      [description]
     */
    public function findSrcByLike($src)
    {
        $map = [];
        $map["a.type"]    = ["EQ",1];
        $map["a.visible"] = ["EQ",0];
        $map["a.src"] = ["LIKE","$src%"];

        return M("order_source")->where($map)->alias("a")
            ->field("a.src as text,a.src as id")->limit(10)->select();
    }

    /**
     * 通过名称查询发单位置标识信息
     * @param  string $sourceName 发单位置名称
     * @return  mixed
     */
    public function findSourceLocationByLikeName($sourceName)
    {
        $map = [];
        $map["a.type"]    = ["EQ",2];
        $map["a.visible"] = ["EQ",0];
        $map["a.name"] = ["LIKE","$sourceName%"];

        return M("order_source")->where($map)->alias("a")
            ->field("a.name as text,a.src as id")->limit(10)->select();
    }

     /**
     * 获取渠道及渠道部门信息
     * @return [type] [description]
     */
    public function getAllSourceDeptListCount($src)
    {
        $map = array(
            "visible" => array("EQ",0),
            "type" => array("EQ",1)
        );

        if (!empty($src)) {
            $map["alias"] = array("EQ",$src);
        }
        return M("order_source")->where($map)->count();
    }

    /**
     * 获取渠道及渠道部门信息
     * @return [type] [description]
     */
    public function getAllSourceDeptList($src,$pageIndex,$pageCount)
    {
        $map = array(
            "a.visible" => array("EQ",0),
            "a.type" => array("EQ",1)
        );

        if (!empty($src)) {
            $map["a.alias"] = array("EQ",$src);
        }

        return M("order_source")->where($map)->alias("a")
                        ->join("join qz_department_identify b on a.dept = b.id")
                        ->field("a.id,a.alias,b.name as dept_name,a.charge")
                        ->order("a.dept,a.id")->limit($pageIndex.",".$pageCount)
                        ->select();
    }

    /**
     * 获取部门渠道信息
     * @param  string $dept   [description]
     * @param  string $charge [description]
     * @return [type]         [description]
     */
    public function getSrcListByDept($dept = '',$charge = "")
    {
        $map = array(
            "dept" => array("EQ",$dept)
        );

        if (!empty($charge)) {
            $map["charge"] = array("EQ",$charge);
        }
        return M("order_source")->where($map)->field("src")->select();
    }

    /*
     * 查询特定部门渠道信息
     * @param  [type] $depts [description]
     * @return [type]      [description]
     */
    public function findSrcDeptList($depts)
    {
        $map = array(
            "a.visible" => array("EQ",0)
        );

        if (is_array($depts) && count($depts) > 0) {
            $map['a.dept'] = array(
                'in',$depts
            );
        }

        return M("order_source")->alias("a")
            ->where($map)
            ->field("a.id,a.alias")
            ->select();
    }

    /**
     * 获取二级来源组数量
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getSourceGroupChildCount($id)
    {
        $map = array(
            "parentid" => array("EQ",$id)
        );

        return M("order_source_group")->where($map)->count();
    }

    /**
     * 获取二级来源组
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getSourceGroupChild($id)
    {
        $map = array(
            "parentid" => array("EQ",$id)
        );

        return M("order_source_group")->where($map)->select();
    }

    public function getOrderSourcesList($map){
        $where = [];
        if($map['time']){
            $where['o.time_real'] = $map['time'];
        }
        if($map['dept']){
            $where['s.dept'] = $map['dept'];
        }
        if($map['group']){
            $where['s.groupid'] = $map['group'];
        }
        if($map['src']){
            $where['s.src'] = $map['src'];
        }
        $buildSql = M('orders')->alias('o')
            ->field('o.id,i.src,s.dept,FROM_UNIXTIME(o.time_real,"%Y-%m-%d") t,FROM_UNIXTIME(o.time_real,"%H") h,count(o.id) orders')
            ->join('left join qz_yy_order_info i on i.oid = o.id')
            ->join('left JOIN qz_order_source s on s.src = i.src')
            ->where($where)
            ->group('t,h')
            ->limit(0,100000000)
            ->buildSql();
        return M('yy_order_info')->table($buildSql)->alias('t')
            ->field("t.*,
MAX(IF(t.h = '00',t.orders,0)) AS h0,
MAX(IF(t.h = '01',t.orders,0)) AS h1,
MAX(IF(t.h = '02',t.orders,0)) AS h2,
MAX(IF(t.h = '03',t.orders,0)) AS h3,
MAX(IF(t.h = '04',t.orders,0)) AS h4,
MAX(IF(t.h = '05',t.orders,0)) AS h5,
MAX(IF(t.h = '06',t.orders,0)) AS h6,
MAX(IF(t.h = '07',t.orders,0)) AS h7,
MAX(IF(t.h = '08',t.orders,0)) AS h8,
MAX(IF(t.h = '09',t.orders,0)) AS h9,
MAX(IF(t.h = '10',t.orders,0)) AS h10,
MAX(IF(t.h = '11',t.orders,0)) AS h11,
MAX(IF(t.h = '12',t.orders,0)) AS h12,
MAX(IF(t.h = '13',t.orders,0)) AS h13,
MAX(IF(t.h = '14',t.orders,0)) AS h14,
MAX(IF(t.h = '15',t.orders,0)) AS h15,
MAX(IF(t.h = '16',t.orders,0)) AS h16,
MAX(IF(t.h = '17',t.orders,0)) AS h17,
MAX(IF(t.h = '18',t.orders,0)) AS h18,
MAX(IF(t.h = '19',t.orders,0)) AS h19,
MAX(IF(t.h = '20',t.orders,0)) AS h20,
MAX(IF(t.h = '21',t.orders,0)) AS h21,
MAX(IF(t.h = '22',t.orders,0)) AS h22,
MAX(IF(t.h = '23',t.orders,0)) AS h23,SUM(t.orders) `all`")
            ->group('t.t')
            ->select();
    }

    public function getMyChildSrcList($category,$level,$group_id)
    {
        $map = array(
            "a.type" => array("EQ",1),
            "a.category" => array("EQ",$category)
        );

        if ($level == 1) {
            $map["_complex"] = array(
                "a.parentid" => array("EQ",$group_id),
                "a.id" => array("EQ",$group_id),
                "_logic" => "OR"
            );
        } elseif ($level == 2) {
            $map["a.id"] = array("EQ",$group_id);
        }

        return M("order_source_group")->where($map)->alias("a")
               ->join("left join qz_order_source s on s.groupid = a.id")
               ->field("a.name as group_name,a.id as group_id,s.src")
               ->select();
    }

    public function getLocationSource()
    {
        $map = array(
            "a.type" => array("EQ",2),
            "a.parentid" => array("EQ",0)
        );
        return M("order_source_group")->where($map)->alias("a")
            ->join("join qz_order_source_group b on a.id = b.parentid")
            ->join("join qz_order_source s on s.groupid = b.id")
            ->field("a.name,a.id,b.name as second_name,b.id  as second_id,s.name as three_name,s.id as three_id")
            ->select();
    }

    public function getSourceSrcByName($name){
        $where = [
            'name'=>['like','%'.$name.'%'],
            'type'=>['eq',1],
        ];
        return $this->field('id,name,src')->where($where)->select();
    }

    public function getRelatedSourceByName($name, $limit = 0){
        $where = [
            "type" => array("EQ", 1),
            "visible" => array("EQ", 0),
            "account_id" => array("EQ", 0),
            "name" => array("LIKE", "%".$name."%"),
        ];

        return $this->where($where)
            ->field(["id", "name", "src"])
            ->order("id desc")
            ->limit($limit)
            ->select();
    }

    public function updateAll($ids, $data){
        $map = array(
            "id" => array("IN", $ids),
        );

        return $this->where($map)->save($data);
    }
}