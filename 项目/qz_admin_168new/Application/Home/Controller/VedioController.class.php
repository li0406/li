<?php

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\ThematicWordsLogicModel;

/**
*  视频学装修
*/
class VedioController extends HomeBaseController
{

    public function index()
    {
        if (I("get.title") !== "") {
            $title = I("get.title");
            $this->assign("title",$title);
        }
        $id = I('get.id');
        $result = $this->getVedioList($title, $id);
        $i = 1;
        foreach ($result['list'] as $k => $v) {
            $result['list'][$k]['count'] = $i;
            $i++;
        }
        $this->assign("list",$result);
        $this->assign("p",I("get.p"));
        $this->display();
    }

    /**
     * 添加编辑视频
     * @return [type] [description]
     */
    public function vedioup()
    {
        if ($_POST) {
            $id = I("post.id");
            $bigCategory = I('post.bigCategory');
            $subCategory = I('post.category');

            $category = array(
                '1' => array('1','2','3'),
                '2' => array('4','5','6','7','8','9'),
                '3' => array('10','11','12','13'),
                '4' => array('14','15'),
            );

            $wxapp_url = I("post.wxapp_url");
            if(!empty($wxapp_url)){
                 /*
                http://www.miaopai.com/show/7TQOBWoFUeNt8M7xqQl5jxazeCxeq44z.htm
                http://gslb.miaopai.com/stream/7TQOBWoFUeNt8M7xqQl5jxazeCxeq44z.mp4
                */
                $parseUrl = parse_url($wxapp_url);
                if($parseUrl['host'] == 'www.miaopai.com'){
                    $wxapp_url = str_replace(array('http://www.miaopai.com/show/','.htm'),'',$wxapp_url);
                    $wxapp_url = 'http://gslb.miaopai.com/stream/'.$wxapp_url.'.mp4';
                }
            }

            $data = array(
                "title" => I("post.title"),
                "description" => I("post.description"),
                "type" => I("post.type"),
                "teacher" => I("post.teacher"),
                "cover_img" => I("post.bigimg"),
                "url" => I("post.url"),
                "mobile_url" => I("post.mobile_url"),
                "uid" => $this->User["id"],
                "uname" => $this->User["name"],
                "updated_at" => time(),
            );

            $model = D("ArticleVedio");
            $status = 0;

            if ($model->create($data,1)) {
                if (!empty($id)) {
                    $isEdit = true;
                    $i = $model->editVedio($id,$data);
                } else {
                    $data["time"] = time();
                    $data['likes'] = rand(500,800);
                    $id = $i = $model->addVedio($data);

                    if (!empty($id)) {
                        // 添加文章标签
                        $thematicLogic = new ThematicWordsLogicModel();
                        $thematicLogic->searchAndRelatedThematic($data["title"], $id, 9, 2, 10);
                    }
                }

                if($isEdit == true){
                    //删除原有的分类信息
                    D('ArticleVedio')->removeCategoryItem($id);
                }

                //一级分类
                foreach ($bigCategory as $k => $v) {
                    $data = array();
                    $data['pid'] = '0';
                    $data['cid'] = $k;
                    $data['vid'] = $id;
                    D('ArticleVedio')->addCategory($data);
                }
                //二级分类
                foreach ($subCategory as $k => $v) {
                    $data = array();
                    $data['pid'] = $this->getBigCategoryId($category,$k);
                    $data['cid'] = $k;
                    $data['vid'] = $id;
                    D('ArticleVedio')->addCategory($data);
                }

                if ($i !== false) {
                    $status = 1;
                    $tags = I("post.tags");
                    //删除原有标签
                    D("ArticleVedio")->deltags($id);
                    //添加标签
                    foreach ($tags as $key => $value) {
                        $subData[] = array(
                            "vedio_id" => $id,
                            "tag_id" => $value
                        );
                    }
                    if (count($subData) > 0) {
                        D("ArticleVedio")->addAllTag($subData);
                    }
                }

            } else {
                $errMsg = $model->getError();
            }

            $this->ajaxReturn(array("info"=>$errMsg,"status"=>$status));
        } else {
            if (I("get.id") !== "") {
                $id = I("get.id");
                $p = I("get.p");
                $result = D("ArticleVedio")->getVedioInfo($id);
                foreach ($result as $key => $value) {
                    if (!isset($info)) {
                        /*if (!empty($value["logo"])) {
                            $imgs = array('<img src="//'.OP("QINIU_DOMAIN")."/".$value["logo"].'"/>');
                            $json = json_encode($imgs);
                            $value["img"] =  $json;
                        }*/
                        if (!empty($value["cover_img"])) {
                            $imgs = array('<img src="//'.OP("QINIU_DOMAIN")."/".$value["cover_img"].'"/>');
                            $json = json_encode($imgs);
                            $value["bigimg"] =  $json;
                        }

                        $info = $value;
                    }
                    if (!empty($value["name"])) {
                        $info["child"][] = array(
                            "id" => $value["tag_id"],
                            "text" => $value["name"]
                        );
                    }
                }

                //分类处理
                $result = D('ArticleVedio')->getCategoryByItem($id);
                foreach ($result as $k => $v) {
                    if($v['pid'] == '0'){
                        $category['big'][] = $v['cid'];
                    }else{
                        $category['sub'][] = $v['cid'];
                    }
                }

                $this->assign("category",$category);
                $this->assign("info",$info);
                $this->assign("p",$p);
            }
            //查询讲师
            $teachers = D("ArticleVedio")->getUsers();
            $this->assign('teachers',$teachers);
            $this->display();
        }
    }

