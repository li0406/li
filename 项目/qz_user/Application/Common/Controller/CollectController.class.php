<?php
namespace Common\Controller;
use Think\Controller;
class CollectController extends Controller{
    /**
     * 添加收藏
     */
    public function setCollect(){
        $data = $_POST;
        if(isset($_SESSION["u_userInfo"])){
            $saveData = array(
                    "classtype"=>remove_xss($data["classtype"]),
                    "classid"=>remove_xss($data["classid"]),
                    "userid"=>$_SESSION["u_userInfo"]["id"],
                    "time"=>time()
                          );
            $i = D("Usercollect")->addCollect($saveData);
            if($i !== false){
                $this->ajaxReturn(array("data"=>"","info"=>"","status"=>1));
            }
            $this->ajaxReturn(array("data"=>"","info"=>"操作失败,请刷新重新！","status"=>0));
        }
        $this->ajaxReturn(array("data"=>"","info"=>"请先登录后收藏！","status"=>0));

    }

    /**
     * 取消收藏
     * @return [type] [description]
     */
    public function cancelcollect(){
        if($_POST){
            $id = I("post.id");
            $type = I("post.type");
            $i = D("Usercollect")->delcollect($id,$_SESSION["u_userInfo"]["id"]);
            if($i !== false){
                $this->ajaxReturn(array("data"=>"","info"=>"","status"=>1));
            }
            $this->ajaxReturn(array("data"=>"","info"=>"操作失败,请刷新重新！","status"=>0));
        }
    }
}