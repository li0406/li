<?php
/**
 * 用户装修日记的总控制器
 */
namespace User\Controller;
use User\Common\Controller\UserBaseController;
class DiaryController extends UserBaseController
{
    private $ajax_result=array("data"=>'','info'=>"ERROR",'status'=>0);
    function _initialize(){
        parent::_initialize();//执行父类初始化
        $this->assign("nav",7);//侧边栏 选中装修日记
        $this->assign('fengge',$this->fengge);//风格赋值
    }
    /**
     * [index 装修日记列表]
     * @return [type] [展示装修日记列表]
     */
    public function index(){
        //获取基本信息
        $info["user"] = $this->baseInfo;
        $result=D('Diary')->get_my_diary_list($info['user']['id']);//获取列表和分页
        //dump($result);
        extract($result);
        $info["diaries"] = $result["list"];
        $this->assign("page",$pageTmp);//分配分页模版
        $this->assign("list",$list);//分配日记列表
        $huxing=D('Meitu')->getHuxing(20,true);//获取户型
        $this->assign('huxing',$huxing);//分配户型变量
        $fengge=D('Meitu')->getFengge(20,true);//获取风格
        $this->assign('fengge',$fengge);//分配风格变量
        $this->assign('info',$info);
        $this->display();
    }

    /**
     * [diary_edit_list 取得单篇日记的列表]
     * @return [type] [description]
     */
    public function diary_edit_list(){
        if(I('get.id') == ""){
            $this->_error();
            die();
        }
        //获取基本信息
        $info["user"] = $this->baseInfo;
        # 查询单篇日记的列表
        $id=I('get.id');
        //查找这篇日记的内容和该日记的后续补充日记的内容
        $result=D('Diary')->get_one_diary_info($id,true,true);//通过id获取某篇日记的信息
        if(count($result) == 0){
            $this->_error();
            die();
        }
        $diary_type=D('Diary')->get_diary_type();//获取进度阶段类别
        import('Library.Org.Util.Fiftercontact');
        $filter = new \Fiftercontact();
        foreach ($result as $k => $v){
            $v["content"] = strip_tags($v["content"]);
            $v["content"] = $filter->filter_common($v["content"],array("Sbc2Dbc","filter_script","filter_link","filter_url"));
            foreach ($diary_type as $key => $value){
                if ($value['id']==$v['first_type']){
                    $v['first_type_name']=$value['type_name'];
                }
                foreach ($value['child'] as $son) {
                    if ($son['id']==$v['second_type'])
                    {
                        $v['second_type_name']=$son['type_name'];
                    }
                }
            }

            if($v["parent_id"] == 0){
                $list = $v;
            }else{
                $list["child"][] = $v;
            }
        }
        $this->assign('id',$id);//赋值id
        $this->assign('list',$list);//日记列表赋值
        $this->assign('info',$info);
        $this->display();//加载文件模版
    }

    /**
     * [add_follow_diary 给某篇日记添加后续日记]
     */
    public function add_follow_diary(){

        //判断是否被封禁
        isBlocked(false);

        //获取基本信息
        $info["user"] = $this->baseInfo;
        #给某篇日记添加后续日记
        $id = I('get.id')+0;
        $info = D('Diary')->getDiaryTheme($id);//获取日记信息
        if (empty($info)){
            $this->redirect("/diary/");#如果日记不存在 则回到主页面
        }
        $info["user"] = $this->baseInfo;//获取个人基本信息
        //此时是该日记的后续日记
        $diary_type=D('Diary')->get_diary_type();//查询日记类型
        $this->assign("diary_type",$diary_type);//赋值日记类型
        $this->assign('id',$id);//赋值该日记id
        $this->assign('info',$info);
        $this->display();//加载模版
    }

