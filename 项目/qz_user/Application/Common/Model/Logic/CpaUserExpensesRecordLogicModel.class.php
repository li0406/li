<?php
/**
 * 家具商用户花费信息
 */

namespace Common\Model\Logic;

class CpaUserExpensesRecordLogicModel
{

    /**
     * 根据用户ID获取用户花费
     * @param $uid
     * @param $time
     * @return int|$this|array|\PDOStatement|string|\think\Collection
     */
    public function getUserSumBill($uid ,$time)
    {
        if (empty($uid)) {
            return ['recharge' => 0, 'consume' => 0, 'refund' => 0];
        }
        $map['user_id'] = [ 'EQ', $uid];
        if (!empty($time)) {
            $map['create_time'] = [ 'between', $time];
        }
        $recharge = D('Common/Db/CpaUserExpensesRecord')->SumBill(array_merge($map, ['type'=>['EQ', 1]]));
        $consume = D('Common/Db/CpaUserExpensesRecord')->SumBill(array_merge($map, ['type'=>['EQ', 2]]));
        $refund = D('Common/Db/CpaUserExpensesRecord')->SumBill(array_merge($map, ['type'=>['EQ', 3]]));
        return ['recharge' => $recharge, 'consume' => $consume, 'refund' => $refund];
    }

    /**
     * 根据用户ID和筛选时间段获取用户账单
     * @param $uid
     * @param $time
     * @param $page
     * @param $pageSize
     * @return $this|array|\PDOStatement|string
     */
    public function getUserExpensesRecord($uid ="", $time ="", $page = 0, $pageSize = 20, $type = 0)
    {
        if (empty($uid)) {
            return null;
        }
        $map['user_id'] = [ 'EQ', $uid];
        if (!empty($type)) {
            $map['type'] =  [ 'EQ', $type];
        }
        if (!empty($time)) {
            $map['create_time'] = [ 'between', $time];
        }
        return D('Common/Db/CpaUserExpensesRecord')->getDataList($map, $page, $pageSize);
    }

    /**
     * 根据用户ID和筛选时间段获取用户账单总记录数
     * @param $uid
     * @param $time
     * @param $page
     * @param $pageSize
     * @return $this|array|\PDOStatement|string
     */
    public function getUserExpensesRecordCount($uid, $time, $type = 0)
    {
        if (empty($uid)) {
            return null;
        }
        $map['user_id'] = [ 'EQ', $uid];
        if (!empty($type)) {
            $map['type'] =  [ 'EQ', $type];
        }
        if (!empty($time)) {
            $map['create_time'] = [ 'between', $time];
        }
        return D('Common/Db/CpaUserExpensesRecord')->getDataListCount($map);
    }


    public function getUserExpensesRecordByPage($uid, $time, $page, $pageSize)
    {
        $list = [];
        $count = $this->getUserExpensesRecordCount($uid, $time);
        if ($count > 0) {
            import('Library.Org.Page.Page');//导入分页类
            //自定义配置项
            $config  = array("prev","next");
            $page = new \Page($page,$pageSize,$count,$config);//实例化分页类
            $pageTmp =  $page->show();
            $list = $this->getUserExpensesRecord($uid, $time, $page, $pageSize);
            return array("list" => $list,"page" => $pageTmp);
        }
        return array("list" => $list,"page" => '');
    }

}