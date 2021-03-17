<?php
/**
 * 手机web, 单页
 */
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
class MobilesingleController extends HomeBaseController {
    public function index(){
        echo "";
    }

    /**
     * 手机版单页发布招标
     *
     */
    public function szb() {
        $list              = array();
         //获取注册业主数量
        $userCount = S('Cache:Mobile:zb:userCount');
        if (!$userCount) {
            $info["userCount"] = releaseCount("user");
            S('Cache:Mobile:zb:userCount',$info["userCount"]);
            $userCount =  $info["userCount"];
        }

        $list['userCount'] = $userCount; //注册用户数


         //获取预算列表
        $jiage = S('Cache:Mobile:zb:jiage');
        if (!$jiage) {
             $jiage = D("Jiage")->getJiage();
             S('Cache:Mobile:zb:jiage',$jiage);
        }
        $list['jiage'] = $jiage;

        $this->assign('list',$list);
         //安全验证码
        $safe = getSafeCode();
        $this->assign("safecode",$safe["safecode"]);
        $this->assign("safekey",$safe["safekey"]);
        $this->assign("ssid",$safe["ssid"]);

        if (!empty($_GET['cs'])) {
            $this->assign("csbm",$_GET['cs']);
        }
        $this->display();
    }
}