<?php
/**
 * 生成城市JS数据所需Model
 */
namespace Home\Model\Db;
use Think\Model;
class BuildcityModel extends Model{

    protected $autoCheckFields = false;

    public function getVipCity(){

        //导入扩展文件
//        import("@.ORG.Wwek");

        //1.获取所有的城市信息
        $sql = "SELECT a.cid as cid,a.cname,a.uid,a.type,a.px,a.px_abc,b.qz_areaid,b.qz_area,b.orders,c.qz_province
        FROM (
            SELECT a.cs,q.cid,q.cname,q.uid,q.type,q.px,q.px_abc,count(if(a.on = 2,a.id,null)) as vipnum FROM qz_user a
            inner join qz_user_company b on a.id = b.userid
            inner join qz_quyu as q on q.cid = a.cs AND q.is_open_city = '1'
            WHERE ( a.classid = 3 ) AND ( b.fake = 0 ) AND ( a.on = '2' ) OR (q.bm = 'lasa')
            GROUP BY a.cs ORDER BY vipnum desc
        ) a
        inner join qz_area as b on a.cid = b.fatherid and b.type = 1
        inner join qz_province as c on c.qz_provinceid = a.uid
        WHERE b.type = 1";

        $list  = M()->query($sql);


        if(count($list)>0){
            $citys = array();
            import('Library.Org.Util.App');
            $app = new \app();
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
            return $citys;
        }
    }

    /**
     * 获取数组形式的城市信息
     * @param  [type] $cs [城市编号]
     * @param  [type] $all [是否选择全部城市]
     * @return [type]      [description]
     */
    public function getCityArray($cs,$all = false){
        $allCity = $this->getVipCity();
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

    /**
     * [getRealVipProvinceCityAndArea 为select联动获取省份数据]
     * @return [type] [description]
     */
    public function getRealVipProvinceCityAndArea(){
        /*$info = M('user')->alias('u')
                         ->field('q.cid,q.uid,q.cname,q.bm,q.px_abc,q.px,p.qz_province')
                         ->join('inner join qz_user_company AS c ON c.userid = u.id')
                         ->join('inner join qz_quyu AS q ON q.cid = u.cs AND q.is_open_city = 1 ' )
                         ->join('inner join qz_province AS p ON p.qz_provinceid = q.uid')
                         ->where('c.fake = 0 AND c.viptype > 0')
                         ->group('u.cs')
                         ->order('q.px')
                         ->select();*/

        $sql = "SELECT a.cid as cid,a.uid,a.cname,a.bm,a.px_abc,a.px,c.qz_province
                FROM (
                    SELECT a.cs,q.cid,q.cname,q.uid,q.type,q.px,q.bm,q.px_abc FROM qz_user a
                    inner join qz_user_company b on a.id = b.userid
                    inner join qz_quyu as q on q.cid = a.cs AND q.is_open_city = '1'
                    WHERE ( a.classid = 3 ) AND ( b.fake = 0 ) AND ( a.on = '2' ) OR (q.bm = 'lasa')
                    GROUP BY a.cs
                ) a
                inner join qz_province as c on c.qz_provinceid = a.uid
                ORDER BY a.px";

        $info  = M()->query($sql);

        $province = [];
        $city = [];

        import('Library.Org.Util.App');
        $app = new \app();

        foreach ($info as $key => $value) {
            if(empty($province[$value['uid']])){
                $province[$value['uid']] = ['id' => $value['uid'],'text' => $app->getFirstCharter($value['qz_province']) . ' ' . $value['qz_province'],'children' => [['id' => '', 'text' => '请选择市','px_abc' => 'A1', 'children' => [['id' => '', 'text' => '请选择区']]]]];
            }
            if(empty($city[$value['cid']])){
                $city[$value['cid']] = ['id' => $value['cid'],'uid' => $value['uid'],
                    'text'=>$value['cname'],
                    'px_abc' => $value['px_abc'],
                    'px' => $value['px'],
                    'children' => [['id' => '',
                        'text' => '请选择区']]];
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
            $value['children'] = multi_array_sort($value['children'],'px');
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

    //获取所有城市的vip数量
    public function getVipCityCount(){
        $map = array(
            "a.classid" =>array("IN",array(3,6)),
            "b.fake" => array("EQ",0),
            "a.on" => '2'
        );
        $result = M("user")->where($map)->alias("a")
            ->join("inner join qz_user_company b on a.id = b.userid")
            ->field("a.cs,count(if(a.on = 2,a.id,null)) as vipnum")
            ->order("cs desc")
            ->group("a.cs")
            ->select();
        return $result;
    }

}
