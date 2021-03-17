<?php
namespace Home\Model;
Use Think\Model;

/**
*   订单电话日志表
*/
class LogTelcenterOrdercallModel extends Model
{
    /**
     * 获取订单最后一次呼叫信息
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function findLastTelInfo($id)
    {
        $map = array(
            "orderid" => array("EQ",$id)
        );
        return M("log_telcenter_ordercall")->where($map)->order("id desc")->find();
    }

    /**
     * 获取最近一天的电话日志记录
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getOrderLastLogOneDay($orderid,$date)
    {
          $map = array(
                "orderid" => array("EQ",$orderid),
                "time_add" => array("EGT",$date)
          );
          return M("log_telcenter_ordercall")->where($map)->select();
    }

    /**
     * 添加记录
     * @param [type] $data [description]
     */
    public function addLog($data)
    {
        return M('log_telcenter_ordercall')->add($data);
    }

    /**
     * 批量添加记录
     * @param [type] $data [description]
     */
    public function addAllLog($data)
    {
        return M('log_telcenter_ordercall')->addAll($data);
    }

    /**
     * 电话记录日志
     * @param  [type] $orderid [description]
     * @return [type]          [description]
     */
    public function tellCall($orderid)
    {
        $map = array(
            "a.orderid" => array("EQ",$orderid)
        );

        $buildSql =  M('log_telcenter_ordercall a')->where($map)
                                              ->join("join qz_log_telcenter tel on tel.callSid = a.callSid ")
                                              ->field("a.time_add,a.admin_user,tel.byetype,tel.starttime,tel.endtime,a.callSid,a.fromtel,tel.recordurl,tel.action")
                                              ->order("tel.id desc,a.id desc")->buildSql();
        return  M('log_telcenter_ordercall')->alias("t")->table($buildSql)
                                             ->field("t.*,IF(t.action = 'Hangup',1,0) mark")
                                             ->group("t.callSid")->select();
    }

    /**
     * 通过订单号获取 通话次数
     *
     * @param  array $orderIds 订单号
     * @return mixed  通话列表 或 false
     */
    public function getOrderCallCountByOrderIds($orderIds)
    {
        if (!empty($orderIds)) {
            if (!is_array($orderIds)) {
                $orderIds = array($orderIds);
            }
            $map = array(
                'o.orderid' => array('IN', $orderIds)
            );
            $build = M('log_telcenter_ordercall')->alias('o')
                                                 ->field('o.orderid AS id')
                                                 ->join('qz_log_telcenter AS t ON o.callSid = t.callSid')
                                                 ->where($map)
                                                 ->group('t.callSid')
                                                 ->buildSql();
            $result = M()->table($build)->alias('z')->field('z.id, count(*) AS repeat_count')->group('z.id')->select();
            return $result;
        }
        return false;
    }

    /**
     * 通过订单号获取 通话次数
     *
     * @param  array $orderIds 订单号
     * @return mixed  通话列表 或 false
     */
    public function getOrderCallListByOrderId($orderId)
    {
        if (!empty($orderId)) {
                $map = array(
                    'o.orderid' => $orderId
                );
                $result = M('log_telcenter_ordercall')->alias('o')
                                                      ->field('*, o.orderid AS orders_id')
                                                      ->join('qz_log_telcenter AS t ON o.callSid = t.callSid')
                                                      ->where($map)
                                                      ->order('t.time_add')
                                                      ->select();
            return $result;
        }
        return false;
    }

    /**
     * 通过订单号获取 通话记录
     *
     * @param  array $orderIds 订单号
     * @return mixed  通话列表 或 false
     */
    public function getOrderCallListByOrderIds($orderIds)
    {
        if (!empty($orderIds)) {
          if (!is_array($orderIds)) {
              $orderIds = array($orderIds);
          }

          $map = array(
              'o.orderid' => array('IN', $orderIds)
          );

          $result = M('log_telcenter_ordercall')->alias('o')
                                                ->field('o.orderid AS orders_id,t.duration,t.starttime,t.callSid,t.action')
                                                ->join('qz_log_telcenter AS t ON o.callSid = t.callSid')
                                                ->where($map)
                                                ->order('t.time_add desc')
                                                ->select();
            return $result;
        }
        return false;
    }

    /**
     * 通过订单号获取 通话记录次数和最新一次的拨打时间
     *
     * @param   mixed $orderIds 订单号
     * @return  array 通话列表
     */
    public static function getOrderCallCountAndLastTimeByOrderIds($orderIds)
    {
        if (count($orderIds) > 0) {
            $map = array(
                'o.orderid' => array('IN', $orderIds)
            );
            $buildSql = M('log_telcenter_ordercall')
                ->alias('o')
                ->field('o.callSid,o.time_add,o.orderid')
                ->join('qz_log_telcenter AS t ON o.callSid = t.callSid')
                ->where($map)
                ->order('o.time_add desc')
                ->group('o.callSid')
                ->buildSql();
            $map = array(
                'a.order_id' => array('IN', $orderIds)
            );
            $nextSql = M("log_holly_order_telcenter")->where($map)->alias("a")
                                                     ->join("join qz_log_holly_telcenter b on a.call_sid = b.call_sid")
                                                     ->field("a.call_sid as callSid,FROM_UNIXTIME(a.time) as time_add,a.order_id as orderid")
                                                     ->order("a.time desc")
                                                     ->buildSql();

            $nextSql1 = M("log_clink_order_telcenter")->where($map)->alias("a")
                ->join("join qz_log_clink_telcenter b on a.call_sid = b.call_sid")
                ->field("a.call_sid as callSid,FROM_UNIXTIME(a.time) as time_add,a.order_id as orderid")
                ->order("a.time desc")
                ->buildSql();

            $buildSql = M()->table($buildSql)->alias("t")
                           ->union($nextSql)
                           ->union($nextSql1)
                           ->buildSql();

            $buildSql = M()->table($buildSql)
                ->alias('t')
                ->field('t.callSid,t.orderid,t.time_add')
                ->order("t.time_add desc")
                ->buildSql();
            return  M()->table($buildSql)->alias("t")->field("count(t.callSid) as call_repeats,t.orderid,t.time_add")
                      ->group('orderid')->select();
        }
        return [];
    }

