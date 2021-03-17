<?php

namespace Home\Model\Logic;

use Common\Enums\ApiConfig;
use Home\Model\Db\ScoreSignRuleModel;
use Think\Exception;

class ScoreSignRuleLogicModel
{
    protected $scoreSignRuleModel;

    protected $scoreSignRuleListLogic;

    protected $perCount = 20;

    protected $status = [
        -1 => '无效',
        1 => '有效',
    ];

    public function __construct()
    {
        $this->scoreSignRuleModel = new ScoreSignRuleModel();
        $this->scoreSignRuleListLogic = new ScoreSignRuleListLogicModel();
    }

    public function add(array $data)
    {
        $formatted = [
            'type' => $data['type'] ?: 1,
            'name' => $data['name'] ?: '',
            'circle' => (int)$data['circle'],
            'score' => (int)$data['score'],
            'start_time' => $data['start_time'],
            'created_at' => time(),
            'updated_at' => time(),
        ];
        $ret = $this->scoreSignRuleModel->add($formatted);
        return $ret;
    }

    /**
     * 编辑
     * @param $ruleID
     * @param array $data
     * @return mixed
     */
    public function edit($ruleID, array $data)
    {
        $formatted = [
            'type' => $data['type'] ?: 1,
            'name' => $data['name'] ?: '',
            'circle' => (int)$data['circle'],
            'score' => (int)$data['score'],
            'start_time' => $data['start_time'],
            'created_at' => time(),
            'updated_at' => time(),
        ];
        $map['id'] = ['eq', $ruleID];
        $ret = $this->scoreSignRuleModel->where($map)->save($formatted);
        return $ret;
    }

    /**
     * 保存数据
     * @param array $ruleData
     * @param $ruleListData
     * @throws Exception
     */
    public function saveData(array $ruleData, $ruleListData)
    {
        try {
            M()->startTrans();

            //获取总天数
            $ruleData['circle'] = count($ruleListData);
            //总分数
            $ruleData['score'] = array_sum(array_column($ruleListData, 'common_score')) + array_sum(array_column($ruleListData, 'extra_score'));
            //1.保存规则
            $newSignRuleId = $this->add($ruleData);
            if (empty($newSignRuleId)) {
                throw new Exception('保存规则失败');
            }

            //2.保存规则列表
            $ret = $this->scoreSignRuleListLogic->addData($newSignRuleId, $ruleListData);
            if (empty($ret)) {
                throw new Exception('保存规则列表失败');
            }

            M()->commit();
        } catch (Exception $e) {
            M()->rollback();
            throw $e;
        }
    }

    /**
     * 编辑数据
     * @param int $ruleID
     * @param array $ruleData
     * @param $ruleListData
     * @throws Exception
     */
    public function editData($ruleID, array $ruleData, $ruleListData)
    {
        try {
            M()->startTrans();

            //获取总天数
            $ruleData['circle'] = count($ruleListData);
            //总分数
            $ruleData['score'] = (int)array_sum(array_column($ruleListData, 'common_score')) + (int)array_sum(array_column($ruleListData, 'extra_score'));
            //1.保存规则
            $editRet = $this->edit($ruleID, $ruleData);
            if (empty($editRet)) {
                throw new Exception('保存规则失败');
            }

            //2.清除
            $delHistoryRule = $this->scoreSignRuleListLogic->drop($ruleID);

            if (empty($delHistoryRule)) {
                throw new Exception('保存规则失败');
            }

            //3.保存规则列表
            $ret = $this->scoreSignRuleListLogic->addData($ruleID, $ruleListData);
            if (empty($ret)) {
                throw new Exception('保存规则列表失败');
            }

            M()->commit();
        } catch (Exception $e) {
            M()->rollback();
            throw $e;
        }
    }

    public function transformData(array $data)
    {
        if (empty($data)) {
            return [];
        }
        //根据开始时间和周期判断当前的规则是否有效
        //开始时间>=当前时间的循环周期
        $data['start_time'] = $data['start_time'] ? date('Y-m-d', $data['start_time']) : '暂未设置';
        $data['status_name'] = $this->status[$data['status']];
        return $data;
    }

