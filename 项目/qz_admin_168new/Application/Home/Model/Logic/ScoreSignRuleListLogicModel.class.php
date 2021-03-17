<?php

namespace Home\Model\Logic;

use Home\Model\Db\ScoreSignRuleListModel;
use Think\Exception;

class ScoreSignRuleListLogicModel
{
    protected $scoreSignRuleListModel;

    public function __construct()
    {
        $this->scoreSignRuleListModel = new ScoreSignRuleListModel();
    }

    public function getList($ruleID)
    {
        $cond['rule_id'] = $ruleID;
        $ret = $this->scoreSignRuleListModel->getList($cond);
        return $ret;
    }

    /**
     * 根据rule_id 清除积分设置列表
     * @param $ruleID
     * @return mixed
     */
    public function drop($ruleID)
    {
        $cond['rule_id'] = $ruleID;
        $ret = $this->scoreSignRuleListModel->drop($cond);
        return $ret;
    }

    /**
     * 添加列表
     * @param $ruleID
     * @param array $data
     * @return bool|string
     */
    public function addData($ruleID, array $data)
    {
        $formatted = [];
        foreach ($data as $key => $value) {
            $formatted[$key]['rule_id'] = $ruleID;
            $formatted[$key]['circle'] = $key+1;
            $formatted[$key]['common_score'] = (int)$value['common_score'];
            $formatted[$key]['extra_score'] = (int)$value['extra_score'];
            $formatted[$key]['created_at'] = time();
            $formatted[$key]['updated_at'] = time();
        }
        return $this->scoreSignRuleListModel->addAll($formatted);
    }
}