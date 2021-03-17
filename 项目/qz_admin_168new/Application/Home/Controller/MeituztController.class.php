<?php

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;

//美图专题

class MeituztController extends HomeBaseController {

    public $directPost = false;

    //首页
    public function index() {
        $title = I('get.title');
        if (!empty($title)) {
            $map['a.title'] = array('like',"%$title%");
        }

        $status = I('get.status');
        if (!empty($status)) {
            $map['a.status'] = $status;
        }

        $is_home = I('get.is_home');
        if (!empty($is_home)) {
            $map['a.is_home'] = $is_home;
        }

        $is_choice = I('get.is_choice');
        if (!empty($is_choice)) {
            $map['a.is_choice'] = $is_choice;
        }
        $pageCount = 15;
        $pageIndex = I('get.p');
        $pageIndex = empty($pageIndex) ? '1' : $pageIndex;

        //获取结果
        $result = D("Meituzt")->getList($map,($pageIndex-1) * $pageCount,$pageCount);
        $list = $result['result'];
        //dump($list);

        //分页
        import('Library.Org.Util.Page');
        $page = new \Page($result['count'], $pageCount);

        $this->assign("list",$list);
        $this->assign('page',$page->show());
        $this->display();
    }

    //增加美图
    public function add() {
        $id = I('get.id');

        if(IS_POST){
            $post = I('post.');
            $save = array(
                'title'        => $post['title'],
                'description'  => $post['description'],
                'keywords'     => $post['keywords'],
                'meitu_desc'   => $post['meitu_desc'],
                'case_desc'    => $post['case_desc'],
                'article_desc' => $post['article_desc'],
                'is_home'      => $post['is_home'],
                'is_choice'    => $post['is_choice'],
            );


            $title = str_replace(" ", "", $post['title']);

            //like查询与输入标题相似的全部数据
            $meitu_title = $this->getmeituztbtapi($title);

            //该标题已存在
            foreach($meitu_title as $key => $value){
                if($title == str_replace(" ", "", $value['text'])){
                    $this->ajaxReturn(array('data' => '', 'info' => '该标题已存在！', 'status' =>0));
                }
            }

            $save['is_home'] = $save['is_home'] == 'on' ? '2' : '1';
            $save['is_choice'] = $save['is_choice'] == 'on' ? '1' : '2';

            if(!empty($post['thumb'])){
                $save['thumb'] = $post['thumb'];
            }

            //验证数据完整性
            if (empty($save['title'])) {
                $this->ajaxReturn(array('status'=>0, 'info'=>'请填写标题！'));
            }
            if(mb_strlen($save['title'],'utf-8') > 50){
                $this->ajaxReturn(array('status'=>0, 'info'=>'标题最多输入50个字 :( '));
            }
            if (empty($save['description'])) {
                $this->ajaxReturn(array('status'=>0, 'info'=>'请填写描述！'));
            }

            $meituList = explode(',',str_replace(array('[',']','&quot;'),'',I('cookie.meituList')));
            $caseList = explode(',',str_replace(array('[',']','&quot;'),'',I('cookie.caseList')));
            $articleList = explode(',',str_replace(array('[',']','&quot;'),'',I('cookie.articleList')));

            if(count($meituList)  != 5){
                $this->ajaxReturn(array('status'=>0, 'info'=>'美图数量必须选择5条'));
            }
            if(count($caseList)  != 8){
                $this->ajaxReturn(array('status'=>0, 'info'=>'案例数量必须选择8条'));
            }
            if(count($articleList)  != 4){
                $this->ajaxReturn(array('status'=>0, 'info'=>'文章数量必须选择4条'));
            }

            //新增
            if (empty($id)) {
                $save['uid'] = session("uc_userinfo.id");
                //创建时间
                $save["create_time"] = time();
                //直接发布
                if (true == $this->directPost) {
                    $save['status'] = 1;
                    $save["time"] = time();
                } else {
                    $save['status'] = 3;
                    if (!empty($post['time'])) {
                        $save["time"] = strtotime(date('Y-m-d', strtotime($post['time'])));
                        if ($save["time"] < strtotime(date('Y-m-d'))) {
                            $this->ajaxReturn(array('info'=>'如需填写预发布日期，则该时间不能小于当前日期！','status'=>0));
                        }
                    } else {
                        $save["time"] = 0;
                    }
                }
                $save["init_status"] = $save['status'];

                $insertId =  D('Meituzt')->addMeituZt($save);
                if ($insertId){

                    $item = array(
                        'uid' => $save['uid'],
                        'time' => time(),
                        'zid' => $insertId,
                    );

                    //美图入库
                    foreach ($meituList as $k => $v) {
                        $item['type'] = '1';
                        $item['item_id'] = $v;
                        D('Meituzt')->addMeituZtItem($item);
                    }

                    //案例入库
                    foreach ($caseList as $k => $v) {
                        $item['type'] = '2';
                        $item['item_id'] = $v;
                        D('Meituzt')->addMeituZtItem($item);
                    }

                    //文章入库
                    foreach ($articleList as $k => $v) {
                        $item['type'] = '3';
                        $item['item_id'] = $v;
                        D('Meituzt')->addMeituZtItem($item);
                    }
                    $this->ajaxReturn(array('status'=>1, 'info'=>'新增成功！'));
                }
                $this->ajaxReturn(array('status'=>0, 'info'=>'新增失败！'));
                die;
            }
        }
        $this->display();
    }

