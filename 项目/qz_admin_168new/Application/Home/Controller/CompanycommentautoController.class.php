<?php

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\CommentAutoLogicModel;

class CompanycommentautoController extends HomeBaseController
{

    /**
     * 评论模板列表
     * @Author   lovenLiu
     * @DateTime 2019-04-29T09:45:32+0800
     */
    public function templatelist()
    {
        $query = [
            "stage" => I("stage"),
            'on_status' => I("on_status"),
            'use_status' => I("use_status"),
        ];

        $page = I("p", 1);
        $limit = I("limit", 20);
        $data = CommentAutoLogicModel::getTemplateList($query, $page, $limit);

        $stage_list = D("CommentAutoTemplate")->stageList;
        foreach ($data["list"] as $key => &$item) {
            $stage = $item["stage"];
            $item["stage_name"] = $stage_list[$stage];
        }

        $pageshow = "";
        if ($data["count"] > $limit) {
            import('Library.Org.Util.Page');
            $page = new \Page($data["count"], $limit);
            $pageshow = $page->show();
        }

        $this->assign('pageshow', $pageshow);
        $this->assign('template_list', $data["list"]);

        $this->assign('query', $query);
        $this->assign('query_str', http_build_query($query));
        $this->assign('stage_list', $stage_list);
        $this->display();
    }

    /**
     * 评论模板编辑
     * @Author   lovenLiu
     * @DateTime 2019-04-29T09:47:43+0800
     */
    public function templatesave($id = 0)
    {

        if (IS_AJAX) {
            $data = [
                "stage" => I("stage"),
                "content" => I("content"),
                "on_status" => I("on_status", 1),
                "updated_at" => time(),
            ];

            if (empty($data["stage"])) {
                $response = sys_rt("请先选择装修阶段", 2);
            } else if (empty($data["content"])) {
                $response = sys_rt("请先填写模板内容", 2);
            } else {
                if (!empty($id)) {
                    $result = D("CommentAutoTemplate")->where(["id" => ":id"])->bind(":id", $id)->save($data);
                } else {
                    $data["created_at"] = time();
                    $result = D("CommentAutoTemplate")->add($data);
                }

                if (!empty($result)) {
                    $response = sys_rt("编辑成功", 1);
                } else {
                    $response = sys_rt("编辑失败", 2);
                }
            }

            $this->ajaxReturn($response);
        }

        $info = [];
        if (!empty($id)) {
            $info = D("CommentAutoTemplate")->find($id);
        }

        $stage_list = D("CommentAutoTemplate")->stageList;

        $this->assign("info", $info);
        $this->assign("stage_list", $stage_list);
        $this->display();
    }

    /**
     * 评论模板删除（逻辑删除）
     * @Author   lovenLiu
     * @DateTime 2019-04-30T09:18:32+0800
     */
    public function templatedel()
    {
        if (IS_AJAX) {
            $id = I("post.id");
            $result = D("CommentAutoTemplate")->where(["id" => ":id"])->bind(":id", $id)->save([
                "updated_at" => time(),
                "enabled" => 2,
            ]);

            if (!empty($result)) {
                $response = sys_rt("删除成功", 1);
            } else {
                $response = sys_rt("删除失败", 2);
            }

            $this->ajaxReturn($response);
        }
    }

