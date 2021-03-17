<?php

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
use Home\Model\Db\SubTagModel;

/**
*  文章专题
*/
class SpecialarticleController extends HomeBaseController
{

    public function index()
    {
        if (I("get.title") !== "") {
            $title = I("get.title");
            $this->assign('title',$title);
        }

        //获取专题模块列表
        $result = $this->getSpeciAlarticleModuleList($title);
        $this->assign("list",$result["list"]);
        $this->assign("page",$result["page"]);
        $this->display();
    }

    /**
     * 设置专题
     * @return [type] [description]
     */
    public function specialup()
    {
        if ($_POST) {
            $id = I("post.id");
            $status = 0;
            $model = D("ArticleSpecialModule");

            //添加新的专题信息
            $data = array(
                "id" => I("post.id"),
                "title" => I("post.title"),
                "description" => I("post.description"),
                "type" => I("post.type"),
                "istop" => I("post.istop"),
                "isbanner" => I("post.isbanner"),
                "cover_img" => I("post.img"),
                "cover_bigimg" => I("post.bigImg"),
                "uid" => $this->User["id"],
                "uname" => $this->User["name"]
            );

            if ($model->create($data,1)) {
                if (!empty($id)) {
                    //修改专题
                    $model->editModule($id,$data);
                } else {
                    //新增专题
                    $data["time"] = time();
                    $id = $i = $model->addModule($data);
                }

                if ($i !== false) {
                    //删除原有的专题信息
                    $model->delModuleClass($id);
                    $status = 1;

                    //添加美图模块
                    $meitu = json_decode(htmlspecialchars_decode(I("post.meitu")),true);
                    if (!empty($meitu)) {
                        //如果没有填写模块标题，不做任何操作
                        if (!empty($meitu["title"])) {
                            if (count($meitu["child"]) > 0) {
                                foreach ($meitu["child"] as $key => $value) {
                                    $all[] = array(
                                        "module" => $id,
                                        "type" => $meitu["type"],
                                        "title" => $meitu["title"],
                                        "subtitle" => $value["title"],
                                        "article_id" => $value["id"],
                                        "sort" => $value["sort"],
                                        "uid" => $this->User["id"],
                                        "uname" => $this->User["name"],
                                        "time" => time()
                                    );
                                }
                                $model->addModuleClass($all);
                                unset($all);
                            }
                        }
                    }

                    //添加文章
                    $artile = json_decode(htmlspecialchars_decode(I("post.article")),true);
                    if (!empty($artile)) {
                        //添加文章模块1
                        //如果没有填写模块标题，不做任何操作
                        if (!empty($artile[1]["title"])) {
                            if (count($artile[1]["child"]) > 0) {
                                foreach ($artile[1]["child"] as $key => $value) {
                                    $all[] = array(
                                        "module" => $id,
                                        "type" => $artile[1]["type"],
                                        "title" => $artile[1]["title"],
                                        "subtitle" => $value["title"],
                                        "article_id" => $value["id"],
                                        "sort" => $value["sort"],
                                        "uid" => $this->User["id"],
                                        "uname" => $this->User["name"],
                                        "time" => time()
                                    );
                                }
                            }
                        }

                        //添加文章模块2
                        //如果没有填写模块标题，不做任何操作
                        if (!empty($artile[2]["title"])) {
                            if (count($artile[2]["child"]) > 0) {
                                foreach ($artile[2]["child"] as $key => $value) {
                                    $all[] = array(
                                        "module" => $id,
                                        "type" => $artile[2]["type"],
                                        "title" => $artile[2]["title"],
                                        "subtitle" => $value["title"],
                                        "article_id" => $value["id"],
                                        "sort" => $value["sort"],
                                        "uid" => $this->User["id"],
                                        "uname" => $this->User["name"],
                                        "time" => time()
                                    );
                                }
                            }
                        }

                        //添加文章模块3
                        //如果没有填写模块标题，不做任何操作
                        if (!empty($artile[3]["title"])) {
                            if (count($artile[3]["child"]) > 0) {
                                foreach ($artile[3]["child"] as $key => $value) {
                                    $all[] = array(
                                        "module" => $id,
                                        "type" => $artile[3]["type"],
                                        "title" => $artile[3]["title"],
                                        "subtitle" => $value["title"],
                                        "article_id" => $value["id"],
                                        "sort" => $value["sort"],
                                        "uid" => $this->User["id"],
                                        "uname" => $this->User["name"],
                                        "time" => time()
                                    );
                                }
                            }
                        }

                        if (count($all) > 0) {
                            $model->addModuleClass($all);
                            unset($all);
                        }
                    }

                    //添加问答模块
                    $ask = json_decode(htmlspecialchars_decode(I("post.ask")),true);
                    if (!empty($ask)) {
                        //添加问答模块
                        //如果没有填写模块标题，不做任何操作
                        if (!empty($ask["title"])) {
                            if (count($ask["child"]) > 0) {
                                foreach ($ask["child"] as $key => $value) {
                                    $all[] = array(
                                        "module" => $id,
                                        "type" => $ask["type"],
                                        "title" => $ask["title"],
                                        "subtitle" => $value["title"],
                                        "article_id" => $value["id"],
                                        "sort" => $value["sort"],
                                        "uid" => $this->User["id"],
                                        "uname" => $this->User["name"],
                                        "time" => time()
                                    );
                                }
                                $model->addModuleClass($all);
                                unset($all);
                            }
                        }
                    }

                    //添加视频模块
                    $video = json_decode(htmlspecialchars_decode(I("post.video")),true);
                    if (!empty($video)) {
                        //添加问答模块
                        //如果没有填写模块标题，不做任何操作
                        if (!empty($video["title"])) {
                            if (count($video["child"]) > 0) {
                                foreach ($video["child"] as $key => $value) {
                                    $all[] = array(
                                        "module" => $id,
                                        "type" => $video["type"],
                                        "title" => $video["title"],
                                        "subtitle" => $value["title"],
                                        "article_id" => $value["id"],
                                        "sort" => $value["sort"],
                                        "uid" => $this->User["id"],
                                        "uname" => $this->User["name"],
                                        "time" => time()
                                    );
                                }
                                $model->addModuleClass($all);
                                unset($all);
                            }
                        }
                    }
                }
            } else {
                $msg = $model->getError();
            }
            $this->ajaxReturn(array("info"=>$msg,"status"=>$status));
        } else {
            if (I("get.id") !== "") {
                $result = D("ArticleSpecialModule")->findArticleModuleInfo(I("get.id"));
                foreach ($result as $key => $value) {
                    if (!isset($info)) {
                        $info["id"] = $value["id"];
                        $info["title"] = $value["title"];
                        $info["description"] = $value["description"];
                        $info["type"] = $value["type"];
                        $info["istop"] = $value["istop"];
                        $info["isbanner"] = $value["isbanner"];
                        $info["cover_img"] = $value["cover_img"];
                        $info["cover_bigimg"] = $value["cover_bigimg"];
                        if (!empty($value["cover_img"])) {
                            $imgs = array('<img src="//'.OP("QINIU_DOMAIN")."/".$value["cover_img"].'"/>');
                            $info["img"] = $imgs;
                        }

                        if (!empty($value["cover_bigimg"])) {
                            $imgs = array('<img src="//'.OP("QINIU_DOMAIN")."/".$value["cover_bigimg"].'"/>');
                            $info["bigImg"] =  $imgs;
                        }
                    }
                    switch ($value["subtype"]) {
                        case 'meitu':
                            if (!isset($info["meitu"])) {
                                $info["meitu"]["title"] = $value["subtitle"];
                            }
                            $info["meitu"]["child"][] = array(
                                "id" => $value["article_id"],
                                "title" => $value["article_title"],
                                "url" => "http://meitu.".C("QZ_YUMING")."/p".$value["article_id"].".html"
                            );
                            break;
                        case 'article_1':
                            if (!isset($info["article_1"])) {
                                $info["article_1"]["title"] = $value["subtitle"];
                            }
                            $info["article_1"]["child"][] = array(
                                "id" => $value["article_id"],
                                "title" => $value["article_title"]
                            );
                            break;
                        case 'article_2':
                            if (!isset($info["article_2"])) {
                                $info["article_2"]["title"] = $value["subtitle"];
                            }
                            $info["article_2"]["child"][] = array(
                                "id" => $value["article_id"],
                                "title" => $value["article_title"]
                            );
                            break;
                        case 'article_3':
                            if (!isset($info["article_3"])) {
                                $info["article_3"]["title"] = $value["subtitle"];
                            }
                            $info["article_3"]["child"][] = array(
                                "id" => $value["article_id"],
                                "title" => $value["article_title"]
                            );
                            break;
                        case 'ask':
                            if (!isset($info["ask"])) {
                                $info["ask"]["title"] = $value["subtitle"];
                            }
                            $info["ask"]["child"][] = array(
                                "id" => $value["article_id"],
                                "title" => $value["article_title"]
                            );
                            break;
                        case 'video':
                            if (!isset($info["video"])) {
                                $info["video"]["title"] = $value["subtitle"];
                            }
                            $info["video"]["child"][] = array(
                                "id" => $value["article_id"],
                                "title" => $value["article_title"],
                                "url" => "https://www.qizuang.com/video/v".$value["article_id"].".html"
                            );
                            break;
                    }
                }
                $this->assign("info",$info);
            }

            //获取专题分类
            $class = D("ArticleSpecialClass")->getAtricleClassList();
            $this->assign("class",$class);
            $this->display();
        }
    }


