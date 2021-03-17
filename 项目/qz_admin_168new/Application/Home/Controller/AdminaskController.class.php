<?php

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\ThematicWordsLogicModel;
use Home\Model\Service\ElasticsearchServiceModel;

class AdminaskController extends HomeBaseController{

    //构造
    public function _initialize(){
        parent::_initialize();
        $this->assign("endtime",date('Y-m-d H:i'));
    }

    //问答后台首页
    public function index(){
        $info['userCount'] = M("user")->count();
        $info['askCount'] = M("ask")->count();
        $info['ask_anwser'] = M("ask_anwser")->count();
        $info['ask_category'] = M("ask_category")->count();
        $info['ask_comment'] = M("ask_comment")->count();
        $info['ask_file'] = M("ask_file")->count();
        $info['ask_seouser'] = M("ask_seouser")->count();
        //总查看数
        $info['sumViews'] = M("ask")->sum('views');
        //最多回答
        $info['maxAsk'] = M("ask")->field('id,title,views,anwsers')->order('anwsers DESC')->limit('0,15')->select();
        //最多回答
        $info['maxViews'] = M("ask")->field('id,title,views')->order('views DESC')->limit('0,15')->select();
        //无人回答数
        $info['noAnswer'] = M("ask")->where('anwsers = 0')->count();

        $this->assign("info",$info);
        $this->assign("nav",7);
        $this->assign("host",'http://'.C('QZ_YUMINGWWW'));
        $this->display();
    }

    //首页精华管理
    public function dist(){
        //从qz_option表中取 #赶时间，代码写的比较乱
        $Db = D("Adminask");
        $action = str_replace('/adminask/dist/','',$_SERVER['REQUEST_URI']);
        //从配置表中取精华数据
        $opt = $Db->getOption('ask_dist');
        $distList = unserialize($opt['option_value']);
        $num = count($distList);
        $this->assign("jumpUrl", '/adminask/dist/');
        //增加
        if($action == 'add'){
            if($num >= 3){
                 $this->error('首页精华只能有三个');
            }
            if($_POST){
                $tempId = explode('|',$_POST['title']);
                $qid = $tempId['0'];
                if(empty($qid) || !is_numeric($qid)){
                    $this->error('问题不存在');
                }
                $data['id'] = $qid;
                $data['order'] = $_POST['order'];
                $data['title'] = $tempId['1'];
                $data['add_time'] = time();
                $data['img'] = $_POST['imgId']['0'];
                if(empty($data['img'])){
                    $this->error('没有选择图片');
                }

                //如果表中没有精华这个字段
                if(empty($distList)){
                    $s['0'] = $data;
                    $value = serialize($s);
                    $newid = $Db->addOption('ask_dist',$value);
                    if (isset($newid)){
                        $this->success('增加成功！');
                    }else{
                        $this->error('增加失败！');
                    }
                    exit();
                }
                foreach ($distList as $k => $v) {
                    if($v['id'] != $qid){
                        $newList[] = $v;
                    }
                }
                $newList[] = $data;
                //dump($newList);
                $s = $this->multi_array_sort($newList,'order');

                $value = serialize($s);

                $newid = $Db->editOption('ask_dist',$value);
                if (isset($newid)){
                    $this->success('增加成功！');
                }else{
                    $this->error('增加失败！');
                }
                exit();
            }else{
                $info['btn'] = '增加精华';
                $info['imgBtn'] = '增加图片';
                $this->assign('info',$info);
                $this->assign("nav",7);
                $this->display('distadd');
                exit();
            }
        }

        //修改
        if(!empty($_GET['edit']) || is_numeric($_GET['edit'])){
            $id = $_GET['edit'];
            foreach ($distList as $k => $v) {
                if($v['id'] == $id){
                    $info = $v;
                    break;
                }
            }
            if(empty($info)){
                $this->error('没有这个精华问题');
                exit();
            }
            if($_POST){
                $tempId = explode('|',$_POST['title']);
                $qid = $tempId['0'];
                if(empty($qid) || !is_numeric($qid)){
                    $this->error('问题不存在');
                }
                $data['id'] = $qid;
                $data['title'] = $tempId['1'];
                $data['add_time'] = time();
                $data['img'] = $_POST['imgId']['0'];
                $data['order'] = $_POST['order'];
                if(empty($data['img'])){
                    $data['img'] = $_POST['oldImg'];
                }
                foreach ($distList as $k => $v) {
                    if($v['id'] != $id){
                        $newList[] = $v;
                    }
                }
                $newList[] = $data;
                //dump($newList);
                $s = $this->multi_array_sort($newList,'order');
                //dump($s);
                //exit();

                $value = serialize($s);
                $newid = $Db->editOption('ask_dist',$value);
                if (isset($newid)){
                    $this->success('修改成功！');
                }else{
                    $this->error('修改失败！');
                }
                exit();
            }else{
                $info['btn'] = '修改精华';
                $info['imgBtn'] = '修改图片';
                $this->assign('info',$info);
                $this->display('distedit');
            }
            exit();
        }

        //dump($distList);
        $this->assign('distList',$distList);
        $this->assign("nav",7);
        $this->display();
    }

    //首页用户排行
    public function topUser(){
        //从qz_option表中取 #赶时间，代码写的比较乱
        $Db = D("Adminask");
        $action = str_replace('/adminask/topuser/','',$_SERVER['REQUEST_URI']);
        //从配置表中取精华数据
        $opt = $Db->getOption('ask_topuser');
        $userList = unserialize($opt['option_value']);
        $num = count($userList);
        $this->assign("jumpUrl", '/adminask/topuser/');
        //增加
        if($action == 'add'){
            if($num >= 3){
                 $this->error('首页热门用户只能有三个');
            }
            if($_POST){

                $tempId = explode('|',$_POST['username']);
                $uid = $tempId['0'];
                if(empty($uid) || !is_numeric($uid)){
                    $this->error('用户不存在');
                }
                $data['uid'] = $uid;
                $data['order'] = $_POST['order'];
                $data['username'] = $tempId['1'];
                $data['add_time'] = time();

                //如果表中没有用户这个字段
                if(empty($userList)){
                    $s['0'] = $data;
                    $value = serialize($s);
                    $newid = $Db->addOption('ask_topuser',$value);
                    if (isset($newid)){
                        $this->success('增加成功！');
                    }else{
                        $this->error('增加失败！');
                    }
                    exit();
                }
                foreach ($userList as $k => $v) {
                    if($v['uid'] != $uid){
                        $newList[] = $v;
                    }
                }
                $newList[] = $data;
                //dump($newList);
                $s = $this->multi_array_sort($newList,'order');

                $value = serialize($s);
                $newid = $Db->editOption('ask_topuser',$value);
                if (isset($newid)){
                    $this->success('增加成功！');
                }else{
                    $this->error('增加失败！');
                }
                exit();
            }else{
                $info['btn'] = '增加用户';
                $this->assign('info',$info);
                $this->display('topuseradd');
                exit();
            }
        }

        //修改
        if(!empty($_GET['edit']) || is_numeric($_GET['edit'])){
            $id = $_GET['edit'];
            foreach ($userList as $k => $v) {
                if($v['uid'] == $id){
                    $info = $v;
                    break;
                }
            }
            if(empty($info)){
                $this->error('没有这个用户');
                exit();
            }
            if($_POST){

                $tempId = explode('|',$_POST['username']);
                $uid = $tempId['0'];
                if(empty($uid) || !is_numeric($uid)){
                    $this->error('用户不存在');
                }
                $data['uid'] = $uid;
                $data['order'] = $_POST['order'];
                $data['username'] = $tempId['1'];
                $data['add_time'] = time();
                foreach ($userList as $k => $v) {
                    if($v['uid'] != $id){
                        $newList[] = $v;
                    }
                }
                $newList[] = $data;
                $s = $this->multi_array_sort($newList,'order');
                $value = serialize($s);
                $newid = $Db->editOption('ask_topuser',$value);
                if (isset($newid)){
                    $this->success('修改成功！');
                }else{
                    $this->error('修改失败！');
                }
                exit();
            }else{
                $info['btn'] = '修改用户';
                $this->assign('info',$info);
                $this->display('topuseredit');
            }
            exit();
        }

        $this->assign('userList',$userList);
        $this->assign("nav",7);
        $this->display();
    }

