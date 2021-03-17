<?php

namespace Home\Model;

use Think\Model;

class KeywordBatchModel extends Model
{
    /**
     * [getKeywordBatchGroupByModule 获取计划任务列表]
     * @return [type] [description]
     */
    public function getKeywordBatchGroupByModule(){
        $build = M('keyword_batch')->field('id,module,status,time,uname,total')->order('time DESC')->buildSql();
        $result = M()->table($build)->alias('w')->group('module')->select();
        return $result;
    }

    /**
     * [addKeywordBatch 新增计划任务]
     * @param [type] $save [description]
     */
    public function addKeywordBatch($save){
        if (!empty($save)) {
            $result = M('keyword_batch')->add($save);
            return $result;
        }
        return false;
    }

    /**
     * [deleteKeywordBatchByModule 根据模块删除计划任务]
     * @param  string $module [description]
     * @return [type]         [description]
     */
    public function deleteKeywordBatchByModule($module = ''){
        if (empty($module)) {
            return false;
        }
        $map = array(
            'module' => $module,
            'status' => 0
        );
        return M("keyword_batch")->where($map)->delete();
    }

    /**
     * [getKeywordBatchListByModule description]
     * @param  string  $module [模块名]
     * @param  integer $start  [开始页]
     * @param  integer $end    [结束页]
     * @param  string  $order  [排序]
     * @return [type]          [description]
     */
    public function getKeywordBatchListByModule($module = '', $start = 0, $end = 1, $order = 'time DESC'){
        if (!empty($module)) {
            $map['module'] = $module;
        }
        $result = M("keyword_batch")->where($map)->order($order)->limit($start, $end)->select();
        return $result;
    }
}