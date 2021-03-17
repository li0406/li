<?php
/**
 * 设计师博文管理控制器  博文列表页 博文编辑添加页
 */
namespace User\Controller;
use User\Common\Controller\DesignerBaseController;
class DesignerblogController extends DesignerBaseController
{
    public function _initialize()
    {
        parent::_initialize();//先去走父类的构造
        $info["user"] = $this->baseInfo;
        $this->assign('info',$info);
        $this->assign("nav",4);//侧边栏
        $this->session_userid=session("u_userInfo.id");//取得设计师id
        $this->session_username=session("u_userInfo.name");//取得设计师id
    }
    //首页 列表展示我的博文
    public function index()
    {
        $p=intval(I("get.p"));//接收页码
        $pageIndex = $p>=1?$p:1;//如果没有传页码 就是第一页
        $pageCount = 10;//每页分10个
        $map['userid']=$this->session_userid;//查询我的文章
        $count=M("article")->where($map)->count();
        if($count)
        {
            import('Library.Org.Page.Page');//导入分页类
            //自定义配置项
            $config  = array("prev","next");
            $page = new \Page($pageIndex,$pageCount,$count,$config);//实例化分页类
            $pageTmp =  $page->show();//得到分页
            $start=($page->pageIndex-1)*$pageCount;//起始页
            $list=M("article")->where($map)->limit($start.",".$pageCount)->order("time desc")->select();//查询列表
            $this->assign('list',$list);//赋值文章列表
            $this->assign('page',$pageTmp);//赋值文章分页
        }
        $this->assign('bm',session("ubm"));
        $this->display();//展示模版
    }
    //展示添加博文的页面
    public function add_blog()
    {
        $this->display();
    }
    public function edit_blog()
    {
        $id=I('get.id');
        if ($id<1)
        {
            $this->redirect("/desblog");
        }
        $blog=M('article')->find($id);
        import('Library.Org.Util.Fiftercontact');//导入过滤类
        $filter = new \Fiftercontact();//实例化过滤类
        $blog['text']=$filter->filter_empty($blog['text']);
        $this->assign('blog',$blog);
        $this->display("add_blog");
    }
    /**
     * 删除设计师博客
     * @return [json] [ajax(data,info,status)]
     */
    public function del_blog()
    {
        $ajax_result=array('data'=>'','info'=>'ERROR','status'=>0);
        $this->check_is_ajax_post();//检验ajax的post访问
        $id=I('post.id');//接收到要删除的id
        $map['id'] = $id;//将id作为条件
        $map['userid'] = $this->session_userid;//自己的所属权限作为条件
        $count = M('article')->where($map)->count();//查询这个博文到底属不属于我
        if ($count>0)
        {
            $res=M('article')->delete($id);
            if ($res)
            {
                $ajax_result['data']="删除成功！";
                $ajax_result['info']="Ok";
                $ajax_result['status']=1;
                $this->add_log("删除设计师博客成功",CONTROLLER_NAME."/".ACTION_NAME);//写入操作日志
            }else
            {
                $ajax_result['data']="删除失败！";
            }
        }else
        {
            $ajax_result['data']="该文章不存在或者不属于您";
        }
        $this->ajaxReturn($ajax_result);
    }
    /**
     * 添加 编辑博文的提交入库
     * @return [json] [ajax(data,info,status)]
     */
    public function save_blog()
    {
        $ajax_result=array('data'=>'','info'=>'ERROR','status'=>0);
        #设计师发布的文章
        $this->check_is_ajax_post();//检验ajax的post访问
        //修改设计师文章的字段
        $edit_field=array('type','title','keys');
        foreach ($edit_field as $k => $v)
        {
            $data[$v]=htmlspecialchars(strip_tags(trim($_POST[$v])));#遍历接收
        }
        $data['text']=htmlspecialchars_decode(I("post.text"));
        import('Library.Org.Util.Fiftercontact');//导入过滤类
        $filter = new \Fiftercontact();//实例化过滤类
        $id=intval(I('post.id'));
        $data['text'] =$filter->filter_common($data['text'],array('filter_url','filter_tel',array("filter_sensitive_words",array(2,3,5))));
        $data['title']=$filter->filter_common($data['title'],array('filter_url','filter_tel','filter_qq'));
        $data['keys'] =$filter->filter_common($data['keys'],array('filter_url','filter_tel','filter_qq'));
        $data['userid']=$this->session_userid;
        if($id>0)
        {
            //编辑提交
            $res=M('article')->where(array('id'=>$id))->save($data);
            if($res)
            {
                $msg = "用户编辑文章【".$data['title']."】 成功";
                $this->add_log($msg,CONTROLLER_NAME."/".ACTION_NAME);//写入操作日志
                $ajax_result['data']=$id;
                $ajax_result['info']="Ok";
                $ajax_result['status']=1;
            }
            else
            {
                $ajax_result['data']="文章编辑失败！";
            }
        }else
        {
            //发布时间限制
            $result = D('Article')->getLastPost(session("u_userInfo.id"));
            if (count($result) > 0) {
                $offset = floor((time() - $result["time"])%86400/60);
                if ($offset <= 5) {
                    $this->ajaxReturn(array("data"=>"您的操作过于频繁，请休息5分钟后再试！","status"=>0));
                    exit();
                }
            }

            //新增提交
            $data['time']=time();
            $res=M('article')->add($data);//新增数据
            if($res)
            {
                $msg = "用户添加文章【".$data['title']."】 成功";
                //同时更新设计师的发布时间  即user表的info_time
                $user_map['id']=$this->session_userid;//获取要更新条件
                $user_data['info_time']=time();//获取当前时间
                M('user')->where($user_map)->save($user_data);//更新时间
                $this->add_log($msg,CONTROLLER_NAME."/".ACTION_NAME);//写入操作日志
                $ajax_result['data']=$res;
                $ajax_result['info']="Ok";
                $ajax_result['status']=1;
            }
            else
            {
                $ajax_result['data']="文章发布失败！";
            }
        }
        $this->ajaxReturn($ajax_result);//返回结果
    }
    /**
     * 检验是否是ajax的post提交
     * @return [type] [description]
     */
    private function check_is_ajax_post()
    {
        if (!IS_POST)
        {
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
    private function add_log($info='',$action)
    {
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
}