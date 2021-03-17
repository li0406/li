<?php
namespace Home\Model;

Use Think\Model;

class AreaModel extends Model{
    protected $autoCheckFields = false;
    var $citys = null;
    var $quyu  = null;
    public function _initialize(){
        $this->citys = [];//S("Cache:Area");
        if(!$this->citys){
            import("Library.Org.Util.App");
            $app = new \App();
            $list =  M("quyu")->alias("a")
            ->join("inner join qz_area as b on a.cid = b.fatherid ")
            ->join("inner join qz_province as c on c.qz_provinceid = a.uid")
            ->field("a.cid as cid,a.cname,a.uid,a.bm,b.qz_areaid,b.qz_area,b.orders,a.type,a.px,a.px_abc,a.is_open_city,a.mark_red,a.parent_city,a.parent_city1,a.parent_city2,a.parent_city3,a.parent_city4,a.other_city,c.qz_provinceid,c.qz_province,a.little")
            ->order("a.bm")->select();
            if(count($list)>0){
                $citys = array();
                foreach ($list as $key => $value) {
                    if(!array_key_exists($value['cid'], $citys)){
                        $citys[$value['cid']] = array();
                        $citys[$value['cid']]["cid"] = $value["cid"];
                        //增加首字母大写
                        $str = $app->getFirstCharter($value["cname"]);
                        $citys[$value['cid']]["key"]          = $str;
                        $citys[$value['cid']]["bm"]           = $value["bm"];
                        $citys[$value['cid']]["uid"]          = $value["uid"];
                        $citys[$value['cid']]["cname"]        = $str." ".$value["cname"];
                        $citys[$value['cid']]["oldname"]      = $value["cname"];
                        $citys[$value['cid']]["type"]         = $value["type"];
                        $citys[$value['cid']]["is_open_city"] = $value["is_open_city"];
                        $citys[$value['cid']]["mark_red"]     = $value["mark_red"];
                        $citys[$value['cid']]["px"]           = $value["px"];
                        $citys[$value['cid']]["px_abc"]       = $value["px_abc"];
                        $citys[$value['cid']]["child"]        = array();
                        $citys[$value['cid']]["parent_city"]  = $value["parent_city"];
                        $citys[$value['cid']]["parent_city1"] = $value["parent_city1"];
                        $citys[$value['cid']]["parent_city2"] = $value["parent_city2"];
                        $citys[$value['cid']]["parent_city3"] = $value["parent_city3"];
                        $citys[$value['cid']]["parent_city4"] = $value["parent_city4"];
                        $citys[$value['cid']]["other_city"]   = $value["other_city"];
                        $citys[$value['cid']]["qz_province"]     = $value["qz_province"];
                        $citys[$value['cid']]["qz_provinceid"]     = $value["qz_provinceid"];
                        $citys[$value['cid']]["little"]        = $value["little"];
                    }

                    $str = $app->getFirstCharter($value["qz_area"]);
                    $quyu = array(
                            "key"=>$str,
                            "qz_areaid"=>$value["qz_areaid"],
                            "qz_area" =>$str." ".$value["qz_area"],
                            "orders" => $value["orders"],
                            "oldname" =>$value["qz_area"]
                                  );
                    $citys[$value['cid']]["child"][]= $quyu;
                }
                $edition = array();
                foreach ($citys as $key => $value) {
                    // 准备要排序的数组
                    $edition[] = $value["key"];
                }
                array_multisort($edition, SORT_ASC, $citys);
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
                S("Cache:Area",$citys,30);
           }
        }
    }
    /**
     * [getRealVipProvinceCityAndArea 为select联动获取省份数据]
     * @return [type] [description]
     */
    public function getRealVipProvinceCityAndArea()
    {

        $info = M('user')->alias('u')
                         ->field('q.cid,q.uid,q.cname,q.bm,q.px,p.qz_province')
                         ->join('qz_user_company AS c ON c.userid = u.id')
                         ->join('qz_quyu AS q ON q.cid = u.cs')
                         ->join('qz_province AS p ON p.qz_provinceid = q.uid')
                         ->where('c.fake = 0 AND c.viptype > 0')
                         ->group('u.cs')
                         ->select();
        $province = [];
        $city = [];
        import('Library.Org.Util.App');
        $app = new \App();
        foreach ($info as $key => $value) {
            if(empty($province[$value['uid']])){
                $province[$value['uid']] = ['id' => $value['uid'],'text' => $app->getFirstCharter($value['qz_province']) . ' ' . $value['qz_province'],'children' => [['id' => '', 'text' => '请选择市','children' => [['id' => '', 'text' => '请选择区']]]]];
            }
            if(empty($city[$value['cid']])){
                $city[$value['cid']] = ['id' => $value['cid'],'uid' => $value['uid'], 'text'=>$value['cname'],'px' => $value['px'],'children' => [['id' => '', 'text' => '请选择区']]];
            }
        }

        //将区县并入城市中
        $area = M('area')->order('orders')->select();
        foreach ($area as $key => $value) {
            array_push($city[$value['fatherid']]['children'], ['id' => $value['qz_areaid'], 'text' => $value['qz_area']]);
        }
        //将城市并入省份
        foreach ($city as $key => $value) {
            if(!empty($province[$value['uid']])){
                array_push($province[$value['uid']]['children'], $value);
            }
        }

        $results = [];
        foreach ($province as $key => $value) {
            array_push($results, $value);
        }
        $other = array(
            '0' => array(
                'id' => '',
                'text' => '请选择省',
                'children' => array(
                    '0' => array(
                        'id' => '',
                        'text' => '请选择市',
                        'children' => array(
                            '0' =>array(
                                'id' => '',
                                'text' => '请选择区'
                            )
                        )
                    )
                )
            )
        );

        $results = multi_array_sort($results,'text');
        $result = array_merge($other,$results);
        return $result;
    }

