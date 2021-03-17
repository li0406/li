<?php

//区域

namespace Common\Model;
use Think\Model;

class QuyuModel extends Model{

    protected $autoCheckFields = false;

    //根据BM头获取城市ID
    public function getCityIdByBm($bm){
        $thisCity = S("Cache:Quyu:".$bm);
        if(empty($thisCity)){
          $thisCity =  $this->getCityInfoByBm($bm);
        }
        return $thisCity['cid'];
    }

    //根据BM头获取城市ID及相邻城市
    public function getCityInfoByBm($bm){
        //先取单个城市的缓存
        $map['a.bm'] = $bm;
        $map['b.type'] = array("EQ",1);

        $result = M("quyu")->alias("a")->where($map)
           ->join("inner join qz_area as b on a.cid = b.fatherid and b.type = 1")
           ->join("inner join qz_province as c on c.qz_provinceid = a.uid")
           ->field("a.px_abc,a.cid as cid,a.cname,a.uid,a.type,a.bm,a.px,a.px_abc,a.parent_city,a.parent_city1,a.parent_city2,a.parent_city3,a.parent_city4,a.other_city,b.qz_areaid,b.qz_area,b.orders,c.qz_province,c.qz_bigpart,c.qz_bigpart_name,a.lng,a.lat")
           ->order("a.bm,qz_area DESC")->select();

        //导入扩展文件
        import('Library.Org.Util.App');
        $app = new \App();

        $city = array(
            'cid' => $result['0']['cid'],
            'usercount' => '',
            'key' => strtoupper(mb_substr( $result['0']['px_abc'],0,1,"utf-8")),
            'bm' => $result['0']['bm'],
            'uid' => $result['0']['uid'],
            'cname' => $areaKey.' '.$result['0']['cname'],
            'oldName' => $result['0']['cname'],
            'province' => $result['0']['qz_province'],
            'bigpart' => $result['0']['qz_bigpart'],
            'bigpart_name' => $result['0']['qz_bigpart_name'],
            'px' => $result['0']['px'],
            'type' => $result['0']['type'],
            'parent_city' => $result['0']['parent_city'],
            'parent_city1' => $result['0']['parent_city1'],
            'parent_city2' => $result['0']['parent_city2'],
            'parent_city3' => $result['0']['parent_city3'],
            'parent_city4' => $result['0']['parent_city4'],
            'lng' => $result['0']["lng"],
            'lat' => $result['0']["lat"]

        );

        foreach ($result as $key => $value) {
            $tempKey = $app->getFirstCharter($value["qz_area"]);
            $tempCity = array(
                'key' => $tempKey,
                'oldName' => $value['qz_area'],
                'qz_areaid' => $value['qz_areaid'],
                'qz_area' => $tempKey.' '.$value['qz_area'],
                'orders' => $value['orders'],
            );

            $city['child'][] = $tempCity;
        }

        $str = multi_array_sort($city['child'],'key');
        unset($city['child']);
        $city['child'] = $str;

        //取相邻城市数据
        $cids = array();
        if(!empty($city['parent_city'])){
            $cids[] = $city['parent_city'];
        }
        if(!empty($city['parent_city1'])){
            $cids[] = $city['parent_city1'];
        }
        if(!empty($city['parent_city2'])){
            $cids[] = $city['parent_city2'];
        }
        if(!empty($city['parent_city3'])){
            $cids[] = $city['parent_city3'];
        }
        if(!empty($city['parent_city4'])){
            $cids[] = $city['parent_city4'];
        }
        $cids = implode(',', $cids);
        if(!empty($cids)){
            $adjMap['cid'] = array('IN',$cids);
            $city['adj_city'] = M("quyu")->where($adjMap)->field("bm,cname name,cid")->order("field(cid,$cids)")->select();
        }else{
            $city['adj_city'] = '';
        }

        return $city;
    }

    /**
     * [getHotCity 获取热门城市]
     * 子站装修公司列表页友情链接热门城市处调用
     * @return [type] [description]
     */
    public function getHotCity()
    {
        $result = M("quyu")->field('cid,uid,cname,bm')->where(array('hot'=>array('EQ',1)))->select();
        return $result;
    }

