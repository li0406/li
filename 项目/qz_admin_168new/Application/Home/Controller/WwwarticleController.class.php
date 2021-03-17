<?php
namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\AdminuserLogicModel;
use Home\Model\Logic\ThematicWordsLogicModel;
use Home\Model\Logic\WwwArticleLogicModel;
use Home\Model\Service\ElasticsearchServiceModel;

class WwwarticleController extends HomeBaseController{

    //是否直接发布
    private $directPost = false;

    /**
     * 文章列表
     */
    public function index()
    {
        $map = array();
        $data = I('get.');
        $pageIndex = 1;
        if(!empty($data['p'])){
            $pageIndex = $data['p'];
        }
        $cid = '';
        if(!empty($data['onelevel']) && $data['onelevel'] != 'null'){
            $cid = $data['onelevel'];
        }
        if(!empty($data['twolevel']) && $data['twolevel'] != 'null'){
            $cid = $data['twolevel'];
        }
        if(!empty($data['threelevel']) && $data['threelevel'] != 'null'){
            $cid = $data['threelevel'];
        }

        // 按分类查询
        if (!empty($cid)) {
            $arr= D('WwwArticleClass')->getArticleClassIdsByClass($cid);
            $id = array();
            foreach ($arr as $row){
                $id[] = $row['id'];
            }
            if(!empty($id)){
                $map['class_id']= array('IN', $id);
            }else{
                //文章分类为空
                $map['class_id']= array('EQ', '');
            }
        }

        // 查询是否推荐
        if ($data['recommend'] != "") {
            $map['a.recommend'] = $data['recommend'] == 1 ? 1 : 0;
        }

        //发布时间范围
        if (!empty($data['addtimeStart'])) {
            $map['a.addtime'][] = array('EGT', strtotime($data['addtimeStart']));
        }
        if (!empty($data['addtimeEnd'])) {
            $map['a.addtime'][] = array('LT', strtotime($data['addtimeEnd']) + 86400);
        }

        // 查询词
        if (!empty($data['condition'])) {
            if (ctype_digit((string)$data['condition'])) {
                $map['a.id'] = ['EQ', intval($data['condition'])];
            } else {
                $map['a.title'] = ['LIKE', '%' . $data['condition'] . '%'];
            }
        }
        if (!empty($data['item'])) {
            $map['a.item_name'] = ['LIKE', '%'.$data['item'].'%'];
        }
        //未审核
        if (!empty($data['state']) && in_array($data['state'], ['-1', '1', '2', '3'])) {
            $map['a.state'] = $data['state'];
        }

        //角色
        if (!empty($data['role_id']) && in_array($data['role_id'], ['24', '69', '151'])) {
            $map['u.uid'] = $data['role_id'];
        }

        //用户id
        if (!empty($data['editer_id'])) {
            $map['a.opid'] = $data['editer_id'];
        }

        $info['info'] = $this->getWwwArticleList($map,$pageIndex);
        $info['articleclass'] = json_encode(D('WwwArticleClass')->getArticleClassTree(false));
        //获取全部定制的分类
        $result =  D('WwwArticleClass')->getArticleClassIdsByClass(418);

        //根据角色id获取改角色的所有用户
        $rolelist = [];
        if (!empty($data['role_id']) && in_array($data['role_id'], ['24', '69', '151'])) {
            $adminUserLogic = new AdminuserLogicModel();
            $rolelist = $adminUserLogic->getAdminUserByUid($data["role_id"]);
        }
        $this->assign("role_list",$rolelist);
        $this->assign("getdata",$data);

        $qwdz = array_column($result,"id");
        $info['qwdz'] = $qwdz;
        $this->assign('info',$info);
        $this->display();
    }

