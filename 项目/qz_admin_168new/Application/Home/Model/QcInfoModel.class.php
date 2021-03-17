<?php

namespace Home\Model;
Use Think\Model;

/**
*
*/
class QcInfoModel extends Model
{
    /**
     * 获取质检项目
     * @return [type] [description]
     */
    public function getQcItem($type = 1)
    {
        $map = array(
            "type" => array(
                array("EQ",$type),
                array("EQ",0),
                "or"
            )
        );
        return M("qc_items")->where($map)->order("parentid,px")->select();
    }

    /**
     * 获取质检项目
     * @return [type] [description]
     */
    public function getNewQcItem($type = 1)
    {
        $map["status"] = array(
            array("EQ",1)
        );
        $map["type"] = array(
            array("EQ",$type),
            array("EQ",0),
            "or"
        );
        return M("qc_items")->where($map)->order("parentid,px")->select();
    }

    /**
     * 添加质检信息
     * @param [type] $data [description]
     */
    public function addQc($data)
    {
        return M("qc_info")->add($data);
    }

    /**
     * 编辑质检信息
     * @param  [type] $id   [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function eidtQc($id,$data)
    {
        $map = array(
            "order_id" => array("EQ",$id)
        );
        return M("qc_info")->where($map)->save($data);
    }

    /**
     * 添加扣分项
     * @param [type] $data [description]
     */
    public function addQcItem($data)
    {
        return M("qc_info_item")->addAll($data);
    }

    /**
     * 添加扣分项
     * @param [type] $data [description]
     */
    public function addQcItemOther($data)
    {
        return M("qc_info_item_other")->addAll($data);
    }

    /**
     * 获取质检内容
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getQcInfoById($id)
    {
        $map = array(
            "a.order_id" => array("EQ",$id)
        );
        return M("qc_info")->where($map)->alias("a")
                           ->join("left join qz_qc_info_item as b on b.order_id = a.order_id")
                           ->field("a.*,b.qc_item_id,b.money")
                           ->select();
    }

    /**
     * 添加推荐录音
     * @param [type] $data [description]
     */
    public function addQcVideo($data)
    {
        return M("qc_telcenter")->add($data);
    }

    /**
     * 获取推荐录音信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getQcVideoInfo($id)
    {
        $map = array(
            "order_id" => array("EQ",$id)
        );
        return M("qc_telcenter")->where($map)->find();
    }

    /**
     * 删除录音
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delQcVideo($id)
    {
        $map = array(
            "order_id" => array("EQ",$id)
        );
        return M("qc_telcenter")->where($map)
                                ->delete();
    }

    /**
     * 删除400质检项
     * @param  [type] $orderid [订单编号]
     * @return [type]          [description]
     */
    public function delQcInfo400($orderid)
    {
        $map = array(
            "order_id" => array("EQ",$orderid)
        );
        return M("qc_info_400")->where($map)->delete();
    }

    /**
     * 删除53质检项
     * @param  [type] $orderid [订单编号]
     * @return [type]          [description]
     */
    public function delQcInfo53($orderid)
    {
        $map = array(
            "id" => array("EQ",$orderid)
        );
        return M("qc_info_53")->where($map)->delete();
    }

    /**
     * 编辑推荐录音
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function editQcVideo($id,$data)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("qc_telcenter")->where($map)->save($data);
    }

    /**
     * 删除质检项目
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delQcItem($id)
    {
        $map = array(
            "order_id" => array("EQ",$id)
        );
        return M("qc_info_item")->where($map)->delete();
    }

    /**
     * 删除质检项目
     * @param  [type] $ids [质检项目ID]
     * @param  [type] $id [订单编号]
     * @return [type]     [description]
     */
    public function delQcItemByIds($ids,$id)
    {
        $map = array(
            "qc_item_id" => array("IN",$ids),
            "order_id" => array("EQ",$id)
        );
        return M("qc_info_item")->where($map)->delete();
    }

    /**
     * 根据类型获取抽检项目
     * @param  [int] $type [质检类型]
     * @param  [int] $orderid [订单编号]
     * @return array
     */
    public function getQcItemByType($orderid,$type)
    {
        $map = array(
            "b.type" => array("EQ",$type),
            "a.order_id" => array("EQ",$orderid)
        );
        return M("qc_info_item")->where($map)->alias("a")
                                ->join("join qz_qc_items b on a.qc_item_id = b.id")
                                ->field("b.id")->select();
    }

    /**
     * [getQcInfoDetailStat 团队问题点明细统计]
     * @param  string $time_start [开始时间]
     * @param  string $time_end   [结束时间]
     * @param  string $user_ids   [客服ID数组]
     * @return [type]             [description]
     */
    public function getQcInfoDetailStat($time_start = '', $time_end = '', $user_ids = '')
    {
        if (!empty($time_start) && !empty($time_end)) {
            $map['i.time'] = array(
                array('EGT', $time_start),
                array('LT', $time_end)
            );
        }

        if (!empty($user_ids)) {
            $map['i.kf_id'] = array('IN', $user_ids);
        }

        $result = M('qc_info')->alias('i')
                              ->field('z.qc_item_id AS item, FROM_UNIXTIME(i.time, "%Y-%m-%d") AS date, COUNT(z.qc_item_id) AS error_count')
                              ->join('qz_qc_info_item AS z ON i.order_id = z.order_id')
                              ->group('CONCAT(date,qc_item_id)')
                              ->where($map)
                              ->select();
        return $result;
    }

    /**
     * [getQcInfoCollectStat 团队问题点汇总统计]
     * @param  string $time_start [开始时间]
     * @param  string $time_end   [结束时间]
     * @return [type]             [description]
     */
    public function getQcInfoCollectStat($time_start = '', $time_end = '')
    {
        if (!empty($time_start) && !empty($time_end)) {
            $map['i.time'] = array(
                array('EGT', $time_start),
                array('LT', $time_end)
            );
        }
        $build = M('qc_info')->alias('i')
                             ->field('FROM_UNIXTIME(i.time, "%Y-%m-%d") AS date,  i.kf_group, i.kf_manager, COUNT(*) AS error_count')
                             ->join('qz_qc_info_item AS z ON z.order_id = i.order_id')
                             ->group('CONCAT(i.kf_group, date)')
                             ->where($map)
                             ->buildSql();
        $result = M()->table($build)->alias('y')
                                    ->field('y.*, u.name AS kf_group_name, x.name AS kf_manager_name')
                                    ->join('left join qz_adminuser AS u ON (u.uid = 31 AND u.kfgroup = y.kf_group)')
                                    ->join('left join qz_adminuser AS x ON x.id = y.kf_manager')
                                    ->select();
        return $result;
    }

