<?php
namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\BaikeLogicModel;
use Home\Model\Logic\ThematicWordsLogicModel;
use Home\Model\Service\ElasticsearchServiceModel;

class BaikeController extends HomeBaseController{

    //构造
    public function _initialize(){
        parent::_initialize();
        $this->assign("endtime",date('Y-m-d H:i'));
    }

    /**
     * [index 美图列表]
     * @return [type] [description]
     */
    public function index()
    {
        //状态数组
        $statusArray = array(
            '1' => array('name' => '待审核', 'sql' => "a.review = '0'"),
            '2' => array('name' => '通过审核', 'sql' => 'a.review != "0" AND a.visible = 0'),
            '3' => array('name' => '未通过审核', 'sql' => 'a.review != "0" AND a.visible = 1'),
            '4' => array('name' => '已删除', 'sql' => 'a.remove = 1'),
            '5' => array('name' => '精选百科', 'sql' => 'a.choice = 1'),
            '6' => array('name' => '大家都在看(百科推荐)', 'sql' => 'a.recommend = 1'),
            '7' => array('name' => '预发布', 'sql' => 'a.review = 1 AND a.visible = 2'),
        );
        //分页数组
        $pageArray = array(
            '1' => array('name' => '10条', 'value' => 10),
            '2' => array('name' => '20条', 'value' => 20),
            '3' => array('name' => '30条', 'value' => 30),
            '4' => array('name' => '40条', 'value' => 40)
        );
        //编辑角色列表
        $roleArray = [
            ['id' => 24, 'name' => '主站编辑'],
            ['id' => 69, 'name' => '编辑组长'],
            ['id' => 151, 'name' => '兼职编辑'],
        ];
        /*S-筛选条件获取*/
        $users = [];
        if (!empty($_GET['role_id'])) {
            $users = D("Adminuser")->getAdminuserListByUid([$_GET['role_id']]);
            if (count($users) > 0) {
                $map['a.uid'] = ['in', array_column($users, 'id')];
            }
        }

        if (!empty($_GET['user_id'])) {
            $map['a.uid'] = ['eq', $_GET['user_id']];
        }

        //分类筛选
        if (!empty($_GET['category'])) {
            $map['a.cid'] = intval($_GET['category']);
        }
        if (!empty($_GET['sub_category'])) {
            $map['a.sub_category'] = intval($_GET['sub_category']);
        }
        //标题或ID筛选
        if (!empty($_GET['keyword'])) {
            $map["_complex"] = array(
                'a.id' => intval($_GET['keyword']),
                'a.title' => array('LIKE', '%'.$_GET['keyword'].'%'),
                'a.item' => array('LIKE', '%'.$_GET['keyword'].'%'),
                '_logic' => 'OR'
            );
        }
        //发布人来源获取
        if (!empty($_GET['source'])) {
            if ('1' == $_GET['source']) {
                $map['a.source'] = 1;
            } else if ('2' == $_GET['source']) {
                $map['a.source'] = 0;
            }
        }

        //只获取删除状态为0的
        $map['remove'] = 0;
        //状态获取
        if (!empty($_GET['status'])) {
            if (isset($statusArray[$_GET['status']])) {
                $map['_string'] = $statusArray[$_GET['status']]['sql'];
                if ($_GET['status'] == 4) {
                    unset($map['remove']);
                }
            }
        }

        //分页筛选
        $pageIndex = 1;
        if(!empty($_GET["p"])){
            $pageIndex = $_GET["p"];
        }
        $pageCount = 10;
        if (!empty($_GET["page_count"])) {
            if (isset($pageArray[$_GET["page_count"]])) {
                $pageCount = $pageArray[$_GET["page_count"]]['value'];
            }
        }
        /*S-筛选条件获取*/


        //获取结果
        $result = D("Adminbaike")->getList($map,($pageIndex-1) * $pageCount,$pageCount);

        $list = $result['result'];
        //分页
        import('Library.Org.Util.Page');
        $page = new \Page($result['count'], $pageCount);
        $show =  $page->show();

        //获取百科分类
        $category = $this->getCategoryTree();

        //模板赋值
        $this->assign("category",json_encode($category));
        $this->assign("users",$users);
        $this->assign("list",$list);
        $this->assign("pageArray",$pageArray);
        $this->assign("statusArray",$statusArray);
        $this->assign("roleArray",$roleArray);
        $this->assign('show',$show);
        $this->display();
    }

