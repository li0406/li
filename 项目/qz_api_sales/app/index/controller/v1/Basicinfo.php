<?php

/**
 * 基础信息设置控制器
 * @Author: liuyupeng
 * @Email: lypeng9205@163.com
 * @Date:   2019-07-23 16:26:27
 */

namespace app\index\controller\v1;

use think\Request;
use think\Exception;
use think\Controller;

use app\common\enum\BaseStatusCodeEnum;
use app\common\model\logic\AdminuserLogic;
use app\common\model\logic\AreaLogic;
use app\common\model\logic\CityLogic;
use app\common\model\logic\CompanyMapmarkerLogic;
use app\common\model\logic\QuyuLogic;
use app\common\model\logic\CompanyLogic;

use app\index\validate\UserCompany as UserCompanyValidate;
use app\common\model\service\SmsService;

class Basicinfo extends Controller {
        
    /**
     * 会员公司设置列表
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function companyList(Request $request, CompanyLogic $companyLogic){
        $options = $request->param();
        $page = $request->param("page", 1, "intval");
        $page_size = $request->param("page_size", 20, "intval");
        $data = $companyLogic->getPageList($options, $page, $page_size);

        $response = sys_response(0, "", [
            "list" => $data["list"],
            "page" => sys_paginate($data["count"], $page_size, $page),
        ]);

        return json($response);
    }

    /**
     * 会员公司详情
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function companyDetail(Request $request, CompanyLogic $companyLogic){
        $id = $request->param("id");
        if (empty($id)) {
            return json(sys_response(4000002));
        }

        // 获取会员公司详情
        $detail = $companyLogic->getCompanyDetail($id);
        if (empty($detail)) {
            return json(sys_response(4000001, "会员公司不存在"));
        }

        $deliver_area = $companyLogic->getCompanyDeliverArea($id, $detail["deliver_city"]);
        unset($detail["deliver_city"]);

        // 返回Response
        $response = sys_response(0, "", [
            "detail" => $detail,
            "deliver_area" => $deliver_area
        ]);

        return json($response);
    }

    /**
     * 会员公司编辑
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function companyEdit(Request $request, CompanyLogic $companyLogic){
        $data = $request->param();

        // 表单数据验证
        $userValidate = new UserCompanyValidate();
        if ($userValidate->scene('edit')->check($data) == false) {
            return json(sys_response(4000002, $userValidate->getError()));
        }

        // 对立公司ID验证
        $other_id = $request->param("other_id");
        $result = $userValidate->otherIdValidate($data["id"], $other_id);
        if ($result !== true) {
            return json(sys_response(4000002, $result));
        }

        $detail = $companyLogic->getCompanyDetail($data["id"]);
        if (empty($detail)) {
            return json(sys_response(4000001, "会员公司不存在"));
        }

        // 验证轮单单价
        if ($detail["cooperate_mode"] == 2 && (empty($data['gz_price']) && empty($data['jb_price']) && empty($data['mjmin_price']) && empty($data['mjmax_price']))) {
            return json(sys_response(4000001, "请填写轮单单价"));
        }

        // 验证PNP配置
        $data["pnp_on"] = check_variate($data["pnp_on"], 2);
        if (!empty($data["pnp_on"]) && $data["pnp_on"] == 1) {
            if (empty($data["pnp_days"])) {
                return json(sys_response(4000001, "请填写号码保护天数"));
            } else if (empty($data["pnp_tel_num"])) {
                return json(sys_response(4000001, "请填写号码保护数量"));
            }
        }

        // 编辑会员公司信息
        $result = $companyLogic->editUserCompanyInfo($data["id"], $data, $detail);
        if ($result !== true) {
            return json(sys_response(5000002, $result));
        }

        // pnp开通后发送短信通知
        if ($detail["pnp_on"] == 2 && $data["pnp_on"] == 1) {
            if (!empty($detail["tel_safe"])) {
                $smsService = new SmsService();
                $smsService->sendSms("202012181055", $detail["tel_safe"]);
            }
        }

        return json(sys_response(0));
    }

    // 会员公司批量开启PNP号码保护
    public function companyPnpOn(Request $request, CompanyLogic $companyLogic){
        $city_id = $request->post("city_id");
        if (empty($city_id)) {
            return json(sys_response(4000002, BaseStatusCodeEnum::CODE_0));
        }

        $ret = $companyLogic->setCityCompanyPnpOn($city_id);
        if ($ret === false) {
            return json(sys_response(1000001, BaseStatusCodeEnum::CODE_1000001));
        }

        return json(sys_response(0));
    }

    // 会员公司批量关闭PNP号码保护
    public function companyPnpOff(Request $request, CompanyLogic $companyLogic){
        $city_id = $request->post("city_id");
        if (empty($city_id)) {
            return json(sys_response(4000002, BaseStatusCodeEnum::CODE_0));
        }

        $ret = $companyLogic->setCityCompanyPnpOff($city_id);
        if ($ret === false) {
            return json(sys_response(1000001, BaseStatusCodeEnum::CODE_1000001));
        }

        return json(sys_response(0));
    }

    /**
     * 城市设置（获取页面数据）
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function salecityInfo(Request $request){
        $quyuLogic = new QuyuLogic();
        $data = $quyuLogic->getSalecityAll();
        return json(sys_response(0, "", $data));
    }

    /**
     * 城市设置（设置）
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function salecityEdit(Request $request){
        $sale_citys = $request->param("sale_citys");
        if (empty($sale_citys)) {
            return json(sys_response(4000002, "请先选择分配城市"));
        }

        $quyuLogic = new QuyuLogic();
        $result = $quyuLogic->setRelationSaleCitys($sale_citys);
        if ($result !== true) {
            return json(sys_response(5000002, $result));
        }

        return json(sys_response(0));
    }



    public function order_map(Request $request)
    {
        $cityLogic = new CityLogic();
        $areaLogic = new AreaLogic();

        $userinfo = $request->param("user");

        // 非超级管理员获取当前用户管辖城市
        $city = $cityLogic->getCitys();

        //添加城市的首字母
        foreach ($city as $k => $value) {
            $key = substr(strtoupper($value["px_abc"]),0,1);
            $city[$k]["name"] = $key." ".$value["cname"];
        }

        $cs = $city[0];                 // 当前城市
        if ($cityid = input("get.cityid")) {
            foreach ($city as $row) if ($row['cid'] == $cityid) {
                $cs = $row;
                break;
            }
        }

        $ids[] = $cs['cid'];

        // 获取相邻城市
        $result = $areaLogic->getCityById($cs["cid"]);

        $ids[] = $result["parent_city"];
        $ids[] = $result["parent_city1"];
        $ids[] = $result["parent_city2"];
        $ids[] = $result["parent_city3"];
        $ids[] = $result["parent_city4"];
        $ids[] = $result["other_city"];
        $ids = array_filter($ids);

        // 标记的列表
        $companyMapmarkerLogic = new CompanyMapmarkerLogic();
        $marks = $companyMapmarkerLogic->getMapMarkers($ids);


        //管理员等级
        $userType = array('1'=>'超管','2'=>'客服','3'=>'销售','30'=>'客服','5'=>'复制','6'=>'总编','7'=>'督导','8'=>'人事','9'=>'助理','21'=>'客服审核','22'=>'回访','23'=>'质检'
        );

        $adminUserLogic = new AdminuserLogic();
        foreach ($marks as $k => $v) {
            $tempU = $adminUserLogic->getAdminUserByUser($v["last_modified_by"]);
            if ($tempU) {
                $marks[$k]['user_type'] = isset($userType[$tempU['uid']]) ? $userType[$tempU['uid']] : null;
            } else {
                $marks[$k]['user_type'] = null;
            }
        }

        //地图辅助名称重定义，避免cname搜索不到该地区
        if (!empty($cs['map_name'])) {
            $cs['cname'] = $cs['map_name'];
        }
        $result = [
            "marks" => $marks ? $marks : [],
            "cs" => $cs ? $cs : [],
            "title" => '装修公司分布 - '. $cs['cname']
        ];
        return json(sys_response(0, BaseStatusCodeEnum::CODE_0, $result));
    }

    public function mark(Request $request)
    {
        $logic = new CompanyMapmarkerLogic();
        $userinfo = $request->param("user");
        $param = input("post.");
        try {
            $returnId = $logic->markAction($param, $userinfo);

            $code = 0;
            $msg = BaseStatusCodeEnum::CODE_0;
            $data["id"] = $returnId;

        } catch (Exception $e) {
            $code = $e->getCode();
            $msg = $e->getMessage();
            $data = null;
        }
        return json(sys_response($code, $msg, $data));
    }

    /**
     * 删除地图上的标记
     * @return \think\response\Json
     */
    public function delMark(Request $request)
    {
        $logic = new CompanyMapmarkerLogic();
        $delId = input("post.id");
        try {

            $logic->delMark($delId);

            $code = 0;
            $msg = BaseStatusCodeEnum::CODE_0;
            $data = null;

        } catch (Exception $e) {
            $code = $e->getCode();
            $msg = $e->getMessage();
            $data = null;
        }
        return json(sys_response($code, $msg, $data));
    }


}