    /**
     * 新增与编辑文章
     */
    public function operate()
    {
        //文章新增编辑
        $data = I('post.');
        if(!empty($data)){
            $admin = getAdminUser();
            $save['tags'] = D('Tags')->getTagIdsByTagNames($data['tagnames']);
            $save['title'] = $data['title'];
            $save['item_name'] = $data['item_name'];
            $save['face'] = $data['face'];
            $save['keywords'] = str_replace('，',',',$data['keywords']);
            $save['subtitle'] = $data['subtitle'];
            $save['isTop'] = $data['istop'];
            $save['recommend'] = $data['recommend'];
            $save['opid'] = $admin['id'];
            $save['content'] = htmlspecialchars_decode($data['content']);
            $save['isoriginal'] = $data['isoriginal'] ?? 0;
            $save['isxiongzhang'] = $data['isxiongzhang'] ?? 0;
            $save['short_introduction'] = $data['article-intro'];
            $save['is_wenda'] = $data['is_wenda'];
            $save['is_kejiwenda'] = $data['is_kejiwenda'];
            $save['brand_model'] = $data['brand_model'];
            $save['app_version'] = $data['app_version'];
            $save['system_version'] = $data['system_version'];

            $id = $data['id'];
            $class = $data['classid'];

            //点击的是否是直接发布
            $directPost = $this->directPost;

            $wwwArticleLogic = new \Home\Model\Logic\WwwArticleLogicModel();
            if (($valiMessage = $wwwArticleLogic->articleValidate($data)) !== true) {
                $this->ajaxReturn(['info' => $valiMessage, 'status' => 0]);
            }

            //将内容的前100个字作为描述
            $save['subtitle'] = mb_substr(strip_tags($save['content']), 0, 100, 'utf-8');

            // 处理关键字
            $save['keywords']  = implode(' ', array_unique(preg_split('/\s+/', trim($save['keywords']))));

            //处理文章中的图片
            $pattern="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png])?)[\'|\"].*?[\/]?>/";
            //$pattern ='/<img\s*(alt=\"\")?\s*(src\s*=((\'|\"))(.*?)(\'|\"))?/is';
            preg_match_all($pattern, $save['content'], $matches);
            $imgs = [];
            if ($matches !== false) {
                foreach ($matches[1] as $key => $value) {
                    $imgs[] = urldecode($value);
                }
            }
            //请在文章内容中插入图片
            if (empty($imgs)) {
                $this->ajaxReturn(['info' => '请在文章内容中插入图片！', 'status' => 0]);
            }

            $save['imgs'] = implode(',',$imgs);

            //判断是否有封面图，没有封面图默认取文章中的第一个图片
            if(empty($save['face'])){
                //替换掉域名和图片后缀
                $replace = ['https://'.C('QINIU_DOMAIN').'/','-s3.jpg'];
                $save['face'] = str_replace($replace, '', explode(',', $save['imgs'])[0]);
            }

            //新增文章
            if(empty($id)){
                //判断新增的文章是否列表页推荐，如果列表页推荐则判断数量是否小于10个
                if (1 == $save['recommend']) {
                    $recommend_list = $this->checkRecommendArticleCount($class);
                    if ($recommend_list !== true) {
                        $this->assign('recommend_list', $recommend_list);
                        $recommend_modal = $this->fetch('recommend_modal');
                        $this->ajaxReturn(array('data'=>$recommend_modal,'info'=>'recommend','status'=>0));
                        exit();
                    }
                }

                $save['userid'] = $admin['id'];
                $save['optime'] = time();
                $save['pv'] = rand(1000,2000);
                $save['likes'] = rand(500,800);
                $save['createtime'] = time();

                //预发布状态默认未审核状态，发布时间为预发布时间
                if (true == $directPost) {
                    if (!empty($data["preview-time"])) {
                        $this->ajaxReturn(array('status'=>0, 'info'=>'直接发布，预发布时间需为空'));
                    }
                    $save['addtime'] = time();
                    $save['state'] = 2;
                } else {
                    if (!empty($data["preview-time"])) {
                        //预发布时间为填写的预发布时间当天的最后一秒的时间戳
                        $save['addtime'] = strtotime(date('Y-m-d', strtotime($data["preview-time"]))) + 86400 - 1;
                        if ($save['addtime'] < time()) {
                            $this->ajaxReturn(array('status'=>0, 'info'=>'预发布时间不能小于当前时间'));
                        }
                    } else {
                        $save['addtime'] = strtotime('+1 day');
                    }
                    $save['state'] = 3;
                }

                //记录初始状态
                $save['init_state'] = $save['state'];

                //新增操作
                $id = D('WwwArticle')->addWwwArticle($class, $save);

                if($id){
                    /**S--处理文章中的关键字(内链)--**/
                    //1-抽出所有的图片
                    $pattern ='/<img.*?\/>/i';
                    preg_match_all($pattern, $save['content'], $matches);
                    if(count($matches[0]) > 0){
                        foreach ($matches[0] as $key => $value) {
                            //将图片替换成变量占位符
                            $save['content'] = str_replace($value, "##!&&", $save['content']);
                            $replaceImg[] = $value;
                        }
                    }
                    //2-处理文章的关键字
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
                        preg_match_all($value,$save["content"],$matches);
                        if(count($matches[0]) > 0){
                            $keywordsList[] = $keywords[$key]["id"];
                            $i ++;
                        }
                    }
                    //3-将所有的图片依次填充到原来位置
                    foreach ($replaceImg as $key => $value) {
                        $save['content'] = preg_replace("/##!&&/",$value,$save['content'],1);
                    }
                    //4-添加关键字到关联表中
                    if (count($keywordsList) > 0) {
                        foreach ($keywordsList as $key => $value) {
                            $subData[] = array(
                                "article_id" => $id,
                                "keyword_id" => $value,
                                "module" => "wwwarticle"
                            );
                        }
                        M("keyword_relate")->addAll($subData);
                    }
                    /**E--处理文章中的关键字(内链)--**/

                    //添加标签关联
                    $service = new ElasticsearchServiceModel();
                    $result = $service->getContentLabel($save['title'],1);

                    if (count($result) > 0) {
                        foreach ($result as $item) {
                            $allLabel[] = [
                                "thematic_id" => $item["id"],
                                "content_id" => $id,
                                "module" => 1
                            ];
                        }

                        $thematicLogic = new ThematicWordsLogicModel();
                        $thematicLogic->addThematicRelated($allLabel);
                    }

                    //如果新增的文章前台可见，则进行推送
                    if (in_array($save['state'], array('2'))) {
                        D('Tags')->editTagCountByTagIds('',$save['tags'],'article_count');

                        //主站文章添加主动推送 2017/07/19
                        $classname = D('WwwArticleClass')->getArticleClassIdsByClass($class);
                        $url = 'https://m.qizuang.com/gonglue/'.$classname[0]['shortname'].'/'.$id.'.html';
                        //根据计数器，添加主动推送(不是预发布状态下)
                        $end_time = strtotime(date('Y-m-d',time()).' 23:59:59');
                        $now_time = time();
                        $cache_time = $end_time - $now_time;
                        $num = returnOriginal($num,false);//查询
                        if($num < 50 && $data['isoriginal'] == 1){
                            //小于50篇且 $data['isoriginal'] = 1 ，推送为原创
                            $sent = sentURLToBaidu($url,true);
                            $num = returnOriginal($num,true);//第二个参数为true时，是数量+1
                            //将返回值写入数据表 qz_www_article_linksubmit
                            $sent = json_decode($sent,true);
                            S('Cache:sent:wwwarticle:original',$sent['remain_original'],$cache_time);//原创剩余数量
                            S('Cache:sent:wwwarticle:normal',$sent['remain'],$cache_time);//主动剩余数量
                            $data = [
                                'aid'       => $id,
                                'url'       => $url,
                                'from'      => 1,//来自主站
                                'type'      => 1,//原创
                                's_code'    => $sent['success_original'],
                                'time'      => time()
                            ];
                            D("WwwArticleKeywords")->addLinkSubmit($data);
                        }else{
                            //大于50篇，仅主动推送
                            $sent = sentURLToBaidu($url,false);
                            //将返回值写入数据表 qz_www_article_linksubmit
                            $sent = json_decode($sent,true);
                            S('Cache:sent:wwwarticle:normal',$sent['remain'],$cache_time);//主动剩余数量
                            $data = [
                                'aid'       => $id,
                                'url'       => $url,
                                'from'      => 1,//来自主站
                                'type'      => 2,//非原创
                                's_code'    => $sent['success'],
                                'time'      => time()
                            ];
                            D("WwwArticleKeywords")->addLinkSubmit($data);
                        }

                        //推送熊掌号
                        if ($save['isxiongzhang']) {
                            $xiongzhang = sentURLToXiongZhang($url);
                        }

                        $save["id"] = $id;
                        $save["url"] = $url;
                        $logic = new WwwArticleLogicModel();

                        if ($save['is_wenda'] == 1) {
                            $logic->pushWenda($save);
                        }

                        if ($save['is_kejiwenda'] == 1) {
                            $logic->pushKejiWenda($save);
                        }

                    }

                    //添加操作日志
                    $log = array(
                        'remark' => '主站文章编辑',
                        'logtype' => 'wwwarticle',
                        'action_id' => $id,
                        'info' => json_encode($data)
                    );
                    D('LogAdmin')->addLog($log);

                    //返回成功
                    $this->ajaxReturn(array('data'=>$sent,'info'=>'新增操作成功！','status'=>1));
                }
            }else{
                //判断编辑的文章是否列表页推荐，如果列表页推荐则判断数量是否小于10个
                if (1 == $save['recommend']) {
                    $recommend_list = $this->checkRecommendArticleCount($class, $id);
                    if ($recommend_list !== true) {
                        $this->assign('recommend_list', $recommend_list);
                        $recommend_modal = $this->fetch('recommend_modal');
                        $this->ajaxReturn(array('data'=>$recommend_modal,'info'=>'recommend','status'=>0));
                        exit();
                    }
                }

                //查询文章信息
                $info = D('WwwArticle')->getWwwArticleById($id);

                //记录操作时间
                $save['optime'] = time();

                //点击直接发布
                if (true == $directPost) {
                    //如果之前是预发布状态
                    if (in_array($info["state"], array('3'))){
                        //如果预发布时间不为空
                        if (!empty($data["preview-time"])) {
                            $this->ajaxReturn(array('status'=>0, 'info'=>'直接发布，预发布时间需为空'));
                        } else {
                            $save['addtime'] = time();
                            $save['state'] = 2;
                        }
                    } else {
                        $this->ajaxReturn(array('status'=>0, 'info'=>'只有预发布状态的文章才可以直接发布'));
                    }
                //点击保存
                } else {
                    //如果之前是预发布状态
                    if (in_array($info["state"], array('3'))){
                        //预发布时间不为空
                        if (!empty($data["preview-time"])) {
                            //预发布时间为填写的预发布时间当天的最后一秒的时间戳
                            $save['addtime'] = strtotime(date('Y-m-d', strtotime($data["preview-time"]))) + 86400 - 1;
                            if ($save['addtime'] < time()) {
                                $this->ajaxReturn(array('status'=>0, 'info'=>'预发布时间不能小于当前时间'));
                            }
                        } else {
                            $save['addtime'] = strtotime('+1 day');
                        }
                    } else {
                        if (!empty($data["preview-time"])) {
                            $this->ajaxReturn(array('status'=>0, 'info'=>'已发布的文章的预发布时间必须为空'));
                        }
                    }
                }

                $result = D('WwwArticle')->editWwwArticle($id,$class,$save);
                if($result){
                    //修改标签数量
                    //初始不可见
                    if (in_array($info['state'], array('-1', '1', '3'))) {
                        //不可见->可见
                        if (in_array($save['state'], array('2'))) {
                            D('Tags')->editTagCountByTagIds('', $save['tags'], 'article_count');
                        }
                    //初始可见
                    } else {
                        //可见->不可见
                        if (in_array($save['state'], array('-1', '1', '3'))) {
                            D('Tags')->editTagCountByTagIds($info['tags'], '', 'article_count');
                        } else {
                            //可见->可见
                            D('Tags')->editTagCountByTagIds($info['tags'],$save['tags'],'article_count');
                        }
                    }

                    $logic = new WwwArticleLogicModel();
                    $url = 'https://m.qizuang.com/gonglue/'.$info['shortname'].'/'.$id.'.html';

                    if ($save['is_wenda'] == 1) {
                        $save["id"] = $id;
                        $save["url"] = $url;
                        
                        $logic->pushWenda(array_merge($save, ['addtime' => $info['addtime']]));
                    }

                    if ($save['is_kejiwenda'] == 1) {
                        $save["id"] = $id;
                        $save["url"] = $url;

                        $logic->pushKejiWenda($save);
                    }

                    //添加操作日志
                    $log = array(
                        'remark' => '主站文章发布',
                        'logtype' => 'wwwarticle',
                        'action_id' => $id,
                        'action' => __CONTROLLER__."/".__ACTION__,
                        'info' => json_encode($save),
                        'time' => time(),
                        'ip' => $ip
                    );
                    D('LogAdmin')->addLog($log);
                    $this->ajaxReturn(array('status'=>1, 'info'=>'编辑操作成功！'));
                }
            }
            $this->ajaxReturn(array('status'=>0, 'info'=>'操作失败！'));
        }
        //文章编辑获取文章信息
        $id = I('get.id');
        if(!empty($id)){
            //获取文章的推荐标签
            $info['info'] = D('WwwArticle')->getWwwArticleById($id);
            if(!empty($info['info']['face'])){
                $info['info']['cover'] = "'".'<img src="//'.C('QINIU_DOMAIN').'/'.$info['info']['face'].'">'."'";
            }
        }