    /**
     * 获取列表数据
     * @param $type
     * @return array
     */
    public function getList($type)
    {
        $ret = [
            'page' => '',
            'data' => [],
        ];
        $map['type'] = $type;
        $count = $this->scoreSignRuleModel->getCount($map);
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $page = new \Page($count, $this->perCount);
            $show = $page->show();
            $data = $this->scoreSignRuleModel->getList($map, $page->firstRow, $page->listRows);
            foreach ($data as $key => $value) {
                $data[$key] = $this->transformData($value);
            }
            $ret['page'] = $show;
            $ret['data'] = $data;
        }
        return $ret;
    }

    /**
     * 开始时间是否已存在
     * @param $type
     * @param $startTime
     * @return int
     */
    public function hasStartTime($type, $startTime)
    {
        $map['type'] = ['eq', $type];
        $map['start_time'] = ['eq', $startTime];
        return $this->scoreSignRuleModel->has($map);
    }

    public function getEnableRule($type)
    {
        $map['type'] = ['eq', $type];
        $map['status'] = ['eq', 1];
        return $this->scoreSignRuleModel->getEnableRule($map);
    }


    /**
     * 开始时间是否已存在
     * @param $id
     * @param $type
     * @param $startTime
     * @return int
     */
    public function hasStartTimeExceptId($id, $type, $startTime)
    {
        $map['id'] = ['neq', $id];
        $map['type'] = ['eq', $type];
        $map['start_time'] = ['eq', $startTime];
        return $this->scoreSignRuleModel->has($map);
    }

    /**
     * 名称是否存在
     * @param $type
     * @param $name
     * @return int
     */
    public function hasName($type, $name)
    {
        $map['type'] = ['eq', $type];
        $map['name'] = ['eq', $name];
        return $this->scoreSignRuleModel->has($map);
    }

    /**
     * 名称是否存在
     * @param $id
     * @param $type
     * @param $name
     * @return int
     */
    public function hasNameExceptId($id, $type, $name)
    {
        $map['id'] = ['neq', $id];
        $map['type'] = ['eq', $type];
        $map['name'] = ['eq', $name];
        return $this->scoreSignRuleModel->has($map);
    }

    public function hasEnableRule($id)
    {
        $map['id'] = ['eq', $id];
        $map['status'] = ['eq', 1];
        return $this->scoreSignRuleModel->has($map);
    }

    /**
     * 获取详情
     * @param $id
     * @return array|mixed
     */
    public function getDetail($id)
    {
        $data = $this->scoreSignRuleModel->getDetail($id);
        if (!empty($data)) {
            $data = $this->transformData($data);
        }
        //获取规则列表
        $data['rules'] = $this->scoreSignRuleListLogic->getList($id);
        return $data;
    }

    /**
     * 格式化导入的数据
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function formatImportSignRule(array $data)
    {
        try {
            $formatted = [];

            for ($i = 1; $i < count($data); $i++) {
                $line = $i +1;
                if (empty($data[$i])) {
                    throw new Exception('导入失败，规则数据不能为空', ApiConfig::PARAMETER_MISSING);
                }
                $day = $data[$i][0];
                $score = $data[$i][1];
                $extraScore = $data[$i][2];

                if (empty($day) || empty($score)) {
                    throw new Exception("导入失败，第{$line}行数据格式不正确", ApiConfig::PARAMETER_MISSING);
                }
                if (!is_numeric($day) || !is_numeric($score) || ($extraScore && !is_numeric($extraScore))) {
                    throw new Exception("导入失败，第{$line}行数据格式不正确", ApiConfig::PARAMETER_MISSING);
                }

                if ($day < 0 || $score < 0 || ($extraScore && $extraScore < 0)) {
                    throw new Exception("导入失败，第{$line}行数据格式不正确", ApiConfig::PARAMETER_MISSING);
                }
                if ($i != $day) {
                    throw new Exception("导入失败，第{$line}行数据格式不正确", ApiConfig::PARAMETER_MISSING);
                }
                $formatted[] = [
                    'common_score' => $score,
                    'extra_score' => $extraScore,
                ];
            }
            return $formatted;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * 删除签到规则
     * @param int $ruleId
     * @throws Exception
     */
    public function delSignRule($ruleId)
    {
        try {
            M()->startTrans();
            $delRule = $this->scoreSignRuleModel->drop($ruleId);
            if (empty($delRule)) {
                throw new Exception('删除失败', ApiConfig::DELETE_FALE);
            }

            $delRuleList = $this->scoreSignRuleListLogic->drop($ruleId);
            if (empty($delRuleList)) {
                throw new Exception('删除失败', ApiConfig::DELETE_FALE);
            }
            M()->commit();
        } catch (Exception $e) {
            M()->rollback();
            throw $e;
        }
    }

}