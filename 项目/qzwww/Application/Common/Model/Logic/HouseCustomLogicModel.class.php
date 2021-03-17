<?php

namespace Common\Model\Logic;

use Common\Model\Db\OrdersModel;

class HouseCustomLogicModel
{
    protected $ordersModel;

    protected $wwwArticleLogic;

    protected $wwwArticleClassLogic;

    //发单数
    const REDIS_CREATE_ORDER_COUNT_KEY = 'WWW:QUDZ:CREATED_ORDER';
    //发单数生成的日期
    const REDIS_CREATE_ORDER_COUNT_TIME_KEY = 'WWW:QUDZ:CREATED_ORDER:TIME';

    public function __construct()
    {
        $this->ordersModel = new OrdersModel();
        $this->wwwArticleLogic = new ArticleLogicModel();
        $this->wwwArticleClassLogic = new WwwArticleClassLogicModel();
    }

    //取 订单表中最新的100条数据，根据这些数据获取到城市和区域，在筛选掉，区域中其他的数据，不足的话 用区域是其他的数组补足，最后拿20条数据
    public function getLastAppoint($count)
    {
        $base = [];
        $other = [];
        $data = $this->ordersModel->getLastAppoint($count);
        $name = [
            '赵', '钱', '孙', '李', '陈', '王', '黄', '周', '吴', '张',
        ];
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if ($value['area'] != '其他') {
                    $base[$key]['name'] = $name[mt_rand(0, count($name) - 1)] . '**';
                    $base[$key]['address'] = $value['city'] . ' ' . $value['area'];
//                    $base[$key]['date'] = date('Y-m-d', $value['time_real']);
                    $base[$key]['date'] = date('Y-m-d', time());
                } else {
                    $other[$key]['name'] = $name[mt_rand(0, count($name) - 1)] . '**';
                    $other[$key]['address'] = $value['city'] . ' ' . $value['area'];
//                    $other[$key]['date'] = date('Y-m-d', $value['time_real']);
                    $other[$key]['date'] = date('Y-m-d', time());
                }
            }
        }
        $ret = array_merge($base, $other);
        $ret = array_slice($ret, 0, 20);
        return $ret;
    }

    /**
     * 获取文章列表
     * @param $page
     * @param $params
     * @return array
     */
    public function getNewsList($page, $params)
    {
        $perCount = 10;
        //获取二级分类的数据
        if ($params['level'] == 2) {
            $data = $this->wwwArticleLogic->getListByGrandClassAndParentClass($page, $perCount, $params);
        } else {
            //获取三级分类的数据
            $data = $this->wwwArticleLogic->getListByGrandClassAndClass($page, $perCount, $params);
        }
        return $data;
    }

    /**
     * 根据第一级获取文章分类
     * @param $grandClass
     * @return array|mixed
     */
    public function getArticleCategory($grandClass)
    {
        $data = $this->wwwArticleClassLogic->getArticleClassIdsByClass($grandClass);
        return $data;
    }

    /**
     * 获取发单数
     * @return int|mixed|string
     */
    public function getCreatedOrderCount()
    {
        //发单数，默认1000-2000，每天刷新的时候随机增加50-100
        $orderCount =  S(static::REDIS_CREATE_ORDER_COUNT_KEY);
        $currentDateRedis = S(static::REDIS_CREATE_ORDER_COUNT_TIME_KEY);
        $currentDate = date('Y-m-d');

        if (!$orderCount) {
            $orderCount = mt_rand(1000, 2000);
        }
        //不是当前日期
        if ($currentDateRedis != $currentDate) {
            $orderCount += mt_rand(50, 100);
            S(static::REDIS_CREATE_ORDER_COUNT_TIME_KEY, $currentDate);
        }

        S(static::REDIS_CREATE_ORDER_COUNT_KEY, $orderCount);
        return $orderCount;
    }
}
