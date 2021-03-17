<?php
/**
 * 公装美图
 */
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
class RecommendController extends HomeBaseController
{
    public function _initialize(){
        parent::_initialize();
    }


    public function index()
    {
        //获取推荐好图数据
        $info['goodimg'] = D("meitu")->getGoodImg();
        $info['imgcount'] = count($info['goodimg']);

        //获取专题好图
        $info['zhuanti'] = D("meitu")->getZhuanti();
        $info['ztcount'] = count($info['zhuanti']);

        $this->assign("info",$info);
        $this->display();
    }

    //推荐好图 编辑和新增
    public function goodimg()
    {
        if(IS_POST){
        //编辑或新增
            //通过

            $data = I("post.data");
            $newid = $data['newid'];
            //获取公装美图的位置
            $condition['goodimg'] = $data['sort'];
            $id = $data['id'];

            if(!empty($id)){
                $count = D("meitu")->getGoodImgCount();
                $info = D("meitu")->getOnemeitu($id);
                if($count >= 6 && $info['goodimg'] == '0'){
                    $this->ajaxReturn(array('info' => "推荐好图已达上限，无法添加！",'status' => 0));
                }
                //编辑状态，先要修改原先的公装美图的goodimg属性为0，在新增
                $old['goodimg'] = '0';
                $result = D("meitu")->editMeitu($id,$old);
                if(false === $result){
                    $this->ajaxReturn(array('info' => "修改失败，请重新尝试！",'status' => 0));
                }
            }else{
                $count = D("meitu")->getGoodImgCount();
                if($count >= 6){
                    $this->ajaxReturn(array('info' => "推荐好图已达上限，无法添加！",'status' => 0));
                }
            }

            $i = D("meitu")->editMeitu($newid,$condition);
            if(false !== $i){
                $this->ajaxReturn(array('status' => 1));
            }
            $this->ajaxReturn(array('info' => "修改失败，请重新尝试！",'status' => 0));
        }else{
            $id = I("get.id");
            if(!empty($id)){
            //编辑
                $info = D("meitu")->getOnemeitu($id);

                $this->assign("info",$info);
            }else{
                //当id为空，新增状态时，检测推荐好图是否达上线
                $count = D("meitu")->getGoodImgCount();
                if($count >= 6){
                    $this->error('推荐好图已达上限，无法添加~~','/recommend/',3);
                }
            }

            $this->display();
        }
    }


     //热门专题 编辑和新增
    public function zhuanti()
    {
        if(IS_POST){
        //编辑或新增
            //通过

            $data = I("post.data");
            $newid = $data['newid'];
            //获取公装美图的位置
            $condition['zt'] = $data['sort'];
            $id = $data['id'];

            if(!empty($id)){
                $count = D("meitu")->getZhuantiCount();
                $info = D("meitu")->getOnemeitu($id);
                if($count >= 6 && $info['zt'] == '0'){
                    $this->ajaxReturn(array('info' => "热门专题已达上限，无法添加！",'status' => 0));
                }
                //编辑状态，先要修改原先的公装美图的zt属性为0，在新增
                $old['zt'] = '0';
                $result = D("meitu")->editMeitu($id,$old);
                if(false === $result){
                    $this->ajaxReturn(array('info' => "修改失败，请重新尝试！",'status' => 0));
                }
            }else{
                $count = D("meitu")->getZhuantiCount();
                if($count >= 6){
                    $this->ajaxReturn(array('info' => "热门专题已达上限，无法添加！",'status' => 0));
                }
            }

            $i = D("meitu")->editMeitu($newid,$condition);
            if(false !== $i){
                $this->ajaxReturn(array('status' => 1));
            }
            $this->ajaxReturn(array('info' => "修改失败，请重新尝试！",'status' => 0));
        }else{
            $id = I("get.id");
            if(!empty($id)){
            //编辑
                $info = D("meitu")->getOnemeitu($id);

                $this->assign("info",$info);
            }else{
                //当id为空，新增状态时，检测推荐好图是否达上线
                $count = D("meitu")->getZhuantiCount();
                if($count >= 6){
                    $this->error('推荐好图已达上限，无法添加~~','/recommend/',3);
                }
            }

            $this->display();
        }
    }

    //获取公装美图的id，title，搜索查询
    public function getpubmeitutitle()
    {
        $query = trim(I("get.query"));
        if(!empty($query)){
            $list = D("meitu")->getMeituTitle($query);
        }
        $this->ajaxReturn(array("data"=>$list,"info"=>"","status"=>1));
    }
}