    //问题列表
    public function question(){
        //生成排序
        $sortby = array(
            '0'=> array('id'=> '0','name'=> '添加时间','sql'=> 'a.id DESC'),
            '1'=> array('id'=> '1','name'=> '标题','sql'=> 'a.title DESC'),
            '2'=> array('id'=> '2','name'=> '分类','sql'=> 'a.sub_category DESC'),
            '3'=> array('id'=> '3','name'=> '用户','sql'=> 'u.name DESC'),
            '4'=> array('id'=> '4','name'=> '答案','sql'=> 'a.anwsers DESC'),
            '5'=> array('id'=> '5','name'=> '状态','sql'=> 'a.status DESC'),
            '6'=> array('id'=> '6','name'=> '显示时间','sql'=> 'a.post_time DESC'),
        );
        //分页
        $pageIndex = 1;
        $pageCount = 15;
        if(!empty($_GET["p"])){
            $pageIndex = $_GET["p"];
        }
        //分类
        if(!empty($_GET["c"])){
            $condition['cateId'] = $_GET["c"];
        }
        //用户
        if(!empty($_GET["u"])){
            $condition['uid'] = $_GET["u"];
        }
        //状态
        if(isset($_GET["status"])){
            $condition['status'] = $_GET["status"];
        }
        //状态
        if(isset($_GET["dist"])){
            $condition['dist'] = $_GET["dist"];
        }
         //状态
        if(isset($_GET["remove"])){
            $condition['remove'] = $_GET["remove"];
        }
        //排序
        if(isset($_GET["sortby"])){
            $by = $_GET["sortby"];
            if($by > 6 || $by < 0 ){
                $this->error('排序错误');
            }
            $sortby[$by]['now'] = 'selected';
            $condition['orderBy'] = $sortby[$by]['sql'];
        }else{
            $sortby['0']['now'] = 'selected';
            $condition['orderBy'] = 'a.id DESC';
        }
        //搜索
        if(!empty($_GET["keyword"])){
            $condition['keyword'] = $_GET["keyword"];
        }

        //时间限制
        $start_time = $_GET['start_time'];
        $end_time = $_GET['end_time'];
        if(!empty($start_time)){
            $condition['start_time'] = strtotime($start_time);
            $info['start_time'] = $start_time;
        }
        if(!empty($end_time)){
            $explodeTime = explode('-',$start_time);
            $condition['end_time'] = mktime(23,59,59,$explodeTime['1'],$explodeTime['2'],$explodeTime['0']);
            $info['end_time'] = $end_time;
        }

        //开始时间和结束时间
        $begin = mktime(0,0,0,date("m"),date("d"),date("Y"));
        $end   = mktime(23,59,59,date("m"),date("d"),date("Y"));

        $visibleArray = array(
            '0'=>array('id'=>'9','name'=>'请选择'),
            '1'=>array('id'=>'0','name'=>'已通过审核'),
            '2'=>array('id'=>'1','name'=>'未通过审核'),
            '3'=>array('id'=>'3','name'=>'待发布'),
        );

        //状态
        if(isset($_GET["visible"])){
            if($_GET["visible"] == '0'){
                $condition['visible'] = '0';
                $visibleArray['1']['now'] = 'selected';
            } else if($_GET["visible"] == '1'){
                $condition['visible'] = '1';
                $visibleArray['2']['now'] = 'selected';
            } else if($_GET["visible"] == '3'){
                $condition['visible'] = '3';
                $visibleArray['3']['now'] = 'selected';
            }
        }

        $info['visibleArray'] = $visibleArray;

        $result = $this->getQList($condition,$pageIndex,$pageCount);

        $qList = $result['qList'];
        foreach ($qList as $k => $v) {
            $qList[$k]['dist'] = $v['is_distillate'] == '1' ? '<span title="精华" class="label label-primary ico icon-trophy"> </span>' : '';
            $qList[$k]['status_ico'] = $v['status'] == '0' ? '' : '<span title="已解决" class="label label-success fa fa-check-circle-o"> </span>';
            $qList[$k]['anwsers'] = $v['anwsers'] == '0' ? '' : $v['anwsers'];
        }

        $this->assign("sortby",$sortby);
        $this->assign("list",$qList);
        $this->assign('page',$result['page']);
        $this->assign("info",$info);
        $this->assign("host",'http://'.C('QZ_YUMINGWWW'));
        $this->display();
    }

    //批量通过审核
    public function passAll(){
        if(IS_POST){
            $data = $_POST['allde'];
            if(!empty($data)){
                $ids = [];
                foreach ($data as $key => $value) {
                    $ids[] = $value['id'];
                }
                $result = M('ask')->field('id,tags,visible')->where(array('id'=>array('IN', $ids)))->select();
                foreach ($result as $key => $value) {
                    if($value['visible'] != 0){
                        $flag = M('ask')->where(array('id'=>$value['id']))->save(array('visible'=>'0'));
                        if($flag){
                            D('Admintags')->editTagCountByTagIds('',$value['tags'],'ask_count');
                        }
                    }
                }
                $this->ajaxReturn(array('info'=>'操作成功','status'=>1));
            }
            $this->ajaxReturn(array('info'=>'操作失败！','status'=>0));
        }
        $this->ajaxReturn(array('info'=>'操作失败','status'=>0));
    }

    //批量删除回答
    public function removeAnwserAll(){
        if(IS_POST){
            $data = $_POST['allde'];

            if(!empty($data)){
                foreach ($data as $key => $value) {
                    //答案update  visible = 1   $value['id']
                    $flag = M('ask_anwser')->where(array('id'=>$value['id']))->save(array('visible'=>'1'));
                    //重新统计问题有效答案数 qid visible
                    //查询问题的采纳答案 best_aid 如果等于当前ID 则update为null
                    $result = M('ask_anwser')->field('id,qid,uid,visible')->where(array('id'=>$value['id']))->select();
                    $qid = $result[0]['qid'];
                    $best_aid = M('ask')->field('id,best_aid')->where(array('id'=>$qid))->select();
                    if($best_aid[0]['best_aid'] == $value['id']){
                        $data['best_aid'] = '';
                    }
                    $qidcount = M("ask_anwser")->where("qid = $qid and visible = 0")->count();
                    $data['anwsers'] = $qidcount;
                    $res_ask = M('ask')->where(array('id'=>$qid))->save($data);//更新问题表答案数

                    //重新统计用户有效回答数 uid visible
                    $uid = $result[0]['uid'];
                    $uidcount = M("ask_anwser")->where("uid = $uid and visible = 0")->count();
                    $res_user = M('user')->where(array('id'=>$uid))->save(array('ask_anwsers'=>$uidcount));//更新用户表回答数
                }
                $this->ajaxReturn(array('data'=>'','info'=>'操作成功','status'=>1));
            }
            $this->ajaxReturn(array('data'=>'','info'=>'操作失败！','status'=>0));
        }
        $this->ajaxReturn(array('data'=>'','info'=>'操作失败','status'=>0));
    }

