<?php
/**
 * 公装美图
 */
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
class PubmeituController extends HomeBaseController
{
    //是否直接发布
    private $directPost = false;

    public function _initialize(){
        parent::_initialize();
    }

    //列表页
    public function index(){

        $info["location"] = D("Pubmeitu")->getPubmeitulocation();

        $info["fengge"] = D("Pubmeitu")->getPubmeitufengge();

        $info["mianji"] = D("Pubmeitu")->getPubmeitumianji();

        $pageIndex = 1;
        $pageCount = 10;
        $p = I("get.p");
        if(!empty($p)){
            $pageIndex = $p;
        }
        //类型
        $location = I("get.lx");
        if(!empty($location)){
            $condition['lx'] = $location;
            $info["lx"] =$condition['lx'];
        }
        //风格
        $fengge = I("get.fg");
        if(!empty($fengge)){
            $condition['fg'] = $fengge;
            $info["fg"] = $condition['fg'];
        }
        //面积
        $mianji = I("get.mj");
        if(!empty($mianji)){
            $condition['mj'] = $mianji;
            $info["mj"] = $condition['mj'];
        }
        //title或者id
        $title = I("get.title");
        if(!empty($title)){
            $condition['title'] = $title;
            $info["title"] = $condition['title'];
        }

        //title或者id
        $id = I("get.id");
        if(!empty($id)){
            $condition['id'] = $id;
            $info["id"] = $condition['id'];
        }

        $list = $this->getPubmeitu($condition,$pageIndex,$pageCount);

        $info['list'] = $list['list'];
        $info['page'] = $list['page'];

        //dump($info);
        $this->assign("info",$info);
        $this->assign("p",$p);
        $this->display();
    }

