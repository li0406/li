<?php

/**
 * 微信小程序首页
 */

namespace WxApp\Controller;

use WxApp\Common\Controller\WxAppBaseController;

class AppletcarouselController extends WxAppBaseController
{
    private $appUser; //小程序用户ID

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 获取banner
     * 请求地址 :http://wxapp.qizuang.com/appletcarousel/banner
     * 参数非必填 :count 个数
     */
    public function banner()
    {
        $where = array();
        $page = max(0, I('get.page'));
        $pageSize = (I('get.count') != '') && (I('get.count') != null) ? I('get.count') : 3;
        $data = D('appletcarousel')->getBanner($where, $page, $pageSize);
        $this->ajaxReturn(array("bannerList" => $data));
    }

    /**
     * 获取文章
     * 请求地址 :http://wxapp.qizuang.com/appletcarousel/article
     * 参数非必填 :count 个数
     * 参数非必填 :order 排序方式 [realview(访问数量)/addtime(添加时间)]
     */
    public function article()
    {
        $where = array();
        $this->appUser = I('get.userid');
        $page = max(0, I('get.page'));
        $pageSize = (I('get.count') != '') && (I('get.count') != null) ? I('get.count') : 5;
        $order = (I('get.order') != '') && (I('get.order') != null) ? I('get.order') : 'addtime';
        $groupIds = '121,114,105,87';
        $arr = D('article')->getArticleClassIdsByClass($groupIds);
        $ids = array();
        foreach ($arr as $row) {
            $ids[] = $row['id'];
        }
        if (!empty($ids)) {
            $where['class_id'] = array('IN', $ids);
        }
        if (I('get.keyword')) {
            $where['title'] = array('like', '%' . trim(I('get.keyword')) . '%');
        }
        $data = D('article')->getArticleList($where, $order, $page, $pageSize);
        $data = $this->isCollect($data, $this->appUser,I('get.classtype'));
        $this->ajaxReturn(array("articleList" => $data));
    }

    /**
     * 获取文章详情
     * 请求地址 http://wxapp.qizuang.com/appletcarousel/details
     * 参数必填 :id 文章id
     */
    public function details()
    {
        $id = intval(I('get.id'));
        $get = I('get.');
        if (empty($id)) {
            $this->ajaxReturn(array('info' => '缺少文章编号', 'state' => 0));
        }
        $data = D('article')->getArticleById($id);
        if ($data) {
            //添加文章pv
            $save = ['pv' => $data['pv'] + 1];
            M('www_article')->where(['id' => $id])->save($save);
            //判断用户是否收藏
            $info = D('collect')->getCollectInfo(['userid' => $get['userid'], 'classid' => $id, 'classtype' => $get['classtype']]);
            if ($info) {
                $data['is_collect'] = 1;
            } else {
                $data['is_collect'] = 0;
            }
        }
        $this->ajaxReturn(array("article" => $data));
    }

    public function detailsRecommend()
    {
        $id = intval(I('get.id'));
        $count = (I('get.count') != '') && (I('get.count') != null) ? I('get.count') : 9;
        if (empty($id)) {
            $this->ajaxReturn(array('info' => '缺少文章编号', 'state' => 0));
        }
        $articleInfo = D('article')->getArticleById($id);
        //获取推荐文章，缓存key和PC端的一样-m.1.5.0 移动端-装修攻略文章终端页数据项逻辑调整
        $recommendArticles = S('Weixin:detailsRecommendList:' . $articleInfo['id']);
        if (empty($recommendArticles)) {
            $recommendArticles = $this->getRelationArticle($articleInfo["category_id"], $count, $articleInfo['id'], $articleInfo['tags'], $articleInfo['keywords']);
            S('Weixin:detailsRecommendList:' . $articleInfo['id'], $recommendArticles, 86400);
        }
        $this->ajaxReturn($recommendArticles);
    }

    /**
     * 文章点赞
     * 请求地址 http://wxapp.qizuang.com/appletcarousel/like
     * 参数必填 :id 文章id
     */
    public function like()
    {
        $id = I('get.id');
        if (!$id) {
            $this->ajaxReturn(array('info' => '缺少编号', 'state' => 0));
        }
        $data = D('article')->getArticleById($id);
        if ($data['likes'] || $data['likes']=='0') {
            $data = ['likes' => ((int)$data['likes'] + 1)];
        }
        $data = D('article')->changeArticleLike($id, $data);
        if ($data) {
            $this->ajaxReturn(array("state" => 1));
        } else {
            $this->ajaxReturn(array('info' => '点赞失败！', "state" => 0));
        }
    }

    /**
     * 装修效果图列表
     * 请求地址 http://wxapp.qizuang.com/appletcarousel/meitu
     * 非必填 page     起始页
     * 非必填 count    条数
     * 非必填 location 装修位置
     * 非必填 fengge   装修风格
     * 非必填 huxing   装修户型大小
     * 非必填 color    装修颜色
     */
    public function meitu()
    {
        $data = I('get.');
        $this->appUser = $data['userid'];
        $pageIndex = max(1, $data['page']);
        $pageSize = ($data['count'] != '') && ($data['count'] != null) ? (int)$data['count'] : 20;
        //获取美图列表
        $info['info'] = $this->getMeituList($data, $pageIndex, $pageSize, $data['keyword'], $data['state']);
        $info['dataCount'] = count($info['info']['list']);

        //获取美图属性
        $info['attribute'] = S('Cache:Applet:meitu:attribute');
        if (!$info['attribute']) {
            $info['attribute'] = D('meitu')->getMeituAttribute('', ['enabled' => 1]);
            S('Cache:Applet:meitu:attribute', $info['attribute'], 900);
        }

        $this->ajaxReturn($info);
    }