    //修改美图
    public function edit() {
        $id = I('get.id');
        if (empty($id)) {
            $this->ajaxReturn(array('status'=>0, 'info'=>'数据错误！'));
        }

        $result = D('Meituzt')->getSpaceials(array('zid'=>$id));
        foreach ($result as $key => $value) {
            $itemList[$value['type']][] = $value['item_id'];
        }

        if(IS_POST){
            $post = I('post.');
            $save = array(
                'title' => $post['title'],
                'description' => $post['description'],
                'keywords' => $post['keywords'],
                'meitu_desc' => $post['meitu_desc'],
                'case_desc' => $post['case_desc'],
                'article_desc' => $post['article_desc'],
                'is_home' => $post['is_home'],
                'is_choice' => $post['is_choice'],
            );

            $save['is_home'] = $save['is_home'] == 'on' ? '2' : '1';
            $save['is_choice'] = $save['is_choice'] == 'on' ? '1' : '2';

            if(!empty($post['thumb'])){
                $save['thumb'] = $post['thumb'];
            }

            //验证数据完整性
            if (empty($save['title'])) {
                $this->ajaxReturn(array('status'=>0, 'info'=>'请填写标题！'));
            }
            if(mb_strlen($save['title'],'utf-8') > 50){
                $this->ajaxReturn(array('status'=>0, 'info'=>'标题最多输入50个字 :( '));
            }
            if (empty($save['description'])) {
                $this->ajaxReturn(array('status'=>0, 'info'=>'请填写描述！'));
            }

            $meituList = explode(',',str_replace(array('[',']','&quot;'),'',I('cookie.meituList')));
            $caseList = explode(',',str_replace(array('[',']','&quot;'),'',I('cookie.caseList')));
            $articleList = explode(',',str_replace(array('[',']','&quot;'),'',I('cookie.articleList')));

            if(count($meituList)  != 5){
                $this->ajaxReturn(array('status'=>0, 'info'=>'美图数量必须选择5条'));
            }
            if(count($caseList)  != 8){
                $this->ajaxReturn(array('status'=>0, 'info'=>'案例数量必须选择8条'));
            }
            if(count($articleList)  != 4){
                $this->ajaxReturn(array('status'=>0, 'info'=>'文章数量必须选择4条'));
            }

            //获取美图专题
            $zt = D('Meituzt')->getSpecial($id);
            //保存用户
            $save['uid'] = $zt['uid'];

            //直接发布，只有预发布的美图才可以直接发布
            if (true == $this->directPost) {
                if ('3' != $zt['status']) {
                    $this->ajaxReturn(array('info'=>'只有预发布状态才可以直接发布！','status'=>0));
                }
                $save['status'] = 1;
                $save['time'] = time();
                //由预发布状态手动改为直接发布，则初始状态也要更改
                $save["init_status"] = '1';
            } else {
                if (!empty($post['time'])) {
                    if ('3' != $zt['status']) {
                        $this->ajaxReturn(array('info'=>'只有预发布状态才可以填写预发布日期！','status'=>0));
                    } else {
                        $save["time"] = strtotime(date('Y-m-d', strtotime($post['time'])));
                        if ($save["time"] < strtotime(date('Y-m-d'))) {
                            $this->ajaxReturn(array('info'=>'如需填写预发布日期，则该日期不能小于当前日期！','status'=>0));
                        }
                    }
                } else {
                    if ('3' == $zt['status']) {
                        $save["time"] = 0;
                    }
                }
            }

            $save['last_time'] = time();
            $save['last_uid'] = session("uc_userinfo.id");

            //保存信息
            D('Meituzt')->updateMeituZt($id,$save);

            $item = array(
                'uid' => $save['uid'],
                'time' => time(),
                'zid' => $id,
            );

            $arrayDiff = array_diff($itemList['1'],$meituList);
            if(!empty($arrayDiff) || $arrayDiff != 'NULL'){
                D("Meituzt")->removeZtItemByType($id,'1');
                //美图入库
                foreach ($meituList as $k => $v) {
                    $item['type'] = '1';
                    $item['item_id'] = $v;
                    D('Meituzt')->addMeituZtItem($item);
                }
            }

            $arrayDiff = array_diff($itemList['2'],$caseList);
            if(!empty($arrayDiff) || $arrayDiff != 'NULL'){
                D("Meituzt")->removeZtItemByType($id,'2');
                //案例入库
                foreach ($caseList as $k => $v) {
                    $item['type'] = '2';
                    $item['item_id'] = $v;
                    D('Meituzt')->addMeituZtItem($item);
                }
            }

            $arrayDiff = array_diff($itemList['3'],$articleList);
            if(!empty($arrayDiff) || $arrayDiff != 'NULL'){
                D("Meituzt")->removeZtItemByType($id,'3');
                //文章入库
                foreach ($articleList as $k => $v) {
                    $item['type'] = '3';
                    $item['item_id'] = $v;
                    D('Meituzt')->addMeituZtItem($item);
                }
            }
            $this->ajaxReturn(array('status'=>1, 'info'=>'修改成功！'));
        }

        $info = D('Meituzt')->getSpecial($id);

        if(!empty($itemList['1'])){
            $this->assign('meituList',D('Meituzt')->getMeituTitle($itemList['1']));
        }
        if(!empty($itemList['2'])){
            $this->assign('caseList',D('Meituzt')->getCaseTitle($itemList['2']));
        }
        if(!empty($itemList['3'])){
            $this->assign('articleList',D('Meituzt')->getArticleTitle($itemList['3']));
        }

        $this->assign('info',$info);
        $this->display();
    }

