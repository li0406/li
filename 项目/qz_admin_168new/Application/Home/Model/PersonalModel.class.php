<?php
// +----------------------------------------------------------------------
// | PersonalModel 人事模型
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://gitee/honoryao All rights reserved.
// +----------------------------------------------------------------------
// | Author: 姚荣<honoryao@qq.com> 2851986856@qq.com
// +----------------------------------------------------------------------

/**
 * Created by PhpStorm.
 * User: jsb
 * Date: 2018/4/21
 * Time: 15:03
 */

namespace Home\Model;

use Think\Model;

class PersonalModel extends Model
{
    protected $autoCheckFields = false;

    /**
     * 获取部门的角色信息
     * @param $departmentId [string|int|array] 部门id(支持数字，数组，字符串)
     * @return [null|array] [返回null或者部门数量和部门角色]
     */
    public function getDepartmentUidById($departmentId)
    {
        if (empty($departmentId)){
            return null;
        }
        if (is_object($departmentId)){
            return null;
        }
        if (is_int($departmentId)){
            $departmentIds = (string)$departmentId;
        }
        if (is_array($departmentId)){
            $departmentIds = implode(',',$departmentId);
        }

        $map = array(
            "a.id" => ["in",$departmentIds],
            "a.enabled" => 0
        );
        return M("department")->where($map)->alias("a")
            ->join("LEFT JOIN qz_role_department as b on b.department_id = a.id")
            ->field("a.id,a.name,GROUP_CONCAT(b.role_id) as roles")
            ->group("a.id")
            ->find();
    }

    /**
     * 获取人事列表
     * @param  int $departmentId [人事部门ID]
     * @param  boolean $select [是否选择首字母]
     * @param  boolean $isStat [是否包括离职人员]
     * @return [type]          [description]
     */
    public function getPersonalList($departmentId = 11,$select = false,$isStat = false)
    {
        $uidList = $this->getDepartmentUidById($departmentId);
        $where = array(
            "uid" => 8,
            "stat" => 1
        );
        if (!empty($uidList['roles'])){
            $where['uid'] = ['in',$uidList['roles']];
        }

        if ($isStat) {
            unset($where["stat"]);
        }
        $result = M('adminuser')->where($where)->field("`name`,id")->order("uid desc")->select();
        if($select){
            //添加名称首字母
            import('Library.Org.Util.App');
            $app = new \app();
            foreach ($result as $key => $value) {
                $str = $app->getFirstCharter($value["name"]);
                $result[$key]["name"] = $str.' '.$value["name"];
            }
            $result = multi_array_sort($result,'name');
        }
        return $result;
    }

    /**
     * 人事呼叫量统计
     * @param  [type] $ids   [人事ID]
     * @param  [type] $begin [开始时间]
     * @param  [type] $end   [结束时间]
     * @return [type]        [description]
     */
    public function getTelStat($ids,$begin,$end,$group = 'uid,days')
    {
        $sql = 'SELECT t1.uid,t1.uname,
                DATE_FORMAT(t1.time_add,"%Y-%m-%d") AS days,
                COUNT(1) AS count,
                COUNT(if(t1.byetype = 0,1,null)) AS tel_count,
                COUNT(if(t1.byetype = 1,1,null)) AS un_tel_count,
                IFNULL(SUM(duration),0) AS sum_time
                FROM (
                    SELECT a.admin_id as uid,a.admin_user AS uname,a.callSid,t.action,t.starttime,t.endtime,t.byetype,t.time_add,t.duration
                    FROM qz_log_telcenter_diycall a JOIN qz_log_telcenter t ON t.callSid = a.callSid
                    WHERE t.action = "hangup" AND a.admin_id IN ('.$ids.') AND a.time_add >= "'.$begin.'" AND a.time_add <= "'.$end.'" ORDER BY t.callSid
                ) t1 GROUP BY '.$group;
        return $this->db()->query($sql);
    }

    /**
     * 实时获取每天详细数据
     * @param [int]$id 员工id
     * @param [string|array]$day 具体天时间(四种格式 2018-05-05|['2018-05-05','2018-06-01']|2018-01-02~2018-02-03|2018-01-02——2018-02-03)
     * @return [array] 查询结果
     */
    public function getItemList($id, $day, $pageNum, $pageSize = 10)
    {
        $where['t.admin_id'] = $id;
        if (is_array($day)) {
            $where['t.time_add'] = ['between', [$day[0] . ' 00:00:00', $day[1] . ' 23:59:59']];
        } elseif (is_string($day)&& (stripos($day,'——') !==false)) {
            $dayArray = explode('——',$day);
            $where['t.time_add'] = ['between', [$dayArray[0] . ' 00:00:00', $dayArray[1] . ' 23:59:59']];
        }elseif (is_string($day)&& (stripos($day,'~') !==false)) {
            $dayArray = explode('~', $day);
            $where['t.time_add'] = ['between', [$dayArray[0] . ' 00:00:00', $dayArray[1] . ' 23:59:59']];
        }else{
            $where['t.time_add'] = ['between', [$day . ' 00:00:00', $day . ' 23:59:59']];
        }

        $list = M("log_telcenter_diycall")->alias("t")
            ->field("a.id,a.action,a.starttime,a.endtime,TIMESTAMPDIFF(SECOND,a.starttime,a.endtime) AS time_diff,a.byetype,a.duration,a.recordurl,a.caller,a.called,t.admin_id as uid,t.admin_user AS uname,t.callSid,t.time_add")
            ->where($where)
            ->join("LEFT JOIN qz_log_telcenter a ON t.callSid = a.callSid")
            ->page("$pageNum,$pageSize")
            ->select();
        return $list;
    }