    /**
     * 装修效果图列表
     * 请求地址 http://wxapp.qizuang.com/appletcarousel/meitujubu
     * 非必填 page     起始页
     * 非必填 count    条数
     * 非必填 location 装修位置
     * 非必填 fengge   装修风格
     * 非必填 huxing   装修户型大小
     * 非必填 color    装修颜色
     */
    public function meitujubu()
    {
        $data = I('get.');
        $this->appUser = $data['userid'];
        $pageIndex = max(1, $data['page']);
        $pageSize = ($data['count'] != '') && ($data['count'] != null) ? (int)$data['count'] : 20;
        //获取美图列表
        $info['info'] = $this->getMeituList($data, $pageIndex, $pageSize, $data['keyword'], $data['state'], 1);
        $info['dataCount'] = count($info['info']['list']);

        //获取美图属性
        $info['attribute'] = [];//S('Cache:Applet:meitu:attribute');
        if (!$info['attribute']) {
            $info['attribute'] = D('meitu')->getMeituAttribute('', ['enabled' => 1]);
            //替换原本 位置 筛选项,改为固定
            if ($info['attribute']['location']) {
                unset($info['attribute']['location']);
                //获取固定位置的分类
                $info['attribute']['location'] = $this->jubuLocationDetail($data['location_wz']);
            }
            [];//S('Cache:Applet:meitu:attribute', $info['attribute'], 900);
        }

        $this->ajaxReturn($info);
    }

    /**
     * 装修效果图详情
     * 请求地址 http://wxapp.qizuang.com/appletcarousel/meituDetails
     * 必填 id 案列id
     */
    public function meituDetails()
    {
        $id = I('get.id');
        $userid = I('get.userid');
        if (!$id) {
            $this->ajaxReturn(array('info' => '缺少编号', 'state' => 0));
        }
        $info = D('meitu')->getMeituImgById($id);
        $params = [
            'location' => I('get.location'),
            'fengge' => I('get.fengge'),
            'huxing' => I('get.huxing'),
            'color' => I('get.color'),
        ];
        $tag = $this->getTagName($params);
        if ($info) {
            //添加文章pv.
            $save = ['pv' => ($info[0]['pv'] + 1)];
            M('meitu')->where(['id' => $info[0]['caseid']])->save($save);
        }
        $info = $this->isCollect($info, $this->appUser,I('get.classtype'));
        foreach ($info as $k => $v) {
            $info[$k]['is_collect'] = 0;
            $result = D('collect')->getCollectInfo(['classid' => $v['caseid'], 'userid' => $userid, 'classtype' => I('get.classtype')]);
            if ($result) {
                $info[$k]['is_collect'] = 1;
            }
            //因为效果图会有多个标签, 所以将其标签对应写死成用户所选标签
            foreach ($tag as $kk => $vv) {
                if($vv['name'] && !empty($vv['name'])){
                    $info[$k][$kk] = $vv['name'];
                }
            }
        }
        $this->ajaxReturn(array('info' => $info));
    }

    /**
     * 公司列表
     * 请求地址 http://wxapp.qizuang.com/appletcarousel/company
     * 非必填 page  起始页
     * 非必填 count 公司数量
     * 非必填 cs 城市id
     * 非必填 fg 风格
     * 非必填 gm 规模
     */
    public function company()
    {
        $data = I('get.');
        $this->appUser = $data['userid'];
        $pageIndex = max(1, $data['page']);
        $pageCount = ($data['count'] != '') && ($data['count'] != null) ? (int)$data['count'] : 20;
        $info['attribute'] = S('Cache:Applet:company:attribute');
        if (!$info['attribute']) {
            //风格
            $info['attribute']['fengge'] = D('Common/fengge')->getfg();
            //规模
            $info['attribute']['guimo'] = D('Common/guimo')->gethGm();
            S('Cache:Applet:company:attribute', $info['attribute'], 900);
        }
        //信赖度排序
        $where['orderby'] = 'comment_score desc,id';
        //城市
        if (isset($data['cs']) && !empty($data['cs'])) {
            $where['cs'] = $data['cs'];
        }
        //区县
        if (isset($data['qx']) && !empty($data['qx'])) {
            $where['qx'] = $data['qx'];
        }
        //风格
        if (isset($data['fg']) && !empty($data['fg'])) {
            $where['fg'] = $data['fg'];
        }
        //规模
        if (isset($data['gm']) && !empty($data['gm'])) {
            $where['gm'] = $data['gm'];
        }
        $info['list'] = $this->getCompanyList($where, $pageIndex, $pageCount);
        $info['dataCount'] = count($info['list']);
        $this->ajaxReturn($info);
    }

