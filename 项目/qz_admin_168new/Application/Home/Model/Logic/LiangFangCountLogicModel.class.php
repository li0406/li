<?php
/**
 * Created by PhpStorm.
 * author: mcj
 * Date: 2018/8/22
 * Time: 13:16
 * 未量房订单/二次回访订单
 */

namespace Home\Model\Logic;


use Common\Enums\OrderStatus;

class LiangFangCountLogicModel
{

    /**
     * 量房统计验证
     * author: mcj
     */
    public function valiLiangFang($data)
    {
        if (!empty($data['time_start']) && !preg_match('/^\d{4}\-\d{1,2}\-\d{1,2}$/', $data['time_start'])) {
            return ['result' => false, 'mes' => '咨询日期截止时间异常'];
        }
        if (!empty($data['time_end']) && !preg_match('/^\d{4}\-\d{1,2}\-\d{1,2}$/', $data['time_end'])) {
            return ['result' => false, 'mes' => '咨询日期截止时间异常'];
        }
        if (!empty($data['time_start']) && !empty($data['time_end'])) {
            if ($data['time_start'] > $data['time_end']) {
                return ['result' => false, 'mes' => '咨询日期开始时间大于截止时间'];
            }
            if (day_diff($data['time_start'],$data['time_end']) >= 31) {
                return ['result' => false, 'mes' => '日期跨度超过一个月'];
            }
        }
        if (!empty($data['order_status']) && !in_array($data['order_status'], [0, 1, 2])) {
            return ['result' => false, 'mes' => '订单状态异常'];
        }
        if (!empty($data['revisit']) && !in_array($data['revisit'], [0, 1, 2])) {
            return ['result' => false, 'mes' => '量房状态异常'];
        }
        return ['result' => true, 'mes' => '验证通过'];
    }

    public function countLiangFang($search_data)
    {
        $map = $this->setMap($search_data);
        return D("Home/Db/LiangFangCount")->countLiangFang($map);
    }

    public function getLiangfang($search_data, $p = 1, $p_size = 20)
    {
        $map = $this->setMap($search_data);
        $skip = ($p - 1) * $p_size;
        return D("Home/Db/LiangFangCount")->getLiangfang($map, $skip, $p_size);

    }
    public function getLiangfangAll($search_data)
    {
        $map = $this->setMap($search_data);
        return D("Home/Db/LiangFangCount")->getLiangfangAll($map);

    }
    /**
     * 静态常量存储查询条件逻辑结果
     * author: mcj
     * @param $data
     * @param bool $cache 是否缓存处理结果默认缓存不重复处理
     * @return array
     */
    public function setMap($data, $cache = true)
    {
        static $map = [];
        if ($cache && !empty($map)) {
            return $map;
        }
        //咨询日期 设置默认查询时间，防止卡死，提高代码茁壮性
        if (!empty($data['time_start'])) {
            $map['time'][] = ['egt', strtotime($data['time_start'] . ' 00:00:00')];
        } else {
            $map['time'][] = ['egt', date("Y-m-d", strtotime("-31 day")) ];
        }
        if (!empty($data['time_end'])) {
            $map['time'][] = ['elt', strtotime($data['time_end'] . ' 23:59:59')];
        } else {
            $map['time'][] = ['elt', time()];
        }
        //订单状态
        if (!empty($data['order_status'])) {
            $map['type_fw'] = ['eq', $data['order_status']];
        }
        //会员量房状态
        if (!empty($data['measure'])) {
            if($data['measure'] == 1){
                $map['measure'] = ['eq', 2];  //未量房
            }elseif($data['measure'] == 2){
                $map['measure'] = ['eq', 3];  //已量房
            }elseif($data['measure'] == 3){  //未知？
                $map['measure'] = array('not in',array(2,3));
            }
        }
        //业主量房状态
        if(!empty($data['yzstatus'])){
            if($data['yzstatus'] == 3){
                $map['yzstatus'] = array('not in',array(1,2));
            }else{
                $map['yzstatus'] = ['eq',$data['yzstatus']];
            }
        }
        //二次回访
        if (!empty($data['revisit'])) {
            if ($data['revisit'] == 1) {
                $map['revisit'] = ['EXP', 'is not null'];
            } else if ($data['revisit'] == 2) {
                $map['revisit'] = ['EXP', 'is null'];
            }
        }
        //订单归属人
        if (!empty($data['uid'])) {
            $map['op_uid'] = ['eq', $data['uid']];
        }
        return $map;
    }


}