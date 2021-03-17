<?php

namespace Home\Model\Logic;

use Common\Enums\ClinkEnum;
use Common\Enums\VoipCodeEnum;
use Home\Model\Db\LogClinkTelcenterModel;
use Home\Model\Db\SelfTelModel;

class SelfTelLogicModel
{
    public function getTelList($get, $user = [], $uid)
    {
        if ($uid != 1 && count($user) == 0) {
            return [];
        }
        $where = [];
        if (!empty($user)) {
            $where['hd.op_uid'] = ['in', $user];
        }
        //呼叫对象
        if (!empty($get['scalltowho'])) {
            $where['hd.call_obj'] = ['eq', $get['scalltowho']];
        }
        //主叫号码
        if (!empty($get['fromtel'])) {
            $where['ht.call_no'] = ['eq', $get['fromtel']];
        }
        //被叫号码
        if (!empty($get['scalltel'])) {
            $where['ht.called_no_encrypt'] = ['eq', md5($get['scalltel'] . C('QZ_YUMING'))];
        }
        if (!empty($get['starttime']) && !empty($get['endtime'])) {
            $where['hd.time'] = ['BETWEEN', [strtotime($get['starttime'] . " 00:00:00"), strtotime($get['endtime'] . " 23:59:59")]];
        } else {
            //设置默认查询时间 三个月
            $where['hd.time'] = ['BETWEEN', [strtotime(date('Y-m-d H:i:s', strtotime('-90 days'))), time()]];
        }
        $selfDb = new SelfTelModel();
        $count = $selfDb->getTelListCount($where);

        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count, 20);
            $show = $p->show();
            $list = $selfDb->getTelList($where, $p->firstRow, $p->listRows);

            return ['list' => $list, 'page' => $show];
        }
    }

    public function getTelDetail($call_sid = '')
    {
        $telDb = new SelfTelModel();
        $list = $telDb->getDiyTelByCallSid($call_sid);

        //天润
        $model = new LogClinkTelcenterModel();
        $result = $model->getRecodeInfo($call_sid);

        if (count($result) > 0) {
            $list[] = [
                'id' => $result["id"],
                'call_sid' => $result["call_sid"],
                'call_no' => $result["cust_callee_clid"],
                'called_no' => $result["cust_callee_clid"],
                'call_time_length' => $result["cdr_end_time"] - $result["cdr_bridge_time"],
                'state' => "",
                'call_state' => ClinkEnum::getStatus($result["cdr_status"]),
                'hanguper' => $result["cdr_call_type"] == 1?"呼入":"外呼",
                'createtime' =>  $result["created_at"],
                "channel" => "clink"
            ];
        }

        return $list;
    }

    /**
     * 合力通话记录格式化
     * @param $list
     * @return array
     */
    public function setHollyFormatter($list)
    {
        $newlist = [];
        if (!empty($list)) {
            foreach ($list as $key => $item) {
                $sub = array(
                    'id' => $item["id"],
                    'call_sid' => $item['call_sid'],
                    'time_add' => date("Y-m-d H:i:s", $item['createtime']),
                    'caller' => $item['call_no'],
                    'called' => $item['called_no'],
                    'duration' => $item['call_time_length'],
                    'recordurl' => '',
                    'byetype' => $item['state'],

                );
                if ($item["channel"] == "") {
                    $sub['action'] = $item['call_state'];
                    $sub['action_name'] = VoipCodeEnum::getHollyAction($item['hanguper'], $item['call_state']);
                    $sub['channel'] = 'holly';
                    $sub['TelCenter_Channel'] = 'holly';
                    $sub['byetype_name'] = VoipCodeEnum::getItem('holly_state', $item['state'], '-');
                } else {
                    $sub["byetype_name"] = "";
                    $sub['action'] = "";
                    $sub['action_name'] = "";
                    $sub['channel'] = 'clink';
                    $sub['TelCenter_Channel'] = 'clink';
                }
                $newlist[] = $sub;
            }
        }

        return $newlist;
    }

}