    /**
     * 公司详情
     * 请求地址 http://wxapp.qizuang.com/appletcarousel/companyDetails
     * 必填 id 公司编号
     * 非必填 page 公司案例起始页
     * 非必填 count 公司案例个数
     */
    public function companyDetails()
    {
        $data = I('get.');
        $pageIndex = max(1, $data['page']);
        $pageCount = ($data['count'] != '') && ($data['count'] != null) ? (int)$data['count'] : 20;
        if (!$data['id']) {
            $this->ajaxReturn(array('info' => '缺少公司编号', 'state' => 0));
        }
        //公司信息
        $info['details'] = S('Cache:Applet:company:details' . $data['id']);
        if (!$info['details']) {
            $info['details'] = $this->getCompanyDetails($data['id']);
            S('Cache:Applet:company:details' . $data['id'], $info['details'], 900);
        }
        //判断用户是否收藏
        $dd = D('collect')->getCollectInfo(['userid' => $data['userid'], 'classid' => $data['id'], 'classtype' => $data['classtype']]);
        if ($dd) {
            $info['details']['is_collect'] = 1;
        } else {
            $info['details']['is_collect'] = 0;
        }
        $pageIndex = max(1, $data['page']);
        $pageCount = ($data['count'] != '') && ($data['count'] != null) ? (int)$data['count'] : 20;
        //公司案列
        $info['cases'] = D("company")->getCompanyCases($data['id'], $pageIndex, $pageCount);

        $scheme_host = getSchemeAndHost();

        //如果是老图,直接拼接
        foreach ($info['cases'] as  $k=>$v){
            if (!$v['img_host']) {
                $info['cases'][$k]['img_path'] = $scheme_host["scheme_full"] . C('STATIC_HOST1') . $v['img_path'] . 'm_' . $v['img'];
            }
        }
        //设计团队
        $info['team'] = $this->getTeamDesigner($data['id'], '', 2, $pageIndex, $pageCount);
        $this->ajaxReturn($info);
    }

    /**
     * 公司案列详情
     * 请求地址 get http://wxapp.qizuang.com/appletcarousel/companyCasesDetail
     */
    public function companyCasesDetail()
    {
        $get = I('get.');
        $data = $this->getCaseInfo($get['id']);
        $this->ajaxReturn($data);
    }

    /**
     * 设计师详情
     * 请求地址 get http://wxapp.qizuang.com/appletcarousel/designer
     */
    public function designer()
    {
        $get = I('get.');
        $info = [];//S("Cache:mobileBlog:" . $get['id']);
        if (!$info) {
            //1.获取设计师的详细信息
            $designer = $this->getDesingerInfo($get['id']);
            $info["designer"] = $designer;
            [];//S("Cache:mobileBlog:" . I("get.id"), $designer, 3600 * 24);
        }
        //获取设计师作品
        $pageIndex = max(1, $get['page']);
        $pageCount = ($get['count'] != '') && ($get['count'] != null) ? (int)$get['count'] : 10;
        $info['cases'] = $this->getDesingerCaseInfo($get['id'], $pageIndex, $pageCount);
        $this->ajaxReturn($info);
    }

    /**
     * 收藏列表
     * 请求地址 get http://wxapp.qizuang.com/appletcarousel/collect
     * 非必填 page  起始页
     * 非必填 count 收藏数量
     * 必填 userid 用户id
     * 必填 classtype 收藏类型 8.效果图 9.装修公司 10.装修攻略
     */
    public function collect()
    {
        $get = I('get.');
        $pageIndex = max(1, $get['page']);
        $pageCount = ($get['count'] != '') && ($get['count'] != null) ? (int)$get['count'] : 20;
        if (!$get['userid']) {
            $this->ajaxReturn(['state' => 0, 'info' => '未找到当前用户']);
        }
        if (!$get['classtype']) {
            $get['classtype'] = 1;
        }
        $where['s.classtype'] = ['eq', $get['classtype']];
        $where['s.userid'] = ['eq', $get['userid']];
        switch ($get['classtype']) {
            //效果图
            case '8':
                $data = D('collect')->getUserCollectMeitu($where, $pageIndex, $pageCount);
                break;
            //装修公司
            case '9':
                $data = D('collect')->getUserCollectCompany($where, $pageIndex, $pageCount);
                $data = $this->getStar($data);
                break;
            //装修攻略
            case '10':
                $data = D('collect')->getUserCollectArticle($where, $pageIndex, $pageCount);
                break;
        }
        $this->ajaxReturn($data);
    }

