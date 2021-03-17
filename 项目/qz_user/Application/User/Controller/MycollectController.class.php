<?php
namespace User\Controller;
use User\Common\Controller\UserBaseController;
class MycollectController extends UserBaseController{
    public function index(){
        //获取装修公司的基本信息
        $info["user"] = $this->baseInfo;
        $type = 1;//默认类型是文章
        if(I("get.type") !== ""){
            switch (I("get.type")) {
                case 'case':
                    //收藏的装修案例
                    $type = 2;
                    $info["active"] = 1;
                    break;
                case 'zixun':
                    //收藏的资讯
                    $type = 3;
                    $info["active"] = 2;
                    $info["isList"] = true;
                    break;
                default:
                    $this->_error();
                    break;
            }
        }

        //获取搜藏列表
        $pageIndex = 1;
        $pageCount = 9;
        if(I("get.p") !== ""){
            $pageIndex = I("get.p");
        }
        $list = $this->getCollectList($_SESSION['u_userInfo']['id'],$type, $pageIndex,$pageCount);
        $info["list"] = $list["list"];
        $info["page"] = $list["page"];
        //侧边栏
        $this->assign("nav",6);
        $this->assign("info",$info);
        $this->display();
    }

    /**
     * 获取收藏列表
     * @return [type] [description]
     */
    private function getCollectList($id,$type,$pageIndex,$pageCount)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $count = D("Usercollect")->getCollectListCount($id,$type);
        if($count > 0){
            import('Library.Org.Page.Page');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \Page($pageIndex,$pageCount,$count,$config);
            $pageTmp =  $page->show();
            $list = D("Usercollect")->getCollectList($id,$type,($page->pageIndex-1)*$pageCount,$pageCount);

            foreach ($list as $key => $value) {
                // 1文章 2案例
                switch ($type) {
                    case '1':
                        if($value["classtype"] == 1){
                            if(empty($value["img_path"])){
                                $img_path = C('QZ_YUMING_SCHEME')."://".C("QZ_YUMINGWWW")."/assets/home/index/img/6.jpg";
                            }else{
                                $img_path = C('QZ_YUMING_SCHEME')."://".C("QINIU_DOMAIN")."/".$value["img_path"]."-w240.jpg";
                            }
                            $src = C('QZ_YUMING_SCHEME')."://".C("QZ_YUMINGWWW")."/gonglue/".$value["shortname"]."/".$value["cid"].".html";
                        }elseif($value["classtype"] == 3){
                            if(empty($value["littleimg_path"])){
                                $img_path = C('QZ_YUMING_SCHEME')."://".C("QZ_YUMINGWWW")."/assets/home/index/img/6.jpg";
                            }else{
                                $img_path = C('QZ_YUMING_SCHEME')."://".C("QINIU_DOMAIN")."/".$value["img_path"]."-w240.jpg";
                            }
                            $src = C('QZ_YUMING_SCHEME')."://".$value["bm"].".".C("QZ_YUMING")."/zxinfo/".$value["shortname"]."/".$value["cid"].".html";
                        }
                        break;
                    case '2':
                        if($value["classtype"] == 2){
                            if($value["img_host"] == "qiniu"){
                                $img_path = C('QZ_YUMING_SCHEME')."://".C("QINIU_DOMAIN")."/".$value["img_path"]."-s3.jpg";
                            }else{
                                $img_path = C('QZ_YUMING_SCHEME')."://".C("STATIC_HOST1").$value["img_path"]."s_".$value["img"];
                            }
                            $src = C('QZ_YUMING_SCHEME')."://".$value["bm"].".".C("QZ_YUMING")."/caseinfo/".$value["cid"].".shtml";
                        }elseif($value["classtype"] == 7){
                            $img_path = C('QZ_YUMING_SCHEME')."://".C("QINIU_DOMAIN")."/".$value["img_path"]."-w660.jpg";
                            $src = C('QZ_YUMING_SCHEME')."://meitu.".C("QZ_YUMING")."/3d-conten".$value["cid"].".html";
                        }else{
                            $list[$key]["classname"] = explode(",", $value["classname"])[0];
                            $img_path = C('QZ_YUMING_SCHEME')."://".C("QINIU_DOMAIN")."/".$value["img_path"]."-s3.jpg";
                            $src = C('QZ_YUMING_SCHEME')."://meitu.".C("QZ_YUMING")."/p".$value["cid"].".shtml";
                        }
                        break;
                    case "3":
                        $src = C('QZ_YUMING_SCHEME')."://".$value["bm"].".".C("QZ_YUMING")."/zixun_info/".$value["cid"].".shtml";
                        break;
                }
                $list[$key]["src"] = $src;
                $list[$key]["img_path"] = $img_path;
            }
            return array("list"=>$list,"page"=>$pageTmp);
        }
        return false;
    }
}