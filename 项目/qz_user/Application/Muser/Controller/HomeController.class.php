<?php
namespace Muser\Controller;
use Common\Model\Logic\RobOrdersLogicModel;
use Muser\Common\Controller\MbaseController;
class HomeController extends MbaseController
{
    protected  $companyinfo = [];
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $this->companyinfo = D('Usercompany')->getUserCompany(session("u_userInfo.id"));
        $this->companyinfo['deliver_area'] = D("User")->getCompanyDeliverAreaid(session("u_userInfo.id"), 'area_id', true);
    }

    public function index()
    {
        //初始化持久化的已读/未读订单的pageindex
        session("order_read_pageindex",1);
        session("order_unread_pageindex",1);
        session("order_rob_pageindex",1);
        session("order_rob_zeng_pageindex",1);
        $readOrder = $this->getOrders(session("u_userInfo.id"),1);
        $this->assign("readOrder",$readOrder["tmp"]);

        $unreadOrder = $this->getOrders(session("u_userInfo.id"),0);
        if(!empty($unreadOrder)){
            $this->assign("unReadOrder",$unreadOrder["tmp"]);
            $this->assign("unReadOrderCount",$unreadOrder["count"]);
        }
        //抢分数据
        $robLogic = new RobOrdersLogicModel();
        $list = $robLogic->getMobileRobOrderList(array_merge($this->companyinfo,session("u_userInfo"),['robtype' => 1]));
        if(count($list) > 0){
            $this->assign("list",$list);
            $tmp = $this->fetch("roblist");
            $this->assign("robOrder",$tmp);
        }
        //抢赠数据
        $zenglist = $robLogic->getMobileRobOrderList(array_merge($this->companyinfo,session("u_userInfo"),['robtype' => 2]));
        if(count($zenglist) > 0){
            $this->assign("list",$zenglist);
            $tmp = $this->fetch("roblist");
            $this->assign("robzengOrder",$tmp);
        }
        //添加修改密码提醒(每隔60天修改一次密码)
        $this->checkPassTime(session("u_userInfo"));
        $this->assign('user_id',session("u_userInfo.id"));
        $this->display();
    }

    public function orderlist()
    {
        if($_POST){
            if(I("post.index") == 0){
                $pageIndex = session("order_read_pageindex");
                session("order_read_pageindex",$pageIndex+1);
                $isread = 1;
            }else if(I("post.index") == 2){
                $pageIndex = session("order_rob_pageindex");
                session("order_rob_pageindex",$pageIndex+1);
            }else if(I("post.index") == 3){
                $pageIndex = session("order_rob_zeng_pageindex");
                session("order_rob_zeng_pageindex",$pageIndex+1);
            }else{
                $pageIndex = session("order_unread_pageindex");
                session("order_unread_pageindex",$pageIndex+1);
                $isread = 0;
            }

            //如果pageindex非数字或者是负数
            $reg = '/^[0-9]*[1-9][0-9]*$/';
            preg_match($reg,$pageIndex,$m);
            if(empty($m)){
                $pageIndex = 1;
            }

            $pageIndex = $pageIndex+1;
            $index = I("post.index");
            if(in_array($index,[2,3])){
                //抢单数据
                $robLogic = new RobOrdersLogicModel();
                $result = $robLogic->getMobileRobOrderList(array_merge($this->companyinfo,session("u_userInfo"),['robtype' => $index - 1]),$pageIndex);
                if(count($result) > 0){
                    $this->assign("list",$result);
                    $result['tmp'] = $this->fetch("roblist");
                }
            }else{
                $result = $this->getOrders(session("u_userInfo.id"),$isread,$pageIndex);
            }
            $status = 0;
            if($result != ""){
                $tmp = $result["tmp"];
                $status = 1;
            }
            $this->ajaxReturn(array("data"=>$tmp,"info"=>"","status"=>$status,'dd'=>$i));
        }
    }

    private function getOrders($company_id, $isread, $pageIndex = 1)
    {
        $pageCount = 10;
        $count = D("Orders")->getMobileOrderListCount($company_id, $isread);
        $rowCount = ceil($count / $pageCount);
        if ($pageIndex > $rowCount) {
            return "";
        }
        $pageIndex = ($pageIndex - 1) * $pageCount;
        $orders = D("Orders")->getMobileOrderList($company_id, $isread, $pageIndex, $pageCount);
        $this->assign("orders", $orders);
        $tmp = $this->fetch("orderlist");
//        if(count($orders) > 0){
//           $orderCount = count($orders);
//        }
        return array("tmp" => $tmp, "count" => $count);
    }

    /**
     * 添加修改密码提醒(每隔30天修改一次密码)
     */
    private function checkPassTime($user)
    {
        $check_alert = S('User:Muser:Home:CheckAlert:id' . $user['id']);
        if ($check_alert != 1 || $check_alert == false) {
            S('User:Muser:Home:CheckAlert:id' . $user['id'], 1, 2592000);
            $this->assign('check_alert', 1);
        }
    }

}