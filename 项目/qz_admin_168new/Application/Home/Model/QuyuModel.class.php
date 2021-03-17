<?php
/**
 * 区域表 对应qz_quyu
 */
namespace Home\Model;
use Think\Model;
class QuyuModel extends Model{

    protected $autoCheckFields = false;

    /**
     * 根据城市编号获取城市信息
     * @return [type] [description]
     */
    public function getCityInfoById($cs){
        $map = array(
            "a.cid"=>array("EQ",$cs)
                     );
        return  M("quyu")->where($map)->alias("a")
               ->join("left join qz_area as b on a.cid = b.fatherid AND b.type=1")->where("a.type=1")
               ->join("left join qz_province as c on c.qz_provinceid = a.uid")
               ->field("a.cid as cid,a.cname,a.uid,a.bm,b.qz_areaid,b.qz_area,b.orders,a.type,a.px,a.px_abc,a.is_open_city,a.mark_red,a.parent_city,a.parent_city1,a.parent_city2,a.parent_city3,a.parent_city4,a.other_city,c.qz_provinceid,c.qz_province")
               ->order("b.orders")->select();
    }

    /**
     * 获取所有的城市及省份
     * @return [type] [description]
     */
    public function getProvinceAndCity () {
        return M("quyu")->alias("a")
                        ->join("left join qz_province as c on c.qz_provinceid = a.uid")
                        ->field("a.cid,a.cname,a.uid,c.qz_province")
                        ->select();
    }

    /**
     * 获取所有城市信息及区县城市
     * @return [type] [description]
     */
    public function getAllCity(){
        return M("quyu")->alias("a")
               ->join("left join qz_area as b on a.cid = b.fatherid AND b.type=1")->where("a.type=1")
               ->join("left join qz_province as c on c.qz_provinceid = a.uid")
               ->field("a.cid as cid,a.cname,a.uid,a.bm,b.qz_areaid,b.qz_area,b.orders,a.type,a.px,a.px_abc,a.map_name,a.is_open_city,a.mark_red,a.parent_city,a.parent_city1,a.parent_city2,a.parent_city3,a.parent_city4,a.other_city,c.qz_provinceid,c.qz_province")
               ->order("a.bm")->select();
    }

    /**
     * [getAllQuyu 获取已开城市的简单信息，排除掉总站]
     * @return [type] [description]
     */
    public function getAllQuyuOnly($field = "*"){
        return M("quyu")->alias("a")
               ->where("a.type=1 AND cid != 000001")
               ->field($field . ",upper(left(a.px_abc,1)) AS first_abc")
               ->order("a.bm,xh")
               ->select();
    }

    public function getAllSimpleQuyuOnly(){
        return M("quyu")->alias("a")
               ->where("a.type=1 AND cid != 000001")
               ->field("a.cid,a.cname,a.bm,upper(left(a.px_abc,1)) AS first_abc")
               ->order("a.bm,xh")
               ->select();
    }

    /**
     * [getQuyuListWithFirstChar 获取带有首字母的区域列表]
     * @param  boolean $cache [是否获取缓存数据]
     * @return [type]         [description]
     */
    public function getQuyuListWithFirstChar($cache = true){
        if ($cache == true) {
            $result = S('QuyuListWithFirstChar');
        }
        if (empty($result)) {
            $result = M("quyu")->alias("a")
                               ->where("a.type=1 AND cid != 000001")
                               ->field("a.cid as cid,a.cname,a.bm,a.uid")
                               ->order("a.bm,xh")
                               ->select();
            import('Library.Org.Util.App');
            $app = new \App();
            foreach ($result as $key => $value) {
                $first_char = $app->getFirstCharter($value["cname"]);
                $result[$key]['char'] = $first_char;
                $result[$key]['cname_with_char'] = $first_char . ' ' . $value["cname"];
            }
            $result = multi_array_sort($result, 'cname_with_char');
            S('QuyuListWithFirstChar', $result, 120);
        }
        return $result;
    }