    public function delspecialmodule()
    {
        if ($_POST) {
            $id = I("post.id");
            $data = array(
                "isdelete" => I("post.val")
            );
            $i = D("ArticleSpecialModule")->editModule($id,$data);
            if ($i !== false) {
                $this->ajaxReturn(array("status"=>1));
            }
            $this->ajaxReturn(array("info"=>'操作失败,请联系技术部！',"status"=>0));
        }
    }

    /**
     * 专题文章的分类
     * @return [type] [description]
     */
    public function specialarticleclass()
    {
        //获取所有的专题文章分类
        $list = $this->getAtricleClassList();
        $this->assign("list",$list);
        $this->display();
    }

    /**
     * 获取分类编辑模板
     * @return [type] [description]
     */
    public function classinfo()
    {
        if ($_POST) {
            $id = I("post.id");
            $data = array(
                "classname" => I("post.name"),
                "shortname" => I("post.shortname"),
                "title" => I("post.title"),
                "description" => I("post.description"),
                "keywords" => I("post.keywords"),
                "px" => I("post.px"),
                "uid" =>  $this->User["id"] ,
                "uname" =>  $this->User["name"],
                "time" => time()
            );
            $model = D("ArticleSpecialClass");
            $status = 0;
            if ($model->create($data,1)) {
                if (!empty($id)) {
                    $i = $model->editClass($id,$data);
                } else {
                    $data["level"] = I("post.level")+1;
                    $data["parentid"] = I("post.parentid");
                    $i = $model->addClass($data);
                }

                if ($i !== false) {
                    $status = 1;
                }
            }else{
                $msg = $model->getError();
            }
            $this->ajaxReturn(array("info"=>$msg, "status"=>$status));
        } else {
            if (I("get.id") !== "") {
                $info = D("ArticleSpecialClass")->findClassInfo(I("get.id"));
                $this->assign("info",$info);
            }

            if (I("get.level") !== "") {
                $this->assign("level",I("get.level"));
            }

            if (I("get.parentid") !== "") {
                $this->assign("parentid",I("get.parentid"));
            }

            $tmp = $this->fetch("specialclasstmp");
            $this->ajaxReturn(array("data" => $tmp,"status" => 1));
        }
    }

