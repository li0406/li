<?php

namespace app\common\model\logic;

use app\common\model\db\ReportCompany;
use app\common\model\db\ReportLogTelcenterOrdercall;
use app\common\model\db\ReportVisit;

class VisitLogic
{
    public function saveVisit($info, $user)
    {
        $save = [
            'company_id' => $info['company_id'],
            'uid' => $user['user_id'],
            'start_time' => strtotime($info['visit_start_time']),
            'end_time' => strtotime($info['visit_end_time']),
            'style' => $info['visit_style'],
            'status' => isset($info['qianyue_status']) ? $info['qianyue_status'] : '1',
            'retainage_time' => isset($info['retainage_time']) ? strtotime($info['retainage_time']) : '0',
            'next_time' => strtotime($info['visit_next_time']),
            'desc' => $info['desc'],
            'pre_money' => isset($info['pre_money']) ? $info['pre_money'] : 0,
            'updated_at' => time(),
            'created_at' => time(),
        ];
        $visitDb = new ReportVisit();
        $status = $visitDb->saveVisit($save);
        if ($status) {
            //更新电话记录
            if(isset($info['call_id']) && !empty($info['call_id'])){
                if($info['visit_style'] == 2){
                    //根据当前的电话商
                    $optionModel = new OptionsLogic();
                    $channel = $optionModel->getMyTelCenterChannelByid($user['user_id']);

                    $logData['record_id'] = $status;
                    switch ($channel){
                        case "holly":
                            $model = new HollyLogic();
                            $model->editReportTelLog($info['call_id'],$logData);
                            break;
                        default:
                            $logData['callSid'] = $info['call_id'];
                            $voip = new ReportLogTelcenterOrdercall();
                            $voip->saveVisitTel([$logData]);
                            break;
                    }
                }
            }

            //更新公司编辑时间
            $companySave = [
                'id' => $info['company_id'],
                'updated_at' => time(),
            ];
            $com = new ReportCompany();
            $com->saveReportCompany([$companySave]);
            return sys_response(0);
        } else {
            return sys_response(5000002);
        }
    }

    public function getVisits($info,$page = 1,$limit = 20)
    {
        //设置查询条件
        $map = function ($query) use ($info) {
            $query->where('qz_report_visit.company_id', '=', $info['company_id']);
            if (!empty($info['visit_start'])){
                $query->where('qz_report_visit.start_time', '>=', strtotime($info['visit_start']));
            }
            if (!empty($info['visit_end'])){
                $query->where('qz_report_visit.end_time', '<=', strtotime($info['visit_end'])+86399);
            }
            if (!empty($info['next_start'])){
                $query->where('qz_report_visit.next_time', '>=', strtotime($info['next_start']));
            }
            if (!empty($info['next_end'])){
                $query->where('qz_report_visit.next_time', '<=', strtotime($info['next_end'])+86399);
            }
            if (!empty($info['created_at_start'])){
                $query->where('qz_report_visit.created_at', '>=', strtotime($info['created_at_start']));
            }
            if (!empty($info['created_at_end'])){
                $query->where('qz_report_visit.created_at', '<=', strtotime($info['created_at_end'])+86399);
            }
        };
        $visitDb = new ReportVisit();
        $count = $visitDb->visitListCount($map);

        $list = [];
        if ($count > 0){
            $list = $visitDb->visitList($map,($page-1) * $limit,$limit);
        }

        return sys_response(0, '', [
            'list' => $list,
            'page' => sys_paginate($count, $limit, $page) #分页信息
        ]);
    }
}