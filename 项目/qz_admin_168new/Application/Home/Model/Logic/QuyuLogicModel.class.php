<?php

namespace Home\Model\Logic;

use Home\Model\Db\AdminquyuModel;
use Home\Model\QuyuModel;

class QuyuLogicModel
{
    private $little = array(
        0 => "A类",
        1 => "B类",
        2 => "C类",
        3 => "D类",
        4 => "S1",
        5 => "S2"
    );

    //获取级别
    public function getlittle()
    {
        return $this->little;
    }


    public function addQuyu($data)
    {
        /*S—获取该城市坐标属性*/
        $options = array(
            'http' => array('timeout' => 3)
        );
        $context = stream_context_create($options);
        //百度地图AK,目前未做成配置项
        $api = 'http://api.map.baidu.com/geocoder/v2/?address=' . $data['cname'] . '&output=json&ak=8Ee3c4jzYCv3djogwCBqaD48';
        $content = file_get_contents($api, false, $context);
        $res = json_decode($content, true);
        $data['lng'] = $res['result']['location']['lng'];
        $data['lat'] = $res['result']['location']['lat'];
        /*E—获取该城市坐标属性*/
        $temp = D('Home/Db/Adminquyu')->getQuyu('', 'xh DESC');
        $data['xh'] = $temp['0']['xh'] + 1;
        $data['time_add'] = time();
        //添加城市信息
        return D('Home/Db/Adminquyu')->addQuyu($data);
    }

    public function editProvince($province, $uid)
    {
        return D('Home/db/Adminquyu')->editProvince($province, $uid);
    }

    /**
     * 获取相邻城市
     *
     * @param $adjacent_cid
     * @return mixed
     */
    public function getAdjacentWithArea($adjacent_cid)
    {
        $map['cid'] = ['in', $adjacent_cid];
        $quyuModel = new AdminquyuModel();
        $citys = $quyuModel->getCitys($map,'field(cid,'.$adjacent_cid.')');
        $areas = $this->getArea($adjacent_cid);
        foreach ($citys as $k => $city) {
            $citys[$k]['area'] = [];
            foreach ($areas as $k1=>$area) {
                if ($city['cid'] == $area['fatherid']) {
                    $citys[$k]['area'][] = $area;
                }
            }
        }
        return $citys;
    }

    /**
     * 获取地区
     *
     * @param $cids
     * @return mixed
     */
    public function getArea($cids)
    {
        $map['fatherid'] = ['in',$cids];
        $quyuModel = new AdminquyuModel();
        return $quyuModel->getAreaList($map);
    }

    public function getUponCity($cid)
    {
        //获取当前城市的相邻城市
        $quyuDb = new AdminquyuModel();
        $field = 'cid,cname,px_abc,parent_city,parent_city1,parent_city2,parent_city3,parent_city4,other_city,concat_ws(",",parent_city,parent_city1,parent_city2,parent_city3,parent_city4,other_city) upon_city';
        $info = $quyuDb->getCitys(['cid' => ['eq', $cid]], '', $field);
        $info = $info[0];
        if (!empty($info['upon_city'])) {
            $uponCity = explode(',', $info['upon_city']);
            if (count(array_filter($uponCity)) > 0) {
                //查询当前城市的相邻城市 他们的相邻城市
                $uponCityInfo = $quyuDb->getCitys(['cid' => ['in', array_filter($uponCity)]], 'xh', 'cid,cname,px_abc,parent_city,parent_city1,parent_city2,parent_city3,parent_city4,other_city');
                foreach ($uponCityInfo as $k => $v) {
                    $uponCityInfo[$v['cid']] = $v;
                    unset($uponCityInfo[$k]);
                }
                //获取首字母
                $info['key'] = strtoupper(mb_substr( $info["px_abc"],0,1,"utf-8"));
                $returnData = [
                    $info
                ];
                //根据当前城市的相邻城市 循环数据
                foreach ($uponCity as $k => $v) {
                    if (isset($uponCityInfo[$v])) {
                        $uponCityInfo[$v]['key'] = strtoupper(mb_substr( $uponCityInfo[$v]["px_abc"],0,1,"utf-8"));
                        array_push($returnData, $uponCityInfo[$v]);
                    }
                }
                return $returnData;
            }
        } else {
            return [$info];
        }
    }

    public function saveUponCity($data)
    {
        if (!empty($data['cs'])) {
            $data['parent_city'] = $_POST['parent_city'];
            $data['parent_city1'] = $_POST['parent_city1'];
            $data['parent_city2'] = $_POST['parent_city2'];
            $data['parent_city3'] = $_POST['parent_city3'];
            $data['parent_city4'] = $_POST['parent_city4'];
            $data['other_city'] = $_POST['other_city'];
            $quyuDb = new AdminquyuModel();
            $quyuDb->editQuyu($data['cs'], $data);
            //添加日志
            $quyuDb->addLog(['id' => $data['cs']], $data);
            //清除缓存
            S('Cache:Area', null);
        } else {
            return [];
        }
    }

    public function getQuyuByCid($cid)
    {
        $cid = empty($cid) ? '' : $cid;
        $map['cid'] = ['in', $cid];
        $map['cname'] = ['neq', '总站'];
        $quyuDb = new AdminquyuModel();
        return $quyuDb->getCitys($map,'px_abc asc,cid asc','cid,cname,upper(left(px_abc,1)) AS px_abc');
    }

    public function getSimpleCitys()
    {
        $ret = D("Quyu")->getAllSimpleQuyuOnly();
        return $ret;
    }

    public function getAllCity()
    {
        $model = new QuyuModel();
        return $model->getAllQuyuOnly();
    }

    //通过一个城市数组获取它旗下子城市的开通城市数组
    public function getSonOpenCity($cs)
    {
        if(empty($cs)){
            return [];
        }
        $search_cs = explode(',',$cs);//原数据用来和查询数据合并返回
        foreach ($search_cs as $k=>$v){
            //如果城市是苏州(320500) 无锡(320200)则跳过处理 因为这两个城市比较特殊 旗下子城市需要手动添加
            if(in_array($v,[320500,320200])){
                unset($search_cs[$k]);
            }
        }
        $quyuDb = new AdminquyuModel();
        //获取当前城市的子城市
        $child = $quyuDb->getAreaList(['fatherid'=>['in',$search_cs]]);
        if (count($child) > 0) {
            //查询quyu表是否有开通的子城市
            $city = $quyuDb->getCitys(['cid'=>['in',array_column($child,'areaid')]]);
            if(count($city) > 0){
                $city = implode(',',array_column($city,'cid'));
                $cs .= ','.$city;
            }
        }
        return $cs;
    }

    public function getCityInfoByCid($cid) {
        $quyuModel = new QuyuModel();
        return $quyuModel->getCityInfoByCid($cid);
    }

    public function getCityInfoByName($city_name)
    {
        if(count($city_name) == 0){
            return [];
        }
        $quyuDb = new QuyuModel();
        return $quyuDb->getCityInfoByName($city_name);
    }
}