    /**
     * 新增收藏
     * 请求地址 post  http://wxapp.qizuang.com/appletcarousel/editcollect
     * 必填 userid 用户id
     * 必填 classtype 收藏类型 8.效果图 9.装修公司 10.装修攻略
     * 必填 classid 收藏编号
     *
     * 删除收藏
     * 请求地址 get  http://wxapp.qizuang.com/appletcarousel/editcollect
     * 必填 id 收藏记录id
     */
    public function editcollect()
    {
        if (IS_POST) {
            $post = I('post.');
            if (!$post['userid']) {
                $this->ajaxReturn(['state' => 0, 'info' => '未找到当前用户']);
            }
            if (!$post['classid']) {
                $this->ajaxReturn(['state' => 0, 'info' => '未找到收藏列表项']);
            }
            if (!$post['classtype']) {
                $this->ajaxReturn(['state' => 0, 'info' => '未找到收藏类型']);
            }
            //判断是否存在
            $info = D('collect')->getCollectInfo(['userid' => $post['userid'], 'classid' => $post['classid'], 'classtype' => $post['classtype']]);;
            if ($info) {
                $this->ajaxReturn(['state' => 1, 'info' => '已添加到数据库']);
            }
            $save = [
                'classtype' => $post['classtype'],
                'classid' => $post['classid'],
                'userid' => $post['userid'],
                'time' => time(),
            ];
            $data = D('collect')->saveCollect($save);
            if ($data) {
                $this->ajaxReturn(['state' => 1, 'info' => '']);
            } else {
                $this->ajaxReturn(['state' => 0, 'info' => '添加失败']);
            }
        }
        $get = I('get.');
        $where = [
            'classid' => $get['classid'],
            'classtype' => $get['classtype'],
            'userid' => $get['userid'],
        ];
        $data = D('collect')->delCollect($where);
        if ($data) {
            $this->ajaxReturn(['state' => 1, 'info' => '']);
        } else {
            $this->ajaxReturn(['state' => 0, 'info' => '删除失败']);
        }
    }


    /**
     * 装修攻略列表页
     * 请求地址 get http://wxapp.qizuang.com/gonglue/:category
     * 非必填 page  起始页
     * 非必填 count 列表数量
     * 必填 category 分类
     */
    public function zxlclist()
    {
        $get = I('get.');
        $this->appUser = $get['userid'];
        $pageIndex = max(1, $get['page']);
        $pageCount = ($get['count'] != '') && ($get['count'] != null) ? (int)$get['count'] : 20;
        $category = $get['category'];
        $categoryClass = D("WwwArticleClass")->getArticleClassByShortname($category);
        $info = $this->getZxArticleList($categoryClass['id'], $pageIndex, $pageCount);
        foreach ($info as $k => $v) {
            $info[$k]['jianjie'] = mbstr(strip_tags($v['content']), 0, 28);
            unset($info[$k]['content']);
        }
        //获取是否收藏
        $info = $this->isCollect($info, $this->appUser,I('get.classtype'),I('get.classtype'));
        $this->ajaxReturn($info);
    }

    /**
     * 装修攻略列表页
     * 请求地址 get http://wxapp.qizuang.com/gongluejubu/:category
     * 非必填 page  起始页
     * 非必填 count 列表数量
     * 必填 category 分类
     */
    public function zxlclistjubu()
    {
        $get = I('get.');
        $this->appUser = $get['userid'];
        $pageIndex = max(1, $get['page']);
        $pageCount = ($get['count'] != '') && ($get['count'] != null) ? (int)$get['count'] : 20;
        $category = $get['category'];
        $categoryClass = D("WwwArticleClass")->getArticleClassByShortname($category);
        $info = $this->getZxArticleList($categoryClass['id'], $pageIndex, $pageCount, urldecode($get['keyword']));
        $returnData = [];
        foreach ($info as $k => $v) {
            $info[$k]['jianjie'] = mb_substr(strip_tags($v['content']), 0, 20, 'utf-8');
            $info[$k]['banner'] = 0;
            unset($info[$k]['content']);
            //先5篇文章后放置一个发单banner，然后每8个效果图后放置一个发单banner, 一直循环8个数据一个banner
            if ($k == 5 || (($k + 1) % 8 == 6)) {
                $returnData[] = ['banner' => 1];
            }
            $returnData[] = $info[$k];
        }
        //获取是否收藏
        $info = $this->isCollect($returnData, $this->appUser);
        $this->ajaxReturn($info);
    }

    /**
     *局部装修效果图获取位置栏目下拉选项
     * 请求地址 get http://wxapp.qizuang.com/appletcarousel/homemeituurl?location_wz=a,b,d,c,h,j
     * 必填 location_wz 位置信息
     * 效果图对应位置名称
    名称	代号	id
    客厅	A	4
    卧室	B	5
    阳台	C	9
    儿童房	D	12
    飘窗	E	22
    鞋柜	F	25
    餐厅	G	6
    厨房	H	7
    卫生间	I	8
    书房	J	10
    玄关	K	11
    衣帽间	L	13
    花园	M	14
    过道	N	15
    入户花园	O	16
    阁楼	P	17
    吧台	Q	18
    隔断	R	19
    楼梯	S	20
    吊顶	T	21
    酒柜	U	23
    窗帘	V	24
    榻榻米	W	26
    照片墙	X	28
    卧室背景墙	Y	29
    电视背景墙	Z	30
    餐厅背景墙	1	31
     */
    public function homeMeituUrl()
    {
        $get = I('get.');
        //获取url排序
        $data = $this->jubuLocationDetail($get['location_wz']);
        //获取风格
        $fg = $this->jubuFenggeDetail();
        $dds = [];
        foreach ($data as $k => $v) {
            foreach ($fg as $kk => $vv) {
                $dd[$kk]['leibieid'] = $v['id'];
                $dd[$kk]['title'] = $v['name'];
                $dd[$kk]['fgid'] = $vv['id'];
                $dd[$kk]['miaosu'] = $vv['miaosu'];
                $dd[$kk]['image'] = $vv['image'];
            }
            $dds[] = $dd;
        }
        $this->ajaxReturn($dds);
    }

