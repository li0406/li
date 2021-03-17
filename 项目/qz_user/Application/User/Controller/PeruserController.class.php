<?php
namespace User\Controller;
use User\Common\Controller\CompanyBaseController;
class PeruserController extends CompanyBaseController{

    public function index(){
        $info["user"] = $this->baseInfo;
        $uid = $_SESSION["u_userInfo"]["id"];
        if($info["user"]["on"] != 2){
            //如果不是会员公司,跳转到后台首页
            header("Location:".C('QZ_YUMING_SCHEME')."://u.qizuang.com/home/");
        }

        $pageIndex =1;
        $pageCount = 20;
        if(I("get.p") !== ""){
            $pageIndex = I("get.p");
        }
        $condition['user_type'] = '3';
        $condition['register_admin_id'] = $uid;
        $condition['orderBy'] = 'register_time DESC ';


        $result = $this->getUserList($condition,$pageIndex,$pageCount);
        //dump($result);
        $userList = $result['list'];
        $info['page'] = $result['page'];
        unset($result);

        //侧边栏
        $this->assign("nav",10);
        $this->assign("userList",$userList);
        $this->assign("info",$info);
        $this->display();
    }

    //增加用户
    public function add(){
        $info["user"] = $this->baseInfo;
        if($info["user"]["on"] != 2){
            //如果不是会员公司,跳转到后台首页
            header("Location:".C('QZ_YUMING_SCHEME')."://u.qizuang.com/home/");
        }
        $cs = $info['user']['cs'];
        $quyu = D('Area')->getCityArray($cs);

        $this->assign('city',$quyu['shen']);//获取城市
        $this->assign('quyu',$quyu['shi'][$cs]);//获取区域

        if(I('get.upload') == 'avatar'){
            $this->uploadAvatar();
            exit();
        }

        //提交表单
        if($_POST){

            if(!empty($_POST['user'])){
                $user = $_POST['user'];
                $map = array(
                    "user" => array("EQ",$user)
                );
                $s = M('user')->field('id')->where($map)->find();
                if(!empty($s)){
                    $this->ajaxReturn(array("data"=>"","info"=>"该用户名已存在！","status"=>2));
                }
            }else{
                $this->ajaxReturn(array("data"=>"","info"=>"用户名不能为空","status"=>3));
                $this->_error();
            }
            import('Library.Org.Util.Fiftercontact');
            $filter = new \Fiftercontact();

            $data['pass'] = md5($_POST['password']);
            $data['user'] = $_POST['user'];
            $data['classid'] = 1;
            $data['name'] = $filter->filter_common($_POST['name'],array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,5)),"filter_tel","filter_url","filter_qq"));
            $data['sex'] = $_POST['sex'];
            $data['time'] = time();
            $data['logo'] = $_POST['avatar'];
            $data['register_time'] = time();
            $data['user_type'] = '3';
            $data['register_admin_id'] = $_POST['uid'];
            $data['tel_safe'] = $info['user']['tel'];
            $data['qx'] = $_POST['qx'];
            $data['cs'] = $_POST['cs'];
            if(empty($_POST['avatar'])){
                $data['logo'] = C('QZ_YUMING_SCHEME')."://".C("QINIU_DOMAIN")."/".OP("DEFAULT_LOGO");
            }
            $result = D("Peruser")->addUser($data);
            if($result){
                $this->ajaxReturn(array("data"=>"","info"=>"增加成功！","status"=>1));
            }else{
                $this->ajaxReturn(array("data"=>"","info"=>"增加失败！","status"=>0));
            }

            exit();
        }

        $info['uid'] = $_SESSION["u_userInfo"]["id"];
        $this->assign("nav",10);
        $this->assign("info",$info);
        $this->display();
    }


    //上传头像
    public function uploadAvatar(){
        //判断是否登录
        if(!isset($_SESSION["u_userInfo"])){
           exit('Need Login');
        }
        //上传图片
        if(!empty($_FILES["Filedata"])){
            $file = $_FILES["Filedata"];
            $fileExt = pathinfo($_FILES["Filedata"]['name'], PATHINFO_EXTENSION);
            $result = $this->uploadToQiNiu($file["tmp_name"],$fileExt);
            if(!isset($result->Err)){
                echo json_encode(array("status"=>1,"data"=>$result));
            }else{
                echo urldecode(json_encode(array("status"=>0,"info"=>urlencode("图片上传失败!"))));
            }
            die();
        }
        echo json_encode(array("status"=>0,"info"=>"无效的上传!"));
        die();
    }

    //上传到七牛服务器
    private function uploadToQiNiu($file,$fileExt){
        import("Library.Org.Qiniu.io",'','.php');
        import("Library.Org.Qiniu.rs",'','.php');
        $bucket = OP('QINIU_BUCKET');
        $accessKey = OP('QINIU_AK');
        $secretKey = OP('QINIU_CK');
        Qiniu_SetKeys($accessKey, $secretKey);
        $putPolicy = new \Qiniu_RS_PutPolicy($bucket);

        $putPolicy->MimeLimit = 'image/jpeg;image/png;image/gif';
        $putPolicy->SaveKey = 'avatar/$(year)$(mon)$(day)/$(etag)'.'.'.$fileExt;
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

    //根据条件获取业主列表并分页
    private function getUserList($condition,$pageIndex = 1,$pageCount = 10)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $pageIndex = intval($pageIndex)<=0?1:intval($pageIndex);
        import('Library.Org.Page.Page');
        $result = D("Common/Peruser")->getUserList($condition,($pageIndex-1) * $pageCount,$pageCount);
        $config  = array("prev","first","last","next");
        $page = new \Page($pageIndex,$pageCount,$result['count'],$config);
        $pageTmp =  $page->show();
        return array("list"=>$result['result'],"page"=>$pageTmp);
    }
}