    //新增编辑操作
    public function operate()
    {
        if (IS_POST) {
            $id           = I('post.id');
            $save = array(
                'title'        => I('post.title'),
                'item'         => I('post.item'),
                'cid'          => I('post.cid'),
                'sub_category' => I('post.sub_category'),
                'thumb'        => I('post.thumb'),
                'description'  => I('post.description'),
                'keywords'     => I('post.title'),
                'content'      => htmlspecialchars_decode(I('post.content')),
                'isoriginal'     => I("post.isoriginal"),
                "isxiongzhang" => I("post.isxiongzhang"),
            );
            //验证数据完整性
            if (empty($save['title'])) {
                $this->ajaxReturn(array('status'=>0, 'info'=>'该填写标题！'));
            }
            if(mb_strlen($save['title'],'utf-8') > 50){
                $this->ajaxReturn(array('status'=>0, 'info'=>'标题最多输入50个字 :( '));
            }
            if (empty($save['cid'])) {
                $this->ajaxReturn(array('status'=>0, 'info'=>'该选择分类！'));
            }
            if (empty($save['sub_category'])) {
                $this->ajaxReturn(array('status'=>0, 'info'=>'该选择子分类！'));
            }
            if (empty($save['thumb'])) {
                $this->ajaxReturn(array('status'=>0, 'info'=>'该上传封面图片！'));
            }
            if (empty($save['description'])) {
                $this->ajaxReturn(array('status'=>0, 'info'=>'该填写简介！'));
            }
            if(mb_strlen($save['description'],'utf-8') > 100){
                $this->ajaxReturn(array('status'=>0, 'info'=>'简介最多输入100个字 :( '));
            }
            if (empty($save['content'])) {
                $this->ajaxReturn(array('status'=>0, 'info'=>'该填写内容！'));
            }
            //如果点击的是预发布 , 则判断是否选择发布时间
            if(I("post.type") == 'yufabu'){
                $post_time = strtotime(I("post.preview_time"));
                if(empty($post_time)){
                    $this->ajaxReturn(array('status'=>0, 'info'=>'该填写预发布日期！'));
                }
            }
            //新增百科
            if (empty($id)) {
                $save['post_time'] = (I("post.type") == 'yufabu') ? $post_time : time();
                $save['uid'] = session("uc_userinfo.id");
                $save['views'] = mt_rand(100,1000);
                $save['review'] = 1;
                $save['visible'] = (I("post.type") == 'yufabu')?'2':'0';
                $save['source'] = '0';
                $id = D("Adminbaike")->addBaike($save);
                if ($id !== false){
                    //添加标签关联
                    $service = new ElasticsearchServiceModel();
                    $result = $service->getContentLabel($save['item'],1);

                    if (count($result) > 0) {
                        foreach ($result as $item) {
                            $allLabel[] = [
                                "thematic_id" => $item["id"],
                                "content_id" => $id,
                                "module" => 2
                            ];
                        }

                        $thematicLogic = new ThematicWordsLogicModel();
                        $thematicLogic->addThematicRelated($allLabel);
                    }

                    //推送百度原创
                    $url = 'https://m.qizuang.com/baike/'.$id.'.html';
                    if(I("post.type") != 'yufabu'){
                        if (I('post.isorigin') == 1) {
                            //推送原创
                            sentURLToBaidu($url,true);
                        } else {
                            //推送非原创
                            sentURLToBaidu($url,false);
                        }
                        if (I('post.isxiongzhang') == 1) {
                            //推送熊掌号
                            sentURLToXiongZhang($url);
                        }
                    }

                    $this->ajaxReturn(array("status"=>1, "info"=>"新增成功！"));
                }
                $this->ajaxReturn(array("status"=>0, "info"=>"新增失败！"));
            }

            //编辑百科
            if (D("Adminbaike")->editBaike($id,$save)){
                $this->ajaxReturn(array("status"=>1, "info"=>"编辑成功！"));
            }
            $this->ajaxReturn(array("status"=>0, "info"=>"编辑失败！"));
        }

        //获取信息
        $id = I('get.id');
        $Baike = D("Adminbaike");
        $info = $Baike->getBaikeByid($id);
        if (count($info) > 0) {
            $info['tagname'] = array_filter(explode(',', $info['tags_name']));
            import('Library.Org.Util.Fiftercontact');
            $filter = new \Fiftercontact();
            $info['content'] = $filter->filter_empty($info['content']);
            if(!empty($info['thumb'])){
                $info['cover'] = "'".'<img class="file-preview-image" src="http://'.C('QINIU_DOMAIN').'/'.$info['thumb'].'">'."'";
            }
            $this->assign('info', $info);
        }

        //获取百科分类
        $category = $this->getCategoryTree();
        //模板赋值
        $this->assign("category",json_encode($category));
        //今天预发布剩余数
        $this->assign("baike_number",S("Cache:sent:baike:surplusnumber" . date('Y-m-d')));

        $this->display();
    }

