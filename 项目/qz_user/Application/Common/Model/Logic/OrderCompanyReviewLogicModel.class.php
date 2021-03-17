<?php

namespace Common\Model\Logic;

use Common\Enums\LiangFangInfo;
use Common\Enums\OrderStatus;
use Common\Model\Db\LogUserOrderChangeModel;
use Think\Exception;

class OrderCompanyReviewLogicModel
{

    /**
     * 取消签单状态
     * author: mcj
     */
    //todo 代码复用性需要调整
    public function removeQianDan($id, $user)
    {
        //查询该订单的分配信息
        //查询该订单信息
        $result = D("Orders")->getOrderInfoById($id);
        if (count($result) > 0) {
            if ($result["qiandan_companyid"] == $user["id"]) {
                //把签单数据重置为空
                $data = array(
                    "qiandan_companyid" => '', //签单公司 id 号
                    "qiandan_mianji" => '', // 签单面积
                    "qiandan_jine" => '', //签单金额
                    "qiandan_status" => null, //状态 默认空 0为申请签单确认 1为确认签单
                    "qiandan_addtime" => '', //签单申请时间
                    "qiandan_chktime" => '', //签单审核时间(签单或取消)
                    "qiandan_remark" => '', //签单备注
                    "qiandan_remark_lasttime" => '', //最后修改签单备注的时间
                    "qiandan_info" => '', //签单信息, 由装修公司填写
                );

                $i = D("Orders")->editOrder($id, $data);
                if ($i !== false) {
                    //导入扩展文件
                    import('Library.Org.Util.App');
                    $app = new \App();
                    unset($data);
                    //记录日志操作日志
                    $data = array(
                        "username" => $user["jc"],
                        "userid" => $user["id"],
                        "ip" => $app->get_client_ip(),
                        "user_agent" => $_SERVER["HTTP_USER_AGENT"],
                        "info" => "用户签单取消 成功",
                        "time" => date("Y-m-d H:i:s"),
                        "action" => CONTROLLER_NAME . "/" . ACTION_NAME,
                        "remark" => "订单号:" . $id
                    );
                    D("Loguser")->addLog($data);

                    //记录历史已签单日志
                    if ($result["qiandan_status"] == 1) {
                        //记录订单已签单信息
                        $lastdata = array(
                            'orderid' => $result['id'], //订单号
                            "qiandan_companyid" => $result['qiandan_companyid'], //签单公司 id 号
                            "qiandan_mianji" => $result['qiandan_mianji'], // 签单面积
                            "qiandan_jine" => $result['qiandan_jine'], //签单金额
                            "qiandan_status" => $result['qiandan_mianqiandan_statusji'], //状态 默认空 0为申请签单确认 1为确认签单
                            "qiandan_addtime" => $result['qiandan_addtime'], //签单申请时间
                            "qiandan_chktime" => $result['qiandan_chktime'], //签单审核时间(签单或取消)
                            "qiandan_remark" => $result['qiandan_remark'], //签单备注
                            "qiandan_remark_lasttime" => $result['qiandan_remark_lasttime'], //最后修改签单备注的时间
                            "qiandan_info" => $result['qiandan_info'], //签单信息, 由装修公司填写
                        );
                        $datalo = array(
                            "username" => $user["jc"],
                            "userid" => $user["id"],
                            "ip" => $app->get_client_ip(),
                            "user_agent" => $_SERVER["HTTP_USER_AGENT"],
                            "info" => json_encode($lastdata),
                            "time" => date("Y-m-d H:i:s"),
                            "action" => CONTROLLER_NAME . "/" . ACTION_NAME,
                            "remark" => 'order_unqiandan'
                        );
                        D("Loguser")->addLog($datalo);
                    }
                }

            }
        }
    }

    /**
     * 我的订单，订单状态接口数据验证
     * author: mcj
     * @param $data
     * @return array
     */
    public function changeStateCompleteVail($data)
    {
        if (empty($data['id']) || !is_numeric($data['id'])) {
            return ['res' => false, 'msg' => '订单号格式错误'];
        }
        if (!isset($data['state']) || !key_exists($data['state'], OrderStatus::getAllStatus())) {
            return ['res' => false, 'msg' => '订单状态异常'];
        }
        if ($data['state'] == OrderStatus::NOT_LIANG_FANG) {
            if (empty($data['reason']) || !$this->_reasonVail($data['reason'])) {
                return ['res' => false, 'msg' => '未量房原因异常'];
            }
        }
        if ($data['state'] == OrderStatus::HAS_SIGN) {
            if (empty($data['qiandan_jine']) || !preg_match('/^\d+\.{0,1}\d{0,2}$/', $data['qiandan_jine'])) {
                return ['res' => false, 'msg' => '签单参数异常'];
            }
        }
        return ['res' => true, 'msg' => '验证通过'];
    }

    protected function _reasonVail($data)
    {
        if (!is_array($data)) {
            return false;
        }
        $reason_arr = LiangFangInfo::getAllFalseReason();
        foreach ($data as $v) {
            if (!key_exists($v, $reason_arr)) {
                return false;
            }
        }
        return true;
    }


    /**
     * 装修公司操作订单状态日志
     * author: mcj
     * @param $order_id
     * @param $user
     * @param string $type
     * @throws Exception
     */

