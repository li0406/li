<?php
/**
 * 质检系统温馨提示内容
 */
namespace Home\Model;

use Think\Model;

class QcKindlyRemindModel extends Model{

    /**
     * [addQcKindlyRemind 新增温馨提示记录]
     * @param string $save [description]
     */
    public function addQcKindlyRemind($save = '')
    {
        if (empty($save)) {
            return false;
        }
        return M('qc_kindly_remind')->add($save);
    }

    /**
     * [getQcKindlyRemindCount 获取温馨提示记录的数量]
     * @param  integer $id                 [记录ID]
     * @param  string  $content            [记录内容]
     * @param  string  $publish_time_start [发布开始时间]
     * @param  string  $publish_time_end   [发布结束时间]
     * @return [type]                      [description]
     */
    public function getQcKindlyRemindCount($id = 0, $content = '', $publish_time_start = '', $publish_time_end = '')
    {
        //编号筛选
        if (!empty($id)) {
            $map['id'] = $id;
        }

        //温馨提示内容
        if (!empty($content)) {
            $map['content'] = array('LIKE', '%' . $content . '%');
        }

        //发布时间筛选
        if (!empty($publish_time_start) && empty($publish_time_end)) {
            $map['publish_time'] = array('EGT', $publish_time_start);
        }else if (!empty($publish_time_end) && empty($publish_time_start)) {
            $map['publish_time'] = array('ELT', $publish_time_end);
        } else if (!empty($publish_time_start) && !empty($publish_time_end)) {
            $map['publish_time'] = array(
                array('EGT', $publish_time_start),
                array('ELT', $publish_time_end)
            );
        }

        return M('qc_kindly_remind')->where($map)->count();
    }

    /**
     * [getQcKindlyRemindList 获取温馨提示记录列表]
     * @param  integer $id                 [记录ID]
     * @param  string  $content            [记录内容]
     * @param  string  $publish_time_start [发布开始时间]
     * @param  string  $publish_time_end   [发布结束时间]
     * @return [type]                      [description]
     */
    public function getQcKindlyRemindList($id = 0, $content = '', $publish_time_start = '', $publish_time_end = '', $start, $end)
    {
        //编号筛选
        if (!empty($id)) {
            $map['id'] = $id;
        }

        //温馨提示内容
        if (!empty($content)) {
            $map['content'] = array('LIKE', '%' . $content . '%');
        }

        //发布时间筛选
        if (!empty($publish_time_start) && empty($publish_time_end)) {
            $map['publish_time'] = array('EGT', $publish_time_start);
        }else if (!empty($publish_time_end) && empty($publish_time_start)) {
            $map['publish_time'] = array('ELT', $publish_time_end);
        } else if (!empty($publish_time_start) && !empty($publish_time_end)) {
            $map['publish_time'] = array(
                array('EGT', $publish_time_start),
                array('ELT', $publish_time_end)
            );
        }

        //限制默认每页显示数量
        $start = intval($start);
        $end = intval($end);

        return M('qc_kindly_remind')->where($map)->order('id DESC')->limit($start, $end)->select();
    }

    /**
     * 获取最新的温馨提示
     * @return [type] [description]
     */
    public function getNewKindlyRemindInfo()
    {
        return M('qc_kindly_remind')->order("id desc")->find();
    }
}