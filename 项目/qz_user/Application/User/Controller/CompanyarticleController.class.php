<?php
namespace User\Controller;
use User\Common\Controller\CompanyBaseController;
class CompanyarticleController extends CompanyBaseController{
    public function index(){
        //基本信息
        $info["user"] = $this->baseInfo;
        //获取装修公司文章列表
        $pageIndex = 1;
        $pageCount = 10;
        if(I("get.p") !== ""){
            $pageIndex = I("get.p");
        }
        $articles = $this->getArticleList($_SESSION["u_userInfo"]["id"],$_SESSION["u_userInfo"]["cs"],$pageIndex,$pageCount,"","",array(1));
        $info["articles"] = $articles["articles"];
        $info["page"] = $articles["page"];
        $this->assign("info",$info);
        //侧边栏
        $this->assign("nav",6);
        $this->display();
    }

    /**
     * 新增/编辑 文章
     * @return [type] [description]
     */
    public function articleup(){
        import('Library.Org.Util.Fiftercontact');
        $filter = new \Fiftercontact();
        if($_POST){
            if (session("u_userInfo.on") != 2) {
                $this->ajaxReturn(array("data"=>"","info"=>"您暂时还不能发送文章,请稍后再试！","status"=>0));
            }

            //为了避免SESSION的丢失,ON字段获取不到,现从数据库中查询ON字段
            $userInfo = D("User")->getSingleUserInfoById($_SESSION["u_userInfo"]["id"]);
            if(count($userInfo) == 0){
                 $this->ajaxReturn(array("data"=>"","info"=>"您的登录超时了,请重新登录！","status"=>0));
            }
            $content =htmlspecialchars_decode(I("post.content"));
            if($userInfo["on"] == 2){
                $content = $filter->filter_common($content,array(array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(3,4,5)),"filter_empty","filter_link")));
            }else{
                $content = $filter->filter_text($content);
            }

