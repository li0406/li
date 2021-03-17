<?php
namespace Sub\Controller;
use Sub\Common\Controller\SubBaseController;
class ZxbjController extends SubBaseController{
    public function _initialize(){
        parent::_initialize();
    }

    public function getDetailsByAjax(){
        if(isset($_COOKIE["w_qizuang_n"])){
            $orderid = $_COOKIE["w_qizuang_n"];
            $order = D("Orders")->getOrderInfoById($orderid);
            if(count($order) > 0){
                $result = $this->calculatePrice($order["mianji"],$order["cs"]);
                $this->ajaxReturn(array("data"=>$result,"info"=>"获取成功！","status"=>1));
            }
        }
        $this->ajaxReturn(array("data"=>'',"info"=>"获取失败，请刷新重试~","status"=>0));
    }


    public function get_city_baojia()
    {
        $cs = I('post.cs');
        if (!empty($cs)) {
            $mianji = [
                100, 125, 90, 110, 150, 200
            ];
            $result = S('Case:Sub:Zxbj:Mortcity:baojia:' . $cs);
            if (empty($result)) {
                $result = $this->calculateMorePrice($mianji, $cs);
                S('Case:Sub:Zxbj:Mortcity:baojia:' . $cs, $result, 21600);
            }
            $this->ajaxReturn(array("data" => $result, "info" => "获取成功！", "error_code" => 0));
        }
        $this->ajaxReturn(array("data" => '', "info" => "获取失败，请刷新重试~", "error_code" => 400));
    }

    /**
     * 计算价格
     * @param  [type] $mianji [面积]
     * @param  [type] $cs [城市]
     * @return [type]         [description]
     */
    private function calculatePrice($mianji,$cs)
    {
        //占比：客厅25% 卧室 18% 厨房 8% 卫生间16% 水电25% 其他 8%
        //计算公式 （城市最低半包单价*120%）*房子的面积

        //获取改订单城市的最低半包价格
        $result = D("Orders")->getCityPrice($cs);
        $price = round( ($result["half_price_min"]+$result["half_price_max"])/2);
        if (empty($price)) {
            $price = 300;
        }

        $total = $price*1.2*$mianji;
        $result['kt'] = $total*0.25 ;
        $result['zw'] = $total*0.18;
        $result['wsj'] = $total*0.16;
        $result['cf'] = $total*0.08;
        $result['sd'] = $total*0.25;
        $result['other'] = $total*0.08;
        $result['total'] = $total;
        return $result;
    }

    /**
     * 计算多面积价格
     * @param  [type] $mianji [面积]
     * @param  [type] $cs [城市]
     * @return [type]         [description]
     */
    private function calculateMorePrice($mianji = [],$cs)
    {
        //占比：客厅25% 卧室 18% 厨房 8% 卫生间16% 水电25% 其他 8%
        //计算公式 （城市最低半包单价*120%）*房子的面积

        //获取改订单城市的最低半包价格
        $result = D("Orders")->getCityPrice($cs);
        $price = round( ($result["half_price_min"]+$result["half_price_max"])/2);
        if (empty($price)) {
            $price = 300;
        }

        $result = [];
        foreach ($mianji as $k=>$v){
            $total = $price*1.2*$v;
            $result[$k]['kt'] = $total*0.25 ;
            $result[$k]['zw'] = $total*0.18;
            $result[$k]['wsj'] = $total*0.16;
            $result[$k]['cf'] = $total*0.08;
            $result[$k]['sd'] = $total*0.25;
            $result[$k]['other'] = $total*0.08;
            $result[$k]['total'] = $total;
        }
        return $result;
    }

}
