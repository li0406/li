<?php
/**
 * 推广广告审核静态页
 */
namespace Home\Controller;
use Think\Controller;

class GrandprixController extends Controller{

    //首页
    public function index(){
        $this->display();
    }

    //建材家装广告页
    public function jiancaijz(){
        $this->display();
    }

    //付费广告开户事宜提供用于广告审核
    public function adv(){
        $this->display("adv_a180319");
    }
    //付费广告城市选择页
    public function advcity(){
        $this->display("advcity");
    }

}