            $title =  $filter->filter_title(I("post.title"));
            $data = array(
                    "type"=>I("post.type"),
                    "title"=>$title,
                    "text"=> $content,
                    "gjz"=>I("post.keywords"),
                    "uon"=>0,
                    "cs"=>$_SESSION["u_userInfo"]["cs"],
                    "subtitle"=>I("post.subtitle")
                          );
            $infoModel = D("Info");
            $infoModel->startTrans();
            if(I("post.id") !== ""){
                //编辑提交
                $id = I("post.id");
                $data["uptime"] = time();
                $i = $infoModel->editInfo($id,$_SESSION["u_userInfo"]["id"],$data);
                $bm = $_SESSION['ubm']['bm'];
                S('Cache:SubCompanyArticle:'.$bm.':'.$id,NULL);
                header("customer:3");
                $msg = "用户编辑文章【".$title."】 成功";
            }else{
                //新增提交
                $data["uid"] = $_SESSION["u_userInfo"]["id"];
                $data["time"] = time();
                $data["on"] = 0;
                if($userInfo["on"] == 2){
                    //会员公司默认已审核
                    $data["on"] = 1;
                }
                $id =  $i = $infoModel->addInfo($data);
                $msg = "用户添加文章【".$title."】 成功";
                //更用户表中的新文章数量
                D("User")->editUvAndPv($_SESSION["u_userInfo"]["id"],"info");
                header("customer:1");
                //新增的时候同时同步user表中的文章跟新时间
                $saveData = array(
                        "info_time"=>time()
                                  );

                $i = D("User")->edtiUserInfo($_SESSION["u_userInfo"]["id"],$saveData);
                header("customer:2");
            }
            if($i !== false){
                //导入扩展文件
                import('Library.Org.Util.App');
                $app = new \App();
                //记录日志
                $data = array(
                  "username"=>$_SESSION["u_userInfo"]["name"],
                  "userid"=>$_SESSION["u_userInfo"]["id"],
                  "ip"=>$app->get_client_ip(),
                  "user_agent"=>$_SERVER["HTTP_USER_AGENT"],
                  "info"=>$msg,
                  "time"=>date("Y-m-d H:i:s"),
                  "action"=>CONTROLLER_NAME."/".ACTION_NAME
                );
                D("Loguser")->addLog($data);
                header("customer:4");
                $infoModel->commit();
                $this->ajaxReturn(array("data"=>$id,"info"=>"","status"=>1));
            }else{
                $infoModel->rollback();
            }
            $this->ajaxReturn(array("data"=>"","info"=>"操作失败！","status"=>0));
        }else{
            if(I("get.id") !== ""){
                //编辑状态
                $id = I("get.id");
                $article = D("Info")->getInfoById($id,$_SESSION["u_userInfo"]["id"]);
                //$article["text"] = preg_replace("/\s/","",$article["text"]);
                $article["text"] = $filter->filter_empty($article["text"]);
                $info["article"] = $article;
            }
            //文章分类
            $articleType = D("Infotype")->getTypes();
            //删除优惠信息,暂时先删除最后一个
            array_pop($articleType);
            $info["articleType"] = $articleType;
            //基本信息
            $info["user"] = $this->baseInfo;
            $this->assign("info",$info);
             //tab栏
            $this->assign("tabNav",1);
            //侧边栏
            $this->assign("nav",6);
            $this->display();
        }
    }

    /**
     * 优惠活动
     */
    public function activityinfo(){
        //基本信息
        $info["user"] = $this->baseInfo;
        if($info["user"]["on"] != 2){
            //如果不是会员公司,跳转到后台首页
            header("Location:".C('QZ_YUMING_SCHEME')."://u.qizuang.com/home/");
        }
        //获取装修公司活动信息列表
        $pageIndex = 1;
        $pageCount = 10;
        if(I("get.p") !== ""){
            $pageIndex = I("get.p");
        }
        $articles = $this->getArticleList($_SESSION["u_userInfo"]["id"],$_SESSION["u_userInfo"]["cs"],$pageIndex,$pageCount,1);
        $info["articles"] = $articles["articles"];
        $info["page"] = $articles["page"];
        $this->assign("info",$info);

        //侧边栏
        $this->assign("nav",11);
        $this->display();
    }

    /**
     * 新增/编辑 优惠活动
     * @return [type] [description]
     */
    public function activityup(){
        import('Library.Org.Util.Fiftercontact');
        $filter = new \Fiftercontact();
        if($_POST){
            if($_SESSION["u_userInfo"]["on"] == 2){
                $content = htmlspecialchars_decode(I("post.content"));
                $content =  $filter->filter_common($content,array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,4,5)),"filter_script"));
                $title =  $filter->filter_title(I("post.title"));
                $data = array(
                        "type"=>1,
                        "title"=> $filter->filter_common(I("post.title"),array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,4,5)),"filter_script")),
                        "text"=>$content,
                        "uon"=>0,
                        "cs"=>$_SESSION["u_userInfo"]["cs"],
                        "start"=>strtotime(I("post.start")),
                        "end"=>strtotime(I("post.end")),
                        "subtitle"=>$filter->filter_common(I("post.remark"),array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,4,5)),"filter_script"))
                );
                $infoModel = D("Info");
                $infoModel->startTrans();
                if(I("post.id") !== ""){
                    //编辑提交
                    $id = I("post.id");
                    $data["uptime"] = time();
                    $i = $infoModel->editInfo($id,$_SESSION["u_userInfo"]["id"],$data);
                    $msg = "用户编辑最新活动【".$title."】 成功";
                }else{
                    //新增提交
                    $data["uid"] = $_SESSION["u_userInfo"]["id"];
                    $data["time"] = time();
                    $data["on"] = 1;
                    $id = $i = $infoModel->addInfo($data);
                    $msg = "用户添加最新活动【".$title."】 成功";
                    //更用户表中的新文章数量
                    D("User")->editUvAndPv($_SESSION["u_userInfo"]["id"],"info");
                    //新增的时候同时同步user表中的文章跟新时间
                    $saveData = array(
                            "info_time"=>time()
                                      );
                    $i = D("User")->edtiUserInfo($_SESSION["u_userInfo"]["id"],$saveData);
                }

                //统计此公司优惠活动
                $userid = $_SESSION["u_userInfo"]["id"];
                $tempData['sales_count'] = M('info')->where(array('type'=>'1','on'=>'1','uid' => $userid))->count();
                M("user")->where(array('id' => $userid))->save($tempData);

                if($i !== false){
                    $infoModel->commit();
                    //导入扩展文件
                    import('Library.Org.Util.App');
                    $app = new \App();
                    //记录日志
                    $data = array(
                      "username"=>$_SESSION["u_userInfo"]["name"],
                      "userid"=>$_SESSION["u_userInfo"]["id"],
                      "ip"=>$app->get_client_ip(),
                      "user_agent"=>$_SERVER["HTTP_USER_AGENT"],
                      "info"=>$msg,
                      "time"=>date("Y-m-d H:i:s"),
                      "action"=>CONTROLLER_NAME."/".ACTION_NAME
                    );
                    D("Loguser")->addLog($data);
                    $this->ajaxReturn(array("data"=>$id,"info"=>"","status"=>1));
                }else{
                     $infoModel->rollback();
                }
            }
            $this->ajaxReturn(array("data"=>"","info"=>"操作失败！","status"=>0));
        }else{
            if(I("get.id") !== ""){
                //编辑状态
                $id = I("get.id");
                $article = D("Info")->getInfoById($id,$_SESSION["u_userInfo"]["id"]);
                $article["start"] = empty($article["start"])?"":$article["start"];
                $article["end"] = empty($article["end"])?"":$article["end"];
                $article["text"] = $filter->filter_common($article["text"],array("filter_empty",""));
                $info["article"] = $article;
            }
            //基本信息
            $info["user"] = $this->baseInfo;
            //tab栏
            $this->assign("tabNav",1);
            //侧边栏
            $this->assign("nav",11);
            $this->assign("info",$info);
            $this->display();
        }
    }

    /**
     * 删除文章
     * @return [type] [description]
     */
    public function delarticle(){
        if($_POST){
            $id = I("post.id");
            $i = D("Info")->delInfo($id,$_SESSION["u_userInfo"]["id"]);
            if($i !== false){
                //导入扩展文件
                import('Library.Org.Util.App');
                $app = new \App();
                //记录日志
                $data = array(
                  "username"=>$_SESSION["u_userInfo"]["name"],
                  "userid"=>$_SESSION["u_userInfo"]["id"],
                  "ip"=>$app->get_client_ip(),
                  "user_agent"=>$_SERVER["HTTP_USER_AGENT"],
                  "info"=>"用户删除文章【id:".$id."】 成功",
                  "time"=>date("Y-m-d H:i:s"),
                  "action"=>CONTROLLER_NAME."/".ACTION_NAME
                );
                D("Loguser")->addLog($data);
                $this->ajaxReturn(array("data"=>"","info"=>"","status"=>1));
            }
        }
        $this->ajaxReturn(array("data"=>"","info"=>"操作失败！","status"=>0));
    }

    private function getArticleList($comid,$cs,$pageIndex,$pageCount,$type,$active,$notClass)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $count = D("Info")->getArticlesByComIdCount($comid,$cs,$type,$active,$notClass,true);
        if($count > 0){
            import('Library.Org.Page.Page');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \Page($pageIndex,$pageCount,$count,$config);
            $pageTmp =  $page->show();
            $articles = D("Info")->getArticlesByComId($comid,$cs,($page->pageIndex-1)*$pageCount,$pageCount,$type,$active,$notClass,true);

            foreach ($articles as $key => $value) {
                $articles[$key]["overmark"] = false;
                if(!empty($value["end"]) && $value["end"] >time() ){
                    $articles[$key]["overmark"] = true;
                }
            }
            return array("articles"=>$articles,"page"=>$pageTmp);
        }
    }
}