    /**
     * [getQuyuList 获取区域列表]
     * @return [type] [description]
     */
    public function getQuyuList($cs=null){
        $map = array(
            'type' => 1
        );
        if (!empty($cs)) {
            $map["cid"] = array("EQ",$cs);
        }
        $result = M("quyu")->field('cid,cname,upper(left(px_abc,1)) px')->where($map)->order("cid")->select();
        foreach ($result as $key => $value) {
            $result[$key]["char_name"] = $value['px'].' '.$value["cname"];
        }
        $result = multi_array_sort($result,'px');
        return $result;
    }
    /**
     * [getQuyuList 获取区域列表]
     * @return [type] [description]
     */
    public function getJiajuQuyuList($cs){
        $map['cid'] = ['NEQ', '000001'];
        return M('jiaju_quyu')->where($map)->order('px_abc,cid')->select();
    }

    /**
     * 为CxSelect获取城市区域
     */
    public function getQuyuAndAreaForCxSelect()
    {
        $cxSelect = M("quyu")->alias("a")
                           ->where("a.type = 1 AND cid != 000001")
                           ->field("id, cid AS code, a.cname, px_abc")
                           ->order("a.bm,xh")
                           ->select();
        import('Library.Org.Util.App');
        $app = new \App();
        foreach ($cxSelect as $key => $value) {
            $first_char = strtoupper($value['px_abc'][0]);
            $cxSelect[$key]['text'] = $first_char . ' ' . $value["cname"];
        }
        $cxSelect = multi_array_sort($cxSelect, 'text');
        $area = array();
        $result = M('area')->select();
        foreach ($result as $key => $value) {
            $area[$value['fatherid']][] = array(
                'id' => $value['id'],
                'code' => $value['qz_areaid'],
                'text' => $value['qz_area']
            );
        }
        foreach ($cxSelect as $key => $value) {
            $cxSelect[$key]['children'] = $area[$value['code']];
        }
        return $cxSelect;
    }

    /**
     * 根据城市编号获取城市信息
     * @param  [type] $cities [description]
     * @return [type]         [description]
     */
    public function getCityInfoByIds($cities){
        $map = array(
            "a.cid"=>array("IN",$cities)
                     );
        return  M("quyu")->where($map)->alias("a")
                         ->field("cid,cname,parent_city,parent_city1,parent_city2,parent_city3,parent_city4,other_city")
                         ->select();
    }

    /**
     * 根据城市编号获取城市信息
     * @param  [type] $cities [description]
     * @return [type]         [description]
     */
    public function getJiajuCityInfoByIds($pid){
        return M('area')->field('qz_areaid,qz_area,fatherid,orders')
            ->where(['fatherid' => $pid])
            ->order('orders asc')
            ->select();
    }

    /**
     * 获取所有开站城市信息
     * @return [type] [description]
     */
    public function getOpenCityInfo(){
        //CONCAT('http://',bm,'.',C('QZ_YUMING')) as 'web',CONCAT('http://',C('QZ_YUMINGM'),'/zhaobiao/?bm=',bm) as 'zhaobiao'
        return M("quyu")->field("cid,cname,bm,is_open_city,little,manager,IF(time_add,FROM_UNIXTIME(time_add),'') as time_add ")
                        ->order("time_add DESC")
                        ->select();
    }


    /**
     * [getQuyuListOnly 获取已开区域，返回字段包含该区域大部分信息和该区域的省份]
     * @param  [type] $map [description]
     * @return [type]      [description]
     */
    public function getQuyuListOnly($map){
        $result = M("quyu")->alias("a")->where($map)
               ->join("left join qz_province as c on c.qz_provinceid = a.uid")
               ->field("a.cid as cid,a.cname,a.uid,a.type,a.bm,a.px,a.px_abc,a.parent_city,a.little,c.qz_province,c.qz_bigpart,c.qz_bigpart_name")
               ->order("a.bm")
               ->select();
        return $result;
    }

    //取所有省份
    public function getProvince($id){
        return M('province')->field('*')->order('qz_province')->select();
    }

    //查询单个城市
    public function getQuyu($map = '',$order = ''){
        return M('quyu')->field('*')->order($order)->where($map)->limit('1')->select();
    }

