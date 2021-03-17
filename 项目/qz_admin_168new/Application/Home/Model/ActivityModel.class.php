<?php

//活动表

namespace Home\Model;
Use Think\Model;

class ActivityModel extends Model{

    protected $autoCheckFields = false;

    /**
     * 活动列表
     * @param  string $name   [活动名称]
     * @param  datetime $begin  [活动开始时间]
     * @param  datetime $end    [活动结束时间]
     * @param  int $status [活动状态]
     * @return int
     */
    public function getActivityListCount($name,$begin,$end,$status)
    {
        if (!empty($name)) {
            $map["name"] = array("LIKE","%$name%");
        }

        if (!empty($begin)) {
            $_complex["start"] =array(
                        array("EGT",$begin),
                        array("ELT",$end),
                    );
        }

        if (!empty($end)) {
            $_complex["end"] = array(
                            array("EGT",$begin),
                            array("ELT",$end),
                        );
        }

        if (count($_complex) > 0) {
            if (count($_complex) > 1 ) {
                $_complex["_logic"] = "OR";
            }

            $map["_complex"] = $_complex;
        }


//        if (!empty($status)) {
//            $map["status"] = array("EQ",$status);
//        }
        if(!empty($status) || $status == 0){
            $now = date('Y-m-d H:i:s', time());
            switch ($status) {
                case '0':
                    $map["start"] = ['lt', $now];
                    $map["end"] = ['lt', $now];
                    break;
                case '1':
                    $map["start"] = ['lt', $now];
                    $map["end"] = ['gt', $now];
                    break;
                case '2':
                    $map["start"] = ['egt', $now];
                    $map["end"] = ['gt', $now];
                    break;
            }
        }

        return M("activity")->where($map)->count();
    }

     /**
     * 活动列表
     * @param  string $name   [活动名称]
     * @param  datetime $begin  [活动开始时间]
     * @param  datetime $end    [活动结束时间]
     * @param  int $status [活动状态]
     * @return array
     */
    public function getActivityList($name,$begin,$end,$status,$pageIndex,$pageCount,$map = [])
    {
        if (!empty($name)) {
            $map["a.name"] = array("LIKE","%$name%");
        }

        if (!empty($begin)) {
            $_complex["a.start"] =array(
                        array("EGT",$begin),
                        array("ELT",$end),
                    );
        }

        if (!empty($end)) {
            $_complex["a.end"] = array(
                            array("EGT",$begin),
                            array("ELT",$end),
                        );
        }

        if (count($_complex) > 0) {
            if (count($_complex) > 1 ) {
                $_complex["_logic"] = "OR";
            }
            $map["_complex"] = $_complex;
        }


//        if (!empty($status)) {
//            $map["a.status"] = array("EQ",$status);
//        }

        if(!empty($status) || $status == 0){
            $now = date('Y-m-d H:i:s', time());
            switch ($status) {
                case '0':
                    $map["start"] = ['lt', $now];
                    $map["end"] = ['lt', $now];
                    break;
                case '1':
                    $map["start"] = ['lt', $now];
                    $map["end"] = ['gt', $now];
                    break;
                case '2':
                    $map["start"] = ['egt', $now];
                    $map["end"] = ['gt', $now];
                    break;
            }
        }


        return M("activity")->where($map)->alias("a")
                            ->field('a.id,a.name,a.start,a.end,a.prize_start,a.prize_end,a.enrollment,a.src,a.status,a.source_id,a.activity_location')
                            ->order("a.status,a.end desc")
                            ->limit($pageIndex,$pageCount)
                            ->select();
    }

    /**
     * 删除奖品设置
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delPrize($id)
    {
        $map = array(
            "activity_id" => array("EQ", $id)
        );
        return M("activity_prize")->where($map)->delete();
    }

    /**
     * 删除多个奖品设置(逻辑删除)
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delPrizes($prize_ids)
    {
        $map = array(
            "id" => array("IN", $prize_ids)
        );

        $data = array("status" => 0);
        return M("activity_prize")->where($map)->data($data)->save();
    }

    /**
     * 添加所有奖品设置
     * @param [type] $data [description]
     */
    public function addAllPrize($data)
    {
        return M("activity_prize")->addAll($data);
    }

    /**
     * 保存奖项设置信息
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function addPrize($data){
        return M("activity_prize")->add($data);
    }

    /**
     * 保存奖项设置信息
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function editPrize($id, $data){
        $map = array(
            "id" => array("EQ", $id)
        );
        return M("activity_prize")->where($map)->save($data);
    }

    /**
     * 根据活动ID获取活动下的有所奖项
     * @return [type] [description]
     */
    public function getPrizeAll($id, $field = "*"){
        $map = array(
            "activity_id" => array("EQ", $id),
            "status" => array("EQ", 1)
        );
        return M("activity_prize")->where($map)->field($field)->order('id asc')->select();
    }