    /**
     * 专题文章列表
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function articles()
    {
        if (I("get.title") !== "") {
            $title = I("get.title");
            $this->assign("title",$title);
        }

        if (I("get.status") !== "") {
            $status = I("get.status");
            $this->assign("status",$status);
        }

        $list = $this->getArticleList($title,$status);
        $this->assign("list",$list);
        $this->display();
    }

    /**
     * 添加/维护 文章
     * @return [type] [description]
     */
    public function articleup()
    {
        if ($_POST) {
            $id = I("post.id");
            $keywords = str_replace(array("、",""),",",I("post.keywords"));
            $data = array(
                "title" => I("post.title"),
                "content" => htmlspecialchars_decode(I("post.content")),
                "type" => I("post.type"),
                "keywords" => $keywords,
                "description" => I("post.description"),
                "cover_img" => I("post.img"),
                "uid" => $this->User["id"],
                "uname" => $this->User["name"]
            );

            $model = D("ArticleSpecial");
            $status = 0;
            if ($model->create($data,1)) {
                if (!empty($id)) {
                    $i = $model->editArticle($id,$data);
                } else {
                    $data["time"] = time();
                    $id = $i = $model->addArticle($data);
                    //添加内链
                    //抽出所有的图片
                    $pattern ='/<img.*?\/>/i';
                    preg_match_all($pattern, $data['content'], $matches);
                    if(count($matches[0]) > 0){
                        foreach ($matches[0] as $key => $value) {
                            //将图片替换成变量占位符
                            $data['content'] = str_replace($value, "%s", $data['content']);
                            $replaceImg[] = $value;
                        }
                    }

                    $keywords = D("WwwArticleKeywords")->getAllKeywords(1);
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
                }

                if ($i !== false) {
                    $status = 1;
                    $tags = I("post.tags");
                    //删除原有的标签
                    $model->delTag($id);

                    //添加标签
                    foreach ($tags as $key => $value) {
                        $subData[] = array(
                            "article_id" => $id,
                            "tag_id" => $value
                        );
                    }

                    if (count($subData) > 0) {
                        $model->addAllTag($subData);
                    }

                    //添加关键字到关联表中
                    if (count($keywordsList) > 0) {
                        foreach ($keywordsList as $key => $value) {
                            $relateData[] = array(
                                "article_id" => $id,
                                "keyword_id" => $value,
                                "module" => "special"
                            );
                        }
                        D("WwwArticleKeywords")->addRelateAll($relateData);
                    }
                }
            } else {
                $msg = $model->getError();
            }
            $this->ajaxReturn(array("info"=>$msg, "status"=>$status));
        } else {
            if (I("get.id") !== "") {
                $info = D("ArticleSpecial")->findArticleInfo(I("get.id"));
                if (count($info) == 0) {
                    $this->_error("该文章不存在");
                }
                $imgs = array('<img src="'.$info["cover_img"].'"/>');
                $json = json_encode($imgs);
                $info["img"] =  $json;

                $tags = array_filter(explode(",", $info["tags"]));
                $tagnames = array_filter(explode(",", $info["tagnames"]));
                foreach ($tags as $key => $value) {
                    $info["child"][] = array(
                            "id" => $value,
                            "text" => $tagnames[$key]
                    );
                }
                $this->assign("info",$info);
            }

            //获取专题分类
            $class = D("ArticleSpecialClass")->getAtricleClassList();
            $this->assign("class",$class);
            $this->display();
        }
    }