    //删除
    public function delete(){
        $id = I('post.id');
        if (empty($id)) {
            $this->ajaxReturn(array('status'=>0, 'info'=>'非法请求'));
        }
        //删除
        $result = D("Adminbaike")->removeBaike($id, 1);
        if ($result) {
            $this->ajaxReturn(array('status'=>1, 'info'=>'删除成功'));
        }
        $this->ajaxReturn(array('status'=>0, 'info'=>'删除失败'));
    }

    /**
     * 推荐位置选择
     */
    public function choiceAndRecommend()
    {
        $id = I('post.id');
        if (empty($id)) {
            $this->ajaxReturn(array('status'=>0, 'info'=>'非法请求'));
        }
        $save = array(
            'choice' => intval(I('post.choice')),
            'lanmu' => intval(I('post.lanmu')),
            'baike' => intval(I('post.baike')),
            'recommend' => intval(I('post.recommend'))
        );
        $result = D("Adminbaike")->editBaike($id, $save);
        if ($result) {
            $this->ajaxReturn(array('status'=>1, 'info'=>'修改推荐成功'));
        }
        $this->ajaxReturn(array('status'=>0, 'info'=>'修改推荐失败'));
    }

    public function verifyTitle()
    {
        $title = trim(I('get.title'));
        $baike_id = I('get.baike_id');
        if(!empty($baike_id)){
            $result = D('Adminbaike')->getBaikeByid($baike_id);
            if($result['title'] == $title){
                $this->ajaxReturn(array('data'=>'','info'=>'可以编辑','status'=>1));
            }
        }
        if(!empty($title)){
            $map['a.title'] = ['eq',$title];
            $map['a.remove'] = ['eq',0];
            $result = D('Adminbaike')->getBaikeForPreview($map);
            if($result){
                $this->ajaxReturn(array('data'=>'','info'=>'标题已存在，请重新编辑标题','status'=>0));
            }
        }
        $this->ajaxReturn(array('data'=>'','info'=>'暂无此百科！','status'=>1));
    }