    /**
     * 新增编辑
     */
    public function pubimg(){
        $admin = getAdminUser();
        //保存操作
        if(IS_POST){
            $model = D("Pubmeitu");
            $condition = I("post.data");

            //输入的标题
            $title = str_replace(" ", "", $condition['title']);

            //like查询与输入标题相似的全部数据
            $meitu_title = $this->getBiaotiApi($title);

            //该标题已存在
            foreach($meitu_title as $key => $value){
                if($title == str_replace(" ", "", $value['text'])){
                    $this->ajaxReturn(array('data' => '', 'info' => '该标题已存在！', 'status' =>0));
                }
            }

            $id = $condition['caseid'];
            $tags = D('Tags')->getTagIdsByTagNames($condition['tagnames']);
            $data = array(
                "title"=>remove_xss($condition["title"]),
                "location"=>$condition["lx"],
                "fengge"=>$condition["fg"],
                "mianji"=>$condition["mj"],
                'tags' => $tags,
                "description"=>remove_xss($condition["description"]),
                //将英文逗号转换成中文的,通过xss之后再转回来
                "keyword"=>str_replace('，',',',remove_xss(str_replace(',','，',$condition["keyword"]))),
                "type"=>remove_xss($condition["type"]),
                "istop"=>remove_xss($condition["istop"])
            );
            if($data["type"] == 2){
                $data["master"] = remove_xss($condition["master"]);
            }else{
                $data["master"] = "";
            }

            //编辑图片描述
            foreach ($condition['img_id'] as $i => $v) {
                if ($condition['img_id'][$i] == $condition['img_desc'][$i]['upId']) {
                    //查询是否存在 存在才能更新
                    $in = D('pubmeitu')->getMeituImgById($condition['img_id'][$i]);
                    if($in){
                        $save_data = ['description' => htmlspecialchars(strip_tags(htmlspecialchars_decode($condition['img_desc'][$i]['text']), '<a>'))];
                        D('pubmeitu')->editPubmeituImg($condition['img_id'][$i], $save_data);
                    } else {
                        $condition['new_img_desc'][] = htmlspecialchars(strip_tags(htmlspecialchars_decode($condition['img_desc'][$i]['text']), '<a>'));
                    }
                }
            }
            //新增美图
            if(empty($id)){
                $rand = rand(1000,2000);
                $data["pv"] = $rand;//随机文章点击量
                $data['likes'] = rand(500,1000);
                $data['uid'] = $admin["id"];
                $data['uname'] = $admin["name"];
                $data['createtime'] = time();

                if (true == $this->directPost) {
                    if (!empty($condition['addtime'])) {
                        $this->ajaxReturn(array('status' => 0, 'info' => '直接发布的预发布时间需为空！'));
                    }
                    $data["time"] = time();
                    $data['visible'] = '0';
                } else {
                    if (!empty($condition['addtime'])) {
                        $data['time'] = strtotime(date('Y-m-d', strtotime($condition['addtime'])));
                        if($data['time'] < strtotime(date('Y-m-d'))){
                            $this->ajaxReturn(array('status' => 0, 'info' => "预发布日期若不为空则必须大于等于当天日期"));
                        }
                    } else {
                        $data['time'] = 0;
                    }
                    $data['visible'] = '2';
                }
                //初始状态
                $data['init_visible'] = $data["visible"];

                //图片存储
                $imgs = $condition['newImgs'];
                if (count($imgs) < 1) {
                    $this->ajaxReturn(array('status' => 0, 'info' => "请上传美图图片"));
                }
                if(count($imgs) == 1){
                    $data["is_single"] = '1';
                }
                $id = $model->addPubmeitu($data);
                //新增的美图存入数据库
                if(false !== $id){
                    D('LogAdminEditor')->addLogAdminEditor('40','1',$id);
                    //不是预发布时，主动推送美图 0为可见，1为不可见，2为定时发布
                    if($data['visible'] == 0){
                        $url = 'http://meitu.qizuang.com/g'.$id.'.html';
                        $sent = sentMeituToBaidu($url,false);
                        //将返回值写入数据表 qz_www_article_linksubmit
                        $sent = json_decode($sent,true);
                        $end_time = strtotime(date('Y-m-d',time()).' 23:59:59');
                        $now_time = time();
                        $cache_time = $end_time - $now_time;
                        S('Cache:sent:wwwarticle:normal',$sent['remain'],$cache_time);//主动剩余数量
                        $data = [
                            'aid'       => $id,
                            'url'       => $url,
                            'from'      => 1,//来自主站
                            'type'      => 2,//普通推送
                            's_code'    => $sent['success'],
                            'time'      => time()
                        ];
                        D("WwwArticleKeywords")->addLinkSubmit($data);
                    }
                    //保存图片
                    foreach ($imgs as $key => $value) {
                        $subData = array(
                            "caseid"=>$id,
                            "img_path"=>$value["name"],
                            "img_host"=>"qiniu",
                            "description"=>$condition['new_img_desc'][$key],
                            "time"=>time(),
                        );
                        D("pubmeitu")->addPubmeituImg($subData);
                    }
                    $this->ajaxReturn(array('status' => 1, 'info' => '新增成功！'));
                }
                $this->ajaxReturn(array('status' => 0, 'info' => '新增失败！'));
            //编辑美图
            } else {
                //Tags处理
                $meituinfo = D("Pubmeitu")->getOnePubmeitu($id);
                //增加修改时间
                $data["updatetime"] = time();
                //编辑图片存储
                $imgs = $condition['newImgs'];
                if ((count($meituinfo['images']) + count($imgs)) < 1) {
                    $this->ajaxReturn(array('status' => 0, 'info' => "请上传美图图片"));
                }
                //单图套图
                if ((count($meituinfo['images']) + count($imgs)) > 1) {
                    $data['is_single'] = 0;
                } else {
                    $data['is_single'] = 1;
                }
                //直接发布
                if (true == $this->directPost) {
                    if ('2' != $meituinfo['visible']) {
                        $this->ajaxReturn(array('status' => 0, 'info' => '只有预发布状态的美图才可以直接发布！'));
                    }
                    $data["time"] = time();
                    $data['visible'] = '0';
                    //由预发布状态手动改为直接发布，则初始状态也要更改
                    $data['init_visible'] = '0';
                } else {
                    //预发布美图保存，需要调整预发布时间
                    if ('2' == $meituinfo['visible']) {
                        if (!empty($condition['addtime'])) {
                            $data['time'] = strtotime(date('Y-m-d', strtotime($condition['addtime'])));
                            if($data['time'] < strtotime(date('Y-m-d'))){
                                $this->ajaxReturn(array('status' => 0, 'info' => "预发布日期若不为空则必须大于等于当天日期"));
                            }
                        } else {
                            $data['time'] = 0;
                        }
                    }
                }
                //保存
                $result = $model->editPubmeitu($id,$data);
                foreach ($imgs as $key => $value) {
                    $subData = array(
                        "caseid"=>$id,
                        "img_path"=>$value["name"],
                        "img_host"=>"qiniu",
                        "description"=>$condition['new_img_desc'][$key],
                        "time"=>time(),
                    );
                    D("pubmeitu")->addPubmeituImg($subData);
                }
                $this->ajaxReturn(array('status' => 1, 'info' => '编辑成功！'));
            }
        }else{
            //编辑状态
            $id = I("get.id");
            $p = I("get.p");
            if(!empty($id)){
                $meitu = D("pubmeitu")->getOnePubmeitu($id);
                $meitu['img'] = D("pubmeitu")->getPubmeituImg($id);
                $meitu['tagname'] = array_filter(explode(',',$meitu['tagname']));

                //将位置转换成数组
                $meitu["location"] = explode(",", $meitu["location"]);
                //将风格转换成数组
                $meitu["fengge"] = explode(",", $meitu["fengge"]);
                //将颜色转换成数组
                $meitu["mianji"] = explode(",", $meitu["mianji"]);
            }

            //获取类型列表
            $info["location"] = D("Pubmeitu")->getPubmeitulocation();
            //获取风格列表
            $info["fengge"] = D("Pubmeitu")->getPubmeitufengge();
            //获取面积列表
            $info["mianji"] = D("Pubmeitu")->getPubmeitumianji();
            //获取设计师推荐列表
            $info["master"] = D("Pubmeitu")->getDesigner();

            //转移字符
            if ($meitu['img']) {
                foreach ($meitu['img'] as $k => $v) {
                    $meitu['img'][$k]['description'] = strip_tags(html_entity_decode($v['description']),'<a>');
                }
            }

            $this->assign("meitu",$meitu);
            $this->assign("p",$p);
            $this->assign("info",$info);
            $this->display();
        }
    }

