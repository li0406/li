<?php

namespace Mobile\Controller;
use Mobile\Common\Controller\MobileBaseController;

/**
* 专题文章
*/
class ArticlespecialController extends MobileBaseController
{
    public function module()
    {
        $id = I("get.id");
        //获取模块信息
        $info = S("Cache:zt:m:module:".$id);
        if (!$info) {
            //获取模块信息
            $info["module"] = $this->getModuleInfo($id);

            // //获取美图模块信息
            $info["meitu"] = $this->getModuleMeituList($id);

            //文章模块1
            $info["article1"] = $this->getModuleArticleList($id,"article_1");

            //文章模块2
            $info["article2"] = $this->getModuleArticleList($id,"article_2");
            //文章模块3
            $info["article3"] = $this->getModuleArticleList($id,"article_3");
            //问答模块
            $info["ask"] = $this->getModuleAskList($id);

            //视频模块
            $info["video"] = $this->getModuleVideoList($id);

            //其他专题
            $info["module"]["other_module"] = $this->getOtherModule($id,3);
            S("Cache:zt:m:module:".$id,$info,900);
        }

        if (count($info["module"]) ==  0) {
           $this->_error();
           die();
        }
        //生成canonical标签属性值
        if(!isset($_GET['a1'])){
            $info['canonical'] = $this->SCHEME_HOST['scheme_full'].C('QZ_YUMINGWWW').$_SERVER['REQUEST_URI'];
        }

        //TDK
        $basic["body"]["title"] = "专题详情";
        $basic["head"]["title"] =  $info["module"]['title']."- 齐装网装修专题";
        $basic["head"]["description"] = $info["module"]['title']+"专题为业主提供".$info["module"]['title']."的详细装修攻略，帮助业主解决".$info["module"]['title']."相关问题，让业主轻松装修不再上当";
        $basic["head"]["keywords"] = $info["module"]['title'];
        $this->assign("basic",$basic);
        $this->assign("info",$info);
        $this->display();
    }

    /**
     * [terminal 专题文章终端页 terminal:终端]
     * @return [type] [description]
     */
    public function terminal()
    {
        $id = I('get.id');
        $class = M('article_special_class')->where(['shortname' => trim(I('get.category'))])->find();
        if(empty($class) || empty($id)){
            $this->_error();
        }
        $info['info'] = D('ArticleSpecial')->getArticleSpecialByIdAndType($id,$class['id']);

        if(empty($info['info'])){
            $this->_error();
        }

        //处理文章中的图片
        $pattern ='/<img.*?\/>/is';
        preg_match_all($pattern,$info['info']['content'], $matches);
        if(count($matches[0]) > 0){
            foreach ($matches[0] as $k => $val) {
                $pattern ='/src=[\"|\'](.*?)[\"|\']/is';
                preg_match_all($pattern,$val, $m);
                foreach ($m[1] as $j => $v) {
                    if(!strpos($v,C('QINIU_DOMAIN'))){
                        $path =$this->SCHEME_HOST['scheme_full'].C('STATIC_HOST1').$v;
                        $info['info']['content']  = str_replace($v,$path,$info['info']['content']);
                    }
                }

                $pattern = '/width\=[\"|\'].*?[\"|\']/is';
                $info['info']['content'] = preg_replace_callback($pattern, function(){
                    return "";
                }, $info['info']['content']);

                $pattern = '/height\=[\"|\'].*?[\"|\']/is';
                $info['info']['content'] = preg_replace_callback($pattern, function(){
                    return "";
                }, $info['info']['content']);
            }

            foreach ($matches[0] as $k => $val) {
                $pattern ='/src=[\"|\'](.*?)[\"|\']/is';
                preg_match_all($pattern,$val, $m);
                foreach ($m[1] as $j => $v) {
                    if(!strpos($v,C('QINIU_DOMAIN'))){
                        $path = $this->SCHEME_HOST['scheme_full'].C('STATIC_HOST1').$v;
                        $info['info']['content']  = str_replace($v,$path,$info['info']['content']);
                    }
                }

                $pattern = '/width: [\d]+px;/is';
                $info['info']['content'] = preg_replace_callback($pattern, function(){
                    return "";
                }, $info['info']['content']);

                $pattern = '/height: [\d]+px;/is';
                $info['info']['content'] = preg_replace_callback($pattern, function(){
                    return "";
                }, $info['info']['content']);
            }
        }

        //获取推荐文章
        $info["recommend"] = S("Cache:as:recommend");
        if (!$info["recommend"]) {
           $info["recommend"] = D("ArticleSpecial")->getRecommendArticleSpecial($class['id'],5);
           S("Cache:as:recommend",$info["recommend"],3600);
        }

        //更新文章浏览量
        D('ArticleSpecial')->editArticleSpecialPvById($id);

        //TDK
        $basic['head']['title'] = $info['info']['title'].'-齐装网';
        $basic['head']['keywords'] = $info['info']['keywords'];
        $basic['head']['description'] = $info['info']['description'];

        $basic['body']['title'] = '装修攻略';

        $this->assign('info',$info);
        $this->assign('basic',$basic);
        $this->display();
    }