    /**
     * 获取首页热门地区
     * @return [type] [description]
     */
    public function getHotCitys($limit){
        return M("quyu")->alias("qy")
                        ->join("inner join (SELECT cs,SUM(case_count) as cc
                                FROM qz_user GROUP BY cs ORDER BY cc DESC LIMIT "
                                . $limit . ") as cscc on qy.cid = cscc.cs")
                        ->field("cscc.cc as count,qy.cname,qy.cid,qy.bm")
                        ->select();
    }

    /**
     * 获取首页的热门城市信息（后台设置的）
     * @return [type]      [description]
     */
    public function getHotCityList(){
        $where = 'ishotcity = 1 and hotorder > 0';
        $city = M("quyu")->where($where)->field("cid,cname,bm,ishotcity,hotorder")->order("hotorder asc")->select();
        return $city;
    }

    /**
     * [getProvinceCityLinkByCid 根据城市ID获取相同省份的城市友情链接]
     * @param  [type] $cid [description]
     * @return [type]      [description]
     */
    public function getProvinceCityLinkByCid($cid)
    {
        if(empty($cid)){
            return false;
        }
        $info = M('quyu')->field('uid')->where(array('cid' => $cid))->find();
        $result = M("quyu")->field('cid,uid,cname,bm')->where(array('uid' => $info['uid'],'little' => '0'))->select();
        return $result;
    }

    //根据 cname 城市名 获取城市信息
    public function getCityIdByCname($cname)
    {
        if (empty($cname)) {
            return false;
        }

        //过滤
        //比如 苏州市 过滤掉市
        $searchArr = array(
                           '市',
                           );
        $replaceArr = array(
                           '',
                           );

        $cname = str_replace($searchArr, $replaceArr, $cname);
        $map = array();
        $map['cname'] = array('eq', $cname);
        return M("quyu")->field('cid,uid,cname,bm')->where($map)->find();
    }

    /**
     * 获取所有的城市
     * @return [type] [description]
     */
    public function getAllCity(){
        return M("quyu")->field("cname,bm,px_abc,mark_red")->order("mark_red desc,px_abc")->select();
    }


    //获取所有城市的vip数量
    public function getCityVipCount($cs){
        $map = array(
            "a.classid" =>array("EQ",3),
            "b.fake" => array("EQ",0),
            "a.on" => '2',
            "a.cs" => $cs
        );

        $cacheKey = 'Cache:vipNum:'.$cs;
        $result = S($cacheKey);
        if (!$result) {
            $result = M("user")->where($map)->alias("a")
                ->join("inner join qz_user_company b on a.id = b.userid")
                ->field("count(if(a.on = 2,a.id,null)) as vipnum")
                ->order("cs desc")
                ->find();
            S($cacheKey, $result, 60*15);
        }
        return $result;
    }

    /**
     * 根据ID获取城市信息
     * @param  string $cid [城市ID]
     * @return array
     */
    public function getCityById($cid)
    {
        $cityInfo = S("C:q:info:".$cid);
        if (!$cityInfo) {
            $map = array(
                "cid" => array("EQ",$cid)
            );

            $cityInfo = M("quyu")->where($map)->field("cid,cname,bm")->find();
            S("C:q:info:".$cid,$cityInfo,3600*6);
        }
        return $cityInfo;
    }


    /**
     * 获取城市 根据指定的城市名
     * @param  string $cname 城市名称
     * @return array | mixed
     */
    public function getCityByName($cname, $limit=1){
        $map = array("cname" => array("IN",$cname));
        return M("quyu")->field("cname,bm")
                        ->where($map)
                        ->limit($limit)
                        ->select();
    }

    /**
     * 根据城市名称获取城市信息
     * @param  string $cname 城市名称
     * @return array | mixed
     */
    public function getCityIdByCityName($cname)
    {
        $map = array("cname" => array("IN",$cname));
        return M("quyu")->field("cid,cname,bm")->where($map)->limit(0,10)->select();
    }

