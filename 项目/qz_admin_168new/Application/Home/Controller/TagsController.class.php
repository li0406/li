<?php
namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;

class TagsController extends HomeBaseController{

    /**
     * [index 标签列表]
     * @return [type] [description]
     */
    public function index()
    {
        $data = I('get.');
        $keyword = $data['condition'];
        if(!empty($data['ordertype'])){
            if(!empty($data['ordervalue']) && $data['ordervalue'] == 1){
                $order = $data['ordertype'];
            }else{
                $order = $data['ordertype']. ' DESC';
            }
        }
        //此处不能使用empty判断
        if($data['istop'] != ''){
            $istop = $data['istop'];
        }
        $type = $data['type'];
        $pageIndex = 1;
        if(!empty($data['p'])){
            $pageIndex = $data['p'];
        }
        $info['info'] = $this->getTagsList($keyword, $istop, $type, $order,$data["location"], $pageIndex);
        $this->assign('info',$info);
        $this->display();
    }

    //获取列表并分页
    private function getTagsList($keyword, $istop, $type, $order,$location, $pageIndex=1,$pageCount = 16)
    {
        $count = D('Tags')->getTagsCount($keyword, $istop ,$type,$location);
        $result['list']=D('Tags')->getTagsList(($pageIndex-1)*$pageCount, $pageCount, $keyword, $istop, $type, $order,$location);

        if($count > $pagecount){
            import('Library.Org.Util.Page');
            $page = new \Page($count,$pageCount);
            $result['page'] =  $page->show();
        }
        return $result;
    }

    public function downloadTags(){
        $option = array(
            'article' => array('name' => '攻略标签', 'url' => 'http://'.C('QZ_YUMINGWWW').'/tags/gonglue'),
            'meitu'   => array('name' => '美图标签', 'url' => 'http://meitu.'.C('QZ_YUMING').'/tags/meitu'),
            'diary'   => array('name' => '日记标签', 'url' => 'http://'.C('QZ_YUMINGWWW').'/tags/riji'),
            'ask'     => array('name' => '问答标签', 'url' => 'http://'.C('QZ_YUMINGWWW').'/tags/wenda'),
            'baike'   => array('name' => '百科标签', 'url' => 'http://'.C('QZ_YUMINGWWW').'/tags/baike'),
        );

        $type = trim(I('get.type'));
        if (empty($option[$type])) {
            $this->error('非法请求');
        }
        $condition = trim(I('get.condition'));
        $istop = trim(I('get.istop'));

        $map = array();
        if (!empty($condition)) {
            $map["name"] = array("LIKE","%$condition%");
        }
        if($istop !== ''){
            $map["istop"] = $istop;
        }

        $chose = $type . '_count';
        $result = M("tags")->field("id,name,istop,time," . $chose)->where($map)->select();
        //导出数据
        import('Library.Org.PHP_XLSXWriter.xlsxwriter');
        try {
            $writer = new \XLSXWriter();

            //标题
            $herder = array(
                '标签名',
                '添加时间',
                '标签调用次数',
                '标签对应的URL地址',
                '是否推荐'
            );
            $wArr = array_values($herder);
            $writer->writeSheetRow('Sheet1', $herder);

            //数据
            foreach ($result as $key => $value) {
                $v = array(
                    $value['name'],
                    date('Y-m-d H:i:s', $value['time']),
                    $value[$chose],
                    $option[$type]['url'] . $value['id'],
                    '1' == $value['istop'] ? '是' : '否'
                );
                $wArr = array_values($v);
                $writer->writeSheetRow('Sheet1', $v);
            }
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");;
            header('Content-Disposition:attachment;filename="'.$option[$type]['name'].'-'.date('Ymd-His').'.xlsx"');
            header("Content-Transfer-Encoding:binary");
            $writer->writeToStdOut("php://output");
        }catch (Exception $e){
            if($_SESSION["uc_userinfo"]["uid"] == 1){
                var_dump($e);
            }
        }
    }

    /**
     * [getTagsApi 获取标签api]
     * @return [type] [description]
     */
    public function getTagsApi(){
        if(!empty($_GET['key'])){
            $tags = D('Tags')->getTagsByName($_GET['key']);
            $data = array();
            foreach ($tags as $k => $v) {
                $data[] = array(
                                'id' => $v['id'],
                                'text' => $v['name']
                                );
            }
            $this->ajaxReturn(array('data' => $data, 'info' => '获取成功！', 'status' =>1));
        }
        $this->ajaxReturn(array('data' => '', 'info' => '获取失败！', 0));
    }


    /**
     *  [getBiaotiApi 获取标题api]
     *  @return [type] [description]
     */
    public function getBiaotiApi(){
            $tags = D('Pubmeitu')->getBiaotiByName($_GET['key']);
            $data = array();
            foreach ($tags as $k => $v) {
                $data[] = array(
                    'id' => $v['id'],
                    'text' => $v['title']
                );
            }
            if(empty($data)){
                $this->ajaxReturn(array('data' => '', 'info' => '获取失败！', 'status' =>0));
            }
            $this->ajaxReturn(array('data' => $data, 'info' => '获取成功！', 'status' =>1));

    }


