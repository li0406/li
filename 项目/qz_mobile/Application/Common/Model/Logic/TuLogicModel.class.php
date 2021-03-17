<?php

namespace Common\Model\Logic;

use Common\Model\Db\TuModel;
use Think\Exception;

class TuLogicModel
{
    protected $tuModel;

    protected $tuCategoryLogic;

    protected $perCount = 40;

    protected $scheme_host;

    const TU_TYPE_PUB = 1;
    const TU_TYPE_HOME = 2;

    /**
     * 家装栏目页筛选项-映射关系
     * @var array
     */
    protected $categoryFilterMap = [
        'pub' => [
            'gz' => 'pub_category',
        ],  //公装
        'home' => [
            'kj' => 'home_space_category',
            'jb' => 'home_part_category',
            'fg' => 'home_style_category',
            'hx' => 'home_layout_category',
        ],
    ];

    public function __construct()
    {
        $this->tuModel = new TuModel();
        $this->tuCategoryLogic = new TuCategoryLogicModel();

        $this->scheme_host = getSchemeAndHost();
    }

    /**
     * html 实体解码
     * @param $value
     * @return string
     */
    private function decode($value)
    {
        return htmlspecialchars_decode($value);
    }

    /**
     * 清洗图片
     * @param string $url
     * @return string|string
     */
    private function transformImage(string $url)
    {
        //有qizuang.com 和协议头
        if (strstr($url, 'http:') !== false || strstr($url, 'https:') !== false) {
            return $url;
        } else {
            $url = $this->scheme_host["scheme_full"] . C('QINIU_DOMAIN') . '/' . $url;
            return $url;
        }
    }

    /**
     * 清洗列表数据
     * @param array $data
     * @return array
     */
    private function transformListData(array $data)
    {
        $formattedData = [];
        if (empty($data)) {
            return $formattedData;
        }
        $formattedData = $data;
        $formattedData['publish_time'] = $data['publish_time'] ? date('Y-m-d H:i:s', $data['publish_time']) : '';
        $formattedData['created_at'] = $data['created_at'] ? date('Y-m-d H:i:s', $data['created_at']) : '';
        $formattedData['updated_at'] = $data['updated_at'] ? date('Y-m-d H:i:s', $data['updated_at']) : '';
        $formattedData['image_src'] = $data['image_src'];
        $formattedData['image_src_total'] = $data['image_src'] ? $this->transformImage($data['image_src']) : '';
        $formattedData['image_title'] = $this->decode($data['image_title']);
        $formattedData['title'] = $this->decode($data['title']);
        $formattedData['keyword'] = $this->decode($data['keyword']);
        $formattedData['description'] = $this->decode($data['description']);

        if ($formattedData['type'] == 1) {
            $formattedData['detail_flag'] = 'g';
            $formattedData['type_name'] = '公装';
            $formattedData['category'] = $data['name'];
            $formattedData['category_space'] = $data['name'];
        } else {
            $formattedData['detail_flag'] = 'j';
            $formattedData['type_name'] = '家装';
            $formattedData['category'] = $data['space_name'].' '.$data['style_name'].' '.$data['part_name'].' '.$data['layout_name'];
            $formattedData['category_space'] = $data['space_name'].' '.$data['style_name'].' '.$data['part_name'].' '.$data['layout_name'];
        }
        return $formattedData;
    }

    /**
     * 获取值
     * @param $id
     * @param $field
     * @return \Think\Model
     */
    public function value($id, $field)
    {
        $map['id'] = $id;
        $value = $this->tuModel->where($map)->getField($field);
        return $value;
    }

    /**
     * 是否有某个值
     * @param $map
     * @return bool
     */
    public function hasValue($map)
    {
        $ret = $this->tuModel->where($map)->getField('id');
        return !!$ret;
    }

    /**
     * 首页美图列表
     * @param  integer $page  [description]
     * @param  integer $limit [description]
     * @return [type]         [description]
     */
    public function getHomePageList($page = 1, $limit = 20){
        $list = [];
        $params = [
            "type" => static::TU_TYPE_HOME
        ];
        $count = $this->tuModel->getHomeCount($params);
        if ($count > 0) {
            $offset = ($page - 1) * $limit;
            $list = $this->tuModel->getHomeList($params, $offset, $limit);
            foreach ($list as $key => $item) {
                $list[$key] = $this->transformListData($item);
            }
        }

        return ["count" => $count, "list" => $list];
    }

