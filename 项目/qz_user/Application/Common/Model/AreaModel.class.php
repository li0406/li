<?php
/**
 * 开通的城市、区县
 */
namespace Common\Model;
use Think\Model;
class AreaModel extends Model{
    protected $autoCheckFields = false; //设置autoCheckFields属性为false后，就会关闭字段信息的自动检测，因为ThinkPHP采用的是惰性数据库连接，只要你不进行数据库查询操作，是不会连接数据库的。
    var $citys = null;
    var $quyu  = null;
    public function _initialize(){
        $this->citys = S("Cache:Area");
        //$this->citys = false;  //这个东西谁敢在线上不注释掉,只有罚钱了
        if(!$this->citys){
            //导入扩展文件
           import('Library.Org.Util.App');
           $app = new \App();
           //1.获取所有的城市信息
            $user_map = [
                'ON'=>['eq',2],
                'classid'=>['eq',3],
            ];
            $buildSql = M("user")->field('cs,count(id) usercount')->where($user_map)->group('cs')->buildSql();
            $buildSql = M()->table($buildSql)->alias('u')
                ->field('a.*,u.usercount')
                ->join('right join qz_quyu a on a.cid = u.cs and a.type = 1')
                ->buildSql();
           $list =  M("quyu")->table($buildSql)->where(array("b.type"=>array("EQ",1)))->alias("a")
           ->join("inner join qz_area as b on a.cid = b.fatherid and b.type = 1")
           ->join("inner join qz_province as c on c.qz_provinceid = a.uid")
           ->field("a.usercount, a.cid as cid,a.cname,a.uid,a.type,a.bm,a.px,a.px_abc,a.parent_city,a.parent_city1,a.parent_city2,a.parent_city3,a.parent_city4,a.other_city,b.qz_areaid,b.qz_area,b.orders,c.qz_province,c.qz_bigpart,c.qz_bigpart_name")
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
                $this->citys = $citys;
                S("Cache:Area",$citys,7200);
           }
        }
    }

    /**
     * 获取全部的城市
     * @return [type] [description]
     */
    public function getAllCity(){
        $citys =  array_values($this->citys);
        return $citys;
    }

    /**
     * 根据BM头获取城市ID
     * @return [type] [description]
     */
    public function getCityIdByBm($bm){
        $cid = null;
        foreach ($this->citys as $key => $value) {
            if(strtolower($value['bm']) == strtolower($bm)){
                $cid = $value["cid"];
                break;
            }
        }
        return $cid;
    }

    /**
     * 根据BM头获取城市ID及相邻城市
     * @return [type] [description]
     */
    public function getCityInfoByBm($bm){
        $city = null;
        foreach ($this->citys as $key => $value) {
            if(strtolower($value['bm']) == strtolower($bm)){
                //获取相邻城市
                if(!empty($this->citys[$value['parent_city']]["bm"])){
                    $value["adj_city"][] = array(
                                    "bm"=>$this->citys[$value['parent_city']]["bm"],
                                    "name"=>$this->citys[$value['parent_city']]["oldName"],
                                    "cid"=>$value['parent_city']
                                               );
                }
                if(!empty($this->citys[$value['parent_city1']]["bm"])){
                    $value["adj_city"][] = array(
                                    "bm"=>$this->citys[$value['parent_city1']]["bm"],
                                    "name"=>$this->citys[$value['parent_city1']]["oldName"],
                                    "cid"=>$value['parent_city1']
                                               );
                }

                if(!empty($this->citys[$value['parent_city2']]["bm"])){
                    $value["adj_city"][] = array(
                                    "bm"=>$this->citys[$value['parent_city2']]["bm"],
                                    "name"=>$this->citys[$value['parent_city2']]["oldName"],
                                    "cid"=>$value['parent_city2']
                                               );
                }
                if(!empty($this->citys[$value['parent_city3']]["bm"])){
                    $value["adj_city"][] = array(
                                    "bm"=>$this->citys[$value['parent_city3']]["bm"],
                                    "name"=>$this->citys[$value['parent_city3']]["oldName"],
                                    "cid"=>$value['parent_city3']
                                               );
                }
                if(!empty($this->citys[$value['parent_city4']]["bm"])){
                    $value["adj_city"][] = array(
                                    "bm"=>$this->citys[$value['parent_city4']]["bm"],
                                    "name"=>$this->citys[$value['parent_city4']]["oldName"],
                                    "cid"=>$value['parent_city4']
                                               );
                }

                $city = $value;
                break;
            }
        }
        return $city;
    }

     /**
     * 根据城市编号获取城市信息
     * @ $order bool 是否按照首字母排序
     * @return [type] [description]
     */
    public function getCityInfoById($id,$order = true){
        $city = null;
        foreach ($this->citys as $key => $value) {
            if($value['cid'] == $id){
                if(!$order){
                     // 准备要排序的数组
                    $edition = array();
                    foreach ($value["child"] as $k => $v) {
                        $edition[] = $v["orders"];
                    }
                    array_multisort($edition, SORT_ASC, $value["child"]);
                }
                $city = $value;
                break;
            }
        }
        return $city;
    }

    /**
     * 根据城市ID获取城市,区县信息
     * @return [type] [description]
     */
    public function getCityById($id,$prefix = true){
        $citys = array();
        foreach ($this->citys as $key => $value) {
            if($key == $id){
                $citys[] = $value;
                break;
            }
        }

        if(!$prefix){
            $exp = explode(' ',$citys[0]["cname"]);
            $citys[0]["cname"] = $exp[1];
            foreach ($citys[0]["child"] as $key => $value) {
                $exp = explode(' ',$value["qz_area"]);
                $citys[0]["child"][$key]["qz_area"] = $exp[1];
            }
            $edition = array();
            foreach ($citys[0]["child"] as $k => $v) {
                $edition[] = $v["orders"];
            }
            array_multisort($edition, SORT_ASC, $citys[0]["child"]);
        }
        return $citys;
    }

    /**
     * 根据城市ID获取省份中的所有城市,区县信息
     * @return [type] [description]
     */
    public function getAllCityById($id){
        $citys = array();
        foreach ($this->citys as $key => $value) {
            if($value["uid"] == $id){
                $citys[] = $value;
                break;
            }
        }
        return $citys;
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

    /*    return M("quyu")->alias("a")
                        ->join("inner JOIN qz_cases as b on a.cid = b.cs")
                        ->field("count(b.id) as count,a.cname,a.cid,a.bm")
                        ->group("b.cs")
                        ->order("count desc")->limit($limit)->select();*/
    }


    //获取城市 根据指定的城市名
    public function getCityByName($citys){
        $map = array("cname" => array("IN",$citys));
        return M("quyu")->field("cname,bm")
                        ->where($map)
                        ->limit($limit)
                        ->select();
    }

    /**
     * 获取所有省份及城市
     * @flag bool 是否按省份划分
     * @return [type] [description]
     */
    public function getAllProvinceAndCitys($flag = false){
        import('Library.Org.Util.App');
        $app = new \App();
        $map = array(
                "b.cid" =>array("NEQ","000001"),
                "b.type" => array("EQ","1")
                     );
        $result = M("province")->alias("a")
                            ->join("inner join qz_quyu as b on a.qz_provinceid = b.uid")
                            ->join("INNER JOIN  (select count(*) as count,uid from qz_quyu GROUP BY uid ) as c on c.uid = a.qz_provinceid")
                            ->field("a.*,b.cname,b.cid,b.bm,b.px,b.px_abc,b.mark_red,c.count")
                            ->order("count desc,b.px")
                            ->select();
        $citys = array();
        if(count($result)>0){
            if($flag){
                foreach ($result as $key => $value) {
                    $str = $app->getFirstCharter($value["cname"]);
                    if(!array_key_exists($str, $citys)){
                        $citys[$str]["pname"] = $str;
                        $citys[$str]["child"] = array();
                    }
                    $citys[$str]["child"][] = $value;
                }
                ksort($citys);
                foreach($citys as $keyc => &$valuec) { //最外层字母层
                    $px_abc = array();
                    foreach($valuec['child'] as $keycs => $valuecs) { //城市层
                        $px_abc[] =  $valuecs['px_abc'];
                    }
                    //sort($px_abc);
                    array_multisort($px_abc, SORT_ASC, $valuec['child']); //按照 px_abc升序排列
                }
            }else{
                foreach ($result as $key => $value) {
                    if(!array_key_exists($value["qz_provinceid"], $citys)){
                        $citys[$value["qz_provinceid"]]["pid"] = $value["qz_provinceid"];
                        $citys[$value["qz_provinceid"]]["pname"] = $value["qz_province"];
                        $citys[$value["qz_provinceid"]]["count"] = $value["count"];
                        if($value["qz_province"] == '重庆市'){
                            $value["qz_province"] = '重庆';
                        }
                        $citys[$value["qz_provinceid"]]["abc"] = $app->getFirstCharter($value["qz_province"]);
                        $citys[$value["qz_provinceid"]]["child"] = array();
                    }
                    $citys[$value["qz_provinceid"]]["child"][] = $value;
                }
            }
            return $citys;
        }
        return null;
    }

     /**
     * 根据城市名称获取城市信息
     * @return [type] [description]
     */
    public function getCityIdByName($name){
        $city = null;
        foreach ($this->citys as $key => $value) {
            if(strpos($value['cname'],trim($name)) !== false){
                $city = $value;
                break;
            }
        }
        return $city;
    }

    /**
     * 获取各大区的所有城市
     * @return [type] [description]
     */
    public function getLargArea(){
        $largArea = array();
        foreach ($this->citys as $key => $value) {
            if($value['type'] == 1){
                if(!array_key_exists($value["bigpart"], $largArea)){
                    $largArea[$value["bigpart"]] = array();
                    $largArea[$value["bigpart"]]["province"] = $value["province"];
                    $largArea[$value["bigpart"]]["bigpart_name"] = $value["bigpart_name"];
                    $largArea[$value["bigpart"]]["child"] = array();
                }
                $exp = explode(' ',$value["cname"]);
                $value["cname"] = $exp[1];
                $largArea[$value["bigpart"]]["child"][] = $value;
            }
        }

        ksort($largArea);

        foreach ($largArea as $key => $value) {
            $edition = array();
            // 准备要排序的数组
            foreach ($value["child"] as $k => $val) {
                $edition[] = $val["px"];
            }
            array_multisort($edition, SORT_ASC, $largArea[$key]["child"]);
        }
        return $largArea;
    }

    /**
     * 获取数组形式的城市信息
     * @param  [type] $cs [城市编号]
     * @param  [type] $all [是否选择全部城市]
     * @param  [type] $filter [是否过滤会员是0的城市]
     * @return [type]      [description]
     */
    public function getCityArray($cs,$all = false){
        $citys = array();
        //如果有城市编号
        if(!empty($cs)){
            $allCity[] = $this->citys[$cs];
        }else{
            $allCity = $this->citys;
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
}