    /**
     * [add_follow_diary_do 添加后续日记入库]
     * @param string $value [json]
     */
    public function add_follow_diary_do($value=''){
        $code = strtolower($_POST["code"]);
        if(!check_verify($code)){
            $this->ajaxReturn(array("data"=>"图形验证码不正确！","info"=>"","status"=>9));
        }

        //接收图片进行入库
        $id=I('post.id')+0;
        $field=array('title','first_type','second_type','is_over','content','diary_time');//规定要接受的字段
        foreach ($field as $k => $v)
        {
            $data[$v]=htmlspecialchars(trim($_POST[$v]));//接收这些字段
        }
        $diary_model = D('Diary');

        //添加发送限制判断,30分钟内只能发送5条日记
        $result = D('Diary')->getLastDiaryCount(session("u_userInfo.id"));
        if (count($result) > 0) {
            $offset = floor((time() - $result["update_time"])%86400/60);
            if (empty($result["update_time"])) {
                $offset = floor((time() - $result["add_time"])%86400/60);
            }

            if ($offset <= 5) {
                $this->ajaxReturn(array("data"=>"您的操作过于频繁，请休息5分钟后再试！","status"=>0));
                exit();
            }
        }

        $diary_model->startTrans();
        //过滤日记内容
        import('Library.Org.Util.Fiftercontact');
        $filter = new \Fiftercontact();
        $data["title"] = $filter->filter_common($data["title"],array("Sbc2Dbc","filter_script","filter_link","filter_url",array("filter_sensitive_words",array(2,3,5))));
        $data["content"] = $filter->filter_common($data["content"],array("Sbc2Dbc","filter_script","filter_link","filter_url",array("filter_sensitive_words",array(2,3,5))));
        $data["content"] = strip_tags($data["content"]);

        $data['diary_time']=strtotime($data['diary_time']);//转换日记时间
        $data['add_time']=time();//记录日记添加时间
        $img_list=array_filter(explode(',',I('post.img')));//转换图片地址为数组并过滤

        $diary_topic_info=$diary_model->getDiaryTheme($id);
        $data['user_id']=$diary_topic_info['user_id'];//用户id
        $data['mianji']=$diary_topic_info['mianji'];//面积
        $data['fengge']=$diary_topic_info['fengge'];//风格
        $data['huxing']=$diary_topic_info['huxing'];//户型
        $data['decorate_type']=$diary_topic_info['decorate_type'];//装修方式
        $data['xiaoqu']=$diary_topic_info['xiaoqu'];//装修方式
        $data['company_name']=$diary_topic_info['company_name'];//装修公司名称
        $data['company_id']=$diary_topic_info['company_id'];//装修公司id
        $data['stage']=$diary_model->get_stage_type($data['first_type']);//进展到第几阶段
        $data['parent_id']=$id;//接收父日记文章的id
        $data["stat"] = 2;

        //更新浏览量
        $data['page_view'] = mt_rand(2000,5000);
        $data['concern_count'] = mt_rand(50,200);
        $data['review'] = '0';

        $result=$diary_model->where(array('id'=>$id))->add($data);
        $res=$diary_model->add_diary_img($result,$img_list);
        //添加文章成功后将首文章的diary_count加1
        $themeData = array(
            "diary_count"=>array('exp','diary_count+1')
        );
        $diary_model->editDiary($id,$themeData);
        if ($res) {
            $diary_model->commit();
            $this->ajax_result['info']="Ok";
            $this->ajax_result['status']=1;
        }else {
            $diary_model->rollback();
            $this->ajax_result['data']="添加日记失败,请刷新重试！";
        }
        $this->ajaxReturn($this->ajax_result);
    }

    /**
     * [edit_diary 编辑日记]
     * @return [type] [编辑日记信息]
     */
    public function edit_diary(){

        //判断是否被封禁
        isBlocked(false);


        //获取基本信息
        $info["user"] = $this->baseInfo;
        #接收id作为日记id然后读取信息编辑信息 如果不存在 跳回主界面
        #给某篇日记添加后续日记
        $id=I('get.id')+0;
        $diary_info=D('Diary')->get_one_diary_info($id,'',true);//获取日记信息
        $diary_info=$diary_info[0];//取第一个元素
        $diary_info['diary_time']=$diary_info['diary_time']>0?date('Y-m-d',$diary_info['diary_time']):date('Y-m-d',time());

        if (empty($diary_info) || $diary_info['stat'] == 1){
            $this->redirect("/diary/");#如果日记不存在 则回到主页面
        }
        $info["user"] = $this->baseInfo;//获取个人基本信息
        //此时是该日记的后续日记
        $diary_type=D('Diary')->get_diary_type();//查询日记类型
        $this->assign("diary_type",$diary_type);//赋值日记类型

        $diary_info['content'] = strip_tags($diary_info['content']);

        $this->assign('id',$id);//赋值该日记id
        $this->assign('diary_info',$diary_info);//赋值该日记id
        //根据日记信息取图集信息
        $diary_img_list=D('Diary')->get_one_diary_img($diary_info['id'],true);//根据日记id获取日记图集
        $this->assign('diary_img_list',$diary_img_list);

        $this->assign('info',$info);
        $this->display();//加载模版
    }