    /**
     * 获取公装列表
     * @param array $params
     * @return array
     */
    public function getPubList(array $params)
    {
        $count = $this->tuModel->getPubCount($params);
        $ret = [
            'page' => '',
            'list' => [],
        ];
        if ($count > 0) {
            $field = 'a.title,a.id,ti.src image_src,ti.title image_title,ti.width image_width,ti.height image_height';
            $data = $this->tuModel->getPubList($params, ($params['p'] - 1) * $this->perCount, $this->perCount, $field);
            $formattedData = [];
            if (!empty($data)) {
                foreach ($data as $value) {
                    $formattedData[] = $this->transformListData($value);
                }
            }
            $ret['list'] = $formattedData;
            //分页
            import('Library.Org.Page.Page');
            //自定义配置项
            $config = array("prev", "next");
            $extra = [
                'baseURL' => $this->scheme_host["scheme_full"] . C('MOBILE_DONAMES') . '/tu/' . $params['original'] . '/'
            ];
            $page = new \Page($params['p'], $this->perCount, $count, $config, $extra);
            $ret['page'] = $page->showTu();
        }
        return $ret;
    }

    /**
     * 获取美图详情
     * @param $id
     * @return array|mixed
     */
    public function getDetail($id, $type = 1)
    {
        if ($type == 1) {
            $data = $this->tuModel->getPubDetail($id);
        } else {
            $data = $this->tuModel->getHomeDetail($id);
        }
        if (!empty($data)) {
            $data = $this->transformListData($data);
        }
        return $data;
    }

    /**
     * 获取家装列表
     * @param array $params
     * @return array
     */
    public function getHomeList(array $params)
    {
        $count = $this->tuModel->getHomeCount($params);
        $ret = [
            'page' => '',
            'list' => [],
        ];
        if ($count > 0) {
            $field = 'a.title,a.id,ti.src image_src,ti.title image_title,ti.width image_width,ti.height image_height';
            $data = $this->tuModel->getHomeList($params, ($params['p'] - 1) * $this->perCount, $this->perCount, $field);
            $formattedData = [];
            if (!empty($data)) {
                foreach ($data as $value) {
                    $formattedData[] = $this->transformListData($value);
                }
            }
            $ret['list'] = $formattedData;
            //分页
            import('Library.Org.Page.Page');
            //自定义配置项
            $config = array("prev", "next");
            $extra = [
                'baseURL' => $this->scheme_host["scheme_full"].C('MOBILE_DONAMES').'/tu/' . $params['original'] . '/'
            ];
            $page = new \Page($params['p'], $this->perCount, $count, $config,$extra);
            $ret['page'] = $page->showTu();
        }
        return $ret;
    }

    /**
     * 获取美图列表筛选参数
     * @param array $params
     * @return array
     * @throws Exception
     */
    public function getCategoryMap(array $params)
    {
        //获取参数并判断 来源-公装/家装 并获取对应分类
        $filterParams = [];
        //默认筛选项
        $category = mb_strcut($params['category'], 0, 2);
        $categoryURL = mb_strcut($params['category'], 2);
        $filterParams['original'] = $params['category'];
        $filterParams['keyword'] = $params['keyword'];
        if ($params['category'] === 'jiazhuang') {
            $filterParams['type'] = 2;
        } elseif ($params['category'] === 'gongzhuang') {
            $filterParams['type'] = 1;
        } elseif ((!empty($category) && !empty($categoryURL) && array_key_exists($category, $this->categoryFilterMap['pub']))) {
            //公装
            //查询category中的url是否存在，并获取对应id
            $categoryData = $this->tuCategoryLogic->findByURL($categoryURL, 'id,name,parent');
            if (empty($categoryData)) {
                throw new Exception('查找分类失败');
            }
            $filterParams['type'] = 1;
            $cate = $this->categoryFilterMap['pub'][$category];
            $filterParams[$cate] = $categoryData['id'];
            $filterParams['category_url'] = $categoryURL;
            $filterParams['category_id'] = $categoryData['id'];
            $filterParams['category_name'] = $categoryData['name'];
            $filterParams['parent'] = $categoryData['parent'];
//            $filterParams['parent_name'] = $this->tuCategoryLogic->value($categoryData['parent'],'name');
        } elseif (!empty($category) && !empty($categoryURL && array_key_exists($category, $this->categoryFilterMap['home']))) {
            //家装
            //查询category中的url是否存在，并获取对应id
            $categoryData = $this->tuCategoryLogic->findByURL($categoryURL, 'id,name,parent');
            if (empty($categoryData)) {
                throw new Exception('查找分类失败');
            }
            $filterParams['type'] = 2;
            $cate = $this->categoryFilterMap['home'][$category];
            $filterParams[$cate] = $categoryData['id'];
            $filterParams['category_url'] = $categoryURL;
            $filterParams['category_id'] = $categoryData['id'];
            $filterParams['category_name'] = $categoryData['name'];
            $filterParams['parent'] = $categoryData['parent'];
//            $filterParams['parent_name'] = $this->tuCategoryLogic->value($categoryData['parent'],'name');
        } else {
            throw new Exception('获取分类参数失败');
        }
        return $filterParams;
    }

