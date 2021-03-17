<?php
// +----------------------------------------------------------------------
// | SmsPushLogicModel
// +----------------------------------------------------------------------

namespace Common\Model\Logic;


use Common\Model\Db\JiajuZxSmsPushLogModel;

class SmsPushLogicModel
{
    /**
     * 修改日志信息
     * @param $id
     * @return bool
     */
    public function setSmsPushLogStatus($id)
    {
        $pushModel = new JiajuZxSmsPushLogModel();
        return $pushModel->editLog(['is_used' => 2],['id' => $id]);
    }

    /**
     * 查询推送日志信息
     * @param string $order_id 装修编号
     * @param string $url      路径地址
     * @return mixed
     */
    public function findSmsPushLog($order_id, $url = '')
    {
        $map['zx_order_id'] = $order_id;
        if (!empty($url)) {
            $map['url_long'] = $url;
        }
        $pushModel = new JiajuZxSmsPushLogModel();
        return $pushModel->getOne($map);
    }
}