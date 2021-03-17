<?php

/**
 * 工地直播
 */

namespace app\index\controller\v1;

use think\Request;
use think\Controller;
use think\facade\Cache;

use app\common\model\logic\WorkSiteInfoLogic;
use app\common\model\logic\WorkSiteLiveLogic;
use app\common\model\service\WechatService;
use app\index\validate\WorksiteInfoValidate;

use app\common\enum\CacheKeyCodeEnum;
use app\common\enum\BaseStatusCodeEnum;
use app\index\enum\WorkSiteLiveStepCodeEnum;


Class WorkSiteLive extends Controller
{
    public function stepList()
    {
        return sys_response(0, '', ['info' => WorkSiteLiveStepCodeEnum::getList()]);
    }

    public function liveList(Request $request, WorkSiteLiveLogic $workSiteLiveLogic){
        $input = array(
            "cs" => $request->param("cs"),
            "qx" => $request->param("qx"),
            "company" => $request->param("company"),//装修公司
            "proprietor" => $request->param("proprietor"),//业主
            "type" => $request->param("type"),//订单类型
            "start_time" => $request->param("start_time"),//签约开始时间
            "end_time" => $request->param("end_time"),//签约结束时间
            "code" => $request->param("code"),//工地
            "step" => $request->param("step"),//施工阶段
        );

        $page = sys_page_format($request->page, 1);
        $limit = sys_page_format($request->limit, 10);

        $data = $workSiteLiveLogic->getLiveList($input, $page, $limit);

        $response = sys_response(0, "", [
            "list" => $data["list"],
            "page" => sys_paginate($data["count"], $limit, $page)
        ]);

        return json($response);
    }

    /**
     * 工地直播详情页
     * @param Request $request
     * @param WorkSiteLiveLogic $workSiteLiveLogic
     * @return \think\response\Json
     */
    public function liveDetail(Request $request, WorkSiteLiveLogic $workSiteLiveLogic){
        $id = $request->param("id");
        if (empty($id)) {
            return json(sys_response(4000002));
        }

        // 获取详情
        $info = $workSiteLiveLogic->getDetail($id);
        if (empty($info)) {
            return json(sys_response(4000001, "工地直播不存在"));
        }

        $worksiteInfoLogic = new WorkSiteInfoLogic();
        $step_list = $worksiteInfoLogic->getLiveStepList($info["id"]);

        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, [
            "info" => $info,
            "step_list" => $step_list
        ]);

        return json($response);
    }

    /**
     * 施工信息列表
     * @param Request $request
     * @param WorksiteInfoLogic $worksiteInfoLogic
     * @return \think\response\Json
     */
    public function infoList(Request $request, WorkSiteInfoLogic $infoLogic){
        $live_id = $request->param("live_id");
        $step = $request->param("step");
        $page = sys_page_format($request->page, 1);
        $limit = sys_page_format($request->limit, 5);

        $data = $infoLogic->getInfoPageList($live_id, $step, $page, $limit);

        $response = sys_response(0, "", [
            "list" => $data["list"],
            "page" => sys_paginate($data["count"], $limit, $page)
        ]);

        return json($response);
    }


    /**
     * 施工信息详情
     * @param Request $request
     * @param WorksiteInfoLogic $worksiteInfoLogic
     * @return \think\response\Json
     */
    public function infoDetail(Request $request, WorkSiteInfoLogic $infoLogic){
        $id = $request->param("id");
        if (empty($id)) {
            return json(sys_response(4000002));
        }

        // 获取详情
        $info = $infoLogic->getInfoDetail($id);
        if (empty($info)) {
            $response = sys_response(4000001, "施工详情不存在");
        } else {
            $step_list = WorkSiteLiveStepCodeEnum::getList("step", true);
            $response = sys_response(0, BaseStatusCodeEnum::CODE_0, [
                "info" => $info,
                "step_list" => $step_list
            ]);
        }

        return json($response);
    }


    /**
     * 施工信息编辑
     * @param Request $request
     * @param WorksiteInfoLogic $worksiteInfoLogic
     * @return \think\response\Json
     */
    public function infoEdit(Request $request, WorkSiteInfoLogic $infoLogic){
        $input = array(
            "id" => $request->param("id"),
            "step" => $request->param("step"),
            "content" => $request->param("content"),
            "media_urls" => $request->param("media_urls"),
            "media_type" => $request->param("media_type", 1)
        );

        $infoValidate = new WorksiteInfoValidate();
        if ($infoValidate->scene("edit")->check($input) == false) {
            return json(sys_response(4000002, $infoValidate->getError()));
        }

        $response = $infoLogic->infoEdit($input);
        return json($response);
    }

    /**
     * 施工信息删除
     * @param Request $request
     * @param WorksiteInfoLogic $worksiteInfoLogic
     */
    public function infoDelete(Request $request, WorkSiteInfoLogic $infoLogic){
        $id = $request->param("id");
        if (empty($id)) {
            return json(sys_response(4000002));
        }

        $response = $infoLogic->infoDelete($id);
        return json($response);
    }

    public function getQrcode(Request $request){
        $live_code = $request->param("live_code");

        //添加缓存
        $cache_key = sprintf(CacheKeyCodeEnum::WORKSITE_QRCODE, strtoupper($live_code));
        $file_content = Cache::get($cache_key);
        if (!$file_content) {
            $wechatService = new WechatService();
            $params = [
                "scene" => sprintf("live_code=%s", $live_code),
                "page" => "pages/home/home",
                "width" => 720
            ];
            if (config("APP_ENV") == "dev") {
                unset($params["page"]);
            }
            $result = $wechatService->excute()->getUnlimited($params);

            if (!empty($result["errcode"])) {
                return json(sys_response($result["errcode"], $result["errmsg"]));
            }

            $file_content = chunk_split(base64_encode($result)); // base64编码
            Cache::set($cache_key,$file_content,86400);
        }
        $response = sys_response(0, "", $file_content);
        return json($response);
    }
}