    //问题增加
    public function add(){
        $Db = D("Adminask");
        //取分类和城市
        $result = $this->getCategoryCity();
        $ask['area_id']  = $result['provinceId'];
        $ask['city_id']  = $result['cityId'];
        $provinceList = $result['provinceList'];

        $ask['is_show'] = 'style="display:none"';
        $ask['btn']  = '增加';
        $ask['act_title'] = '增加问题';

        //提交了
        if($_POST){
            //新增均为已审核通过（待发布）
            $data['review'] = '1';
            $data['source'] = 2;
            $data['visible'] = 3;
            $data['create_time'] = time();
            $data['post_time'] = 0;
            $data['modify_time'] = 0;

            $data['area_id'] = $_POST['area_id'];
            $data['city_id'] = $_POST['city_id'];
            $data['cid'] = $_POST['cid'];
            $data['sub_category'] = $_POST['sub_category'];
            $user = $this->autoUser($_POST['username']);
            $data['uid'] = $user['uid'];
            $data['username'] = $user['username'];
            $data['views'] = mt_rand(100,1000);

            //Tag 处理,操作成功后更新标签统计数量
            $tags = D('Admintags')->getTagIdsByTagNames($_POST['tagnames'],2);
            $tagid = '';
            $tagname = '';
            foreach ($tags as $key => $value) {
                $tagid = $tagid . $key . ',';
                $tagname = $tagname . $value . ',';
            }
            $data['tags'] = trim($tagid, ',');
            $data['tags_name'] = trim($tagname, ',');


            if(mb_strlen($_POST['title'],'utf-8') > 50){
                $this->error('标题最多输入50个字');
            }else{
                $data['title'] = $_POST['title'];
            }

            if(!checkLength($_POST['content'],10,3500)){
                $this->error('内容要在10-3500字之间');
            }

            if(!empty($_POST['content'])){
                $data['description'] = $this->mbstr(htmlspecialchars(strip_tags($_POST['content'])),0,200);
            }
            $data['content'] = nl2br($_POST['content']);
            $data['keywords'] = $this->mbstr(htmlspecialchars(strip_tags($_POST['keywords'])),0,30);

            //取省份
            foreach ($provinceList as $k => $v) {
                if($v['qz_provinceid'] == $_POST['area_id']){
                    $data['area'] = $v['qz_province'];
                    break;
                }
            }
            //取城市
            $cityList = $Db->getCityList();
            foreach ($cityList[$_POST['area_id']] as $k => $v) {
                if($v['cityid'] == $_POST['city_id']){
                    $data['city'] = $v['city'];
                    break;
                }
            }

            $newid = $Db->addQuestion($data);
            if (!empty($newid)){
                $images = $_POST['imgId'];
                if(!empty($images)){
                    foreach ($images as $k => $v) {
                        $Db->addUploadImage($newid,$newid,'1',$v);
                    }
                }
                //添加标签关联
                $service = new ElasticsearchServiceModel();
                $result = $service->getContentLabel($data['title'],1);

                if (count($result) > 0) {
                    foreach ($result as $item) {
                        $allLabel[] = [
                            "thematic_id" => $item["id"],
                            "content_id" => $newid,
                            "module" => 3
                        ];
                    }

                    $thematicLogic = new ThematicWordsLogicModel();
                    $thematicLogic->addThematicRelated($allLabel);
                }

                //更新标签统计数量
                D('LogAdminEditor')->addLogAdminEditor('60','1',$newid);
                $this->success('增加成功！');
            }else{
                $this->error('增加失败！');
            }
        }else{
            $this->assign("starttime",date('Y-m-d H:i'));
            $this->assign('rootCategory',$result['rootCategory']);
            $this->assign('provinceList',$provinceList);
            $this->assign('cityList',$result['cityList']);
            $this->assign('ask',$ask);
            $this->assign("nav",7);
            $this->display('add');
        }
    }

    //问题修改
    public function edit(){
        $id = $_GET['id'];
        $Ask = D("Adminask");
        $info = $Ask->getAskByid($id);
        $info['tagname'] = array_filter(explode(',', $info['tags_name']));

        //取一级和二级分类
        $categorys = $this->getCategory();
        //获取本分类
        $category = $categorys[$info['cid']];
        //获取所有根分类
        foreach ($categorys as $key => $value) {
             unset($value['sub_cate']);
            $rootCategory[$key] = $value;
        }
        unset($categorys);
        $info['category_name'] = $category['name'];
        foreach ($category['sub_cate'] as $key => $value) {
            if($value['cid'] == $info['sub_category']){
                $info['sub_category_name'] = $value['name'];
                break;
            }
        }

        $userCityId = $info['city_id'];
        //获取省份列表
        $provinceList = $Ask->getAreaList();
        //根据城市ID取省份ID
        $myProvinceId = $Ask->getProvinceIdByCityId($userCityId);
        //获取所有城市列表
        $allCityList = $Ask->getCityList();
        //取用户城市
        $cityList = $allCityList[$info['area_id']];
        unset($allCityList);
        //根据用户城市输出区域信息
        foreach ($cityList as $k => $v) {
            if($v['cityid'] == $userCityId){
                 $cityList[$k]['selected'] = 'selected';
            }
        }

        $info['content'] = htmlspecialchars(strip_tags($info['content']));
        $info['btn'] = '修改';
        $info['act_title'] = '修改问题';

        if($_POST){
            $data['area_id'] = $_POST['area_id'];
            $data['city_id'] = $_POST['city_id'];
            $data['cid'] = $_POST['cid'];
            $data['sub_category'] = $_POST['sub_category'];
            $data['modify_time'] = time();

            //获取原来的标签
            $askinfo = D("Adminask")->getAskByid($id);

            //Tag 处理
            $tags = D('Admintags')->getTagIdsByTagNames($_POST['tagnames'],2);
            $tagid = '';
            $tagname = '';
            foreach ($tags as $key => $value) {
                $tagid = $tagid . $key . ',';
                $tagname = $tagname . $value . ',';
            }
            $data['tags'] = trim($tagid, ',');
            $data['tags_name'] = trim($tagname, ',');

            if(mb_strlen($_POST['title'],'utf-8') > 50){
                $this->error('标题最多输入50个字');
            }else{
                $data['title'] = $_POST['title'];
            }

            if(mb_strlen($_POST['content'],'utf-8') > 1501){
                $this->error('内容最多输入1500个字');
            }

            if(!empty($_POST['content'])){
                $data['description'] = $this->mbstr(htmlspecialchars(strip_tags($_POST['content'])),0,200);
            }

            $data['content'] = nl2br($_POST['content']);
            $data['keywords'] = $this->mbstr(htmlspecialchars(strip_tags($_POST['keywords'])),0,30);

            //取省份
            foreach ($provinceList as $k => $v){
                if($v['qz_provinceid'] == $_POST['area_id']){
                    $data['area'] = $v['qz_province'];
                    break;
                }
            }

            //取城市
            $cityList = $Ask->getCityList();
            foreach ($cityList[$_POST['area_id']] as $k => $v) {
                if($v['cityid'] == $_POST['city_id']){
                    $data['city'] = $v['city'];
                    break;
                }
            }

            //处理图片，删除原有的图片
            M("ask_file")->where(array('qid' => $id))->delete();
            $images = $_POST['imgId'];
            if(!empty($images)){
                foreach ($images as $k => $v) {
                    $Ask->addUploadImage($id,$id,'1',$v);
                }
                $is_edit = true;
            }
            if ($Ask->editQuestion($id,$data) || $is_edit){
                //判断问答状态是否在使用中，在使用中则更新标签统计数量
                if($askinfo['visible'] == 0){
                    D('Admintags')->editTagCountByTagIds($askinfo['tags'],$data['tags'],'ask_count');
                }
                $this->success('修改成功！');
            }else{
                $this->error('修改失败！');
            }
            exit();
        }else{
            $img = $Ask->getImages($id,'1');
            if(!empty($img)){
                $info['img'] = '1';
            }
            $this->assign("ask",$info);
            $this->assign("img",$img);
            $this->assign("host",'http://'.C('QZ_YUMINGWWW'));
            $this->assign("nav",7);
            $this->assign('rootCategory',$rootCategory);
            $this->assign('subCategory',$category['sub_cate']);
            $this->assign('provinceList',$provinceList);
            $this->assign('cityList',$cityList);
            $this->display('add');
        }
    }