    /**
     * 问题订单统计
     * @param  string $time_start 开始时间
     * @param  string $time_end   结束时间
     * @param  string $kf_group   客服组
     * @param  string $kf_manager 客服师
     * @return array              结果
     */
    public function getQcInfoTelcenterQuestionStatList($time_start = '', $time_end = '', $kf_id = 0, $kf_group = '', $kf_manager = '', $docking_id = 0, $docking_group = 0, $docking_manager = 0)
    {
        if (empty($time_start) || empty($time_end)) {
            return false;
        }
        $map['i.time'] = array(
            array('EGT', $time_start),
            array('LT', $time_end)
        );
        //审核客服条件筛选
        $kf_id = intval($kf_id);
        if (!empty($kf_id)) {
            $map['i.kf_id'] = $kf_id;
        }
        $kf_group = intval($kf_group);
        if (!empty($kf_group)) {
            $map['i.kf_group'] = $kf_group;
        }
        $kf_manager = intval($kf_manager);
        if (!empty($kf_manager)) {
            $map['i.kf_manager'] = $kf_manager;
        }
        //对接客服条件筛选
        $docking_id = intval($docking_id);
        if (!empty($docking_id)) {
            $map['i.docking_id'] = $docking_id;
        }
        $docking_group = intval($docking_group);
        if (!empty($docking_group)) {
            $map['i.docking_group'] = $docking_group;
        }
        $docking_manager = intval($docking_manager);
        if (!empty($docking_manager)) {
            $map['i.docking_manager'] = $docking_manager;
        }
        return M('qc_info')->alias('i')
                            ->field('
                                FROM_UNIXTIME(i.time, "%Y-%m-%d") AS date,
                                i.kf_id,
                                i.kf_group,
                                i.kf_manager,
                                i.docking_id,
                                i.docking_group,
                                i.docking_manager,
                                SUM(IF(y.parentid = 1, 1, 0)) AS sh_gt_cw,
                                SUM(IF(y.parentid = 2, 1, 0)) AS sh_jl_cw,
                                SUM(IF((y.parentid = 3 AND y.group = 1), 1, 0)) AS sh_cz_cw,
                                SUM(IF((y.parentid = 3 AND y.group = 2), 1, 0)) AS dj_cz_cw,
                                SUM(IF((y.parentid is not null AND y.group = 1), 1, 0)) AS sh_zj_cw,
                                SUM(IF((y.parentid is not null AND y.group = 2), 1, 0)) AS dj_zj_cw,
                                SUM(IF((y.parentid is not null), 1, 0)) AS zj_wc
                            ')
                            ->join('LEFT JOIN qz_qc_info_item AS z ON z.order_id = i.order_id')
                            ->join('LEFT JOIN qz_qc_items AS y ON (y.id = z.qc_item_id AND y.type = 1)')
                            ->where($map)
                            ->group('i.order_id')
                            ->select();
    }
    /**
     * 问题订单统计新
     * @param  string $time_start 开始时间
     * @param  string $time_end   结束时间
     * @param  string $kf_group   客服组
     * @param  string $kf_manager 客服师
     * @return array              结果
     */
    public function getQcInfoTelcenterQuestionStatListNew($time_start = '', $time_end = '', $kf_id = 0, $kf_group = '', $kf_manager = '', $docking_id = 0, $docking_group = 0, $docking_manager = 0)
    {
        if (empty($time_start) || empty($time_end)) {
            return false;
        }
        $map['i.time'] = array(
            array('EGT', $time_start),
            array('LT', $time_end)
        );
        //审核客服条件筛选
        $kf_id = intval($kf_id);
        if (!empty($kf_id)) {
            $having[] ='m.id='.$kf_id;
        }
        $kf_group = intval($kf_group);
        if (!empty($kf_group)) {
            $having[] = 'm.kfgroup='.$kf_group;
        }
        $kf_manager = intval($kf_manager);
        if (!empty($kf_manager)) {
            $having[] = 'substring_index(m.kfmanager,",",1)='.$kf_manager;
        }
        $havings = implode(' AND ',$having);
        //对接客服条件筛选
        $docking_id = intval($docking_id);
        if (!empty($docking_id)) {
            $map['i.docking_id'] = $docking_id;
        }
        $docking_group = intval($docking_group);
        if (!empty($docking_group)) {
            $map['i.docking_group'] = $docking_group;
        }
        $docking_manager = intval($docking_manager);
        if (!empty($docking_manager)) {
            $map['i.docking_manager'] = $docking_manager;
        }
        $buildSql =  M('qc_info')->alias('i')
            ->field('
                                FROM_UNIXTIME(i.time, "%Y-%m-%d") AS date,
                                i.kf_id,
                                m.addtime,
                                m.state as mstate,
                                m.uid as muid,
                                m.name as kf_name,
                                m.kfgroup as kf_group,
                                m.kfmanager as kf_manager,
                                i.docking_id,
                                i.docking_group,
                                i.docking_manager,
                                i.order_id,
                                y.name,
                                y.type,
                                y.group,
			                    y.parentid
                            ')
            ->join('LEFT JOIN qz_qc_info_item AS z ON z.order_id = i.order_id')
            ->join('LEFT JOIN qz_qc_items AS y ON (y.id = z.qc_item_id AND y.type = 1)')
            ->join('LEFT JOIN qz_adminuser AS m ON m.id = i.kf_id')
            ->where($map)
            ->having($havings)
            ->buildSql();
        return M()->table($buildSql)->alias('t')
            ->field('
                date,
                kf_id,
                addtime,
                mstate,
                muid,
                kf_name,
                kf_group,
                kf_manager,
                docking_id,
                docking_group,
                docking_manager,
                sum(if(type = 1 and `group` = 1 and parentid = 1,1,0)) as sh_gt_cw,
                sum(if(type = 1 and `group` = 1 and parentid = 2,1,0)) as sh_jl_cw,
                sum(if(type = 1 and `group` = 1 and parentid = 3,1,0)) as sh_cz_cw,
                sum(if(type = 1 and `group` = 2 and parentid = 3,1,0)) as dj_cz_cw,
                sum(if(type = 1 and `group` = 1 and parentid <> 0,1,0)) as sh_zj_cw,
                sum(if(type = 1 and `group` = 2 and parentid <> 0,1,0)) as dj_zj_cw
            ')
            ->group('order_id')
            ->select();
    }

    /**
     * 录音操作问题统计
     * @param  string $time_start 开始时间
     * @param  string $time_end   结束时间
     * @param  string $kf_group   客服组
     * @param  string $kf_manager 客服师
     * @return array              结果
     */
    public function getLuYinCaoZuoWenTiTongJiList($time_start = '', $time_end = '', $kf_id = 0, $kf_group = '', $kf_manager = '', $docking_id = 0, $docking_group = 0, $docking_manager = 0)
    {
        if (empty($time_start) || empty($time_end)) {
            return false;
        }
        $map['i.time'] = array(
            array('EGT', $time_start),
            array('LT', $time_end)
        );
        //审核客服条件筛选
        $kf_id = intval($kf_id);
        if (!empty($kf_id)) {
            $map['i.kf_id'] = $kf_id;
        }
        $kf_group = intval($kf_group);
        if (!empty($kf_group)) {
            $map['x.kfgroup'] = $kf_group;
        }
        $kf_manager = intval($kf_manager);
        if (!empty($kf_manager)) {
            $map['x.kfmanager'] = $kf_manager;
        }
        //对接客服条件筛选
        $docking_id = intval($docking_id);
        if (!empty($docking_id)) {
            $map['i.docking_id'] = $docking_id;
        }
        $docking_group = intval($docking_group);
        if (!empty($docking_group)) {
            $map['k.kfgroup'] = $docking_group;
        }
        $docking_manager = intval($docking_manager);
        if (!empty($docking_manager)) {
            $map['k.kfmanager'] = $docking_manager;
        }
        return M('qc_info')->alias('i')
                            ->field('
                                FROM_UNIXTIME(i.time, "%Y-%m-%d") AS date,
                                i.kf_id,
                                x.kfgroup AS kf_group,
                                x.kfmanager AS kf_manager,
                                i.docking_id,
                                k.kfgroup AS docking_group,
                                k.kfmanager AS docking_manager,
                                SUM(IF(y.parentid = 1, 1, 0)) AS sh_gt_cw,
                                SUM(IF(y.parentid = 2, 1, 0)) AS sh_jl_cw,
                                SUM(IF((y.parentid = 3 AND y.group = 1), 1, 0)) AS sh_cz_cw,
                                SUM(IF((y.parentid = 3 AND y.group = 2), 1, 0)) AS dj_cz_cw,
                                SUM(IF((y.parentid is not null AND y.group = 1), 1, 0)) AS sh_zj_cw,
                                SUM(IF((y.parentid is not null AND y.group = 2), 1, 0)) AS dj_zj_cw,
                                SUM(IF((y.parentid is not null), 1, 0)) AS zj_wc
                            ')
                            ->join('LEFT JOIN qz_qc_info_item AS z ON z.order_id = i.order_id')
                            ->join('LEFT JOIN qz_qc_items AS y ON (y.id = z.qc_item_id AND y.type = 1)')
                            ->join('INNER JOIN qz_adminuser AS x ON x.id = i.kf_id')
                            ->join('LEFT JOIN qz_adminuser AS k ON k.id = i.docking_id')
                            ->where($map)
                            ->group('i.order_id')
                            ->select();
    }

    /**
     * [getQcInfoCount 获取抽检录音数量]
     * @param  [type] $order_id   [订单ID]
     * @param  [type] $time_start [开始时间]
     * @param  [type] $time_end   [结束时间]
     * @param  [type] $op_uid     [抽检人]
     * @param  [type] $kf_id      [客服ID]
     * @param  [type] $sub_yd     [客服引导状态]
     * @param  [type] $sub_hs     [客服核实状态]
     * @param  [type] $sub_td     [客服态度]
     * @param  [type] $sub_bz     [客服备注]
     * @param  [type] $sub_sh     [客服审核]
     * @return [type]             [description]
     */
    public function getQcInfoCount($order_id, $time_start, $time_end, $op_uid, $kf_id, $sub_yd, $sub_hs, $sub_td, $sub_bz, $sub_sh)
    {
        //订单ID
        if (!empty($order_id)) {
            $map['i.order_id'] = $order_id;
        }
        //抽检时间
        if (!empty($time_start)) {
            $map['i.time'][] = array('EGT', intval($time_start));
        }
        if (!empty($time_end)) {
            $map['i.time'][] = array('LT', intval($time_end));
        }
        //抽检人
        if (!empty($op_uid)) {
            $map['i.op_uid'] = $op_uid;
        }
        //审核人ID(客服ID)
        if (!empty($kf_id)) {
            $map['i.kf_id'] = $kf_id;
        }
        //话术引导
        if (!empty($sub_yd)) {
            $map['t.sub_yd'] = $sub_yd;
        }
        //信息核实
        if (!empty($sub_hs)) {
            $map['t.sub_hs'] = $sub_hs;
        }
        //服务态度
        if (!empty($sub_td)) {
            $map['t.sub_td'] = $sub_td;
        }
        //后台备注
        if (!empty($sub_bz)) {
            $map['t.sub_bz'] = $sub_bz;
        }
        //操作审核
        if (!empty($sub_sh)) {
            $map['t.sub_sh'] = $sub_sh;
        }
        $build = M('qc_info')->alias('i')
                             ->field('i.order_id')
                             ->join('qz_qc_telcenter AS t ON t.order_id = i.order_id')
                             ->where($map)
                             ->group('i.order_id')
                             ->buildSql();
        return M()->table($build)->alias('z')->count();
    }

    /**
     * [getQcInfoList 获取抽检录音列表]
     * @param  [type] $order_id   [订单ID]
     * @param  [type] $time_start [开始时间]
     * @param  [type] $time_end   [结束时间]
     * @param  [type] $op_uid     [抽检人]
     * @param  [type] $kf_id      [客服ID]
     * @param  [type] $sub_yd     [客服引导状态]
     * @param  [type] $sub_hs     [客服核实状态]
     * @param  [type] $sub_td     [客服态度]
     * @param  [type] $sub_bz     [客服备注]
     * @param  [type] $sub_sh     [客服审核]
     * @param  [type] $start      [开始位置]
     * @param  [type] $end        [偏移量]
     * @return [type]             [description]
     */
    public function getQcInfoList($order_id, $time_start, $time_end, $op_uid, $kf_id, $sub_yd, $sub_hs, $sub_td, $sub_bz, $sub_sh, $start, $end)
    {
        //订单ID
        if (!empty($order_id)) {
            $map['i.order_id'] = $order_id;
        }
        //抽检时间
        if (!empty($time_start)) {
            $map['i.time'][] = array('EGT', intval($time_start));
        }
        if (!empty($time_end)) {
            $map['i.time'][] = array('LT', intval($time_end));
        }
        //抽检人
        if (!empty($op_uid)) {
            $map['i.op_uid'] = $op_uid;
        }
        //审核人ID(客服ID)
        if (!empty($kf_id)) {
            $map['i.kf_id'] = $kf_id;
        }
        //话术引导
        if (!empty($sub_yd)) {
            $map['t.sub_yd'] = $sub_yd;
        }
        //信息核实
        if (!empty($sub_hs)) {
            $map['t.sub_hs'] = $sub_hs;
        }
        //服务态度
        if (!empty($sub_td)) {
            $map['t.sub_td'] = $sub_td;
        }
        //后台备注
        if (!empty($sub_bz)) {
            $map['t.sub_bz'] = $sub_bz;
        }
        //操作审核
        if (!empty($sub_sh)) {
            $map['t.sub_sh'] = $sub_sh;
        }
        return M('qc_info')->alias('i')
                           ->field('i.time, i.order_id, i.type AS qc_info_type, i.op_name, o.on, o.on_sub, o.type_fw, i.kf_name, u.name AS kf_group_name,t.sub_tj, t.type AS qc_telcenter_type')
                           ->join('qz_qc_telcenter AS t ON t.order_id = i.order_id')
                           ->join('qz_orders AS o ON o.id = i.order_id')
                           ->join('LEFT JOIN qz_adminuser AS u ON u.kfgroup = i.kf_group and u.uid = 31')
                           ->where($map)
                           ->group('i.order_id')
                           ->limit($start, $end)
                           ->select();
    }


    /**
     * [getLogTelcenterRecordStat 获取电话录音记录统计]
     * @param  [type] $time_start   [开始操作时间]
     * @param  [type] $time_end     [结束操作时间]
     * @param  array  $adminuser_id [操作人]
     * @return [type]               [description]
     */
    public function getLogTelcenterRecordStat($time_start, $time_end, $adminuser_id = array())
    {
        if (!empty($time_start)) {
            $map['i.time'][] = array('EGT', $time_start);
            $where['a.operate_time'][] = array('EGT', $time_start);
        }

        if (!empty($time_end)) {
            $map['i.time'][] = array('LT', $time_end);
            $where['a.operate_time'][] = array('LT', $time_end);
        }

        if (!empty($adminuser_id)) {
            if (!is_array($adminuser_id)) {
                $adminuser_id = array($adminuser_id);
            }
            $map['i.op_uid'] = array('IN', $adminuser_id);
            $where['a.adminuser_id'] = array('IN', $adminuser_id);
        }

        $buildOne = M('qc_info')->alias('i')
                                ->field('i.op_uid,
                                    i.order_id,
                                    q.on,
                                    q.on_sub,
                                    q.type_fw,
                                    COUNT(DISTINCT(c.call_sid)) AS record_sum,
                                    COUNT(DISTINCT(l.callSid)) AS record_click_number'
                                )
                                ->join('LEFT JOIN qz_log_holly_order_telcenter AS c ON c.order_id = i.order_id')
                                ->join('LEFT JOIN qz_log_telcenter_listen_ordercall AS l ON l.orders_id = i.order_id')
                                ->join('LEFT JOIN qz_orders AS q ON q.id = i.order_id')
                                ->group('CONCAT(i.op_uid,i.order_id)')
                                ->where($map)
                                ->buildSql();
        $buildTwo = M('log_telcenter_listen_ordercall')->alias('a')
                                                       ->field('a.orders_id AS order_id, a.adminuser_id AS op_uid, b.call_time_length as duration')
                                                       ->join('qz_log_holly_telcenter AS b ON (b.call_sid = a.callSid)')
                                                       ->where($where)
                                                       ->group('CONCAT(a.adminuser_id,a.orders_id, a.callSid)')
                                                       ->buildSql();
        $buildThr = M()->table($buildTwo)->alias('x')
                                         ->field('x.order_id, x.op_uid, SUM(x.duration) AS listen_record_time')
                                         ->group('CONCAT(x.op_uid,x.order_id)')
                                         ->buildSql();
        return M()->table($buildOne)->alias('z')
                                    ->field('
                                                z.op_uid,
                                                z.on,
                                                z.on_sub,
                                                z.type_fw,
                                                COUNT(*) AS order_sum,
                                                SUM(z.record_sum) AS record_sum,
                                                SUM(z.record_click_number) AS record_click_number,
                                                SUM(y.listen_record_time) AS listen_record_time
                                            ')
                                    ->join('LEFT JOIN ' . $buildThr . ' AS y ON (z.op_uid = y.op_uid AND z.order_id = y.order_id)')
                                    ->group('CONCAT(z.op_uid,z.on,z.on_sub,z.type_fw)')
                                    ->select();
    }

    /**
     * 抽检问题统计
     * @param  [type] $begin [开始时间]
     * @param  [type] $end   [结束时间]
     * @return [type]        [description]
     */
    public function getQcItemStat($begin,$end)
    {
        $map = array(
            "a.type" => array("EQ",1),
            "a.parentid" => array("NEQ",0)
        );
        $buildSql = M("qc_items")->alias("a")->where($map)
                        ->join("join qz_qc_items b on b.id = a.parentid")
                        ->field("a.id,a.name,a.parentid,b.id as pid ,b.name as pname,b.px")
                        ->order("b.px,a.id")
                        ->buildSql();

        $buildSql = M("qc_items")->table($buildSql)->alias("t")
                         ->join("left join qz_qc_info_item  b on t.id = b.qc_item_id")
                         ->join("left join qz_qc_info i on i.order_id = b.order_id and i.time >= $begin and i.time < $end")
                         ->field('t.*,FROM_UNIXTIME(i.time,"%Y%m%d") as time')
                         ->buildSql();
        return  M("qc_items")->table($buildSql)->alias("t")
                             ->field("t.id,t.name,t.parentid,t.pname,t.time,count(if(t.time is not null,1,null)) as count")
                             ->group("t.id,t.time")
                             ->order("t.px,t.id")
                             ->select();
    }

    /**
     * 质检结论错误明细统计数量
     * @param  integer $order_id        订单ID
     * @param  string  $start_time      开始时间
     * @param  string  $end_time        结束时间
     * @param  integer $op_uid          质检人ID
     * @param  integer $kf_manager      审核客服师
     * @param  integer $kf_group        审核客服团
     * @param  integer $kf_id           审核客服
     * @param  integer $docking_manager 对接客服师
     * @param  integer $docking_group   对接客服团
     * @param  integer $docking_id      对接客服
     * @param  integer $remark          有无备注，1有，2无
     * @param  integer $error           有无错误，1有，2无
     * @param  integer $type            错误类型，1审核错误，2对接错误
     * @param  integer $item            错误项
     * @return integer
     */
    public function getQcConclusionErrorDetailCount($order_id = 0, $start_time = '', $end_time = '', $op_uid = 0, $kf_manager = 0, $kf_group = 0, $kf_id = 0, $docking_manager = 0, $docking_group = 0, $docking_id = 0, $remark = 0, $error = 0, $type = 0, $item = 0,$qctype = 0){

        //订单ID
        if (!empty($order_id)) {
            $map['i.order_id'] = $order_id;
        }
        //开始结束时间
        if (!empty($start_time)) {
            $map['i.time'][] = array(
                'EGT', $start_time
            );
        }
        if (!empty($end_time)) {
            $map['i.time'][] = array(
                'LT', $end_time
            );
        }
        //质检员
        if (!empty($op_uid)) {
            $map['i.op_uid'] = $op_uid;
        }
        //审核客服师
        if (!empty($kf_manager)) {
            $map['i.kf_manager'] = $kf_manager;
        }
        //审核客服团
        if (!empty($kf_group)) {
            $map['i.kf_group'] = $kf_group;
        }
        //审核客服
        if (!empty($kf_id)) {
            $map['i.kf_id'] = $kf_id;
        }
        //对接客服师
        if (!empty($docking_manager)) {
            $map['i.docking_manager'] = $docking_manager;
        }
        //对接客服团
        if (!empty($docking_group)) {
            $map['i.docking_group'] = $docking_group;
        }
        //对接客服
        if (!empty($docking_id)) {
            $map['i.docking_id'] = $docking_id;
        }
        //备注
        if (!empty($remark)) {
            if ('1' == $remark) {
                $map['i.remark'] = array('NEQ', '');
            } else {
                $map['i.remark'] = array('EQ', '');
            }
        }
        //错误类型
        if (!empty($type)) {
            if ('1' == $type) {
                //审核错误
                $where['x.group'] = array('EQ', '1');
            } else {
                //对接错误
                $where['x.group'] = array('EQ', '2');
            }
        }
        //错误项
        if (!empty($item)) {
            $where['y.qc_item_id'][] = array('EQ', $item);
        }
        //有无错误
        if (!empty($error)) {
            if ('1' == $error) {
                //有错误
                $condition['items'] = array('EXP', 'IS NOT NULL');
            } else if ('2' == $error) {
                //无错误
                $condition['items'] = array('EXP', 'IS NULL');
            }
        }

        //质检类型
        if (!empty($qctype)) {
            $map['i.type'] = array("EQ",$qctype);
        }

        $build = M('qc_info')->alias('i')
                             ->field("i.order_id")
                             ->where($map)
                             ->buildSql();

        $build = M()->table($build)->alias("z")
                                   ->field("z.*,x.parentid,GROUP_CONCAT(DISTINCT x.name SEPARATOR ';') as items,x.group")
                                   ->join("left join qz_orders AS o on z.order_id = o.id")
                                   ->join("left join qz_qc_info_item AS y on y.order_id = z.order_id")
                                   ->join("left join qz_qc_items x on x.id = y.qc_item_id and x.type = 1 and x.parentid <> 0")
                                   ->group("z.order_id,x.group")
                                   ->where($where)
                                   ->buildSql();
        return M()->table($build)->alias('k')->where($condition)->count();
    }

    /**
     * 质检结论错误明细统计列表
     * @param  integer $order_id        订单ID
     * @param  string  $start_time      开始时间
     * @param  string  $end_time        结束时间
     * @param  integer $op_uid          质检人ID
     * @param  integer $kf_manager      审核客服师
     * @param  integer $kf_group        审核客服团
     * @param  integer $kf_id           审核客服
     * @param  integer $docking_manager 对接客服师
     * @param  integer $docking_group   对接客服团
     * @param  integer $docking_id      对接客服
     * @param  integer $remark          有无备注，1有，2无
     * @param  integer $error           有无错误，1有，2无
     * @param  integer $type            错误类型，1审核错误，2对接错误
     * @param  integer $item            错误项
     * @param  integer $start           开始位置
     * @param  integer $end             结束位置
     * @return array
     */
    public function getQcConclusionErrorDetailList($order_id = 0, $start_time = '', $end_time = '', $op_uid = 0, $kf_manager = 0, $kf_group = 0, $kf_id = 0, $docking_manager = 0, $docking_group = 0, $docking_id = 0, $remark = 0, $error = 0, $type = 0, $item = 0, $qctype = 0,$start = 0, $end = 20){

        //订单ID
        if (!empty($order_id)) {
            $map['i.order_id'] = $order_id;
        }
        //开始结束时间
        if (!empty($start_time)) {
            $map['i.time'][] = array(
                'EGT', $start_time
            );
        }
        if (!empty($end_time)) {
            $map['i.time'][] = array(
                'LT', $end_time
            );
        }
        //质检员
        if (!empty($op_uid)) {
            $map['i.op_uid'] = $op_uid;
        }
        //审核客服师
        if (!empty($kf_manager)) {
            $map['i.kf_manager'] = $kf_manager;
        }
        //审核客服团
        if (!empty($kf_group)) {
            $map['i.kf_group'] = $kf_group;
        }
        //审核客服
        if (!empty($kf_id)) {
            $map['i.kf_id'] = $kf_id;
        }
        //对接客服师
        if (!empty($docking_manager)) {
            $map['i.docking_manager'] = $docking_manager;
        }
        //对接客服团
        if (!empty($docking_group)) {
            $map['i.docking_group'] = $docking_group;
        }
        //对接客服
        if (!empty($docking_id)) {
            $map['i.docking_id'] = $docking_id;
        }
        //备注
        if (!empty($remark)) {
            if ('1' == $remark) {
                $map['i.remark'] = array('NEQ', '');
            } else {
                $map['i.remark'] = array('EQ', '');
            }
        }
        //错误类型
        if (!empty($type)) {
            if ('1' == $type) {
                //审核错误
                $where['x.group'] = array('EQ', '1');
            } else {
                //对接错误
                $where['x.group'] = array('EQ', '2');
            }
        }
        //错误项
        if (!empty($item)) {
            $where['y.qc_item_id'][] = array('EQ', $item);
        }
        //有无错误
        if (!empty($error)) {
            if ('1' == $error) {
                //有错误
                $condition['items'] = array('EXP', 'IS NOT NULL');
            } else if ('2' == $error) {
                //无错误
                $condition['items'] = array('EXP', 'IS NULL');
            }
        }

        //质检类型
        if (!empty($qctype)) {
            $map['i.type'] = array("EQ",$qctype);
        }

        $build = M('qc_info')->alias('i')
                             ->join("left join qz_orders o on i.order_id = o.id ")
                             ->join("left join qz_adminuser AS u on u.id = i.kf_id")
                             ->join("left join qz_adminuser AS w on w.kfgroup = i.kf_group and w.uid = 31 and w.stat = 1")
                             ->field('i.order_id, FROM_UNIXTIME(i.time,"%Y-%m-%d %H:%i:%s") as time,i.type,i.op_name,o.on,o.type_fw,w.name as group_name,u.name as kf,i.remark,i.kf_name,i.docking_name,i.remark2')
                             ->where($map)
                             ->buildSql();

        $build = M()->table($build)->alias("z")
                                   ->field("z.*,x.parentid,GROUP_CONCAT(DISTINCT x.name SEPARATOR ';') as items, x.group")
                                   ->join("left join qz_qc_info_item AS y on y.order_id = z.order_id")
                                   ->join("left join qz_qc_items x on x.id = y.qc_item_id and x.type = 1 and x.parentid <> 0")
                                   ->group("z.order_id,x.group")
                                   ->where($where)
                                   ->order('z.time desc')
                                   ->buildSql();
        return M()->table($build)->alias('k')->where($condition)->limit($start, $end)->select();
    }

     /**
     * 质检判定错误明细统计
     * @param  [type] $id   [订单编号]
     * @param  [type] $date [质检日期]
     * @param  [type] $qc   [质检员]
     * @return [type]       [description]
     */
    public function getQcQualityConclusionStat($id,$begin,$end,$qc)
    {
        $map = array(
            "a.status" => array("EQ",2),
            "a.sampling_time" => array(
                array("EGT",$begin),
                array("ELT",$end)
            )
        );

        if (!empty($id)) {
            $map["a.order_id"] = array("EQ",$id);
        }

        if (!empty($qc)) {
            $map["a.op_uid"] = array("EQ",$qc);
        }

        $buildSql = M("qc_info")->where($map)->alias("a")
                    ->join("left join qz_qc_info_item b on a.order_id = b.order_id")
                    ->join("left join qz_qc_items c on c.id = b.qc_item_id and c.type = 2 and c.parentid = 0")
                    ->field('FROM_UNIXTIME(a.sampling_time,"%Y%m%d") as time,a.op_uid, a.op_name,a.type,a.order_id,c.name as item_name,c.id as item_id,a.sampling_remark,FROM_UNIXTIME(a.time,"%Y%m%d") as qctime')
                    ->buildSql();
        $buildSql = M("qc_info")->table($buildSql)->alias("t")
                                ->field("t.*,count(t.item_id) as item_count")
                                ->group("t.order_id,t.item_id")
                                ->buildSql();
        $buildSql = M("qc_info")->table($buildSql)->alias("t1")
                                ->field("t1.time,
                                        t1.op_name,
                                        t1.type,
                                        t1.order_id,
                                        t1.sampling_remark,
                                        t1.op_uid,
                                        t1.qctime,
                                        case when item_id = 25 then item_count else 0   end as '25',
                                        case when item_id = 26 then item_count else 0   end as '26',
                                        case when item_id = 27 then item_count else 0   end as '27',
                                        case when item_id = 28 then item_count else 0   end as '28',
                                        case when item_id = 29 then item_count else 0   end as '29',
                                        case when item_id = 30 then item_count else 0   end as '30',
                                        case when item_id = 31 then item_count else 0   end as '31',
                                        case when item_id = 32 then item_count else 0   end as '32'")
                                ->buildSql();
        return $buildSql = M("qc_info")->table($buildSql)->alias("t2")
                                       ->field("t2.time,
                                                t2.op_name,
                                                t2.type,
                                                t2.order_id,
                                                t2.sampling_remark,
                                                t2.op_uid,
                                                t2.qctime,
                                                max(t2.25) as '25',
                                                max(t2.26) as '26',
                                                max(t2.27) as '27',
                                                max(t2.28) as '28',
                                                max(t2.29) as '29',
                                                max(t2.30) as '30',
                                                max(t2.31) as '31',
                                                max(t2.32) as '32'")
                                       ->group("t2.order_id")
                                       ->order("t2.time desc")
                                       ->select();
    }

    /**
     * 获取负激励数据
     * @param  int $monthStart [质检开始时间]
     * @param  int $monthEnd   [质检结束时间]
     * @param  int $type       [客服类型]
     * @return array
     */
    public function getQCInfoItemList($monthStart, $monthEnd, $kf,$group,$manager,$dockingkf)
    {
        $map = array(
            "a.kf_id" => array("NEQ",0),
            "a.time" => array(
                array("EGT",$monthStart),
                array("ELT",$monthEnd)
            )
        );

        if (!empty($kf)) {
            $map["a.kf_id"] = array("EQ",$kf);
        }

        if (!empty($dockingkf)) {
            $map["a.docking_id"] = array("EQ",$dockingkf);
        }

        if (!empty($group)) {
            $map["a.kf_group"] = array("EQ",$group);
            if ($type == 2) {
                $map["c.group"] = array("EQ",2);
            } else {
                $map["c.group"] = array("EQ",1);
            }
        }

        if (!empty($manager)) {
            $map["a.kf_manager"] = array("EQ",$manager);
        }

        return M("qc_info")->where($map)->alias("a")
                    ->join("join qz_qc_info_item b on a.order_id = b.order_id")
                    ->join("join qz_qc_items c on c.id = b.qc_item_id")
                    ->join("left join qz_adminuser u on u.id = a.kf_manager")
                    ->join("left join qz_adminuser u1 on u1.id = a.docking_manager")
                    ->field("a.order_id,a.kf_id,a.kf_name,a.kf_group,a.docking_id,a.docking_name,a.docking_group,b.qc_item_id,c.`group`,b.money,u.name as kf_manager,u1.name as docking_manager")
                    ->order("a.kf_group,a.kf_group desc")
                    ->select();
    }

    /**
     * 质检工作量统计
     * @param  [type] $begin [开始时间]
     * @param  [type] $end   [结束时间]
     * @return array
     */
    public function getWorkStat($begin, $end)
    {
        $map = array(
            "a.time" => array(
                array("EGT",$begin),
                array("ELT",$end)
            ),
            "a.status" => array("NEQ",0)
        );

        $buildSql = M("qc_info")->where($map)->alias("a")
                           ->join("join qz_orders o on a.order_id = o.id")
                           ->field("a.op_uid,a.op_name,case when o.on = 4 and o.type_fw = 1 then 1 end as fen,case when o.on = 4 and o.type_fw = 2 then 1 end as zen,case when o.on = 5 then 1 end as wx,case when o.on = 0 and o.on_sub = 9 then 1 end as cxd,case when o.on = 4 and o.on_sub = 8 then 1 end as sd,case when o.on = 2 then 1 end as dd,FROM_UNIXTIME(a.time,'%Y-%m-%d') as time")
                           ->buildSql();

        $buildSql =  M("qc_info")->table($buildSql)->alias("t")
                            ->group('t.op_uid,time')
                            ->field("t.op_uid,t.op_name,t.time,sum(fen) as fen,sum(zen) as zen,sum(wx) as wx,sum(cxd) as cxd,sum(dd) as dd,sum(sd) as sd")
                            ->order("t.time")
                            ->buildSql();
        return  M("qc_info")->table($buildSql)->alias("t1")
                            ->group("t1.op_uid")
                            ->field("t1.op_uid,t1.op_name,sum(fen) as fen,sum(zen) as zen,sum(wx) as wx,count(t1.op_uid) as count")
                            ->order("t1.op_uid desc")
                            ->select();
    }

    /**
     * 质检工作量统计
     * @param  [type] $begin [开始时间]
     * @param  [type] $end   [结束时间]
     * @return array
     */
    public function getSamplingWorkStat($begin, $end)
    {
        $map = array(
            "a.sampling_time" => array(
                array("EGT",$begin),
                array("ELT",$end)
            ),
            "a.status" => array("EQ",2)
        );

        $buildSql = M("qc_info")->where($map)->alias("a")
                           ->join("join qz_orders o on a.order_id = o.id")
                           ->field("a.sampling_id as op_uid,a.sampling_name as op_name,case when o.on = 4 and o.type_fw = 1 then 1 end as fen,case when o.on = 4 and o.type_fw = 2 then 1 end as zen,case when o.on = 5 then 1 end as wx,FROM_UNIXTIME(a.sampling_time,'%Y-%m-%d') as time")
                           ->buildSql();

        $buildSql =  M("qc_info")->table($buildSql)->alias("t")
                            ->group('t.op_uid,time')
                            ->field("t.op_uid,t.op_name,t.time,sum(fen) as fen,sum(zen) as zen,sum(wx) as wx")
                            ->order("t.time")
                            ->buildSql();
        return  M("qc_info")->table($buildSql)->alias("t1")
                            ->group("t1.op_uid")
                            ->field("t1.op_uid,t1.op_name,sum(fen) as fen,sum(zen) as zen,sum(wx) as wx,count(t1.op_uid) as count")
                            ->select();
    }


    /**
     * 根据合规性获取质检数量
     * @param  intval $op_uid              质检人ID
     * @param  string $conform_regulation  合规性
     * @param  string $time_start          开始时间
     * @param  string $time_end            结束时间
     * @return intval                      数量
     */
    public function getQcInfoCountByConformRegulation($op_uid,$conform_regulation, $time_start, $time_end)
    {
        //质检人ID
        if (!empty($op_uid)) {
            $map['i.op_uid'] = $op_uid;
        }
        //合规性
        if (!empty($conform_regulation)) {
            $map['i.conform_regulation'] = $conform_regulation;
        }
        //抽检时间
        if (!empty($time_start)) {
            $map['i.time'][] = array('EGT', intval($time_start));
        }
        if (!empty($time_end)) {
            $map['i.time'][] = array('LT', intval($time_end));
        }
        return M('qc_info')->alias('i')->where($map)->count();
    }


    /**
     * 根据合规性获取质检列表
     * @param  intval $op_uid              质检人ID
     * @param  string $conform_regulation  合规性
     * @param  string $time_start          开始时间
     * @param  string $time_end            结束时间
     * @param  string $start               开始位置
     * @param  string $end                 偏移量
     * @return array                       列表数组
     */
    public function getQcInfoListByConformRegulation($op_uid,$conform_regulation, $time_start, $time_end, $start, $end)
    {
        //质检人ID
        if (!empty($op_uid)) {
            $map['i.op_uid'] = $op_uid;
        }
        //合规性
        if (!empty($conform_regulation)) {
            $map['i.conform_regulation'] = $conform_regulation;
        }
        //抽检时间
        if (!empty($time_start)) {
            $map['i.time'][] = array('EGT', intval($time_start));
        }
        if (!empty($time_end)) {
            $map['i.time'][] = array('LT', intval($time_end));
        }
        return M('qc_info')->alias('i')
                           ->field('i.time, i.order_id, i.op_name, i.kf_name, q.cname, o.on, o.on_sub, o.type_fw, conform_regulation, conform_regulation_remark')
                           ->join('qz_orders AS o ON o.id = i.order_id')
                           ->join('LEFT JOIN qz_quyu AS q ON q.cid = o.cs')
                           ->join('LEFT JOIN qz_adminuser AS u ON u.id = i.kf_group')
                           ->where($map)
                           ->order('i.time DESC')
                           ->group('i.order_id')
                           ->limit($start, $end)
                           ->select();
    }

    /**

     * 订单推送及时度列表数量
     * @param  [type] $id        [订单编号]
     * @param  [type] $group     [对接客服组]
     * @param  [type] $kf        [对接客服]
     * @param  [type] $status    [推送状态]
     * @param  [type] $time      [耗时]
     * @param  [type] $begin     [分单开始时间]
     * @param  [type] $end       [分单结束时间]
     * @return mix
     */
    public function getOrderPushListCount($id,$group,$kf,$status,$time,$begin,$end)
    {
        $map = array(
           "a.time" => array(
                array("EGT",$begin),
                array("ELT",$end)
            )
        );

        if (!empty($id)) {
            $map["a.order_id"] = array("EQ",$id);
        }

        if (!empty($group)) {
            $map["u.kfgroup"] = array("EQ",$group);
        }

        if (!empty($kf)) {
            $map["a.op_uid"] = array("EQ",$kf);
        }

        if (!empty($status)) {
            $map2["t.status"] = array("EQ",$status);
        }

        if (!empty($time)) {
            if ($time == 1) {
                $map2["t.diff_date"] = array("ELT",60*5);
            } elseif ($time == 2) {
                $map2["t.diff_date"] = array("GT",60*5);
            }
        }

        $buildSql = M("order_docking")->where($map)->alias("a")
                          ->join("left join qz_adminuser u on u.id = a.op_uid")
                          ->join("left join qz_log_wx_ordersend b on b.orderid = a.order_id")
                          ->field("a.order_id,a.op_uid, a.op_uname,u.kfgroup, FROM_UNIXTIME(a.time) as docking_date, FROM_UNIXTIME(b.time_add) as send_date,TIMESTAMPDIFF(SECOND ,FROM_UNIXTIME(a.time), FROM_UNIXTIME(b.time_add)) as diff_date,if(b.time_add is not null,1,2) as status")
                          ->group("a.order_id")
                          ->buildSql();

        return  M("order_docking")->table($buildSql)->where($map2)->alias("t")->count();
    }

    /**
     * 订单推送及时度列表
     * @param  [type] $id        [订单编号]
     * @param  [type] $group     [对接客服组]
     * @param  [type] $kf        [对接客服]
     * @param  [type] $status    [推送状态]
     * @param  [type] $time      [耗时]
     * @param  [type] $begin     [分单开始时间]
     * @param  [type] $end       [分单结束时间]
     * @return mix
     */
    public function getOrderPushList($id,$group,$kf,$status,$time,$begin,$end,$pageIndex,$pageCount)
    {
        $map = array(
           "a.time" => array(
                array("EGT",$begin),
                array("ELT",$end)
            )
        );

        if (!empty($id)) {
            $map["a.order_id"] = array("EQ",$id);
        }

        if (!empty($group)) {
            $map["u.kfgroup"] = array("EQ",$group);
        }

        if (!empty($kf)) {
            $map["a.op_uid"] = array("EQ",$kf);
        }

        if (!empty($status)) {
            $map2["t.status"] = array("EQ",$status);
        }

        if (!empty($time)) {
            if ($time == 1) {
                $map2["t.diff_date"] = array("ELT",60*5);
            } elseif ($time == 2) {
                $map2["t.diff_date"] = array("GT",60*5);
            }
        }

        $buildSql = M("order_docking")->where($map)->alias("a")
                          ->join("left join qz_adminuser u on u.id = a.op_uid")
                          ->join("left join qz_log_wx_ordersend b on b.orderid = a.order_id")
                          ->field("a.order_id,a.op_uid, a.op_uname,u.kfgroup, FROM_UNIXTIME(a.time) as docking_date, FROM_UNIXTIME(b.time_add) as send_date,TIMESTAMPDIFF(SECOND ,FROM_UNIXTIME(a.time), FROM_UNIXTIME(b.time_add)) as diff_date,if(b.time_add is not null,1,2) as status")
                          ->group("a.order_id")
                          ->buildSql();

        return  M("order_docking")->table($buildSql)->where($map2)->alias("t")
                                  ->limit($pageIndex.",".$pageCount)
                                  ->order("t.docking_date desc")
                                  ->select();
    }

    /*
     * 分赠合规性汇总列表
     * @param  intval $op_uid              质检人ID
     * @param  string $conform_regulation  合规性
     * @param  string $time_start          开始时间
     * @param  string $time_end            结束时间
     * @return array                       列表数组
     */
    public function getStatusByRegulationCollect($time_start, $time_end, $op_uid, $conform_regulation)
    {
        //质检人ID
        if (!empty($op_uid)) {
            $map['i.op_uid'] = $op_uid;
        }
        //合规性
        if (!empty($conform_regulation)) {
            $map['i.conform_regulation'] = $conform_regulation;
        } else {
            $map['i.conform_regulation'] = array('IN', array(1,2,3));
        }
        //抽检时间
        if (!empty($time_start)) {
            $map['i.time'][] = array('EGT', intval($time_start));
        }
        if (!empty($time_end)) {
            $map['i.time'][] = array('LT', intval($time_end));
        }
        return M('qc_info')->alias('i')
                           ->field('i.time, i.op_name, o.on, o.type_fw')
                           ->join('qz_orders AS o ON o.id = i.order_id')
                           ->where($map)
                           ->order('i.time DESC')
                           ->group('i.order_id')
                           ->select();
    }

    /**
     * 质检抽检管理
     * @param  integer $orders_id              订单ID
     * @param  integer $orders_cs              订单城市
     * @param  integer $orders_type            订单状态类型
     * @param  string  $orders_time_real_start 订单真实开始时间
     * @param  string  $orders_time_real_end   订单真实结束时间
     * @param  integer $kf_id                  客服ID
     * @param  integer $kf_group               客服团
     * @param  integer $kf_manager             客服师
     * @param  integer $op_uid                 质检人ID
     * @param  integer $status                 质检状态
     * @param  string  $time_start             质检开始时间
     * @param  string  $time_end               质检结束时间
     * @param  string  $sampling_start         抽检开始时间
     * @param  string  $sampling_end           抽检结束时间
     * @return integer                         数量
     */
    public function getSamplingListCount($orders_id = 0, $orders_cs = 0, $orders_type = 0, $orders_time_real_start = '', $orders_time_real_end = '', $kf_id = 0, $kf_group = 0, $kf_manager = 0, $op_uid = 0, $status = 0, $time_start = '', $time_end = '', $sampling_start = '', $sampling_end = '' ,$ids)
    {
        $map = array(
             "i.status" => array("IN",array(1,2)),
        );

        if (count($ids) > 0) {
            $map["o.source"] = array("NOT IN",$ids);
        }

        //订单ID
        if (!empty($orders_id)) {
            $map["o.id"] = array("EQ",$orders_id);
        }
        //订单城市
        if (!empty($orders_cs)) {
            $map["o.cs"] = array("EQ",$orders_cs);
        }
        //订单状态
        if (!empty($orders_type)) {
            switch ($orders_type) {
                case 1:
                    $map["o.on"] = array("EQ",4);
                    $map["o.type_fw"] = array("EQ",1);
                    break;
                case 2:
                    $map["o.on"] = array("EQ",4);
                    $map["o.type_fw"] = array("EQ",2);
                    break;
                case 3:
                    $map["o.on"] = array("EQ",0);
                    $map["o.on_sub"] = array("EQ",9);
                    break;
                case 4:
                    $map["o.on"] = array("EQ",0);
                    $map["o.on_sub"] = array("EQ",8);
                    break;
                case 5:
                    $map["o.on"] = array("EQ",2);
                    break;
                case 6:
                    $map["o.on"] = array("EQ",5);
                    break;
            }
        }
        //订单发布时间
        if(!empty($orders_time_real_start)){
            $map["o.time_real"][] = array("EGT",$orders_time_real_start);
        }
        if(!empty($orders_time_real_end)){
            $map["o.time_real"][] = array("ELT",$orders_time_real_end);
        }
        //客服
        if (!empty($kf_id)) {
            $map["u.id"] = array("EQ",$kf_id);
        }
        //客服团
        if (!empty($kf_group)) {
            $map["u.kfgroup"] = array("EQ",$kf_group);
        }
        //客服师
        if (!empty($kf_manager)) {
            $map["_string"] = "find_in_set($kf_manager,u.kfmanager)";
        }
        //质检人ID
        if (!empty($op_uid)) {
            $map["i.op_uid"] = array("EQ",$op_uid);
        }
        //质检状态
        if (!empty($status)) {
            $map["i.status"] = array("EQ",$status);
        }
        //质检时间
        if (!empty($time_start)) {
            $map['i.time'][] = array('EGT', $time_start);
        }
        if (!empty($time_end)) {
            $map['i.time'][] = array('ELT', $time_end);
        }
        //抽检时间
        if (!empty($sampling_start)) {
            $map["i.sampling_time"][] = array('EGT', $sampling_start);
        }
        if (!empty($sampling_end)) {
            $map["i.sampling_time"][] = array('ELT', $sampling_end);
        }



        return M("qc_info")->alias("i")
                           ->join("join qz_orders o on o.id = i.order_id")
                           ->join("left join qz_order_csos_new new ON new.order_id = o.id")
                           ->join("left join qz_adminuser u on u.id = new.user_id")
                           ->where($map)
                           ->count();
    }

    /**
     * 质检抽检管理
     * @param  integer $orders_id              订单ID
     * @param  integer $orders_cs              订单城市
     * @param  integer $orders_type            订单状态类型
     * @param  string  $orders_time_real_start 订单真实开始时间
     * @param  string  $orders_time_real_end   订单真实结束时间
     * @param  integer $kf_id                  客服ID
     * @param  integer $kf_group               客服团
     * @param  integer $kf_manager             客服师
     * @param  integer $op_uid                 质检人ID
     * @param  integer $status                 质检状态
     * @param  string  $time_start             质检开始时间
     * @param  string  $time_end               质检结束时间
     * @param  string  $sampling_start         抽检开始时间
     * @param  string  $sampling_end           抽检结束时间
     * @param  integer $pageIndex              开始位置
     * @param  integer $pageCount              获取数量
     * @return array                           查询列表
     */
    public function getSamplingList($orders_id = 0, $orders_cs = 0, $orders_type = 0, $orders_time_real_start = '', $orders_time_real_end = '', $kf_id = 0, $kf_group = 0, $kf_manager = 0, $op_uid = 0, $status = 0, $time_start = '', $time_end = '', $sampling_start = '', $sampling_end = '', $pageIndex = 0, $pageCount = 20 ,$ids)
    {
        $map = array(
            "i.status" => array("IN",array(1,2)),
        );

        if (count($ids) > 0) {
            $map["o.source"] = array("NOT IN",$ids);
        }

        //订单ID
        if (!empty($orders_id)) {
            $map["o.id"] = array("EQ",$orders_id);
        }
        //订单城市
        if (!empty($orders_cs)) {
            $map["o.cs"] = array("EQ",$orders_cs);
        }
        //订单状态
        if (!empty($orders_type)) {
            switch ($orders_type) {
                case 1:
                    $map["o.on"] = array("EQ",4);
                    $map["o.type_fw"] = array("EQ",1);
                    break;
                case 2:
                    $map["o.on"] = array("EQ",4);
                    $map["o.type_fw"] = array("EQ",2);
                    break;
                case 3:
                    $map["o.on"] = array("EQ",0);
                    $map["o.on_sub"] = array("EQ",9);
                    break;
                case 4:
                    $map["o.on"] = array("EQ",0);
                    $map["o.on_sub"] = array("EQ",8);
                    break;
                case 5:
                    $map["o.on"] = array("EQ",2);
                    break;
                case 6:
                    $map["o.on"] = array("EQ",5);
                    break;
            }
        }
        //订单发布时间
        if(!empty($orders_time_real_start)){
            $map["o.time_real"][] = array("EGT",$orders_time_real_start);
        }
        if(!empty($orders_time_real_end)){
            $map["o.time_real"][] = array("ELT",$orders_time_real_end);
        }
        //客服
        if (!empty($kf_id)) {
            $map["u.id"] = array("EQ",$kf_id);
        }
        //客服团
        if (!empty($kf_group)) {
            $map["u.kfgroup"] = array("EQ",$kf_group);
        }
        //客服师
        if (!empty($kf_manager)) {
            $map["_string"] = "find_in_set($kf_manager,u.kfmanager)";
        }
        //质检人ID
        if (!empty($op_uid)) {
            $map["i.op_uid"] = array("EQ",$op_uid);
        }
        //质检状态
        if (!empty($status)) {
            $map["i.status"] = array("EQ",$status);
        }
        //质检时间
        if (!empty($time_start)) {
            $map['i.time'][] = array('EGT', $time_start);
        }
        if (!empty($time_end)) {
            $map['i.time'][] = array('ELT', $time_end);
        }
        //抽检时间
        if (!empty($sampling_start)) {
            $map["i.sampling_time"][] = array('EGT', $sampling_start);
        }
        if (!empty($sampling_end)) {
            $map["i.sampling_time"][] = array('ELT', $sampling_end);
        }
        return  M("qc_info")->alias("i")
                           ->field("o.id,o.time_real,o.on,o.type_fw,o.on_sub,u.name as chk_customer,o.fen_customer, i.status as state,i.op_name, i.time AS qc_time,substring_index(u.kfmanager,',',1) as kfmanager,u.kfgroup,u.id as uid,o.cs,o.lasttime, i.sampling_status,tel.type as tel_type,tel.status as tel_status,i.sampling_time,o.source,u.state as ustate,u.uid as uuid,new.user_name as chk_name")
                           ->order("i.status,o.time_real desc")
                           ->join("join qz_orders o on o.id = i.order_id")
                           ->join("left join qz_order_csos_new new ON new.order_id = o.id")
                           ->join("left join qz_adminuser u on u.id = new.user_id")
                           ->join("left join qz_qc_telcenter tel on tel.order_id = i.order_id")
                           ->where($map)
                           ->limit($pageIndex, $pageCount)
                           ->select();
    }

    /**
     * 获取质检时间统计
     * @param  int $id    [质检人员ID]
     * @param  date $begin [质检开始时间]
     * @param  date $end   [质检结束时间]
     * @return array
     */
    public function getQcTimeStat($id,$begin,$end)
    {
        if (!empty($id)) {
            $where .= " and a.op_uid = ".$id;
        }

        $sql = 'select
                t2.op_uid,t2.op_name,
                count(if(t2.on = 4 and t2.type_fw = 1,1,null)) as fen_count,
                count(if(t2.on = 4 and t2.type_fw = 2,1,null)) as zen_count,
                count(if(t2.on = 5,1,null)) as wx_count,
                count(1) as count,
                sum(if(t2.on = 4 and t2.type_fw = 1,t2.time_diff,0)) as fen_time,
                sum(if(t2.on = 4 and t2.type_fw = 2,t2.time_diff,0)) as zen_time,
                sum(if(t2.on = 5,t2.time_diff,0)) as wx_time,
                sum(t2.time_diff) as time_diff,
                sum(if(t2.on = 4 and t2.type_fw = 1,t2.push_time,0)) as fen_push_time,
                sum(if(t2.on = 4 and t2.type_fw = 2,t2.push_time,0)) as zen_push_time,
                sum(if(t2.on = 5,t2.push_time,0)) as wx_push_time,
                sum(t2.push_time) as push_time
                from(
                        select
                        t1.op_uid,t1.op_name,t1.order_id,t1.push_time,t1.on,t1.type_fw,
                        sum(if(t1.time_diff is null,0,t1.time_diff)) as time_diff
                        from (
                            select
                            t.*,TIMESTAMPDIFF(SECOND,tel.starttime,tel.endtime)+1 as time_diff
                            from (
                                    select a.order_id,a.op_uid,a.op_name,a.push_time,o.on,o.type_fw from qz_qc_info a
                                    join qz_orders o on o.id = a.order_id
                                    where a.time >= '.$begin.' and a.time < '.$end.' and ((o.on = 4 and type_fw in (1,2)) or o.on = 5) '.$where.'
                            ) t
                            left join qz_log_telcenter_ordercall cal on cal.orderid = t.order_id
                            left join qz_log_telcenter tel on tel.callsid = cal.callsid  and tel.action = "Hangup"
                        ) t1 group by t1.order_id,t1.op_uid
                ) t2 group by t2.op_uid';
        return $this->db(1,"DB_CONFIG1")->query($sql);
    }


    /**
     * 获取错误项次数
     * @param  int $item    [错误项ID]
     * @param  int $docking [对接客服ID]
     * @param  date $begin   [质检开始时间]
     * @param  date $end     [质检结束时间]
     * @return array
     */
    public function findQcItemCount($item,$docking,$begin,$end)
    {
        $map = array(
            "b.qc_item_id" => array("EQ",$item),
            "a.time" => array(
                array("EGT",$begin),
                array("ELT",$end)
            ),
            "a.docking_id" => array("EQ",$docking)
        );
        $buildSql = M("qc_info")->where($map)->alias("a")
                           ->join("join qz_qc_info_item as b on a.order_id = b.order_id")
                           ->field("a.order_id")
                           ->buildSql();

        return M("qc_info")->table($buildSql)->alias("t")->count();
    }

    /**
     * 添加400数据
     * @param [type] $data [description]
     */
    public function addAllInfoBy400($data)
    {
         return M("qc_info_400")->addAll($data);
    }

    /**
     * 添加53数据
     * @param [type] $data [description]
     */
    public function addAllInfoBy53($data)
    {
         return M("qc_info_53")->addAll($data);
    }

    /**
     * 质检400列表数量
     * @param  [type] $begin [开始时间]
     * @param  [type] $end   [结束时间]
     * @return [type]        [description]
     */
    public function getQcList400Count($begin,$end)
    {
        $map = array(
            "time" => array(
                array("EGT",$begin),
                array("ELT",$end)
            )
        );
        return M("qc_info_400")->where($map)->count();
    }

    /**
     * 质检400列表
     * @param  [type] $begin [开始时间]
     * @param  [type] $end   [结束时间]
     * @return [type]        [description]
     */
    public function getQcList400($begin,$end,$pageIndex,$pageCount)
    {
        $map = array(
            "time" => array(
                array("EGT",$begin),
                array("ELT",$end)
            )
        );

        $buildSql = M("qc_info_400")->where($map)->limit($pageIndex,$pageCount)->order("id desc")->buildSql();

        return M("qc_info_400")->table($buildSql)->alias("t")
                               ->join("join qz_qc_info_item_other b on t.id = b.order_id and qc_item_id in (38,39,40,41)")
                               ->join("left join qz_adminuser c on c.id = t.kf_id")
                               ->join("left join qz_adminuser d on d.id = t.qc_uid")
                                ->join("left join qz_quyu q on q.cid = t.city_id")
                               ->field("t.*,b.qc_item_id,b.money,c.name as kfname,d.name as qcname,q.cname")
                               ->order("t.id desc,b.qc_item_id")
                               ->select();
    }

    /**
     * 质检53列表数量
     * @param  [type] $begin [开始时间]
     * @param  [type] $end   [结束时间]
     * @return [type]        [description]
     */
    public function getQcList53Count($begin,$end)
    {
         $map = array(
            "time" => array(
                array("EGT",$begin),
                array("ELT",$end)
            )
        );
        return M("qc_info_53")->where($map)->count();
    }

    /**
     * 质检53列表
     * @param  [type] $begin [开始时间]
     * @param  [type] $end   [结束时间]
     * @return [type]        [description]
     */
    public function getQcList53($begin,$end,$pageIndex,$pageCount)
    {
        $map = array(
            "time" => array(
                array("EGT",$begin),
                array("ELT",$end)
            )
        );

        $buildSql = M("qc_info_53")->where($map)->limit($pageIndex,$pageCount)->order("id desc")->buildSql();

        return M("qc_info_53")->table($buildSql)->alias("t")
                               ->join("join qz_qc_info_item_other b on t.id = b.order_id ")
                               ->join("join qz_qc_items i on i.id = b.qc_item_id and i.type = 4 and i.status = 1")
                               ->join("left join qz_adminuser c on c.id = t.kf_id")
                               ->join("left join qz_adminuser d on d.id = t.qc_uid")
                               ->field("t.*,b.qc_item_id,b.money,c.name as kfname,d.name as qcname")
                               ->order("t.id desc,i.parentid, i.px")
                               ->select();
    }

     /**
     * 53、400质检统计
     * @return [type] [description]
     */
    public function getWorkStat400($begin,$end)
    {
        $sql = "select
                qc_uid,
                u.name,
                if(sum(count) > 0 AND mark = '400',1,0) as '400mark',
                if(sum(count) > 0 AND mark = '53',1,0) as '53mark',
                sum(IF (mark = '400', count, 0)) AS '400',
                sum(IF (mark = '53', count, 0)) AS '53'
                from (
                    select
                    qc_uid,mark,count
                    from (
                        select qc_uid,'400' as mark,count(id) as count from qz_qc_info_400
                        where time >= $begin and time < $end
                        group by qc_uid
                    ) t
                    union all (
                        select qc_uid,'53' as mark,count(id) as count  from qz_qc_info_53
                        where time >= $begin and time < $end
                        group by qc_uid
                    )
                ) t
                join qz_adminuser u on u.id = t.qc_uid
                group by qc_uid";
        return $this->db(1,"DB_CONFIG1")->query($sql);
    }

    /**
     * 400/53客服负激励统计
     * @param [type] $begin [开始时间]
     * @param [type] $end   [结束时间]
     */
    public function get400QCInfoItemList($begin,$end)
    {
        $sql = "select
                u.`name`,t2.id,t2.kf_id,t2.type,sum(moneycount) as moneycount,
                count(t2.kf_id) as count,mark
                from (
                    select
                    t.kf_id,t.id,t.type,sum(count) as moneycount,mark
                    from (
                        select
                        kf_id,type,count,mark,id
                        from (
                            select kf_id,
                            case when qc_item_id = 38 then '2'
                                     when qc_item_id = 39 then '3'
                                     when qc_item_id = 40 then '4'
                                     when qc_item_id = 41 then '4'
                            end as type,b.money as count,'400' as mark,a.id  from qz_qc_info_400 a
                            join qz_qc_info_item_other b on b.order_id = a.id
                            join qz_qc_items i on i.id = b.qc_item_id and i.`group` = 3 and i.status = 1
                            where time >= $begin and time < $end
                            order by a.order_id,b.qc_item_id
                        ) t
                        union all (
                            select kf_id,
                            case when qc_item_id = 59 then '1'
                                     when qc_item_id = 49 then '2'
                                     when qc_item_id = 50 or qc_item_id = 51 then '3'
                                     when qc_item_id = 60 then '4'
                            end as type,
                            b.money as count,'53' as mark,a.id
                            from qz_qc_info_53 a
                            join qz_qc_info_item_other b on b.order_id = a.id
                            join qz_qc_items i on i.id = b.qc_item_id and i.`group` = 4 and i.status = 1
                            where time >= $begin and time < $end
                            order by a.id,b.qc_item_id
                        )
                    ) t group by t.kf_id,t.type,id
                    ORDER BY kf_id,id,type
                ) t2
                join qz_adminuser u on u.id = t2.kf_id
                group by t2.id,t2.kf_id,t2.type,t2.mark";

            return $this->query($sql);
    }

    /**
     * 删除400/53质检项目
     * @param  [type] $id   [description]
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    public function delQcItemOther($id,$type)
    {
        $map = array(
            "order_id" => array("EQ",$id)
        );
        if ($type == 1) {
            $map["qc_item_id"] = array("IN",array(38,39,40,41));
        } else {
            $map["qc_item_id"] = array("IN",array(42,43,44,45,46,47,48,49,50,51,52,53,59,60));
        }

        return M("qc_info_item_other")->where($map)->delete();
    }
    /**
     * 编辑400客服信息
     * @param  [type] $id   [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function editInfoBy400($id,$data)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("qc_info_400")->where($map)->save($data);
    }

    /**
     * 编辑53客服信息
     * @param  [type] $id   [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function editInfoBy53($id,$data)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("qc_info_53")->where($map)->save($data);
    }

    /**
     * 删除400信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delInfoBy400($id)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("qc_info_400")->where($map)->delete();
    }

    /**
     * 删除53信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delInfoBy53($id)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("qc_info_53")->where($map)->delete();
    }

    /**
     * 获取客服错误项数量
     * @param
     */
    public function errorItemsCount($data){
        if (!empty($data['name'])) {
            $map['name'] = array("LIKE","%".$data['name']."%");
        }

        if (!empty($data['group'])) {
            $map['group'] = $data['group'];
        }

        $map['parentid'] = array('neq',0);

        if (!empty($data['parentid'])) {
            $map['parentid'] = $data['parentid'];
        }


        $map["type"] = array(
            array("EQ",1)
        );
        return M('qc_items')->where($map)->count();
    }

    /**
     * 获取客服错误项
     */
    public function errorItems($data,$start=0, $end=20){
        if (!empty($data['name'])) {
            $map['name'] = array("LIKE","%".$data['name']."%");
        }

        if (!empty($data['group'])) {
            $map['group'] = $data['group'];
        }

        $map['parentid'] = array('neq',0);

        if (!empty($data['parentid'])) {
            $map['parentid'] = $data['parentid'];
        }


        $map["type"] = array(
            array("EQ",1)
        );
        return M('qc_items')->where($map)->order('id desc')->limit($start, $end)->select();
    }

    /**
     * 获取客服错误项/组(通过id)
     */
    public function getErrorItemById($id){
        if (!empty($id)) {
            $map['id'] = array('eq',$id);
        }
        return M('qc_items')->where($map)->find();
    }

    /**
     * 获取错误组
     * @return mixed
     */
    public function errorGroups($data){
        if (!empty($data['name'])) {
            $map['name'] = array("LIKE","%".$data['name']."%");
        }

        $map["type"] = array(
            array("EQ",1),
            array("EQ",0),
            "or"
        );
        $map['parentid'] = array('eq',0);
        return M('qc_items')->where($map)->field('id,name,group')->select();
    }

    /**
     * 添加错误项
     * @param $data
     * @return mixed
     */
    public function addItem($data){
        return M("qc_items")->add($data);
    }

    /**
     * 编辑错误项
     * @param $data
     * @return mixed
     */
    public function editItem($data,$id){
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("qc_items")->where($map)->save($data);
    }

    public function getQcInfoByIds($order_id)
    {
         $map = array(
             "order_id" => array("IN",$order_id)
         );
         return $this->where($map)->field("order_id,op_name")->select();
    }
}