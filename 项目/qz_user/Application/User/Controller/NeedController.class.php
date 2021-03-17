<?php
namespace User\Controller;
use User\Common\Controller\UserBaseController;
class NeedController extends UserBaseController{
    public function index(){
        //获取基本信息
        $info["user"] = $this->baseInfo;
        //获取当前城市
        $citys = D("Area")->getCityArray($_SESSION["u_userInfo"]["cs"]);
        $citys["shen"] = $citys["shen"][0];
        $citys["shi"] = $citys["shi"][$_SESSION["u_userInfo"]["cs"]];
        $info["citys"] = $citys;
        //获取户型
        $hx = D("Common/Huxing")->gethx();
        $info["huxing"] = $hx;
        //获取装修方式
        $fangshi  =  D("Common/Fangshi")->getfs();
        $info["fangshi"] = $fangshi;
        //获取装修风格列表
        $fg = D("Common/Fengge")->getfg();
        $info["fengge"] = $fg;
        //获取价格列表
        $jiage = D("Common/jiage")->getJiage();
        $info["jiage"] = $jiage;
        //获取室厅卫
        $info["shi"] = array(1,2,3,4,5,6);
        $info["ting"] = array(0,1,2);
        $info["wei"] = array(0,1,2,3);
        $info["chu"] = array(0,1);
        $info["yangtai"] = array(0,1,2,3);
        $this->assign("info",$info);

        //安全验证码
        $safe = getSafeCode();
        $this->assign("safecode",$safe["safecode"]);
        $this->assign("safekey",$safe["safekey"]);
        $this->assign("ssid",$safe["ssid"]);

        //侧边栏
        $this->assign("nav",1);
        $this->display();
    }


    /**
     * 装修需求管理
     * @return [type] [description]
     */
    public function needlist(){
        //获取基本信息
        $info["user"] = $this->baseInfo;
        //获取需求列表
        $pageIndex = 1;
        $pageCount = 10;
        if(I("get.p") !== ""){
            $pageIndex = I("get.p");
        }
        $orders = $this->getNeedList($info["user"]["id"],$info["user"]["tel_safe"],$info["user"]["tel_safe_chk"],$pageIndex,$pageCount);

        $info["orders"] = $orders["orders"];
        $info["page"] = $orders["page"];
        $this->assign("info",$info);
        //侧边栏
        $this->assign("nav",2);
        $this->display();
    }

    private function getNeedList($id,$tel,$safe,$pageIndex,$pageCount)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $count = D("Orders")->getOrdersByIdCount($id,$tel,$safe);
        if($count > 0){
            import('Library.Org.Page.Page');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \Page($pageIndex,$pageCount,$count,$config);
            $pageTmp =  $page->show();
            $orders = D("Orders")->getOrdersById($id,$tel,$safe,($page->pageIndex-1)*$pageCount,$pageCount);
            foreach ($orders as $key => $value) {
                $logos =array_filter( explode(',',$value["logos"]));
                $bm = array_filter(explode(',',$value["bm"]));
                $comid = array_filter(explode(',',$value["comid"]));
                foreach ($logos as $k => $val) {
                    $sub = array(
                            "logo"=>$val,
                            "bm"=>$bm[$k],
                            "comid"=>$comid[$k]
                                 );
                    $orders[$key]["company"][] = $sub;
                }
            }
            return array("orders"=>$orders,"page"=>$pageTmp);
        }
        return null;
    }
}