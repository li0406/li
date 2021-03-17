<?php
/**
 * 订单分配装修公司表
 */

namespace Home\Model\Db;

use Think\Model;

class OrderInfoModel extends Model
{
    protected $tableName = "order_info";

    protected $autoCheckFields = false;

    /**
     * 获取订单已经分配的装修公司
     * @param $order_id
     * @param $field
     * @return mixed
     */
    public function getOrderCompanyInfo($order_id, $field = 'i.*')
    {
        $where = ['order' => ['eq', $order_id]];
        return $this->alias('i')
            ->field($field)
            ->join('qz_user_company c on c.userid = i.com')
            ->where($where)
            ->select();
    }

    /**
     * 直接赠送时 , 查询的可分配公司(抢单池直接赠送 , 和勾选了直接赠送)
     * @param array $company
     * @param string $lx
     * @param string $cs
     * @param integer $type 1.勾选直接赠送 2.抢单池直接赠送
     * @return mixed
     */
    public function getCompanyGetLess($company = [], $lx = '', $cs, $type = 1)
    {
        $where = [
            'u.cs' => ['eq', $cs]
        ];
        $info_where = ' 1=1';
        if (count($company) > 0) {
            $where = ['u.id' => ['in', $company]];
            $start_time = strtotime(date('Y-m') . '-1 00:00:00');
            $end_time = time();
            $company = implode(',', $company);
            $info_where = ' i.addtime BETWEEN ' . $start_time . ' AND ' . $end_time . ' AND i.com in (' . $company . ')';
        }

        if ($lx) {
            if ($lx == 1) {
                $where['c.lx'] = ['in', [1, 3]];
            }
            if ($lx == 2) {
                $where['c.lx'] = ['in', [2, 3]];
            }
        }

        switch ($type){
            case 1:
                //勾选直接赠送 ,仅按照分单少的排序
                $order = 'allot_num,register_time';
                break;
            case 2:
                //抢单池直接赠送 ,先按需要抢单转赠单的,再按分单少的排序
                $order = 'all_give,allot_num,register_time';
                break;
            default:
                $order = '';
        }

        return M('user')->alias('u')
            ->field('u.id com,sum(IF(i.type_fw=2,1,0)) allot_num,u.register_time,c.other_id,co.all_give,c.cooperate_mode')
            ->join('join qz_user_company c ON u.id = c.userid and c.get_gift_order = 1')
            ->join('left join qz_company_order_option co ON co.company_id = u.id and co.all_give = 1')
            ->join('left join qz_order_info i ON u.id = i.com and' . $info_where)
            ->where($where)
            ->group('u.id')
            ->order($order)
            ->select();
    }

    /**
     * 根据城市获取需要全部抢单的装修公司(用于分配到抢单池)
     * @param array $company 查询的公司
     * @param string $lx 订单类型
     * @param string $cs 订单城市
     * @return mixed
     */
    public function getCompanyAllRob($company = [],$lx = '',$cs = '')
    {
        $where = [];
        if (count($company) > 0) {
            $where = ['u.id' => ['in', $company]];
        }
        $where['co.all_rob'] = ['eq', 1];//需要全部抢单
        $where['u.cs'] = ['eq', $cs];//需要订单城市的公司

        if(!$cs){
            return [];
        }
        if ($lx) {
            if ($lx == 1) {
                $where['c.lx'] = ['in', [1, 3]];
            }
            if ($lx == 2) {
                $where['c.lx'] = ['in', [2, 3]];
            }
        }
        $info_where = ' 1=1';
        if (count($company) > 0) {
            $start_time = strtotime(date('Y-m') . '-1 00:00:00');
            $end_time = time();
            $company = implode(',', $company);
            $info_where = ' i.addtime BETWEEN ' . $start_time . ' AND ' . $end_time . ' AND i.com in (' . $company . ')';
        }

        return M('user')->alias('u')
            ->field("u.id com,u.register_time,c.other_id,co.get_other_company,sum(IF(i.type_fw = 2, 1, 0)) zen_num")
            ->join('join qz_user_company c ON u.id = c.userid and c.get_gift_order = 1')
            ->join('join qz_company_order_option co ON co.company_id = u.id and co.start_time <= ' . time() . ' and co.end_time >=' . time())
            ->join('left join qz_order_info i ON u.id = i.com and' . $info_where)
            ->where($where)
            ->group('u.id')
            ->order('zen_num,register_time desc')
            ->select();
    }

    /**
     * 获取订单已经分配的装修公司
     * @param $order_id
     * @return mixed
     */
    public function getOrderCompanyInfoByWhere($where){
        return $this->where($where)->select();
    }

    /**
     * 获取订单分配装修公司
     * @param  [type] $orderid [description]
     * @return [type]          [description]
     */
    public function getOrderCompany($orderid,$field='a.isread,u.jc,u.id as comid,a.type_fw')
    {
        $map = array(
            "a.order" => array("eq", $orderid)
        );

        return $this->alias("a")->where($map)
            ->join('join qz_user u on u.id = a.com')
            ->join('join qz_user_company c on c.userid = u.id')
            ->field($field)
            ->order('u.id')
            ->select();
    }

    public function getFenAndReviewCompanyCount($orderno){
        $where['o.on'] = ['eq',4];
//        $where['o.type_fw'] = ['eq',1];
        $where['o.id'] = ['eq',$orderno];
        return M('orders')->alias('o')
            ->join('qz_order_info cr on cr.order = o.id')
            ->where($where)
            ->count(1);
    }

    public function getLockOrderInfo($map, $field = 'i.*')
    {
        return $this->alias('i')
            ->field($field)
            ->join('qz_user u on u.id = i.com')
            ->join('qz_user_company c on u.id = c.userid')
            ->where($map)
            ->select();
    }


    //根据公司id列表获取公司的未读订单数量
    public function getCompanyNotReadOrder($comidlist)
    {
        $map = [
            "a.com" => array("IN", $comidlist),
            "a.isread" => array("EQ", 0)
        ];

        $subSql = $this->alias("a")->where($map)->field("a.com,a.`order`")->buildSql();

        return M()->table($subSql)->alias("t")
            ->field('t.com,count(t.`order`) ordercount')
            ->group('t.com')
            ->select();
    }

    public function getOrderCompanyReview($order_id){
        $map = array(
            "i.`order`" => array("EQ", $order_id)
        );

        return $this->alias("i")->where($map)
            ->join("left join qz_order_company_review as r on r.orderid = i.`order` and r.comid = i.`com`")
            ->join("left join qz_user as u on u.id = i.`com`")
            ->field(["i.`order`", "i.`com`", "r.status", "u.jc as company_jc", "u.qc as company_qc"])
            ->select();
    }

    // 获取订单分配统计
    public function getOrderAllotStats($order_ids){
        $map = array(
            "i.`order`" => array("IN", $order_ids)
        );

        $list = $this->alias("i")->where($map)
            ->field([
                "i.`order` as order_id",
                "count(i.id) as allot_num"
            ])
            ->group("i.`order`")
            ->select();

        return $list;
    }
}