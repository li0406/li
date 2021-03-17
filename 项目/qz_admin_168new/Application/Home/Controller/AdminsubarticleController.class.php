<?php

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
use Home\Model\Db\SubTagModel;

/**
* 分站文章
*/
class AdminsubarticleController extends HomeBaseController
{
    public function index()
    {
        //获取所有分站
        $city = D("Quyu")->getAllQuyuOnly();
        $this->assign("citys",$city);

        if (I("get.title") !== "") {
            $title = I("get.title");
        }

        if (I("get.city") !== "") {
            $citys = I("get.city");
        }

        $id = intval(I('get.id'));
        $state = intval(I('get.state'));

        $pageIndex = 1;
        $pageCount = 20;
        //获取文章列表
        $list = $this->getArticleList($pageIndex,$pageCount,$title,$citys,$id,$state);
        $this->assign("list",$list["list"]);
        $this->assign("page",$list["page"]);
        $this->display();
    }

    /**
     * 编辑/添加文章
     */
    public function articleUp()
    {
        if ($_POST) {
            $id = I("post.id");

            $data = array(
                "id"          => I("post.id"),
                "title"       => I("post.title"),
                "description" => mb_substr(strip_tags(htmlspecialchars_decode(I("post.content"))), 0, 100, 'utf-8'),
                "classid"     => I("post.type"),
                "content"     => htmlspecialchars_decode(I("post.content")),
                "face"        => I("post.img"),
                "cs"          => I("post.city"),
                "pv"          => rand(1000,2000),
                "istop"       => I("post.istop"),
                "is_to_subcompany" => I("post.is_to_subcompany"),
                "authid"      => $this->User["id"],
                "tags"        => implode(",",array_filter(I("post.tags"))),
                "keywords"    => str_replace('，',',',I("post.keywords")),
                "isoriginal"  => I("post.isoriginal"),
                "isxiongzhang"  => I("post.isxiongzhang"),
            );
            $sub_tags = array_filter(I("post.sub_tags"));
            //抽出所有的图片
            $pattern ='/<img.*?\/>/i';
            preg_match_all($pattern, $data['content'], $matches);

            if(count($matches[0]) > 0){
                foreach ($matches[0] as $key => $value) {
                    $pattern = '/src\s*=[\'|\"](.*?)[\'|\"]/';
                    preg_match_all($pattern, $value, $m);
                    //将图片替换成变量占位符
                    $data['content'] = str_replace($value, "%s", $data['content']);
                    if (count($m[1]) > 0) {
                        $replaceImg[] = $m[1][0];
                    }
                }
            }else{ //文章中没有图片时不能操作
                $this->ajaxReturn(array('info'=>'请在文章内容中插入图片','status'=>0));
            }

            //将所有的图片依次填充到原来位置
            if(count($matches[0]) > 0){
                foreach ($matches[0] as $key => $value) {
                    $data['content'] = preg_replace("/\%s/",$value,$data['content'],1);
                }
            }

            $data["img"] = implode(",",$replaceImg);
            $preview_time = I("post.preview-time");
            $status = 0;
            $model = D("LittleArticle");

            if ($model->create($data,1)) {
                $id = I("post.id");
                if (!empty($id)) {
                    unset($data['authid']);
                    //查询文章信息
                    $info = D("LittleArticle")->findArticleInfo($id);
                    //预发布
                    if (3 == $info["state"]) {
                        //预发布时间不为空
                        if (!empty($preview_time)) {
                            $data['addtime'] = strtotime($preview_time);
                            //预发布时间不能小于当前时间
                            if ($data['addtime'] < time()) {
                                $this->ajaxReturn(array('info'=>'预发布时间不能小于当前时间','status'=>0));
                            }
                            $data['state'] = 3;
                        //预发布时间为空
                        } else {
                            $data['addtime'] = time();
                            $data['state'] = 2;
                        }
                    //已发布
                    } else {
                        if (!empty($preview_time)) {
                            $this->ajaxReturn(array('info'=>'只有预发布文章才能填写预发布时间！','status'=>0));
                        }
                    }
                    //编辑文章
                    $i = D("LittleArticle")->editArticle(I("post.id"),$data);
                    if ($i != false) {
                        //更新文章标识
                        $tagData = [];
                        (new SubTagModel())->deleteInfo( I("post.id"));
                        if(!empty($sub_tags)){
                            foreach($sub_tags as $key=>$val){
                                $tagData[$key]['article_id'] =  I("post.id");
                                $tagData[$key]['tag_id'] = $val;
                                $tagData[$key]['created_at'] = time();
                                $tagData[$key]['updated_at'] = time();
                            }
                            (new SubTagModel())->insertInfo($tagData);
                        }


                        //更新标签-初始不可见
                        if (in_array($info['state'], array('-1', '1', '3'))) {
                            //不可见->可见
                            if (in_array($data['state'], array('2'))) {
                                D('Tags')->editTagCountByTagIds('', $data['tags'], 'subarticle_count');
                            }
                        //更新标签-初始可见
                        } else {
                            //可见->不可见
                            if (in_array($data['state'], array('-1', '1', '3'))) {
                                D('Tags')->editTagCountByTagIds($info['tags'], '', 'subarticle_count');
                            } else {
                                //可见->可见
                                D('Tags')->editTagCountByTagIds($info['tags'],$data['tags'],'subarticle_count');
                            }
                        }
                    }
                    $action = 2;
                } else {
                    //新增
                    $data["addtime"] = time();
                    $data["createtime"] = time();
                    //预发布状态默认未审核状态，发布时间为预发布时间
                    if (!empty($preview_time)) {
                        $data['addtime'] = strtotime($preview_time);
                        //预发布时间不能小于当前时间
                        if ($data['addtime'] < time()) {
                            $this->ajaxReturn(array('info'=>'预发布时间不能小于当前时间','status'=>0));
                        }
                        $data['state'] = 3;
                    } else {
                        $data['state'] = 2;
                    }
                    //新增文章
                    $id = $i = $model->addArticle($data);
                    //更新标签
                    if ($i != false) {
                        //添加文章标识
                        $tagData = [];
                        if(!empty($sub_tags)){
                            foreach($sub_tags as $key=>$val){
                                $tagData[$key]['tag_id'] =  $val;
                                $tagData[$key]['article_id'] = $id;
                                $tagData[$key]['created_at'] = time();
                                $tagData[$key]['updated_at'] = time();
                            }

                            ( new SubTagModel())->insertInfo($tagData);
                        }


                        if (in_array($data['state'], array('2'))) {
                            D('Tags')->editTagCountByTagIds('', $data['tags'], 'subarticle_count');
                        }
                    }
                    //内链关键字处理
                    $keywords = D("WwwArticleKeywords")->getAllKeywords(2);
                    shuffle($keywords);
                    foreach ($keywords as $key => $value) {
                        $arr[] = "/".trim($value["name"])."/";
                    }
                    $i = 0;
                    foreach ($arr as $key => $value) {
                        if($i == 8){
                            break;
                        }
                        preg_match_all($value,$data['content'],$matches);
                        if(count($matches[0]) > 0){
                            $keywordsList[] = $keywords[$key]["id"];
                            $i ++;
                        }
                    }
                    $action = 1;

                    //获取城市信息
                    $cityInfo = D("Quyu")->getCityInfoById($data["cs"]);
                    $url = 'https://m.qizuang.com/'.$cityInfo[0]["bm"].'/zxinfo/'.$id.'.html';

                    //推送指百度
                    if ($data["isoriginal"]) {
                        $sent = sentURLToBaidu($url,true);
                    }

                    //推送熊掌号
                    if ($data["isxiongzhang"]) {
                        $xiongzhang = sentURLToXiongZhang($url);
                    }
                }

                if ($i !== false) {
                    $status = 1;
                    //添加关键字到关联表中
                    if (count($keywordsList) > 0) {
                        foreach ($keywordsList as $key => $value) {
                            $relateData[] = array(
                                "article_id" => $id,
                                "keyword_id" => $value,
                                "module"     => "subarticle"
                            );
                        }
                        D("WwwArticleKeywords")->addRelateAll($relateData);
                    }
                    //添加操作日志
                    $log = array(
                        'remark' => '分站文章发布',
                        'logtype' => 'subarticle',
                        'action_id' => $id,
                        'info' => json_encode($data)
                    );
                    D('LogAdmin')->addLog($log);
                    //添加操作日志
                    D('LogAdminEditor')->addLogAdminEditor('20',$action,$retVal);
                }
            } else {
                $msg = $model->getError();
            }
            $this->ajaxReturn(array("info"=>$msg,"status"=>$status));
        } else {
            if (I("get.id") !== "") {
                $info = D("LittleArticle")->findArticleInfoV2(I("get.id"));

                if (count($info) == 0) {
                    $this->_error("该文章不存在");
                }
                
                $prefix = "http://" . C("QINIU_DOMAIN") . "/";
                $count = substr_count($info["face"], C("QINIU_DOMAIN"));
                if ($count > 1 && strpos($info["face"], $prefix) === 0){
                    $info["face"] = str_replace($prefix, "", $info["face"]);
                }

                if (strpos($info["face"],"http://") === false && strpos($info["face"],"https://") === false) {
                    $info["face"] = $prefix . $info["face"];
                }

                $imgs = array('<img src="'.$info["face"].'"/>');
                $json = json_encode($imgs);
                $info["img"] =  $json;

//                $tags = array_filter(explode(",", $info["tags"]));
//                $tagnames = array_filter(explode(",", $info["tagnames"]));
//                foreach ($tags as $key => $value) {
//                    $info["child"][] = array(
//                        "id" => $value,
//                        "text" => $tagnames[$key]
//                    );
//                }


                //559文章标识
                $sub_tags = array_filter(explode(",", $info["sub_tags"]));
                $sub_tagnames = array_filter(explode(",", $info["sub_tagnames"]));
                foreach ($sub_tags as $key => $value) {
                    $info["sub_child"][] = array(
                        "id" => $value,
                        "text" => $sub_tagnames[$key]
                    );
                }

                $this->assign("info",$info);
            }
            //获取所有分站
            $city = D("Quyu")->getAllQuyuOnly();
            $this->assign("citys",$city);
            //获取文章分类
            $type = D("LittleArticle")->getArticleClassList();
            $this->assign("type",$type);
            $this->display();
        }
    }

