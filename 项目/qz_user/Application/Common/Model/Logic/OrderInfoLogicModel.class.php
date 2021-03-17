<?php

namespace Common\Model\Logic;

use Think\Exception;

class OrderInfoLogicModel
{

    /**
     * 获取装修公司需要提示的订单
     * 当前产品要求提示时间为15天，此需求以后一定会变动，改成变量控制--15天
     * author: mcj
     * @param int $company_id
     * @return mixed
     * @throws \Think\Exception
     */

    public function getRemindOrder($company_id = 0)
    {
        if (empty($company_id)) {
            throw new Exception('获取装修公司需要提示的订单，参数异常');
        }
        $map = [
            'a.com' => $company_id,
            'b.`on`'=>['EQ',4]
        ];
        return D("Common/Db/OrderInfo")->getRemindOrder($map, 15);
    }

    public function getNeedToDo($company_id = 0)
    {
        if (empty($company_id)) {
            throw new Exception('获取装修公司未处理订单，参数异常');
        }
        $map = [
            'a.com' => $company_id,
            'b.`on`' => ['EQ', 4],
        ];
        $condition ='c.status=0';
        return D("Common/Db/OrderInfo")->getNeedToDo($map,$condition);
    }

    /**
     * 批量搁置订单提醒
     * author: mcj
     * @param array $order_id_array
     * @return mixed
     */
    public function ignoreOrder($order_id_array = [], $company_id)
    {
        $map = [
            'order' => ['IN', $order_id_array],
            'com' => $company_id,
        ];
        $order_info_db = D("Common/Db/OrderInfo");
        $order_info = $order_info_db->getOrder($map);
        foreach ($order_info as $value) {
            $this->makeOrderIgnore($value);
        }
        return true;
    }

    /**
     * 装修公司分单，忽略提醒
     * author: mcj
     * @param $order_info
     */
    public function makeOrderIgnore($order_info)
    {
        $diff_day = day_diff(date('Y-m-d', $order_info['addtime']),date('Y-m-d'));
        $ignore_day = $diff_day + 7;
        $update = [
            'ignore_day' => $ignore_day
        ];
        D("Common/Db/OrderInfo")->updateInfo(['id' => $order_info['id']], $update);
    }
}