    //快速发贴
    public function quickPost(){
        $Db = D("Adminask");
        $result = $this->getCategoryCity();
        $ask['area_id']  = $result['provinceId'];
        $ask['city_id']  = $result['cityId'];
        $provinceList = $result['provinceList'];
        $ask['is_show'] = 'style="display:none"';
        $ask['btn']  = '增加';
        $ask['post_time']  = date('Y-m-d H:i');

        //提交
        if($_POST){
            $data['area_id'] = $_POST['area_id'];
            $data['city_id'] = $_POST['city_id'];
            $data['cid'] = $_POST['cid'];
            $data['sub_category'] = $_POST['sub_category'];
            //$data['post_time'] = time();
            $data['review'] = '1';

            $user = $this->autoUser($_POST['username']);
            $data['uid'] = $user['uid'];
            $data['username'] = $user['username'];
            $data['views'] = mt_rand(100,1000);

            if(empty($data['cid']) || empty($data['sub_category'])){
                $this->error('分类信息为空');
            }
            if(empty($data['area_id']) || empty($data['city_id'])){
                $this->error('城市信息为空');
            }

            $data['post_time'] = strtotime($_POST['post_time']);

            if($data['post_time'] > time()){
                $data['visible'] = '3'; //设置状态为定时发布
            }

            if(mb_strlen($_POST['title'],'utf-8') > 50){
                $this->error('问题标题最多输入50个字');
            }else{
                $data['title'] = $_POST['title'];
            }

            //Tag 处理,操作成功后更新标签统计数量
            $tags = D('Admintags')->getTagIdsByTagNames($_POST['tagnames'],2);
            $tagid = '';
            $tagname = '';
            foreach ($tags as $key => $value) {
                $tagid = $tagid . $key . ',';
                $tagname = $tagname . $value . ',';
            }
            $data['tags'] = trim($tagid, ',');
            $data['tags_name'] = trim($tagname, ',');

            if(!checkLength($_POST['content'],10,1500)){
                $this->error('问题内容要在10-1500字之间');
            }

            $data['description'] = $this->mbstr(htmlspecialchars(strip_tags($_POST['content'])),0,200);
            $data['content'] = nl2br($_POST['content']);
            $data['keywords'] = $this->mbstr(htmlspecialchars(strip_tags($_POST['keywords'])),0,50);

            //取省份
            foreach ($provinceList as $k => $v) {
                if($v['qz_provinceid'] == $_POST['area_id']){
                    $data['area'] = $v['qz_province'];
                    break;
                }
            }

            //取城市
            $cityList = $Db->getCityList();
            foreach ($cityList[$_POST['area_id']] as $k => $v) {
                if($v['cityid'] == $_POST['city_id']){
                    $data['city'] = $v['city'];
                    break;
                }
            }

            //返回最新插入的ID
            $newid = $Db->addQuestion($data);
            //dump($newid);
            if (!empty($newid)){
                $images = $_POST['imgId'];
                if(!empty($images)){
                    foreach ($images as $k => $v) {
                        $Db->addUploadImage($newid,$newid,'1',$v);
                    }
                }
                $cid = $data['sub_category'];
                M("ask_category")->where(array('cid'=>$cid))->setInc('count');

                //更新标签统计数量
                D('Admintags')->editTagCountByTagIds('',$data['tags'],'ask_count');

                //开始增加答案 ----------------------------------------------------------------
                $a_username = $_POST['a_username'];
                $a_post_time = $_POST['a_post_time'];
                $a_content = $_POST['a_content'];
                $answerNumber = count($a_username);
                for ($i=0; $i <= $answerNumber; $i++) {
                    $message .= $this->quickAnswer($newid,$a_username[$i],$a_post_time[$i],$a_content[$i],$i);
                }
                $this->success('问题增加成功！'.$message);
            }else{
                $this->error('增加失败！');
            }
            exit();
        }else{
            $this->assign('rootCategory',$result['rootCategory']);
            $this->assign('provinceList',$result['provinceList']);
            $this->assign('cityList',$result['cityList']);
            $this->assign('ask',$ask);
            $this->assign("nav",7);
            $this->display('quickpost');
        }
    }

    //删除问题
    public function remove(){
        $id = $_GET['id'];
        if(empty($id) || !is_numeric($id)){
            $this->error('数据错误！');
        }
        $Ask = D("Adminask");
        $info = $Ask->getAskByid($id);

        //删除时更改标签数量，备注：只有在未通过审核才能删除
        if($info['visible'] == 0){
            D('Admintags')->editTagCountByTagIds($info['tags'],'','ask_count');
        }

        $log = array(
            'logtype' => 'del_ask',
            'info' => $info,
            'action_id' => $id,
        );
        D('Adminask')->addLog($log);

        if ($Ask->removeQuestion($id)){
            $this->success('删除成功！');
        }else{
            $this->error('删除失败！');
        }
    }

    //问题审核
    public function visible(){
        $id = $_GET['id'];
        $type = $_GET['type'];

        if(empty($id) || !is_numeric($id)){
            $this->error('数据错误！');
        }
        $Ask = D("Adminask");
        $info = $Ask->getAskByid($id);

        //审核通过
        $thematicLogic = new ThematicWordsLogicModel();
        //删除标签
        $thematicLogic->delThematicRelated($id,3);

        if($type == '2'){
            //恢复时更改标签数量,判断原来的状态是否是可见,0通过 1不通过
            if($info['visible'] != 0){
                D('Admintags')->editTagCountByTagIds('',$info['tags'],'ask_count');
            }
            $visible = '0';
            $action = '审核通过';

            //添加标签关联
            $service = new ElasticsearchServiceModel();
            $result = $service->getContentLabel($info['title'],1);

            if (count($result) > 0) {
                foreach ($result as $item) {
                    $allLabel[] = [
                        "thematic_id" => $item["id"],
                        "content_id" => $id,
                        "module" => 3
                    ];
                }
                $thematicLogic->addThematicRelated($allLabel);
            }
        }else{
            //删除时更改标签数量,判断原来的状态是否是可见,0通过 1不通过,
            if($info['visible'] == 0){
                D('Admintags')->editTagCountByTagIds($info['tags'],'','ask_count');
            }
            $visible = '1';
            $action = '取消审核';
        }

        if ($Ask->visibleQuestion($id,$visible)){
            D('LogAdminEditor')->addLogAdminEditor('60','3',$id);
            $this->success($action.'成功！');
        }else{
            $this->error($action.'失败！');
        }
    }

    //问题未通过审核
    public function notPassAsk(){
        $id = $_POST['id'];
        $reason = $_POST['reason'];

        if(empty($id) || !is_numeric($id)){
            $this->error('数据错误！');
        }
        $Ask = D("Adminask");
        $info = $Ask->getAskByid($id);

        if ($Ask->visibleQuestion($id,'1',$reason)){
            $thematicLogic = new ThematicWordsLogicModel();
            //删除标签
            $thematicLogic->delThematicRelated($id,3);
            //删除时更改标签数量,0通过 1不通过
            if($info['visible'] == 0){
                D('Admintags')->editTagCountByTagIds($info['tags'],'','ask_count');
            }
            //D('LogAdminEditor')->addLogAdminEditor('60','3',$id);
            $this->success('取消审核操作成功！');
        }else{
            $this->error('取消审核操作失败！');
        }
    }

    //问题设精
    public function distillate(){
        $id = $_GET['id'];
        if(empty($id) || !is_numeric($id)){
            $this->error('数据错误！');
        }
        $Ask = D("Adminask");

        $ask = $Ask->getAskByid($id);

        if($ask['is_distillate'] == '1'){
            $type = 'null';
            $action = '取消精华';
        }else{
            $type = '1';
            $action = '设为精华';
        }
        if ($Ask->distillate($id,$type)){
            $this->success($action.'成功！');
        }else{
            $this->error($action.'失败！');
        }
    }

    //分类列表
    public function category(){
        $category = $this->getCategory();
        //dump($category);
        $this->assign("category",$category);
        $this->assign("nav",7);
        $this->assign("host",'http://'.C('QZ_YUMINGWWW'));
        $this->display();
    }

    //增加分类
    public function categoryadd(){
        $DB = D('Adminask');
        //取分类
        $category = $DB->getCategoryById($id);

        if($_POST){
            $id = $_POST['id'];
            $data['pid'] = $_POST['cid'];
            $data['order_id'] = $_POST['order_id'];
            if(mb_strlen($_POST['name'],'utf-8') > 15){
                $this->error('分类名最多输入15个字');
            }else{
                $data['name'] = $_POST['name'];
            }
            // if(mb_strlen($_POST['content'],'utf-8') > 150){
            //     $this->error('关键字最多输入150个字');
            // }
            if(!empty($_POST['title'])){
                if (mb_strlen($_POST['title'], 'utf-8') > 80) {
                    $this->error('标题长度不能超过80个字符！');
                }
                $data['title'] = $this->mbstr(htmlspecialchars(strip_tags($_POST['title'])),0,80);
            } else {
                $this->error('标题不能为空！');
            }
            if(!empty($_POST['keywords'])){
                $data['keywords'] = $this->mbstr(htmlspecialchars(strip_tags($_POST['keywords'])),0,50);
            }
            if(!empty($_POST['description'])){
                $data['description'] = $this->mbstr(htmlspecialchars(strip_tags($_POST['description'])),0,150);
            }
            if ($DB->addCategory($data)){

                $this->success('增加分类成功！');
            }else{
                $this->error('增加分类失败！');
            }
        }else{
            $categorys = $this->getCategory(1);
            $category['btn'] = '增加分类';
            $this->assign("categoryList",$categorys);
            $this->assign("category",$category);
            $this->assign("nav",7);
            $this->assign("host",'http://'.C('QZ_YUMINGWWW'));
            $this->display('cateedit');
        }
    }