    /**
     * 美图小程序 首页风格排序
     */
    public function homefgmeitu()
    {
        $scheme_host = getSchemeAndHost();
        $get = I('get.');
        //获取url排序
        $data = $this->jubuLocationDetail($get['location_wz']);
        $data['fg'] = [
            ['id' => '6', 'miaosu' => '地中海风格', 'image' => $scheme_host["scheme_full"].C("QINIU_DOMAIN").'/file/20180225/FoDXKzJTcxgsNVuGidCTl7jnni59.png'],
            ['id' => '10', 'miaosu' => '日式风格', 'image' => $scheme_host["scheme_full"].C("QINIU_DOMAIN").'/file/20180225/FhrD9dVUez2qe4ApzUN7F4uD0w_t.png'],
            ['id' => '21', 'miaosu' => '北欧风格', 'image' => $scheme_host["scheme_full"].C("QINIU_DOMAIN").'/file/20180225/FqzwO4j481uZfkvUbpLnjUCHVqdi.png'],
            ['id' => '15', 'miaosu' => '简欧风格', 'image' => $scheme_host["scheme_full"].C("QINIU_DOMAIN").'/file/20180225/FpfbwhxXjvn-axHmGKA5pQ-Ikybi.png'],
            ['id' => '7', 'miaosu' => '美式风格', 'image' => $scheme_host["scheme_full"].C("QINIU_DOMAIN").'/file/20180225/Fg-mUa_dmzNahvSpzkplHHo8vRm7.png']
        ];
        $this->ajaxReturn($data);
    }

    /**
     * 获取资讯列表页文章列表
     * @param  string $category_ids [分类编号]
     * @param  integer $pageIndex [description]
     * @param  integer $pageCount [description]
     */
    private function getZxArticleList($category_ids = '', $pageIndex, $pageCount, $keyword = '')
    {
        $returnResult = [];
        if (count($category_ids) > 0) {
            //获取文章的分类
            $result = D("WwwArticle")->getArticleListByIds([[$category_ids]], ($pageIndex - 1) * $pageCount, $pageCount, $keyword);
            foreach ($result as $key => $value) {
                //只保留页面所需数据
                $returnResult[$key]['id'] = $value['id'];
                $returnResult[$key]['shortname'] = $value['shortname'];
                $returnResult[$key]['face'] = $value['face'];
                $returnResult[$key]['title'] = $value['title'];
                $returnResult[$key]['jianjie'] = $value['jianjie'];
                $returnResult[$key]['pv'] = $value['pv'];
                $returnResult[$key]['content'] = $value['content'];
                $returnResult[$key]["img_host"] = "qiniu";
                //如果是老站链接过来的文章，同一用history代替
                if (empty($value["shortname"])) {
                    $returnResult[$key]["shortname"] = "history";
                    $returnResult[$key]["title"] = str_replace("_齐装网", "", $value["title"]);
                    $returnResult[$key]["img_host"] = "";
                }
                if (!empty($value["imgs"])) {
                    $exp = explode(",", $value["imgs"]);
                    $exp = array_filter($exp);
                    $returnResult[$key]["imgs"] = $exp;
                }

                if (empty($value["face"])) {
                    $img = str_replace("http://". C("QINIU_DOMAIN") . "/", "", $result[$key]["imgs"][0]);
                    $img = str_replace("https://". C("QINIU_DOMAIN") . "/", "", $img);
                    $img = str_replace("-s3.jpg", "", $img);
                    $returnResult[$key]["face"] = $img;
                }
            }
        }
        return $returnResult;
    }

    /**
     * 获取装修效果图列表并分页
     * @param $params 请求参数
     * @param int $pageIndex 页数
     * @param int $pageCount 每页个数
     * @param $keyword 搜索数据
     * @param $state 效果图状态 1：正常 2：预发布 3：未审核
     * @param int $jubu 是否是局部装修小程序
     * @return mixed
     */
    private function getMeituList($params, $pageIndex = 1, $pageCount = 20, $keyword, $state, $jubu = 0)
    {
        $qnhost = C('QINIU_DOMAIN');
        $result['list'] = D('meitu')->getMeituList($params, ($pageIndex - 1) * $pageCount, $pageCount, $keyword, $state);
        //因为局部效果图会有多个标签, 所以将其标签对应写死成用户所选标签
        if ($jubu != 0) {
            $tag = $this->getTagName($params);
        }
        //返回数据
        $returnData = [];
        foreach ($result['list'] as $key => $value) {
            $result['list'][$key]["tagname"] = str_replace(",", " ", $value["tagname"]);
            $result['list'][$key]["img_path"] = explode(',', $value["img_path"]); //将多张图片转换成数组
            foreach ($result['list'][$key]["img_path"] as $k => $img) {
                $result['list'][$key]["img_path"][$k] = $qnhost . '/' . $img;
            }
            //局部装修小程序 需要插入banner / 才有对应标签显示
            if ($jubu != 0) {
                //因为效果图会有多个标签, 所以将其标签对应写死成用户所选标签
                foreach ($tag as $k => $v) {
                    $result['list'][$key][$k] = $v['name'];
                }
                //先2个效果图后放置一个发单banner，然后3个效果图后放置一个发单banner，最后每隔4个效果图放置一个发单banner。
                if (($key) == 2 || ($key + 1) == 6 || ($key > 6 && ($key + 1) % 4 == 2)) {
                    $returnData[] = ['banner' => 1];
                }
                $returnData[] = $result['list'][$key];
            } else {
                $returnData[] = $result['list'][$key];
            }
        }
        $result['list'] = $this->isCollect($returnData, $this->appUser);
        return $result;
    }