    /**
     * 自动发布评论设置
     * @Author   lovenLiu
     * @DateTime 2019-04-29T09:47:53+0800
     */
    public function autosetting()
    {
        if (IS_AJAX) {
            $data = [
                "comment_on" => I("comment_on", 2),
                "citys" => I("citys"),
                "company_type" => I("company_type"),
                "publish_number" => I("publish_number"),
                "publish_begin" => I("publish_begin"),
                "publish_end" => I("publish_end"),
                // "publish_interval" => I("publish_interval"),
            ];

            $response = sys_rt('验证成功', 1);
            if (empty($data['company_type'])) {
                $response = sys_rt('请先选择应用对象', 2);
            } else if (empty($data['publish_number'])) {
                $response = sys_rt('请先填写发布数量', 2);
            } else if (empty($data['publish_begin']) || empty($data['publish_end'])) {
                $response = sys_rt('请先填写发布时间段', 2);
            } else if (strtotime($data['publish_begin']) >= strtotime($data['publish_end'])) {
                $response = sys_rt('发布时间段的结束时间必须大于开始时间', 2);
            }

            // 验证发布数量
            if ($response["status"] == 1) {
                if (!is_numeric($data["publish_number"]) || strpos($data["publish_number"], ".")) {
                    $response = sys_rt('发布数量格式错误（必须为正整数）', 2);
                } else if (strlen($data["publish_number"]) > 10) {
                    $response = sys_rt('发布数量长度超过限制', 2);
                }
            }

            // 验证时间间隔
            // if ($response["status"] == 1) {
            //     if (!is_numeric($data["publish_interval"]) || strpos($data["publish_interval"], ".")) {
            //         $response = sys_rt('时间间隔格式错误（必须为正整数）', 2);
            //     } else if (strlen($data["publish_interval"]) > 10) {
            //         $response = sys_rt('时间间隔长度超过限制', 2);
            //     }
            // }

            if ($response["status"] == 1) {
                // if (is_array($data["company_type"])) {
                //     $data['company_type'] = implode(",", $data['company_type']);
                // }

                $response = CommentAutoLogicModel::saveSetting($data);
            }

            $this->ajaxReturn($response);
        }

        $config = D("CommentAutoSetting")->getSetting();

        // 设置默认值
        if ($config["comment_on"] != 1) {
            $config["comment_on"] = 2;
        }

        $config["company_type"] = explode(",", $config["company_type"]);

        $this->assign('config', $config);
        $this->display();
    }

    public function importExcel()
    {
        import("Library.Org.Phpexcel.PHPExcel","",".php");
        import("Library.Org.Phpexcel.PHPExcel.IOFactory","",".php");

        $fileType = explode(".", $_FILES["fileup"]["name"]);
        $ext = $fileType[1];

        $path = $_FILES["fileup"]["tmp_name"];

        if($ext == "xls"){
            import("Library.Org.Phpexcel.PHPExcel.Reader.Excel5","",".php");
            $objReader = \PHPExcel_IOFactory::createReader('Excel5');
        }elseif($ext == "xlsx"){
            import("Library.Org.Phpexcel.PHPExcel.Reader.Excel2007","",".php");
            $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        }

        $objPHPExcel = $objReader->load($_FILES["fileup"]["tmp_name"]);
        $data = $objPHPExcel->getActiveSheet()->toArray();

        if(!$data) $this->ajaxReturn(array("info" => "文件读取失败", "status" => 0));

        if(count($data) > 1001) $this->ajaxReturn(array("info" => "导入数据不能超过1000条", "status" => 0));

        $header = array_flip($data[0]);
        $needHeader = ['装修阶段', '编辑模板', '是否启用'];
        foreach ($needHeader as $v) {
            if(!array_key_exists($v, $header)){
                $this->ajaxReturn(array("info"=>"缺少必要字段", "status"=>0));
                break;
            }
        }

        unset($data[0]);

        $stage_list = D("CommentAutoTemplate")->stageList;
        $stage = array_flip($stage_list);

        $insert = array();
        $on_status = array('是' => 1, '否' => 2);

        foreach ($data as $k => $v) {
            $isContinue = false;
            foreach ($needHeader as $vv) {
                if(empty($v[$header[$vv]])){
                    $isContinue = true;
                    break;
                }
            }

            if($isContinue) continue;

            if(!($value[0] = $stage[$v[$header['装修阶段']]])) continue;
            if(!($value[1] = $v[$header['编辑模板']])) continue;
            if(!($value[2] = $on_status[$v[$header['是否启用']]])) continue;

            $insert[] = array(
                'stage' => $value[0],
                'content' => $value[1],
                'use_status' => 2,
                'on_status' => $value[2],
                'enabled' => 1,
                'created_at' => time(),
                'updated_at' => time()
            );
        }

        if(!$insert) $this->ajaxReturn(array("info" => "没有可用的数据", "status" => 0));

        $result = D("CommentAutoTemplate")->addAll($insert);
        if(!$result) $this->ajaxReturn(array("info"=>"导入失败", "status" => 0));

        $this->ajaxReturn(array("info" => "", "status" => 1));
    }
}