    /**
     * 预览
     */
    public function preview()
    {
        $id = I('get.id');
        if (empty($id)) {
            $this->ajaxReturn(array('status'=>0, 'info'=>'非法请求'));
        }

        //筛选条件
        $sort = I('get.sort');
        $status = I('get.status');
        if (empty($sort)) {
            $map = array(
                'a.id' => intval($id)
            );
        } else {
            if ('prev' == $sort) {
                $order = 'a.id DESC';
                $map['a.id'] = array('LT', $id);
            } else {
                $order = 'a.id ASC';
                $map['a.id'] = array('GT', $id);
            }
            if (!empty($status)) {
                if ('1' == $status) {
                    $map['a.review'] ='0';
                } else if ('2' == $status) {
                    $map['a.review'] = array('NEQ', '0');
                    $map['a.visible'] = '0';
                } else if ('3' == $status) {
                    $map['a.review'] = array('NEQ', '0');
                    $map['a.visible'] = '1';
                }
            }
        }
        $info = D('Adminbaike')->getBaikeForPreview($map, $order);
        if (empty($info)) {
            $this->ajaxReturn(array('status'=>0, 'info'=>'没有了~'));
        }

        //抽离目录
        $content = '$#@' . $info['content'];
        $reg ='/<strong[^>]*>([\w\W]*?)<\/strong\s*>/';
        preg_match_all($reg, $content, $matches);
        $main['catalog'] = $matches[1];
        $temp = array_filter(preg_split($reg, $content));

        //如果目录数量和分割的数量一致，则说明内容的最后面有strong标签，此种情况直接采用原来形式
        if (count($main['catalog']) == count($temp)) {
            unset($main['catalog']);
        } else {
            $i = 1;
            foreach ($temp as $key => $value) {
                $value = trim($value);
                if ($i == 1) {
                    $value = trim(ltrim($value, '$#@'));
                    //说明在截取的第一个目录之上还有内容，此内容不属于任何目录，暂归为简介
                    if (!empty($value)) {
                        $main['brief'] = $value;
                    }
                } else {
                    $main['content'][] = $value;
                }
                $i++;
            }
        }
        //如果目录和内容不匹配，直接采用原来样式
        if (count($main['catalog']) != count($main['content'])) {
            unset($main['catalog']);
            unset($main['content']);
        }

        //获取最近历史输入
        $history = S('Admin:Adminbaike:history');
        if (empty($history)) {
            $history = D('Adminbaike')->getFrequentReview();
            S('Admin:Adminbaike:history', $history, 30);
        }
        foreach ($history as $key => $value) {
            $history_string .= '<li class="review_info" data-value='.$value['review'].'>'. ($key + 1) . '.' . $value['review'].'</li>';
        }

        //模板赋值
        $this->assign('info', $info);
        $this->assign('main', $main);
        $this->assign('history_string', $history_string);
        $content = $this->fetch();
        $this->ajaxReturn(array('status'=>1, 'data'=>$content));
    }

    /**
     * 改变状态
     */
    public function operateStatus()
    {
        $id = I('post.id');
        if (empty($id)) {
            $this->ajaxReturn(array('status'=>0, 'info'=>'非法请求'));
        }
        $save = array(
            'review'  => I('post.review'),
            'visible' => I('post.visible')
        );
        if (empty($save['review'])) {
            $save['review'] = '1';
        }
        $result = D("Adminbaike")->editBaike($id, $save);
        $this->ajaxReturn(array('status'=>1, 'info'=>'修改审核成功'));
    }

    //获取分类树
    public function getCategoryTree()
    {
        $temp = D('Adminbaike')->getCategorys();
        $temp_category = array();
        $category = array(
            array(
                'cid' => 0,
                'name' => '请选择',
                'children' => array(
                    array(
                        'cid' => 0,
                        'name' => '请选择'
                    )
                )
            )
        );
        foreach ($temp as $key => $value) {
            if (empty($value['pid'])) {
                $temp_category[$value['cid']] = $value;
            }
        }
        foreach ($temp as $key => $value) {
            if (!empty($value['pid'])) {
                $temp_category[$value['pid']]['children'][] = $value;
            }
        }
        foreach ($temp_category as $key => $value) {
            array_unshift($value['children'], array(
                'cid' => 0,
                'name' => '请选择'
            ));
            $category[] = $value;
        }
        return $category;
    }

    //获取百科分类
    private function getCategory(){
        $tempCategory = D("Adminbaike")->getCategorys();
        $category = array();
        if($tempCategory){
            foreach ($tempCategory as $k => $v ){
                if($v['pid'] == '0') {
                    $category[$v['cid']] = $v;
                    unset($k);
                }
            }
            foreach ($tempCategory as $k => $v ){
                if($v['pid'] != '0') {
                    $category[$v['pid']]['sub_cate'][] = $v;
                }
            }
        }
        return $category;
    }

    //中文字符串截取
    private function mbstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {
        if(function_exists("mb_substr"))
            $slice = mb_substr($str, $start, $length, $charset);
        elseif(function_exists('iconv_substr')) {
            $slice = iconv_substr($str,$start,$length,$charset);
            if(false === $slice) {
                $slice = '';
            }
        }else{
            $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
            preg_match_all($re[$charset], $str, $match);
            $slice = join("",array_slice($match[0], $start, $length));
        }
        $fix='';
        if(strlen($slice) < strlen($str)){
            $fix='...';
        }
        return $suffix ? $slice.$fix : $slice;
    }

