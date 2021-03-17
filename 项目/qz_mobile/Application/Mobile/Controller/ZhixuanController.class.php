<?php

namespace Mobile\Controller;

use Mobile\Common\Controller\MobileBaseController;
use Common\Model\Logic\CompanyLogicModel;

class ZhixuanController extends MobileBaseController{
    public function zxzq(){
        $cityInfo = $this->cityInfo;

        //关键字、描述、标题
        $basic["head"]["title"] = "大数据智选装修公司-齐装网";
        $basic["head"]["keywords"] = "装修公司,如何选择装修公司";
        $basic["head"]["description"] = "装修公司如何选择?大数据分析,三步轻松匹配装修公司,足不出户乐享装修.";

        $this->assign("basic",$basic);

        if($_GET['p'] == '2'){
            $this->display('zx_type');
        }elseif($_GET['p'] == '3'){
            $this->display('make_order');
        }elseif($_GET['p'] == '4'){
            if(I('get.cs')){
                $condition['cs'] = I('get.cs');
            }else if($cityInfo['cid']){
                $condition['cs'] = $cityInfo['cid'];
            }else{
                $condition['cs'] = 320500;
            }
            
            $companylogic = new CompanyLogicModel();
            $companyList = $companylogic->getZongheShiliList($condition);

            $this->assign('companyList', $companyList);
            $this->display('order_result');
        }elseif($_GET['p'] == '5'){
            $this->display('final_result');
        }else{
            $this->display();    
        }
    }

    public function zxzq_xf(){
        //关键字、描述、标题
        $basic["head"]["title"] = "大数据智选装修公司_新房装修-齐装网";
        $basic["head"]["keywords"] = "新房装修公司,如何选择新房装修公司";
        $basic["head"]["description"] = "新房装修公司如何选择?大数据分析,三步轻松匹配新房装修公司,足不出户乐享装修.";
        
        $this->assign("basic",$basic);

        if($_GET['p'] == '2'){
            $this->assign('type', 1);
            $this->display('fengge');
        }else{
            $this->display();
        }
    }

    public function zxzq_bs(){
        //关键字、描述、标题
        $basic["head"]["title"] = "大数据智选装修公司_别墅大宅-齐装网";
        $basic["head"]["keywords"] = "别墅大宅装修公司,如何选择别墅大宅装修公司";
        $basic["head"]["description"] = "别墅大宅装修公司如何选择?大数据分析,三步轻松匹配别墅大宅装修公司,足不出户乐享装修.";
        
        $this->assign("basic",$basic);

        if($_GET['p'] == '2'){
            $this->assign('type', 2);
            $this->display('fengge');
        }else{
            $this->display();
        }
    }

    public function zxzq_jf(){
        //关键字、描述、标题
        $basic["head"]["title"] = "大数据智选装修公司_旧房改造-齐装网";
        $basic["head"]["keywords"] = "旧房改造装修公司,如何选择旧房改造公司";
        $basic["head"]["description"] = "旧房改造装修公司如何选择?大数据分析,三步轻松匹配旧房改造装修公司,足不出户乐享装修.";
        
        $this->assign("basic",$basic);

        if($_GET['p'] == '2'){
            $this->assign('type', 3);
            $this->display('fengge');
        }else{
            $this->display();
        }
    }

    public function zxzq_sy(){
        //关键字、描述、标题
        $basic["head"]["title"] = "大数据智选装修公司_商业办公-齐装网";
        $basic["head"]["keywords"] = "商业装修公司,如何选择商业办公空间装修公司";
        $basic["head"]["description"] = "商业办公装修公司如何选择?大数据分析,三步轻松匹配商业办公装修公司,足不出户乐享装修.";
        
        $this->assign("basic",$basic);

        $this->display();
    }

    public function fb_order(){
        if($_POST['type'] == 1){
            $_POST['lx'] = 1;
            $_POST['lxs'] = 1;
        }elseif($_POST['type'] == 2){
            $_POST['lx'] = 1;
        }elseif($_POST['type'] == 3){
            $_POST['lx'] = 1;
            $_POST['lxs'] = 3;
        }elseif($_POST['type'] == 4){
            $_POST['lx'] = 2;
        }
        unset($_POST['type']);

        $_POST['text'] = $_POST['content'];
        unset($_POST['content']);

        $_POST['fengge'] = $this->fengge($_POST['fengge']);

        R("Common/Zbfb/fb_order");
        die();
    }

    private function fengge($name){
        return [
            '现代简约' => 1,
            '中式/新中式' => 3,
            '欧式/美式' => 2,
            '港式/法式' => 9,
            '东南亚/混搭' => 8,
            '日式/宜家' => 9,
            '田园/地中海' => 5,
            '古典/轻奢' => 4
        ][$name] ?? 9;
    }
}