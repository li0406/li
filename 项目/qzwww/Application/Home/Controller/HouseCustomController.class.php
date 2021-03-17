<?php


namespace Home\Controller;

use Common\Model\Logic\AdvertLogicModel;
use Common\Model\Logic\ArticleLogicModel;
use Common\Model\Logic\HouseCustomLogicModel;

use Common\Model\Logic\TuCategoryLogicModel;
use Common\Model\Logic\TuLogicModel;
use Common\Model\Logic\WwwArticleClassLogicModel;
use Home\Common\Controller\HomeBaseController;
use Think\Exception;

class HouseCustomController extends HomeBaseController
{
    protected $houseCustomLogic;

    protected $advertLogic;

    protected $wwwArticleClassLogic;

    protected $wwwArticleLogic;

    protected $tuLogic;

    protected $tuCategoryLogic;

    /**
     * 全屋定制 第一级分类的id
     */
    const WWW_ARTICLE_CLASS_HOUSE_CUSTOM = 418;

    /**
     * 全屋定制首页轮播广告管理的位置id
     */
    const AD_HOME_POSITION_ID = 14;

    /**
     * 全屋定制-咨询首页轮播
     */
    const AD_NEWS_POSITION_ID = 17;

    /**
     * 全屋定制-图库轮播
     */
    const AD_TU_POSITION_ID = 18;

    /**
     * 全屋定制首页banner默认图
     */
    const HOME_DEFAULT_BANNER_IMAGE = 'https://staticqn.qizuang.com/custom/20200109/Fq5wWsJvYo3TSGn0Ue-mZvSk9feq';

    /**
     * 资讯页banner默认图
     */
    const NEWS_DEFAULT_BANNER_IMAGE = 'https://staticqn.qizuang.com/custom/20200109/FiDlpauYq2n3jU8z9jKPPeZvxEn9';

    /**
     * 图库页banner默认图
     */
    const TU_DEFAULT_BANNER_IMAGE = 'https://staticqn.qizuang.com/custom/20200109/FtZxzRz5-Aqf8oW5r_r7YZ7HXIlu';

    public function __construct()
    {
        parent::__construct();
        $this->houseCustomLogic = new HouseCustomLogicModel();
        $this->advertLogic = new AdvertLogicModel();
        $this->wwwArticleClassLogic = new WwwArticleClassLogicModel();
        $this->wwwArticleLogic = new ArticleLogicModel();
        $this->tuLogic = new TuLogicModel();
        $this->tuCategoryLogic = new TuCategoryLogicModel();

        $this->getHeader();
    }

    /**
     * 公共部分
     */
    public function getHeader()
    {
        //根据第一级分类获取头部的资讯二级和三级分类
        $headerNewClass = $this->houseCustomLogic->getArticleCategory(static::WWW_ARTICLE_CLASS_HOUSE_CUSTOM);


        $orderCount = $this->houseCustomLogic->getCreatedOrderCount();
        $this->assign('current_order_count', $orderCount);
        $this->assign('grand_class', static::WWW_ARTICLE_CLASS_HOUSE_CUSTOM);
        $this->assign('header_new_class', $headerNewClass);
    }

    //首页
    public function index()
    {
        try {
            //首页轮播广告
            $ad = $this->advertLogic->getAdByPosition(static::AD_HOME_POSITION_ID, 4);
            if (empty($ad)) {
                $ad[] = [
                    'id' => 0,
                    'name' => '',
                    'url' => '',
                    'source' => static::HOME_DEFAULT_BANNER_IMAGE,
                    'title' => '',
                ];
            }
            // 资讯

            //获取资讯分类
            $articleClass = $this->wwwArticleClassLogic->getByGrandParent(static::WWW_ARTICLE_CLASS_HOUSE_CUSTOM, 6);
            //资讯 -左侧数据
            $articleLeft = $this->wwwArticleLogic->getByGrandClass(static::WWW_ARTICLE_CLASS_HOUSE_CUSTOM, 13);
            //资讯- 精选
            $ids = array_column($articleLeft, 'id') ?: [];
            $articleRight = $this->wwwArticleLogic->getHotNotIdByGrandClass(static::WWW_ARTICLE_CLASS_HOUSE_CUSTOM, $ids, 6);

            //图库
            $tu = $this->tuLogic->getHouseCustomHome(1, 8);

            //最新申请
            $lastAppoint = $this->houseCustomLogic->getLastAppoint(100);
            //友情链接

            $this->assign('ad', $ad);
            $this->assign('article_class', $articleClass);
            $this->assign('article_left', $articleLeft);
            $this->assign('article_right', $articleRight);
            $this->assign('tu', $tu);
            $this->assign('last_appoint', $lastAppoint);
            $this->assign('nav_current', 1);
            $this->display();
        } catch (Exception $e) {
            $this->_error();
        }
    }