    //获取城市的bm信息
    public function getBm($city_id){
        $map['cid'] = $city_id;
        $bm = $this->field('bm')->where($map)->find();
        return $bm;
    }


    /**
     * 通过城市名称获取城市信息
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public function getCityInfoByName($name)
    {
        $cityInfo = S("Cache:M:Q:".$name);
        if (!$cityInfo) {
            $map = array(
                 "a.cname" => array("EQ",$name)
            );
            $cityInfo = M("quyu")->where($map)->alias("a")
                                 ->join("join qz_province b on a.uid = b.qz_provinceid")
                                 ->field("a.*,b.qz_province as province")
                                 ->find();
            S("Cache:M:Q:".$name,$cityInfo,3600);
        }
        return $cityInfo;
    }

    /**
     * 根据父级城市ID获取区域列表
     * @param   $fatherid  城市id
     * @return  城市区域列表
     */
    public function getAreaByFatherId($fatherid=''){
        if(empty($fatherid)){
            return false;
        }
        $AreaByFatherId = S("C:CS:A1:".$fatherid);
        if (empty($AreaByFatherId)) {
            $AreaByFatherId = M('area')->field('qz_areaid AS id,qz_area AS name')
                                       ->where(array('fatherid' => $fatherid))
                                       ->order('orders')->select();
            S("C:CS:A1:".$fatherid, $AreaByFatherId, 15*60);
        }
        return $AreaByFatherId;
    }

    /*
    *   根据qz_areaid获取区域的详细信息
    *   @param  [string] $cid       [城市编号]
    *   @return [array]  $result    [区域信息数组]
    */
    public function getAreaInfos($cid)
    {
        $map['a.qz_areaid'] = $cid;
        $result = M("area")->alias("a")->where($map)
                           ->join("left join qz_quyu q on q.cid = a.fatherid")
                           ->field("a.*,q.bm")
                           ->find();
        return $result;
    }

    /**
     * 获取所有省份
     */
    public function getAllProvinceList()
    {
        return M("province")->order("qz_provinceid")->field("qz_provinceid as id,qz_province as name")->select();
    }

    /**
     * 获取省份城市列表
     * @param string $value
     */
    public function getProvinceCityList($province_id)
    {
        $map = array(
            "uid" => array("EQ",$province_id)
        );
        return $this->where($map)->field("cname as name,cid as id")->order("px_abc,cid")->select();
    }

    //根据城市id获取城市信息
    public function getCityInfoByCid($cid)
    {
        $map = [];
        $map['cid'] = $cid;
        return M("quyu")->field('cid,uid,cname,bm')->where($map)->find();

    }


