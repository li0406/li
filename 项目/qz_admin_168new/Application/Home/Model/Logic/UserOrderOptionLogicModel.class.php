<?php

namespace Home\Model\Logic;

use Home\Model\Db\CompanyOrderOptionModel;
use Home\Model\LogAdminModel;

class UserOrderOptionLogicModel
{
    /**
     * 获取数据列表
     * @param $get
     * @return array
     */
    public function getOrderOption($get)
    {
        $optionDb = new CompanyOrderOptionModel();
        $start = '';
        $end = '';
        if ($get['begin'] && $get['end']) {
            $start = strtotime($get['begin'] . ' 00:00:01');
            $end = strtotime($get['end'] . ' 23:59:59');
        }
        $count = $optionDb->getUserOptionCount($get['condition'], $get['city'], $start, $end);
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count, 20);
            $show = $p->show();
            $list = $optionDb->getUserOptionList($get['condition'], $get['city'], $start, $end, $p->firstRow, $p->listRows);
            //处理续费数据
            return ['list' => $list, 'page' => $show];
        }
    }

    /**
     * 获取配置详情
     * @param $id
     * @return mixed
     */
    public function getCompanyOrderOptionInfo($id)
    {
        $optionDb = new CompanyOrderOptionModel();
        return $optionDb->getCompanyOrderOptionInfo($id);
    }

    /**
     * 删除配置项
     * @param $id
     * @return array
     */
    public function delOption($id)
    {
        $optionDb = new CompanyOrderOptionModel();
        $old_info = $optionDb->getCompanyOrderOptionInfo($id);
        $info = $optionDb->delInfo($id);
        if ($info) {
            $logDb = new LogAdminModel();
            $logDb->addTypeLog('userorderoption-del', $old_info);
            return ['error_code' => 0];
        } else {
            return ['error_code' => 404, 'error_msg' => '操作失败! 刷新后再试'];
        }
    }

    /**
     * 保存配置项
     * @param $post
     * @param $user
     * @return array
     */
    public function saveOption($post, $user)
    {
        if (!$post['company_id']) {
            return ['error_code' => 404, 'error_msg' => '缺少数据 !'];
        }
        $optionDb = new CompanyOrderOptionModel();
        $save = [
            'company_id' => $post['company_id'],
            'all_rob' => !empty($post['all_rob']) ? $post['all_rob'] : 2,
            'all_give' => !empty($post['all_give']) ? $post['all_give'] : 2,
            'get_other_company' => 0, //不做设置(仅保留设置项)
            'op_uid' => $user['id'],
            'op_name' => $user['name'],
        ];
        if (!$post['edit_id']) {
            $save['add_time'] = time();
        }
        if ($post['begin'] && $post['end']) {
            $save['start_time'] = strtotime($post['begin'] . ' 00:00:01');
            $save['end_time'] = strtotime($post['end'] . ' 23:59:59');
        }
        if ($post['edit_id']) {
            //编辑
            $status = $optionDb->editOptionInfo($post['edit_id'], $save);
        } else {
            //验证是否存在
            $info = $optionDb->getOptionByCompany($post['company_id']);
            if ($info) {
                return ['error_code' => 404, 'error_msg' => '记录已存在! '];
            }
            //新增
            $status = $optionDb->addInfo($save);
        }
        if ($status) {
            return ['error_code' => 0];
        } else {
            return ['error_code' => 404, 'error_msg' => '操作失败! '];
        }
    }
}
