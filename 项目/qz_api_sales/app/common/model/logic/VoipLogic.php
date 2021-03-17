<?php

namespace app\common\model\logic;

use app\common\enum\BaseStatusCodeEnum;
use app\common\model\db\Adminuser;
use app\common\model\db\LogAdmin;
use app\common\model\db\LogTelcenterOrdercall;
use app\common\model\db\ReportLogTelcenterOrdercall;
use app\common\model\db\LogHollyReportTelcenter;
use app\common\model\db\LogHollyTelcenter;
use app\common\model\logic\Telcuct;
use app\index\enum\VoipCodeEnum;

class VoipLogic
{
    public function telephoneVoipCall($user,$tel = ''){
        //验证手机号码
        if (empty($tel)) {
            return sys_response(4000010);
        }
        //取当前登录用户 电话提供商设置
        $Doption                        = new OptionsLogic();
        $TelCenter_Channel              = $Doption->getMyTelCenter_ChannelByid($user['user_id']); //获取当前帐号的电话提供商信息
        $TelCenter_ChannelInfo          = $Doption->getTelCenter_ChannelInfoByChannel($TelCenter_Channel); //通过提供商标识 获取完整的信息

        //查询VOIP电话号码
        $adminUser = new Adminuser();
        $userInfo = $adminUser->findUserVoipInfo($user['user_id'], $TelCenter_ChannelInfo['solutions']);

        if (empty($userInfo["voipaccount"])) {
            return array("code"=>"404","errmsg"=>"请先设置VOIP号码");
        }

        //呼叫频率限制，如果该订单
//        $voip = D("Home/LogTelcenterOrdercall");

        //取当前电话提供商
//        $Doption        = D('Options');
        $TelCenter_Channel      = $Doption->getMyTelCenter_ChannelByid($user['user_id']);

        //提供商选择逻辑
        if ($TelCenter_Channel == 'cuct') {
            //走 联通云总机
            $from           = $userInfo['tel_work1']; //VOIP主叫号码
            $toTel          = $tel; //被叫电话号码
            $telDb = new Telcuct($userInfo);
            $cuctResult     = $telDb->callBack($from, $toTel);

            if($cuctResult['resp']['respCode'] == '0') {
                //呼叫记录日志
                $logData['calltype']       = 'callBack';
                $logData['callSid']        = $cuctResult['resp']['callBack']['callId'];
                //因为联通云总机主叫号码是绑定的号码,并非voip号,所以这里给voip号码, 故意不给主叫(主叫是绑定的号码)
                $logData['fromtel']        = $userInfo['voipaccount']; //主叫
                $logData['totel']          = substr($toTel, 0,3) . '*****' . substr($toTel, -3); //被叫
                $logData['fromSerNum']     = ''; //主叫显号
                $logData['customerSerNum'] = $userInfo['tel_customer_ser_num']; //被叫显号
                $logData['time_add']       = date('Y-m-d H:i:s');
                $logData['admin_id']       = $user['user_id'];
                $logData['admin_user']     = $user['user_name'];
                $logData['appid']          = OP('CUCT_APPID_QZ');
                $logData['channel']        = $TelCenter_Channel; //电话提供商渠道
                $logData['created_at']     = time();
                $logData['updated_at']     = time();
                $voip = new ReportLogTelcenterOrdercall();
                $voip->addLog($logData);
                //返回ajax
                return sys_response(1000010,BaseStatusCodeEnum::CODE_1000010,$cuctResult['resp']['callBack']);
            } else {
                $logDb = new LogAdmin();
                // 记录日志
                $logArr['time']   = time();
                $logArr['log']    = json_encode($cuctResult);
                $logArr['level'] = '失败';
                $logArr['module'] = 'cuct_callback';
                $logDb->addTelFailedLog($user['user_id'], $logArr);
                //接口不成功 返回msg信息
                return sys_response(1000012, BaseStatusCodeEnum::CODE_1000012 . ',' . $cuctResult['msg']);
            }
        }

        return sys_response(1000011);
    }