    /**
     * 更改文章状态
     */
    public function setState()
    {
        if ($_POST) {
            $id = intval(I("post.id"));
            $state = intval(I("post.val"));
            //判断参数
            if (empty($id) || !in_array($state, array('1', '2'))) {
                $this->ajaxReturn(array("info"=>'非法请求！', "status"=>0));
            }
            //查询文章信息
            $info = D("LittleArticle")->findArticleInfo($id);
            if (empty($info)) {
                $this->ajaxReturn(array("info"=>'您操作的文章不存在', "status"=>0));
            }
            $save = array(
                "state" => $state
            );
            //将预发布改为已发布，发布时间改为当前时间
            if (3 == $info['state'] && 2 == $save['state']) {
                $save['addtime'] = time();
            }
            //保存更改
            $result = D("LittleArticle")->editArticle($id, $save);
            if ($result) {
                //更新标签-初始不可见
                if (in_array($info['state'], array('-1', '1', '3'))) {
                    //不可见->可见
                    if (in_array($save['state'], array('2'))) {
                        D('Tags')->editTagCountByTagIds('', $info['tags'], 'subarticle_count');
                    }
                //更新标签-初始可见
                } else {
                    //可见->不可见
                    if (in_array($save['state'], array('-1', '1', '3'))) {
                        D('Tags')->editTagCountByTagIds($info['tags'], '', 'subarticle_count');
                    }
                }
                //添加操作日志
                $log = array(
                    'remark' => '分站文章状态更改',
                    'logtype' => 'subarticle_setstate',
                    'action_id' => $id,
                    'info' => json_encode(array($info, $save))
                );
                D('LogAdmin')->addLog($log);
                $this->ajaxReturn(array("status"=>1));
            }
            $this->ajaxReturn(array("info"=>'操作失败！',"status"=>0));
        }
        $this->ajaxReturn(array("info"=>'非法请求！', "status"=>0));
    }