    /**
     * 通过订单号获取 通话次数
     *
     * @param  array $orderIds 订单号
     * @return mixed  通话列表 或 false
     */
    public function getBackOrderCallListByOrderId($orderId)
    {
        $data = D('Home/Db/CompanyLiangFangTelBack')->getOrderTelBack($orderId);
        if (!$data['ordercall_id']) {
            return [];
        }
        if (!empty($orderId)) {
            $map = array(
                'o.orderid' => $orderId,
                'o.id' => ['in', $data['ordercall_id']],
            );
            $result = M('log_telcenter_ordercall')->alias('o')
                ->field('*, o.orderid AS orders_id')
                ->join('qz_log_telcenter AS t ON o.callSid = t.callSid')
                ->where($map)
                ->order('t.time_add')
                ->select();
            return $result;
        }
        return false;
    }

    //挂机代码对应的信息，鉴权挂机类型说明
    //@http://docs.yuntongxun.com/index.php/%E9%89%B4%E6%9D%83%E6%8C%82%E6%9C%BA%E7%B1%BB%E5%9E%8B%E8%AF%B4%E6%98%8E
    /**
     * [get_byetype_info 挂机代码对应的信息]
     * @param  [int] $byetype [挂机代码]
     * @return [string]          [信息]
     */
    public function getByeTypeInfo($byetype) {
        $reinfo = '';
        if (empty($byetype)) {
            return "";
        }
        switch ($byetype) {
            case '1':
                $reinfo = "通话中取消回拨、直拨和外呼的正常结束通话";
                break;
            case '2':
                $reinfo = "账户欠费或者设置的通话时间到";
                break;
            case '3':
                $reinfo = "回拨通话中主叫挂断，正常结束通话";
                break;
            case '4':
                $reinfo = "回拨通话中被叫挂断，正常结束通话";
                break;
            case '-1':
                $reinfo = "被叫没有振铃就收到了挂断消息";
                break;
            case '-2':
                $reinfo = "呼叫超时没有接通被挂断";
                break;
            case '-5':
                $reinfo = "被叫通道建立了被挂断";
                break;
            case '-6':
                $reinfo = "系统鉴权失败";
                break;
            case '-7':
                $reinfo = "第三方鉴权失败";
                break;
            case '-8':
                $reinfo = "直拨被叫振铃了挂断";
                break;
            case '-3':
                $reinfo = "回拨主叫接通了主叫挂断";
                break;
            case '-4':
                $reinfo = "回拨主叫通道创建了被挂断";
                break;
            case '-9':
                $reinfo = "回拨被叫振铃了挂断";
                break;
            case '-10':
                $reinfo = "回拨主叫振铃了挂断";
                break;
            default:
                # code...
                break;
        }
        return $reinfo;
    }

    /**
     * 处理通话列表 成人类友好的数据列表
     * 多家电话提供商的通话记录是存在一个表中
     * 多家的通话记录播放是不一样的, 比如容联云通讯是直接给的 url 地址,
     * 而联通云总机需要用callid去调用临时的录音url地址
     * 这个函数做一次数据加工,以供数据展示用
     * @param calllist 结果数组
     * @param dispalyType  列表显示类型      0 显示挂机记录,  1显示所有记录
     */
    public function callistHuman($calllist, $displayType=0)
    {
        foreach ($calllist as $key => &$value) {

            if (substr($value['callsid'],0,3) == 'api') {
                //如果callSid(callid) 是api开头的则认为是 联通云总机的记录
                $value['TelCenter_Channel'] = 'cuct';
            } else if (isset($value['call_time_length'])) {
                $value['TelCenter_Channel'] = 'holly'; //合力亿捷
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

            if ($value['TelCenter_Channel'] == 'holly') {
                //如果渠道是合力忆捷 计算下人类友好的通话时间
                if (!empty($value['call_time_length'])) {
                    $value['call_time_length_human'] =  timediff($value['call_time_length']);
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
     * 通过订单号获取 通话次数
     * @param    $orderIds 订单号数组
     * @return   通话次数 或 false
     */
    public function getOrderCallCountDetailByOrderIds($orderIds)
    {
        if (!empty($orderIds)) {
            if (!is_array($orderIds)) {
                $orderIds = array($orderIds);
            }
            $map = array(
                'orderid' => array('IN', $orderIds)
            );
            $buildSql = M('log_telcenter_ordercall')
                ->field('orderid,count(id) as boda_count,callSid')
                ->where($map)
                ->group('orderid')
                ->buildSql();
            return M('log_telcenter_ordercall')->table($buildSql)->alias('a')
                ->field('a.*,count(if(b.action = "Hangup" AND endtime > 0,1,null)) as botong_count')
                ->join('JOIN qz_log_telcenter b ON a.callSid = b.callSid')
                ->group('b.callSid')
                ->select();
        }
        return false;
    }
}