    /**
     * [getAnameByAids 通过区域ID的数组获取区域的名字]
     * @param  array  $aids [description]
     * @return [type]       [description]
     */
    public function getAnameByAids($aids = array()){
        if (empty($aids)) {
            return false;
        }
        if (!is_array($aids)) {
            $aids = array($aids);
        }
        $map['qz_areaid'] = array('IN', $aids);
        $result = M('area')->field('qz_areaid AS aid, qz_area AS aname')->where($map)->select();
        return $result;
    }

    /**
     * [get_level_select_city 获取二级联动城市 城市和区县]
     * @return [array] [二级联动数组]
     */
    public function get_level_select_city()
    {
        //获取二级联动城市 管辖城市和城市下面的区县
        $edition = array();
        $quyu=$this->getAllCitys();
        foreach ($quyu as $key => $value)
        {
            if($value['cid']=="000001"){continue;}//如果是主站则去除 不然会显示一个空
            $child_city_info=$this->getCityById($value['cid'],false);
            if (count(array_filter($child_city_info))>0)
            {
                $city[]=$child_city_info;
                $edition[]=$child_city_info['key'];
            }
        }
        array_multisort($edition, SORT_ASC, $city);
        return $city;//返回联动城市
    }

    /**
     * 获取全部城市信息信息
     * @return [type] [description]
     */
    public function getAllCitys(){
        return M("quyu")->select();
    }

    /**
     * 根据城市ID获取城市,区县信息
     * @return [type] [description]
     */
    public function getCityById($id,$prefix = true){
        $citys = array();
        foreach ($this->citys as $key => $value) {
            if($key == $id){
                $citys = $value;
                break;
            }
        }

        if(!$prefix){
            $exp = explode(' ',$citys["cname"]);
            //$citys["cname"] = $exp[1];
            foreach ($citys["child"] as $key => $value) {
                $exp = explode(' ',$value["qz_area"]);
                $citys["child"][$key]["qz_area"] = $exp[1];
            }
            $edition = array();
            foreach ($citys["child"] as $k => $v) {
                $edition[] = $v["orders"];
            }
            array_multisort($edition, SORT_ASC, $citys["child"]);
        }
        return $citys;
    }

    /**
     * 获取已开通的城市
     * @return [type] [description]
     */
    public function getOpenCtiys($prefix = true){
        $citys = array();
        foreach ($this->citys as $key => $value) {
            //if($value["type"] == 1){
                if(!$prefix){
                    $exp = explode(' ',$value["cname"]);
                    $value["cname"] =$exp[1];
                }
                $citys[] = $value;
            //}
        }
        $allCity = array(
                "cid"=>"000001",
                "cname"=>"总站"
                         );
        array_unshift($citys, $allCity);
        return $citys;
    }

    /**
     * 根据城市名称获取城市信息
     * @param  [type] $city [城市名称]
     * @return [type]       [description]
     */
    public function getCityInfoByName($city,$limit){
        $citys = array();
        $i =0;
        $all = $this->citys;
        $www = array(
                "key"=>"Z",
                "cname"=>"Z 总站",
                "cid" =>"000001"
                     );
        $all[] = $www;
        foreach ($all as $key => $value) {
            if($i < $limit){
                if((strtolower($value["key"]) == $city || strpos($value["cname"],$city) !== false || strpos($value["cid"],$city) !== false)){
                    $citys[] = $value;
                    $i++;
                }
            }else{
                break;
            }
        }
        return $citys;
    }

    /**
     * 获取所有省份及城市
     * @flag bool 是否按省份划分
     * @return [type] [description]
     */
    public function getAllProvinceAndCitys($flag = false){
        import('Library.Org.Util.Wwek');
        $map = array(
            "b.cid" =>array("NEQ","000001"),
            "b.type" => array("EQ","1")
        );
        $result = M("province")->alias("a")
            ->join("inner join qz_quyu as b on a.qz_provinceid = b.uid")
            ->join("INNER JOIN  (select count(*) as count,uid from qz_quyu GROUP BY uid ) as c on c.uid = a.qz_provinceid")
            ->field("a.*,b.cname,b.cid,b.bm,b.px,b.px_abc,b.plate,b.mark_red,c.count")
            ->order("count desc,b.px")
            ->select();
        $citys = array();
        if(count($result)>0){
            if($flag){
                foreach ($result as $key => $value) {
                    $str = substr(strtoupper($value["px_abc"]), 0,1);
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
                        $citys[$value["qz_provinceid"]]["abc"] = getFirstCharter($value["qz_province"]);
                        $citys[$value["qz_provinceid"]]["child"] = array();
                    }
                    $citys[$value["qz_provinceid"]]["child"][] = $value;
                }
            }
            return $citys;
        }
        return null;
    }

    public function getChildByCity($cid,$field = 'qz_areaid,qz_area')
    {
        if (!empty($cid)) {
            if(is_array($cid)){
                $map = ['fatherid' => ['in', $cid]];
            }else{
                $map = ['fatherid' => ['eq', $cid]];
            }
            return $this->field($field)->where($map)->select();
        }
    }

    public function getCityInfoByCid($cid)
    {
        if (empty($cid)) {
            return [];
        }
        $where = [
            'cid' => ['in', $cid]
        ];
        return M('quyu')->field('cid,cname')->where($where)->select();
    }
}