    public function findtags()
    {
        if ($_POST) {
            $query = I("post.query");
            $result = D("Tags")->getTagsByName($query);
            return $this->ajaxReturn(array("data"=>$result,"status"=>1));
        }
    }

    public function editvedio()
    {
         if ($_POST) {
            $id = I("post.id");

            $data = array(
                "istop" => I("post.value")
            );
            $i = D("ArticleVedio")->editVedio($id,$data);
            if ($i !== false) {
                $this->ajaxReturn(array("status"=>1));
            }
            $this->ajaxReturn(array("info"=>"操作失败,请联系技术部门！","status"=>0));
        }
    }

    public function delvedio($value='')
    {
        if ($_POST) {
            $id = I("post.id");

            $data = array(
                "isdelete" => 1
            );
            $i = D("ArticleVedio")->editVedio($id,$data);
            if ($i !== false) {
                $this->ajaxReturn(array("status"=>1));
            }
            $this->ajaxReturn(array("info"=>"操作失败,请联系技术部门！","status"=>0));
        }
    }

    private function getVedioList($title, $id)
    {
        $count = D("ArticleVedio")->getVedioListCount($title, $id);

        if(count($count) > 0){
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $p->setConfig('header','个申请');
            $p->setConfig('prev', "上一页");
            $p->setConfig('next', '下一页');
            $p->setConfig('theme','%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
            $show = $p->show();
            $list = D("ArticleVedio")->getVedioList($p->firstRow,$p->listRows,$title, $id);
            return array("page"=>$show,"list"=>$list);
        }
    }

    /**
     * 视频讲师（含编辑）
     */
    public function addUser()
    {
        if($_POST){
            //有内容传入，先添加或编辑 传入 author  logo
            $data['author'] = I('post.author');
            $data['logo']   = I('post.logo');
            $tid            = I('post.tid');
            //查询是否有这个讲师
            //$user = D("ArticleVedio")->getUsers($data['author']);
            if(empty($tid)){
                //没有这个用户，新加
                $data['time'] = time();
                $data['status'] = 1;
                $i = D("ArticleVedio")->editUser(0,$data);
            }else{
                //有这个用户，编辑
                $i = D("ArticleVedio")->editUser($tid,$data);
            }

            if ($i !== false) {
                $this->ajaxReturn(array("status"=>1));
            }
            $this->ajaxReturn(array("info"=>"操作失败,请重试！","status"=>0));
        }
        //读取讲师
        $keyword = I('get.keyword');
        $list = D("ArticleVedio")->getUsers($keyword);
        $i = 1;
        foreach ($list as $k => $v) {
            $list[$k]['count'] = $i;
            $list[$k]['url'] = 'http://'.OP("QINIU_DOMAIN")."/".$v["logo"];
            $list[$k]['time'] = date('Y-m-d',$v['time']);
            $i++;
        }
        $this->assign('list',$list);
        $this->assign('keyword',$keyword);
        $this->display();
    }

    /**
     * 删除视频讲师
     */
    public function delUser()
    {
        if ($_POST) {
            $id = I("post.id");

            $data = array(
                "status" => 0
            );
            $i = D("ArticleVedio")->editUser($id,$data);
            if ($i !== false) {
                $this->ajaxReturn(array("status"=>1));
            }
            $this->ajaxReturn(array("info"=>"操作失败,请重试！","status"=>0));
        }
    }

    //获取上级分类id
    public function getBigCategoryId($array,$value){
        foreach($array  as $k => $item) {
            if(!is_array($item)) {
                if ($item == $value) {
                    return $k;
                } else {
                    continue;
                }
            }
            if(in_array($value, $item)) {
                return $k;
            } else if($this->getBigCategoryId($value, $item)) {
                return $k;
            }
        }
        return false;
    }
}