    //获取百科标签
    public function getTagsApi(){
        if(!empty($_GET['key'])){
            $tags = D('Tags')->getTagsByName($_GET['key'], 15, $_GET['istop']);
            $data = array();
            foreach ($tags as $k => $v) {
                $data[] = array(
                                'id' => $v['id'],
                                'text' => $v['name']
                                );
            }
            $this->ajaxReturn(array("data"=>$data,"info"=>"获取成功！","status"=>1));
        }
        $this->ajaxReturn(array("data"=>"","info"=>"获取失败！","status"=>1));
    }

    //取分类并返回Ajax
    public function getCategoryByAjax(){
        $subCategoryId = intval($_GET['id']);
        if(!empty($subCategoryId)){
            $category = $this->getCategory();
            $theCategory = $category[$subCategoryId]['sub_cate'];
            if(!empty($theCategory)){
                $rt = '';
                foreach ($theCategory as $k => $v) {
                    $rt .= '<option value="'.$v["cid"].'">'.$v["name"].'</option>';
                }
                exit($rt);
            }
            exit();
        }
    }


    //取城市
    public function getCity(){
        $cityid = intval($_GET['cid']);

        if(!empty($cityid)){
            $cityList = D("Adminbaike")->getCityList();
            $theCity = $cityList[$cityid];
            unset($cityList);
            if(!empty($theCity)){
                $rt = '';
                foreach ($theCity as $k => $v) {
                    $rt .= '<option value="'.$v["cityid"].'">'.$v["city"].'</option>';
                }
                exit($rt);
            }
            exit();
        }
    }

    /**
     * [checktitle 检查是否有重复的百科标题]
     * @return [type]        [description]
     */
    public function checktitle()
    {
        $map = array(
                     'id' => array('NEQ',intval($_POST['id'])),
                     'title' => array('EQ',$_POST['title']),
                     'visible' => array('EQ','0')
                     );
        $res = D("Adminbaike")->getBaikeByMap($map);

        if(empty($res)){
            $this->ajaxReturn(array('data'=>'','info'=>'','status'=>1));
        }else{
            $this->ajaxReturn(array('data'=>'','info'=>'存在已通过审核的相同标题的百科','status'=>0));
        }
    }

    //取分类最终输出列表
    private function getCategoryShow(){
        //取一级和二级分类
        $categorys = $this->getCategory();
        //获取所有根分类
        foreach ($categorys as $key => $value) {
             unset($value['sub_cate']);
            $rootCategory[$key] = $value;
        }
        unset($categorys);
        return $rootCategory;
    }

    //分类列表
    public function category(){
        $category = $this->getCategory(1);
        $this->assign("category",$category);
        $this->assign("nav",7);
        $this->assign("host",'http://'.C('QZ_YUMINGWWW'));
        $this->display();
    }

    //推荐分类
    public function catetop(){
        $id = $_GET['id'];
        if(empty($id) || !is_numeric($id)){
            $this->error('数据错误！');
        }
        $Baike = D("Adminbaike");

        $info = $Baike->getCategoryById($id);

        if($info['is_top'] == '1'){
            $type = null;
        }else{
            $type = '1';
        }
        if ($Baike->setTopCategory($id,$type)){
            $this->ajaxReturn(['status'=>1]);
        }else{
            $this->ajaxReturn(['status'=>0,'info'=>'操作失败!']);
        }
    }

    //删除分类
    public function cateremove(){
        $id = $_GET['id'];
        if(empty($id) || !is_numeric($id)){
            $this->error('数据错误！');
        }
        if (D("Adminbaike")->removeCategory($id)){
            $this->success('删除分类成功！');
        }else{
            $this->error('删除分类失败！');
        }
    }