    //修改分类
    public function catedit(){
        if(empty($_GET["id"]) || !is_numeric($_GET["id"])){
            $this->error('不是正确的分类');
        }
        $id = $_GET['id'];

        $DB = D('Adminask');
        //取分类
        $category = $DB->getCategoryById($id);

        if($_POST){
            $id = $_POST['id'];
            $data['pid'] = $_POST['cid'];
            $data['order_id'] = $_POST['order_id'];
            if(mb_strlen($_POST['name'],'utf-8') > 15){
                $this->error('分类名最多输入15个字');
            }else{
                $data['name'] = $_POST['name'];
            }
            // if(mb_strlen($_POST['content'],'utf-8') > 150){
            //     $this->error('关键字最多输入150个字');
            // }
            if(!empty($_POST['title'])){
                if (mb_strlen($_POST['title'], 'utf-8') > 80) {
                    $this->error('标题长度不能超过80个字符！');
                }
                $data['title'] = $this->mbstr(htmlspecialchars(strip_tags($_POST['title'])),0,80);
            } else {
                $this->error('标题不能为空！');
            }
            if(!empty($_POST['keywords'])){
                $data['keywords'] = $this->mbstr(htmlspecialchars(strip_tags($_POST['keywords'])),0,50);
            }
            if(!empty($_POST['description'])){
                $data['description'] = $this->mbstr(htmlspecialchars(strip_tags($_POST['description'])),0,150);
            }
            if ($DB->editCategory($id,$data)){
                $this->success('修改分类成功！');
            }else{
                $this->error('修改分类失败！');
            }
        }else{
            if($category['pid'] !== '0'){
                $categorys = $this->getCategory(1);
            }else{
                $category['is_root'] = '1';
            }
            $category['btn'] = '修改分类';
            $this->assign("category",$category);
            $this->assign("categoryList",$categorys);
            $this->assign("nav",7);
            $this->assign("host",'http://'.C('QZ_YUMINGWWW'));
            $this->display('cateedit');
        }
    }

    //答案管理
    public function anwsers (){
        //分页
        $pageIndex = 1;
        $pageCount = 15;
        if(!empty($_GET["p"])){
            $pageIndex = $_GET["p"];
        }
        //问题ID
        if(!empty($_GET["qid"])){
            $condition['qid'] = $_GET["qid"];
        }
          //用户
        if(!empty($_GET["u"])){
            $condition['uid'] = $_GET["u"];
        }
         //状态
        if(isset($_GET["remove"])){
            $condition['remove'] = $_GET["remove"];
        }
        //排序
        $condition['orderBy'] = 'a.id DESC';

        //搜索
        if(!empty($_GET["keyword"])){
            $condition['keyword'] = $_GET["keyword"];
        }

        $result = $this->getAList($condition,$pageIndex,$pageCount);

        $qList = $result['qList'];

        foreach ($qList as $k => $v) {
            $qList[$k]['visible_origin'] = $qList[$k]['visible'];
            $qList[$k]['visible'] = $v['visible'] == '1' ? '<span class="label label-danger">已删除</span>' : '';
            $qList[$k]['content'] = $this->mbstr(htmlspecialchars(strip_tags($v['content'])),0,140);
            $qList[$k]['status_text'] = $v['status'] == '0' ? '' : '<span class="label label-success" >已解决</span>';
            $qList[$k]['anwsers'] = $v['anwsers'] == '0' ? '' : $v['anwsers'];
        }

        $this->assign("sortby",$sortby);
        $this->assign("list",$qList);
        $this->assign('page',$result['page']);
        $this->assign("nav",7);
        $this->assign("host",'http://'.C('QZ_YUMINGWWW'));
        $this->display();
    }

    //答案增加
    public function anwserAdd(){
        //提交了订单
        if($_POST){
            $Db = D("Adminask");
            $tempId = explode('|',$_POST['title']);
            $qid = $tempId['0'];
            if(empty($qid) || !is_numeric($qid)){
                exit('问题不存在或已过期');
            }
            $data['qid'] = $qid;

            $username = $_POST['username'];
            if(empty($username)){
                exit('无效的用户');
            }
//            $user = $this->autoUser($_POST['username']);
            $username = explode('|',$username);
            $data['uid'] = $username[0];
            $data['username'] = $username[1];

            if(empty($data['uid']) || empty($data['username'])){
                exit('无效的用户');
            }

            //不能回答自已的问题
            $askinfo = $Db->getAskByid($qid);
            if($askinfo['uid'] == $data['uid']){
                $this->error('不能回答自已题出的问题');
            }

            //检测是否已回答
            $isAnswer = $Db->getAnwserByuid($qid,$data['uid']);
            if(!empty($isAnswer)){
                $this->error('你已回答过此问题~!');
            }

            //如果该问题不是预发布，则该答案直接发布
            if (3 != $askinfo['visible']) {
                $data['create_time'] = time();
                $data['post_time'] = time();
                $data['visible'] = '0';
            } else {
                $data['create_time'] = time();
                $data['post_time'] = 0;
                $data['visible'] = '3';
            }

            if(mb_strlen($_POST['content'],'utf-8') > 1501){
                $this->error('内容最多输入1500个字');
            }
            if(!empty($_POST['content'])){
                $data['content'] = htmlspecialchars(strip_tags($_POST['content']));
                $data['content'] = nl2br($data['content']);
            }else{
                $this->error('必须输入回答内容');
            }

            $newid = $Db->addAnwser($data);
            if (isset($newid)){
                $images = $_POST['imgId'];
                if(!empty($images)){
                    foreach ($images as $k => $v) {
                        $Db->addUploadImage($qid,$newid,'2',$v);
                    }
                }
                D('LogAdminEditor')->addLogAdminEditor('61','1',$newid);
                $this->success('增加成功！');
            }else{
                $this->error('增加失败！');
            }
        }else{

            $this->assign('ask',$info);
            $this->assign("host",'http://'.C('QZ_YUMINGWWW'));
            $this->assign("nav",7);
            $this->display('anwseradd');
        }
    }

    //答案修改
    public function anwserEdit(){
        $id = $_GET['id'];
        $Ask = D("Adminask");
        $info = $Ask->getAnwserByid($id);

        $qid = $info['qid'];
        $info['content'] = htmlspecialchars(strip_tags($info['content']));
        $info['btn'] = '修改';

        $ask = $Ask->getAskByid($info['qid']);
        $info['title'] = $ask['title'];
        $img = $Ask->getImages($id,'2');
        if(!empty($img)){
            $info['img'] = '1';
        }

        if($_POST){

            $aid = $_POST['aid'];

            if(empty($aid) || !is_numeric($aid)){
                exit('答案不存在或已过期');
            }
            $data['id'] = $aid;
            if(mb_strlen($_POST['content'],'utf-8') > 2501){
                $this->error('内容最多输入1500个字');
            }

            if(!empty($_POST['content'])){
                $data['content'] = htmlspecialchars(strip_tags($_POST['content']));
                $data['content'] = nl2br($data['content']);
            }else{
                $this->error('必须输入回答内容');
            }
            $Ask->editAnwser($aid,$data);
            $images = $_POST['imgId'];
            M("ask_file")->where(array('fid' => $aid))->delete();
            foreach ($images as $k => $v) {
                $Ask->addUploadImage($qid,$aid,'2',$v);
            }

            $this->success('修改成功！');
        }else{
            $this->assign("info",$info);
            $this->assign("img",$img);
            $this->assign("nav",7);
            $this->display('anwseredit');
        }
    }

    //删除答案
    public function anwserRemove(){
        $id = $_GET['id'];
        if(empty($id) || !is_numeric($id)){
            $this->error('数据错误！');
        }
        $Ask = D("Adminask");
        $info = $Ask->getAnwserByid($id);

        if($info['visible'] == '1'){
            $type = '0';
            $action = '恢复';
        }else{
            $type = '1';
            $action = '删除';
        }

        if ($Ask->removeAnwser($id,$type)){
            $qid = $info['qid'];
            $uid = $info['uid'];

            //重新统计问题有效答案数 qid visible
            //查询问题的采纳答案 best_aid 如果等于当前ID 则update为null
            $best_aid = M('ask')->field('id,best_aid')->where(array('id'=>$qid))->select();
            if($best_aid[0]['best_aid'] == $id){
                $data['best_aid'] = '';
            }
            $qidcount = M("ask_anwser")->where("qid = $qid and visible = 0")->count();
            $data['anwsers'] = $qidcount;
            $res_ask = M('ask')->where(array('id'=>$qid))->save($data);//更新问题表答案数

            //重新统计用户有效回答数 uid visible
            $uidcount = M("ask_anwser")->where("uid = $uid and visible = 0")->count();
            $res_user = M('user')->where(array('id'=>$uid))->save(array('ask_anwsers'=>$uidcount));//更新用户表回答数

            $this->success($action.'成功！');

        }else{
            $this->error($action.'失败！');
        }
    }