    /**
     * 处理通话列表 成人类友好的数据列表
     * 多家电话提供商的通话记录是存在一个表中
     * 多家的通话记录播放是不一样的, 比如容联云通讯是直接给的 url 地址,
     * 而联通云总机需要用callid去调用临时的录音url地址
     * 这个函数做一次数据加工,以供数据展示用
     * @param $calllist
     * @param int $displayType
     * @return mixed
     */
    public function callistHuman($calllist, $displayType=0)
    {
        foreach ($calllist as $key => &$value) {

            if (substr($value['call_sid'],0,3) == 'api') {
                //如果callSid(callid) 是api开头的则认为是 联通云总机的记录
                $value['TelCenter_Channel'] = 'cuct';
            } else {
                //否则就是 容联云通讯的记录
                $value['TelCenter_Channel'] = 'ytx';
            }

            if (!empty($value['channel'])) {
                //如果已经有渠道标识了, 就进行覆盖
                $value['TelCenter_Channel'] = $value['channel'];
            }

            if ($value['TelCenter_Channel'] == 'cuct') {
                //如果渠道是联通云总机 计算下人类友好的通话时间
                if (!empty($value['duration'])) {
                    $value['duration_human'] =  timediff($value['duration']);
                }
            }

            if ($displayType == 0) {
                //只显示挂机记录
                if ($value['action'] != 'Hangup') {
                    //dump($value['action']);
                    unset($calllist[$key]);
                }
                if ($value['TelCenter_Channel'] == 'cuct') {
                    //如果是 联通云总机, 处理掉 byetype 为1的记录
                    if ($value['byetype'] == 1) {
                        unset($calllist[$key]);
                    }
                }
            }

            if ($displayType == 1) {
                //即便是显示所有呼叫记录也处理 联通云总机的 回拨接口中 总机对主叫的拨打记录
                // $actionArr = array('CallEstablish_Sub','Hangup_Sub');
                // if (in_array($value['action'], $actionArr)) {
                //     unset($calllist[$key]);
                // }
            }
        }
        //dump($calllist);
        return $calllist;
    }

    /**
     * 返回格式处理
     * @Author   liuyupeng
     * @DateTime 2019-05-20T15:12:36+0800
     */
    public function setFormatter($calllist){
        foreach ($calllist as $key => $item) {
            $calllist[$key]['action_name'] = VoipCodeEnum::getItem('action', $item['action']); // 呼叫动作
            $calllist[$key]['byetype_name'] = VoipCodeEnum::getItem('byetype', $item['byetype'], '-'); // 挂机类型
        }

        return $calllist;
    }

    /**
     * 获取通话记录
     * @param  [type] $call_sid [description]
     * @param  [type] $channel  [description]
     * @return [type]           [description]
     */
    public function getVoipRecordDefault($call_sid){
        $telDb = new ReportLogTelcenterOrdercall();
        $calllist = $telDb->getVisitTelDetailByCallSid($call_sid);

        if (!empty($calllist)) {
            $calllist = $this->callistHuman($calllist, 1);
            $calllist = $this->setFormatter($calllist);
        }

        return $calllist;
    }

    /**
     * 获取合力通话记录
     * @param  [type] $call_sid [description]
     * @return [type]           [description]
     */
    public function getVoipRecordHolly($call_sid){
        $logHollyTelcenter = new LogHollyTelcenter();
        $calllist = $logHollyTelcenter->getListByCallSid($call_sid);
        $calllist = $this->setHollyFormatter($calllist);

        return $calllist;
    }

	/**
     * 通过订单号获取通话记录
     * @param  [type] $orderid [description]
     * @return [type]          [description]
     */
    public function getTelListByOrderId($orderid){
        // 通话记录
        $telDb = new LogTelcenterOrdercall();
        $logList = $telDb->getOrderCallListByOrderId($orderid);
        $logList = $this->callistHuman($logList, 1);
        if (!empty($logList)) {
            $logList = $this->setFormatter($logList);
        }

        // 合力通话记录
        $logHollyTelcenter = new LogHollyTelcenter();
        $logHollyList = $logHollyTelcenter->getListByOrderId($orderid);
        $logHollyList = $this->setHollyFormatter($logHollyList);

        // 合并多平台通话记录（一定要在处理完格式之后合并）
        $logList = array_merge($logList, $logHollyList);

        // 合并后重新排序
        if (!empty($logList)) {
            $times = array_column($logList, 'time_add');
            array_multisort($times, SORT_ASC, $logList);
        }

        return $logList;
    }


    /**
     * 合力通话记录格式化
     * @param [type] $list [description]
     */
    public function setHollyFormatter($list){
        $newlist = [];
        if (!empty($list)) {
            foreach ($list as $key => $item) {
                $newlist[] = array(
                    'id' => $item["id"],
                    'call_sid' => $item['call_sid'],
                    'time_add' => date("Y-m-d H:i:s", $item['createtime']),
                    'channel' => 'holly',
                    'TelCenter_Channel' => 'holly',
                    'action' => $item['call_state'] == 'Unlink' ? 'Hangup' : $item['call_state'],
                    'action_name' => VoipCodeEnum::getHollyAction($item['hanguper'], $item['call_state']),
                    'caller' => $item['call_no'],
                    'called' => $item['called_no'],
                    'duration' => $item['call_time_length'],
                    'recordurl' => '',
                    'byetype' => $item['state'],
                    'byetype_name' => VoipCodeEnum::getItem('holly_state', $item['state'], '-')
                );
            }
        }

        return $newlist;
    }

}