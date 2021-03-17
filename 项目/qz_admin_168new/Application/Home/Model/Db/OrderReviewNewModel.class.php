<?php
/**
 * 订单分配装修公司表
 */

namespace Home\Model\Db;

use Think\Model;

class OrderReviewNewModel extends Model
{
    protected $tableName = 'order_review_new';

    public function setMap(array $reqParams)
    {
        foreach ($reqParams as $key => $v) {
            if(!is_array($v)){
                $reqParams[$key] = trim($v);
            }
        }

        $where = $map = [];
        $where['is_delete'] = 1;
        if (!empty($reqParams['search'])) {
            if (strlen($reqParams['search']) == 11) {
                $where['o.tel_encrypt'] = ['eq', order_tel_encrypt($reqParams['search'])];
            } else if (is_numeric($reqParams['search'])) {
                $where['ow.order_id'] = ['eq', $reqParams['search']];
            } else {
                $where['o.xiaoqu'] = ['like', "%{$reqParams['search']}%"];
            }
        }

        if (!empty($reqParams['start']) && !empty($reqParams['end'])) {
            $where["ow.next_visit_time"] = array("BETWEEN", [
                $reqParams["start"],
                $reqParams["end"]
            ]);

        } elseif (!empty($reqParams['start'])) {
            $where['ow.next_visit_time'] = ['egt', $reqParams['start']];
        } elseif (!empty($reqParams['end'])) {
            $where['ow.next_visit_time'] = ['elt', $reqParams['end']];
        }

        if (!empty($reqParams['review_type'])) {
            $where['ow.review_type'] = ['eq', $reqParams['review_type']];
        }

        //已量房 1，未量房 3
        if (!empty($reqParams['remark_type'])) {
            $where['ow.remark_type'] = ['eq', $reqParams['remark_type']];
        }

        if (!empty($reqParams['kf_cs'])) {
            $where['o.cs'] = ['in', $reqParams['kf_cs']];
        }

        if (!empty($reqParams['kf'])) {
            $where['ow.review_uid'] = $reqParams['kf'];
        }

        if (!empty($reqParams['order_type'])) {
            $where['o.type_fw'] = $reqParams['order_type'];
        }

        if (!empty($reqParams['applytel'])) {
            $map['t.`status`'] = $reqParams['applytel'];
        }

        if (!empty($reqParams['is_measure_room'])) {
            //1,2,4 已量房
            $is_measure_room = [1, 2, 4];
            $map['is_measure_room'] = ['in', $is_measure_room];
            if($reqParams['is_measure_room']==1){
                $map['is_measure_room_flag'] = 'in';
            }else {
                $map['is_measure_room_flag'] = 'not in';
            }
        }

        if (!empty($reqParams['company'])) {
            $map["_complex"] = array(
                "u.id" => array("EQ", (int)$reqParams['company']),
                "u.jc" => array("EQ", $reqParams['company']),
                "_logic" => "OR"
            );
        }

        // 新单：回访状态为新单的回访订单，排序：回访时间（正序）
        // 已量房：回访状态为已量单的回访订单，排序：回访时间（正序）
        // 未量房：回访状态为未量单的回访订单，排序：回访时间（正序）
        // 已签单：回访状态为已签单的回访订单，排序：回访时间（倒序）
        // 待定单：回访状态为待定单的回访订单，排序：回访时间（正序）
        // 终结单：回访状态为终结单的回访订单，排序：回访时间（倒序）
        // 订单总览：所有回访状态的回访订单，排序：回访时间（倒序）
        // 排序说明：除上述描述外，根据客服操作时间倒序排列
        
        //回访状态:1.预处理 2.新单 3.已量房 4.未量房 5.已签单 6.待定单 7.终结单
        if (in_array($reqParams['review_type'], [1, 3, 4, 6,])) {
            $order = 'next_visit_time asc,updated_at desc';
        } else {
            $order = 'next_visit_time desc,updated_at desc';
        }

        //排序
        $ret['where'] = $where;
        $ret['map'] = $map;
        $ret['order'] = $order;
        return $ret;
    }