        import('Library.Org.Util.Fiftercontact');
        $filter = new \Fiftercontact();
        $info['info']['content'] = $filter->filter_empty($info['info']['content']);
        $info['categorytree'] = json_encode(D('WwwArticleClass')->getArticleClassTree());

        $all_num['original'] = S('Cache:sent:wwwarticle:original');//原创剩余数量
        $all_num['normal'] = S('Cache:sent:wwwarticle:normal');//主动剩余数量
        $this->assign('all_num',$all_num);

        $this->assign('info',$info);
        $this->display();
    }

    /**
     * 直接发布
     */
    public function directPost()
    {
        $this->directPost = true;
        return $this->operate();
    }

    /**
     * 编辑文章是否推荐
     */
    public function editIsTop()
    {
        $id = I('post.id');
        $istop = I('post.istop');
        if(in_array($istop, array('0','1','2'))){
            $result = D('WwwArticle')->editWwwArticleIsTopById($id,$istop);
            if($result){
                $this->ajaxReturn(array('data'=>'','info'=>'操作成功！','status'=>1));
            }
        }
        $this->ajaxReturn(array('data'=>'','info'=>'操作失败！','status'=>0));
    }

    /**
     * 编辑文章是否推荐
     */
    public function editRecommend()
    {
        $id = I('post.id');
        $recommend = I('post.recommend');
        if(in_array($recommend, array('0','1'))){
            $info = D('WwwArticle')->getWwwArticleById($id);
            //如果是推荐则判断推荐数量
            if ($recommend == 1) {
                $recommend_list = $this->checkRecommendArticleCount($info['classid'], $id);
                if ($recommend_list !== true) {
                    $this->ajaxReturn(array('data'=>'','info'=>'该分类下列表页推荐文章数量已超出10个！','status'=>0));
                }
            }
            $result = D('WwwArticle')->editWwwArticleRecommendById($id,$recommend);
            if($result){
                $this->ajaxReturn(array('data'=>'','info'=>'操作成功！','status'=>1));
            } else {
                $this->ajaxReturn(array('data'=>'','info'=>'数据更新失败，请核查！','status'=>0));
            }
        }
        $this->ajaxReturn(array('data'=>'','info'=>'操作失败！','status'=>0));
    }

    /**
     * 编辑文章状态
     */
    public function editState()
    {
        $id = I('post.id');
        $state = I('post.state');
        if(in_array($state, array('-1','1','2','3'))){
            $info = D('WwwArticle')->getWwwArticleById($id);
            if ($info["from_ruanwen"] == 1 && $state == -1) {
                $this->ajaxReturn(array('data'=>'','info'=>'软文城文章不能删除','status'=>0));
            }

            //预发布改为发布，则发布时间更改为当前时间
            if ('3' == $info['state'] && '3' != $state) {
                $save = array(
                    'addtime' => time(),
                    'state' => $state
                );
            } else {
                $save = array(
                    'state' => $state
                );
            }

            $result = D('WwwArticle')->editWwwArticleById($id, $save);
            if($result){
                //修改标签数量
                if (in_array($info['state'], array('-1', '1', '3'))) {
                    //不可见->可见
                    if (in_array($state, array('2'))) {
                        D('Tags')->editTagCountByTagIds('', $info['tags'], 'article_count');
                    }
                //初始可见
                } else {
                    //可见->不可见
                    if (in_array($state, array('-1', '1', '3'))) {
                        D('Tags')->editTagCountByTagIds($info['tags'], '', 'article_count');
                    }
                }

                if ($info["is_wenda"] == 1) {
                    $logic = new WwwArticleLogicModel();
                    $url = 'https://m.qizuang.com/gonglue/'.$info['shortname'].'/'.$id.'.html';
                    $info["url"] = $url;
                    $info["id"] = $id;
                    $logic->pushWenda($info);
                }

                //添加操作日志
                $log = array(
                    'remark' => '主站文章改变审核状态',
                    'logtype' => 'wwwarticle',
                    'action_id' => $id,
                    'action' => __CONTROLLER__."/".__ACTION__,
                    'info' => $state,
                    'time' => time(),
                    'ip' => $ip
                );
                D('LogAdmin')->addLog($log);

                $this->ajaxReturn(array('data'=>'','info'=>'操作成功！','status'=>1));
            }
        }
        $this->ajaxReturn(array('data'=>'','info'=>'操作失败！','status'=>0));
    }

    public function verifyTitle()
    {
        $title = I('get.title');
        if(!empty($title)){
            $result = D('WwwArticle')->getWwwArticleByTitle($title);
            if(count($result) > 0){
                $this->ajaxReturn(array('data'=>'','info'=>'已存在该标题的文章！','status'=>1));
            }
        }
        $this->ajaxReturn(array('data'=>'','info'=>'暂无此文章！','status'=>0));
    }

    /**
     * [checkRecommendArticleCount 检测列表页推荐文章数量]
     * @param  integer $class_id [分类ID]
     * @param  integer $max      [最大容许的推荐文章]
     * @param  integer $id       [排除的文章ID]
     * @return [type]            [description]
     */
    private function checkRecommendArticleCount($class_id = 0, $id = 0, $max = 10){
        //未删除的，包含未审核的
        $map['a.state'] = array('NEQ','-1');
        $map['a.recommend'] = array('EQ','1');
        if (!empty($id)) {
            $map['a.id'] = array('NEQ',$id);
        }
        if (!empty($class_id)) {
            $map['r.class_id'] = array('EQ',$class_id);
            $result = D('WwwArticle')->getWwwArticleList($map);
            if (count($result) < $max) {
                return true;
            }
            return $result;
        }
        return false;
    }

    //获取列表并分页
    public function getWwwArticleList($map,$pageIndex=1,$pageCount = 16)
    {
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);
        $count = D('WwwArticle')->getWwwArticleCountV2($map);
        $result['list']=D('WwwArticle')->getWwwArticleListV2($map,($pageIndex-1)*$pageCount,$pageCount,'a.addtime DESC');

        foreach ($result['list'] as $key => $value) {
            $result['list'][$key]["tagname"] = str_replace(",", " ", $value["tagname"]);

            if ($value["from_ruanwen"] == 1) {
                $result['list'][$key]["uname"] = "软文城";
            }
        }
        if($count > $pageCount){
            import('Library.Org.Util.Page');
            $page = new \Page($count,$pageCount);
            $result['page'] =  $page->show();
        }
        return $result;
    }


    // feature-702
    public function getUsersByRoleId()
    {
        $adminUserLogic = new AdminuserLogicModel();
        $role_id = I('get.roleid');
        if (empty($role_id)) {
            return [];
        }
        $list = $adminUserLogic->getAdminUserByUid($role_id);
        $result = [];
        $result["error_code"] = 0;
        $result["error_msg"] = "操作成功";
        $result["data"] = $list ? $list : [];
        echo json_encode($result);exit();

    }
}