    /**
     * 推荐文章至装修资讯页操作
     */
    public function recommendArticle()
    {
        if (IS_POST) {
            $istop = intval(I("post.val"));
            $id = intval(I("post.id"));
            //判断参数
            if (empty($id) || !in_array($istop, array(0,1))) {
                $this->ajaxReturn(array("info"=>'非法请求！', "status"=>0));
            }
            //查询文章信息
            $info = D("LittleArticle")->findArticleInfo($id);
            if (empty($info)) {
                $this->ajaxReturn(array("info"=>'您操作的文章不存在', "status"=>0));
            }
            $upResult = D("LittleArticle")->editArticle($id,["istop" =>  $istop]);
            if ($upResult !== false) {
                $this->ajaxReturn(["info"=>'修改成功',"status"=>1]);
            } else {
                $this->ajaxReturn(["info"=>'操作失败',"status"=>0]);
            }
        } else {
            $this->ajaxReturn(["info"=>'操作失败',"status"=>0]);
        }
    }

    /**
     * 推荐至装修公司页操作
     */
    public function recommendToSubArticle()
    {
        if (IS_POST) {
            $istop = intval(I("post.val"));
            $id = intval(I("post.id"));
            //判断参数
            if (empty($id) || !in_array($istop, array(0,1))) {
                $this->ajaxReturn(array("info"=>'非法请求！', "status"=>0));
            }
            //查询文章信息
            $info = D("LittleArticle")->findArticleInfo($id);
            if (empty($info)) {
                $this->ajaxReturn(array("info"=>'您操作的文章不存在', "status"=>0));
            }
            $upResult = D("LittleArticle")->editArticle($id,["is_to_subcompany" =>  $istop]);
            if ($upResult !== false) {
                $this->ajaxReturn(["info"=>'修改成功',"status"=>1]);
            } else {
                $this->ajaxReturn(["info"=>'操作失败',"status"=>0]);
            }
        } else {
            $this->ajaxReturn(["info"=>'操作失败',"status"=>0]);
        }
    }

    /**
     * 获取文章列表
     */
    private function getArticleList($pageIndex,$pageCount,$title,$city,$id,$state=0)
    {
        $count = D("LittleArticle")->getArticleListCount($title,$city,$id,$state);
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count,$pageCount);
            $p->setConfig('header','个申请');
            $p->setConfig('prev', "上一页");
            $p->setConfig('next', '下一页');
            $p->setConfig('theme','%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
            $show    = $p->show();
            $list = D("LittleArticle")->getArticleList($p->firstRow,$p->listRows,$title,$city,$id,$state);
            return array("page"=>$show,"list"=>$list);
        }
    }
}
