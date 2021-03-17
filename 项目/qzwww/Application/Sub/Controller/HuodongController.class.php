<?php
/**
 * 活动
 */
namespace Sub\Controller;
use Sub\Common\Controller\SubBaseController;
class HuodongController extends SubBaseController{


    public function index()
    {
        $this->display();
    }

    public function juranzhijia()
    {
        $bm = $this->bm;
        //限制只有成都能打开
        if('cd' == $bm) {
            
            $this->display();
        } else {
            $this->error();
        }
    }
}