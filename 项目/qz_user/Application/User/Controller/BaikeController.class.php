<?php
namespace User\Controller;
use User\Common\Controller\UserCenterBaseController;
class BaikeController extends UserCenterBaseController{

    public function _initialize(){
        if(!isset($_SESSION["u_userInfo"])){
           header("LOCATION:".C('QZ_YUMING_SCHEME')."://u.qizuang.com");
           die();
        }

        //如果城市字段为空，则先选择所在城市
        if(empty($_SESSION["u_userInfo"]["cs"])){
            //获取城市信息
            //$citys = getCityArray();
            //$this->assign("citys", json_encode($citys));
            $citytmp = $this->fetch("Home/citytmp");
            $this->assign("citytmp",$citytmp);
        }
        $this->baseInfo = $this->getUserInfo($_SESSION["u_userInfo"]["id"]);
        $this->baseInfo["unreadsystem"] = $this->getUnSystemNoticeCount($_SESSION["u_userInfo"]["id"],$_SESSION["u_userInfo"]["cs"]);

        //获取邀请设计师的装修公司数量
        $team = D("Team")->getUserTeamInfo($_SESSION["u_userInfo"]["id"]);
        $this->baseInfo["unreadinvite"] = 0;
        if(count($team) <= 0){
            $this->baseInfo["unreadinvite"] =  D("Team")->getInviteCompanyCount($_SESSION["u_userInfo"]["id"]);
        }

        $classid =  $this->baseInfo['classid'];
        $info["user"] = $this->baseInfo;
        $bm = $this->getQuyuByCid($info["user"]['cs']);
        $info["user"]['bm'] = $bm['bm'];
        $this->userInfo = $info;

        if($classid == 1){
            $this->assign("nav",10);
        }
        if($classid == 2){
            $this->assign("nav",10);
        }
        if($classid == 3){
            $this->assign("nav",9);
        }
        $this->assign("title","齐装网");
    }

    //首页
    public function index(){
        $this->_empty() ;
        $area = getUserPosition();
        $this->assign('area',$area);
        $info = $this->userInfo;

        $isVisible = I('get.visible');
        if(!empty($isVisible)){
            $info['hidden_style'] = 'class="active" ';

            $condition['visible'] = array("EQ",'1');
        }else{
            $info['visible_style'] = 'class="active" ';
            $condition['visible'] = array("NEQ",'5');
        }

        $pageIndex = 1;
        $pageCount = 15;
        if(!empty($_GET["p"])){
            $pageIndex = remove_xss($_GET["p"]);
        }

        $condition['orderBy'] = 'a.post_time DESC,id';
        $condition['uid'] = array("EQ",$info["user"]['id']);

        $result = $this->getList($condition,$pageIndex,$pageCount);
        $list = $result['list'];
        foreach ($list as $key => $value) {
            if($value['remove'] == '1'){
                $list[$key]['visible'] = '9';
            }
        }


        $this->assign("list",$list);
        $this->assign('page',$result['page']);
        $this->assign("info",$info);
        $this->display();
    }

