<?php

//客服模块

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

class KefuController extends HomeBaseController{

    public function _initialize(){
        parent::_initialize();
        //判断是否是搜索引擎蜘蛛
        $robotIsTrue = B("Common\Behavior\RobotCheck");
        if (true === $robotIsTrue) {
            $this->assign('robot',1);
        }
        $headerTmp = "";
        if(empty($this->cityInfo["bm"])){
            $t = T("Home@Index:header");
        }else{
            if(!$robotIsTrue){
                $t = T("Sub@Index:header");
            }
            //显示头部导航栏效果
            $this->assign("nav_show",true);
        }
        $headerTmp = $this->fetch($t);
        $this->assign("headerTmp",$headerTmp);
    }

    //首页
    public function index(){
        $category = $this->getCategory();
        $this->assign("category",$category);
        $this->display();
    }

     //分类
    public function category(){
        $url = I('get.id');
        if(empty($url)){
            $this->error();
        }
        $DB = D('Kefu');

        //所有分类
        $category = $this->getCategory();
        $cateInfo = $DB->getCategoryByUrl($url);
        if(empty($cateInfo)){
            $this->error('没有这个分类！');
        }

        //显示此分类下所有文章列表
        $pageIndex = 1;
        $pageCount = 10;
        $condition['cid'] = $cateInfo['cid'];
        if(!empty($_GET["p"])){
            $pageIndex = remove_xss($_GET["p"]);
            $pageContent ="第".$pageIndex."页";
        }

        $result = $this->getList($condition,$pageIndex,$pageCount);

        $this->assign("list",$result["list"]);
        $this->assign("page",$result["page"]);
        $this->assign('category',$category);
        $this->assign('cateInfo',$cateInfo);
        $this->display();
    }

    //获取列表
    private function getList($condition,$pageIndex = 1,$pageCount = 10)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        import('Library.Org.Page.Page');
        $result = D("Kefu")->getList($condition,($pageIndex-1) * $pageCount,$pageCount);
        $count = $result['count'];
        $list = $result['result'];
        $config  = array("prev","first","last","next");
        $page = new \Page($pageIndex,$pageCount,$count,$config);
        $pageTmp =  $page->show();
        return array("list"=>$list,"page"=>$pageTmp,"num"=>$count);
    }

    //获取分类
    private function getCategory($cid = '',$update = false){
        $category = S('Cache:Kefu:Category');
        //如果数据为空
        if(empty($category) || $update != false){
            $tempCategory = D("Kefu")->getCategory();
            $category = array();
            if($tempCategory){
                //先得出一级数组
                foreach ($tempCategory as $k => $v ){
                    if($v['pid'] == '0') {
                        $category[$v['cid']] = $v;
                    }
                    if($v['pid'] != '0') {
                        $category[$v['pid']]['sub_cate'][] = $v;
                    }
                }
            }
            ksort($category);
            S('Cache:Kefu:Category',$category,86400 * 7);
        }
        //根据 Cid 返回
        if(!empty($cid)){
            foreach ($tempCategory as $k => $v ){
                if($v['cid'] == $cid) {
                    return $v;
                    exit;
                }
            }
        }
        return $category;
    }
}