    /**
     * 直接发布
     */
    public function directPost()
    {
        $this->directPost = true;
        return $this->pubimg();
    }

    //公装美图--属性
    public function att()
    {
        $type = I("get.type");

        //获取列表
        $pageIndex = 1;
        $pageCount = 10;

        $p = I("get.p");
        if(!empty($p)){
            $pageIndex = $p;
        }

        $keyword = I("get.keyword");
        if(!empty($keyword)){
            $condition['keyword'] = $keyword;
        }

        switch ($type) {
            case 'lx':
                $attname = "类型";
                $condition['type'] = "1";
                $info = $this->getAttlist($condition,$pageIndex,$pageCount);
                break;
            case 'fg':
                $attname = "风格";
                $condition['type'] = "2";
                $info = $this->getAttlist($condition,$pageIndex,$pageCount);
                break;
            case 'mj':
                $attname = "面积";
                $condition['type'] = "3";
                $info = $this->getAttlist($condition,$pageIndex,$pageCount);
                break;
            default:
                redirect('/Pubmeitu/att?type=lx',0);
                break;
        }
        $this->assign("info",$info);
        $this->assign("attname",$attname);
        $this->display();
    }

    public function action()
    {
        if(IS_POST){
            $model = D("Pubmeitu");
            $condition = I("post.data");
            $id = $condition['attid'];
            $data = array(
                "name"=>remove_xss($condition["title"]),
                "px"=>remove_xss($condition["px"]),
                "type"=>remove_xss($condition["type"]),
                "uid"=> session("uc_userinfo.id"),
                "uname"=> session("uc_userinfo.name"),
                "istop"=>remove_xss($condition["istop"]),
                "enabled"=>remove_xss($condition["enabled"])
            );

            //编辑
            if(!empty($id)){
                $i = D("Pubmeitu")->editPubmeituAtt($id,$data);
            }else{
            //新建
                $data['time'] = time();
                $i = D("Pubmeitu")->addPubmeituAtt($data);
            }

            if(false !== $i){
                $this->ajaxReturn(array('data' => $data,'status' => 1));
            }
            $this->ajaxReturn(array('info' => "添加失败!",'status' => 0));
        }else{
            $type = I("get.type");
            switch ($type) {
                case 'lx':
                    $attname = "类型";
                    break;
                case 'fg':
                    $attname = "风格";
                    break;
                case 'mj':
                    $attname = "面积";
                    break;
                default:
                    redirect('/Pubmeitu/action?type=lx',0);
                    break;
            }

            $id = I("get.id");
            if(!empty($id)){
               $att = D("Pubmeitu")->getOneAtt($id);
               $this->assign("att",$att);
            }

            $this->assign("attname",$attname);
            $this->display();
        }
    }

