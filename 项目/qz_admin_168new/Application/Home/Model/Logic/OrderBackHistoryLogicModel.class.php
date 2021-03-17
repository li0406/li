<?php
//撤销单逻辑
namespace Home\Model\Logic;

use Home\Model\Db\AdminuserModel;
use Home\Model\Db\OrderBackRecordModel;
use Home\Model\OrderSourceModel;

class OrderBackHistoryLogicModel
{
    public static $reason = [
        1 => '销售分赠错误修改',
        2 => '工种、设计师、材料商发布',
        3 => '同行、装修公司，施工队发布',
        4 => '订单质量问题',
        5 => '重复订单',
        6 => '更改订单编辑内容',
        7 => '需要再次电话确认',
        8 => '订单分赠性质更改',
        9 => '上下备注不一致',
        10 => '转到下属城市',
        11 => '压单',
        99 => '其它',
    ];

    public function getBackHistory($input)
    {
        $where = [];
        if (!empty($input['order_id'])) {
            $where['t.order_id'] = ['eq', trim($input['order_id'])];
        }

        if (!empty($input['start_time']) && !empty($input['end_time'])) {
            $start = $input['start_time'];
            $end = $input['end_time'];
        } else {
            $start = date('Y-m-d', strtotime('-1 month'));
            $end = date('Y-m-d');
        }
        $where['start'] = strtotime($start . ' 00:00:00');
        $where['end'] = strtotime($end . ' 23:59:59');

        $search_uid = [];

        if (!empty($input['cs'])) {
            $where['t.cs'] = ['eq', $input['cs']];
        }

        if (!empty($input['op_uid'])) {
            $where['t.op_uid'] = ['eq', $input['op_uid']];
            $search_uid['op'] = $input['op_uid'];
        }

        if (!empty($input['push_uid'])) {
            $where['t.push_uid'] = ['eq', $input['push_uid']];
            $search_uid['push'] = $input['push_uid'];
        }

        if (!empty($input['reason'])) {
            $where['t.back_reason'] = ['eq', $input['reason']];
        }

        if (!empty($input['src_alias'])) {
            $where['t.alias'] = ['eq', $input['src_alias']];
            $search['src_alias'] = $input['src_alias'];
        }

        if (!empty($input['check_uid'])) {
            $where['t.check_uid'] = ['eq', $input['check_uid']];
            $search_uid['check'] = $input['check_uid'];
        }

        if (!empty($input['check_status'])) {
            $where['t.status'] = ['eq', $input['check_status']];
        }

        if (!empty($input['allot_state'])) {
            $where['t.allot_state'] = ['eq', $input['allot_state']];
            $search['allot_state'] = $input['allot_state'];
        }

        //获取查询的用户信息
        if (count($search_uid) > 0) {
            $adminDb = new AdminuserModel();
            $user = $adminDb->getUserInfoById($search_uid);
            $user = array_column($user, null, 'id');
            foreach ($search_uid as $k => $v) {
                $search[$k] = isset($user[$v]) ? $user[$v] : [];
            }
        }

        $search['start'] = $start;
        $search['end'] = $end;

        $backDb = new OrderBackRecordModel();
        $count = $backDb->getBackRecordCount($where);
        $pageCount = 20;
        $show = '';
        $list = [];
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count, $pageCount);
            $p->setConfig('prev', "上一页");
            $p->setConfig('next', '下一页');
            $show = $p->show();
            $list = $backDb->getBackRecordList($where, $p->firstRow, $p->listRows);
            foreach ($list as $k => $v) {
                $list[$k]['back_reason_name'] = self::$reason[$v['back_reason']] ?: '';
                $list[$k]['status_name'] = $v['status'] == 1 ? '正常' : ($v['status'] == 2 ? '异常' : '');

                switch ($v['allot_state']) {
                    case 1:
                        $list[$k]["allot_state_name"] = "已分配";
                        break;
                    case 2:
                        $list[$k]["allot_state_name"] = "未分配";
                        break;
                    default:
                        $list[$k]["allot_state_name"] = "";
                        break;
                }

            }
        }
        return array("page" => $show, "list" => $list, 'search' => $search);
    }

    public function getOrderBackRecordList($order_id)
    {
        $backDb = new OrderBackRecordModel();
        $list = $backDb->getOrderBackRecord($order_id);
        //获取撤回原因图片
        $img = $backDb->getBackRecordImg(array_column($list, 'id'));
        $recordImg = [];
        foreach ($img as $k => $v) {
            $recordImg[$v['record_id']][] = $v['img'];
            unset($img[$k]);
        }

        $info = [];//最新的记录信息
        foreach ($list as $k => $v) {
            $list[$k]['back_reason_name'] = self::$reason[$v['back_reason']] ?: '';
            $list[$k]['status_name'] = $v['status'] == 1 ? '正常' : ($v['status'] == 2 ? '异常' : '未审核');
            if (isset($recordImg[$v['id']])) {
                foreach ($recordImg[$v['id']] as $kk => $vv) {
                    $list[$k]['img'][] = 'https://' . changeImgUrl($vv);
                }
            }
            //将最新的记录返回,用来页面判断审核
            if ($k == 0) {
                $info = $v;
            }
        }
        return ['list' => $list, 'info' => $info];
    }

    public function checkRecord($input, $user_id)
    {
        $save = [
            'check_uid' => $user_id,
            'status' => $input['status'],
            'check_remark' => $input['remark'],
            'check_time' => time(),
            'updated_at' => time(),
        ];
        $backDb = new OrderBackRecordModel();
        return $backDb->editRecord($input['id'], $save);
    }
}