    public function OrderChangeLog($order_id, $user, $type = '', $module = '')
    {
        if ($type == '') {
            throw new Exception('装修公司修改订单，记录类型日志异常');
        }
        if ($module == '') {
            throw new Exception('装修公司修改订单，记录模块日志异常');
        }
        $module_name = ($module == 1) ? '我的订单模块' : '二次回访模块';
        switch ($type) {
            case 1:
                $info = $user["id"] . "编辑订单【" . $order_id . "】已量房信息状态";
                break;
            case 2:
                $info = $user["id"] . "编辑订单【" . $order_id . "】已见面信息状态";
                break;
            case 3:
                $info = $user["id"] . "编辑订单【" . $order_id . "】未量房信息状态";
                break;
            case 4:
                $info = $user["id"] . "编辑订单【" . $order_id . "】已签单信息状态";
                break;
            case 99:
                $info = $user["id"] . "查看订单详细信息";
                break;
        }
        import('Library.Org.Util.App');
        $app = new \App();
        $data = array(
            "username" => $user["jc"],
            "userid" => $user["id"],
            "ip" => $app->get_client_ip(),
            "user_agent" => $_SERVER["HTTP_USER_AGENT"],
            "info" => $module_name . $info,
            "time" => date("Y-m-d H:i:s"),
            "action" => CONTROLLER_NAME . "/" . ACTION_NAME,
            "remark" => "订单号: " . $order_id
        );
        //记录查看订单情况
        return D("Loguser")->addLog($data);
    }


    public function replaceReview($data, $user)
    {
        $info = D("Common/Db/OrderCompanyReview")->getReviewInfo($data["id"], $user["id"]);
        
        // 如果记录不存在则新增一条 2020/04/01
        if (empty($info)) {
            D("Common/Db/OrderCompanyReview")->addReview([
                    "orderid" => $data["id"],
                    "comid" => $user["id"],
                    "time" => time()
                ]);

            $info = D("Common/Db/OrderCompanyReview")->getReviewInfo($data["id"], $user["id"]);
        }

        $record = [
            'time' => time()
        ];

        // 当量房状态从“请选择”、“未量房”更改为“已量房”、“已到店/已见面”时，记录第一次量房时间
        $lfstate = [OrderStatus::HAS_LIANG_FANG, OrderStatus::HAS_MEET];
        $nolfstate = [OrderStatus::ORDER_DEFAULT, OrderStatus::NOT_LIANG_FANG];
        if (in_array($info["status"], $nolfstate) && in_array($data["state"], $lfstate)) {
            $record["lf_time"] = time();
        }

		if($data['state'] == OrderStatus::HAS_LIANG_FANG){
			$record['vip_channel'] = 2;
		}
        if (!empty($data['id'])) {
            $record['orderid'] = $data['id'];
        }
        // if (!empty($data['state'])) {
            $record['status'] = $data['state'];
        // }
        if (!empty($data['reason'])) {
            $record['reason'] = implode(',', $data['reason']);
        }
        if (!empty($data['remark'])) {
            $record['remark'] = $data['remark'];
        }
        return  D("Common/Db/OrderCompanyReview")->editReview($data['id'], $user['id'],$record);
    }

    /**
     * 添加跟踪信息
     * author: mcj
     * @param $data
     * @return mixed
     */

    public function addReview($data)
    {
		$data['time'] = time();
		if($data['status'] == OrderStatus::HAS_LIANG_FANG){
			$data['lf_time'] = time();
		}
        return D("Common/Db/OrderCompanyReview")->addReview($data);
    }

    /**
     * 根据订单号获取跟踪信息
     * @param  [type] $order_id [description]
     * @return [type]           [description]
     */
    public function getInfo($order_id, $company_id)
    {
        return D("Common/Db/OrderCompanyReview")->getReviewInfo($order_id, $company_id);
    }

    /**
     * 编辑跟踪信息
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function editReview($id,$company_id,$data)
    {
        $info = D("Common/Db/OrderCompanyReview")->getReviewInfo($id, $company_id);

        $lfstate = [OrderStatus::HAS_LIANG_FANG, OrderStatus::HAS_MEET];
        $nolfstate = [OrderStatus::ORDER_DEFAULT, OrderStatus::NOT_LIANG_FANG];
        if (in_array($info["status"], $nolfstate) && in_array($data["state"], $lfstate)) {
            $data["lf_time"] = time();
        }

		$data['time'] = time();
        return D("Common/Db/OrderCompanyReview")->editReview($id,$company_id,$data);
    }

    /**
     * 装修公司操作订单状态日志
     * @param $order_id
     * @param $user
     * @param string $type
     * @param int $log_id
     * @throws Exception
     */
    public function saveCompanyOrderChangeLog($order_id, $user, $type = '', $log_id = 0)
    {
        $save = [
            'order_id'=>$order_id,
            'company_id'=>$user['id'],
            'status'=>$type?:0,
            'log_id'=>$log_id?:0,
            'created_at'=>time(),
        ];
        switch ($type) {
            case 1:
                $save['remark'] = $user["jc"] . "编辑订单【" . $order_id . "】已量房信息状态";
                break;
            case 2:
                $save['remark'] = $user["jc"] . "编辑订单【" . $order_id . "】已见面信息状态";
                break;
            case 3:
                $save['remark'] = $user["jc"] . "编辑订单【" . $order_id . "】未量房信息状态";
                break;
            case 4:
                $save['remark'] = $user["jc"] . "编辑订单【" . $order_id . "】已签单信息状态";
                break;
            case 99:
                $save['remark'] = $user["jc"] . "查看订单详细信息";
                break;
        }

        $logChangeDb = new LogUserOrderChangeModel();
        return $logChangeDb->saveOrderChange($save);
    }
}