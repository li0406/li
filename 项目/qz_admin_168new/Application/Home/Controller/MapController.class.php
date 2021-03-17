<?php


namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

/**
*
*/
class MapController extends HomeBaseController
{

    public function mapmarker()
    {
        if (I("get.id") !== "") {
            //查询订单信息
            $order = D('Home/Orders');
            $info = $order->findOrderInfo(I("get.id"));
            $this->assign("info",$info);
        }

        //获取城市
        // $citys = getAdminCityIds(true,true,true);
        // $citys = getCityListByCityIds($citys);
        //获取城市信息
        $api = A("Home/Api");
        $citys = $api->getAllCityInfo();

        //百度地图城市辅助重命名
        foreach ($citys as $key => $value) {
            if (!empty($value['map_name'])) {
                $citys[$key]['oldname'] = $value['map_name'];
            }
        }

        $this->assign("citys",$citys);
        if (!isset($info)) {
            $city = $citys[0]["oldname"];
            $cityId = $citys[0]["cid"];
        } else {
            foreach ($citys as $key => $value) {
                if ($value["cid"] == $info["cs"]) {
                    $city = $value["oldname"];
                    break;
                }
            }
            $cityId = $info["cs"];
        }

        if (I("get.city") !== "") {
            foreach ($citys as $key => $value) {
                if ($value["cid"] == I("get.city")) {
                    $city = $value["oldname"];
                    break;
                }
            }
            $cityId = I("get.city");
        }
        $ids[] = $cityId;

        //获取相邻城市
        // $result = D("Quyu")->getCityInfoById($cityId);
        $result = D("OrderCityRelation")->getRelationCity($cityId);

        foreach ($result as $key => $value) {
            $ids[] = $value["cid"];
        }

        $ids = array_filter($ids);

        //获取该城市的标注装修公司
        $companys = D("CompanyMapmarker")->getCityCompanys($ids);


        $this->assign("companys",$companys);
        $this->assign("json_companys",$companys);
        $this->assign("city",$city);
        $this->assign("cityId",$cityId);
        //如果是客服不显示销售报备信息
        $this->display();
    }

    /**
     * 删除锚点
     * @return [type] [description]
     */
    public function delmarker()
    {
        if ($_POST) {
            //删除标注
            $i = D("CompanyMapmarker")->delMark(I("post.id"));
            if ($i !== false) {
                $this->ajaxReturn(array("code"=>200));
            }
            $this->ajaxReturn(array("code"=>404,"errmsg" => "删除失败！"));
        }
    }

    /**
     * 添加标注
     * @return [type] [description]
     */
    public function addmarker()
    {
        if ($_POST) {
            //查询标记信息
            $mark = D("CompanyMapmarker")->findMark(I("post.lng"),I("post.lat"));

            //添加标注
            $data = array(
                "cityid" => I("post.cityid"),
                "lng" => I("post.lng"),
                "lat" => I("post.lat"),
                "address" => I("post.addr"),
                "companies" => I("post.com"),
                "last_modified_by" => session("uc_userinfo.name"),
                "last_modified_time" => time()
            );
            if (count($mark) == 0) {
                $i = D("CompanyMapmarker")->addMark($data);
            } else {
                $i = D("CompanyMapmarker")->editMark($mark["id"],$data);
            }

            if ($i !== false) {
                $this->ajaxReturn(array("code"=>200));
            }
            $this->ajaxReturn(array("code"=>404,"errmsg" => "添加失败！"));
        }
    }
}