    /**
     * 添加活动
     * @param [type] $data [description]
     */
    public function addActivity($data)
    {
        return M("activity")->add($data);
    }

    /**
     * 查询活动信息
     * @param  [type] $id [活动ID]
     * @return array
     */
    public function getActivity($id)
    {
        $map = array(
            "a.id" => array("EQ",$id)
        );
        return M("activity")->where($map)->alias("a")
                            ->join("left join qz_activity_prize b on a.id = b.activity_id and b.status = 1")
                            ->field("a.*,activity_id,b.id as prize_id,prize_name,prize_level,prize_type,prize_count,prize_rate,prize_address,prize_tips,prize_mode,prize_day_count,prize_receive_count")->order("prize_level")
                            ->select();
    }

    /**
     * 编辑活动
     * @param  [type] $id   [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function eidtActivity($id,$data)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("activity")->where($map)->save($data);
    }

    /**
     * 活动参与名单
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getPrizeUserListCount($id)
    {
        $map = array(
            "a.activity_id" => array("EQ", $id),
            "a.prize_id" => array("NEQ", 0),
            "a.win_status" => array("EQ", 1)
        );

        return M("activity_userinfo")->where($map)->alias("a")->count();
    }

    /**
     * 活动参与名单
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getPrizeUserList($id,$pageIndex,$pageCount)
    {
        $map = array(
            "a.activity_id" => array("EQ",$id),
            "a.prize_id" => array("NEQ",0),
            "a.win_status" => array("EQ", 1)
        );

        $buildSql =  M("activity_userinfo")->where($map)->alias("a")
                                      ->join("left join qz_orders o on a.order_id = o.id")
                                      ->join("left join qz_activity_prize p on a.prize_id = p.id")
                                      ->field("a.id,a.order_id,a.prize_status,a.address,a.time,o.source,o.time_real,o.mianji,o.tel,o.cs,o.qx,p.prize_name,o.name,a.tel user_tel,a.consignee")
                                      ->limit($pageIndex.",".$pageCount)
                                      ->buildSql();

        return  M("activity_userinfo")->table($buildSql)->alias("t")
                                      ->join("left join qz_quyu as q on q.cid = t.cs")
                                      ->join("left join qz_area as area on area.qz_areaid = t.qx")
                                      ->field("t.*,q.cname,area.qz_area")
                                      ->order("t.prize_status,t.id desc")->select();
    }

    /**
     * 编辑活动用户信息
     * @param  [type] $id   [用户参与ID]
     * @param  [type] $data [description]
     * @return bool
     */
    public function editPrizeUser($id,$data)
    {
        $map = array(
            "id" => array("EQ",$id)
        );

        return M("activity_userinfo")->where($map)->save($data);
    }

    /**
     * 获取活动ID
     * @return [type] [description]
     */
    public function getActivityIds()
    {
        return M("activity")->field("source_id,name")->select();
    }

    /**
     * 获取非发单活动获奖人员名单数量
     * @param  [type] $source_name [活动名称]
     * @return [type]              [description]
     */
    public function getNoSourcePrizeUserListCount($source_name)
    {
        $map = array(
            "activity_sign" => array("EQ",$source_name),
            "is_winning" => 1
        );
        return M("weixin_activity")->where($map)->count();
    }

    /**
     * 获取非发单活动获奖人员名单数量
     * @param  [type] $source_name [活动名称]
     * @return [type]              [description]
     */
    public function getNoSourcePrizeUserList($source_name)
    {
        $map = array(
            "activity_sign" => array("EQ",$source_name),
            "is_winning" => 1
        );
        return M("weixin_activity")->where($map)->field("name,tel,integral_amount,is_winning,address,winning_time")->select();
    }

    public function getActivityInfoBySource($source)
    {
        $map = array(
            "_string" => "find_in_set($source,source_id)"
        );
        return M("activity")->where($map)->field("name")->find();
    }

    /**
     * 中奖记录
     * @param  [type]  $activity_id [description]
     * @param  integer $time_start  [description]
     * @param  integer $time_end    [description]
     * @return [type]               [description]
     */
    public function getActivityUserinfoList($activity_id, $time_start = 0, $time_end = 0){
        $map = array(
            "activity_id" => array("EQ", $activity_id)
        );

        if (!empty($time_start) && !empty($time_end)) {
            $map["time"] = array("BETWEEN", [$time_start, $time_end]);
        } else if (!empty($time_start)) {
            $map["time"] = array("EGT", $time_start);
        } else if (!empty($time_end)) {
            $map["time"] = array("ELT", $time_end);
        }

        $field = ['id', 'user_id', 'activity_id', 'prize_id', 'win_status', 'time'];
        return M("activity_userinfo")->where($map)->field($field)->select();
    }
}

