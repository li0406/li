<?php

namespace Home\Model\Logic;

use Home\Model\CommentAutoTemplateModel;

class CommentAutoLogicModel
{

    public static function getTemplateList($query, $page = 1, $limit = 20)
    {

        // 查询条件（参数绑定形式）
        $map = ["enabled" => ":enabled"];
        $bind = [":enabled" => 1];

        // 装修阶段
        if (isset($query['stage']) && !empty($query["stage"])) {
            $map['stage'] = ":stage";
            $bind[":stage"] = $query["stage"];
        }

        // 启用状态
        if (isset($query['on_status']) && !empty($query["on_status"])) {
            $map['on_status'] = ":on_status";
            $bind[":on_status"] = $query["on_status"];
        }

        // 使用状态
        if (isset($query['use_status']) && !empty($query["use_status"])) {
            $map['use_status'] = ":use_status";
            $bind[":use_status"] = $query["use_status"];
        }

        $catModel = new CommentAutoTemplateModel();
        $count = $catModel->where($map)->bind($bind)->count('id');
        $list = $catModel->where($map)->bind($bind)->order("created_at desc")->page($page, $limit)->select();

        return [
            "count" => $count,
            "list" => $list,
        ];
    }

    // 保存设置
    public static function saveSetting($data)
    {
        D()->startTrans();

        $response = sys_rt('execute:ok', 1);
        $commentAutoSetting = D("CommentAutoSetting");
        foreach ($data as $key => $item) {
            $result = $commentAutoSetting->saveSettingByKey($key, $item);
            if (empty($result)) {
                $response = sys_rt('操作失败', 2);
            }
        }

        if ($response['status'] == 1) {
            D()->commit();
        } else {
            D()->rollback();
        }

        return $response;
    }

}
