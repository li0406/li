<?php
// +----------------------------------------------------------------------
// | LogQiandanchk
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\db\Query;
use think\db\Where;
use think\Model;

class LogQiandanchk extends Model
{
    protected $autoWriteTimestamp = false;
    protected $table = 'qz_log_qiandanchk';

    /**
     * 获取最新一条签单日志
     *
     * @param $orderid
     * @param string $value
     * @return array|mixed|null|\PDOStatement|string|Model
     */
    public function getLastLog($orderid, $value = '')
    {
        $map['orderid'] = $orderid;
        if ($value) {
            return self::where(new Where($map))->order('optime desc')->value($value);
        }
        return self::where(new Where($map))->order('optime desc')->find();
    }

    /**
     * 增加签单日志
     *
     * @param string $type 签单类型如 fenpei zixun
     * @param int $orderid 订单号
     * @param array $data 数组
     * @return mixed
     */
    public function addLog($type, $orderid, $action, $data = [])
    {
        if (!empty($type) && !empty($orderid) && !empty($action)) {
            $data_log['type'] = $type;
            $data_log['orderid'] = $orderid;
            $data_log['action'] = $action;
            if (!empty($data)) {
                $data_log['orign_data'] = json_encode($data, 320);
            }
            $data_log['opid'] = request()->user['user_id'];
            $data_log['opname'] = request()->user['user_name'];
            $data_log['optime'] = time();
            return self::insert($data_log);
        } else {
            return false;
        }
    }

    public function getLogCount($order_id){
        $where = $this->getLogMap($order_id);
        return $this->where($where)->count();
    }

    public function getLogList($order_id, $offset, $limit)
    {
        $where = $this->getLogMap($order_id);
        return $this->where($where)
            ->order("id desc")
            ->limit($offset, $limit)
            ->select();
    }

    public function getLogMap($order_id)
    {
        $query = new Query();
        $query->where("type", "fenpei");
        $query->where("action", "qd_chkc");
        if (!empty($order_id)) {
            $query->where("orderid", $order_id);
        }
        return $query;
    }
}