    //编辑日记
    public function edit_diary_do(){
        import('Library.Org.Util.Fiftercontact');
        $filter = new \Fiftercontact();

        //接收图片进行入库
        $id=I('post.id')+0;

        $diary_info=D('Diary')->get_one_diary_info($id,'',true);//获取日记信息
        if (empty($diary_info) || $diary_info['stat'] == 1){
            $this->redirect("/diary/");#如果日记不存在 则回到主页面
        }

        $field=array('title','first_type','is_over','second_type','content','diary_time');//规定要接受的字段
        foreach ($field as $k => $v){
            $data[$v] = remove_xss($filter->filter_common($_POST[$v],array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,5)),"filter_tel","filter_mobile","filter_link","filter_url")));
        }
        $data['diary_time']=strtotime($data['diary_time']);//转换日记时间
        $data['update_time']=time();//记录日记添加时间
        $img_list=array_filter(explode(',',I('post.img')));//转换图片地址为数组并过滤
        $diary_model = D('Diary');
        $res=$diary_model->add_diary_img($id,$img_list);
        $result=$diary_model->where(array('id'=>$id))->save($data);
        if ($res){
            $this->ajax_result['info']="Ok";
            $this->ajax_result['status']=1;
        }else{
            $this->ajax_result['data']="修改日记失败,请刷新重试！";
        }
        $this->ajaxReturn($this->ajax_result);
    }

    //添加日记
    public function add_diary(){

        //判断是否被封禁
        isBlocked(false);

        $id=I('get.id')+0;
        if ($id<1){
            $this->add_diary_first();//对路由接收的id进行处理 如果没有id是第一步
        }else{
            $this->add_diary_second();//对路由接收的id进行处理 如果有id说明是第二步的提交
        }
    }
    /**
     * [write_diary 书写日记]
     * @return [type] [加载模版]
     */
    public function write_diary(){
        $id=I('get.id')+0;
        if ($id>0){
            $this->assign('id',$id);//赋值id
            $this->write_diary_first();//对路由接收的id进行处理 如果没有id是第一步页面的展示
        }else {
            $this->redirect("/add_diary/");//没有id则回到主页面
        }
    }
    /**
     * [write_diary_second 写日记入库第二步]
     * @return [json] [json]
     */
    public function write_diary_second(){
        import('Library.Org.Util.Fiftercontact');
        $filter = new \Fiftercontact();

        $code = strtolower($_POST["code"]);
        if(!check_verify($code)){
            $this->ajaxReturn(array("data"=>"图形验证码不正确！","info"=>"","status"=>9));
        }

        //接收图片进行入库
        $id=I('post.id')+0;
        //规定要接受的字段
        $field=array('title','first_type','is_over','second_type','content','diary_time');

        foreach ($field as $k => $v){
            $data[$v] = remove_xss($filter->filter_common($_POST[$v],array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,5)),"filter_tel","filter_mobile","filter_link","filter_url")));
        }

        $diary_model=D('Diary');

        //限制发布频率
        $result = D('Diary')->getLastDiaryCount(session("u_userInfo.id"));
        if (count($result) > 0) {
            $offset = floor((time() - $result["add_time"])%86400/60);
            if ($offset <= 5) {
                $this->ajaxReturn(array("data"=>"您的操作过于频繁，请休息5分钟后再试！","status"=>0));
                exit();
            }
        }

        $diary_model->startTrans();
        $img_list=array_filter(explode(',',I('post.img')));//转换图片地址为数组并过滤
        $data["stat"] = 2;
        //将日记添加入库
        //过滤日记内容
        import('Library.Org.Util.Fiftercontact');
        $filter = new \Fiftercontact();

        //事先过滤元素
        $data["content"] = strip_tags($data["content"]);
        $data["content"] = $filter->filter_common($data["content"],array("Sbc2Dbc","filter_script","filter_link","filter_url",array("filter_sensitive_words",array(2,3,5))));
        $data['diary_time'] = strtotime($data['diary_time']);//转换日记时间
        $data['add_time'] = time();//记录日记添加时间


        //处理日记的关键字
        $keywords = D("Wwwarticlekeywords")->getAllKeywords(1);
        shuffle($keywords);
        foreach ($keywords as $key => $value){
            $arr[] = "/".trim($value["name"])."/";
        }
        $i = 0;
        foreach ($arr as $key => $value){
            if($i == 3){
                break;
            }
            preg_match_all($value,$data["content"],$matches);
            if(count($matches[0]) > 0){
                $link =  "<a href='".$keywords[$key]["href"]."' target='_blank'  title='".$keywords[$key]["name"]."''>".$keywords[$key]["name"]."</a>";
                $data['content'] = preg_replace($value,$link,$data['content'],1);
                $i ++;
            }
        }

        $diary_topic_info=$diary_model->getDiaryTheme($id);
        if(count($diary_topic_info) > 0){
            //修改当前主题的启用状态
            $themeData = array(
                        "stat"=>2,
                        "diary_count"=>array('exp','diary_count+1')
                               );
            $diary_model->editDiary($id,$themeData);

            $data['user_id']=$diary_topic_info['user_id'];//用户id
            $data['mianji']=$diary_topic_info['mianji'];//面积
            $data['fengge']=$diary_topic_info['fengge'];//风格
            $data['huxing']=$diary_topic_info['huxing'];//户型
            $data['decorate_type']=$diary_topic_info['decorate_type'];//装修方式
            $data['xiaoqu']=$diary_topic_info['xiaoqu'];//装修方式
            $data['company_name']=$diary_topic_info['company_name'];//装修公司名称
            $data['company_id']=$diary_topic_info['company_id'];//装修公司id
            $data['stage']=$diary_model->get_stage_type($data['first_type']);//进展到第几阶段
            $data['parent_id']= $id;//接收父日记文章的id
            $user = $this->baseInfo;//获取用户信息
            $data['page_view'] = mt_rand(2000,5000);
            $data['concern_count'] = mt_rand(50,200);
            $data['review'] = '0';

            $result=$diary_model->where(array('id'=>$id))->add($data);
            $diary_model->add_diary_img($result,$img_list);
        }

        if ($result){
            $diary_model->commit();
            $this->ajax_result['info']="Ok";
            $this->ajax_result['status']=1;
        }else{
            $diary_model->rollback();
            $this->ajax_result['data']="添加日记失败,请刷新重试！";
        }
        $this->ajaxReturn($this->ajax_result);
    }
    /**
     * [add_diary  添加装修日记展示页面]
     * @return [type] [添加装修日记表单]
     */
    public function add_diary_first(){
        //获取基本信息
        $info["user"] = $this->baseInfo;
        //获取当前城市
        $citys = D("Area")->getCityArray($_SESSION["u_userInfo"]["cs"]);
        $citys["shen"] = $citys["shen"][0];
        $citys["shi"] = $citys["shi"][$_SESSION["u_userInfo"]["cs"]];
        $info["citys"] = $citys;
        //获取装修方式
        $fangshi  =  D("Common/Fangshi")->getfs();
        $info["fangshi"] = $fangshi;
        $this->assign("info",$info);
        $huxing=D('Meitu')->getHuxing(20,true);
        $this->assign('huxing',$huxing);//分配户型变量
        $fengge=D('Meitu')->getFengge(20,true);//获取风格
        $this->assign('fengge',$fengge);//分配风格变量
        $this->assign('info',$info);
        $this->display("add_diary_first");
    }


    //入库主题日记
    public function add_diary_second(){
        import('Library.Org.Util.Fiftercontact');
        $filter = new \Fiftercontact();
        #接收参数进行入库
        $field=array("title","huxing","mianji","qx","xiaoqu","fengge","decorate_type","check_company","company_name");
        foreach ($field as $key => $v){
            if ($v != "fengge") {
                $data[$v] = remove_xss($filter->filter_common($_POST[$v],array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,5)),"filter_tel","filter_mobile","filter_link","filter_url")));
            }else{
                $data[$v] = $filter->filter_common($_POST[$v],array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,5)),"filter_tel","filter_mobile","filter_link","filter_url"));
            }
        }

        $info["user"] = $this->baseInfo;//获取用户信息
        $data['user_id']= $info["user"]['id'];//取得用户id

        $data['stat'] = 2;
        $data['add_time'] = time();
        $data['diary_time'] = time();

        $data['page_view'] = mt_rand(2000,5000);
        $data['concern_count'] = mt_rand(50,200);
        $data['review'] = '0';

        $res = D('Diary')->add_new_diary($data);//添加新日记
        if ($res){
            $this->ajax_result['data']=$res;
            $this->ajax_result['info']="Ok";
        }else{
            $this->ajax_result['data']="添加日记失败,请刷新重试！";
        }
        $this->ajaxReturn($this->ajax_result);
    }
    /**
     * [write_diary_first 书写日记第一步]
     * @return [type] [书写日记展示页面]
     */
    public function write_diary_first(){
          //获取基本信息
        $info["user"] = $this->baseInfo;
        //此时是第一篇日记  此时用户没有该类日记的标签 取所有标签
        $diary_type=D('Diary')->get_diary_type();//查询日记类型
        $this->assign("diary_type",$diary_type);//赋值日记类型
        $this->assign('info',$info);
        $this->display("write_diary_first");//加载模版
    }


    /**
     * 删除日记图片的ajax方法
     */
    public function del_diary_img(){
        $this->check_is_ajax_post();//检测是否是ajax提交过来的
        $id=$_POST['id']+0;//接收要删除图片的id
        $info["user"] = $this->baseInfo;//获取用户信息
        $user_id=$info["user"]['id'];//取得用户id
        $check_is_last_img=D('Diary')->check_is_last_img($id);//检测是否是最后一张图或者没图了
        if ($check_is_last_img)
        {
            $ajax_result['data']="删除失败！";
            $ajax_result['info']="最后一张图不能删除";
            $ajax_result['status']=0;
            $this->add_log("最后一张图不能删除",CONTROLLER_NAME."/".ACTION_NAME);//写入操作日志
            $this->ajaxReturn($ajax_result);//返回json结果
        }
        $res=D('Diary')->del_diary_img($id,$user_id);//删除该图片
        if ($res)
        {
            $ajax_result['data']="删除成功！";
            $ajax_result['info']="删除图片成功";
            $ajax_result['status']=1;
            $this->add_log("删除日记图片成功",CONTROLLER_NAME."/".ACTION_NAME);//写入操作日志
        }else
        {
            $ajax_result['data']="删除失败！";
            $ajax_result['info']="删除失败！";
            $ajax_result['status']=0;
            $this->add_log("删除日记图片失败",CONTROLLER_NAME."/".ACTION_NAME);//写入操作日志
        }
        $this->ajaxReturn($ajax_result);//返回json结果
    }

    /**
     * [get_diary_level_type 获取日记等级类别]
     * @return [json] [ json ]
     */
    public function get_diary_level_type(){
        $id=$_POST['id']+0;
        $type_list=$this->get_diary_type($id);
        $this->ajax_result['data']=$type_list;
        $this->ajax_result['info']="Ok";
        return $this->ajaxReturn($this->ajax_result);
    }

    /**
     * 检验是否是ajax的post提交
     * @return [type] [description]
     */
    private function check_is_ajax_post(){
        if (!IS_POST){
            $ajax_result['data']="非法访问！";
            $ajax_result['info']="ERROR";
            $ajax_result['status']=0;
            $this->ajaxReturn($ajax_result);
        }
    }


    /**
     * 添加操作日志
     * @param string $info   [记录信息]
     * @param [type] $action [动作控制器和方法]
     */
    private function add_log($info='',$action){
        //记录日志
        import('Library.Org.Util.App');
        $app = new \App();
        $data = array(
              "username"=>$this->session_username,      //记录操作人
              "userid"=>$this->session_userid,          //记录操作人id
              "ip"=>$app->get_client_ip(),              //记录客户ip
              "user_agent"=>$_SERVER["HTTP_USER_AGENT"],//记录user_agent
              "info"=>$info,                            //记录标记消息
              "time"=>date("Y-m-d H:i:s"),              //记录添加时间
              "action"=>$action                         //记录操作控制器和方法
              );
        D("Loguser")->addLog($data);//添加日志
    }


    //判断是否是SeoUser
    private function isSeoUser($uid){
        $map['id'] = $uid;
        $map['user_type'] = '2';
        return M('user')->field('id,user_type,register_admin_id')->where($map)->find();
    }
}