    /**
     * 直接发布按钮
     */
    public function directPost()
    {
        $this->directPost = true;
        $type = I('get.type');
        if ('add' == $type) {
            $this->add();
        } else {
            $this->edit();
        }
    }

    //删除
    public function setStatus(){
        $id = I('post.id');
        $status = I('post.status');
        if (empty($id)) {
            $this->ajaxReturn(array('status'=>0, 'info'=>'非法请求'));
        }

        $info = D('Meituzt')->getSpecial($id);
        if ('3' == $info['status'] && '3' != $status) {
            $save = array(
                'status' => $status,
                'time' => time(),
            );
        } else {
            $save = array(
                'status' => $status
            );
        }

        //保存数据
        $result = D('Meituzt')->updateMeituZt($id,$save);
        if ($result) {
            $this->ajaxReturn(array('status'=>1, 'info'=>'操作成功'));
        }
        $this->ajaxReturn(array('status'=>0, 'info'=>'操作失败'));
    }

    //美图列表
    public function meituBox(){
        $data = I('get.');
        $pageIndex = 1;
        if(!empty($data['p'])){
            $pageIndex = $data['p'];
        }

        $ids = $this->getIdsList('meitu');
        if(!empty($ids)){
            $data['ids'] = $ids;
        }

        //获取美图列表
        $info = R('Meitu/getMeituList',array($data, $pageIndex=1, $pageCount = 16, $data['keyword'], $data['state']));
        $info['list'] = $this->getChoiceList($info['list'],$ids);

        //获取美图属性
        $info['attribute'] = D('Meitu')->getMeituAttribute('', ['enabled' => 1]);
        $this->assign('oldids',json_encode($ids));
        $this->assign('info',$info);
        $this->display('meituBox');
    }

