<?php

namespace app\common\model\logic;

use app\common\enum\BaseStatusCodeEnum;
use app\index\enum\WorkSiteLiveStepCodeEnum;
use think\Db;
use think\facade\Request;
use app\common\model\db\WorksiteLog;
use app\common\model\db\WorkSiteInfo;
use app\common\model\db\WorksiteMedia;
use app\common\common\enum\StepCodeEnum;
use app\common\common\enum\StatusCodeEnum;

class WorkSiteInfoLogic {

    const THUMB_POSTFIX = "?imageView2/1/w/360";
    const THUMB_VIDEO_POSTFIX = "?vframe/jpg/offset/1";

    /**
     * 获取施工信息详情
     * @param $id
     * @return array
     */
    public function getInfoDetail($id){
        $worksiteInfo = new WorksiteInfo();
        $detail = $worksiteInfo->getById($id);

        $info = [];
        if (!empty($detail)) {
            $info = $detail->toArray();
            $info["media_list"] = $detail->mediaList->toArray();

            $qiniu_domin = config("app.qiniu.domain");
            $qiniu_vedio = config("app.qiniu_vedio.domain");

            foreach ($info["media_list"] as $key => $media) {
                if ($media["type"] == 2) {
                    $url_full = $qiniu_vedio . "/" . $media["url"];
                    $info["media_list"][$key]["url_full"] = $url_full;
                    $info["media_list"][$key]["url_thumb"] = $url_full . static::THUMB_VIDEO_POSTFIX;
                } else {
                    $url_full = $qiniu_domin . "/" . $media["url"];
                    $info["media_list"][$key]["url_full"] = $url_full;
                    $info["media_list"][$key]["url_thumb"] = $url_full . static::THUMB_POSTFIX;
                }
                //设置当前施工信息媒体类型(目前每条施工信息只有一种类型)
                $info["media_type"] = $media["type"];
            }
        }

        return $info;
    }


    /**
     * 获取施工信息列表
     * @param $live_id
     * @param int $step
     * @param int $page
     * @param int $limit
     * @param int $user_id
     * @return array
     */
    public function getInfoPageList($live_id, $step = 0, $page = 1, $limit = 5,$user_id = null){
        $worksiteInfo = new WorkSiteInfo();
        $count = $worksiteInfo->getPageCount($live_id, $step);

        $list = [];
        if ($count > 0) {
            $list = $worksiteInfo->getPageList($live_id, $step, ($page - 1) * $limit, $limit);
            $list = $list->toArray();

            $qiniu_domin = config("app.qiniu.domain");
            $qiniu_vedio = config("app.qiniu_vedio.domain");

            foreach ($list as $key => $item) {
                $list[$key]["created_date"] = date("Y-m-d H:i:s", $item["created_at"]);
                $list[$key]["step_name"] = WorkSiteLiveStepCodeEnum::getItem("step", $item["step"]);

                foreach ($item["media_list"] as $k => $media) {
                    if ($media["type"] == 2) {
                        $url_full = $qiniu_vedio . "/" . $media["url"];
                        $list[$key]["media_list"][$k]["url_full"] = $url_full;
                        $list[$key]["media_list"][$k]["url_thumb"] = $url_full . static::THUMB_VIDEO_POSTFIX;
                    } else {
                        $url_full = $qiniu_domin . "/" . $media["url"];
                        $list[$key]["media_list"][$k]["url_full"] = $url_full;
                        $list[$key]["media_list"][$k]["url_thumb"] = $url_full . static::THUMB_POSTFIX;
                    }
                    //设置当前施工信息媒体类型(目前每条施工信息只有一种类型)
                    $list[$key]['media_type'] = $media["type"];
                }

                //兼容小程序
                if (!empty($user_id) && $user_id == $item['user_id']) {
                    $list[$key]["self"] = 1;
                } else {
                    $list[$key]["self"] = 0;
                }
            }
        }

        return ["count" => $count, "list" => $list];
    }

    /**
     * 编辑施工信息
     * 说明：装修公司使用，需要验证装修公司权限
     * @param $input
     * @return array
     */
    public function infoEdit($input){
        $user = Request::instance()->user;

        Db::startTrans();
        try {
            $worksiteInfo = new WorksiteInfo();
            $detail = $worksiteInfo->getById($input["id"]);
            if (empty($detail)) {
                throw new \Exception(BaseStatusCodeEnum::CODE_4000001, 4000001);
            }

            $data = array(
                "step" => $input["step"],
                "content" => $input["content"],
                "updated_at" => time()
            );

            $result = $worksiteInfo->where("id", $input["id"])->data($data)->update();
            if (empty($result)) {
                throw new \Exception(BaseStatusCodeEnum::CODE_5000002, 5000002);
            }

            // 处理附件（图片或者视频）
            $worksiteMedia = new WorksiteMedia();
            $worksiteMedia->setInfoMedia($input["id"], $input["media_urls"], $input["media_type"]);

            // 添加施工信息编辑日志
            WorksiteLog::addLog("edit", $input["id"], $user["user_id"], "business", $input);

            Db::commit();
            $code = 0;
            $msg = BaseStatusCodeEnum::CODE_0;
        } catch (\Exception $e) {
            Db::rollback();
            $code = $e->getCode();
            $msg = $e->getMessage();
        }

        return sys_response($code, $msg);
    }

    /**
     * 施工信息删除
     * 说明：装修公司使用，需要验证装修公司权限
     * @param $id
     * @return array
     */
    public function infoDelete($id){
        $user = Request::instance()->user;

        try {
            $worksiteInfo = new WorkSiteInfo();
            $detail = $worksiteInfo->getById($id);
            if (empty($detail)) {
                throw new \Exception("施工信息不存在", 4000001);
            }

            // 删除施工信息
            $result = $worksiteInfo->where("id", $id)->delete();
            if (empty($result)) {
                throw new \Exception(BaseStatusCodeEnum::CODE_5000002, 5000002);
            }

            // 删除施工信息多媒体数据
            $worksiteMedia = new WorksiteMedia();
            $worksiteMedia->deleteMediaByInfoId($id);

            // 添加施工信息删除日志
            WorksiteLog::addLog("delete", $id, $user["user_id"], "business");

            $code = 0;
            $msg = BaseStatusCodeEnum::CODE_0;
        } catch (\Exception $e) {
            $code = $e->getCode();
            $msg = $e->getMessage();
        }

        return sys_response($code, $msg);
    }

    /**
     * 获取施工阶段
     * @param $live_id
     * @return array|string|\think\Collection
     */
    public function getLiveStepList($live_id){
        $worksiteInfo = new WorkSiteInfo();
        $infoList = $worksiteInfo->getLiveStep($live_id);
        $steps = array_column($infoList->toArray(), "step");
        $step_list = WorkSiteLiveStepCodeEnum::getListByIds("step", $steps);
        return array_reverse($step_list);
    }


}