    //获取公司列表并分页
    private function getCompanyList($map, $pageIndex = 1, $pageCount = 20)
    {
        $result = D("company")->getList($map, ($pageIndex - 1) * $pageCount, $pageCount);
        foreach ($result as $k => $v) {
            if($v['jc']){
                $result[$k]['qc'] = $result[$k]['jc'];
            }
        }
        //获取是否收藏
        $result['result'] = $this->isCollect($result['result'], $this->appUser);
        //获取星级
        $result = $this->getStar($result['result']);
        return $result;
    }

    //计算星星
    private function getStar($list)
    {
        foreach ($list as $key => $value) {
            if ($value["comment_score"] >= 9) {
                $list[$key]["star"] = 5;
            } elseif ($value["comment_score"] >= 8 && $value["comment_score"] < 9) {
                $list[$key]["star"] = 4;
            } elseif ($value["comment_score"] >= 6 && $value["comment_score"] < 8) {
                $list[$key]["star"] = 3;
            } elseif ($value["comment_score"] >= 4 && $value["comment_score"] < 6) {
                $list[$key]["star"] = 2;
            } else {
                $list[$key]["star"] = 1;
            }
        }
        return $list;
    }

    //计算星星 公共
    private function getCommonStar($data)
    {
        if ($data >= 9) {
            $star = 5;
        } elseif ($data >= 8 && $data < 9) {
            $star = 4;
        } elseif ($data >= 6 && $data < 8) {
            $star = 3;
        } elseif ($data >= 4 && $data < 6) {
            $star = 2;
        } else {
            $star = 1;
        }
        return $star;
    }

    /**
     * 判断用户是否收藏
     * @param $info 数据
     * @param $user 当前用户
     */
    private function isCollect($info, $user,$classtype = '')
    {
        foreach ($info as $k => $v) {
            $info[$k]['is_collect'] = 0;
            if ($user && $user != 'undefined' && $user != 'null') {
                $where = [
                    'classid' => $v['id'],
                    'userid' => $user
                ];
                if ($classtype) {
                    $where['classtype'] = $classtype;
                }
                $result = D('collect')->getCollectInfo($where);
                if ($result) {
                    $info[$k]['is_collect'] = 1;
                }
            }
        }
        return $info;
    }

    /**
     * [getRelationArticle 获取相关文章]
     * @param  string $type [文章类型]
     * @param  integer $limit [获取数目]
     * @param  integer $tagid [标签id]
     * @param  string $keywords [关键字]
     * @return [type]            [description]
     */
    private function getRelationArticle($type = '', $limit = 9, $notid = 0, $tagid = 0, $keywords = '')
    {
        //如果有标签id或者关键字，先利用分类和(标签或者关键字)搜索,不考虑分类
        //文章属于不同分类，但是标签或者关键字只要有一个相同，就可以调用
        //如果文章有多个关键字或者标签（有空格需要过滤空格），只要其中有一个相同，就调用
        if (!empty($tagid) || !empty($keywords)) {
            $string = '';
            //拼接标签查询
            if (!empty($tagid)) {
                $tags = array_filter(explode(',', $tagid));
                foreach ($tags as $key => $value) {
                    $string = $string . 'FIND_IN_SET("' . $value . '",a.tags) OR ';
                }
            }
            //拼接关键字查询
            if (!empty($keywords)) {
                $keys = array_filter(explode(',', $keywords));
                foreach ($keys as $key => $value) {
                    $string = $string . 'FIND_IN_SET("' . $value . '",a.keywords) OR ';
                }
            }
            $string = rtrim($string, 'OR ');
            if (!empty($string)) {
                $map = array(
                    [
                        '_string' => $string,
                        '_logic' => 'OR'
                    ]
                );
            }
        }

        if (empty($limit)) {
            $limit = 6;
        }

        //排除某个标签，避免调用了相同文章
        if (!empty($notid)) {
            $map['a.id'] = array('NEQ', $notid);
        }
        $result = D("WwwArticle")->getArticleListByMap($map, $limit);

        //如果更加标签或关键字获取的数量少于需要的数量，再次根据分类来获取
        $count = $limit - count($result);
        $other = [];
        if ($count > 0) {
            $map = [];
            if (!empty($type)) {
                $map['b.class_id'] = $type;
            }
            //排除相同ID的文章
            if (!empty($result)) {
                $ids = '';
                if (!empty($notid)) {
                    $ids = $notid . ',';
                }
                foreach ($result as $key => $value) {
                    $ids = $ids . $value['id'] . ',';
                }
                $map['a.id'] = array('NOT IN', trim($ids, ','));
            }
            $other = D("WwwArticle")->getArticleListByMap($map, $count);
        }
        $return = array_merge($result, $other);
        return $return;
    }