    //资讯首页
    public function news()
    {
        try {
            //轮播广告
            $ad = $this->advertLogic->getAdByPosition(static::AD_NEWS_POSITION_ID, 4);
            if (empty($ad)) {
                $ad[] = [
                    'id' => 0,
                    'name' => '',
                    'url' => '',
                    'source' => static::NEWS_DEFAULT_BANNER_IMAGE,
                    'title' => '',
                ];
            }
            //今日头条
            $hot = $this->wwwArticleLogic->getTodayHotByGrandClass(static::WWW_ARTICLE_CLASS_HOUSE_CUSTOM, 10);

            //家居设计 分类 419
            $designer = $this->wwwArticleLogic->getByParentCLass(static::WWW_ARTICLE_CLASS_HOUSE_CUSTOM, 419, 14);

            //品牌资讯
            $brand = $this->wwwArticleLogic->getByParentCLass(static::WWW_ARTICLE_CLASS_HOUSE_CUSTOM, 423, 10);

            //定制攻略
            $strategy = $this->wwwArticleLogic->getByParentCLass(static::WWW_ARTICLE_CLASS_HOUSE_CUSTOM, 428, 14);

            //保养护理
            $maintain = $this->wwwArticleLogic->getByParentCLass(static::WWW_ARTICLE_CLASS_HOUSE_CUSTOM, 436, 3);


            $this->assign('ad', $ad);
            $this->assign('hot', $hot);
            $this->assign('designer', $designer);
            $this->assign('brand', $brand);
            $this->assign('strategy', $strategy);
            $this->assign('maintain', $maintain);
            $this->assign('nav_current', 2);
            $this->display();
        } catch (Exception $e) {
            $this->_error();
        }
    }

    //咨询栏目页
    public function newsCategory()
    {
        try {
            $shortName = I('category');
            $page = (int)I('page');
            $page = $page > 0 ? $page : 1;
            //是否存在这个分类
            if (empty($shortName)) {
                $this->_error();
            }
            $category = $this->wwwArticleClassLogic->getByGrandClassAndShortName(static::WWW_ARTICLE_CLASS_HOUSE_CUSTOM, $shortName);
            //是否存在这个分类
            if (empty($category)) {
                $this->_error();
            }
            $params = [
                'grand_class' => static::WWW_ARTICLE_CLASS_HOUSE_CUSTOM,
                'level' => $category['level'],
                'class' => $category['id'],
                'shortname' => $category['shortname'],
            ];
            //获取列表数据
            $data = $this->houseCustomLogic->getNewsList($page, $params);
            //资讯- 精选
            $hot = $this->wwwArticleLogic->getHotByGrandClass(static::WWW_ARTICLE_CLASS_HOUSE_CUSTOM, 6);

            $this->assign('category', $category);
            $this->assign('data', $data);
            $this->assign('hot', $hot);
            $this->display();
        } catch (Exception $e) {
            $this->_error();
        }
    }

    //咨询详情页
    public function newsDetail()
    {
        try {
            $shortName = I('category');
            $id = I('id');
            //是否存在这个分类
            if (empty($shortName) || empty($id)) {
                throw new Exception('参数有误');
            }
            $category = $this->wwwArticleClassLogic->getByGrandClassAndShortName(static::WWW_ARTICLE_CLASS_HOUSE_CUSTOM, $shortName);
            //是否存在这个分类
            if (empty($category)) {
                throw new Exception('没有数据');
            }

            //根据id获取数据
            $data = $this->wwwArticleLogic->getArticleInfo($id, $category['id']);

            if (empty($data)) {
                throw new Exception('没有数据');
            }

            $data['description'] = mb_substr  (strip_tags($data['content']), 0, 100);

            if (empty($data['id'])) {
                throw new Exception('没有数据');
            }

            //pv+1
            $this->wwwArticleLogic->incField($id, 'pv', 1);

            //资讯- 精选
            $hot = $this->wwwArticleLogic->getHotNotIdByGrandClass(static::WWW_ARTICLE_CLASS_HOUSE_CUSTOM, [$id], 10);

            $this->assign('shortName', $shortName);
            $this->assign('category', $category);
            $this->assign('data', $data);
            $this->assign('hot', $hot);
            $this->display();
        } catch (Exception $e) {
            $this->_error();
        }
    }

    //全屋定制图库
    public function tu()
    {
        try {
            //首页轮播广告
            $ad = $this->advertLogic->getAdByPosition(static::AD_TU_POSITION_ID, 4);
            if (empty($ad)) {
                $ad[] = [
                    'id' => 0,
                    'name' => '',
                    'url' => '',
                    'source' => static::TU_DEFAULT_BANNER_IMAGE,
                    'title' => '',
                ];
            }
            //图库数据
            $data = $this->tuLogic->getHouseCustomHome(1, 40);

            $this->assign('nav_current', 3);
            $this->assign('ad', $ad);
            $this->assign('data', $data);
            $this->display();

        } catch (Exception $e) {
            $this->_error();
        }
    }