    //查询单个区域
    public function getArea($map = '',$order = '',$limit = '1'){
        return M('area')->field('*')->order($order)->where($map)->limit($limit)->select();
    }

    //增加城市
    public function addQuyu($data){
        return M("quyu")->add($data);
    }

    //增加城市
    public function addAllQuyu($data){
        return M("quyu")->addAll($data);
    }

    //编辑城市
    public function editQuyu($id,$data){
        return M("quyu")->where(array('cid'=>$id))->save($data);
    }

    //增加区域
    public function addArea($data){
        return M("area")->add($data);
    }

    //增加区域
    public function addAllArea($data){
        return M("area")->addAll($data);
    }

    //编辑区域
    public function editArea($id,$data){
        return M("area")->where(array('qz_areaid'=>$id))->save($data);
    }

    //记录日志
    public function addLog($map, $data) {
        if (empty($data)) {
            return false;
        }
        $adddata = array();
        $adddata['cid'] = $map['id'];
        $adddata['action'] = $map['action'];
        $adddata['username'] = session("uc_userinfo.name");
        $adddata['admin_id'] = session("uc_userinfo.id");
        $adddata['postdata'] = json_encode($map).','.json_encode($data);
        $adddata['addtime']  = time();
        return M('log_quyu')->add($adddata);
    }


    /**
     * 取城市的相邻城市信息
     * @return [type] [description]
     */
    public function getAllBaseCity(){
        return M("quyu")->field("cid,cname,bm,parent_city,parent_city1,parent_city2,parent_city3,parent_city4,other_city,is_open_city")
                        ->select();
    }

    public function getOrderRelation(){
        return M("order_city_relation")->select();
    }

    /**
     * 获取标红的热门城市
     * @return [type] [description]
     */
    public function getHotCity (){
        $map = array(
            "type" => array("EQ",1),
            "mark_red" => array("EQ",1)
                     );
        return M("quyu")->where($map)->order("px_abc")->select();
    }

    /**
     * 获取所有的区县
     * @return [type] [description]
     */
    public function getAllArea()
    {
        $map = array(
            "type" => array("EQ",1)
        );
        return M("area")->where($map)->order("fatherid,`orders` asc")->select();
    }

    /**
     * 根据区域获取县区
     * @param  [type] $cid [description]
     * @return [type]      [description]
     */
    public function getAreaByQuyu($cid){
        $map = array(
            "type" => array("EQ", 1),
            "fatherid" => array("EQ", $cid)
        );

        return M("area")->where($map)->field("qz_areaid,qz_area")->order("orders asc,id desc")->select();
    }

     /**
     * 写入热门城市
     * @return [type] [description]
     */
    public function setNewHotCitys($data){
        //所有旧的热门清空
        $data_old = M("quyu")->where("ishotcity = 1 and hotorder > 0")->field('cid,ishotcity,hotorder')->select();
        $set['ishotcity'] = 0;
        $set['hotorder'] = 0;
        $num = M("quyu")->data($set)->where("id > 0")->save();
        $log1['username'] = session("uc_userinfo.name");
        $log1['admin_id'] = session("uc_userinfo.id");
        $log1['action']   = 'edit_hotcity_del';
        $log1['postdata'] = json_encode($data_old);
        $log1['addtime']  =  time();
        M("log_quyu")->data($log1)->add();//日志第一条，删除旧的排序
        //写入新热门城市
        foreach ($data as $k => $v) {
            $map['cid'] = $v['cid'];
            unset($v['cid']);
            $result[] = M('quyu')->where($map)->data($v)->save();
        }
        $log2['username'] = session("uc_userinfo.name");
        $log2['admin_id'] = session("uc_userinfo.id");
        $log2['action']   = 'edit_hotcity_add';
        $log2['postdata'] = json_encode($data);
        $log2['addtime']  =  time();
        M("log_quyu")->data($log2)->add();//日志第二条，添加

        return $result;

    }

    /**
     * 查询新热门城市
     * @return [type] [description]
     */
    public function getNewHotCitys($limit = null){
        $where = 'ishotcity = 1 and hotorder > 0';
        if(!empty($limit)){
            $where .= " limit $limit";
        }
        $data = M('quyu')->where($where)->field('cid,cname,bm,ishotcity,hotorder,isshow')->order('hotorder asc')->select();
        return $data;
    }

