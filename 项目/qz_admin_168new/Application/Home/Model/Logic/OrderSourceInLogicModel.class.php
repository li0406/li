<?php

namespace Home\Model\Logic;

use Home\Model\Db\OrderSourceInModel;

class OrderSourceInLogicModel
{
    public function getSourceIn()
    {
        $limit = 20;
        $sourceInDb = new OrderSourceInModel();
        $count = $sourceInDb->getSourceInCount();
        $result = [];
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $Page = new \Page($count, $limit);
            $result['list'] = $sourceInDb->getSourceInList($Page->firstRow, $Page->listRows);
            $result['page'] = $Page->show();
        }
        return $result;
    }

    public function getSourceInInfo($id)
    {
        $sourceInDb = new OrderSourceInModel();
        $where = ['id' => ['eq', $id]];
        return $sourceInDb->getSourceInInfo($where);
    }

    public function checkSourceIn($data)
    {
        $sourceInDb = new OrderSourceInModel();
        $where = ['type_name' => ['eq', $data['type_name']]];
        $info = $sourceInDb->getSourceInInfo($where);
        if (!empty($data['id'])) {
            if (!empty($info)) {
                if ($data['id'] != $info['id']) {
                    return ['error_code' => 400, 'error_msg' => '该订单类型已存在'];
                }
            }
            //获取当前订单类型的关联数据,验证是否有关联
            if($data['is_use'] == 2){
                $count = $sourceInDb->getOrderSourceInRelationCount($data['id']);
                if ($count > 0) {
                    return ['error_code' => 400, 'error_msg' => '该订单类型已有渠道关联,无法删除'];
                }
            }
        } else {
            if (!empty($info)) {
                return ['error_code' => 400, 'error_msg' => '该订单类型已存在'];
            }
        }
        return ['error_code' => 0];
    }

    public function saveSourceIn($data, $user)
    {
        $inDb = new OrderSourceInModel();
        $save = [
            'type_name' => trim($data['type_name']),
            'description' => trim($data['description']),
            'is_use' => $data['is_use'],
            'updated_at' => time(),
        ];
        if (!empty($data['id'])) {
            $status = $inDb->editSourceIn($data['id'], $save);
        } else {
            $save['op_name'] = $user['user'];
            $save['op_uid'] = $user['id'];
            $save['created_at'] = time();
            $status = $inDb->addSourceIn($save);
        }
        //清除缓存
        S('Cache:168new:SourceIn', null);
        return $status;
    }

    /**
     * 获取使用中的订单类型(编辑/新增时更新)
     * @return array|bool|mixed|string|null
     */
    public function getSourceInSelect()
    {
        $sourceIn = S('Cache:168new:SourceIn');
        if (empty($sourceIn)) {
            $sourceInDb = new OrderSourceInModel();
            $sourceIn = $sourceInDb->getUseSourceInList();
            S('Cache:168new:SourceIn', $sourceIn, 60 * 60 * 24);
        }
        return $sourceIn;
    }
}
