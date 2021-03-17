<?php
/**
 * Created by PhpStorm.
 * author: mcj
 * Date: 2018/8/22
 * Time: 13:16
 * 未量房订单/二次回访订单
 */

namespace Common\Model\Logic;

use Common\Enums\OrderStatus;

class CompanyLiangFangCorrLogicModel
{


    public function countTwiceBackSimple($order_id,$user)
    {
        if (empty($order_id)) {
            throw new Exception('二次回访记录，查询参数非法');
        }
        $map = [
            'order_id' => $order_id,
            'company_id' => $user['id'],
            'status' => ['EQ',0],

        ];
        return D("Common/Db/CompanyLiangFangCorr")->countTwiceBackSimple($map);
    }

    public function updateCorr($data, $user)
    {
        $record['status'] = $data['state'];
		$record['time'] = time();
		if($data['state'] == OrderStatus::HAS_LIANG_FANG){
			$record['lf_time'] = time();
		}
        D("Common/Db/OrderCompanyReview")->editReview($data['id'],$user['id'], $record);
        if (!empty($data['reason'])) {
            $update['reason'] = implode(',', $data['reason']);
            $map = [
                'comid' => $user['id'],
                'order_id' => $data['id'],
            ];
            if (!empty($data['remark'])) {
                $update['remark'] = $data['remark'];
            }
            D("Common/Db/CompanyLiangFangCorr")->updateCorr($map, $update);
        }
        return true;
    }

    /**
     * 获取二次回访记录条数
     * author: mcj
     * @param $company_id
     * @param $search
     * @param $isread
     * @return mixed
     */
    public function getBackReplyCount($company_id, $search, $isread)
    {
        $map = $this->_getReplyBackMap($company_id, $search, $isread);
        return D("Common/Db/CompanyLiangFangCorr")->getBackReplyCount($map);
    }

    /**
     * 获取二次回访记录
     * author: mcj
     * @param $company_id
     * @param $search
     * @param $isread
     * @param int $p
     * @param int $p_size
     * @return int
     */
    public function getBackReply($company_id, $search, $isread, $p = 1, $p_size = 20)
    {
        $map = $this->_getReplyBackMap($company_id, $search, $isread);
        $skip = ($p - 1) * $p_size;
        return D("Common/Db/CompanyLiangFangCorr")->getBackReply($map, $skip, $p_size);
    }

    /**
     * 获取未读订单数量
     * author: mcj
     * @param $order_id
     * @param $company_id
     * @return mixed
     */
    public function getOrderReadCount($order_id, $company_id)
    {
        $map = array(
            "order_id" => array("EQ", $order_id),
            "company_id" => array("EQ", $company_id),
            "is_read" => array("EQ", 1)
        );
        return D("Common/Db/CompanyLiangFangCorr")->getBackReplyCountSimple($map);
    }


    /**
     * 修改二次回访推送记录，为已读
     * author: mcj
     * @param $order_id
     * @param $company_id
     * @return mixed
     */
    public function editOrderRead($order_id, $company_id)
    {
        $map = [
            'order_id' => $order_id,
            'company_id' => $company_id,
        ];
        $update = ['is_read' => 2];
        return D("Common/Db/CompanyLiangFangCorr")->updateCorr($map, $update);
    }

    /**
     * 根据条件设置二次回访查询条件数组
     * author: mcj
     * @param $company_id
     * @param $search
     * @param $isread
     * @return array
     */
    protected function _getReplyBackMap($company_id, $search, $isread)
    {
        $map = array(
            't.company_id' => array('EQ', $company_id),
            't.status' => array('EQ', 0),
            'b.on' => array('EQ', 4)
        );
        if (!empty($search)) {
            import('Library.Org.Util.App');
            $app = new \App();
            $map['_complex'] = array(
                'b.xiaoqu' => array('LIKE', "%$search%"),
                "b.tel_encrypt"=>array("EQ",$app->order_tel_encrypt($search)),
                'b.id' => array('LIKE', "%$search%"),
                '_logic' => 'OR'
            );
        }
        if ($isread !== "" && $isread !== null) {
            $map["t.is_read"] = array("EQ", $isread);
        }
        return $map;
    }

}