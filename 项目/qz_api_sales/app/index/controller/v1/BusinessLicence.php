<?php

/**
 * 营业执照审核控制器
 * @Author: liuyupeng
 * @Email: lypeng9205@163.com
 * @Date:   2019-07-16 09:49:27
 */

namespace app\index\controller\v1;

use think\Request;
use think\Controller;

use app\common\model\logic\SalesSettingLogic;
use app\common\model\logic\BusinessLicenceLogic;

class BusinessLicence extends Controller {
        
    /**
     * 城市负责人
     * @return [type] [description]
     */
    public function citymanager(Request $request){
        $user_keyword = $request->param("user_keyword");
        $list = model("db.adminuser")->getCityManagersByKeyword($user_keyword);
        return json(sys_response(0, "", ["list" => $list]));
    }

    /**
     * 营业执照审核列表
     * 待审核、审核确认、审核记录
     * @return [type] [description]
     */
    public function blList(Request $request, BusinessLicenceLogic $busLicLogic){
        $actfrom = $request->param("actfrom");
        $page = $request->param("page", 1, "intval");
        $page_size = $request->param("page_size", 20, "intval");

        $data = $busLicLogic->getBlPageList($actfrom, $request->param(), $page, $page_size);

        $response = sys_response(0, "", [
            "list" => $data["list"],
            "page" => sys_paginate($data["count"], $page_size, $page)
        ]);

        return json($response);
    }

    /**
     * 营业执照上传列表
     * @return [type] [description]
     */
    public function uploadList(Request $request, BusinessLicenceLogic $busLicLogic){
        $page = $request->param("page", 1, "intval");
        $page_size = $request->param("page_size", 20, "intval");

        $user_keyword = $request->param("user_keyword");
        if (empty($user_keyword)) {
            return json(sys_response(4000002, "请先输入会员信息"));
        }

        $data = $busLicLogic->getSaleCityList($user_keyword, $page, $page_size);

        $response = sys_response(0, "", [
            "list" => $data["list"],
            "page" => sys_paginate($data["count"], $page_size, $page)
        ]);

        return json($response);
    }

    /**
     * 上传
     * @return [type] [description]
     */
    public function uploadFile(Request $request, BusinessLicenceLogic $busLicLogic){
        $company_id = $request->param("company_id");
        $img1 = $request->param("img1");
        $img3 = $request->param("img3");
        $img4 = $request->param("img4");

        if (empty($company_id)) {
            return json(sys_response(4000002));
        }

        if (empty($img1) && empty($img3) && empty($img4)) {
            return json(sys_response(4000002, "请先上传图片"));
        }

        $result = $busLicLogic->saveUpFile($company_id, $img1, $img3, $img4);
        if (empty($result)) {
            return json(sys_response(5000002, "上传失败"));
        }

        return json(sys_response(0));
    }

    /**
     * 清空
     * @return [type] [description]
     */
    public function uploadClean(Request $request, BusinessLicenceLogic $busLicLogic){
        $company_id = $request->param("company_id");
        if (empty($company_id)) {
            return json(sys_response(4000002));
        }

        $response = $busLicLogic->examRecordClean($company_id);
        return json($response);
    }

    /**
     * 初审
     * @return [type] [description]
     */
    public function examFirst(Request $request, BusinessLicenceLogic $busLicLogic){
        $company_id = $request->param("company_id");
        $type = $request->param("type");
        $state = $request->param("state");
        $remark = $request->param("remark");

        if (empty($company_id) || empty($type) || empty($state)) {
            return json(sys_response(4000002));
        }

        $response = $busLicLogic->examRecordFirst($company_id, $type, $state, $remark);
        return json($response);
    }

    /**
     * 终审
     * @return [type] [description]
     */
    public function examFinal(Request $request, BusinessLicenceLogic $busLicLogic){
        $company_id = $request->param("company_id");
        $type = $request->param("type");

        if (empty($company_id) || empty($type)) {
            return json(sys_response(4000002));
        }

        $response = $busLicLogic->examRecordFinal($company_id, $type);
        return json($response);
    }

    /**
     * 重审/撤回
     * @return [type] [description]
     */
    public function examReset(Request $request, BusinessLicenceLogic $busLicLogic){
        $company_id = $request->param("company_id");
        $type = $request->param("type");

        if (empty($company_id) || empty($type)) {
            return json(sys_response(4000002));
        }

        $response = $busLicLogic->examRecordReset($company_id, $type);
        return json($response);
    }

    /**
     * 营业执照数据统计
     *
     * @return array
     */
    public function statistics(BusinessLicenceLogic $busLicLogic)
    {
        $get = $this->request->get();
        $p = $this->request->get('page', 1, 'intval');
        $psize = $this->request->get('page_count', 20, 'intval');
        return $busLicLogic->licenceStatistics($get, $p, $psize);
    }
}