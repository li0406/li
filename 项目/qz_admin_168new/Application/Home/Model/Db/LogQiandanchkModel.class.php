<?php


namespace Home\Model\Db;

use Think\Model;

class LogQiandanchkModel extends Model
{
    /**
     * 增加签单日志
     * @param str $type   签单类型如 fenpei zixun
     * @param int $orderid 订单号
     * @param array $data   数组
     * @return
     */
    public function add_log($type, $orderid, $action, $data='')
    {
        if (!empty($type) && !empty($orderid) && !empty($action)) {
            $data_log = array();
            $data_log['type'] = $type; //类型
            $data_log['orderid'] = $orderid; //订单号
            $data_log['action'] = $action; //订单号
            if (!empty($data)) {
                $data_log['orign_data'] = json_encode($data);
            }
            $data_log['opid']   = session('uc_userinfo.id');
            $data_log['opname'] = session('uc_userinfo.name');
            $data_log['optime'] = time();
            return M('log_qiandanchk')->add($data_log);
        }else{
            return false;
        }
    }
}