    //案例列表
    public function caseBox(){
        //获取GET参数
        $cases_id         = I('get.cases_id');
        /*$designer_id      = I('get.designer_id');
        $company_id       = I('get.company_id');*/
        $cs               = I('get.cs');
        $title            = I("get.title");
        $cases_time_start = I('get.cases_time_start');
        $cases_time_end   = I('get.cases_time_end');

        if(empty($cases_id)){
            $cases_id = 0;
        }


        //时间参数转换
        $cases_time_start = empty($cases_time_start) ? '' : strtotime($cases_time_start);
        $cases_time_end   = empty($cases_time_end) ? '' : strtotime($cases_time_end);

        $is_choice = I('get.is_choice');
        $ids = $this->getIdsList('case');
        if(!empty($ids) && !empty($is_choice)){
            $cases_id = $ids;
        }

        $info = R('Cases/getCasesList',array($cases_id, $cs, $cases_time_start, $cases_time_end, $title, 15, 1));
        $info['list'] = $this->getChoiceList($info['list'],$ids);
        $this->assign('oldids',json_encode($ids));
        $this->assign('citys',D('Quyu')->getQuyuList());
        $this->assign('info',$info);
        $this->display('caseBox');
    }

    //文章列表
    public function articleBox(){
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

        // 查询词
        if (!empty($data['condition'])) {
            $searchstr  = $data['condition'];
            $map['_complex'] = array(
                    '_logic' => 'OR',
                    'a.title'  => array('LIKE', "%$searchstr%"),
                    'a.id'  => array('EQ', intval($searchstr))
                    );
        }

        //未审核
        if(!empty($data['state']) && in_array($data['state'],array('1','2'))){
            $map['a.state'] = $data['state'];
        }

        $is_choice = I('get.is_choice');
        $ids = $this->getIdsList('article');

        if($is_choice == '1' && !empty($ids)){
            $map['a.id'] = array('IN',$ids);
        }elseif($is_choice == '2' && !empty($ids)){
            $map['a.id'] = array('NOT IN',$ids);
        }


        $info = R('Wwwarticle/getWwwArticleList',array($map,$pageIndex));
        $info['articleclass'] = json_encode(D('WwwArticleClass')->getArticleClassTree(false));

        $info['list'] = $this->getChoiceList($info['list'],$ids);
        $this->assign('oldids',json_encode($ids));
        $this->assign('info',$info);
        $this->display('articleBox');
    }

    //获取标题
    public function getTitle(){
        $module = I('get.module');
        if($module == 'meitu'){
            $idList = str_replace(array('[',']','&quot;'),'',I('cookie.meituList'));
            $idList = explode(',',$idList);
            $result = D('Meituzt')->getMeituTitle($idList);
            $this->ajaxReturn(array('status'=>1, 'data'=>$result));
        }
        if($module == 'case'){
            $idList = str_replace(array('[',']','&quot;'),'',I('cookie.caseList'));
            $idList = explode(',',$idList);
            $result = D('Meituzt')->getCaseTitle($idList);
            $this->ajaxReturn(array('status'=>1, 'data'=>$result));
        }
        if($module == 'article'){
            $idList = str_replace(array('[',']','&quot;'),'',I('cookie.articleList'));
            $idList = explode(',',$idList);
            $result = D('Meituzt')->getArticleTitle($idList);
            $this->ajaxReturn(array('status'=>1, 'data'=>$result));
        }
    }

    //获取 Cookie中选择的ID
    public function getIdsList($module){
        if(!in_array($module,array('meitu','case','article'))){
            $this->_error('数据不正确');
        }
        $ids = str_replace(array('[',']','&quot;'),'',I('cookie.'.$module.'List'));
        if(empty($ids)){
            return '';
        }
        return explode(',',$ids);
    }

    //获取选择状态后列表
    public function getChoiceList($list,$ids){
        foreach ($list as $k => $v) {
            if(in_array($v['id'],$ids)){
                $list[$k]['toggleIcon'] = 'fa-toggle-on';
            }else{
                $list[$k]['toggleIcon'] = 'fa-toggle-off';
            }
        }
        return $list;
    }

