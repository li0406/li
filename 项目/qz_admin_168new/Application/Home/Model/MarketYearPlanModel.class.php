<?php

namespace Home\Model;

Use Think\Model;

class MarketYearPlanModel extends Model
{

    /**
     * 批量增加财年计划
     * @param array $save 存储的数组
     * @return  bool 是否插入成功
     */
    public function addAllYearPlan($save = array())
    {
        if (empty($save)) {
            return false;
        }
        return M('market_year_plan')->addAll($save);
    }

    /**
     * 设置财年计划不可用
     * @param integer $plan_year 年份
     * @return  bool 是否设置成功
     */
    public function setYearPlayDisabledByPlanYear($plan_year = 0)
    {
        if (empty($plan_year)) {
            return false;
        }

        $map = array(
            'plan_year' => array('EQ', intval($plan_year)),
            'status' => array('EQ', 1)
        );

        return M('market_year_plan')->where($map)->save(array('status' => 2));
    }

    /**
     * 根据年份获取财年计划
     * @param  integer $plan_year 年份
     * @return array              财年计划数组
     */
    public function getYearPlanByPlanYear($plan_year = 0)
    {
        if (empty($plan_year)) {
            return false;
        }
        $map = array(
            'plan_year' => array('EQ', intval($plan_year)),
            'status' => array('EQ', 1)
        );
        return M('market_year_plan')->where($map)->order('id ASC')->select();
    }


    /**
     * 根据月份获取财年计划
     * @param  string $plan_month 月份
     * @return array              查询列表
     */
    public function getYearPlanByPlanMonth($plan_month = '')
    {
        if (empty($plan_month)) {
            return false;
        }
        $map = array(
            'plan_month' => array('EQ', $plan_month),
            'status' => array('EQ', 1)
        );
        return M('market_year_plan')->where($map)->order('id ASC')->find();
    }

}