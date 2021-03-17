<?php
// +----------------------------------------------------------------------
// | OrdersCompanyReportView
// +----------------------------------------------------------------------
namespace app\common\model\logic;

use app\common\model\db\User;
use app\common\model\db\OrdersCompanyReport;
use app\common\model\db\LogQiandanchk;

class OrdersCompanyReportLogic
{
    protected $ordersCompanyReportModel;

    public function __construct()
    {
        $this->ordersCompanyReportModel = new OrdersCompanyReport();
    }

    public function consultation($param, $page, $pageSize)
    {
        $map['deleted'] = 0;
        // 非超级管理员获取当前用户管辖城市
        $citys = AdminuserLogic::getCityIds();
        if (!empty($citys)) {
            $map['cs'] = ['in', $citys];
            if (!empty($param['cs'])) {
                if (in_array($param['cs'], $citys)) {
                    $map['cs'] = $param['cs'];
                } else {
                    return sys_response(4000002, '暂无权限查看数据', []);
                }
            }
        } else {
            return sys_response(4000002, '暂无权限查看数据', []);
        }

        if (!empty($param['addstart']) && !empty($param['addend'])) {
            $map['time_add'] = ['between', [strtotime($param['addstart']), strtotime($param['addend'] . ' 23:59:59')]];
        }
        if (!empty($param['qdstart']) && !empty($param['qdend'])) {
            $map['time_qd'] = ['between', [$param['qdstart'], $param['qdend'] . ' 23:59:59']];
        }
        if (!empty($param['chkstart']) && !empty($param['chkend'])) {
            $map['time_chk'] = ['between', [strtotime($param['chkstart']), strtotime($param['chkend'] . ' 23:59:59')]];
        }
        if (isset($param['order_on']) && $param['order_on'] !== '') {
            $map['order_on'] = $param['order_on'];
        }
        if (!empty($param['zxfs'])) {
            $map['zxfs'] = $param['zxfs'];
        }
        if (!empty($param['search'])) {
            if (ctype_digit((string)$param['search'])) {
                if (preg_match('/^(13|14|15|16|17|18|19)[0-9]{9}$/', $param['search'])) {
                    $map['tel168'] = $param['search'];
                } else {
                    $map['order_company_id'] = $param['search'];
                }
            } else {
                // 获取匹配对应的公司id
                $userModel = new User();
                $comid = $userModel->getCompanyByNameMatchAll(['company_name' => $param['search']]);
                if (empty($comid)) {
                    return sys_response(0, '', [
                        'list' => [],
                        'page' => sys_paginate(0, $pageSize, $page)
                    ]);
                }
                $map['order_company_id'] = ['in', array_column($comid->toArray(), 'id')];
            }
        }
        $count = $this->ordersCompanyReportModel->getCount($map);
        if (!empty($count)) {
            $list = $this->ordersCompanyReportModel->getList($map, $page, $pageSize, ['city', 'area', 'qdcompany'])->toArray();
            $list = array_map(function ($value) {
                array_walk($value, function (&$val, $key) {
                    if ($val && is_string($key) && stripos($key, 'time') !== false && ctype_digit((string)$val) && $val <= 2147483647) {
                        $val = date('Y-m-d H:i:s', $val);
                    } else {
                        $val = is_null($val) ? '' : $val;
                    }
                });
                return $value;
            }, $list);
        }
        $PermissionLogic = new PermissionLogic();
        $havePurview = (request()->user['role_id'] == 1 ||
            !empty($PermissionLogic->getMenuByRoleidAndName(request()->user['role_id'],'会员签单审核操作'))
        ) ? true : false;
        return sys_response(0, '', [
            'list' => !empty($list) ? $list : [],
            'show_btn' => $havePurview ? 1 : 0,
            'page' => sys_paginate($count, $pageSize, $page)
        ]);
    }