    /**
     * FIXME
     * 获取总数
     * @param array $reqParams
     * @return mixed
     */
    public function getAllCount($uid, array $map)
    {
        $where1= $map['where'];
        $where = $map['map'];
        $map3 = [];
        if (isset($where['_complex']) && !empty($where['_complex'])) {
            $map3 = $where['_complex'];
            unset($where['_complex']);
        }

        $map4 = [];
        $map4Flag = $where['is_measure_room_flag'];
        if (isset($where['is_measure_room']) && !empty($where['is_measure_room'])) {
            $map4['cr.status'] = $where['is_measure_room'];
        }
        unset($where['is_measure_room']);
        unset($where['is_measure_room_flag']);

        //查找所有 符合条件的 qz_order_company_review 订单
        $buildSql0 = $this->alias('tt')
            ->field('tt.order_id')
            ->join('LEFT JOIN qz_order_company_review AS cr ON tt.order_id = cr.orderid')
            ->where($map4)
            ->group('tt.order_id')
            ->getField('order_id',true);

        $map5 = $where1;
        if($map4Flag){
            $map5['ow.order_id'] = [$map4Flag,$buildSql0];
        }
        $buildSql = $this->alias('ow')
            ->field('ow.*,o.tel,o.tel_encrypt,o.cs,o.qx,o.xiaoqu,cr.status cr_status,o.type_fw')
            ->join('JOIN qz_orders AS o ON ow.order_id = o.id')
            ->join('LEFT JOIN qz_order_company_review AS cr ON ow.order_id = cr.orderid')
            ->where($map5)
            ->group('ow.order_id')
            ->buildSql();
        $buildSql1 = $this->table($buildSql)->alias('t')
            ->field('t.*,u.id uid,u.jc,st.tel8 safe_true_tel')
            ->join("join qz_order_info i on i.`order` = t.order_id")
            ->join("join safe_order_tel8 st on st.orderid=t.order_id")
            ->join("join qz_user u on u.id = i.com")
            ->where($map3)
            ->buildSql();


        //找到最新的申请纪录
//        $map2 = [];
//        if (!empty($uid)) {
//            $map2['apply_id'] = ['eq', $uid];
//        }
        $applyTableSql1 = M('order_review_new_apply_tel')->alias('ort1')
            ->field("max(ort1.id) id")
//            ->where($map2)
            ->group('ort1.order_id')
            ->order('id desc')
            ->buildSql();

        $applyTableMap['id'] = ['exp', "in $applyTableSql1"];
        $applyTableSql2 = M('order_review_new_apply_tel')->alias('ort2')
            ->field("id,status,order_id")
            ->where($applyTableMap)
            ->group('ort2.order_id')
            ->buildSql();


        $buildSql3 = $this->table($buildSql1)->alias("ow")
            ->join('JOIN safe_order_tel8 AS sot ON ow.order_id = sot.orderid')
            ->join('LEFT JOIN qz_quyu AS q ON q.cid = ow.cs')
            ->join('LEFT JOIN qz_area AS a ON a.qz_areaid = ow.qx')
            ->join("LEFT JOIN {$applyTableSql2} as t ON t.order_id = ow.order_id")
            ->where($where)
            ->field("ow.order_id")
            ->group("ow.order_id")
            ->buildSql();
        $count = $this->table($buildSql3)->alias('t')->count();

        return $count;
    }