    //采纳答案
    public function adopt(){
        $qid = $_GET['qid'];
        $aid = $_GET['id'];
        if(empty($qid) || !is_numeric($qid) || empty($aid) || !is_numeric($aid) ){
            $this->error('数据错误！');
        }
        $Ask = D("Adminask");
        $ask = $Ask->getAskByid($qid);

        if(!empty($ask['best_aid'])){
            $type = 'null';
            $action = '取消采纳';
        }else{
            $type = $aid;
            $action = '采纳';
        }

        //取回答的ID
        $answer = $Ask->getAnwserByid($aid);

        if ($Ask->adopt($qid,$type)){
            $uid = $answer['uid'];
            //取消采纳时
            if($type == 'null'){
                 M("user")->where(array('id'=>$uid))->setDec('ask_adopts');
             }else{
                 M("user")->where(array('id'=>$uid))->setInc('ask_adopts');
             }

            $this->success($action.'成功！');
        }else{
            $this->error($action.'失败！');
        }
    }

    //评论管理
    public function comment (){
        //分页
        $pageIndex = 1;
        $pageCount = 15;
        if(!empty($_GET["p"])){
            $pageIndex = $_GET["p"];
        }
          //用户
        if(!empty($_GET["u"])){
            $condition['uid'] = $_GET["u"];
        }
         //状态
        if(isset($_GET["remove"])){
            $condition['remove'] = $_GET["remove"];
        }
        //搜索
        if(!empty($_GET["keyword"])){
            $condition['keyword'] = $_GET["keyword"];
        }

        $condition['orderBy'] = 'a.post_time DESC';
        $result = $this->getCList($condition,$pageIndex,$pageCount);

        $qList = $result['qList'];

        foreach ($qList as $k => $v) {
            $qList[$k]['visible'] = $v['visible'] == '1' ? '<span class="label label-danger">已删除</span>' : '';
            $qList[$k]['content'] = $this->mbstr(htmlspecialchars(strip_tags($v['content'])),0,140);
            $qList[$k]['post_time'] = date('Y-m-d H:i:s',$v['post_time']);
        }
        //dump($qList);

        $this->assign("list",$qList);
        $this->assign('page',$result['page']);
        $this->assign("nav",7);
        $this->assign("host",'http://'.C('QZ_YUMINGWWW'));
        $this->display();
    }

    //评论修改
    public function commentEdit(){
        $id = $_GET['id'];
        $Ask = D("Adminask");
        $info = $Ask->getCommentByid($id);
        //dump($info);
        $info['content'] = htmlspecialchars(strip_tags($info['content']));
        $info['btn'] = '修改';
        $info['post_time'] = date('Y-m-d H:i:s',$info['post_time']);

        if($_POST){
            $id = $_POST['cid'];
            if(empty($id) || !is_numeric($id)){
                exit('答案不存在或已过期');
            }
            $data['id'] = $id;
            $data['post_time'] = strtotime($_POST['post_time']);
        /*       if(mb_strlen($_POST['content'],'utf-8') > 1501){
                $this->error('内容最多输入1500个字');
            }*/
            if(!empty($_POST['content'])){
                $data['content'] = htmlspecialchars(strip_tags($_POST['content']));
                $data['content'] = nl2br($data['content']);
            }else{
                $this->error('必须输入回答内容');
            }

            if ($Ask->editComment($id,$data)){
                $this->success('修改成功！');
            }else{
                $this->error('修改失败！');
            }
        }else{
            $this->assign("info",$info);
            $this->assign("host",'http://'.C('QZ_YUMINGWWW'));
            $this->assign("nav",7);
            $this->display('commentedit');
        }
    }

    //评论增加
    public function commentAdd(){
        $Db = D("Adminask");

        $aid = $_GET['id'];
        if(!empty($aid) || is_numeric($aid)){
            $info = $Db->getAnwserByid($aid);
            $info['content'] = $this->mbstr(htmlspecialchars(strip_tags($info['content'])),0,30);
            $info['title'] = $info['id'].'|'.$info['content'];
        }

        $info['btn']  = '增加';
        $info['post_time']  = date('Y-m-d H:i:s');
        //提交了订单
        if($_POST){

            $tempId = explode('|',$_POST['title']);
            $qid = $tempId['0'];
            if(empty($qid) || !is_numeric($qid)){
                exit('问题不存在或已过期');
            }
            $data['aid'] = $qid;

            $user = $this->autoUser($_POST['username']);
            $data['uid'] = $user['uid'];
            $data['username'] = $user['username'];


            $data['post_time'] = strtotime($_POST['post_time']);
        /*    if(mb_strlen($_POST['content'],'utf-8') > 1501){
                $this->error('内容最多输入1500个字');
            }*/
            if(!empty($_POST['content'])){
                $data['content'] = htmlspecialchars(strip_tags($_POST['content']));
                $data['content'] = nl2br($data['content']);
            }else{
                $this->error('必须输入回答内容');
            }

            $info = $Db->getAnwserByid($qid);
            $data['qid'] = $info['qid'];

            if ($Db->addComment($data)){
                M("ask_anwser")->where(array('id'=>$qid))->setInc('comments');
                $this->success('增加成功！');
            }else{
                $this->error('增加失败！');
            }

            exit();
        }else{

            //$userList = $Db->getUserList();
            //$this->assign('userList',$userList);
            $this->assign('info',$info);
            $this->assign("host",'http://'.C('QZ_YUMINGWWW'));
            $this->assign("nav",7);
            $this->display('commentadd');
        }
    }

    //评论删除
    public function commentRemove(){
        $id = $_GET['id'];
        if(empty($id) || !is_numeric($id)){
            $this->error('数据错误！');
        }
        $Ask = D("Adminask");
        $info = $Ask->getCommentByid($id);

        if($info['visible'] == '1'){
            $type = '0';
            $action = '恢复';
        }else{
            $type = '1';
            $action = '删除';
        }
        if ($Ask->removeComment($id,$type)){
            if($type == '1'){
                $id = $info['id'];
                M("ask_anwser")->where(array('id'=>$id))->setDec('comments');
            }
            $this->success($action.'成功！');
        }else{
            $this->error($action.'失败！');
        }
    }

    //取提问内容，返回Ajax
    public function getQuestionInfo(){
        $id = intval($_GET['id']);

        if(!empty($id)){
            $info = D("Adminask")->getAskByid($id);
            if(!empty($info)){
                $this->ajaxReturn(array('data'=>'','info'=>$info['content'],'title'=>$info['title'],'status'=>1));
            }else{
                $this->ajaxReturn(array('data'=>'','info'=>'没有这个提问！','status'=>0));
            }
        }else{
            $this->ajaxReturn(array('data'=>'','info'=>'操作失败！','status'=>0));
        }
    }

    //返回问答后台推荐用户 会员装修公司
    public function getVipUserList(){
        $keyword = $_GET['text'];
        if(!empty($keyword)){
            $map = array(
                'user'         =>  array('like','%'.$keyword.'%'),
                "on"         =>  array('EQ','2'),
                //"classid"         =>  array('EQ','3'),
            );
            $users = M('user')->field('id,user')
                    ->limit('0,15')
                    ->where($map)
                    ->select();
            if(!empty($users)){
                $status = '1';
                $msg    = '查询成功!';
                $data   = '';
                $lists = array();
                foreach ($users as $key => $v) {
                    $value['uid']  = $v['id'];
                    $value['name'] = $v['user'];
                    $lists[]=$value;
                }
                $data = $lists;
            }
        }
        if(empty($data)){
            $status = '0'; //状态
            $msg    = '没有找到,请重新查询!';  //消息
            $data   = '';  //数据
        }
        $this->ajaxReturn(array('data'=>$data,'info'=>$msg,'status'=>$status));
        die();
    }

    //检测用户是否为问答管理后台用户
    public function getSeoUser(){
        $keyword = $_GET['text'];
        if(!empty($keyword)){
            $map = array(
                "username"         =>  array('like','%'.$keyword.'%')
            );
            $users = M('ask_seouser')->field('uid,username')
                    ->limit('0,15')
                    ->where($map)
                    ->select();
            if(!empty($users)){
                $status = '1';
                $msg    = '查询成功!';
                $data   = '';
                $lists = array();
                foreach ($users as $key => $value) {
                    $value['uid']  = $value['uid'];
                    $value['name'] = $value['username'];
                    $lists[]=$value;
                }
                $data = $lists;
            }
        }
        if(empty($data)){
            $status = '0'; //状态
            $msg    = '没有找到,请重新查询!';  //消息
            $data   = '';  //数据
        }
        $this->ajaxReturn(array('data'=>$data,'info'=>$msg,'status'=>$status));
        die();
    }