    /**
     * [likeAction AJAX请求喜欢]
     * @return [type] [description]
     */
    public function likeAction()
    {
        $id = I('post.id');
        if(!empty($id)){
            $result = D('ArticleSpecial')->editArticleSpecialLikesById($id);
            if($result){
                $this->ajaxReturn(array('data'=>'','info'=>'操作成功','status'=>1));
            }
        }
        $this->ajaxReturn(array('data'=>'','info'=>'操作失败','status'=>0));
    }

    /**
     * 获取其他模块信息
     * @param  [type] $id    [模块ID]
     * @param  [type] $limit [偏移量]
     * @return [type]        [description]
     */
    private function getOtherModule($id,$limit)
    {
        $result = D("ArticleSpecial")->getOtherModule($id,$limit);
        return $result;
    }

        /**
     * 获取美图信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    private function getModuleMeituList($id)
    {
        $result = D("ArticleSpecialModule")->getModuleMeituList($id);

        foreach ($result as $key => $value) {
            if (!array_key_exists($value["type"],$list)) {
                $list["title"] = $value["title"];
            }
            $list["child"][] = $value;
        }
        return $list;
    }

     /**
     * 获取美图信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    private function getModuleArticleList($id,$type)
    {
        $result = D("ArticleSpecialModule")->getModuleArticleList($id,$type);
        foreach ($result as $key => $value) {
            if (!array_key_exists($value["type"],$list)) {
                $list["title"] = $value["title"];
            }
            $value["id"] = $value["article_id"];
            $list["child"][] = $value;
        }
        return $list;
    }

    /**
     * 获取问答信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    private function getModuleAskList($id)
    {
        import('Library.Org.Util.Fiftercontact');
        $filter = new \Fiftercontact();
        $result = D("ArticleSpecialModule")->getModuleAskList($id);
        foreach ($result as $key => $value) {
            if (!array_key_exists($value["type"],$list)) {
                $list["title"] = $value["title"];
            }
            $value["content"] = strip_tags($filter->filter_empty($value["content"]));
            $list["child"][] = $value;
        }
        return $list;
    }

    /**
     * 获取视频模块信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    private function getModuleVideoList($id)
    {
        $result = D("ArticleSpecialModule")->getModuleVideoList($id);
        foreach ($result as $key => $value) {
            if (!array_key_exists($value["type"],$list)) {
                $list["title"] = $value["title"];
            }
            $list["child"][] = $value;
        }
        return $list;
    }

    private function getModuleInfo($id)
    {

        $result = D("ArticleSpecial")->getModuleInfo($id);
        // foreach ($result as $key => $value) {
        //     if ($key == 0) {
        //         $list = array(
        //             "title" => $value["title"],
        //             "description" => $value["description"],
        //             "img" =>$value["cover_bigimg"]
        //         );
        //     }

        //     if (!array_key_exists($value["item_title"],$list["item"])) {
        //         $list["item"][$value["item_title"]]["name"] = $value["item_title"];
        //     }
        //     $list["item"][$value["item_title"]]["articles"][]= array(
        //                             "id" => $value["article_id"],
        //                             "title" => $value["article_title"],
        //                             "shortname" => $value["shortname"],
        //                             "description" => $value["article_description"],
        //                             "thumb" => $value["thumb"]
        //     );
        // }

        return $result;
    }
}