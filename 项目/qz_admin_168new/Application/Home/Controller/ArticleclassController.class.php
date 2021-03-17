<?php
namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\WwwArticleLogicModel;

class ArticleClassController extends HomeBaseController{

    /**
     * [category 文章分类管理]
     * @return [type] [description]
     */
    public function index()
    {
        $info['categorytree'] = D('WwwArticleClass')->getArticleClassTree();
        $this->assign('info', $info);
        $this->display();
    }

    /**
     * [operate 更新主站文章分类]
     * @return [type] [description]
     */
    public function operate(){
        $data = $status = 0;
        $info = '文章分类更新失败!';
        if (isset($_POST['class_tree'])) {
            $unIds = array();
            $addIds = array();
            $cat = json_decode($_POST['class_tree'], true);
            //递归获取的json数据,获取所有文章编号
            $ids = $this->getTreeId($cat);
            //将新增的分类取出
            foreach ($ids as $key => $value) {
                if(isset($value["pid"])){
                    $addIds[] = $value;
                    unset($ids[$key]);
                }
            }

            //查询出当前所有的分类
            $categories = D('WwwArticleClass')->getArticleClassList();
            //交叉对比当前的编号,如果没有则表示删除,取出删除的分类编号
            foreach ($categories as $key => $value) {
                foreach ($ids as $k => $val) {
                    if($val["id"] == $value["id"]){
                        unset($categories[$key]);
                    }
                }
            }

            foreach ($categories as $key => $value) {
               $unIds[] = $value["id"];
            }

            //如果IDS大于0，则标识有修改的分类
            if(count($unIds) > 0 ){
                $data = array(
                        "obsolete"=>1
                              );

                $result = D('WwwArticleClass')->editArticleClass($unIds,$data);
                if($result !==  false){
                    $info   = 'OK';
                    $status = 1;
                }else{
                    $info   = '操作失败!';
                    $status = 0;
                }
            }

            //如果IDS的数量不为0，则标识有新加的分类
            if(count($addIds) > 0){
                D('WwwArticleClass')->startTrans();
                $i = 0;
                foreach ($addIds as $key => $value) {
                    //查询出最大ID
                    $maxid = D('WwwArticleClass')->getArticleClassMaxId();
                    $saveData = array(
                        "classname"=>$value["name"],
                        "pid"=>$value["pid"],
                        "id"=>$maxid+1,
                        "is_new"=>1
                              );
                    $result =  D('WwwArticleClass')->addArticleClass($saveData);
                    if($result !==  false){
                        $i ++;
                    }
                }

                if($i == count($addIds)){
                    D('WwwArticleClass')->commit();
                    $status = 1;
                    $info = 'OK';
                }else{
                    D('WwwArticleClass')->rollback();
                    $status = 0;
                    $info = '操作失败';
                }
            }
        }
        $this->ajaxReturn($data, $info, $status);
    }

    /**
     * 根据编号跟新文章信息
     * @return [type] [description]
     */
    public function operateDetails(){
        $data = I('post.');
        if(!empty($data)){
            $info = array(
                    "classname"=>$data["name"],
                    "seq"=>$data["seq"],
                    "shortname"=>$data["shortname"],
                    "keywords"=>$data["keywords"],
                    "description"=>$data["description"],
                    "title"=>$data["title"]
                          );
            if(empty($data["id"])){ //新增分类
                $info['pid'] = $data["pid"];
                $info['level'] = $data["level"]+1;
                $info['is_new'] = 1;
                $data["id"] = $result = D("WwwArticleClass")->addArticleClass($info);
            }else{ //编辑分类
                $result = D("WwwArticleClass")->editArticleClass($data["id"],$info);
            }
            if($result !== false){
                $logic = new WwwArticleLogicModel();
                //删除原来的分词
                $logic->delClassParticiple($data["id"],1);
                //添加新的分词
                $search_word = str_replace("；",";",I("post.search_word"));
                $search_word = array_filter(explode(";",$search_word));

                if (count($search_word) > 0) {
                    $logic->setClassParticiple($data["id"],$search_word);
                }

                $this->ajaxReturn(array('data' => '','info' => '操作成功','status' =>1));
            }
        }
        $this->ajaxReturn(array('data' => '','info' => '操作失败','status' =>0));
    }

    public function delArticleClass()
    {
        $id = I('post.id');
        if(!empty($id)){
            $result = D("WwwArticleClass")->editArticleClass(array($id),array('obsolete'=>1));
            if($result){
                $this->ajaxReturn(array('data' => '','info' => '编辑成功','status' =>1));
            }
        }
        $this->ajaxReturn(array('data' => '','info' => "",'status' =>0));
    }

    /**
     * 获取文章类别的详细信息模版
     * @return [type] [description]
     */
    public function getArticleClassDetails(){
        //编辑文章分类
        $id = I('get.id');
        if(!empty($id)){
            $classInfo = D("WwwArticleClass")->getArticleClassById($id);
            $this->assign("info",$classInfo);
            $tmp = $this->fetch("details");
            $this->ajaxReturn(array('data' => $tmp,'info' => '','status' =>1));
        }
        //新增文章分类
        $parentid = I('post.parentid');
        $level = I('post.level');
        $this->assign("info",array('parentid' => $parentid, 'level' => $level));
        $tmp = $this->fetch("details");
        $this->ajaxReturn(array('data' => $tmp,'info' => '','status' =>1));
    }
}
