<?php
/**
 * 通话相关模块
 */

namespace Home\Model\Logic;

use Common\Enums\ClinkEnum;
use Home\Model\Service\PhoneCallClinkServiceModel;
use Home\Model\Db\LogTelcenterReviewOrdercallModel;

class LogTelcenterOrdercallLogicModel
{
    public function getOrderCallCountDetailByOrderIds($info){
        $tel = D('LogTelcenterOrdercall')->getOrderCallCountDetailByOrderIds(array_column($info,'id'));
        $telData = [];
        foreach ($tel as $k=>$v){
            $telData[$v['orderid']] = $v;
        }
        foreach ($info as $k => $v) {
            $info[$k]['boda_count'] = empty($telData[$v['id']]['boda_count']) ? 0 : $telData[$v['id']]['boda_count'];
            $info[$k]['botong_count'] = empty($telData[$v['id']]['botong_count']) ? 0 : $telData[$v['id']]['botong_count'];
            $info[$k]['time_real'] = date("Y", $v['time_real']) . '年' . date("m", $v['time_real']) . '月' . date("d", $v['time_real']) . '日';
        }
        return $info;
    }

    // 订单回访呼叫数量统计
    public function getReviewOrderCallCount($orderIds, $kfIds = []){
        if (!is_array($orderIds)){
            $orderIds = explode(",", $orderIds);
        }

        $orderIds = array_filter(array_unique($orderIds));
        $reviewOrderCallModel = new LogTelcenterReviewOrdercallModel();

        // 回访电话纪录日志(老)
        $telLog = $reviewOrderCallModel->getReviewOrderCallCount($orderIds, $kfIds);
        $telLog = array_column($telLog, null, "id");

        // 合力回访电话纪录日志(新)
        $hollyTelLog = $reviewOrderCallModel->getHeLiReviewOrderCallCount($orderIds, $kfIds);
        $hollyTelLog = array_column($hollyTelLog, null, "id");

        // 天润回访电话纪录日志
        $clinkTelLog = $reviewOrderCallModel->getClinkReviewOrderCallCount($orderIds, $kfIds);
        $clinkTelLog = array_column($clinkTelLog, null, "id");

        $list = [];
        foreach ($orderIds as $key => $order_id) {

            $list[$order_id] = [
                "def_tel_log_count" => $telLog[$order_id]["repeat_count"] ?? 0,
                "holly_tel_log_count" => $hollyTelLog[$order_id]["repeat_count"] ?? 0,
                "clink_tel_log_count" => $clinkTelLog[$order_id]["repeat_count"] ?? 0,
            ];
            
            $list[$order_id]["tel_log_count"] = $list[$order_id]['def_tel_log_count'] + $list[$order_id]['holly_tel_log_count'] + $list[$order_id]['clink_tel_log_count'];
        }

        return $list;
    }

    // 订单回访呼叫列表
    public function getReviewOrderCallList($order_id, $kfIds = []){
        $reviewOrderCallModel = new LogTelcenterReviewOrdercallModel();

        // 回访电话纪录日志(老)
        $telList = $reviewOrderCallModel->getOrderCallListByOrderId($order_id, $kfIds);
        $telList = D("LogTelcenterOrdercall")->callistHuman($telList, 1); //数据再加工
        $telList = $this->setTelFormatter($telList);

        // 查询合力的电话记录(新)
        $hollyLogic = new HollyLogicModel();
        $hollyList = $hollyLogic->getOrderReviewTelLogById($order_id, $kfIds);

        // 查询天润通话记录
        $model = new PhoneCallClinkServiceModel();
        $clinkList = $model->getReviewOrderTelLogById($order_id, $kfIds);

        if (count($clinkList) > 0) {
            foreach ($clinkList as $key => $item) {
                $clinkList[$key] = [
                    "time_add" => date("Y-m-d H:i:s", $item["created_at"]),
                    "byetype" => ClinkEnum::getStatus($item["cdr_status"]),
                    "call_time_length" => $item["cdr_bridge_time"],
                    "callsid" => $item["call_sid"],
                    "caller" => $item["cust_callee_clid"],
                    "called" => $item["cdr_customer_number"],
                    "admin_user" => $item["op_uname"],
                    "action" => $item["cdr_call_type"] == 1 ? "呼入" : "外呼",
                    "TelCenter_Channel" => "clink"
                ];
            }
        }

        $list = array_merge($telList, $hollyList, $clinkList);

        // 按时间重新排序
        $edition = array_column($list, "time_add");
        array_multisort($edition, SORT_DESC, $list);

        return $list;
    }

    private function setTelFormatter($list){
        $reviewOrderCallModel = new LogTelcenterReviewOrdercallModel();

        foreach ($list as $key => &$value) {
            $value['tonghuasj'] = $value['endtime'] - $value['starttime']; //通话时长
            //呼叫动作
            switch ($value['action']) {
                case 'CallAuth':
                    $value['action'] = "开始";
                    break;
                case 'CallEstablish':
                    $value['action'] = "接通";
                    break;
                case 'Hangup':
                    $value['action'] = "挂断";
                    break;
                case 'CallEstablish_Sub':
                    $value['action'] = "主叫接通";
                    break;
                case 'Hangup_Sub':
                    $value['action'] = "主叫挂断";
                    break;
                default:
                    # code...
                    break;
            }
            //拨打方式
            switch ($value['calltype']) {
                case 'callBack':
                    $value['calltype'] = "回拨拨打";
                    break;
                default:
                    break;
            }

            //挂断方式
            if ($value['TelCenter_Channel'] == 'cuct') {
                //对联通云总机的挂机代码处理
                //state：状态：0-呼叫挂机（默认值）；1-通话失败。
                switch ($value['byetype']) {
                    case '0':
                        $value['byetypewz'] = '呼叫挂机';
                        break;
                    case '1':
                        $value['byetypewz'] = '通话失败';
                        break;
                    default:
                        $value['byetypewz'] = '-';
                        break;
                }
            } else {
                // 默认是 云通讯的挂机代码
                $value['byetypewz'] = $reviewOrderCallModel->getByeTypeInfo($value['byetype']);
            }
        }

        return $list;
    }
}