    private function getCompanyDetails($company_id)
    {
        $details = D("company")->getCompanyDetails($company_id);
        $details["avgsj"] = round($details["avgsj"], 1);
        $details["avgfw"] = round($details["avgfw"], 1);
        $details["avgsg"] = round($details["avgsg"], 1);
        $details["evaluation"] = $details["avgcuont"];
        if ($details["avgsj"] != 0 && $details["avgfw"] && $details["avgsg"]) {
            $details["evaluation"] = round(($details["avgsj"] + $details["avgfw"] + $details["avgsg"]) / 3, 2);
        }
        if ($details["avgsj"] != 0 && $details["avgfw"] && $details["avgsg"]) {
            $details["good_commend"] = round(($details["good"] / $details["newcount"]) * 100, 2);
        } else {
            $details["good_commend"] = round(($details["oldgood"] / $details["oldcount"]) * 100, 2);
        }
        if(empty($details["kouhao"])){
            $details["kouhao"] = '为你打造更好的生活 !';
        }
        //计算星星
        $details["avgsj"] = $this->getCommonStar($details["avgsj"]);
        $details["avgfw"] = $this->getCommonStar($details["avgfw"]);
        $details["avgsg"] = $this->getCommonStar($details["avgsg"]);
        unset($details['good'], $details['oldgood'], $details['oldcount'], $details['newcount'], $details['avgcount']);
        return $details;
    }

    /**
     * 获取公司团队
     * @param $id  公司id
     * @param $zw
     * @param $zt  入住状态 0 为处理 1 拒绝 2.入住
     * @param $page
     * @param $pageCount
     * @return array
     */
    private function getTeamDesigner($id, $zw, $zt, $page, $pageCount)
    {
        //查询设计师资料
        $users = D("User")->getTeamDesignerList($id, $zw, $zt, ($page - 1) * $pageCount, $pageCount);
        $returnData = [];
        foreach ($users as $k => $v) {
            $returnData[$k]['uid'] = $v['uid'];
            $returnData[$k]['name'] = $v['name'];
            $returnData[$k]['logo'] = $v['logo'];
            $returnData[$k]['jobtime'] = empty($v['jobtime']) ? '暂无' : $v['jobtime'];
            $returnData[$k]['zw'] = $v['zw'];
        }
        return $returnData;
    }

    /**
     * 获取案例信息
     * @param  string $id [案例编号]
     * @param  string $cs [所在城市]
     * @return [type]     [description]
     */
    private function getCaseInfo($id = '', $cs = '')
    {
        $caseInfo = D("Common/Cases")->getMobileCaseInfo($id, $cs);
        $returnData = [];
        if (count($caseInfo) > 0) {
            $scheme_host = getSchemeAndHost();
            foreach ($caseInfo as $key => $value) {
                if ($key == 0) {
                    $returnData['id'] = $value['id'];
                    $returnData['title'] = $value['title'];
                    $returnData['mianji'] = $value['mianji'];
                    $returnData['qc'] = $value['qc'];
                    $returnData['jc'] = $value['jc'];
                    $returnData['fengge'] = $value['fengge'];
                }
                if (!$value['img_host']) {
                    $value['img_path'] = $scheme_host["scheme_full"] . C('STATIC_HOST1') . '/' . $value['img_path'] . 'm_' . $value['img'];
                }
                $returnData["imgs"][$key]['img_host'] = $value['img_host'];
                $returnData["imgs"][$key]['img_path'] = $value['img_path'];
            }
            return $returnData;
        }
        return null;
    }

    /**
     * 获取设计师信息
     * @param  [type] $id [设计师编号]
     * @param  [type] $cid [公司编号]
     * @return [type]     [description]
     */
    private function getDesingerInfo($id, $cs = '')
    {
        $user = D("Common/User")->getDesingerInfo($id, $cs);
        $returnData = [];
        $returnData['uid'] = $user['uid'];
        $returnData['name'] = $user['name'];
        $returnData['zw'] = $user['zw'];
        $returnData['jobtime'] = empty($user['jobtime']) ? '暂无' : $user['jobtime'];
        $returnData['logo'] = $user['logo'];
        $returnData['linian'] = $user['linian'];
        $returnData['text'] = $user['text'];
        return $returnData;
    }

    /**
     * 获取设计师案例列表
     * @param  [type] $id        [设计师编号]
     * @param  [type] $pageIndex [description]
     * @param  [type] $pageCount [description]
     * @param  [type] $tab       [额外参数]
     * @return [type]            [description]
     */
    private function getDesingerCaseInfo($id, $pageIndex, $pageCount)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $cases = D("Cases")->getDesingerCaseInfo($id, ($pageIndex - 1) * $pageCount, $pageCount);
        $returnData = [];