    //增加答案时返回Ajax
    public function getTitle(){
        $keyword = $_GET['text'];

        if(!empty($keyword)){
            $map = array(
                "title"         =>  array('like','%'.$keyword.'%')
            );
            $question = M('ask')->field('id,title')->where($map)
                    ->order('post_time')
                    ->limit('0,10')
                    ->where($map)
                    ->select();
            //dump($question);
            if(!empty($question)){
                $status = '1';
                $msg    = '查询成功!';
                $data   = '';
                $lists = array();
                foreach ($question as $key => $value) {
                    $value['qid']          = $value['id'];
                    $value['title'] = $value['title'];
                    $lists[]=$value;
                }
                $data = $lists;
            }
        }
        if(empty($data)){
            $status = '0'; //状态
            $msg    = '没有找到,请重新查询!';  //消息
            $data   = '';  //数据
        }
        $this->ajaxReturn(array('data'=>$data,'info'=>$msg,'status'=>$status));
        die();
    }

    //增加评论时返回Ajax
    public function getAnwserByAjax(){
        $keyword = $_GET['text'];

        if(!isset($keyword[5])){
            $status = '0'; //状态
            $data   = '';  //数据
            $this->ajaxReturn(array('data'=>$data,'info'=>$msg,'status'=>$status));
            die();
        }

        if(!empty($keyword)){
            $map = array(
                "content"         =>  array('like','%'.$keyword.'%')
            );
            $question = M('ask_anwser')->field('id,content')->where($map)
                    ->order('post_time')
                    ->limit('0,10')
                    ->where($map)
                    ->select();
            //dump($question);
            if(!empty($question)){
                $status = '1';
                $msg    = '查询成功!';
                $data   = '';
                $lists = array();
                foreach ($question as $key => $value) {
                    $v['aid']          = $value['id'];
                    $v['title'] = $this->mbstr(htmlspecialchars(strip_tags($value['content'])),0,30);
                    $lists[]= $v;
                }
                $data = $lists;
            }
        }
        if(empty($data)){
            $status = '0'; //状态
            $msg    = '没有找到,请重新查询!';  //消息
            $data   = '';  //数据
        }
        $this->ajaxReturn(array('data'=>$data,'info'=>$msg,'status'=>$status));
        die();
    }

    //取分类并返回Ajax
    public function getCategoryByAjax(){
        $subCategoryId = intval($_GET['id']);
        if(!empty($subCategoryId)){
            $category = $this->getCategory();
            $theCategory = $category[$subCategoryId]['sub_cate'];
            if(!empty($theCategory)){
                $rt = '';
                foreach ($theCategory as $k => $v) {
                    $rt .= '<option value="'.$v["cid"].'">'.$v["name"].'</option>';
                }
                exit($rt);
            }
            exit();
        }
    }

    //禁封用户
    public function blocked(){
        if($_POST){
            $uid = $_POST["uid"];
            $type = $_POST["type"];

            if($type == '1'){
                $this->sendMessage($uid);
                $time = '0';
            }
            if($type == '2'){
                //禁言3小时
                $time = time() + 10800;
            }
            if($type == '3'){
                //禁言7小时
                $time = time() + 25200;
            }
            if($type == '4'){
                //永久
                $time = '1';
            }
            $blocked = D("Adminask")->blockedUser($uid,$time);
            $this->ajaxReturn(array("info"=>'成功', "status"=>'1'));
        }
        $this->ajaxReturn(array("info"=>'失败', "status"=>'0'));
    }