    //新增百科
    public function add(){

        //判断是否被封禁
        isBlocked(false);

        $info = $this->userInfo;
        //Action
        $pid = I('get.pid');
        if(!empty($pid)){
            $map['pid'] = $pid;
            $category = D('Baike')->getCategory($map);
            if(!empty($category)){
                $rt = '';
                foreach ($category as $k => $v) {
                    $rt .= '<option value="'.$v["cid"].'">'.$v["name"].'</option>';
                }
                exit($rt);
            }
            die();
        }

        $baike['title'] = '新增';

        //提交了表单
        if(IS_POST){

            //发送间隔为5分钟
            $result = D('Baike')->checkRate($_SESSION["u_userInfo"]['id']);
            if (count($result) > 0) {
                $offset = floor((time() - $result["post_time"])%86400/60);
                if ($offset <= 5) {
                    $this->ajaxReturn(array("data"=>"","info"=>"您的操作过于频繁，请休息5分钟后再试！","status"=>0));
                    exit();
                }
            }

            $tmpPost = I('post.');
            //只取需要的，避免POST其它数据会出错
            $data['uid'] = $_SESSION["u_userInfo"]['id'];
            $data['item'] = $tmpPost['item'];
            $data['title'] = $tmpPost['item'];
            //$data['tags_name'] = $tmpPost['tags'];
            $data['cid'] = $tmpPost['bigcate'];
            $data['sub_category'] = $tmpPost['subcate'];
            $data['description'] = $tmpPost['description'];
            $data['content'] = nl2br($_POST['editorValue']);

            $data['visible'] = '1';
            $data['post_time'] = time();
            $data['source'] = '1';

            if(!empty($tmpPost['area_id'])){
                $data['area_id'] = $tmpPost['area_id'];
            }
            if(!empty($tmpPost['city_id'])){
                $data['city_id'] = $tmpPost['city_id'];
            }

            //如果是装修公司
            if($data['cid'] == '35'){
                $data['description'] = $tmpPost['description_1'];
                $data['content'] = $tmpPost['description_1'].'<p><strong>服务及专长</strong></p>'.$tmpPost['description_2'].'<p><strong>团队及资质</strong></p>'.$tmpPost['description_3'];
                $data['content'] = nl2br($data['content']);
                $data['sub_category'] = '107';
            }

            empty($data['cid']) && $this->ajaxReturn(array("data"=>"","info"=>"分类不能为空！","status"=>0));
            empty($data['sub_category']) && $this->ajaxReturn(array("data"=>"","info"=>"子分类不能为空！","status"=>0));
            empty($data['item']) && $this->ajaxReturn(array("data"=>"","info"=>"条目不能为空！","status"=>0));
            empty($data['content']) && $this->ajaxReturn(array("data"=>"","info"=>"内容不能为空！","status"=>0));

            $images = $_POST['imgId'];
            if(count($images) > 1 ){
                $this->ajaxReturn(array("data"=>"","info"=>"图片最多只能上传1张","status"=>0));
                die();
            }

            if(!empty($images)){
                $data['thumb'] = $images;
            }

            if(mb_strlen($data['content'],'utf-8') > 50000){
                $this->ajaxReturn(array("data"=>"","info"=>"内容最多输入5000个字","status"=>0));
                die();
            }

            //Tags处理：新标签，旧标签，类型
            //$data["tags"] = $this->getTags($data['tags_name'],'','6');

            if(!empty($data['description'])){
                $data['description'] = mbstr(htmlspecialchars(strip_tags($data['description'])),0,200);
            }
            //过滤敏感词
            import('Library.Org.Util.Fiftercontact');
            $filter = new \Fiftercontact();
            $data['content'] = $filter->filter_common($data['content'],array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,5)),"filter_link","filter_html_url"));
            $data['item'] = $filter->filter_common($data['item'],array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,5)),"filter_link"));
            $data['title'] = $filter->filter_common($data['title'],array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,5)),"filter_link","filter_html_url"));
            $data['description'] = $filter->filter_common($data['description'],array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,5)),"filter_link","filter_html_url"));
            $id = M("baike")->add($data);
            $this->ajaxReturn(array("data"=>"$id","info"=>"发布成功","status"=>1));
            die();
        }


        //根据 province ID 输出城市列表 Select # from GET request url
        $cityid = intval(I('get.getcity'));
        if(!empty($cityid)){
            $cityList = D("Common/Ask")->getCityList();
            $theCity = $cityList[$cityid];
            unset($cityList);
            if(!empty($theCity)){
                $rt = '';
                foreach ($theCity as $k => $v) {
                    $rt .= '<option value="'.$v["cityid"].'">'.$v["city"].'</option>';
                }
                exit($rt);
            }
            exit();
        }


        //取用户城市和区域
        $userCityId = $_SESSION["u_userInfo"]['cs'];
        $Db = D("Common/Ask");
        $provinceList = $Db->getAreaList();
        $myProvinceId = $Db->getProvinceIdByCityId($userCityId);
        foreach ($provinceList as $k => $v) {
            if($v['qz_provinceid'] == $myProvinceId){
                $provinceList[$k]['selected'] = 'selected';
                break;
            }
        }
        $allCityList = $Db->getCityList();
        $cityList = $allCityList[$myProvinceId];
        unset($allCityList);
        foreach ($cityList as $k => $v) {
            if($v['cityid'] == $userCityId){
                 $cityList[$k]['selected'] = 'selected';
            }
        }

        $info['user_classid'] = $info['user']['classid'];

        $map['pid'] = '0';
        $category = D('Baike')->getCategory($map);

        $this->assign("provinces",$provinceList);
        $this->assign("citys",$cityList);
        $this->assign("baike",$baike);
        $this->assign("category",$category);
        $this->assign("info",$info);
        $this->display();
    }

    //编辑百科
    public function edit(){

        //判断是否被封禁
        isBlocked(false);

        $info = $this->userInfo;

        $id = I('get.id');
        if(empty($id) OR !is_numeric($id)){
            $this->_error();
        }

        $baike = D('Baike')->getBaike($id,false);

        if($baike['uid'] != $_SESSION["u_userInfo"]['id']){
            $this->_error();
        }

        //只能对未审核的日记编辑
        if(empty($baike) || $baike['visible'] == 0){
            $this->_error();
        }

        //提交了表单
        if(IS_POST){
            $tmpPost = I('post.');
            $id = $tmpPost['id'];
            unset($tmpPost['id']);

            $baike = D('Baike')->getBaike($id,false);

            //只取需要的，避免POST其它数据会出错
            $data['uid'] = $_SESSION["u_userInfo"]['id'];
            $data['item'] = $tmpPost['item'];
            //$data['tags_name'] = $tmpPost['tags'];
            $data['cid'] = $tmpPost['bigcate'];
            $data['sub_category'] = $tmpPost['subcate'];
            $data['description'] = $tmpPost['description'];
            $data['content'] = nl2br($_POST['editorValue']);
            if(empty($tmpPost['area_id'])){
                $data['area_id'] = $tmpPost['area_id'];
            }
            if(empty($tmpPost['city_id'])){
                $data['city_id'] = $tmpPost['city_id'];
            }
            $data['visible'] = '1';
            $data['modify_time'] = time();

            //如果分类是装修公司百科
            if($data['cid'] == '35'){
                $data['description'] = $tmpPost['description_1'];
                $data['content'] = $tmpPost['description_1'].'<p><strong>服务及专长</strong></p>'.$tmpPost['description_2'].'<p><strong>团队及资质</strong></p>'.$tmpPost['description_3'];
                $data['content'] = nl2br($data['content']);
                $data['sub_category'] = '107';
            }

            empty($data['cid']) && $this->ajaxReturn(array("data"=>"","info"=>"分类不能为空！","status"=>0));
            empty($data['sub_category']) && $this->ajaxReturn(array("data"=>"","info"=>"子分类不能为空！","status"=>0));
            empty($data['item']) && $this->ajaxReturn(array("data"=>"","info"=>"条目不能为空！","status"=>0));
            empty($data['description']) && $this->ajaxReturn(array("data"=>"","info"=>"描述不能为空！","status"=>0));
            empty($data['content']) && $this->ajaxReturn(array("data"=>"","info"=>"内容不能为空！","status"=>0));

            $images = $_POST['imgId'];


            if(count($images) > 1 ){
                $this->ajaxReturn(array("data"=>"","info"=>"图片最多只能上传1张","status"=>0));
                die();
            }


            if(!empty($images)){
                $data['thumb'] = $images;
            }

            if(mb_strlen($data['content'],'utf-8') > 5000){
                $this->ajaxReturn(array("data"=>"","info"=>"内容最多输入5000个字","status"=>0));
                die();
            }

            //Tags处理：新标签，旧标签，类型,如果之前是通过审核，则对标签数量进行处理,
            //$data["tags"] = $this->getTags($data['tags_name'],$baike['tags'],'6');

            if(!empty($data['description'])){
                $data['description'] = mbstr(htmlspecialchars(strip_tags($data['description'])),0,200);
            }

            //过滤敏感词
            import('Library.Org.Util.Fiftercontact');
            $filter = new \Fiftercontact();
            $data['content'] = $filter->filter_common($data['content'],array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,5)),"filter_link","filter_url","filter_html_url"));
            $data['item'] = $filter->filter_common($data['item'],array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,5)),"filter_link","filter_url","filter_html_url"));
            $data['title'] = $filter->filter_common($data['title'],array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,5)),"filter_link","filter_url","filter_html_url"));
            $data['description'] = $filter->filter_common($data['description'],array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,5)),"filter_link","filter_url","filter_html_url"));

            $map = array(
                "id" => $id,
                "uid" => $data['uid']
            );

            $s = M("baike")->where($map)->save($data);
            $this->ajaxReturn(array("data"=>"$id","info"=>"发布成功","status"=>1));
            die();
        }

        //取用户城市和区域
        $userCityId = $_SESSION["u_userInfo"]['cs'];
        $Db = D("Common/Ask");
        $provinceList = $Db->getAreaList();
        //dump($provinceList);
        foreach ($provinceList as $k => $v) {
            if($v['qz_provinceid'] == $baike['city_id']){
                $provinceList[$k]['selected'] = 'selected';
                break;
            }
        }


        $allCityList = $Db->getCityList();
        $cityList = $allCityList[$baike['area_id']];
        unset($allCityList);
        foreach ($cityList as $k => $v) {
            if($v['cityid'] == $userCityId){
                 $cityList[$k]['selected'] = 'selected';
            }
        }


        if($baike['cid'] == '35'){
            $temp = str_replace(array('<p><strong>服务及专长</strong></p>','<p><strong>团队及资质</strong></p>'),'@-@-@',$baike['content']);
            $temp = strip_tags($temp);
            $temp = explode('@-@-@', $temp);
            $baike['desc_1'] = $temp['0'];
            $baike['desc_2'] = $temp['1'];
            $baike['desc_3'] = $temp['2'];
        }

        $map['pid'] = '0';
        $category = D('Baike')->getCategory($map);

        $map['pid'] = $baike['cid'];
        $subCate = D('Baike')->getCategory($map);

        //$baike['content'] = htmlspecialchars($baike['content']);
        $baike['act'] = '编辑';

        $info['user_classid'] = $info['user']['classid'];
        $this->assign("category",$category);
        $this->assign("provinces",$provinceList);
        $this->assign("citys",$cityList);
        $this->assign("subCate",$subCate);
        $this->assign("info",$info);
        $this->assign("baike",$baike);
        $this->display();
    }

    //删除百科
    public function remove(){

        //判断是否被封禁
        if(isBlocked() == false){
            $this->ajaxReturn(array("data"=>"","info"=>"您的帐号已被系统封禁！","status"=>0));
        }

        $info = $this->userInfo;
        $id = I('get.id');
        if(empty($id) OR !is_numeric($id)){
            $this->_error();
        }

        $baike = D('Baike')->getBaike($id,false);
        if(empty($baike)){
            $this->_error();
        }

        if($baike['uid'] != $_SESSION["u_userInfo"]['id']){
            $this->_error();
        }

        $i = D("Baike")->remove($id,$_SESSION["u_userInfo"]["id"]);
        if($i !== false){
            //标签处理,如果是已审核删除，则处理标签数量
            if($baike['review'] != 0 && $baike['visible'] == 0){
                $tags = array_filter(explode(',', $baike['tags']));
                foreach ($tags as $k => $v) {
                    D("Tags")->setTagsNum($v,'Dec',$type);
                }
            }
            $this->ajaxReturn(array("data"=>"","info"=>"","status"=>1));
        }
        $this->ajaxReturn(array("data"=>"","info"=>"操作失败,请刷新重新！","status"=>0));
    }

    //上传图片
    public function upload(){
        //判断是否登录
        if(!isset($_SESSION["u_userInfo"])){
           exit('Need Login');
        }
        //上传图片
        if(!empty($_FILES["file"])){
            $file = $_FILES["file"];
            $fileExt = pathinfo($_FILES["file"]['name'], PATHINFO_EXTENSION);
            $result = $this->uploadToQiNiu($file["tmp_name"],$fileExt,'baike');
            if(gettype($result) != "object"){
                echo json_encode(array("error"=>0,"pic"=>$result["key"],"name"=>$result["key"]));
            }else{
                echo json_encode(array("error"=>"图片上传失败,请联系技术部门！"));
            }
        }
        die();
    }


    //上传到七牛服务器
    private function uploadToQiNiu($file,$fileExt,$module){
        import("Library.Org.Qiniu.io",'','.php');
        import("Library.Org.Qiniu.rs",'','.php');
        $bucket = OP('QINIU_BUCKET');
        $accessKey = OP('QINIU_AK');
        $secretKey = OP('QINIU_CK');
        Qiniu_SetKeys($accessKey, $secretKey);
        $putPolicy = new \Qiniu_RS_PutPolicy($bucket);

        $putPolicy->MimeLimit = 'image/jpeg;image/png;image/gif';
        $putPolicy->SaveKey = $module.'/$(year)$(mon)$(day)/$(etag)'.'.'.$fileExt;
        $upToken = $putPolicy->Token(null);
        $putExtra = new \Qiniu_PutExtra();
        $putExtra->Crc32 = 1;
        list($ret, $err) = Qiniu_PutFile($upToken, null, $file, $putExtra);
        if($err == null){
            $result = array(
                    "hash"=>$ret["hash"],
                    "key"=> $ret["key"]
                            );
            return $result;
        }
        return $err;
    }

    //Tags处理：新标签，旧标签，类型
    private function getTags($tagsName,$oldTags,$type){
        //规定字段
        $countField = array('1'=>'article_count','2'=>'meitu_count','3'=>'diary_count','4'=>'ask_count','5'=>'subarticle_count','6'=>'baike_count');
        $countField = $countField[$type];

        //如果新标签和旧标签都为空直接返回 - 没有标签
        if(empty($tagsName) && empty($oldTags)){
            return '';
        }

        //如果新标签为空，旧标签不为空，可以理解为删除标签，由于编辑后状态为未审核，故在审核时统一处理
        /*if(empty($tagsName) && !empty($oldTags)){
            foreach ($oldTags as $k => $v) {
                $v = trim($v);
                if(!empty($v)){
                    D("Tags")->setTagsNum($v,'Dec',$type);
                }
            }
        }*/

        //分割，合并，清理标签
        $tagsName = str_replace(array(' ','，',',','?','？','!','！','~','-','@'),',',$tagsName);
        $tag_arr = array_unique(array_filter(explode(",",$tagsName))); //数组形式
        $tagsName = implode($tag_arr,",");//字符串形式

        //查询标签是否已存在数据库 （数据库中不应有重复标签）
        $isInTags = D("Tags")->findTagsByName($tagsName);

        //重组数据 $newTags 为 数据库中存在的Tags
        foreach ($isInTags as $k => $v){
            $newTags[$v['name']] = $v;
        }

        //原来的Tags
        $oldTags = array_filter(explode(",",$oldTags));

        //循环输入的Tags
        foreach ($tag_arr as $k => $v){
            $v = trim($v);
            //如果值不为空
            if(!empty($v)){
                //如果数据库中已存在此Tags
                if(!empty($newTags[$v])){
                    //记录此Tag的ID
                    $tagsId .= $newTags[$v]['id'].',';
                    //如果本次Tag不存在于之前的Tags
                    /*if(!in_array($newTags[$v]['id'],$oldTags)){
                        //数据库中加 1
                        D("Tags")->setTagsNum($newTags[$v]['id'],'Inc',$type);
                    }*/
                }else{
                    //不存在，增加l，由于是未审核状态，故标签数量为0
                    $tagData = array("name" => $v,"type" => $type,"time" => time(),$countField => '0',);
                    $i = D("Tags")->addTags($tagData);
                    $tagsId .= $i.",";
                }
            }
        }

        //取最新Tags的ID数组
        $newTagsArray = array_unique(array_filter(explode(",",$tagsId)));
        $tags = implode($newTagsArray,",").',';

        //开始删除Tags统计处理
        foreach ($oldTags as $k => $v) {
            if(!in_array($v,$newTagsArray)){
                //从数据库中减1
                D("Tags")->setTagsNum($v,'Dec',$type);
            }
        }
        return $tags;
    }

    //根据条件获取列表并分页
    private function getList($condition,$pageIndex = 1,$pageCount = 10)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        import('Library.Org.Page.Page');
        $result = D("Common/Baike")->getListByCategory($condition,($pageIndex-1) * $pageCount,$pageCount);
        $count = $result['count'];
        $qList = $result['result'];
        $config  = array("prev","first","last","next");
        $page = new \Page($pageIndex,$pageCount,$count,$config);
        $pageTmp =  $page->show();
        return array("list"=>$qList,"page"=>$pageTmp);
    }

    //获取用户基本信息
    private function getUserInfo($uid){
        $map = array(
            "id" => array("EQ",$uid)
        );
        return M('user')->field('*')->where( $map)->find();
    }

    //获取用户 BM 值
    private function getQuyuByCid($cityid){
        $map = array(
            "cid" => array("EQ",$cityid)
        );
        return M('quyu')->field('bm')->where($map)->find();
    }

    //获取用户的未读信息
    private function getUnSystemNoticeCount($id,$cs){
        $count = D("Usersystemnotice")->getUnSystemNoticeCount($id,$cs,1);
        if(count($count)> 0){
            return $count["unreadsystem"];
        }
        return 0;
    }

}