        $scheme_host = getSchemeAndHost();
        foreach ($cases as $k => $v) {
            if (!$v['img_host']) {
                $v['img_path'] = $scheme_host["scheme_full"] . C('STATIC_HOST1') . '/' . $v['img_path'] . 'm_' . $v['img'];
            }
            $returnData[$k]['caseid'] = $v['caseid'];
            $returnData[$k]['title'] = $v['title'];
            $returnData[$k]['img_path'] = $v['img_path'];
            $returnData[$k]['img_host'] = $v['img_host'];
            $returnData[$k]['fg'] = $v['fg'];
            $returnData[$k]['huxing'] = $v['huxing'];
        }
        return $returnData;
    }

    private function getTagName($params)
    {
        $tag = '';
        if ($params['location']) {
            $tag["wz"] = D('meitu')->getTagNameById('location', $params['location']);
        }
        if ($params['fengge']) {
            $tag["fg"] = D('meitu')->getTagNameById('fengge', $params['fengge']);
        }
        if ($params['huxing']) {
            $tag["hx"] = D('meitu')->getTagNameById('huxing', $params['huxing']);
        }
        if ($params['color']) {
            $tag["ys"] = D('meitu')->getTagNameById('color', $params['color']);
        }
        return $tag;
    }

    /**
     * 局部装修 位置分类 数据
     */
    private function jubuLocationDetail($wz)
    {
        $returnData = [];
        //设置默认值
        if(!$wz){
            $returnData[0] = ['id' => '8', 'name' => '卫生间'];
            $returnData[1] = ['id' => '4', 'name' => '客厅'];
            $returnData[2] = ['id' => '5', 'name' => '卧室'];
            $returnData[3] = ['id' => '7', 'name' => '厨房'];
            $returnData[4] = ['id' => '9', 'name' => '阳台'];
            $returnData[5] = ['id' => '21', 'name' => '吊顶'];
            $returnData[6] = ['id' => '22', 'name' => '飘窗'];
        }
        $data = ['a' => [
            'id' => '4',
            'name' => '客厅',], 'b' => [
            'id' => '5',
            'name' => '卧室',], 'c' => [
            'id' => '9',
            'name' => '阳台',], 'd' => [
            'id' => '12',
            'name' => '儿童房',], 'e' => [
            'id' => '22',
            'name' => '飘窗',], 'f' => [
            'id' => '25',
            'name' => '鞋柜',], 'g' => [
            'id' => '6',
            'name' => '餐厅',], 'h' => [
            'id' => '7',
            'name' => '厨房',], 'i' => [
            'id' => '8',
            'name' => '卫生间',], 'j' => [
            'id' => '10',
            'name' => '书房',], 'k' => [
            'id' => '11',
            'name' => '玄关',], 'l' => [
            'id' => '13',
            'name' => '衣帽间',], 'm' => [
            'id' => '14',
            'name' => '花园',], 'n' => [
            'id' => '15',
            'name' => '过道',], 'o' => [
            'id' => '16',
            'name' => '入户花园',], 'p' => [
            'id' => '17',
            'name' => '阁楼',], 'q' => [
            'id' => '18',
            'name' => '吧台',], 'r' => [
            'id' => '19',
            'name' => '隔断',], 's' => [
            'id' => '20',
            'name' => '楼梯',], 't' => [
            'id' => '21',
            'name' => '吊顶',], 'u' => [
            'id' => '23',
            'name' => '酒柜',], 'v' => [
            'id' => '24',
            'name' => '窗帘',], 'w' => [
            'id' => '26',
            'name' => '榻榻米',], 'x' => [
            'id' => '28',
            'name' => '照片墙',], 'y' => [
            'id' => '29',
            'name' => '卧室背景墙',], 'z' => [
            'id' => '30',
            'name' => '电视背景墙',], '1' => [
            'id' => '31',
            'name' => '餐厅背景墙',],
            ];
        $arrs = explode(',',$wz);
        foreach ($arrs as $k=>$v){
            $returnData[$k] = $data[$v];
        }
        return $returnData;
    }

    /**
     * 获取局部风格
     * @return array
     */
    private function jubuFenggeDetail()
    {
        $scheme_host = getSchemeAndHost();
        return [
            ['id' => '33', 'miaosu' => '巴洛克风格', 'image' => $scheme_host["scheme_full"].C("QINIU_DOMAIN").'/file/20180116/FmYG-MgT4UC50W5hdZWJ46jBXWj-.jpg'],
            ['id' => '21', 'miaosu' => '北欧风格', 'image' => $scheme_host["scheme_full"].C("QINIU_DOMAIN").'/file/20180116/FnXNuxB9Z3k2Pn85fCsK-tWD_rKO.jpg'],
            ['id' => '6', 'miaosu' => '地中海风格', 'image' => $scheme_host["scheme_full"].C("QINIU_DOMAIN").'/file/20180116/FgpLwimS-ewOG0dwcNdseg6Ch0Xr.jpg'],
            ['id' => '15', 'miaosu' => '简欧风格', 'image' => $scheme_host["scheme_full"].C("QINIU_DOMAIN").'/file/20180116/FlVHP0HuJJNzTEkbVXomLUXV2yic.png'],
            ['id' => '7', 'miaosu' => '美式风格', 'image' => $scheme_host["scheme_full"].C("QINIU_DOMAIN").'/file/20180116/FjlHPM9Uvf9EJxMZoZY58yRXYrfd.png'],
            ['id' => '8', 'miaosu' => '欧式风格', 'image' => $scheme_host["scheme_full"].C("QINIU_DOMAIN").'/file/20180116/FoYSDv-1lEPJzlRR2dQ3Fs-Yr8Lj.jpg'],
        ];
    }
}