    //编辑/新增 分类
    public function editCate()
    {
        $DB = D('Adminbaike');
        $post = I('post.');
        if($post['pid']){
            $data['pid'] = $post['pid'];
        }
        $id = $post['edit_id'];
        $data['name'] = trim(remove_xss($post['name']));
        $data['url'] = trim(remove_xss($post['url']));
        $data['order_id'] = (int)remove_xss($post['order_id']);
        $data['title'] = trim(remove_xss($post['title']));
        $data['keywords'] = trim(remove_xss($post['keywords']));
        $data['description'] = trim(remove_xss($post['description']));
        //验证表单数据
        $checkResult = D('Home/Logic/AdminbaikeLogic')->checkCateForm($data);
        if ($checkResult !== true){
            $this->ajaxReturn($checkResult);
        }
        if (!empty($post['edit_id'])) {
            //logo info
            $info = [
                'action_id' => $post['edit_id'],
                'remark' => '编辑分组' . $post['edit_id'],
                'logtype' => 'editBaikeCategory',
                'info' => json_encode($data)
            ];
            //编辑
            $status = $DB->editCategory($post['edit_id'], $data);

        } else {
            //新增
            $id = $status = $DB->addCategory($data);
            //logo info
            $info = [
                'action_id' => $status,
                'remark' => '新增分组' . $status,
                'logtype' => 'editBaikeCategory',
                'info' => json_encode($data)
            ];
        }
        if ($status!==false) {
            $logic = new BaikeLogicModel();
            //删除原来的分词
            $logic->delClassParticiple($id,2);
            //添加新的分词
            $search_word = str_replace("；",";",I("post.search_word"));
            $search_word = array_filter(explode(";",$search_word));
            if (count($search_word) > 0) {
                $logic->setClassParticiple($id,$search_word);
            }

            //添加logo
            D("Adminask")->addLog($info);
            $this->ajaxReturn(['info' => '修改成功', 'status' => 1]);
        } else {
            $this->ajaxReturn(['info' => '操作失败！', 'status' => 0]);
        }
    }

    public function getCategoryInfo()
    {
        $id = I('get.id');
        $DB = D('Adminbaike');
        //取分类
        $category = $DB->getCategoryById($id);
        if ($category) {
            $this->ajaxReturn(['info' => $category, 'status' => 1]);
        } else {
            $this->ajaxReturn(['info' => '获取分类信息失败!', 'status' => 0]);
        }
    }

    public function pluralistic()
    {
        //状态数组
        $statusArray = array(
            '1' => array('name' => '待审核', 'sql' => "a.review = '0'"),
            '2' => array('name' => '通过审核', 'sql' => 'a.review != "0" AND a.visible = 0'),
            '3' => array('name' => '未通过审核', 'sql' => 'a.review != "0" AND a.visible = 1'),
            '4' => array('name' => '已删除', 'sql' => 'a.remove = 1'),
            '5' => array('name' => '精选百科', 'sql' => 'a.choice = 1'),
            '6' => array('name' => '大家都在看(百科推荐)', 'sql' => 'a.recommend = 1'),
            '7' => array('name' => '预发布', 'sql' => 'a.review = 1 AND a.visible = 2'),
        );
        //分页数组
        $pageArray = array(
            '1' => array('name' => '10条', 'value' => 10),
            '2' => array('name' => '20条', 'value' => 20),
            '3' => array('name' => '30条', 'value' => 30),
            '4' => array('name' => '40条', 'value' => 40)
        );

        $baikeLogic = new BaikeLogicModel();
        $info = $baikeLogic->getBaikeList(I('get.'),$this->User,$statusArray);
        //获取百科分类
        $category = $this->getCategoryTree();

        //模板赋值
        $this->assign("category",json_encode($category));
        $this->assign("list",$info['list']);
        $this->assign("pageArray",$pageArray);
        $this->assign("statusArray",$statusArray);
        $this->assign('show',$info['page']);
        $this->display();
    }

