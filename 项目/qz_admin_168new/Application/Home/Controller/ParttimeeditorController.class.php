<?php

// 兼职编辑相关功能控制器

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;

class ParttimeeditorController extends HomeBaseController {

    // 文章列表
    public function wwwarticle(){

    }


    // 兼职编辑文章统计分析
    public function articleanalysis(){

        $this->assign("data", $data);
        $this->display();
    }
}