    /**
     * 获取所有省份及城市
     * @flag bool 是否按省份划分 true 是 false 不是
     * @return [type] [description]
     */
    public function getAllProvinceAndCitys($flag = false){
        import('Library.Org.Util.App');
        $app = new \App();
        $map = array(
                "b.cid" =>array("NEQ","000001"),
        );

        $result = M("province")->alias("a")
                ->join("inner join qz_quyu as b on a.qz_provinceid = b.uid AND b.type = '1' AND is_open_city = '1' ")
                ->field("a.*,b.cname,b.cid,b.bm,b.px,b.px_abc,b.mark_red")
                ->order("b.cid")
                ->select();

        if (count($result) > 0) {
            if ($flag) {
                //按省份
                foreach ($result as $key => $value) {
                    if(!array_key_exists($value["qz_provinceid"], $citys)){
                        $str = $app->getFirstCharter($value["cname"]);
                        $citys[$value["qz_provinceid"]]["abc"] = $str;
                        $citys[$value["qz_provinceid"]]["pid"] = $value["qz_provinceid"];
                        $citys[$value["qz_provinceid"]]["pname"] = $value["qz_province"];
                        if($value["qz_province"] == '重庆市'){
                            $value["qz_province"] = '重庆';
                        }
                        $str = $app->getFirstCharter($value["qz_province"]);
                        $citys[$value["qz_provinceid"]]["abc"] = $str;
                        $citys[$value["qz_provinceid"]]["child"] = array();
                    }
                    $citys[$value["qz_provinceid"]]["child"][] = $value;
                }
                //省份排序
                foreach ($citys as $key => $value) {
                    $edition[] = $value["abc"];
                }
                array_multisort($edition, SORT_ASC,$citys);
                unset($edition);
                //城市排序
                foreach ($citys as $key => $value) {
                    $edition = array();
                    foreach ($value["child"] as $k => $v) {
                        $edition[] = $v["px"];
                    }
                    array_multisort($edition, SORT_ASC,$citys[$key]["child"]);
                }

            } else {
                //按字母
                foreach ($result as $key => $value) {
                    $str = $app->getFirstCharter($value["cname"]);
                    if(!array_key_exists($str, $citys)){
                        $citys[$str]["pname"] = $str;
                        $citys[$str]["child"] = array();
                    }
                    $citys[$str]["child"][] = $value;
                }
                //按城市首字母排序
                foreach ($citys as $key => $value) {
                    $edition[] = $value["pname"];
                }
                array_multisort($edition, SORT_ASC,$citys);
                unset($edition);
                //按城市的拼音排序
                foreach ($citys as $key => $value) {
                    $edition = array();
                    foreach ($value["child"] as $k => $v) {
                        $edition[] = strtolower($v["px_abc"]);
                    }
                    array_multisort($edition, SORT_ASC,$citys[$key]["child"]);
                    unset($edition);
                }
            }
        }

        return $citys;
    }

    /**
     * 获取数组形式的城市信息
     * @param  [type] $cs [城市编号]
     * @param  [type] $all [是否选择全部城市]
     * @param  [type] $filter [是否过滤会员是0的城市]
     * @return [type]      [description]
     */
    public function getCityArray($cs,$all = false){
        $allCitys = $this->getCitys();
        
        $citys = array();
        //如果有城市编号
        if(!empty($cs)){
            $allCity[] = $allCitys[$cs];
        }else{
            $allCity = $allCitys;
            // foreach ( $allCity as $key => $value) {
            //     if($value["usercount"] == 0){
            //         if($filter){
            //             unset($allCity[$key]);
            //         }
            //     }
            // }
        }

        foreach ($allCity as $key => $value) {
            if($value["type"] == 1){
                $shen = array(
                    "id"=>$value["cid"],
                    "oldname"=>$value["oldName"],
                    "cname"=>$value["cname"]
                );
                $citys["shen"][] = $shen;
            }else{
                if($all){
                    $shen = array(
                        "id"=>$value["cid"],
                        "oldname"=>$value["oldName"],
                        "cname"=>$value["cname"]
                              );
                    $citys["shen"][] = $shen;
                }
            }

            // 准备要排序的数组
            $edition = array();
            foreach ($value["child"] as $k => $v) {
                $edition[] = $v["orders"];
            }
            array_multisort($edition, SORT_ASC, $value["child"]);
            $citys["shi"][$value["cid"]] = $value["child"];
        }
        return $citys;
    }