    public function consultation_detail($id)
    {
        if (empty($id)) {
            return sys_response(4000002);
        }
        $map['deleted'] = 0;
        $map['id'] = $id;
        $detail = $this->ordersCompanyReportModel->getOne($map, ['city', 'area', 'hx', 'fs', 'fg', 'qdcompany'])->toArray();
        array_walk($detail, function (&$val, $key) {
            if ($val && is_string($key) && stripos($key, 'time') !== false && ctype_digit((string)$val) && $val <= 2147483647) {
                $val = date('Y-m-d H:i:s', $val);
            } elseif ($key !== 'qiandan_status') {
                $val = is_null($val) ? '' : $val;
            }
        });
        return sys_response(0, '',
            [
                'detail' => $detail
            ]
        );
    }

    public function consultation_check($id, $user)
    {
        if (empty($id)) {
            return sys_response(4000002);
        }

        $map['deleted'] = 0;
        $map['id'] = $id;
        $ordersCompanyReport = $this->ordersCompanyReportModel->getOne($map);
        if (empty($ordersCompanyReport)) {
            return sys_response(4000001, '数据不存在/已被删除', []);
        }
        if ($ordersCompanyReport->order_on == 0) {
            $ordersCompanyReport->order_on = 1;
            $action = 'checked';
        } else {
            $ordersCompanyReport->order_on = 0;
            $action = 'cancel';
        }

        $ordersCompanyReport->time_chk = time();
        $ordersCompanyReport->op_name = $user['user_name'];
        $flag = $ordersCompanyReport->save();
        if ($flag !== false) {
            //取消审核删除工地直播
            if ($action == 'cancel') {
                //取消审核则直接删除已经生成的工地直播数据
                $worksiteLogic = new WorkSiteLiveLogic();
                $worksiteLogic->delWorkSite($ordersCompanyReport->id,2);
            } else {
                // 审核通过生成工地直播
                $worksiteLogic = new WorkSiteLiveLogic();
                $worksiteLogic->addOrderWorkSite($ordersCompanyReport->id, $ordersCompanyReport->order_company_id, 2);
            }

            $qiandanLogModel = new LogQiandanchk();
            $qiandanLogModel->addLog('zixun', $ordersCompanyReport->id, $action,
                [
                    'id' => $ordersCompanyReport->id,
                    'order_on' => $ordersCompanyReport->order_on,
                    'time_chk' => $ordersCompanyReport->time_chk,
                    'op_name' => $ordersCompanyReport->op_name,
                ]
            );
            return sys_response(0, '');
        } else {
            return sys_response(5000002, '');
        }
    }

    public function consultation_count($param, $page, $pageSize) {
        $start = strtotime('-30 days', strtotime('today'));
        $end = time();
        $cs = '';
        if (!empty($param['start']) && !empty($param['end'])) {
            $start = strtotime($param['start']);
            $end = strtotime($param['end'].' 23:59:59');
        }

        if (!empty($param['cs'])) {
            $cs = $param['cs'];
        }

        // 合法排序字段
        $validOrderFields = [
            "sum_num asc",
            "sum_num desc",
            "consult_num asc",
            "consult_num desc",
            "qiandan_num asc",
            "qiandan_num desc",
        ];

        $order = 'sum_num desc';
        if (!empty($param['orderby']) && in_array($param['orderby'], $validOrderFields)) {
            $order = $param['orderby'];
        }

        $list = $this->ordersCompanyReportModel->getConsultList($start, $end, $cs, $order)->toArray();
        $count = count($list);
        $return_list = array_slice($list, ($page - 1) * $pageSize, $pageSize);

        // 数据汇总
        $total = [
            "city_count" => $count,
            "consul_count" => 0,
            "qiandan_count" => 0,
            "sum_count" => 0
        ];
        
        foreach ($list as $key => $item) {
            $total["qiandan_count"] += $item["qiandan_num"];
            $total["consul_count"] += $item["consult_num"];
            $total["sum_count"] += $item["sum_num"];
        }

        return sys_response(0, '', [
            'list' => $return_list,
            'time' => [date('Y-m-d', $start), date('Y-m-d', $end)],
            'page' => sys_paginate($count, $pageSize, $page),
            'total' => $total
        ]);
    }
}