    //删除美图、类型、风格、面积
    public function del(){
        if($_POST){
            $type = I("post.type");
            $id = I("post.id");

            //删除标签时更改标签数量
            /*
            $tagInfo = M("meitu")->where(array('id'=>$id))->find();
            $tags = explode(',',$tagInfo['tags']);
            foreach ($tags as $k => $v) {
                if(!empty($v)){
                    D('Admintags')->setTagsNum($v,'Dec','2');
                }
            }
            */
            //根据类型删除属性
            switch ($type) {
                case 'att':
                    $on = I("post.on");
                    $i = D("Pubmeitu")->delPubmeituAtt($id,$on);
                break;
                case 'img':
                    $i = D("Pubmeitu")->delPubmeitu($id);
                break;
                /*case 'designer':
                    $i = D("Pubmeitu")->delDesigner($id);
                break;*/
                default:
                    $this->ajaxReturn(array("info" => "暂无该类型","status" => 0));
                    break;
            }
            if($i !== false){
                $this->ajaxReturn(array("status" => 1));
            }
            $this->ajaxReturn(array("info" => "删除失败！","status" => 0));

        }
    }

    //编辑时删除图片的
    public function delimg(){
        $id = I("get.id");
        $key = I("get.key");

        if(!empty($id)){
            //取出图片记录
            $info = D('Pubmeitu')->getMeituImgById($id);
            //取出该图片美图记录
            $meitu =D("Pubmeitu")->getOnePubmeitu($info['caseid']);
            //删除图片
            $result = D("Pubmeitu")->delImg($id,$key);
        }
        if($result){
            //判断删除后的图片数量 1：单图 大于1：套图
            //备注：此处可能会因为主从数据库延迟造成数量查询问题
            if ((count($meitu['images']) -1 ) > 1) {
                $save = array('is_single' => 0);
            } else {
                $save = array('is_single' => 1);
            }
            //如果状态不一致，更改状态
            if ($meitu['is_single'] != $save['is_single']) {
                D('Pubmeitu')->editPubmeitu($meitu['id'], $save);
            }
            $log = array(
                'remark' => '删除公装美图图片！',
                'logtype' => 'pubmeitu_delimgbyimgid',
                'action_id' => $meitu['id'],
                'info' => $result
            );
            D('LogAdmin')->addLog($log);
            $this->ajaxReturn(array('data'=>'','info'=>'操作成功！','status'=>1));
        }
        $this->ajaxReturn(array('data'=>'','info'=>'操作失败！','status'=>0));
    }

    //获取公装案例函数
    private function getPubmeitu($condition,$pageIndex,$pageCount){
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);
        $count = D("Pubmeitu")->getPubmeituListCount($condition,($pageIndex-1) * $pageCount,$pageCount);
        if($count > 0){
            import('Library.Org.Page.Page');
            $result = D("Pubmeitu")->getPubmeituList($condition,($pageIndex-1) * $pageCount,$pageCount);
            //查询热门标签
            foreach ($result as $key => $value) {
                $tags =  array_filter(explode(",",$value["tags"]));
                $result[$key]["tagname"] ="";
                if(count($tags) > 0){
                    //查询标签信息
                    $tags = D("Admintags")->getTagsInfo($tags);
                    foreach ($tags as $k => $val) {
                       $result[$key]["tagname"] .=" ".$val["name"];
                    }
                }
            }
            $config  = array("prev","first","last","next");
            $page = new \Page($pageIndex,$pageCount,$count,$config);
            $pageTmp =  $page->show();
            return array("list"=>$result,"page"=>$pageTmp);
        }
    }

    //获取公装案例函数
    private function getAttlist($condition,$pageIndex,$pageCount){
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);
        $count = D("Pubmeitu")->getAttlistCount($condition,($pageIndex-1) * $pageCount,$pageCount);
        if($count > 0){
            import('Library.Org.Page.Page');
            $result = D("Pubmeitu")->getAttlist($condition,($pageIndex-1) * $pageCount,$pageCount);
            foreach ($result as $key => $value) {
                $result[$key]['time'] = date("Y-m-d",$value['time']);
            }

            $config  = array("prev","first","last","next");
            $page = new \Page($pageIndex,$pageCount,$count,$config);
            $pageTmp =  $page->show();
            return array("list"=>$result,"page"=>$pageTmp);
        }
    }


    /**
     *  [getBiaotiApi 获取标题api]
     *  @return [type] [description]
     */
    public function getBiaotiApi($title){
        $tags = D('Pubmeitu')->getBiaotiByName($title, '');
        $data = array();
        foreach ($tags as $k => $v) {
            $data[] = array(
                'id' => $v['id'],
                'text' => $v['title']
            );
        }
        if(!empty($data)){
           return $data;
        }
        return false;
    }

}