    public function getCitys(){
        $citys = S("Cache:Citys");
        if(!$citys){
            //导入扩展文件
            import('Library.Org.Util.App');
            $app = new \App();
            $map = array(
                "a.type"=>array("EQ",1)
            );

            //1.获取所有的城市信息
            $buildSql =  M("quyu")->where($map)->alias("a")
                ->join("left join qz_user as u on u.cs = a.cid and u.on = 2")
                ->field("a.*,count(u.id) as usercount")
                ->group("a.cid")
                ->buildSql();

            $list =  M("quyu")->table($buildSql)->where(array("b.type"=>array("EQ",1)))->alias("a")
                ->join("inner join qz_area as b on a.cid = b.fatherid and b.type = 1")
                ->join("inner join qz_province as c on c.qz_provinceid = a.uid")
                ->field("a.usercount, a.cid as cid,a.cname,a.uid,a.type,a.bm,a.px,a.px_abc,a.parent_city,a.parent_city1,a.parent_city2,a.parent_city3,a.parent_city4,a.other_city,b.qz_areaid,b.qz_area,b.orders,c.qz_province,c.qz_bigpart,c.qz_bigpart_name,a.lng,a.lat")
                ->order("a.bm")->select();

            if(count($list)>0){
                $citys = array();
                foreach ($list as $key => $value) {
                    if(!array_key_exists($value['cid'], $citys)){
                        $citys[$value['cid']] = array();
                        $citys[$value['cid']]["cid"] = $value["cid"];
                        //会员数量
                        $citys[$value['cid']]["usercount"] = $value["usercount"];
                        //增加首字母大写
                        $str =  substr( ucfirst($value["px_abc"]) , 0, 1);
                        if (empty($str)) {
                            $str = $app->getFirstCharter($value["cname"]);
                        }

                        $citys[$value['cid']]["key"]          = $str;
                        $citys[$value['cid']]["bm"]           = $value["bm"];
                        $citys[$value['cid']]["uid"]          = $value["uid"];
                        $citys[$value['cid']]["cname"]        = $str." ".$value["cname"];
                        $citys[$value['cid']]["oldName"]      = $value["cname"];
                        $citys[$value['cid']]["province"]     = $value["qz_province"];
                        $citys[$value['cid']]["bigpart"]      = $value["qz_bigpart"];
                        $citys[$value['cid']]["bigpart_name"] = $value["qz_bigpart_name"];
                        $citys[$value['cid']]["px"]           = $value["px"];
                        $citys[$value['cid']]["type"]         = $value["type"];
                        $citys[$value['cid']]["parent_city"]  = $value["parent_city"];
                        $citys[$value['cid']]["parent_city1"] = $value["parent_city1"];
                        $citys[$value['cid']]["parent_city2"] = $value["parent_city2"];
                        $citys[$value['cid']]["parent_city3"] = $value["parent_city3"];
                        $citys[$value['cid']]["parent_city4"] = $value["parent_city4"];
                        $citys[$value['cid']]["lng"] = $value["lng"];
                        $citys[$value['cid']]["lat"] = $value["lat"];
                        $citys[$value['cid']]["child"]        = array();
                    }

                    $str = $app->getFirstCharter($value["qz_area"]);
                    $quyu = array(
                            "key"=>$str,
                            "oldName" =>$value["qz_area"],
                            "qz_areaid"=>$value["qz_areaid"],
                            "qz_area" =>$str." ".$value["qz_area"],
                            "orders" => $value["orders"],
                                  );
                    $citys[$value['cid']]["child"][]= $quyu;
                }
                $edition = array();
                foreach ($citys as $key => $value) {
                    // 准备要排序的数组
                    $edition[] = $value["key"];
                }
                array_multisort($edition, SORT_ASC,SORT_STRING,$citys);
                foreach ($citys as $key => $value) {
                    // 准备要排序的数组
                    $edition = array();
                    foreach ($value["child"] as $k => $v) {
                        $edition[] = $v["key"];
                    }
                    array_multisort($edition, SORT_ASC, $citys[$key]["child"]);
                }
                //因为排序,重新替换数组的键
                foreach ($citys as $key => $value) {
                    $citys[$value["cid"]] = $value;
                    unset($citys[$key]);
                }

                S("Cache:Citys",$citys,7200);
            }
        }

        return $citys;
    }

    public function getCityByCid($cid)
    {
        return $this->field('u.cid,u.cname,u.bm')->alias('u')->where(['u.cid' => ['in', $cid]])->select();
    }

    public function getAllOpenCitys()
    {
        $map = [
            'b.type' => 1,
            'b.is_open_city'
        ];
        return M('province')->alias('a')
            ->where($map)
            ->field('a.id,a.qz_provinceid,a.qz_province,a.province_abc,a.qz_bigpart,a.qz_bigpart_name,a.type,b.cname,b.cid,b.bm,b.px,b.px_abc,b.mark_red')
            ->join('INNER JOIN qz_quyu AS b ON a.qz_provinceid = b.uid')
            ->order('b.px_abc')
            ->select();
    }


}
