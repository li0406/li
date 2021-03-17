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
        $city = S("Cache:Quyu:".$bm);
        if(empty($city)){
            $map['a.bm'] = $bm;
            $map['b.type'] = array("EQ",1);

            $result = M("quyu")->alias("a")->where($map)
               ->join("inner join qz_area as b on a.cid = b.fatherid and b.type = 1")
               ->join("inner join qz_province as c on c.qz_provinceid = a.uid")
               ->field("a.cid as cid,a.cname,a.uid,a.type,a.bm,a.px,a.px_abc,a.parent_city,a.parent_city1,a.parent_city2,a.parent_city3,a.parent_city4,a.other_city,b.qz_areaid,b.qz_area,b.orders,c.qz_province,c.qz_bigpart,c.qz_bigpart_name")
               ->order("a.bm,qz_area DESC")->select();

            //导入扩展文件
            import('Library.Org.Util.App');
            $app = new \App();

            $areaKey = $app->getFirstCharter($result['0']['cname']);
            $city = array();
            $city = array(
                'cid' => $result['0']['cid'],
                'usercount' => '',
                'key' => $areaKey,
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
                'parent_city4' => $result['0']['parent_city4']
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
            $adjMap['parent_city'] = $city['cid'];
            $city['adj_city'] = M("quyu")->where($adjMap)->field("bm,cname name,cid")->order("px")->select();
            S("Cache:Quyu:".$bm,$city,86400);
        }
        return $city;
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
                "a.cname" => array("LIKE","%$name%")
            );
            $cityInfo = M("quyu")->where($map)->alias("a")
                ->join("join qz_province b on a.uid = b.qz_provinceid")
                ->field("a.*,b.qz_province as province")
                ->find();
            S("Cache:M:Q:".$name,$cityInfo,3600);
        }
        return $cityInfo;
    }

}