    /**
     * 实时获取每天详细数据
     * @param [int]$id 员工id
     * @param [string|array]$day 具体天时间(四种格式 2018-05-05|['2018-05-05','2018-06-01']|2018-01-02~2018-02-03|2018-01-02——2018-02-03)
     * @return [array] 查询结果
     */
    public function getVoiceList($id, $day,$time, $pageNum, $pageSize = 10)
    {
        $where['t.admin_id'] = ['in',(string)$id];
        $where['a.duration'] = ['egt',(string)$time];
        $where['a.action'] = 'Hangup';
        if (is_array($day)) {
            $where['t.time_add'] = ['between', [$day[0] . ' 00:00:00', $day[1] . ' 23:59:59']];
        } elseif (is_string($day)&& (stripos($day,'——') !==false)) {
            $dayArray = explode('——',$day);
            $where['t.time_add'] = ['between', [$dayArray[0] . ' 00:00:00', $dayArray[1] . ' 23:59:59']];
        }elseif (is_string($day)&& (stripos($day,'~') !==false)) {
            $dayArray = explode('~', $day);
            $where['t.time_add'] = ['between', [$dayArray[0] . ' 00:00:00', $dayArray[1] . ' 23:59:59']];
        }else{
            $where['t.time_add'] = ['between', [$day . ' 00:00:00', $day . ' 23:59:59']];
        }

        $buildSql = M("log_telcenter_diycall")->alias("t")
            ->field("a.id,a.action,a.starttime,a.endtime,TIMESTAMPDIFF(SECOND,a.starttime,a.endtime) AS time_diff,a.byetype,a.duration,a.recordurl,a.caller,a.called,t.admin_id as uid,t.admin_user AS uname,t.callSid,t.time_add,t.channel")
            ->where($where)
            ->join("LEFT JOIN qz_log_telcenter a ON t.callSid = a.callSid")
            ->buildSql();

        if (count($id) > 0) {
            $map["a.op_uid"] = array("IN",$id);
        }

        if (count($day) > 0) {
            if (!empty($day[0]) && !empty($day[1])) {
                $map["a.time"] = array(
                    array("EGT",strtotime($day[0])),
                    array("ELT",strtotime($day[1])),
                );
            }
        }

        if (!empty($time)) {
            $map["b.call_time_length"] = array("egt",$time);
        }

        $nextSql = M("log_holly_diy_telcenter")->alias("a")->where($map)
            ->join("join qz_log_holly_telcenter b on a.call_sid = b.call_sid")
            ->field("b.id,'' as action,b.begin as starttime,end as endtime,call_time_length as time_diff,'' as byetype,call_time_length as duration,record_file as recordurl,b.call_no as caller,called_no as called,a.op_uid as uid, a.op_uname as uname,a.call_sid as callSid,FROM_UNIXTIME(a.time) as time_add,'holly' as channel")
            ->buildSql();

        $buildSql = M()->table($buildSql)->alias("t")
                        ->union($nextSql)->buildSql();

        $list = M()->table($buildSql)->alias("t")
            ->order("time_add desc")
            ->page($pageNum.",".$pageSize)
            ->select();
        return $list;
    }

    public function getVoiceCount($id, $day,$time)
    {
        $where['t.admin_id'] = ['in',(string)$id];
        $where['a.duration'] = ['egt',(string)$time];
        $where['a.action'] = 'Hangup';
        if (is_array($day)) {
            $where['t.time_add'] = ['between', [$day[0] . ' 00:00:00', $day[1] . ' 23:59:59']];
        } elseif (is_string($day)&& (stripos($day,'——') !==false)) {
            $dayArray = explode('——',$day);
            $where['t.time_add'] = ['between', [$dayArray[0] . ' 00:00:00', $dayArray[1] . ' 23:59:59']];
        }elseif (is_string($day)&& (stripos($day,'~') !==false)) {
            $dayArray = explode('~', $day);
            $where['t.time_add'] = ['between', [$dayArray[0] . ' 00:00:00', $dayArray[1] . ' 23:59:59']];
        }else{
            $where['t.time_add'] = ['between', [$day . ' 00:00:00', $day . ' 23:59:59']];
        }

        $buildSql = M("log_telcenter_diycall")->alias("t")->field('t.id')->where($where)
                    ->join("LEFT JOIN qz_log_telcenter a ON t.callSid = a.callSid")->buildSql();

        if (count($id) > 0) {
           $map["a.op_uid"] = array("IN",$id);
        }

        if (count($day) > 0) {
            if (!empty($day[0]) && !empty($day[1])) {
                $map["a.time"] = array(
                    array("EGT",strtotime($day[0])),
                    array("ELT",strtotime($day[1])),
                );
            }
        }

        if (!empty($time)) {
            $map["b.call_time_length"] = array("egt",$time);
        }

        $nextSql = M("log_holly_diy_telcenter")->alias("a")->field("a.id")->where($map)
                                              ->join("join qz_log_holly_telcenter b on a.call_sid = b.call_sid")
                                              ->buildSql();

        $buildSql = M()->table($buildSql)->alias("t")
                                         ->union($nextSql)->buildSql();

        return M()->table($buildSql)->alias("t")->count();
    }
}