    //美图专题banner管理
    public function banner()
    {
        $title = I('get.title');
        if (!empty($title)) {
            $map['title'] = array('like', "%$title%");
            $this->assign('title',$title);
        }
        $status = I('get.status');
        if (!empty($status)) {
            $map['status'] = $status;
            $this->assign('status',$status);
        }
        $type = I('get.type', 0, 'intval');
        $order = 'type ASC,time DESC';
        if (!empty($type)) {
            $map['type'] = $type;
            $this->assign('type',$type);
        }
        $pageNum = max('1', I('get.p', '1', 'intval'));
        $pageSize = I('get.pageSize', '10', 'intval');
        //获取结果
        $result = D("Meituzt")->getBannerList($map, $pageNum, $pageSize,$order);
        $list = $result['result'];

        //分页
        import('Library.Org.Util.Page');
        $page = new \Page($result['count'], $pageSize);

        $this->assign("list", $list);
        $this->assign("query", $_SERVER['QUERY_STRING']);
        $this->assign('page', $page->show());
        $this->display();
    }

    //美图专题banner编辑
    public function banneroperate()
    {
        $id = I('get.id');
        if (IS_POST) {
            $post = I('post.');
            $save = array(
                'title' => trim($post['title']),
                'order_id' => trim($post['order_id']),
                'type' => trim($post['type']),
                'url' => trim($post['url']),
                'bd_url' => trim($post['bd_url']),
                'm_url' => trim($post['m_url']),
                'thumb' => trim($post['thumb']),
                'img' => trim($post['img']),
                'status' => trim($post['status']),

            );
            $save['status'] = $save['status'] == 'on' ? '1' : '2';

            //验证数据完整性
            if (empty($save['type'])) {
                $this->ajaxReturn(array('status' => 0, 'info' => '请选择banner所属模块'));
            }
            if (empty($save['title'])) {
                $this->ajaxReturn(array('status' => 0, 'info' => '文章标题不符合规定'));
            }
            if (empty($save['order_id'])) {
                $this->ajaxReturn(array('status' => 0, 'info' => '请输入排序的值'));
            }
            if (!preg_match('/^[0-9]*$/i', $save['order_id']) || $save['order_id'] <= 0) {
                $this->ajaxReturn(array('status' => 0, 'info' => '排序只能输入大于0的整数'));
            }
            if (mb_strlen($save['title'], 'utf-8') > 30) {
                $this->ajaxReturn(array('status' => 0, 'info' => '文章标题不符合规定'));
            }
            if (empty($save['url']) || !filter_var($save['url'], FILTER_VALIDATE_URL)) {
                $this->ajaxReturn(array('status' => 0, 'info' => '请填写正确的PC端链接URL地址'));
            }
//            if (!empty($save['bd_url']) && !filter_var($save['bd_url'], FILTER_VALIDATE_URL)) {
//                $this->ajaxReturn(array('status' => 0, 'info' => '请填写正确的小程序链接URL地址'));
//            }
            if (!empty($save['m_url']) && !filter_var($save['m_url'], FILTER_VALIDATE_URL)) {
                $this->ajaxReturn(array('status' => 0, 'info' => '请填写正确的m端链接URL地址'));
            }
            if (empty($save['thumb'])) {
                $this->ajaxReturn(array('status' => 0, 'info' => '请上传上传PC端图片'));
            }
//            if (empty($save['img'])) {
//                $this->ajaxReturn(array('status' => 0, 'info' => '请上传m端和小程序图片'));
//            }
            $save['bd_url'] = $save['bd_url'] ?: '';
            $save['img'] = $save['img'] ?: '';
            $save['time'] = time();
            $save['uid'] = session("uc_userinfo.id");

            if (empty($id)) {
                $insertId = D('Meituzt')->addBanner($save);
                if ($insertId) {
                    $this->ajaxReturn(array('status' => 1, 'info' => '新增成功！', 'type' => $save['type']));
                } else {
                    $this->ajaxReturn(array('status' => 0, 'info' => '新增失败！', 'type' => ''));
                }
            }

            $update = D('Meituzt')->updateBanner($id, $save);
            if ($update !== false) {
                $this->ajaxReturn(array('status' => 1, 'info' => '修改成功！'));
            } else {
                $this->ajaxReturn(array('status' => 0, 'info' => '修改失败,信息可能未变化！'));
            }
        }
        $info = D('Meituzt')->getBanner($id);
        $this->assign('info', $info);
        $this->display('banneroperate');
    }