    /**
     * [getCnameByCids 通过城市ID数组获取城市的名字]
     * @param  array  $cids [description]
     * @return [type]       [description]
     */
    public function getCnameByCids($cids = array()){
        if (empty($cids)) {
            return false;
        }
        if (!is_array($cids)) {
            $cids = array($cids);
        }
        $map['cid'] = array('IN', $cids);
        $result = M('quyu')->field('cid, cname')->where($map)->select();
        return $result;
    }

    /**
     * 获取字母分类的城市
     * @return [type] [description]
     */
    public function getAllCitysForCount(){
        $map['id'] = array('NEQ',1);
        $quyu = M('quyu')->where($map)->field("id,cid,cname,bm")->select();
        return $quyu;
    }

    /**
     * 获取字母分类的城市
     * @return [type] [description]
     */
    public function getQuyuByBmFirst($first = 'a'){
        $map['px_abc'] = array('like',$first."%");
        $quyu = M('quyu')->where($map)->field("id,cid,cname,bm,px_abc,parent_city")->order("px_abc")->select();
        foreach ($quyu as $k => $v) {
            $num = D("User")->getUserNumByCityId($v['cid']);//真实会员数
            $point_num = D("User")->getPointsNumByCityId($v['cid']);//会员分单指标数
            $quyu[$k]['num'] = $num;
            if($point_num >= $num && $num!= 0){
                $quyu[$k]['flag'] = 1;//选中。绿色
            }else{
                $quyu[$k]['flag'] = 0;//不选中，蓝色
            }
        }
        return $quyu;
    }


    /**
     * 获取 一定时间段中 没有会员的城市 每天的订单数目 统计
     * @param  int $starttime  开始时间戳
     * @param  int $endtime    结束时间戳
     * @return $result 查询节诶过
     */
    public function getNoVipOrderCount($starttime, $endtime) {
        $sqltmp = "
            select SUBSTRING(FROM_UNIXTIME(o.time_real), 1, 10) as timeday,count(o.id) as ordercount from (
                    select count(if(t.on = 2 and c.fake = 0,1,null)) as count,t.cname,t.cs from (
                            select b.cs,a.cname,b.id,b.on from qz_quyu as a
                            inner join qz_user as b on a.cid = b.cs
                            where b.cs <> '000001'
                    ) t INNER join qz_user_company as c on c.userid = t.id
                    group by t.cs HAVING count = 0
            )t1
            INNER join qz_orders as o on o.cs = t1.cs
            WHERE
            o.time_real BETWEEN  %s AND %s
            AND o.`on` = 0
            group by SUBSTRING(FROM_UNIXTIME(o.time_real), 1, 10)
            order BY o.time_real ASC
            ";
        $sql = sprintf($sqltmp, $starttime, $endtime);
        $result = M()->query($sql);
        return $result;
    }

    /**
     * 根据部门查询管辖城市
     * @param  [type] $dep [description]
     * @return [type]      [description]
     */
    public function getCityInfoByDept($dep)
    {
        $map = array(
            "is_open_city" => array("EQ",1)
        );
        switch ($dep) {
            case 5:
                $map["manager"] = array("EQ",1);
                break;
            case 6:
                $map["manager"] = array("NEQ",1);
                break;
        }

        return D("Quyu")->where($map)->field("cid")->select();
    }

    /**
     * 获取已运营城市信息
     * @return [type] [description]
     */
    public function getOpenCityList()
    {
        $map = array(
            "is_open_city"=>array("EQ",1)
        );

        return M('quyu')->where($map)->field("cid,bm,cname")->order("cid")->select();
    }


