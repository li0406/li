<?php
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
class ZbController extends HomeBaseController {

    public function _initialize(){
        parent::_initialize();
        //添加顶部搜索栏信息
        $this->assign('serch_uri','companysearch');
        $this->assign('serch_type','装修公司');
        $this->assign('holdercontent','全国超过十万家装修公司为您免费设计');
    }
    public function index(){

        // 跳转到手机端
        if (ismobile()) {
            header("Location: ".$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . '/baojia/');
            exit();
        }

        $zbInfo = S('Cache:Zhaobiao');
        if(!$zbInfo){
            //获取申请装修服务列表
            $zbInfo["orders"] = $this->getOrderList();
            //获取最新业主点评
            $zbInfo["comments"] = $this->getComment();
            S("Cache:Zhaobiao",$zbInfo,3600);
        }
        $info["orders"] = $zbInfo["orders"];
        //随机取6条评论信息
        $info["comment"] = array_slice($zbInfo["comments"], mt_rand(1, count($zbInfo["comments"]) - 6), 6);

        //添加顶部搜索栏信息
        $this->assign('serch_uri','companysearch');
        $this->assign('serch_type','装修公司');
        $this->assign('holdercontent','全国超过十万家装修公司为您免费设计');

        //导航栏标识
        $this->assign("tabIndex",1);
        $this->assign("zbInfo",$info);
        $this->display("index_p220");
    }

    public function sheji()
    {
        // 跳转到手机端
        if (ismobile()) {
            header("Location: ".$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . '/sheji/');
            exit();
        }

        //添加选中效果
        $this->assign('choose_menu', 'sheji');
        //导航栏标识
        $this->assign("tabIndex",2);
        $this->assign("zbInfo",$info);
        $this->display();
    }

    private function getOrderList(){
        // 获取风格
        $fengge = D("Fengge")->getfg();

        import('Library.Org.Util.App');
        $app = new \App();
        $halfCount = 0;
        for ($i = 0; $i < 50 ; $i++) {
            $xing = $app->getRandXing();
            $sex_array = array("先生","女士");
            $sex = $sex_array[rand(0,1)];
            $sub["name"] = $xing.$sex;
            $mianji = rand(80,120);
            $sub["mianji"] = $mianji;
            $leixing_array = array("半包","全包");
            $seed = rand(0,1);
            $halfCount =  $seed == 0? $halfCount+1:$halfCount;
            if($halfCount > 10){
                $seed = 1;
            }
            $jiage = $seed == 0?$mianji *368:$mianji*688;
            $sub["leixing"] = $leixing_array[$seed];
            $sub["jiage"] = round(($jiage/10000),1)."万元";
            $sub["fengge"] = $fengge[rand(0,count($fengge)-1)]["name"];
            $show_time = mt_rand(10, 600);
            if($show_time >= 60){
                $sub["time"] = floor($show_time/60).'分钟前';
            }else{
                $sub["time"] = $show_time.'秒前';
            }
            $data[] = $sub;
        }

        return $data;
    }

    private function getComment(){
        $comment =D("Comment")->getNewComment(50);
        foreach ($comment as $key => $value) {
            $rand = rand(1,10);
            $comment[$key]["uptime"] = $rand."分钟前";
        }
        shuffle($comment);
        return $comment;
    }
}