    //根据 province ID 输出城市列表
    public function getCityByAjax(){
        $cityid = intval($_GET['id']);
        if(!empty($cityid)){
            $cityList = D("Adminask")->getCityList();
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
    }


    //快速添加答案 注：这里没有用终止错误处理，只是跳过
    private function quickAnswer($qid,$username,$time,$content,$num){
        //dump('快速添加答案');
        if(empty($qid) || empty($username) || empty($time) || empty($content)){
            return false;
        }
        $Db = D("Adminask");
        $data['qid'] = $qid;
        //dump($username);
        $user = $this->autoUser($username);
        //dump($user);
        $data['uid'] = $user['uid'];
        $data['username'] = $user['username'];

        //不能回答自已的问题
        $askinfo = $Db->getAskByid($qid);
        if($askinfo['uid'] == $data['uid']){
            return '第'.$num.'条答案：不能回答自已题出的问题';
        }

        //检测是否已回答
        $askinfo = $Db->getAnwserByuid($qid,$uid);
        if(!empty($askinfo)){
            return '第'.$num.'条答案：你已回答过此问题~!';
        }

        $data['post_time'] = strtotime($time);

        if(!empty($content)){
            $data['content'] = htmlspecialchars(strip_tags($content));
            $data['content'] = nl2br($data['content']);
        }else{
            return '第'.$num.'条答案：必须输入回答内容';
        }
        $aid = $Db->addAnwser($data);
        if (isset($aid)){
            //增加用户表中的 ask_anwsers 字段
            M("user")->where(array('id'=>$data['uid']))->setInc('ask_anwsers');
            //增加问题表中的 回答数
            M("ask")->where(array('id'=>$qid))->setInc('anwsers');
            //更新问答表中的 last_time 字段
            $temp['last_time'] = $data['post_time'];
            M("ask")->where(array('id'=>$qid))->save($temp);
            $message = '第'.$num.'个答案增加成功！';
        }else{
            $message = '但增加第'.$num.'个答案失败！';
        }
        return $message;
    }

    //根据输入的username值处理用户注册、已存在等问题
    private function autoUser($username){
        $tempUser = explode('|',$username);
        $iUser['uid'] = $tempUser['0'];
        $iUser['username'] = $tempUser['1'];
        unset($tempUser);
        //如果UID或UserName任一不存在（如果数据库中有，肯定会提示）
        if(empty($iUser['uid']) || empty($iUser['username'])){
            $user = '';
            $tempUser = $this->addUser($username);
            $user['uid'] = $tempUser['uid'];
            $user['username'] = $tempUser['username'];
        }else{
            $Db = D("Adminask");
            //这里的逻辑是：只要指定UID和Username就可以输入
            $userinfo = $Db->getUserById($iUser['uid']);
            if(empty($userinfo)){
                $tempUser = $this->addUser($username);
                $user['uid'] = $tempUser['uid'];
                $user['username'] = $tempUser['username'];
            }else{
                //可以指定非SEO用户 要求格式务必为： uid|username
                $user['uid'] = $userinfo['id'];
                $user['username'] = $userinfo['user'];
            }
        }
        return $user;
    }

    //自动注册用户
    private function addUser($username){
        $username = empty($username) ? $this->error('用户名不存在，自动注册失败!') : $username;
        $map['user'] = $username;
        //检测用户是否存在，执行这一步的先提条件是：没有输入UID和Username 或者　输入了用户却不存在
        $userinfo = M('user')->field('id,user')->where($map)->select();
        //如果用户不存在
        if(empty($userinfo)){
            $data['classid'] = '1';
            $data['user'] = $username;
            $data['name'] = $username;
            $data['register_admin_id'] = '1';
            $data["pass"] = md5('111111');
            $data["register_admin_id"] = session("uc_userinfo.id");
            $data["register_time"] = time();
            $n =  M("user")->add($data);
            if($n){
                $user['uid'] = $n;
                $user['username'] = $username;
                //开始插入用户关系表:
                $seouser['uid'] =$n;
                $seouser['username'] = $username;
                M('ask_seouser')->add($seouser);
                return $user;
            }else{
                $this->error('自动注册用户失败！');
                return flase;
            }
        }else{
            $user = D("Adminask")->isSEOUser($userinfo['id']);
            if(!empty($user)){
                $user['uid'] = $userinfo['id'];
                $user['username'] = $userinfo['user'];
                return $user;
            }else{
                $this->error('用户已被注册了，并且非SEO帐号');
            }
        }
        die();
    }

    //获取问题列表并分页
    private function getQList($condition,$pageIndex = 1,$pageCount = 10){
        import('Library.Org.Page.Page');
        $result = D("Adminask")->getQuestionList($condition,($pageIndex-1) * $pageCount,$pageCount);
        $count = $result['count'];
        $qList = $result['result'];
        $config  = array("prev","first","last","next");
        $page = new \Page($pageIndex,$pageCount,$count,$config);
        $pageTmp =  $page->show();
        return array("qList"=>$qList,"page"=>$pageTmp);
    }

    //获取答案列表并分页
    private function getAList($condition,$pageIndex = 1,$pageCount = 10){
        import('Library.Org.Page.Page');
        $result = D("Adminask")->getAnwserList($condition,($pageIndex-1) * $pageCount,$pageCount);
        $count = $result['count'];
        $qList = $result['result'];
        $config  = array("prev","first","last","next");
        //dump($result);
        $page = new \Page($pageIndex,$pageCount,$count,$config);
        $pageTmp =  $page->show();
        return array("qList"=>$qList,"page"=>$pageTmp);
    }

    //获取评论列表并分页
    private function getCList($condition,$pageIndex = 1,$pageCount = 10){
        import('Library.Org.Page.Page');
        $result = D("Adminask")->getCommentList($condition,($pageIndex-1) * $pageCount,$pageCount);
        $count = $result['count'];
        $qList = $result['result'];
        $config  = array("prev","first","last","next");
        //dump($result);
        $page = new \Page($pageIndex,$pageCount,$count,$config);
        $pageTmp =  $page->show();
        return array("qList"=>$qList,"page"=>$pageTmp);
    }

    //取分类和城市最终输出列表
    private function getCategoryCity(){
        //取一级和二级分类
        $categorys = $this->getCategory();
        //获取所有根分类
        foreach ($categorys as $key => $value) {
             unset($value['sub_cate']);
            $rootCategory[$key] = $value;
        }
        unset($categorys);
        $Db = D("Adminask");

        //设置默认省份和城市ID
        $provinceId = '320000';
        $cityId = '320500';
         //获取省份列表
        $provinceList = $Db->getAreaList();
        //获取所有城市列表
        $allCityList = $Db->getCityList();
        //取用户城市
        $cityList = $allCityList[$provinceId];
        unset($allCityList);
        $result['provinceId'] = $provinceId;
        $result['cityId'] = $cityId;
        $result['rootCategory'] = $rootCategory;
        $result['provinceList'] = $provinceList;
        $result['cityList'] = $cityList;
        return $result;
    }

    //获取问题分类 使用缓存
    private function getCategory($update = '0'){
        $tempCategory = D("Adminask")->getCategory();
        $category = array();
        if($tempCategory){
            foreach ($tempCategory as $k => $v ){
                if($v['pid'] == '0') {
                    $category[$v['cid']] = $v;
                    unset($k);
                }
            }
            foreach ($tempCategory as $k => $v ){
                if($v['pid'] != '0') {
                    $category[$v['pid']]['sub_cate'][] = $v;
                }
            }
        }
        ksort($category);
        return $category;
    }

    //中文字符串截取
    private function mbstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {
        if(function_exists("mb_substr"))
            $slice = mb_substr($str, $start, $length, $charset);
        elseif(function_exists('iconv_substr')) {
            $slice = iconv_substr($str,$start,$length,$charset);
            if(false === $slice) {
                $slice = '';
            }
        }else{
            $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
            preg_match_all($re[$charset], $str, $match);
            $slice = join("",array_slice($match[0], $start, $length));
        }
        $fix='';
        if(strlen($slice) < strlen($str)){
            $fix='...';
        }
        return $suffix ? $slice.$fix : $slice;
    }

    //上传图片
    public function upload(){
        $type = $_POST['type'];
        $id = $_POST['id'];
        //上传图片
        if(!empty($_FILES["Filedata"])){
            $file = $_FILES["Filedata"];
            $fileExt = pathinfo($_FILES["Filedata"]['name'], PATHINFO_EXTENSION);
            $result = $this->uploadToQiNiu('ask',$file["tmp_name"],$fileExt);
            if(gettype($result) != "object"){
                //如果ID不为空
                if(!empty($id)){
                    D("Adminask")->addUploadImage($id,$id,$type,$result["key"]['name']);
                }
                echo json_encode(array("status"=>1,"data"=>$result["key"]['name']));
            }else{
                echo json_encode(array("status"=>0,"info"=>"图片上传失败,请联系技术部门！"));
            }
        }
        die;
    }

     //上传到七牛服务器
    private function uploadToQiNiu($prefix,$file,$fileExt){
        if($_POST){
            if (count($_FILES) == 0) {
                header("Content-type:text/html;charset=utf-8");
                header("HTTP/1.1 405 Picture not uploaded");
                die();
            }

            $setting = C('UPLOAD_IMG_QINIU');
            $setting["saveName"] =  array('uniqid','');

            $file = $_FILES[array_keys($_FILES)[0]];

            $setting["savePath"] = "";
            $info = '';

            if(!empty($prefix)){
                $setting["savePath"] = $prefix.'/';
            }

            $setting["subName"] = array('date', 'Ymd');
            $setting["saveExt"] = $fileExt;
            $setting["driverConfig"]["domain"] = OP("QINIU_DOMAIN");
            $setting["driverConfig"]["bucket"] = OP('QINIU_BUCKET');
            $setting["driverConfig"]["secretKey"] = OP("QINIU_CK");
            $setting["driverConfig"]["accessKey"] =  OP("QINIU_AK");
            $Upload = new \Think\Upload($setting);
            $data = $Upload->upload($_FILES);

            if($data !== false){
                return array(
                    "key" => $data[array_keys($_FILES)[0]],
                    'status'=>1
                );
            }
        }
    }

    //上传到七牛服务器
    private function uploadToQiNius($prefix,$file,$fileExt){
        import('@.ORG.qiniu.io', '', '.php');
        import('@.ORG.qiniu.rs', '', '.php');
        $bucket = OP('QINIU_BUCKET');
        $accessKey = OP('QINIU_AK');
        $secretKey = OP('QINIU_CK');
        Qiniu_SetKeys($accessKey, $secretKey);
        $putPolicy = new Qiniu_RS_PutPolicy($bucket);

        $putPolicy->MimeLimit = 'image/jpeg;image/png;image/gif';
        $putPolicy->SaveKey = $prefix.'/$(year)$(mon)$(day)/$(etag)'.'.'.$fileExt;
        $upToken = $putPolicy->Token(null);
        $putExtra = new Qiniu_PutExtra();
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

    //二维数组排序
    private function multi_array_sort($multi_array,$sort_key,$sort=SORT_ASC){
        if(is_array($multi_array)){
            foreach ($multi_array as $row_array){
                if(is_array($row_array)){
                    $key_array[] = $row_array[$sort_key];
                }else{
                    return false;
                }
            }
        }else{
            return false;
        }
        array_multisort($key_array,$sort,$multi_array);
        return $multi_array;
    }

    public function syncCount()
    {
        $type = ('1' == I('get.type')) ? 1 : 2;
        $page = (intval(I('get.page')) > 0) ? intval(I('get.page')) : 1;
        $each = 10000;

        if ($type == 1) {
            /*同步答案表的数量到问题表*/
            $temp = M('ask')->field('id')->limit(($page - 1) * $each, $each)->order('id ASC')->select();
            if (!empty($temp)) {
                foreach ($temp as $key => $value) {
                    $count = M('ask_anwser')->where(array('qid' => $value['id'], 'content' => array('NEQ', '')))->count();
                    M('ask')->where(array('id' => $value['id']))->save(array('anwsers' => $count));
                }
                $page++;
                $redirct = C('168NEW_URL') . '/adminask/synccount/?type=' . $type . '&page=' . $page;
                $this->assign('redirct',$redirct);
                $this->display();
                exit();
            } else {
                //进行用户答案数量同步
                $page = 1;
                $type = 2;
            }
        }

        /*同步答案表的数量到用户表*/
        if ($type == 2) {
            $temp = M('user')->field('id')->limit(($page - 1) * $each, $each)->order('id ASC')->select();
            if (!empty($temp)) {
                foreach ($temp as $key => $value) {
                    $count = M('ask_anwser')->where(array('uid' => $value['id'], 'content' => array('NEQ', '')))->count();
                    M('user')->where(array('id' => $value['id']))->save(array('ask_anwsers' => $count));
                }
                $page++;
                $redirct = C('168NEW_URL') . '/adminask/synccount/?type=' . $type . '&page=' . $page;
                $this->assign('redirct',$redirct);
                $this->display();
                exit();
            }
        }

        echo 'DONE';
        exit();
    }

}