    /**
     * 异步获取图库数据
     */
    public function getHomeList()
    {
        try {
            $page = (int)I('p');
            $page = $page > 0 ? $page : 1;
            //图库数据
            $data = $this->tuLogic->getHouseCustomHome($page, 40);
            if (empty($data)) {
                throw new Exception('没有数据更多数据啦~', 404);
            }

            $code = 0;
            $msg = 'ok';
        } catch (Exception $e) {
            $code = $e->getCode();
            $msg = $e->getMessage();
            $data = null;
        }
        $ret = [
            'code' => $code,
            'msg' => $msg,
            'list' => $data,
        ];
        echo json_encode($ret, true);
        exit();
    }

    //全屋定制图库-所有
    public function tuAll()
    {
        try {
            //图库数据
            $data = $this->tuLogic->getHouseCustomHome(1, 40);
            $this->assign('data', $data);
            $this->display();
        } catch (Exception $e) {
            $this->_error();
        }
    }

    //全屋定制图库-筛选栏目页
    public function tuCategory()
    {
        try {
            $params = I('get.');
            //获取对应的分类数据

            $filterParams = $this->tuLogic->getCategoryMap($params);

            $filterParams['p'] = (int)$params['p'] ?: 1;

            //根据分类id获取分类数据

            if (empty($filterParams) || empty($filterParams['category_id'])) {
                throw new Exception('没有分类数据');
            }
            $category = $this->tuCategoryLogic->find($filterParams['category_id']);
            if (empty($category)) {
                throw new Exception('没有分类数据');
            }

            $data = $this->tuLogic->getHouseCustom($filterParams);

            //获取二级分类的数据

            $parentCategory = $this->tuCategoryLogic->find($category['parent']);

            //获取seo
            $seo = [
                'title' => "{$category['name']}全屋定制案例 - {$category['name']}定制效果图欣赏 - {$category['name']}家具设计效果图 - 齐装网全屋定制",
                'keyword' => "{$category['name']}全屋定制,{$category['name']}定制案例,{$category['name']}定制效果图,{$category['name']}家具设计，{$category['name']}家具定制,{$category['name']}家具设计",
                'description' => "齐装网{$category['name']}全屋定制效果图专区，为您提供2020年流行的{$category['name']}家具定制方案，{$category['name']}家具设计案例，{$category['name']}家具全屋定制效果图",
                'canonical' => '/qwdz/tu/' . $params['category'] . '/',
            ];

            $this->assign('seo', $seo);
            $this->assign('parent_category', $parentCategory);
            $this->assign('category', $category);
            $this->assign('data', $data['list']);

            $this->display();
        } catch (Exception $e) {
            $this->_error();
        }
    }

    /**
     * 根据分类获取图库中全屋定制的数据
     */
    public function getCategoryList()
    {
        try {
            $params = I('get.');
            //获取对应的分类数据

            $filterParams = $this->tuLogic->getCategoryMap($params);

            $filterParams['p'] = (int)$params['p'] ?: 1;
            $data = $this->tuLogic->getHouseCustom($filterParams);
            if (empty($data)) {
                throw new Exception('没有数据更多数据啦~', 404);
            }

            $code = 0;
            $msg = 'ok';
        } catch (Exception $e) {
            $code = $e->getCode();
            $msg = $e->getMessage();
            $data = null;
        }
        $ret = [
            'code' => $code,
            'msg' => $msg,
            'list' => $data,
        ];
        echo json_encode($ret, true);
        exit();
    }

    //全屋定制图库-详情页
    public function tuDetail()
    {
        try {
            $id = I('get.id');
            $detail = $this->tuLogic->getDetail($id, 3);

            if (empty($detail)) {
                throw new Exception('未发现数据');
            }

            //添加PV值
            $this->tuLogic->meituAddPv($id);

            //发布时间前后的的9条数据

            $other = $this->tuLogic->getHouseCustomDetailOther($id, 11);
            //上一张
            $prev = [];
            //下一张
            $next = [];
            //当前右侧
            $left = $other;
            $data = [$detail];
            if (!empty($other)) {
                if (count($other) >= 10) {
                    $next = $other[0];
                    $next['class'] = 'next';
                    $next = [$next];
                    $left = array_slice($other, 1, 9);
                }
                if (count($other) == 11) {
                    $prev = $other[10];
                    $prev['class'] = 'prev';
                    $prev = [$prev];
                }
                $data = array_merge($prev, $data, $left, $next);
            }

            //其他效果图
            $relativeImages = $this->tuLogic->getRelativeTu($id, 6);

            $this->assign('detail', $detail);
            $this->assign('data', $data);
            $this->assign('relative_images', $relativeImages);
            $this->assign('other', $other);

            $this->display();
        } catch (Exception $e) {
            $this->_error();
        }
    }

    /**
     * 获取全屋定制-图库详情中的相关图
     */
    public function getTuDetailRelative()
    {
        try {
            //根据id 获取相关分类的 关联图
            $id = I('get.id');
            if (empty($id)) {
                throw new Exception('参数有误', 400);
            }

            $data = $this->tuLogic->getRelativeTu($id, 6);
            $code = 0;
            $msg = 'ok';
        } catch (Exception $e) {
            $code = $e->getCode();
            $msg = $e->getMessage();
            $data = [];
        }
        $ret = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ];
        echo json_encode($ret);
        exit();
    }
}
