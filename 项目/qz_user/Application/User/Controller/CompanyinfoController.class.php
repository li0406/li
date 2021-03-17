<?php
/**
 * 企业信息管理控制器 处理企业信息 企业图片 企业通栏 企业地图  企业分店等控制器
 */
namespace User\Controller;
use Common\Enums\ApiConfig;
use Common\Model\Logic\CompanyTagsLogicModel;
use User\Common\Controller\CompanyBaseController;
class CompanyinfoController extends CompanyBaseController
{
    /**
     * [_initialize 构造方法检测权限和session]
     * @return [type] [description]
     */
    public function _initialize()
    {
        parent::_initialize();//先去走父类的构造
        if(isset($_SESSION["u_userInfo"]))
        {
            $classid = $_SESSION["u_userInfo"]["classid"];//获取用户类别
            if (!in_array($classid,array(3,6)))
            {
                header("Location:".C('QZ_YUMING_SCHEME')."://u.qizuang.com");//如果不是装修公司 不允许进入这个控制器
            }
            $this->nav=1;//确定当前的左侧选项导航在公司信息上
            $this->info = D("User")->getCompanyInfoByAdmin($_SESSION["u_userInfo"]["id"]);
            //获取公司信息
            $this->session_userid=session("u_userInfo.id");//取得公司id
            $this->session_username=session("u_userInfo.name");//取得公司id
        }
        else
        {
            header("Location:".C('QZ_YUMING_SCHEME')."://u.qizuang.com");//没有登录不允许进入控制器
        }
    }
    /**
     * [index 企业用户中心个人信息控制器的主方法]
     * @return [type] [template]
     */
    public function index()
    {
        $info["user"] = $this->info;
        $this->assign('info',$info);
        $cs=$info['user']['cs'];
        $quyu=D('Area')->getCityArray($cs);
        $this->assign('city',$quyu['shen']);//获取城市
        $this->assign('quyu',$quyu['shi'][$cs]);//获取区域
        $marks = M('user_company')
            ->where(array( 'userid' => $this->session_userid))
            ->field('id,lng, lat,map_info,map_address')
            ->select();
        if ($marks[0]['lng']!="")
        {
            $can_make_map=0;//已有标注 不可再标注
        }else
        {
            $can_make_map=1;//还没有标注 可以标注
        }
        $this->assign('can_make_map',$can_make_map);//赋值是否能标注
        $this->assign('marks',  json_encode($marks));//地图标记点
        if(!empty($info['user']['logo'])){
            $logo['0'] = array(
                          'id' => $info['user']['id'],
                          'img_path'=> $info['user']['logo'],
                          'img_host' => 'complex'
                          );
            $this->assign('logo',json_encode($logo));
        }

        $this->assign('tabNav',0);//设定第一个tab选中
        $this->display();
    }
    /**
     * 编辑企业信息
     * @return [json] [ajax(data,info,status)]
     */
    public function edit_info()
    {
        //判断当前用户ID是否可以和编辑的用户ID一致，
        if($_POST['id'] != session('u_userInfo.id')){
            $this->ajaxReturn(['status' => 0, 'info'=>'登录超时，请重新登陆！','data' => '']);
            exit();
            return false;
        }
        $ajax_result=array('data'=>'','info'=>'ERROR','status'=>0);
        #修改装修公司基本资料
        $this->check_is_ajax_post();//检验ajax的post访问
        //修改装修公司user表的字段
        $edit_field=array('qc','jc','kouhao','qx','name','sex','tel','cals','cal','qq','qq1','dz');
        $edit_company_field=array('nickname','nickname1');//修改装修公司详情表user_company的表的字段
        foreach ($edit_field as $k => $v)
        {
            $user_data[$v]=htmlspecialchars(strip_tags($_POST[$v]));#遍历接收
        }
        //如果区县为空就unset
        if(empty($user_data['qx'])){
            unset($user_data['qx']);
        }
        foreach ($edit_company_field as $k => $v)
        {
            $user_company_data[$v]=htmlspecialchars(strip_tags($_POST[$v]));#遍历接收
        }

        //先设返回信息为成功
        $ajax_result['info'] = "";

        //导入过滤类 过滤基本信息
        import('Library.Org.Util.Fiftercontact');//导入过滤类
        $filter = new \Fiftercontact();//实例化过滤类
        $user_data['qc']=  $filter->filter_common($user_data['qc'],array("Sbc2Dbc","filter_script","filter_space",array("filter_sensitive_words",array(2,3,5)),"filter_tel","filter_url","filter_qq"));
        //采用过滤  tel url qq的形式过滤描述
        $user_data['jc']=  $filter->filter_common($user_data['jc'],array("Sbc2Dbc","filter_script","filter_space",array("filter_sensitive_words",array(2,3,5)),"filter_tel","filter_url","filter_qq"));

        if(empty($user_data['qc'])){
            $ajax_result['info'] = "必须要填写公司全称！";
        }
        if(empty($user_data['jc'])){
            $ajax_result['info'] = "必须要填写公司简称！";
        }
        if(mb_strlen($user_data['qc'],'utf8') > 20){
            $ajax_result['info'] = "公司全称不能大于20个中文";
        }
        if(mb_strlen($user_data['jc'],'utf8') > 8){
            $ajax_result['info'] = "公司简称不能大于8个中文！";
        }

        if(!empty($ajax_result['info'])){
            $this->ajaxReturn($ajax_result);
        }

        //采用过滤  tel url qq的形式过滤描述
        $user_data['kouhao']=  $filter->filter_common($user_data['kouhao'],array("Sbc2Dbc","filter_script","filter_space",array("filter_sensitive_words",array(2,3,5)),"filter_tel","filter_url","filter_qq"));
        //采用过滤  tel url qq的形式过滤描述
        $user_data['name']=  $filter->filter_common($user_data['name'],array("filter_tel","filter_url","filter_qq"));
        //采用过滤  tel url qq的形式过滤描述
        $user_data['dz']=  $filter->filter_common($user_data['dz'],array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,5)),"filter_tel","filter_url","filter_qq"));
        //采用过滤  tel url qq的形式过滤描述
        $user_company_data['nickname']=$filter->filter_common($user_company_data['nickname'],array("filter_tel","filter_url","filter_qq",array("filter_sensitive_words",array(2,3,5))));//采用过滤  tel url qq的形式过滤描述
        $user_company_data['nickname1']=$filter->filter_common($user_company_data['nickname1'],array("filter_tel","filter_url","filter_qq",array("filter_sensitive_words",array(2,3,5))));//采用过滤  tel url qq的形式过滤描述

        $id=$this->session_userid;//我的用户个人id
        $res=M('user')->where(array('id'=>$id))->save($user_data);//更新用户表数据
        $res_company=M('user_company')->where(array('userid'=>$id))->save($user_company_data);//更新用户详情表数据

        //更新最后操作时间（op_info_last_time字段）
        $changedata = [];
        $changedata['op_info_last_time'] = time();
        D("Usercompany")->editUserCompany($id,$changedata);

        //由于THINKPHP自身save方法的缺陷 导致必须影响行数才会返回真 这样一来 如果用户不做更改而提交 导致返回false
        //会被用户误认为更新失败 此处我们采用乐观锁 认为每次更新都成功了
        $ajax_result['data']="更新资料成功!";

        $ajax_result['status']=1;
        $this->add_log("编辑企业基本信息",CONTROLLER_NAME."/".ACTION_NAME);//写入操作日志
        $this->ajaxReturn($ajax_result);
    }

    public function editLogo()
    {
        $data["logo"]=htmlspecialchars(strip_tags($_POST['logo']));
        $map["id"]=$this->session_userid;//我的用户个人id
        if(!empty($data["logo"]) && !empty($map["id"])){
            $data["logo"]= C('QZ_YUMING_SCHEME')."://".C('QINIU_DOMAIN')."/".$data["logo"];//如果上传了图片采用全路径
            $result = M("user")->where($map)->save($data);
        }
        if($result){
            //如果装修公司编辑了,同步更新LOGO广告表中的LOGO
            $id = $map["id"];
            $subData = array(
                    "img"=>$data["logo"],
                    "host"=>"",
                    "update_time"=>time()
                             );
            D("Advs")->editAdvs($id,$subData);
            //清除该公司当前城市的LOGO缓存
            S("Cache:hotLogos".$_SESSION["u_userInfo"]["bm"],null);

            //如果装修公司编辑了,同步更新新版LOGO广告表中的LOGO
            $advbannermap = array('company_id' =>$id,'module'=>'home_brand');
            $result = M('adv_banner')->where($advbannermap)->select();
            if(!empty($result)){
                $img_url = str_replace(C('QZ_YUMING_SCHEME')."://".C('QINIU_DOMAIN')."/",'',$data["logo"]);
                $advdata = array('img_url' => $img_url);
                M('adv_banner')->where($advbannermap)->save($advdata);
                S('Cache:Home:Index:brands:'.$_SESSION["u_userInfo"]["cs"],null);
                S('Cache:Home:Index:recommendBrands:'.$_SESSION["u_userInfo"]["cs"],null);
            }

            //更新最后操作时间（op_info_last_time字段）
            $changedata = [];
            $changedata['op_info_last_time'] = time();
            D("Usercompany")->editUserCompany($this->session_userid,$changedata);

            $ajax_result['info']="更新装修公司LOGO成功!";
            $ajax_result['status']=1;
            $this->ajaxReturn($ajax_result);
            exit();
        }
        $ajax_result['status']=0;
        $ajax_result['info']="更新失败!";
        $this->ajaxReturn($ajax_result);
    }

    /**
     * [detail_info 详细资料界面]
     * @return [type] [调用模版]
     */
    public function detail_info()
    {
        $id=$this->session_userid;//我的用户个人id
        $info['user'] = $this->info;
        $this->assign('info',$info);
        $cs=$info['user']['cs'];
        $quyu=D('Area')->getCityArray($cs);
        $this->assign('quyu',$quyu['shi'][$cs]);//获取区域
        //获取服务类型
        $fuwu=D('Leixing')->getlx();
        $baozhang=D('Leixing')->getbg();
        $qita=array_pop($fuwu);//弹出其他
        $gongzhuang=array_pop($fuwu);//去除所有公装
        $jiazhuang=array_pop($fuwu);//去除所有家装
        $fuwu[]=$qita;//将其他放到最后
        array_unshift($fuwu,$jiazhuang);//压入家装
        array_unshift($fuwu,$gongzhuang);//压入工装
        $this->assign('fuwu',$fuwu);//赋值服务类型
        $this->assign('baozhang',$baozhang);//赋值服务保障

        $fengge=D('Fengge')->getfg();//获取风格
        $this->assign('fengge',$fengge);//赋值风格
        $guimo=D('Guimo')->gethGm();//获取规模
        $this->assign('guimo',$guimo);//赋值规模
        $this->assign('tabNav',1);//设置让tab_header中选项卡选中详细资料
        $this->display();
    }
    /**
     * 编辑企业详细信息
     * @return [json] [ajax(data,info,status)]
     */
    public function edit_detail_info()
    {
        $ajax_result=array('data'=>'','info'=>'ERROR','status'=>0);
        #修改装修公司基本资料
        $this->check_is_ajax_post();//检验ajax的post访问
        #编辑详细信息 修改装修公司user_company表的字段
        $edit_field=array('quyu','fuwu','fengge','jiawei','chengli','guimo','luxian','text','baozhang','team_num');
        foreach ($edit_field as $key => $v) {
            $data[$v]=htmlspecialchars(strip_tags(trim($_POST[$v])));
        }
        //开始过滤一部分数据
        import('Library.Org.Util.Fiftercontact');
        $filter = new \Fiftercontact();
        $data['luxian']=$filter->filter_common($data['luxian'],array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,5)),"filter_tel","filter_url","filter_qq"));//采用过滤  tel url qq的形式过滤交通路线
        $data['text']=$filter->filter_common($data['text'],array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,5)),"filter_tel","filter_url","filter_qq"));//采用过滤  tel url qq的形式过滤简介

        $id=$this->session_userid;//我的用户个人id
        $res_company=M('user_company')->where(array('userid'=>$id))->save($data);//更新用户详情表数据
        //由于THINKPHP自身save方法的缺陷 导致必须影响行数才会返回真 这样一来 如果用户不做更改而提交 导致返回false
        //会被用户误认为更新失败 此处我们采用乐观锁 认为每次更新都成功了
        $ajax_result['data']="更新资料成功!";
        $ajax_result['info']="Ok";
        $ajax_result['status']=1;

        //更新最后操作时间（op_info_last_time字段）
        $changedata = [];
        $changedata['op_info_last_time'] = time();
        D("Usercompany")->editUserCompany($id,$changedata);

        $this->add_log("编辑企业详细信息",CONTROLLER_NAME."/".ACTION_NAME);//写入操作日志
        $this->ajaxReturn($ajax_result);
    }

    /**
     * [tag 企业标签界面]
     * @return [type] [调用模版]
     */
    public function tag()
    {
        if (IS_POST) {
            $status = D("Common/Logic/CompanyTagsLogic")->saveCompanyTags(I('post.'), $this->session_userid);
            if ($status) {

                //更新最后操作时间（op_info_last_time字段）
                $changedata = [];
                $changedata['op_info_last_time'] = time();
                D("Usercompany")->editUserCompany($_SESSION["u_userInfo"]["id"],$changedata);

                $this->ajaxReturn(['status' => ApiConfig::REQUEST_SUCCESS, 'info' => '保存成功']);
            } else {
                $this->ajaxReturn(['status' => ApiConfig::REQUEST_FAILL, 'info' => '保存失败']);
            }
        }
        $tags = D("Common/Logic/CompanyTagsLogic")->getCompanyTags($this->session_userid);
        $this->assign('tags',json_encode($tags));
        $this->assign('tabNav',2);//设置让tab_header中选项卡选中公司标签
        $info['user'] = $this->info;
        $this->assign('info',$info);
        $this-> display();
    }
    /**
     * [company_img 企业图片界面]
     * @return [type] [调用模版]
     */
    public function company_img()
    {
        $id = $this->session_userid;//我的用户个人id
        $info['user'] = $this->info;

        $this->assign('info',$info);
        //取个人图片
        $img_list=M('company_img')->field("id,img as img_path,img_host,0 as img_on ")->where(array('userid'=>$id))->select();//取我的图片
        foreach ($img_list as $key => $value)
        {
            //如果是七牛的图片 就把 //staticqn.qizuang.com/ 改为空字符串 统一保证无staticqn.qizuang.com域名
            if ($value['img_host']=="qiniu")
            {
                $img_list[$key]['img_path']=str_replace("//".C("QINIU_DOMAIN")."/","",$value['img_path']);
            }else
            {
                $img_list[$key]['img_path']="upload/company/m_".$value['img_path'];
            }
        }

        if (!empty($info['user']["img"])) {
            $img[] = array(
                "id" => 1,
                "img_path" => $info['user']["img"],
                "img_host" => "qiniu",
                "img_on" => 0
            );
        }

        $this->assign('img',json_encode($img));
        $this->assign('img_list',json_encode($img_list));
        $this->assign('tabNav',3);//设置让tab_header中选项卡选中企业图片
        $this->display("company_img_p285");
    }

    /**
     * 编辑企业详细信息
     * @return [json] [ajax(data,info,status)]
     */
    public function edit_company_img()
    {
        #编辑企业的形象图片
        $ajax_result=array('data'=>'','info'=>'ERROR','status'=>0);
        $this->check_is_ajax_post();//检验ajax的post访问
        $img_list=array_filter(explode(',',$_POST['img']));
        if (empty($img_list))
        {
            $ajax_result['data']="您没有上传图片！";
            return $this->ajaxReturn($ajax_result);
        }
        //开始图片入库
        $id=$this->session_userid;//我的用户个人id
        $company_img=M('company_img');//获取模型
        $company_img->startTrans();//开启事务
        $flag=true;//定义初始化标识为真
        foreach ($img_list as $key => $value)
        {
            $subData = array(
                    "userid"=>$id,
                    "img"=>$value,
                    "img_host"=>"qiniu",
                            );
            $i = $company_img->add($subData);//图片入库
            if($i === false){
                $flag = false;
            }
        }
        if($flag)
        {
            $company_img->commit();//成功可以提交
            $ajax_result['data']="添加企业图片成功";
            $ajax_result['info']="Ok";
            $ajax_result['status']=1;
            $this->add_log("添加企业形象图片成功",CONTROLLER_NAME."/".ACTION_NAME);//写入操作日志
            //更新最后操作时间（op_info_last_time字段）
            $changedata = [];
            $changedata['op_info_last_time'] = time();
            D("Usercompany")->editUserCompany($this->session_userid,$changedata);

        }else{
            $company_img->rollback();//失败要回滚
            $ajax_result['data']="修改企业图片失败";
            $this->add_log("添加企业形象图片失败",CONTROLLER_NAME."/".ACTION_NAME);//写入操作日志
        }
        $this->ajaxReturn($ajax_result);//返回json结果
    }

    /**
     * 删除企业形象图片的ajax方法
     */
    public function del_company_img()
    {
        $this->check_is_ajax_post();//检测是否是ajax提交过来的
        $map['id']=$_POST['id']+0;//接收要删除图片的id
        $map['userid']=$this->session_userid;//只能删除自己的图片
        $res=M('company_img')->where($map)->delete();
        if ($res)
        {
            $ajax_result['data']="删除成功！";
            $ajax_result['info']="删除图片成功";
            $ajax_result['status']=1;
            $this->add_log("删除企业形象图片成功",CONTROLLER_NAME."/".ACTION_NAME);//写入操作日志
            //更新最后操作时间（op_info_last_time字段）
            $changedata = [];
            $changedata['op_info_last_time'] = time();
            D("Usercompany")->editUserCompany($this->session_userid,$changedata);
        }else
        {
            $ajax_result['data']="删除失败！";
            $ajax_result['info']="删除图片成功";
            $ajax_result['status']=0;
            $this->add_log("删除企业形象图片成功",CONTROLLER_NAME."/".ACTION_NAME);//写入操作日志
        }
        $this->ajaxReturn($ajax_result);//返回json结果
    }


    /**
     * 删除企业形象图片的ajax方法
     */
    public function del_company_logo()
    {
        $this->check_is_ajax_post();//检测是否是ajax提交过来的
        $map['id']=$this->session_userid;//接收要删除图片的id
        $map['logo']=I('post.key');//只能删除自己的图片

        $res=M('user')->where($map)->save(array('logo' => ''));
        if ($res){
            $ajax_result['data']="删除成功！";
            $ajax_result['info']="删除图片成功";
            $ajax_result['status']=1;
            $this->add_log("删除企业形象图片成功",CONTROLLER_NAME."/".ACTION_NAME);//写入操作日志
            //更新最后操作时间（op_info_last_time字段）
            $changedata = [];
            $changedata['op_info_last_time'] = time();
            D("Usercompany")->editUserCompany($this->session_userid,$changedata);

        }else{
            $ajax_result['data']="删除失败！";
            $ajax_result['info']="删除图片失败";
            $ajax_result['status']=0;
            $this->add_log("删除企业形象图片失败",CONTROLLER_NAME."/".ACTION_NAME);//写入操作日志
        }
        $this->ajaxReturn($ajax_result);//返回json结果
    }

    /**
     * [tonglan 企业通栏管理]
     * @return [type] [调用模版]
     */
    public function tonglan()
    {
        $info["user"] = $this->info;
        $this->assign('info',$info);//取hengfu字段(显示图片)和on字段(判断是否能上传)
        //由于更新图片可能实时存在 所以必须直接去user_company中查询数据库的hengfu和img_host
        $id=$this->session_userid;//我的用户个人id
        $img_info=M('user_company')->field("hengfu,img_host")->where(array('userid'=>$id))->find();
        $this->assign('img_info',$img_info);
        $this->assign('tabNav',3);//设置让tab_header中选项卡选中通栏管理
        $this->display();
    }

    /**
     * 编辑企业通栏图片信息
     * @return [json] [ajax(data,info,status)]
     */
    public function edit_tonglan()
    {
        #编辑企业的通栏图片
        $ajax_result=array('data'=>'','info'=>'ERROR','status'=>0);
        $this->check_is_ajax_post();//检验ajax的post访问
        $img_list=$_POST['img'];//接收图片
        if (empty($img_list))
        {
            $ajax_result['data']="您没有上传图片！";
            return $this->ajaxReturn($ajax_result);
        }
        //开始图片入库
        $id=$this->session_userid;//我的用户个人id
        $user = M('user')->field('`on`')->find($id);//查询我是不是会员公司 只有会员公司可以自定义横幅
        if ($user['on'] != 2)
        {
            $ajax_result['data']="对不起，您不是会员，或者会员已经到期！";
            return $this->ajaxReturn($ajax_result);
        }
        $data['hengfu']=$img_list;
        $data['img_host']='qiniu';
        $res=M('user_company')->where(array('userid'=>$id))->save($data);

        if($res)
        {
            $ajax_result['data']="添加企业通栏成功";
            $ajax_result['info']="Ok";
            $ajax_result['status']=1;
            $this->add_log("编辑企业通栏图片信息成功",CONTROLLER_NAME."/".ACTION_NAME);//写入操作日志
        }else{
            $ajax_result['data']="修改企业通栏失败";
            $this->add_log("编辑企业通栏图片信息成功",CONTROLLER_NAME."/".ACTION_NAME);//写入操作日志
        }
        $this->ajaxReturn($ajax_result);//返回json结果
    }

    /**
     * 恢复默认通栏
     */
    public function default_tonglan()
    {
        #恢复企业的默认通栏
        $ajax_result=array('data'=>'','info'=>'ERROR','status'=>0);
        $this->check_is_ajax_post();//检验ajax的post访问
        $id=$this->session_userid;//我的用户个人id
        $data['hengfu']='/Public/company/image/banner.jpg';
        $data['img_host']='';
        $res=M('user_company')->where(array('userid'=>$id))->save($data);
        if ($res)
        {
            $ajax_result['data']="恢复默认成功!";
            $ajax_result['info']="Ok";
            $ajax_result['status']=1;
            $this->add_log("恢复默认通栏成功",CONTROLLER_NAME."/".ACTION_NAME);//写入操作日志
        }
        else
        {
            $ajax_result['data']="恢复默认失败!";
            $this->add_log("恢复默认通栏成功",CONTROLLER_NAME."/".ACTION_NAME);//写入操作日志
        }
        $this->ajaxReturn($ajax_result);//返回json结果
    }
    /**
     * [branchstore 企业分店管理]
     * @return [type] [调用模版]
     */
    public function branchstore()
    {
        $id=$this->session_userid;//我的用户个人id
        $info['user'] = $this->info;
        $this->assign('info',$info);
        $store_list = array();
        $list = M('company_branchstore')->where(array('comid' => $id))->order('seq')->select();//取分店列表
        $this->assign('list', $list);//赋值分店列表
        $this->assign('tabNav',4);//设置让tab_header中选项卡选中分店管理
        $this->display();
    }
    /**
     * 编辑企业网店信息
     * @return [json] [ajax(data,info,status)]
     */
    public function save_branchstore()
    {
        $ajax_result=array('data'=>'','info'=>'ERROR','status'=>0);
        #修改装修公司网店信息
        $this->check_is_ajax_post();//检验ajax的post访问
        #编辑详细信息 修改企业网店信息company_branchstore表的字段
        $edit_field=array('id','shortname','seq','subname','addr','tel','mobile','nickname','qq','nickname1','qq1');
        foreach ($edit_field as $key => $v) {
            $data[$v]=htmlspecialchars(strip_tags(trim($_POST[$v])));
        }
        $data['seq']=intval($data['seq']);//强制转化排序
        //开始过滤一部分数据
        import('Library.Org.Util.Fiftercontact');
        $filter = new \Fiftercontact();
        $data['shortname']=$filter->filter_common($data['shortname'],array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,5)),"filter_tel","filter_url","filter_qq"));//采用过滤  tel url qq的形式过滤名称
        $data['subname']=$filter->filter_common($data['subname'],array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,5)),"filter_tel","filter_url","filter_qq"));//采用过滤  tel url qq的形式过滤全称
        $data['addr']=$filter->filter_common($data['addr'],array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,5)),"filter_tel","filter_url","filter_qq"));//采用过滤  tel url qq的形式过滤地址
        $data['nickname']=$filter->filter_common($data['nickname'],array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,5)),"filter_tel","filter_url","filter_qq"));//采用过滤  tel url qq的形式过客服qq名称
        $data['nickname1']=$filter->filter_common($data['nickname1'],array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,5)),"filter_tel","filter_url","filter_qq"));//采用过滤  tel url qq的形式过客服qq名称

        $id=$this->session_userid;//我的用户个人id
        if ($data['id']>0)
        {
            #有id传参 是更新数据
            $map['comid']=$id;
            $map['id']=$data['id'];
            $res_company=M('company_branchstore')->where($map)->save($data);//更新用户详情表数据
            //由于THINKPHP自身save方法的缺陷 导致必须影响行数才会返回真 这样一来 如果用户不做更改而提交 导致返回false
            //会被用户误认为更新失败 此处我们采用乐观锁 认为每次更新都成功了
            $ajax_result['data']="更新分店成功!";
            $ajax_result['info']="Ok";
            $ajax_result['status']=1;
            $this->add_log("编辑企业分店信息成功",CONTROLLER_NAME."/".ACTION_NAME);//写入操作日志
            //更新最后操作时间（op_info_last_time字段）
            $changedata = [];
            $changedata['op_info_last_time'] = time();
            D("Usercompany")->editUserCompany($this->session_userid,$changedata);
        }else
        {
            unset($data['id']);
            $data['comid']=$id;
            $res_company=M('company_branchstore')->add($data);
            if ($res_company)
            {
                $ajax_result['data']="添加分店成功!";
                $ajax_result['info']="Ok";
                $ajax_result['status']=1;
                $this->add_log("添加企业分店成功",CONTROLLER_NAME."/".ACTION_NAME);//写入操作日志
                //更新最后操作时间（op_info_last_time字段）
                $changedata = [];
                $changedata['op_info_last_time'] = time();
                D("Usercompany")->editUserCompany($this->session_userid,$changedata);
            }
            else
            {
                $ajax_result['data']="添加分店失败!";
                $this->add_log("添加企业分店失败",CONTROLLER_NAME."/".ACTION_NAME);//写入操作日志
            }
        }
        $this->ajaxReturn($ajax_result);
    }
    /**
     * 删除企业分店信息
     * @return [json] [ajax(data,info,status)]
     */
    public function del_branchstore()
    {
        $ajax_result=array('data'=>'','info'=>'ERROR','status'=>0);
        $this->check_is_ajax_post();//检验ajax的post访问
        $id=I('post.id');//接收要传参的id
        if ($id<1)
        {
            $ajax_result['data']="传参丢失！";
            $this->ajaxReturn($ajax_result);
        }
        $map['id']=$id;//查询条件
        $map['comid']=$this->session_userid;//查询条件
        $company_branchstore=M('company_branchstore')->where($map)->select();//先查是不是我所管辖的这个分店
        if (!$company_branchstore)
        {
            $ajax_result['data']="该分店不存在或该分店不属于您！";//分店不存在或者说分店不属于这家装修公司
            $this->ajaxReturn($ajax_result);
        }
        $res=M('company_branchstore')->where($map)->delete();//真删这家分店
        if ($res)
        {
            $ajax_result['data']="删除分店成功！";
            $ajax_result['info']="Ok";
            $ajax_result['status']=1;
            $this->add_log("删除企业分店信息成功",CONTROLLER_NAME."/".ACTION_NAME);//写入操作日志
        }else
        {
            $ajax_result['data']="删除分店失败！";
            $this->add_log("删除企业分店信息失败",CONTROLLER_NAME."/".ACTION_NAME);//写入操作日志
        }
        $this->ajaxReturn($ajax_result);
    }
    /**
     * 修改企业百度地图标记地址
     * @return [json] [ajax(data,info,status)]
     */
    public function company_map()
    {
        $ajax_result=array('data'=>'','info'=>'ERROR','status'=>0);
        $this->check_is_ajax_post();//检验ajax的post访问
        $id=$this->session_userid;//我的用户个人id
        $data['lng']=I('post.lng');//接收经度
        $data['lat']=I('post.lat');//接收纬度
        $data['map_info']=I('post.com');//接收地图描述
        $data['map_address']=I('post.addr');//接收地图地址
        //导入过滤类 过滤地图描述
        import('Library.Org.Util.Fiftercontact');//导入过滤类
        $filter = new \Fiftercontact();//实例化过滤类
        $data['map_info']=  $filter->filter_common($data['map_info'],array("filter_tel","filter_url","filter_qq"));//采用过滤  tel url qq的形式过滤描述
        //过滤完毕 开始写入日志
        $this->add_log("修改企业百度地图标记地址",CONTROLLER_NAME."/".ACTION_NAME);//写入操作日志
        /*
        unserialize
        lng:120.649404
        lat:31.289261
        com:dfdsfsdfsdfsdfsdf
        addr:苏州市姑苏区翠庭桥
        cityid:320500
         */
        $res_company = M('user_company')->where(array('userid'=>$id))->save($data);
        $ajax_result['data']="标记成功!";
        $ajax_result['info']="Ok";
        $ajax_result['status']=1;
        $this->ajaxReturn($ajax_result);//返回json结果
    }

    /**
     * 删除企业百度地图标记地址
     * @return [json] [ajax(data,info,status)]
     */
    public function company_map_del()
    {
        $ajax_result=array('data'=>'','info'=>'ERROR','status'=>0);
        $this->check_is_ajax_post();//检验ajax的post访问
        $id=$this->session_userid;//我的用户个人id
        $data['lng']='';//经度设置为空
        $data['lat']='';//纬度设置为空
        $data['map_info']='';//地图描述设置为空
        $data['map_address']='';//地图地址设置为空
        $res_company=M('user_company')->where(array('userid'=>$id))->save($data);
        $this->add_log("删除企业百度地图标记地址",CONTROLLER_NAME."/".ACTION_NAME);//写入操作日志
        $ajax_result['data']="删除成功!";
        $ajax_result['info']="Ok";
        $ajax_result['status']=1;
        $this->ajaxReturn($ajax_result);//返回json结果
    }

    /**
     * 营业执照
     * @return [type] [description]
     */
    public function businessLicence()
    {
        if ($_POST) {
            $img1 = array_filter(explode(",",I("post.img1")));
            if (count($img1) == 0) {
                $this->ajaxReturn(array('info'=>'请上传营业执照！','status'=>0));//返回json结果
            }
            $img2 = array_filter(explode(",",I("post.img2")));
            $img3 = array_filter(explode(",",I("post.img3")));

            $img = array(
                "company_id" => session("u_userInfo.id"),
                "type" => 1,
                "img1" => $img1[0],
                "img2" => empty($img1[1])?"":$img1[1],
                "img3" => empty($img1[2])?"":$img1[2],
                "img4" => empty($img1[3])?"":$img1[3],
                "img_host" => 'qiniu',
                "time" => time(),
                "state" => 1
            );

            if (I("post.img_state1") != "") {
                $img["state"] = I("post.img_state1");
            }
            $data[] = $img;

            if (count($img3) > 0) {
                $img = array(
                    "company_id" => session("u_userInfo.id"),
                    "type" => 3,
                    "img1" => $img3[0],
                    "img2" => empty($img3[1])?"":$img3[1],
                    "img3" => empty($img3[2])?"":$img3[2],
                    "img4" => empty($img3[3])?"":$img3[3],
                    "img_host" => 'qiniu',
                    "time" => time(),
                    "state" => 1
                );
                if (I("post.img_state3") != "") {
                    $img["state"] = I("post.img_state3");
                }
                $data[] = $img;
            }

            //删除原来的审核图片
            D("SaleBusinessLicence")->delBusinessLicence(session("u_userInfo.id"));

            //添加审核图片
            $i = D("SaleBusinessLicence")->addBusinessLicence($data);

            if ($i !== false) {
                //更新最后操作时间（op_info_last_time字段）
                $changedata = [];
                $changedata['op_info_last_time'] = time();
                D("Usercompany")->editUserCompany($_SESSION["u_userInfo"]["id"],$changedata);

                $this->ajaxReturn(array('status'=> 1));
            }
            $this->ajaxReturn(array('info'=>'网络传输异常,请稍后再试！','status'=>0));

        } else {
            //设置让tab_header中选项卡选中营业执照审核
            $this->assign('tabNav',6);
            //查询装修公司营业执照信息
            $result = D("SaleBusinessLicence")->findCompanyBusinessLicenceInfo(session("u_userInfo.id"));
            foreach ($result as $key => $value) {
                if (!isset($info)) {
                    // $info["state"] = $value["state"];
                    $info["company_id"] = $value["company_id"];
                }

                switch ($value["state"]) {
                    case '1':
                    case '2':
                    case '3':
                        //审核中
                        $info["child"][$value["type"]]['step'] = 1;
                        $count ++;
                        break;
                    case '4':
                        $info["child"][$value["type"]]['step'] = 3;
                        $count ++;
                        break;
                    case '5':
                        $info["child"][$value["type"]]['step'] = 4;
                        $count ++;
                        break;
                    default:
                        $info["child"][$value["type"]]['step'] = 2;
                        break;
                }
                $info["child"][$value["type"]]["state"] = $value["state"];
                $info["child"][$value["type"]]["child"][] = $value["img1"];
                $info["child"][$value["type"]]["images"] .= $value["img1"].",";
                if (!empty($value["img2"])) {
                    $info["child"][$value["type"]]["child"][] = $value["img2"];
                    $info["child"][$value["type"]]["images"] .= $value["img2"].",";
                }
                if (!empty($value["img3"])) {
                    $info["child"][$value["type"]]["child"][] = $value["img3"];
                    $info["child"][$value["type"]]["images"] .= $value["img3"].",";
                }
                if (!empty($value["img4"])) {
                    $info["child"][$value["type"]]["child"][] = $value["img4"];
                    $info["child"][$value["type"]]["images"] .= $value["img4"].",";
                }
            }

            if ($count != 2) {
                $info["isSave"] = 1;
            }
            $info["user"] = $this->info;
            $this->assign("info",$info);
            $this->assign("step",$step);
            $this->display();
        }
    }

    /**
     * 删除营业执照图片
     * @return [type] [description]
     */
    public function delBusinessLicence()
    {
        if ($_POST) {
            $type = I("post.type");
            $id=$this->session_userid;

            $i = D("SaleBusinessLicence")->delBusinessLicence($id,$type);
            if ($i !== false) {
                $this->ajaxReturn(array('status'=> 1));
            }
            $this->ajaxReturn(array('info'=>'网络传输异常,请稍后再试！','status'=>0));
        }
    }

    /*
     * 添加企业形象图片
     */
    public function add_enterprise_img()
    {
        if ($_POST) {
            $data = array(
                "img" => I("post.img")
            );
            $i = D("Usercompany")->editUserCompany($this->session_userid,$data);
            if ($i !== false) {
                $this->info["img"] = I("post.img");
                $this->ajaxReturn(array('data'=>'','info'=>'','status'=>1));
            }
            $this->ajaxReturn(array('data'=>'','info'=>'上传失败,请刷新重试！','status'=>0));
        }
    }

    /**
     * 删除企业形象图片
     * @return [type] [description]
     */
    public function del_enterprise_img()
    {
        if ($_POST) {
            $data = array(
                "img" => ""
            );
            $i = D("Usercompany")->editUserCompany($this->session_userid,$data);
            if ($i !== false) {
                 $this->info["img"] = "";
                $this->ajaxReturn(array('data'=>'','info'=>'','status'=>1));
            }
            $this->ajaxReturn(array('data'=>'','info'=>'删除失败,请刷新重试！','status'=>0));
        }
    }

    public function companysms()
    {
        if (IS_POST) {
            if (empty($this->baseInfo["id"])) {
                $this->ajaxReturn(["data" => "", "info" => "登录过期，请重新登录", "status" => 0]);
            }

            if (S("Cache:Geetest:".session_id()) == 1) {
                S("Cache:Geetest:".session_id(),null);
                $_POST['tel'] = I("POST.tel");
                R("Common/Sms/sendsms");
            }
            die();
        }
    }

    /**
     * 邮件发送
     * @return [type] [description]
     */
    public function sendemail(){
        if(IS_POST){
            if (S("Cache:Geetest:".session_id()) == 1) {
                S("Cache:Geetest:".session_id(),null);
                $_POST['email'] =  $this->baseInfo["mail_safe"];
                R("Common/Sms/sendemail");
            }
        }
        $this->ajaxReturn(array("data"=>"","info"=>"验证错误！","status"=>9));
        die();
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
     * 获取装修公司基本信息
     * @param  [type] $comid [description]
     * @param  [type] $cs    [description]
     * @return [type]        [description]
     */
    private function getCompanyInfoByAdmin($comid,$cs){
        $user =  D("User")->getCompanyInfoByAdmin($comid,$cs);
        return $user;
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