    /**
     * FIXME
     * 获取订单数据
     * @param array $reqParams
     * @param $start
     * @param $end
     * @return mixed
     */
    public function getAll($uid, array $map, $start, $end)
    {
        $where1= $map['where'];
        $where = $map['map'];
        $order = $map['order'];

        $map3 = [];
        if (isset($where['_complex']) && !empty($where['_complex'])) {
            $map3 = $where['_complex'];
        }
        unset($where['_complex']);

        $map4 = [];
        $map4Flag = $where['is_measure_room_flag'];
        if (isset($where['is_measure_room']) && !empty($where['is_measure_room'])) {
            $map4['cr.status'] = $where['is_measure_room'];
        }

        unset($where['is_measure_room']);
        unset($where['is_measure_room_flag']);

        //查找所有 符合条件的 qz_order_company_review 订单
        $buildSql0 = $this->alias('tt')
            ->field('tt.order_id')
            ->join('LEFT JOIN qz_order_company_review AS cr ON tt.order_id = cr.orderid')
            ->where($map4)
            ->group('tt.order_id')
            ->getField('order_id',true);

        $map5 = $where1;
        if($map4Flag){
            $map5['ow.order_id'] = [$map4Flag,$buildSql0];
        }

        $buildSql = $this->alias('ow')
            ->field('ow.*,o.tel,o.tel_encrypt,o.cs,o.qx,o.xiaoqu,o.type_fw,cr.status cr_status')
            ->join('JOIN qz_orders AS o ON ow.order_id = o.id')
            ->join('LEFT JOIN qz_order_company_review AS cr ON ow.order_id = cr.orderid')
            ->where($map5)
            ->group('ow.order_id')
            ->buildSql();
        $buildSql1 = $this->table($buildSql)->alias('t')
            ->field('t.*,u.id uid,u.jc,st.tel8 safe_true_tel')
            ->join("join qz_order_info i on i.`order` = t.order_id")
            ->join("join safe_order_tel8 st on st.orderid=t.order_id")
            ->join("join qz_user u on u.id = i.com")
            ->where($map3)
            ->buildSql();


        //找到最新的申请纪录
//        $map2 = [];
//        if (!empty($uid)) {
//            $map2['apply_id'] = ['eq', $uid];
//        }
        $applyTableSql1 = M('order_review_new_apply_tel')->alias('ort1')
            ->field("max(ort1.id) id")
//            ->where($map2)
            ->group('ort1.order_id')
            ->order('id desc')
            ->buildSql();

        $applyTableMap['id'] = ['exp', "in $applyTableSql1"];
        $applyTableSql2 = M('order_review_new_apply_tel')->alias('ort2')
            ->field("id,status,order_id,pass_time,apply_id")
            ->where($applyTableMap)
            ->group('ort2.order_id')
            ->buildSql();


        return $this->table($buildSql1)->alias("ow")
            ->join('JOIN safe_order_tel8 AS sot ON ow.order_id = sot.orderid')
            ->join('LEFT JOIN qz_quyu AS q ON q.cid = ow.cs')
            ->join('LEFT JOIN qz_area AS a ON a.qz_areaid = ow.qx')
            ->join('LEFT JOIN qz_order_review_new_remark AS nr ON nr.id = ow.remark_type')
            ->join("LEFT JOIN {$applyTableSql2} t ON t.order_id = ow.order_id")
            ->field("ow.*,q.cname AS city,a.qz_area AS area,t.status AS apply_tel_status,t.pass_time,t.apply_id,sot.tel8,nr.name remark_name")
            ->where($where)
            ->order($order)
            ->group("ow.order_id")
            ->limit($start, $end)
            ->select();
    }

    /**
     * 根据订单id查询当前时间是否有跟单小计
     * @param $orderId
     * @param $date
     * @return bool
     */
    public function hasOrderTrack($orderId, $date)
    {
        $map = [];
        $map['order_id'] = ['eq', $orderId];
        $map['track_time'] = ['between', [strtotime($date), strtotime($date) + 86400 - 1]];
        $ret = M('company_track')->where($map)->count();
        return !!$ret;
    }

    public function hasOrderCompanyReview($orderId, $date)
    {
        $map = [];
        $map['orderid'] = ['eq', $orderId];
        $map['time'] = ['between', [strtotime($date), strtotime($date) + 86400 - 1]];
        $ret = M('order_company_review')->where($map)->count();
        return !!$ret;
    }

    public function getInfoByOrder($order_id)
    {
        $where = [
            'order_id' => ['eq', $order_id]
        ];
        return $this->where($where)->find();
    }

