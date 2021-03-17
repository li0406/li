<?php
namespace Home\Controller;

use Common\Enums\ApiConfig;
use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\MeituLogicModel;
use Think\Exception;

class MeituController extends HomeBaseController{

    public $directPost = false;
    public $directPostThreedimensional = false;

    protected $meituLogic;

    public function __construct()
    {
        parent::__construct();
        $this->meituLogic = new MeituLogicModel();
    }
    /**
     * [index 美图列表]
     * @return [type] [description]
     */
    public function index()
    {
        $data = I('get.');
        $pageIndex = 1;
        if(!empty($data['p'])){
            $pageIndex = $data['p'];
        }
        //获取美图列表
        $info['info'] = $this->getMeituList($data, $pageIndex=1, $pageCount = 16, $data['keyword'], $data['state']);

        //dump($info['info']);

        //获取美图属性
        $info['attribute'] = D('Meitu')->getMeituAttribute('', ['enabled' => 1]);

        $this->assign('info',$info);
        $this->assign('p',$data['p']);
        $this->display();
    }

    /**
     * [operate 美图详情页]
     * @return [type] [description]
     */
    public function operate(){
        $data = I('post.');
        if(!empty($data)){
            $id = $data['id'];
            $save['title'] = $data['title'];
            $save['location'] = $data['location'];
            $save['fengge'] = $data['fengge'];
            $save['huxing'] = $data['huxing'];
            $save['color'] = $data['color'];
            $save['tags'] = D('Tags')->getTagIdsByTagNames($data['tagnames']);
            $save['keyword'] = $data['keyword'];
            $save['description'] = $data['description'];
            $save['type'] = $data['type'];
            $save['master'] = $save['type'] == 1 ? 0 : $data['master'];
            $save['istop'] = $data['istop'];


            //输入的标题
            $title = str_replace(" ", "", $data['title']);

            //like查询与输入标题相似的已存在的标题
            $meitu_title = $this->getJiajumeituapi($title,$id);

            //该标题已存在
            foreach($meitu_title as $key => $value){
                if($title == str_replace(" ", "", $value['text'])){
                    $this->ajaxReturn(array('data' => '', 'info' => '该标题已存在！', 'status' =>0));
                }
            }


            //必填项验证
            if (empty($save['title'])) {
                $this->ajaxReturn(array('info'=>'请填写标题！','status'=>0));
            }
            //必填项验证
            if (empty($save['location'])) {
                $this->ajaxReturn(array('info'=>'请选择位置属性！','status'=>0));
            }
            //必填项验证
            if (empty($save['fengge'])) {
                $this->ajaxReturn(array('info'=>'请选择风格属性！','status'=>0));
            }
            //必填项验证
            if (empty($save['huxing'])) {
                $this->ajaxReturn(array('info'=>'请选择户型属性！','status'=>0));
            }
            //必填项验证
            if (empty($save['color'])) {
                $this->ajaxReturn(array('info'=>'请选择颜色属性！','status'=>0));
            }
            //必填项验证
            if (empty($save['tags'])) {
                $this->ajaxReturn(array('info'=>'请填写标签！','status'=>0));
            }
            //必填项验证
            if (empty($save['description'])) {
                $this->ajaxReturn(array('info'=>'请填写描述！','status'=>0));
            }

            foreach ($data['img_id'] as $i => $v) {
                if ($data['img_id'][$i] == $data['img_desc'][$i]['upId']) {
                    //查询是否存在 存在才能更新
                    $in = D('Meitu')->getMeituImgById($data['img_id'][$i]);
                    if($in){
                        $save_data = ['description' => htmlspecialchars(strip_tags(htmlspecialchars_decode($data['img_desc'][$i]['text']), '<a>'))];
                        D('Meitu')->editMeituImg($data['img_id'][$i], $save_data);
                    }else{
                        $data['new_img_desc'][] = htmlspecialchars(strip_tags(htmlspecialchars_decode($data['img_desc'][$i]['text']), '<a>'));
                    }
                }
            }
            $images = [];
            foreach ($data['images'] as $key => $value) {
                $images[] = array(
                    'img_path' => $value,
                    'description' => $data['new_img_desc'][$key],
                    'img_host' => 'qiniu',
                    'px'       => $key+1,
                    'img_on'   => $key == 0 ? 1 : 0,
                    'time'     => time()
                );
            }

            $admin = getAdminUser();

            if(empty($id)){
                //必填项验证，此处需要新增的情况下才判断
                if (empty($images)) {
                    $this->ajaxReturn(array('info'=>'请上传图片！','status'=>0));
                }
                $save["pv"] = rand(1000,2000);//随机文章点击量
                //判断是否是预发布状态，如果是预发布状态，则发布时间改为编辑自定义的时间，否则为当前时间
                //直接发布
                if (true == $this->directPost) {
                    $save['state'] = 1;
                    $save["time"] = time();
                } else {
                    $save['state'] = 2;
                    if (!empty($data['time'])) {
                        $save["time"] = strtotime(date('Y-m-d', strtotime($data['time'])));
                        if ($save["time"] < strtotime(date('Y-m-d'))) {
                            $this->ajaxReturn(array('info'=>'如需填写预发布日期，则该时间不能小于当前日期！','status'=>0));
                        }
                    } else {
                        $save["time"] = 0;
                    }
                }

                $save["uid"] = $admin['id'];
                $save["uname"] = $admin['name'];
                $save['init_state'] = $save['state'];
                $save['createtime'] = time();
                $save['updatetime'] = time();
                $save['likes'] = rand(500,1000);
                $save['is_single'] = count($images) == 1 ? 1 : 0;
                $result = D('Meitu')->addMeitu($save);
                if($result){
                    D('Meitu')->addMeituImages($result, $images);
                    //直接发布时
                    if($save['state'] == 1){
                        //主动推送美图 1是发布 2是预发布 3是未审核
                        $url = 'http://meitu.qizuang.com/p'.$result.'.html';
                        $sent = sentMeituToBaidu($url,false);
                        //将返回值写入数据表 qz_www_article_linksubmit
                        $sent = json_decode($sent,true);
                        $data = [
                                'aid'       => $result,
                                'url'       => $url,
                                'from'      => 1,//来自主站
                                'type'      => 2,//普通推送
                                's_code'    => $sent['success'],
                                'time'      => time()
                            ];
                        D("WwwArticleKeywords")->addLinkSubmit($data);
                        //更改标签数量
                        D('Tags')->editTagCountByTagIds('', $save['tags'], 'meitu_count');
                    }
                    $this->ajaxReturn(array('data'=>'','info'=>'操作成功！','status'=>1));
                }
            } else {
                $meitu = D('Meitu')->getMeituById($id);
                //直接发布，只有预发布的美图才可以直接发布
                if (true == $this->directPost) {
                    if ('2' != $meitu['state']) {
                        $this->ajaxReturn(array('info'=>'只有预发布状态才可以直接发布！','status'=>0));
                    }
                    $save['state'] = 1;
                    //由预发布状态手动改为直接发布，则初始状态也要更改
                    $save['init_state'] = 1;
                    $save['time'] = time();
                } else {
                    if (!empty($data['time'])) {
                        if ('2' != $meitu['state']) {
                            $this->ajaxReturn(array('info'=>'只有预发布状态才可以填写预发布日期！','status'=>0));
                        } else {
                            $save["time"] = strtotime(date('Y-m-d', strtotime($data['time'])));
                            if ($save["time"] < strtotime(date('Y-m-d'))) {
                                $this->ajaxReturn(array('info'=>'如需填写预发布日期，则该日期不能小于当前日期！','status'=>0));
                            }
                        }
                    } else {
                        if ('2' == $meitu['state']) {
                            $save["time"] = 0;
                        }
                    }
                }

                //判断美图图片数量,进行单图套图判断
                if ((count($meitu['images']) + count($images)) > 1) {
                    $save['is_single'] = 0;
                } else {
                    $save['is_single'] = 1;
                }
                $save['updatetime'] = time();
                $save['update_uid'] = $admin['id'];
                $result = D('Meitu')->editMeitu($id, $save);
                if($result){
                    //预发布->发布
                    if ($meitu['state'] == 2 && $save['state'] == 1) {
                        D('Tags')->editTagCountByTagIds('',$save['tags'],'meitu_count');
                    //发布->发布
                    } else if ($meitu['state'] == 1) {
                        D('Tags')->editTagCountByTagIds($meitu['tags'],$save['tags'],'meitu_count');
                    }
                    D('Meitu')->addMeituImages($id, $images);
                    $this->ajaxReturn(array('info'=>'操作成功！','status'=>1));
                }
            }
            $this->ajaxReturn(array('info'=>'操作失败！','status'=>0));
        }

        $id = I('get.id');
        $p = I('get.p');
        if(!empty($id)){
            $info['info'] = D('Meitu')->getMeituById($id);
            //美图图片预览
            if(!empty($info['info']['images'])){
                foreach ($info['info']['images'] as $key => $value) {
                    $info['info']['preview'][] = '<img data-id= '.$value['id'].' class="file-preview-image" src= "http://'.OP('QINIU_DOMAIN').'/'.$value['img_path'].'-slt220" >';
                    $info['info']['previewconfig'][] = ['url' => '/meitu/deletemeituimgbyid/?id=' . $value['id'] . '&key=' . $value['id'], 'text' => strip_tags(html_entity_decode($value['description']),'<a>')];
                }
                $info['info']['preview'] = json_encode($info['info']['preview']);
                $info['info']['previewconfig'] = json_encode($info['info']['previewconfig']);
            }
        }
        //获取设计师推荐列表
        $info["master"] = D("Pubmeitu")->getDesigner();
        //获取美图属性+
        $info['attribute'] = D('Meitu')->getMeituAttribute('', ['enabled' => 1]);
        $this->assign('info',$info);
        $this->assign('p',$p);
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
     * [attribute 美图属性管理，风格户型面积颜色]
     * @return [type] [description]
     */
    public function attribute(){
        $data = I('post.');
        if(!empty($data)){
            $id = $data['id'];
            $type = $data['type'];
            if($type == 'color'){
                $save['color'] = $data['color'];
            }
            $save['name'] = $data['name'];
            $save['title'] = $data['title'];
            $save['keywords'] = $data['keywords'];
            $save['description'] = $data['description'];
            $save['px'] = $data['px'];
            $save['istop'] = $data['istop'];
            $save['enabled'] = $data['enabled'];
            unset($data);
            if(empty($id)){
                $save['time'] = time();
                $result = D('Meitu')->addMeituAttribute($type, $save);
            }else{
                $result = D('Meitu')->saveMeituAttribute($type, $id, $save);
            }
            if($result){
                $this->ajaxReturn(array('data'=>'','info'=>'操作成功！','status'=>1));
            }
            $this->ajaxReturn(array('data'=>'','info'=>'操作失败！','status'=>0));
        }
        $attribute = I('get.attribute');
        $info['attribute'] = $attribute;
        $info['info'] = D('Meitu')->getMeituAttribute($attribute);
        $this->assign('info',$info);
        $this->display();
    }

    public function subWebsite(){
        $info = $this->getRecommend();
        $data = I('post.');
        if(!empty($data)){
            $save = [];
            $save['img'] = $data['img'];
            $save['type'] = $data['type'];
            if($data['type'] == 1){
                $explode = array_filter(explode('|', $data['title']));
                $save['id'] = $explode[0];
                $save['title'] = $explode[1];
            }else{
                $save['id'] = $save['img'];
                $save['title'] = $data['title'];
            }
            $save['order'] = $data['order'];
            $save['add_time'] = time();
            $subdata = unserialize(D('Options')->getOptionNoCache('subhome_meitu_dist')['option_value']);
            $old = $subdata;
            if(empty($data['id'])){
                if($save['type'] == 1 && count($info['imrecommend']) < 12){
                    $subdata[] = $save;
                }
                if($save['type'] == 2 && count($info['wdrecommend']) < 12){
                    $subdata[] = $save;
                }
            }else{
                $id = $data['id'];
                foreach ($subdata as $key => $value) {
                    if($value['type'] == $save['type'] && $value['id'] == $id){
                        $subdata[$key] = $save;
                    }
                }
            }
            if($subdata != $old){
                $result = D('Options')->setOption('subhome_meitu_dist',serialize($subdata));
                if($result){
                    $this->ajaxReturn(array('data'=>'','info'=>'操作成功！','status'=>1));
                }
            }
            $this->ajaxReturn(array('data'=>'','info'=>'操作失败！','status'=>0));
        }

        $this->assign('info',$info);
        $this->display();
    }


    public function recommendimage(){
        $id = I('get.id');
        $result = $this->getRecommend()['imrecommend'];
        foreach ($result as $key => $value) {
            if($id == $value['id']){
                $info['info'] = $value;
            }
        }
        if(empty($info)){
            $this->error();
        }
        $this->assign('info',$info);
        $this->display();
    }


    public function recommendword(){
        $id = I('get.id');
        $result = $this->getRecommend()['wdrecommend'];
        foreach ($result as $key => $value) {
            if($id == $value['id']){
                $info['info'] = $value;
            }
        }
        if(empty($info)){
            $this->error();
        }
        $this->assign('info',$info);
        $this->display();
    }

    private function getRecommend(){
        $subdata = unserialize(D('Options')->getOptionNoCache('subhome_meitu_dist')['option_value']);
        foreach ($subdata as $key => $value) {
            switch ($value['type']) {
                case '1':
                    $imrecommend[] = $value;
                    break;
                case '2':
                    $wdrecommend[] = $value;
                    break;
                default:
                    break;
            }
        }
        $result = ['imrecommend' => $imrecommend, 'wdrecommend' => $wdrecommend];
        return $result;
    }

    /**
     * [deleteMeituImgById 通过美图中的图片id删除美图图片]
     * @return [type] [description]
     */
    public function deleteMeituImgById(){
        $id = I('get.id');
        if(!empty($id)){
            //取出图片记录
            $info = D('Meitu')->getMeituImgById($id);
            //取出该图片美图记录
            $meitu = D('Meitu')->getMeituById($info['caseid']);
            //删除图片
            $result = D('Meitu')->deleteMeituImgById($id);
        }
        if($result){
            //判断删除后的图片数量 1：单图 大于1：套图
            //备注：此处可能会因为主从数据库延迟造成数量查询问题
            if ((count($meitu['images']) -1 ) > 1) {
                $save = array('is_single' => 0);
            } else {
                $save = array('is_single' => 1);
            }

            //如果状态不一致，更改状态
            if ($meitu['is_single'] != $save['is_single']) {
                D('Meitu')->editMeitu($meitu['id'], $save);
            }

            $log = array(
                'remark' => '删除家居美图图片！',
                'logtype' => 'meitu_delimgbyimgid',
                'action_id' => $meitu['id'],
                'info' => $result
            );
            D('LogAdmin')->addLog($log);
            $this->ajaxReturn(array('data'=>'','info'=>'操作成功！','status'=>1));
        }
        $this->ajaxReturn(array('data'=>'','info'=>'操作失败！','status'=>0));
    }

    /**
     * [deleteMeituById 通过id删除美图]
     * @return [type] [description]
     */
    public function deleteMeituById(){
        $id = I('get.id');
        $meitu = D('Meitu')->getMeituById($id);
        $result = D('Meitu')->deleteMeituById($id);
        if($result){
            //处理标签
            D('Tags')->editTagCountByTagIds($meitu['tags'],'','meitu_count');
            $this->ajaxReturn(array('data'=>'','info'=>'操作成功！','status'=>1));
        }
        $this->ajaxReturn(array('data'=>'','info'=>'操作失败！','status'=>0));
    }

    public function editMeituState(){
        $id = I('post.id');
        $state = I('post.state');
        if(in_array($state, array('1','3'))){
            $meitu = D('Meitu')->getMeituById($id);
            $save['state'] = $state;
            $result = D('Meitu')->editMeitu($id,$save);
            if($result){
                //原来状态可见改为不可见，需要减少标签数量
                if($meitu['state'] == 1 && $state == 3){
                    D('Tags')->editTagCountByTagIds($meitu['tags'],'','meitu_count');
                }
                //原来状态不可见改为可见，需要增加标签数量
                if($meitu['state'] == 3 && $state == 1){
                    D('Tags')->editTagCountByTagIds('',$meitu['tags'],'meitu_count');
                }
                if($state == 1){
                    //此处为通过审核
                    $url = 'http://meitu.qizuang.com/p'.$result.'.html';
                    $sent = sentURLToBaidu($url,false);

                    //将返回值写入数据表 qz_www_article_linksubmit
                    $sent = json_decode($sent,true);
                    $now_time = time();
                    $cache_time = $end_time - $now_time;
                    S('Cache:sent:wwwarticle:normal',$sent['remain'],$cache_time);//主动剩余数量
                    $data = [
                            'aid'       => $result,
                            'url'       => $url,
                            'from'      => 1,//来自主站
                            'type'      => 2,//普通推送
                            's_code'    => $sent['success'],
                            'time'      => time()
                        ];
                    D("WwwArticleKeywords")->addLinkSubmit($data);
                }
                $this->ajaxReturn(array('data'=>'','info'=>'操作成功！','status'=>1));
            }
            $this->ajaxReturn(array('data'=>'','info'=>'操作失败！','status'=>0));
        }
        $this->ajaxReturn(array('data'=>'','info'=>'参数错误，操作失败！','status'=>0));
    }

    //获取列表并分页
    public function getMeituList($params,$pageIndex=1,$pageCount = 16,$keyword,$state)
    {
        $count = D('Meitu')->getMeituCount($params,$keyword,$state);

        if($count > $pageCount){
            import('Library.Org.Util.Page');
            $page = new \Page($count,$pageCount);
            $result['page'] =  $page->show();
            $pageIndex = $page->nowPage;
        }

        $result['list']=D('Meitu')->getMeituList($params,($pageIndex - 1)*$pageCount,$pageCount,$keyword,$state);
        foreach ($result['list'] as $key => $value) {
            $result['list'][$key]["tagname"] = str_replace(",", " ", $value["tagname"]);
        }
        return $result;
    }

    /**
     * [meituthreedimensional 3D效果图列表]
     * @return [type] [description]
     */
    public function threedimensionallist(){

        $fengge = intval(I('get.fengge'));
        $huxing = intval(I('get.huxing'));
        $adminuser_id = intval(I('get.adminuser_id'));
        $condition = I('get.condition');
        if (is_numeric($condition)) {
            $id = intval($condition);
        } else {
            $title = trim($condition);
        }
        $start = I('get.p') > 0 ? intval(I('get.p')) : 1;
        $each = 20;

        $count = D('XiaoguotuThreedimension')->getCount($id, $title, $fengge, $huxing, $adminuser_id);

        if($count > $each){
            import('Library.Org.Util.Page');
            $page = new \Page($count,$each);
            $vars['page'] =  $page->show();
            $start = $page->nowPage;
        }

        $vars['info'] = D('XiaoguotuThreedimension')->getList($id, $title, $fengge, $huxing, $adminuser_id, ($start - 1)*$each,$each);
        $vars['category'] = M('xiaoguotu_threedimension_category')->where(array(
            'status' => 1
        ))->select();
        $vars['adminuser'] = D('Adminuser')->getAdminuserListByUid(26);
        $this->assign('vars', $vars);
        $this->display();
    }

    /**
     * [meituthreedimensional 3D效果图风格管理]
     * @return [type] [description]
     */
    public function meituthreedimensionalstyle(){
        $data['type'] = 1;
        $data['status'] = 1;
        $stylelist = M('xiaoguotu_threedimension_category')->where($data)->select();
        $this->assign('style',$stylelist);
    	$this->display();
    }

    public function meituthreedimensionalstyleadd(){
        if($_POST){
            if($_POST['id']){
                $styleid['id'] = $_POST['id'];
                $data['name'] = $_POST['name'];
                $data['sort'] = $_POST['sort'];
                $data['recommend'] =$_POST['recommend'];
                M('xiaoguotu_threedimension_category')->where($styleid)->save($data);
            }else{
                $data['type'] = 1;
                $data['status'] =1;
                $data['add_time'] = date("Y-m-d h:i:s");
                $data['name'] = $_POST['name'];
                $data['sort'] = $_POST['sort'];
                $data['recommend'] =$_POST['recommend'];
                M('xiaoguotu_threedimension_category')->add($data);
            }
        }
    }

    public function meituthreedimensionalstyledelete(){
        if($_POST){
            $deptid['id'] = $_POST['id'];
            $data['status'] = 2;
            M('xiaoguotu_threedimension_category')->where($deptid)->save($data);
        }
    }


        /**
     * [meituthreedimensional 3D效果图户型管理]
     * @return [type] [description]
     */
    public function meituthreedimensionallayout(){
        $data['type'] = 2;
        $data['status'] = 1;
        $stylelist = M('xiaoguotu_threedimension_category')->where($data)->select();
        $this->assign('style',$stylelist);
     	$this->display();
    }

    /**
     * 3D效果图新增与编辑
     */
    public function threedimensionaloperate(){
        if (IS_POST) {
            $id = I('post.id');
            $save = array(
                'title'        => I('post.title'),
                'tags'         => D('Tags')->getTagIdsByTagNames(I('post.tagnames')),
                'path'         => I('post.path'),
                'fengge'       => I('post.fengge'),
                'huxing'       => I('post.huxing'),
            );

            $imgs = I('post.imgs');
            foreach ($imgs as $key => $value) {
                if ($value["name"] == "bg") {
                    $save['face'] = $value["key"];
                    unset($imgs[$key]);
                }
            }

            if (count($imgs) != 7) {
                $this->ajaxReturn(array('status' => 0, 'info' => '效果图数量错误,请重新上传！'));
            }

            if (empty($id)) {
                $save['create_time'] = date('Y-m-d H:i:s');
                //直接发布
                if (true == $this->directPostThreedimensional) {
                    $save['publish_time'] = date('Y-m-d H:i:s');
                    $save['status'] = 1;
                } else {
                    $publish_time = I('post.publish_time');
                    if (empty($publish_time)) {
                        $save['publish_time'] = array('exp', 'null');
                    } else {
                        $save["publish_time"] = date('Y-m-d 00:00:00', strtotime(I('post.publish_time')));
                        if (strtotime($save["publish_time"]) < strtotime(date('Y-m-d'))) {
                            $this->ajaxReturn(array('info'=>'如需填写预发布日期，则该日期不能小于当前日期！','status'=>0));
                        }
                    }
                    $save['status'] = 3;
                }
                $save['init_status'] = $save['status'];
                $save['adminuser_id'] = getAdminUser('id');

                $xiaoguotu_threedimension_id = D('XiaoguotuThreedimension')->insertThreedimension($save);
                if ($xiaoguotu_threedimension_id) {
                    $save = array();
                    foreach ($imgs as $key => $value) {
                        $save[] = array(
                            'xiaoguotu_threedimension_id' => $xiaoguotu_threedimension_id,
                            'url' => $value["key"],
                            'sort' => ($key + 1)
                        );
                    }
                    D('XiaoguotuThreedimension')->insertThreedimensionImg($save);
                    $this->ajaxReturn(array('status'=>1, 'info' => '新增成功'));
                }
            } else {
                $info = D('XiaoguotuThreedimension')->getThreedimensionById($id);
                $publish_time = I('post.publish_time');
                //直接发布，只有预发布的美图才可以直接发布
                if (true == $this->directPostThreedimensional) {
                    if ('3' != $info['status']) {
                        $this->ajaxReturn(array('info'=>'只有预发布状态才可以直接发布！','status'=>0));
                    }
                    if (!empty($publish_time)) {
                        $this->ajaxReturn(array('info'=>'直接发布的预发布日期需为空！','status'=>0));
                    }
                    $save['status'] = 1;
                    $save['publish_time'] = date('Y-m-d H:i:s');
                    //由预发布状态手动改为直接发布，则初始状态也要更改
                    $save['init_status'] = '1';
                } else {
                    if (!empty($publish_time)) {
                        if ('3' != $info['status']) {
                            $this->ajaxReturn(array('info'=>'只有预发布状态才可以填写预发布日期！','status'=>0));
                        } else {
                            $save["publish_time"] = date('Y-m-d 00:00:00', strtotime($publish_time));
                            if (strtotime($save["publish_time"]) < strtotime(date('Y-m-d'))) {
                                $this->ajaxReturn(array('info'=>'如需填写预发布日期，则该日期不能小于当前日期！','status'=>0));
                            }
                        }
                    } else {
                        if ('3' == $info['status']) {
                            $save["publish_time"] = array('exp', 'null');
                        }
                    }
                }

                $save['update_uid'] = getAdminUser('id');
                $save['update_time'] = date('Y-m-d H:i:s');

                D('XiaoguotuThreedimension')->updateThreedimension($id, $save);
                $result = D('XiaoguotuThreedimension')->getThreedimensionImgByXgtId($id);
                if (count($result) == 0) {
                   foreach ($imgs as $key => $value) {
                        $sub[] = array(
                            'xiaoguotu_threedimension_id' => $id,
                            'url' => $value["key"],
                            'sort' => ($key + 1)
                        );
                    }
                    D('XiaoguotuThreedimension')->insertThreedimensionImg($sub);
                }
                $this->ajaxReturn(array('status'=>1, 'info' => '编辑成功'));
            }
            $this->ajaxReturn(array('status' => 0, 'info' => '操作失败'));
        }

        $vars['category'] = M('xiaoguotu_threedimension_category')->where(array(
            'status' => 1
        ))->select();

        $id = I('get.id');
        if (!empty($id)) {
            $info = D('XiaoguotuThreedimension')->getThreedimensionById($id);
            if (!empty($info)) {
                $info['tagname'] = array_filter(explode(',', $info['tagname']));
                $updata_time = strtotime($info['update_time']);
                $preview[] =  '<img class="file-preview-image" src= "http://'.OP('QINIU_DOMAIN').'/'.$info['face'].'-w660.jpg?updata_time='. $updata_time . '" data-url="' . $value['url'] . '">';

                $previewconfig[] = ['url' => '/meitu/fileinputremovefile/?id='.$info['id']];
                $imgs['preview'] = json_encode($preview);
                $imgs['previewconfig'] = json_encode($previewconfig);

                $result = D('XiaoguotuThreedimension')->getThreedimensionImgByXgtId($info['id']);

                foreach ($result as $key => $value) {
                    $info["imgs"][] = array("key"=>$value["url"]);
                }
                $info["imgs"][] = array(
                    "name" => "bg",
                    "key" => $info['face']
                );
                $info["imgs"] = json_encode($info["imgs"]);
            }
        } else {
            $info['path'] = date('YmdHis') . rand(100000, 999999);
        }
        $vars['info'] = $info;
        $vars['imgs'] = $imgs;
        $this->assign('vars', $vars);
        $this->display();
    }

    /**
     * 直接发布3D效果图
     */
    public function directPostThreedimensional()
    {
        $this->directPostThreedimensional = true;
        $this->threedimensionaloperate();
    }

    public function meituthreedimensionallayoutadd(){
        if($_POST){
            if($_POST['id']){
                $styleid['id'] = $_POST['id'];
                $data['name'] = $_POST['name'];
                $data['sort'] = $_POST['sort'];
                $data['recommend'] =$_POST['recommend'];
                M('xiaoguotu_threedimension_category')->where($styleid)->save($data);
            }else{
                $data['type'] = 2;
                $data['status'] =1;
                $data['add_time'] = date("Y-m-d h:i:s");
                $data['name'] = $_POST['name'];
                $data['sort'] = $_POST['sort'];
                $data['recommend'] =$_POST['recommend'];
                M('xiaoguotu_threedimension_category')->add($data);
            }
        }
    }

    public function meituthreedimensionallayoutdelete(){
        if($_POST){
            $deptid['id'] = $_POST['id'];
            $data['status'] = 2;
            M('xiaoguotu_threedimension_category')->where($deptid)->save($data);
        }
    }

    /**
     * 3D效果图-删除图片文件
     */
    public function fileinputremovefile()
    {
        $id = I('get.id');

        if (!empty($id)) {
            //清除案例的封面
            $data = array(
                "face" => ""
            );
            D('XiaoguotuThreedimension')->updateThreedimension($id,$data);
            $result = D('XiaoguotuThreedimension')->deleteThreedimensionImg($id);
            if ($result) {
                $this->ajaxReturn(array('status'=>1));
            }
        }
        $this->ajaxReturn(array('status'=>0, 'info' => '删除失败~'));
    }

    /**
     * 删除3D效果图
     */
    public function threedimensionaldelete()
    {
        $id = I('post.id');
        if (!empty($id)) {
            $save = array(
                'status' => 2
            );
            D('XiaoguotuThreedimension')->updateThreedimension($id, $save);
            $this->ajaxReturn(array('status'=>1,  'info' => '删除成功'));
        }
        $this->ajaxReturn(array('status' => 0, 'info' => '非法请求'));
    }

    //获取家居美图的标题
    public function getJiajumeituapi($title="",$id){
        if(empty($title)){
            $tags = D('Meitu')->getJiajumeituByName($_GET['key']);
        }else{
            $tags = D('Meitu')->getJiajumeituByName($title, '');
        }

       /* if(empty($tags)){
            $tags = D('Meitu')->getJiajumeituByName($title);
        }*/
        $data = array();
        foreach ($tags as $k => $v) {
            if($v['id'] != $id){
                $data[] = array(
                    'id' => $v['id'],
                    'text' => $v['title']
                );
            }
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

    //分站首页效果图推荐(文字推荐)
    public function subWebEffect(){
        $tab = I('get.tab');

        if ($tab == 1) {
            $cityList = D("Quyu")->getAllQuyuOnly("cid,cname");
            $cs = I("get.cs");
            if (empty($cs)) {
                $cs = $cityList[0]["cid"];
            }

            // 分站效果图推荐
            $list = D("SubhomeEffect")->getTypeList(2, $cs);
            $this->assign("cityList", $cityList);
            $this->assign("cs", $cs);
        } else {
            $info = $this->getSubWebRecommend();
            $list = $info["wdrecommend"];
            foreach ($list as $key => $item) {
                if (empty($item["updated_at"])) {
                    $list[$key]["updated_at"] = $item["add_time"];
                }
            }
        }

        $this->assign("list", $list);
        $this->display();
    }

    /**
     * 分站首页效果图推荐
     * @return [type] [description]
     */
    public function subwebtu(){
        $tab = I('get.tab');
        if ($tab == 1) {
            $cityList = D("Quyu")->getAllQuyuOnly("cid,cname");
            $cs = I("get.cs");
            if (empty($cs)) {
                $cs = $cityList[0]["cid"];
            }

            // 分站效果图推荐
            $list = D("SubhomeEffect")->getTypeList(1, $cs);
            $this->assign("cityList", $cityList);
            $this->assign("cs", $cs);
        } else {
            $info = $this->getSubWebRecommend();
            $list = $info["imrecommend"];
            foreach ($list as $key => $item) {
                if (empty($item["updated_at"])) {
                    $list[$key]["updated_at"] = $item["add_time"];
                }
            }
        }

        $this->assign("list", $list);
        $this->display();
    }

    //分站首页效果图推荐编辑
    public function subWebEffectEdit(){
        $data = I('post.');
        if(!empty($data)){
            $save = [];
            $save['img'] = I("img", "");
            $save['url'] = $data['url'];
            $save['type'] = $data['type'];
            $save['title'] = $data['title'];
            $save['order'] = $data['order'];
            $save['updated_at'] = time();

            if (empty($data["cs"])) {
                $this->subWebEffectEditMain($data["id"], $save);
            } else {
                $this->subWebEffectEditSub($data["id"], $save, $data["cs"]);
            }
        }

        $this->ajaxReturn(array('data'=>'','info'=>'操作失败！','status'=>0));
    }

    // 分站首页效果图推荐编辑（分站推荐）
    private function subWebEffectEditSub($id, $save, $cs){
        $list = D("SubhomeEffect")->getTypeList($save['type'], $cs);

        $save['cs'] = $cs;
        if(empty($id)){
            $save['created_at'] = time();
            foreach ($list as $key => $item) {
                if($item['title'] == $save['title']){
                    $this->ajaxReturn(array('data'=>'','info'=>'标题不能重复！','status'=>0));
                }
            }

            if($save['type'] == 1 && count($list) >= 8){
                $this->ajaxReturn(array('data'=>'','info'=>'美图图片推荐最多为8个~','status'=>0));
            }

            if($save['type'] == 2 && count($list) >= 6){
                $this->ajaxReturn(array('data'=>'','info'=>'美图文字推荐最多为6个~','status'=>0));
            }

            $result = M("subhome_effect")->add($save);
        } else {
            $save['id'] = $id;
            foreach ($list as $key => $item) {
                if($item['title'] == $save['title'] && $item['id'] != $save['id']){
                    $this->ajaxReturn(array('data'=>'','info'=>'标题不能重复！','status'=>0));
                }

                if($item['order'] == $save['order'] && $item['id'] != $save['id']){
                    $this->ajaxReturn(array('data'=>'','info'=>'排序不能重复！','status'=>0));
                }
            }
            $result = M("subhome_effect")->save($save);
        }
        
        if (!empty($result)) {
            $this->ajaxReturn(array('data'=>'','info'=>'操作成功！','status'=>1));
        }

        $this->ajaxReturn(array('data'=>'','info'=>'操作失败！','status'=>0));
    }

    // 分站首页效果图推荐编辑（总推荐）
    private function subWebEffectEditMain($id, $save){
        $subdata = unserialize(D('Options')->getOptionNoCache('subhome_effect_meitu_dist')['option_value']);
        $countType = array_count_values(array_column($subdata, "type"));
        $old = $subdata;

        if(empty($id)){
            $save['add_time'] = time();
            foreach ($subdata as $key => $value) {
                if($value['title'] == $save['title'] && $value['type'] == $save['type']){
                    $this->ajaxReturn(array('data'=>'','info'=>'标题不能重复！','status'=>0));
                }
            }

            if($save['type'] == 1 && $countType[1] >= 8){
                $this->ajaxReturn(array('data'=>'','info'=>'美图图片推荐最多为8个~','status'=>0));
            }

            if($save['type'] == 2 && $countType[2] >= 6){
                $this->ajaxReturn(array('data'=>'','info'=>'美图文字推荐最多为6个~','status'=>0));
            }

            $string = new \Org\Util\Stringnew;
            $save['id'] = $string::uuid();
            $subdata[] = $save;
        }else{
            $save['id'] = $id;
            foreach ($subdata as $key => $value) {
                if($value['type'] == $save['type'] && $value['id'] == $save['id']){
                    $subdata[$key] = $save;
                    $subdata[$key]['add_time'] = $value['add_time'];
                }else if($value['title'] == $save['title'] && $value['type'] == $save['type']){
                    $this->ajaxReturn(array('data'=>'','info'=>'标题不能重复！','status'=>0));
                }else if($value['order'] == $save['order'] && $value['type'] == $save['type']){
                    $this->ajaxReturn(array('data'=>'','info'=>'排序不能重复！','status'=>0));
                }
            }
        }

        if($subdata != $old){
            if($old == false){
                $map['option_name']= "subhome_effect_meitu_dist";
                $map['option_value']= serialize($subdata);
                $map['option_group']= "ask";
                $map['option_remark']= "分站首页效果图";
                $result = D('Options')->addOption($map);
                if($result){
                    $this->ajaxReturn(array('data'=>'','info'=>'操作成功！','status'=>1));
                }
            }else{
                $result = D('Options')->setOption('subhome_effect_meitu_dist',serialize($subdata));
                if($result){
                    $this->ajaxReturn(array('data'=>'','info'=>'操作成功！','status'=>1));
                }
            }
        }

        $this->ajaxReturn(array('data'=>'','info'=>'操作失败！','status'=>0));
    }

    // 新友情链接
    public function newfriendly(){
        $this->display();
    }
    
    // 美图推荐编辑
    public function newimgup(){
        $id = I('get.id');
        $cs = I('get.cs');

        if (!empty($cs)) {
            $list = D("SubhomeEffect")->getTypeList(1, $cs);
        } else {
            $list = $this->getSubWebRecommend()['imrecommend'];
        }

        foreach ($list as $key => $value) {
            if($id == $value['id']){
                $info = $value;
            }
        }

        $this->assign('info', $info);
        $this->assign('count', count($list) + 1);
        $this->display();
    }

    // 文字推荐编辑
    public function newwordup(){
        $id = I('get.id');
        $cs = I('get.cs');

        if (!empty($cs)) {
            $list = D("SubhomeEffect")->getTypeList(2, $cs);
        } else {
            $list = $this->getSubWebRecommend()['wdrecommend'];
        }

        foreach ($list as $key => $value) {
            if($id == $value['id']){
                $info = $value;
            }
        }

        $this->assign('info', $info);
        $this->assign('count', count($list) + 1);
        $this->display();
    }

    private function getSubWebRecommend(){
        $subdata = unserialize(D('Options')->getOptionNoCache('subhome_effect_meitu_dist')['option_value']);
        foreach ($subdata as $key => $value) {
            switch ($value['type']) {
                case '1':
                    $imrecommend[] = $value;
                    break;
                case '2':
                    $wdrecommend[] = $value;
                    break;
                default:
                    break;
            }
        }
        $result = ['imrecommend' => $imrecommend, 'wdrecommend' => $wdrecommend];
        return $result;
    }

    /**
     * 单个/批量重新发布
     * 勾选的多条记录重新发布，创建时间以及发布时间更新为当前日期
     */
    public function multiPublish()
    {
        try {
            $ids = I('post.ids');
            if (empty($ids)) {
                throw new Exception('参数有误', ApiConfig::PARAMETER_ILLEGAL);
            }

            //更新 创建时间以及发布时间更新为当前日期

            $this->meituLogic->multiPublish($ids);

            $code = 0;
            $msg = 'ok';
        } catch (Exception $e) {
            $code = $e->getCode();
            $msg = $e->getMessage();
        }
        $ret = [
            'code' => $code,
            'msg' => $msg,
        ];
        $this->ajaxReturn($ret);
    }

    /**
     * 重新存储
     */
    public function restore()
    {
        try {
            $ids = I('post.ids');
            if (empty($ids)) {
                throw new Exception('参数有误', ApiConfig::PARAMETER_ILLEGAL);
            }

            //更新 创建时间以及发布时间更新为当前日期

            $this->meituLogic->restore($ids);

            $code = 0;
            $msg = 'ok';
        } catch (Exception $e) {
            $code = $e->getCode();
            $msg = $e->getMessage();
        }
        $ret = [
            'code' => $code,
            'msg' => $msg,
        ];
        $this->ajaxReturn($ret);
    }
}