<?php
/**
 *  装修公司主动签单表 qz_orders_company_report
 */
namespace Common\Model;
use Think\Model;
class OrderscompanyreportModel extends Model{
    protected $tableName ="orders_company_report";

    /**
     * 获取装修公司自主签单数量
     * @param  [type] $comid     [description]
     * @param  string $on        [description]
     * @param  string $text      [description]
     * @param  [type] $pageIndex [description]
     * @param  [type] $pageCount [description]
     * @return [type]            [description]
     */
    public function getOrdersCount($comid,$on="",$text=""){
        $map = array(
                "order_company_id"=>array("EQ",$comid),
                "deleted"=>array("EQ",0)
                     );
        if($on !== null){
            $map["order_on"] = array("EQ",$on);
        }

        if(!empty($text)){
            $map["_complex"] = array(
                            "tel168"=>array("LIKE","%$text%"),
                            "name"=>array("LIKE","%$text%"),
                            "xiaoqu"=>array("LIKE","%$text%"),
                            "_logic"=>"OR"
                                     );
        }
        return  M("orders_company_report")->where($map)
                                          ->count();

    }

    /**
     * 获取装修公司自主签单
     * @return [type] [description]
     */
    public function getOrders($comid,$on="",$text="",$pageIndex,$pageCount)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $map = array(
                "order_company_id"=>array("EQ",$comid),
                "deleted"=>array("EQ",0)
                     );
        if($on !== null){
            $map["order_on"] = array("EQ",$on);
        }

        if(!empty($text)){
            $map["_complex"] = array(
                            "tel168"=>array("LIKE","%$text%"),
                            "name"=>array("LIKE","%$text%"),
                            "xiaoqu"=>array("LIKE","%$text%"),
                            "_logic"=>"OR"
                                     );
        }
        $buildSql = M("orders_company_report")->where($map)
                                              ->order("time_add desc")
                                              ->limit($pageIndex.",".$pageCount)
                                              ->buildSql();

        return M("orders_company_report")->table($buildSql)->alias("a")
                                         ->join("LEFT JOIN qz_area as b on b.qz_areaid = a.qx")
                                         ->join("LEFT JOIN qz_fengge as c on c.id = a.fengge")
                                         ->join("LEFT JOIN qz_fangshi as e on e.id = a.fangshi")
                                         ->join("LEFT JOIN qz_huxing as f on f.id = a.huxing")
                                         ->field("a.id,a.order_on,a.zxfs,a.yusuanjt as ys,a.name,a.tel168,a.time_add,b.qz_area,c.name as fg,e.name as fs,f.name as hx,e.name as fangshi")
                                         ->order("a.time_add desc")
                                         ->select();

    }

    /**
     * 查询公司的自主签单信息
     * @param  [type] $id    [description]
     * @param  [type] $comid [description]
     * @return [type]        [description]
     */
    public function getOrderById($id,$comid){
        $map = array(
                "a.id"=>array("EQ",$id),
                "a.order_company_id"=>array("EQ",$comid)
                     );
        return M("orders_company_report")->where($map)->alias("a")
                                         ->join("LEFT JOIN qz_area as b on b.qz_areaid = a.qx")
                                         ->join("LEFT JOIN qz_fengge as c on c.id = a.fengge")
                                         ->join("LEFT JOIN qz_jiage as d on d.id = a.yusuan")
                                         ->join("LEFT JOIN qz_huxing as e on e.id = a.huxing")
                                         ->field("a.time_add,a.shi,a.ting,a.wei,a.yusuanjt as yusuan,a.time_qd,a.zxfs,a.name,a.sex,a.tel168,a.xiaoqu,a.dz,a.mianji,a.remarks,b.qz_area,c.name as fengge,e.name as huxing")
                                         ->find();
    }

    /**
     * 编辑订单信息
     * @return [type] [description]
     */
    public function editOrder($id,$data){
        $map = array(
                "id" => array("EQ",$id)
                     );
        return M("orders_company_report")->where($map)
                                         ->save($data);
    }

    /**
     * 添加订单信息
     * @param [type] $data [description]
     */
    public function addOrder($data){
        if ($result = M("orders_company_report")->add($data)) {
            $result = M("orders_company_report")->getLastInsID();
        }
        return $result;
    }
}