    // 
    public function getSearchCategoryMap($category_param){
        $category = mb_strcut($category_param, 0, 2);
        $categoryURL = mb_strcut($category_param, 2);

        $cate_id = 0;
        $cate_field = "";
        if (!empty($category) && !empty($categoryURL)) {
            $categoryData = $this->tuCategoryLogic->findByURL($categoryURL, 'id,name,parent');
            $cate_id = $categoryData["id"];
            if (array_key_exists($category, $this->categoryFilterMap['pub'])) {
                $cate_field = $this->categoryFilterMap['pub'][$category];
            } else if (array_key_exists($category, $this->categoryFilterMap['home'])) {
                $cate_field = $this->categoryFilterMap['home'][$category];
            }
        }

        return ["cate_field" => $cate_field, "cate_id" => $cate_id];
    }

    /**
     * 获取美图列表数据
     * @param $params
     * @return array
     * @throws Exception
     */
    public function getList($params)
    {
        try {
            $filterParams = $this->getCategoryMap($params);
            $filterParams['p'] = (int)$params['p'] ?: 1;
            if ($filterParams['type'] == 1) {
                $ret = $this->getPubList($filterParams);
            } else {
                $ret = $this->getHomeList($filterParams);
            }
            return $ret;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getSEO($type, $params, array $categoryData)
    {
        $data = [];
        if ($type == 2) {
            $data["title"] = "家装效果图 - 家庭装修设计效果图 - 齐装网装修效果图";
            if ($params['p'] && $params['p']!=1) {
                $data["title"] = "家装效果图 - 家庭装修设计效果图" . "【第{$params['p']}页】 - 齐装网装修效果图";
            }
            $data["keywords"] = "家装效果图,家居装修效果图,家庭装修设计效果图";
            $data["description"] = "齐装网家装效果图栏目汇集了数十万家庭装修效果图，包含客厅、卧室、卫生间等家庭局部装修效果图，中式、田园、美式、现代简约等家庭风格装修效果图，海量的家装设计案例尽在齐装网！";
        } else {
            $data["title"] = "公装效果图 - 公装案例效果图 - 齐装网装修效果图";
            if ($params['p'] && $params['p']!=1) {
                $data["title"] = "公装效果图 - 公装案例效果图" . "【第{$params['p']}页】 - 齐装网装修效果图";
            }
            $data["keywords"] = "公装效果图,公装设计案例效果图 ";
            $data["description"] = "齐家网公装效果图专区,为您提供国内外流行的公装效果图和公装设计案例，包括店铺、办公室、写字楼、厂房等公装设计效果图大全。";
        }
        if ($categoryData) {
            $data["title"] = $categoryData['title'] . " - 齐装网装修效果图";
            if ($params['p'] && $params['p']!=1) {
                $data["title"] = $categoryData['title'] . "【第{$params['p']}页】 - 齐装网装修效果图";
            }
            $data["keywords"] = $categoryData['keyword'];
            $data["description"] = $categoryData['description'];
        }
        return $data;
    }

    /**
     * 获取家装美图
     * @return array
     */
    public function getHomeFriendLink()
    {
        /*S-友情链接模块：以下链接添加友情链接模块*/
        $type = 'meitu-list';
        $friendLink = S('Cache:Meitu:Index:Friendlink:link-'  . $type);
        $friendLink = [];
        if (!$friendLink) {
            $friendLink['link'] = D('Friendlink')->getFriendLinkList('000001', '1', 'meitu-list');
        }
        S('Cache:Meitu:Index:Friendlink:link-' . $type, $friendLink['link'], 900);
        return $friendLink;
    }

    /**
     * 获取公装美图
     * @return array
     */
    public function getPubFriendLink()
    {
        $type = 'gongzhuang';

        $friendLink = S('Cache:Meitu:Index:Friendlink:link-' . $type);
        $friendLink = [];
        if(empty($friendLink)){
            $friendLink['link'] = D('Friendlink')->getFriendLinkList('000001', '1', $type);
            S('Cache:Meitu:Pubmeitu:Friendlink:link-' . $type, $friendLink['link'], 900);
        }
        return $friendLink;
    }

    //根据图片id获取相关图片
    public function getRelevantImgsByImgId($id)
    {
        $bascinfo = $this->tuModel->getMeituBaseInfoById($id);
        if($bascinfo['type'] == 1){     //公装美图获取相关
            $result = $this->tuModel->getPubRelevantMeitu($bascinfo['pub_category'],$id,6);
        }elseif($bascinfo['type'] == 2){    //家装美图获取相关
            $firstlist = [];

            //获取家装美图的四个分类和分类的启用状态
            $categoryinfo = $this->tuModel->getMeituCategoryAndCategoryState($id);
            if($categoryinfo['c1is_enable'] == 1 || $categoryinfo['c2is_enable'] == 1 || $categoryinfo['c3is_enable'] == 1 || $categoryinfo['c4is_enable'] == 1){
                //只要有一个分类是启用，就去匹配四个分类都相同的数据
                $map = [];
                $map['tu.id'] = array('neq',$id);
                $map['tu.type'] = array('eq',2);
                $map['tu.state'] = array('eq',3);
                $map['tu.home_space_category'] = array('eq',$bascinfo['home_space_category']);
                $map['tu.home_part_category'] = array('eq',$bascinfo['home_part_category']);
                $map['tu.home_style_category'] = array('eq',$bascinfo['home_style_category']);
                $map['tu.home_layout_category'] = array('eq',$bascinfo['home_layout_category']);
                $map['tu.state'] = array('eq',3);
                $map['c1.is_enable'] = array('eq',1);
                $map['c2.is_enable'] = array('eq',1);
                $map['c3.is_enable'] = array('eq',1);
                $map['c4.is_enable'] = array('eq',1);
                $firstlist = $this->tuModel->getMeituSameMeitu($map,6);
            }

            if(count($firstlist) >= 6){
                $result = $firstlist;
            }else{
                //不足， 补齐
                if($categoryinfo['c1is_enable'] == 1){      //获取相同home_space_category的数据
                    $map = [];
                    $limit = 6 - count($firstlist);
                    $ids = [];
                    foreach ($firstlist as $key => $val){
                        $ids[] = intval($val['id']);
                    }

                    $map['tu.type'] = array('eq',2);
                    $map['tu.state'] = array('eq',3);
                    if(count($ids) > 0){
                        $map['tu.id'] = array('not in',$ids);
                    }
                    $map['tu.id'] = array('neq',$id);
                    $map['tu.home_space_category'] = array('eq',$categoryinfo['c1_id']);
                    $secondlist = $this->tuModel->getNewMeituRelevantMeitu($map,1,$limit);
                    if($secondlist){
                        $firstlist = array_merge($firstlist,$secondlist);
                    }
                }

                if($categoryinfo['c2is_enable'] == 1){      //获取相同home_part_category的数据
                    $map = [];
                    $limit = 6 - count($firstlist);
                    if($limit > 0){
                        $ids = [];
                        foreach ($firstlist as $key => $val){
                            $ids[] = intval($val['id']);
                        }
                        $map['tu.type'] = array('eq',2);
                        $map['tu.state'] = array('eq',3);
                        if(count($ids) > 0){
                            $map['tu.id'] = array('not in',$ids);
                        }
                        $map['tu.id'] = array('neq',$id);
                        $map['tu.home_part_category'] = array('eq',$categoryinfo['c2_id']);
                        $secondlist = $this->tuModel->getNewMeituRelevantMeitu($map,2,$limit);
                        if($secondlist){
                            $firstlist = array_merge($firstlist,$secondlist);
                        }
                    }
                }

                if($categoryinfo['c3is_enable'] == 1){      //获取相同home_style_category的数据
                    $map = [];
                    $limit = 6 - count($firstlist);

                    if($limit > 0){
                        $ids = [];
                        foreach ($firstlist as $key => $val){
                            $ids[] = intval($val['id']);
                        }
                        $map['tu.type'] = array('eq',2);
                        $map['tu.state'] = array('eq',3);
                        if(count($ids) > 0){
                            $map['tu.id'] = array('not in',$ids);
                        }
                        $map['tu.id'] = array('neq',$id);
                        $map['tu.home_style_category'] = array('eq',$categoryinfo['c3_id']);
                        $secondlist = $this->tuModel->getNewMeituRelevantMeitu($map,3,$limit);

                        if($secondlist){
                            $firstlist = array_merge($firstlist,$secondlist);
                        }
                    }

                }

                if($categoryinfo['c4is_enable'] == 1){      //获取相同home_layout_category的数据
                    $map = [];
                    $limit = 6 - count($firstlist);

                    if($limit > 0){
                        $ids = [];
                        foreach ($firstlist as $key => $val){
                            $ids[] = intval($val['id']);
                        }
                        $map['tu.type'] = array('eq',2);
                        $map['tu.state'] = array('eq',3);
                        if(count($ids) > 0){
                            $map['tu.id'] = array('not in',$ids);
                        }
                        $map['tu.id'] = array('neq',$id);
                        $map['tu.home_layout_category'] = array('eq',$categoryinfo['c4_id']);
                        $secondlist = $this->tuModel->getNewMeituRelevantMeitu($map,4,$limit);
                        if($secondlist){
                            $firstlist = array_merge($firstlist,$secondlist);
                        }
                    }

                }

                $result = $firstlist;
            }

        }else{
            return array();
        }


        foreach ($result as $key => $value){
            if($value['type'] == 1){
                $result[$key]['meitu_type'] = 'g';
            }elseif($value['type'] == 2){
                $result[$key]['meitu_type'] = 'j';
            }
            if($value['image_src']){
                $result[$key]['image_src'] = $this->scheme_host["scheme_full"].C('QINIU_DOMAIN')."/" . $value['image_src']."-w300.jpg";
            }
        }
        $return = [];
        $return['publish_time'] = date('Y-m-d H:i:s',$bascinfo['publish_time']);
        $return['list'] = $result ? $result : array();
        return $return;

    }
    
    
    //获取当前美图的分类信息
    public function getMeituCategroys($id)
    {
        $bascinfo = $this->tuModel->getMeituBaseInfoById($id);
        $schemeAndHost = getSchemeAndHost();
        if($bascinfo['type'] == 1){     //公装美图
            $categoryinfo = $this->tuModel->getPubMeituCategoryAndCategoryState($id);
            $return['type'] = 1;
            if(intval($categoryinfo['c1is_enable']) == 1){
                $return['first_name'] = $categoryinfo['gz_name'];
                $return['first_url'] = $schemeAndHost['scheme_host'].'/tu/gz'.$categoryinfo['gz_url'];
            }else{
                $return['first_name'] = '';
                $return['first_url'] = '';
            }
            $return['other'] = [];
        }else{      //家装美图
            //获取家装美图的四个分类和分类的启用状态
            $categoryinfo = $this->tuModel->getMeituCategoryAndCategoryState($id);
            $return['type'] = 2;
            $i = 0;
            if(intval($categoryinfo['c1is_enable']) == 1){
                $return['first_name'] = $categoryinfo['kj_name'];
                $return['first_url'] = $schemeAndHost['scheme_host'].'/tu/kj'.$categoryinfo['kj_url'];
                $return['other'] = [];
                if(intval($categoryinfo['c3is_enable']) == 1){
                    $return['other'][$i]['name'] = $categoryinfo['fg_name'];
                    $return['other'][$i++]['url'] = $schemeAndHost['scheme_host'].'/tu/fg'.$categoryinfo['fg_url'];
                }
                if(intval($categoryinfo['c4is_enable']) == 1){
                    $return['other'][$i]['name'] = $categoryinfo['hx_name'];
                    $return['other'][$i++]['url'] = $schemeAndHost['scheme_host'].'/tu/hx'.$categoryinfo['hx_url'];
                }
                if(intval($categoryinfo['c2is_enable']) == 1){
                    $return['other'][$i]['name'] = $categoryinfo['jb_name'];
                    $return['other'][$i++]['url'] = $schemeAndHost['scheme_host'].'/tu/jb'.$categoryinfo['jb_url'];
                }
            }elseif(intval($categoryinfo['c3is_enable']) == 1){
                $return['first_name'] = $categoryinfo['fg_name'];
                $return['first_url'] = $schemeAndHost['scheme_host'].'/tu/fg'.$categoryinfo['fg_url'];
                $return['other'] = [];
                if(intval($categoryinfo['c1is_enable']) == 1){
                    $return['other'][$i]['name'] = $categoryinfo['kj_name'];
                    $return['other'][$i++]['url'] = $schemeAndHost['scheme_host'].'/tu/kj'.$categoryinfo['kj_url'];
                }
                if(intval($categoryinfo['c4is_enable']) == 1){
                    $return['other'][$i]['name'] = $categoryinfo['hx_name'];
                    $return['other'][$i++]['url'] = $schemeAndHost['scheme_host'].'/tu/hx'.$categoryinfo['hx_url'];
                }
                if(intval($categoryinfo['c2is_enable']) == 1){
                    $return['other'][$i]['name'] = $categoryinfo['jb_name'];
                    $return['other'][$i++]['url'] = $schemeAndHost['scheme_host'].'/tu/jb'.$categoryinfo['jb_url'];
                }

            }elseif(intval($categoryinfo['c4is_enable']) == 1){
                $return['first_name'] = $categoryinfo['hx_name'];
                $return['first_url'] = $schemeAndHost['scheme_host'].'/tu/hx'.$categoryinfo['hx_url'];
                $return['other'] = [];
                if(intval($categoryinfo['c1is_enable']) == 1){
                    $return['other'][$i]['name'] = $categoryinfo['kj_name'];
                    $return['other'][$i++]['url'] = $schemeAndHost['scheme_host'].'/tu/kj'.$categoryinfo['kj_url'];
                }
                if(intval($categoryinfo['c3is_enable']) == 1){
                    $return['other'][$i]['name'] = $categoryinfo['fg_name'];
                    $return['other'][$i++]['url'] = $schemeAndHost['scheme_host'].'/tu/fg'.$categoryinfo['fg_url'];
                }
                if(intval($categoryinfo['c2is_enable']) == 1){
                    $return['other'][$i]['name'] = $categoryinfo['jb_name'];
                    $return['other'][$i++]['url'] = $schemeAndHost['scheme_host'].'/tu/jb'.$categoryinfo['jb_url'];
                }

            }elseif(intval($categoryinfo['c2is_enable']) == 1){
                $return['first_name'] = $categoryinfo['jb_name'];
                $return['first_url'] = $schemeAndHost['scheme_host'].'/tu/jb'.$categoryinfo['jb_url'];
                $return['other'] = [];
                if(intval($categoryinfo['c1is_enable']) == 1){
                    $return['other'][$i]['name'] = $categoryinfo['kj_name'];
                    $return['other'][$i++]['url'] = $schemeAndHost['scheme_host'].'/tu/kj'.$categoryinfo['kj_url'];
                }
                if(intval($categoryinfo['c3is_enable']) == 1){
                    $return['other'][$i]['name'] = $categoryinfo['fg_name'];
                    $return['other'][$i++]['url'] = $schemeAndHost['scheme_host'].'/tu/fg'.$categoryinfo['fg_url'];
                }
                if(intval($categoryinfo['c4is_enable']) == 1){
                    $return['other'][$i]['name'] = $categoryinfo['hx_name'];
                    $return['other'][$i++]['url'] = $schemeAndHost['scheme_host'].'/tu/hx'.$categoryinfo['hx_url'];
                }

            }else{
                $return['first_name'] = '';
                $return['first_url'] = '';
                $return['other'] = [];
            }
        }
        return $return;

    }

    //添加美图的PV值
    public function meituAddPv($id)
    {
        $this->tuModel->meituAddPv($id);
    }

    public function getMeiTuListByFg($fg, $page = null, $pageSize = null)
    {
        $params['type'] = 2;
        $params['home_style_category'] = $fg;
        return $this->tuModel->getHomeList($params,$page,$pageSize);
    }

    public function setLikeInc($id){
        return $this->tuModel->setLikeInc($id);
    }

    //获取图片上/下
    public function getSingleCases($id,$type,$meitu_type = 1)
    {
        $getlist = $this->tuModel->getTuSingleCases($id,$type,$meitu_type);
        if(count($getlist) > 0){
            foreach ($getlist as $key => $val){
                $getlist[$key]['img_host'] = 'qiniu';
                $getlist[$key]['top_title'] = $val['title']. '-齐装网装修效果图';
            }
            return $getlist ? $getlist : [];
        }else{
            return array();
        }
    }

    // 获取推荐美图
    public function getRecommendTuHome($limit = 20, $offset = 0){
        $list = $this->tuModel->getTuHomeRecommendList($limit, $offset);

        foreach ($list as $key => &$item) {
            $item['detail_flag'] = 'j';
            $item['type_name'] = '家装';
            $item["image_src"] = $this->transformImage($item['image_src']);
        }
     
        return $list;
    }
}