    public function editReviewInfo($order_id, $save)
    {
        $cond['order_id'] = $order_id;
        return $this->where($cond)->save($save);
    }

    public function addReviewInfo($save)
    {
        return $this->add($save);
    }

    public function addAllReviewInfo($save)
    {
        return $this->addAll($save);
    }

    public function getDetail($uid,$map)
    {
        $where1 = $map['where'];
        $where = $map['map'];

        $map3 = [];
        if (isset($where['_complex']) && !empty($where['_complex'])) {
            $map3 = $where['_complex'];
        }
        unset($where['_complex']);

        $map4 = [];
        $map4Flag = $where['is_measure_room_flag'];
        if (isset($where['is_measure_room']) && !empty($where['is_measure_room'])) {
            $map4['cr.status'] = $where['is_measure_room'];
        }

        unset($where['is_measure_room']);
        unset($where['is_measure_room_flag']);

        //查找所有 符合条件的 qz_order_company_review 订单
        $buildSql0 = $this->alias('tt')
            ->field('tt.order_id')
            ->join('LEFT JOIN qz_order_company_review AS cr ON tt.order_id = cr.orderid')
            ->where($map4)
            ->group('tt.order_id')
            ->getField('order_id',true);
        $map5 = $where1;
        if($map4Flag){
            $map5['ow.order_id'] = [$map4Flag,$buildSql0];
        }
        $buildSql = $this->alias('ow')
            ->field('ow.*,o.tel,o.tel_encrypt,o.cs,o.qx,o.xiaoqu,cr.status cr_status')
            ->join('JOIN qz_orders AS o ON ow.order_id = o.id')
            ->join('LEFT JOIN qz_order_company_review AS cr ON ow.order_id = cr.orderid')
            ->where($map5)
            ->group('ow.order_id')
            ->buildSql();
        $buildSql1 = $this->table($buildSql)->alias('t')
            ->field('t.*,u.id uid,u.jc,st.tel8 safe_true_tel')
            ->join("join qz_order_info i on i.`order` = t.order_id")
            ->join("join safe_order_tel8 st on st.orderid=t.order_id")
            ->join("join qz_user u on u.id = i.com")
            ->where($map3)
            ->buildSql();


        //找到最新的申请纪录
//        $map2 = [];
//        if (!empty($uid)) {
//            $map2['apply_id'] = ['eq', $uid];
//        }
        $applyTableSql1 = M('order_review_new_apply_tel')->alias('ort1')
            ->field("max(ort1.id) id")
//            ->where($map2)
            ->group('ort1.order_id')
            ->order('id desc')
            ->buildSql();

        $applyTableMap['id'] = ['exp', "in $applyTableSql1"];
        $applyTableSql2 = M('order_review_new_apply_tel')->alias('ort2')
            ->field("id,status,order_id,pass_time,apply_id")
            ->where($applyTableMap)
            ->group('ort2.order_id')
            ->buildSql();


        $data = $this->table($buildSql1)->alias("ow")
            ->join('JOIN safe_order_tel8 AS sot ON ow.order_id = sot.orderid')
            ->join('LEFT JOIN qz_quyu AS q ON q.cid = ow.cs')
            ->join('LEFT JOIN qz_area AS a ON a.qz_areaid = ow.qx')
            ->join('LEFT JOIN qz_order_review_new_remark AS nr ON nr.id = ow.remark_type')
            ->join("LEFT JOIN {$applyTableSql2} t ON t.order_id = ow.order_id")
            ->field("ow.*,q.cname AS city,a.qz_area AS area,t.status AS apply_tel_status,t.pass_time,t.apply_id,sot.tel8,nr.name remark_name")
            ->where($where)
            ->group("ow.order_id")
            ->find();
        return $data ?: [];
    }

    public function getOrderReviewNew($ids)
    {
        if (count($ids) == 0) {
            return [];
        }
        return $this->field('id,order_id')->where(['order_id' => ['in', $ids]])->select();
    }
}