    //删除
    public function bannerdelete()
    {
        $id = I('post.id');
        if (empty($id)) {
            $this->ajaxReturn(array('status' => 0, 'info' => '非法请求'));
        }

        //删除
        $result = D("Meituzt")->removeBanner($id);
        if ($result) {
            $this->ajaxReturn(array('status' => 1, 'info' => '删除成功'));
        }
        $this->ajaxReturn(array('status' => 0, 'info' => '删除失败'));
    }

    /**
     * 上传图片
     * @return [type] [description]
     */
    public function uploadImg()
    {
        if($_POST){
            if (count($_FILES) == 0) {
                header("Content-type:text/html;charset=utf-8");
                header("HTTP/1.1 405 Picture not uploaded");
                die();
            }
            $width = imagesx($_FILES);
            $height = imagesy($_FILES);
            if ($width != 930 || $height != 340){
                header("Content-type:text/html;charset=utf-8");
                header("HTTP/1.1 405 图片限制为930px×340px");
                die();
            }
            $setting = C('UPLOAD_IMG_QINIU');
            $setting["saveName"] =  array('uniqid','');

            $file = $_FILES[array_keys($_FILES)[0]];

            if(!empty($_POST['chars'])){
                $setting["saveName"] = $this->getFilePinYinName($file['name']);
            }
            $setting["savePath"] = "";
            $info = '';
            if(!empty($_POST['prefix'])){
                $setting["savePath"] = $_POST['prefix'].'/';
            }
            $setting["subName"] = array('date', 'Ymd');
            $setting["driverConfig"]["domain"] = OP("QINIU_DOMAIN");
            $setting["driverConfig"]["bucket"] = OP('QINIU_BUCKET');
            $setting["driverConfig"]["secretKey"] = OP("QINIU_CK");
            $setting["driverConfig"]["accessKey"] =  OP("QINIU_AK");
            $Upload = new \Think\Upload($setting);
            $data = $Upload->upload($_FILES);

            if($data !== false){
                $this->ajaxReturn(array('data'=>$data[array_keys($_FILES)[0]],'info'=>$info,'status'=>1));
            }
        }
        $this->ajaxReturn(
            array(
                'data'=>'',
                'info'=>'上传失败，请重新上传，如多次失败请联系技术部门！',
                'status'=>0
            )
        );
        die();
    }

    /**
     * [getFilePinYinName 获取汉子拼音字符串，备注，有的获取不到]
     * @param  [type] $string [description]
     * @return [type]         [description]
     */
    private function getFilePinYinName($string)
    {
        $name = substr($string, 0, strrpos($string, '.'));
        import("Library.Org.PinYin.PinYin");
        $PinYin = new \PinYin();
        $name = $PinYin->getAllPY($name) . '-' . substr(time(), -6);
        //替换掉文件名中间的空格
        $pattern = array(' ', '.');
        $name = str_replace($pattern, '', $name);
        return $name;
    }

    //获取美图专题的标题
    public function getmeituztbtapi($title=""){
        if(empty($title)){
            $tags = D('Meituzt')->getmeituztByName($_GET['key']);
        }else{
            $tags = D('Meituzt')->getmeituztByName($title, '');
        }

        /* if(empty($tags)){
             $tags = D('Meitu')->getJiajumeituByName($title);
         }*/
        $data = array();
        foreach ($tags as $k => $v) {
            $data[] = array(
                'id' => $v['id'],
                'text' => $v['title']
            );
        }
        if(empty($title)){
            if(empty($data)){
                $this->ajaxReturn(array('data' => '', 'info' => '获取失败！', 'status' =>0));
            }
            $this->ajaxReturn(array('data' => $data, 'info' => '获取成功！', 'status' =>1));
        }

        if(!empty($data)){
            return $data;
        }
        return false;
    }
}