    /**
     * [operate 新增编辑标签]
     * @return [type] [description]
     */
    public function operate()
    {
        $data = I('post.');
        if(!empty($data)){
            $id = $data['id'];
            $save['name'] = $data['name'];
            $save['istop'] = $data['istop'];
            if(empty($id)){
                //判断标签是否存在
                if (true == D('Tags')->judgeTagExistByName($save['name'])) {
                    $this->ajaxReturn(array('data' => '', 'info' => '该标签已存在！', 'status' =>0));
                }
                $save['time'] = time();
                $result = D('Tags')->addTags($save);
                if($result){
                    $this->ajaxReturn(array('data' => $data, 'info' => '操作成功！', 'status' =>1));
                }
            }else{
                $result = D('Tags')->editTags($id, $save);
                if($result){
                    $this->ajaxReturn(array('data' => $data, 'info' => '操作成功！', 'status' =>1));
                }
            }
            $this->ajaxReturn(array('data' => '', 'info' => '操作失败！', 'status' =>0));
        }
        $id = I('get.id');
        $info['info'] = D('tags')->getTagsById($id);
        $this->assign('info',$info);
        $this->display();
    }

    /**
     * [deleteTags 删除标签]
     * @return [type] [description]
     */
    public function deleteTags()
    {
        $id = I('get.id');
        if(!empty($id)){
            $result = D('Tags')->deleteTagsByIds($id);
            if($result){
                $this->ajaxReturn(array('data' => $data, 'info' => '操作成功！', 'status' =>1));
            }
        }
        $this->ajaxReturn(array('data' => '', 'info' => '操作失败！', 'status' =>0));
    }

    /**
     * [deleteAllTags 批量删除]
     * @return [type] [description]
     */
    public function deleteAllTags()
    {
        $ids = I('get.ids');
        if(!empty($ids)){
            $result = D('Tags')->deleteTagsByIds($ids);
            if($result){
                $this->ajaxReturn(array('data' => $data, 'info' => '操作成功！', 'status' =>1));
            }
        }
        $this->ajaxReturn(array('data' => '', 'info' => '操作失败！', 'status' =>0));
    }

    /**
     * [editIstop 是否推荐]
     * @return [type] [description]
     */
    public function editIstop()
    {
        $id = I('post.id');
        $istop = I('post.istop');
        if(!empty($id) && in_array($istop,array('0','1'))){
            $save['istop'] = $istop;
            $result = D('Tags')->editTags($id, $save);
            if($result){
                $this->ajaxReturn(array('data' => $data, 'info' => '操作成功！', 'status' =>1));
            }
        }
        $this->ajaxReturn(array('data' => '', 'info' => '操作失败！', 'status' =>0));
    }

    /**
     * [count 统计标签数量]
     * @return [type] [description]
     */
    public function count()
    {
        $index = I('get.index');
        $type = I('get.type');
        if(empty($index)){
            $index = 0;
        }
        $result = M('tags')->field('id')->where(array('id' =>array('GT', $index)))->order('id')->limit(1)->find();
        if(!empty($result)){
            $id = $result['id'];
            $index = $id;
            if(empty($type)){
                //替换主站文章表的数据qz_www_article tags
                $data['article_count'] = M('www_article')->where('find_in_set('.$id.',tags)')
                                                         ->where(array('state' => 2))
                                                         ->count();

                //替换美图表的数据qz_meitu tags
                $data['meitu_count'] = M('meitu')->where('find_in_set('.$id.',tags)')
                                                 ->count();

                //替换日记表的数据qz_diary_info tags
                $data['diary_count'] = M('diary_info')->where('find_in_set('.$id.',tags)')
                                                      ->where(array('stat' => 1))
                                                      ->count();

                //替换问答表的数据qz_ask tags
                $data['ask_count'] = M('ask')->where('find_in_set('.$id.',tags)')
                                             ->where(array('visible' => 0))
                                             ->count();

                //替换分站文章表的数据qz_little_article tags
                $data['subarticle_count'] = M('little_article')->where('find_in_set('.$id.',tags)')
                                                               ->where(array('state' => 2))
                                                               ->count();

                //替换百科表的数据qz_baike tags
                $data['baike_count'] = M('baike')->where('find_in_set('.$id.',tags)')
                                                 ->where(array('remove' => '0','visible'=>'0'))
                                                 ->count();
            }else{
                switch ($type) {
                    case '1':
                        //替换主站文章表的数据qz_www_article tags
                        $data['article_count'] = M('www_article')->where('find_in_set('.$id.',tags)')
                                                                 ->where(array('state' => 2))
                                                                 ->count();
                        break;

                    case '2':
                        //替换美图表的数据qz_meitu tags
                        $data['meitu_count'] = M('meitu')->where('find_in_set('.$id.',tags)')
                                                         ->count();
                        break;

                    case '3':
                        //替换日记表的数据qz_diary_info tags
                        $data['diary_count'] = M('diary_info')->where('find_in_set('.$id.',tags)')
                                                              ->where(array('stat' => 1))
                                                              ->count();
                        break;

                    case '4':
                        //替换问答表的数据qz_ask tags
                        $data['ask_count'] = M('ask')->where('find_in_set('.$id.',tags)')
                                                     ->where(array('visible' => 0))
                                                     ->count();
                        break;

                    case '5':
                        //替换分站文章表的数据qz_little_article tags
                        $data['subarticle_count'] = M('little_article')->where('find_in_set('.$id.',tags)')
                                                                       ->where(array('state' => 2))
                                                                       ->count();
                        break;

                    case '6':
                        //替换百科表的数据qz_baike tags
                        $data['baike_count'] = M('baike')->where('find_in_set('.$id.',tags)')
                                                         ->where(array('remove' => '0','visible'=>'0'))
                                                         ->count();
                        break;

                    default:
                        # code...
                        break;
                }
            }
            M('tags')->where(array('id' => $id))->save($data);
            echo "Done:".$index;
            $this->assign('index',$index);
            $this->display();
        }
        echo "Success:".$index;
        exit;
    }
}