    /**
     *
     * 通过城市名称查询城市信息和区县（区域）信息
     *
     * @param $cityname
     * @return array
     */
    public function getCityInfoByCityName($cityname){
        $map = [];
        $map['a.cname'] = ['EQ',$cityname];
        $cs = M("quyu")->alias("a")->where($map)
            ->join("LEFT JOIN qz_province as c on c.qz_provinceid = a.uid")
            ->field("a.cid as cid,a.cname,a.uid,a.type,a.bm,a.px,a.px_abc,a.parent_city,a.little,c.qz_province,c.qz_bigpart,c.qz_bigpart_name")
            ->order("a.bm")
            ->find();
        $qx   = M("quyu")->alias("a")->where($map)
            ->join("LEFT JOIN qz_area as c on c.fatherid = a.cid")
            ->field("c.*")
            ->order("c.orders ASC")
            ->select();
        return ['cs'=>$cs,'qx'=>$qx];
    }

    /**
     * 保存
     * @param  [] $map  条件
     * @param  [] $data 数据
     * @return bool       sql结果
     */
    public function saveQuyu($map, $data) {
        if (empty($map) || empty($data)) {
            return false;
        }
        return M('quyu')->where($map)->save($data);
    }

    public function getProvinceLastCityByChar()
    {
        $buildSql = M("province")->alias("a")
                                ->join("join qz_quyu q on a.qz_provinceid = q.uid")
                                ->field("q.px_abc,lower(left(px_abc,1)) as word")->order("q.px_abc desc")->buildSql();

        return  M("province")->table($buildSql)->alias("a")
                            ->group("a.word")->select();
    }

    public function getProvinceLastCityPx()
    {
        $buildSql = M("province")->alias("a")
            ->join("join qz_quyu q on a.qz_provinceid = q.uid")
            ->field("q.uid,q.px")->order("q.px desc")->buildSql();
        return  M("province")->table($buildSql)->alias("a")
            ->group("a.uid")->select();
    }

    // 城市区域关联
    public function getRelationCityList($cid, $is_exclusive, $relation_cs, $relation_qx, $is_relation){
        $map = array(
            "q.type" => array("EQ", 1)
        );

        if (!empty($cid)) {
            $map["q.cid"] = array("EQ", $cid);
        } else {
            $map["q.cid"] = array("NEQ", "000001");
        }

        if (!empty($is_exclusive)) {
            $map["q.is_exclusive"] = array("EQ", 1);
        }

        if (!empty($relation_cs)) {
            $map["q.relation_cs"] = array("EQ", $relation_cs);
        }

        if (!empty($relation_qx)) {
            $map["q.relation_qx"] = array("EQ", $relation_qx);
        }

        if (!empty($is_relation)) {
            $map["q.is_relation"] = array("EQ", $is_relation);
        }

        $list = M("quyu")->alias("q")
            ->where($map)
            ->field(["q.cid", "q.cname", "q.is_open_city", "q.is_relation", "q.relation_cs", "q.relation_qx"])
            ->order("q.is_relation asc,q.is_open_city desc,q.bm asc,q.xh")
            ->select();

        return $list;
    }

    /**
     * 获取远程管辖城市
     * @return [type] [description]
     */
    public function getKefuCitys(){
        // 查询会员公司大于4家的城市
        $userMap = array(
            "u.`on`" => array("EQ", 2),
            "c.fake" => array("EQ", 0)
        );
        $userSql = M("user")->alias("u")
            ->join("join qz_user_company as c on u.id = c.userid")
            ->where($userMap)
            ->field("u.cs")->group("u.cs")
            ->having("count(u.id) > 4")
            ->buildSql();

        // 查询不接受质检介入的城市
        $subMap = array(
            "to" => array("EQ", "jihebu")
        );
        $subSql = M("sales_quyu")->where($subMap)->field("cs")->buildSql();

        // 查询会员公司大于4家的城市或者不接受质检介入的城市
        $map = array(
            "q.is_open_city" => array("EQ", 1),
            "_string" => "q.cid in $userSql or q.cid in $subSql"
        );
        return M("quyu")->alias("q")
            ->where($map)
            ->field("q.cid,q.cname")
            ->select();
    }

    public function getCityInfoByName($city_name)
    {
        if (count($city_name) == 0) {
            return [];
        }
        return $this->field('cid,cname')->where(['cname' => ['in', $city_name]])->select();
    }

    public function getCityInfoByCid($cid)
    {
        return $this->field('cid,cname')->where(['cid' => $cid])->find();
    }
}
