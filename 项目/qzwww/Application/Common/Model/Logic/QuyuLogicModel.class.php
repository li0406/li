<?php

namespace Common\Model\Logic;

use Common\Model\QuyuModel;

class QuyuLogicModel
{
    public function getAllProvince()
    {
        $model = new QuyuModel();
        return $model->getAllProvinceList();
    }

    public function getProvinceCityList($province_id)
    {
        $model = new QuyuModel();
        return $model->getProvinceCityList($province_id);
    }

    //根据城市id获取城市信息
    public function getCityInfoByCid($cid)
    {
        $model = new QuyuModel();
        if(!$cid){
            return array();
        }
        $return =  $model->getCityInfoByCid($cid);
        return $return ? $return : array();
    }

    //获取所有城市
    public function getAllCity()
    {
        $model = new QuyuModel();
        return $model->getAllCity();
    }

    /**
     * 获取热门城市
     * @return [type] [description]
     */
    public function getHotCitys(){
        $citys = D("Common/Quyu")->getHotCityList();
        if(count($citys) > 0){
            return $citys;
        }
        return null;
    }

    /**
     * 获取所有城市
     * @return [type] [description]
     */
    public function getTopCitys($flag = false){
        $citys = D("Common/Quyu")->getAllProvinceAndCitys($flag);
        $city = [];
        foreach ($citys as $k=>$v){
            if(in_array($v['pname'],['A','B','C','D','E','F','G','H'])){
                foreach ($v['child'] as $kk=>$vv){
                    $city['one'][] = $vv;
                }
            }elseif (in_array($v['pname'],['I','J','K','L','M','N','O','P','Q','R','S','T'])){
                foreach ($v['child'] as $kk=>$vv){
                    $city['two'][] = $vv;
                }
            }elseif (in_array($v['pname'],['U','V','W','X','Y','Z'])){
                foreach ($v['child'] as $kk=>$vv){
                    $city['three'][] = $vv;
                }
            }
            unset($citys[$k]);
        }

        return $city;
    }

    public function getAllOpenCitys()
    {
        $model = new QuyuModel();
        $list = $model->getAllOpenCitys();

        //导入扩展文件
        import('Library.Org.Util.App');
        $app = new \App();

        foreach ($list as $key => $val) {
            $tempKey = $app->getFirstCharter($val["cname"]);
            $list[$key]["cname_u"] = $tempKey . ' ' . $val['cname'];
        }

        return $list;
    }
}