    //新增编辑操作
    public function pluralistic_operate()
    {
        if (IS_POST) {
            $id           = I('post.id');
            $save = array(
                'title'        => I('post.title'),
                'item'         => I('post.item'),
                'cid'          => I('post.cid'),
                'sub_category' => I('post.sub_category'),
                'thumb'        => I('post.thumb'),
                'description'  => I('post.description'),
                'keywords'     => I('post.title'),
                'content'      => htmlspecialchars_decode(I('post.content')),
                'isoriginal'     => I("post.isoriginal"),
                "isxiongzhang" => I("post.isxiongzhang"),
            );
            //验证数据完整性
            if (empty($save['title'])) {
                $this->ajaxReturn(array('status'=>0, 'info'=>'该填写标题！'));
            }
            if(mb_strlen($save['title'],'utf-8') > 50){
                $this->ajaxReturn(array('status'=>0, 'info'=>'标题最多输入50个字 :( '));
            }
            if (empty($save['cid'])) {
                $this->ajaxReturn(array('status'=>0, 'info'=>'该选择分类！'));
            }
            if (empty($save['sub_category'])) {
                $this->ajaxReturn(array('status'=>0, 'info'=>'该选择子分类！'));
            }
            if (empty($save['thumb'])) {
                $this->ajaxReturn(array('status'=>0, 'info'=>'该上传封面图片！'));
            }
            if (empty($save['description'])) {
                $this->ajaxReturn(array('status'=>0, 'info'=>'该填写简介！'));
            }
            if(mb_strlen($save['description'],'utf-8') > 100){
                $this->ajaxReturn(array('status'=>0, 'info'=>'简介最多输入100个字 :( '));
            }
            if (empty($save['content'])) {
                $this->ajaxReturn(array('status'=>0, 'info'=>'该填写内容！'));
            }
            //如果点击的是预发布 , 则判断是否选择发布时间
            if(I("post.type") == 'yufabu'){
                $post_time = strtotime(I("post.preview_time"));
                if(empty($post_time)){
                    $this->ajaxReturn(array('status'=>0, 'info'=>'该填写预发布日期！'));
                }
            }
            //新增百科
            if (empty($id)) {
                $save['post_time'] = (I("post.type") == 'yufabu') ? $post_time : time();
                $save['uid'] = session("uc_userinfo.id");
                $save['views'] = mt_rand(100,1000);
                $save['review'] = 1;
                $save['visible'] = (I("post.type") == 'yufabu')?'2':'0';
                $save['source'] = '0';
                $id = D("Adminbaike")->addBaike($save);
                if ($id !== false){
                    //添加标签关联
                    $service = new ElasticsearchServiceModel();
                    $result = $service->getContentLabel($save['item'],1);

                    if (count($result) > 0) {
                        foreach ($result as $item) {
                            $allLabel[] = [
                                "thematic_id" => $item["id"],
                                "content_id" => $id,
                                "module" => 2
                            ];
                        }

                        $thematicLogic = new ThematicWordsLogicModel();
                        $thematicLogic->addThematicRelated($allLabel);
                    }

                    //推送百度原创
                    $url = 'https://m.qizuang.com/baike/'.$id.'.html';
                    if(I("post.type") != 'yufabu'){
                        if (I('post.isorigin') == 1) {
                            //推送原创
                            sentURLToBaidu($url,true);
                        } else {
                            //推送非原创
                            sentURLToBaidu($url,false);
                        }
                        if (I('post.isxiongzhang') == 1) {
                            //推送熊掌号
                            sentURLToXiongZhang($url);
                        }
                    }

                    $this->ajaxReturn(array("status"=>1, "info"=>"新增成功！"));
                }
                $this->ajaxReturn(array("status"=>0, "info"=>"新增失败！"));
            }

            //编辑百科
            if (D("Adminbaike")->editBaike($id,$save)){
                $this->ajaxReturn(array("status"=>1, "info"=>"编辑成功！"));
            }
            $this->ajaxReturn(array("status"=>0, "info"=>"编辑失败！"));
        }

        //获取信息
        $id = I('get.id');
        $Baike = D("Adminbaike");
        $info = $Baike->getBaikeByid($id);
        if (count($info) > 0) {
            $info['tagname'] = array_filter(explode(',', $info['tags_name']));
            import('Library.Org.Util.Fiftercontact');
            $filter = new \Fiftercontact();
            $info['content'] = $filter->filter_empty($info['content']);
            if(!empty($info['thumb'])){
                $info['cover'] = "'".'<img class="file-preview-image" src="http://'.C('QINIU_DOMAIN').'/'.$info['thumb'].'">'."'";
            }
            $this->assign('info', $info);
        }

        //获取百科分类
        $category = $this->getCategoryTree();
        //模板赋值
        $this->assign("category",json_encode($category));
        //今天预发布剩余数
        $this->assign("baike_number",S("Cache:sent:baike:surplusnumber" . date('Y-m-d')));

        $this->display();
    }

    public function getRoleUser()
    {
        $role_id = I('get.role_id');
        $users = D("Adminuser")->getAdminuserListByUid([$role_id]);
        $this->ajaxReturn(['error_code' => 0, 'data' => $users]);
    }
}