    /**
     * 文章审核
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function audit()
    {
        if ($_POST) {
            $id = I("post.id");
            $data = array(
                "status" => I("post.val")
            );
            $i = D("ArticleSpecial")->editArticle($id,$data);
            if ($i !== false) {
                $this->ajaxReturn(array("status" => 1));
            }
            $this->ajaxReturn(array("info" => '操作失败,请联系技术部！',"status" => 0));
        }
    }

    /**
     * 获取文章信息
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getarticle($value='')
    {
        if ($_POST) {
            $type = I("post.type");
            $query = I("post.condition");
            $list = D("WwwArticle")->getArticleListByTitle($query,$type,10);
            $this->ajaxReturn(array("data" => $list,"status" => 1));
        }
    }

    /**
     * 获取美图列表
     * @return [type] [description]
     */
    public function getmeitu()
    {
        if ($_POST) {
            $query = I("post.condition");
            $list = D("Meitu")->getMeituByTitle($query);
            foreach ($list as $key => $value) {
                $list[$key]["url"] = "http://meitu.".C("QZ_YUMING")."/p".$value["id"].".html";
            }
            $this->ajaxReturn(array("data" => $list,"status" => 1));
        }
    }

    /**
     * 获取问答列表
     * @return [type] [description]
     */
    public function getask()
    {
        if ($_POST) {
            $query = I("post.condition");
            $list = D("Adminask")->getAskByTitle($query);
            $this->ajaxReturn(array("data" => $list,"status" => 1));
        }
    }

