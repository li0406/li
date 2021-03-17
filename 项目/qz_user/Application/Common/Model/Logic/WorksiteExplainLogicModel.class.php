<?php

namespace Common\Model\Logic;

class WorksiteExplainLogicModel {

    // 工地直播说明提示会员工公司设置缓存key
    const COMPANY_WORKSITE_EXPLAIN_CACHE_KEY = "PCUSER:WORKSITE:EXPLAIN:DATA";

    /**
     * 获取会员公司设置提示关闭状态
     * @param  [type] $company_id [description]
     * @return [type]             [description]
     */
    public function getExplainStatus($company_id){
        $day = date("Y-m-d");
        $data = S(static::COMPANY_WORKSITE_EXPLAIN_CACHE_KEY);
        if (!empty($data) && array_key_exists($day, $data)) {
            if (in_array($company_id, $data[$day])) {
                return true;
            }
        }

        return false;
    }

    /**
     * 设置会员公司提示关闭状态
     * @param [type] $company_id [description]
     */
    public function setExplain($company_id){
        $data = [];
        $day = date("Y-m-d");
        $cachedata = S(static::COMPANY_WORKSITE_EXPLAIN_CACHE_KEY);
        if (!empty($cachedata) && array_key_exists($day, $cachedata)) {
            $data[$day] = $cachedata[$day];
        }

        $data[$day][] = $company_id;
        $data[$day] = array_filter(array_unique($data[$day]));
        S(static::COMPANY_WORKSITE_EXPLAIN_CACHE_KEY, $data, 86400);
    }

}