    /**
     * 获取视频列表
     * @return [type] [description]
     */
    public function getvideo()
    {
        if ($_POST) {
            $query = I("post.condition");
            $list = D("ArticleVedio")->getVideoByTitle($query);
            foreach ($list as $key => $value) {
                $list[$key]["url"] = "https://www.qizuang.com/video/v".$value["id"].".html";
            }
            $this->ajaxReturn(array("data" => $list,"status" => 1));
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

    //文章标识
    public function findsubtags()
    {



            $query = I("post.query");
            $cs = I("post.cs");
            $result =  (new SubTagModel())->getTagsByName($query,$cs,20);
            return $this->ajaxReturn(array("data"=>$result,"status"=>1));

    }

    private function getAtricleClassList()
    {
        $result = D("ArticleSpecialClass")->getAtricleClassList();
        foreach ($result as $key => $value) {
            if ($value["level"] == 1) {
                $value["child"] = $this->getChild($value["id"],$result);
                $list[] = $value;
            }
        }
        return $list;
    }

    private function getChild($parentid, $node)
    {
        foreach ($node as $key => $value) {
            if ($value["parentid"] == $parentid) {
                $value["child"] = $this->getChild($value["id"],$node);
                $list[] = $value;
            }
        }
        return $list;
    }

    private function getArticleList($content, $status)
    {
        $count = D("ArticleSpecial")->getArticleListCount($content, $status);
        if(count($count) > 0){
            import('Library.Org.Util.Page');
            $p = new \Page($count,10);
            $p->setConfig('header','个申请');
            $p->setConfig('prev', "上一页");
            $p->setConfig('next', '下一页');
            $p->setConfig('theme','%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
            $show = $p->show();
            $list = D("ArticleSpecial")->getArticleList($p->firstRow,$p->listRows,$content, $status);
            return array("page"=>$show,"list"=>$list);
        }
    }

    private function getArticleTagsList($where, $order)
    {
        $count = D('www_article_tags')->getDataCount($where);
        if (count($count) > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count, 10);
            $p->setConfig('header', '个申请');
            $p->setConfig('prev', "上一页");
            $p->setConfig('next', '下一页');
            $p->setConfig('theme', '%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
            $show = $p->show();
            $list = D("www_article_tags")->getData($where, 'a.*,b.name as pname', $p->firstRow, $p->listRows, $order);
            return array("page" => $show, "list" => $list);
        }
    }

    /**
     * 获取专题模块列表
     * @param  [type] $title [description]
     * @return [type]        [description]
     */
    private function getSpeciAlarticleModuleList($title)
    {
        $count = D("ArticleSpecialModule")->getSpeciAlarticleModuleCount($title);

        if(count($count) > 0){
            import('Library.Org.Util.Page');
            $p = new \Page($count,10);
            $p->setConfig('header','个申请');
            $p->setConfig('prev', "上一页");
            $p->setConfig('next', '下一页');
            $p->setConfig('theme','%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
            $show = $p->show();
            $list = D("ArticleSpecialModule")->getSpeciAlarticleModuleList($p->firstRow,$p->listRows,$title);
            return array("page"=>$show,"list"=>$list);
        }
    }


    /**
     * 详情底部标签管理
     *
     * @return array|void
     */
    public function basetags()
    {
        $style = I('get.style');                        //展示页
        $group = I('get.group');                        //标签组ID
        $tagName = I('get.name');                       //标签名称
        $position = I('get.position');                  //展示位置
        $begin = I('get.begin');                        //添加开始时间
        $end = I('get.end', '');                        //添加结束时间

        $where = ['a.p_id' => ['NEQ', 0]];              //列表查询条件

        $logicModel = new \Home\Model\Logic\WwwArticleTagsModel();

        if (!empty($style)) {
            $map['style'] = $where['a.style'] = intval($style) - 1;
            $map['style'] = intval($style) - 1;
            $groupData = $logicModel->getgroup($map);
            $this->assign(['group'=>$groupData]);
        }
        if (!empty($group)) {
            $where['a.p_id'] = $group;
        }
        if (!empty($tagName)) {
            $where['_complex'] = [
                '_logic' => 'OR',
                'a.name' => ['like', '%' . $tagName . '%'],
                'a.url' => ['like', '%' . $tagName . '%']
            ];
        }
        if (!empty($position)) {
            $where['a.position'] = $position;
        }
        if (!empty($begin) && !empty($end)) {
            $where['a.addtime'] = ['between', [strtotime($begin), strtotime($end.' 23:59:59')]];
        }
        $pageSize = empty(cookie('pagezise')) ? 10 : intval(cookie('pagezise'));

        //标签数据
        $c_data = $logicModel->getArticleTagsList($where, 'a.addtime desc,a.id desc', $pageSize);
        $this->assign(['cdata' => $c_data['list']]);
        $this->assign(['page' => $c_data['page']]);

        $this->display();
    }

    /**
     * 详情底部标签编辑
     *
     * @return void
     */
    public function basetagsedit()
    {
        $logicModel = new \Home\Model\Logic\WwwArticleTagsModel();
        if (IS_POST) {
            $post = I('post.');
            if (empty($post['name'])) {
                $this->ajaxReturn(['status' => 0, 'info' => '标签名称不能为空! ']);
            }
            $save = [
                'name' => remove_xss(trim($post['name'])),
                'url' => remove_xss(trim($post['url'])),
                'm_url' => remove_xss(trim($post['m_url'])),
                'opid' => session('uc_userinfo.id'),
                'order' => 0,
                'on' => 1
            ];

            $valiDateMap['a.name'] = ['EQ',$post['name']];
            if (!empty($post['id'])) {
                //判断是否存在数据
                $tagValition = $logicModel->getInfoBtId(['a.id' => $post['id']], 'a.*');

                if ($tagValition) {
                    //判断新名称是否有重复值
                    $valiDateMap['a.id'] = ['NEQ', $post['id']];
                    $valiDateMap['a.style'] = ['EQ', $tagValition['style']];

                    $valiName = $logicModel->getInfoBtId($valiDateMap,'a.*');
                    if(!empty($valiName)){
                        $this->ajaxReturn(['status' => 0, 'info' => '名称已存在! ']);
                    }

                    $save['optime'] = time();
                    $save['style'] = $tagValition['style'];
                    $save['position'] = $tagValition['position'];
                    $save['p_id'] = $tagValition['p_id'];
                    $state = $logicModel->upData(['id' => $post['id']], $save);
                    if ($state) {
                        $this->ajaxReturn(['status' => 1, 'info' => '']);
                    } else {
                        $this->ajaxReturn(['status' => 0, 'info' => '更新失败! ']);
                    }
                } else {
                    $this->ajaxReturn(['status' => 0, 'info' => '数据不存在! ']);
                }
            } else {
                if (empty($post['style'])) {
                    $this->ajaxReturn(['status' => 0, 'info' => '请选择展示页! ']);
                }

                if (empty($post['pid']) || intval($post['pid']) === 0) {
                    $this->ajaxReturn(['status' => 0, 'info' => '请选择标签组! ']);
                }

                $valiParent = $logicModel->getInfoBtId(['a.id' => $post['pid']], 'a.*');
                if (empty($valiParent)) {
                    $this->ajaxReturn(['status' => 0, 'info' => '标签组不存在，请核实后再操作! ']);
                }
                //判断新名称是否有重复值
                $valiDateMap['a.style'] = ['EQ',$post['style'] - 1];
                $tagValition = $logicModel->getInfoBtId($valiDateMap, 'a.*');
                if (!empty($tagValition)) {
                    $this->ajaxReturn(['status' => 0, 'info' => '名称已经存在']);
                }

                $save['addtime'] = $save['optime'] = time();
                $save['style'] = intval($post['style']) - 1;
                $save['p_id'] = $post['pid'];
                $save['position'] = $valiParent['position'];
                $state = $logicModel->addData($save);
                if ($state) {
                    $this->ajaxReturn(['status' => 1, 'info' => '']);
                } else {
                    $this->ajaxReturn(['status' => 0, 'info' => '添加失败! ']);
                }
            }
        }
        //操作状态 1.组添加 /组编辑 2.标签添加 /标签编辑
        $id = I('get.id');

        $data = [];
        if (!empty($id)) {
            $data = $logicModel->getInfoBtId(['a.id' => $id],'a.id,a.`name`,a.url,a.m_url,a.position,b.name pname,b.id pid,a.style');
        }

        $this->assign(['data' => $data]);
        $this->display();
    }

    /**
     * 获取标签组
     *
     * @return void
     */
    public function getgroup()
    {
        $style = $post = I('get.style');
        if (empty($style)) {
            $this->ajaxReturn(['status' => 0, 'info' => '数据错误，刷新重试 ']);
        }
        $logicModel = new \Home\Model\Logic\WwwArticleTagsModel();
        $map['style'] = intval($style) - 1;
        $group = $logicModel->getgroup($map);
        $this->ajaxReturn(['status' => 1, 'info' => '','data'=>$group]);
    }
    /**
     * 详情底部标签删除
     *
     * @return void
     */
    public function deltags()
    {
        if (IS_POST) {
            $delIds = I('post.del_id');
            if (empty($delIds)) {
                $this->ajaxReturn(['status' => 0, 'info' => '参数错误! ']);
            }
            $logicModel = new \Home\Model\Logic\WwwArticleTagsModel();
            $state = $logicModel->delTags($delIds);
            if ($state) {
                $this->ajaxReturn(['status' => 1, 'info' => '']);
            } else {
                $this->ajaxReturn(['status' => 0, 'info' => '删除失败! ']);
            }
        }
        $this->ajaxReturn(['status' => 0, 'info' => '数据错误，刷新重试 ']);
    }

    /**
     * 标签组管理
     *
     * @return void
     */
    public function taggroup()
    {
        $style = I('get.style');                        //展示页
        $position = I('get.position');                  //展示位置
        $begin = I('get.begin');                        //添加开始时间
        $end = I('get.end', '');                        //添加结束时间
        $logicModel = new \Home\Model\Logic\WwwArticleTagsModel();
        $where = [];
        if (!empty($style)) {
            $where['style'] = intval($style) - 1;
        }
        if (!empty($position)) {
            $where['position'] = $position;
        }
        if (!empty($begin) && !empty($end)) {
            $where['addtime'] = ['between', [strtotime($begin), strtotime($end.' 23:59:59')]];
        }
        $group = $logicModel->getTagsGroupList($where,'addtime desc,id desc');
        $this->assign(['group' => $group['list']]);
        $this->assign(['page' => $group['page']]);
        $this->display();
    }

    /**
     * 标签组添加/修改/展示
     *
     * @return void
     */
    public function addtaggroup()
    {
        $logicModel = new \Home\Model\Logic\WwwArticleTagsModel();
        if (IS_POST) {
            $post = I('post.');
            if (empty($post['name'])) {
                $this->ajaxReturn(['status' => 0, 'info' => '标签组名称不能为空! ']);
            }
            $save['name'] = remove_xss(trim($post['name']));
            $save['opid'] = session('uc_userinfo.id');
            $save['url'] = '';
            $save['m_url'] = '';
            $save['p_id'] = 0;

            $valiDateMap['a.name'] = ['EQ', $post['name']];

            if (!empty($post['id'])) {
                //判断是否存在数据
                $tagValition = $logicModel->getInfoBtId(['a.id' => $post['id']], 'a.*');
                if ($tagValition) {
                    //判断新名称是否有重复值
                    $valiDateMap['a.id'] = ['NEQ', $post['id']];
                    $valiDateMap['a.style'] = ['EQ', $tagValition['style']];
                    $hadrepeat = $logicModel->getInfoBtId($valiDateMap, 'a.*');
                    if ($hadrepeat) {
                        $this->ajaxReturn(['status' => 0, 'info' => '名称已存在! ']);
                    }
                    $save['optime'] = time();
                    $save['style'] = $tagValition['style'];
                    $save['position'] = $tagValition['position'];

                    $state = $logicModel->upData(['id' => $post['id']], $save);
                    if ($state) {
                        $this->ajaxReturn(['status' => 1, 'info' => '']);
                    } else {
                        $this->ajaxReturn(['status' => 0, 'info' => '更新失败! ']);
                    }
                } else {
                    $this->ajaxReturn(['status' => 0, 'info' => '数据不存在! ']);
                }
            } else {
                if (empty($post['style']) || intval($post['style']) === 0) {
                    $this->ajaxReturn(['status' => 0, 'info' => '请选择展示页! ']);
                }
                if (empty($post['position']) || intval($post['position']) === 0) {
                    $this->ajaxReturn(['status' => 0, 'info' => '请选择展示位置! ']);
                }

                //判断新名称是否有重复值
                $valiDateMap['a.style'] = ['EQ', $post['style'] - 1];
                $tagValition = $logicModel->getInfoBtId($valiDateMap, 'a.*');
                if (!empty($tagValition)) {
                    $this->ajaxReturn(['status' => 0, 'info' => '名称已经存在']);
                }
                $save['addtime'] = $save['optime'] = time();
                $save['style'] = intval($post['style']) - 1;
                $save['position'] = $post['position'];
                $state = $logicModel->addData($save);
                if ($state) {
                    $this->ajaxReturn(['status' => 1, 'info' => '']);
                } else {
                    $this->ajaxReturn(['status' => 0, 'info' => '添加失败! ']);
                }
            }
        }
        //操作状态 1.组添加 /组编辑 2.标签添加 /标签编辑
        $id = I('get.id');

        $data = [];
        if (!empty($id)) {
            $data = $logicModel->getInfoBtId(['a.id' => $id],'a.*');
        }
        $this->assign(['data' => $data]);
        $this->display();
    }


    /**
     * 详情标签组删除
     *
     * @return void
     */
    public function delgroup()
    {
        if (IS_POST) {
            $delIds = I('post.del_id');
            if (empty($delIds)) {
                $this->ajaxReturn(['status' => 0, 'info' => '参数错误! ']);
            }
            $logicModel = new \Home\Model\Logic\WwwArticleTagsModel();
            $list = $logicModel->getList(['a.p_id'=>['in',$delIds]]);
            if (!empty($list)) {
                $this->ajaxReturn(['status' => 0, 'info' => '标签组下标签未删除']);
            }
            $state = $logicModel->delTags($delIds);
            if ($state) {
                $this->ajaxReturn(['status' => 1, 'info' => '']);
            } else {
                $this->ajaxReturn(['status' => 0, 'info' => '删除失败! ']);
            }
        }
        $this->ajaxReturn(['status' => 0, 'info' => '数据错误，刷新重试 ']);
    }
}
