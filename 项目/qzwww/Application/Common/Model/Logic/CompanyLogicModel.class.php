<?php
// +----------------------------------------------------------------------
// | CompanyLogicModel  装修公司逻辑层
// +----------------------------------------------------------------------
// | author: mcj
// +----------------------------------------------------------------------
// | Date: 2019/2/20 Time: 15:04
// +----------------------------------------------------------------------
namespace Common\Model\Logic;

use Common\Model\Db\CompanyModel;
use Common\Model\Db\UserCompanyAccountLogModel;
use Common\Model\Db\UserCompanyEmployeeModel;
use Common\Model\TeamModel;

class CompanyLogicModel
{
    protected $autoCheckFields = false;

    /**
     * 获取主站首页装修公司
     *
     * @param string $cityid 城市ID
     * @return mixed
     */
    public function getSubstationRecommend($cityid, $limit = '')
    {
        $companyDb = new CompanyModel();
        $trueCompany = $companyDb->getTrueCompany($cityid, $limit);
        $trueCompanyNumber = count($trueCompany);
        $orderBy = ['sort' => 'asc', 'end_time' => 'desc', 'a.register_time' => 'desc'];
        if ($trueCompanyNumber >= 6) {
            if ($trueCompanyNumber % 6 == 0) {
                return $trueCompany;
            } else {
                $fakeCompany = $companyDb->getFakeCompany($cityid, 6 - ($trueCompanyNumber % 6), $orderBy);
                if (count($fakeCompany) > 0) {
                    $trueCompany = array_merge($trueCompany, $fakeCompany);
                }
            }
        } else {
            $fakeCompany = $companyDb->getFakeCompany($cityid, 6 - count($trueCompany), $orderBy);
            if (count($fakeCompany) > 0) {
                $trueCompany = array_merge($trueCompany, $fakeCompany);
            }
        }
        return $trueCompany;
    }

    /**
     * 获取假会员【'a.on' = 2, 'fake' = 1】
     *
     * @param string $cityid 城市ID
     * @param int    $limit  获取几条
     * @param array  $sort   排序条件
     * @return mixed
     */
    public function getTrueFakeCompany($cityid = '', $limit = 0, $sort = ['sort' => 'asc', 'a.register_time' => 'asc'])
    {
        $companyDb = new CompanyModel();
        return $companyDb->getFakeCompany($cityid, $limit, $sort, ['a.on' => 2, 'fake' => 1]);
    }

    /**
     * 计算星星
     *
     * @param $companyList
     */
    public static function bindStar(&$companyList)
    {

        array_walk($companyList, function (&$v1) {
            if ($v1['comment_score'] >= 9) {
                $v1['star'] = 5;
            } elseif ($v1['comment_score'] >= 8 && $v1['comment_score'] < 9) {
                $v1['star'] = 4;
            } elseif ($v1['comment_score'] >= 6 && $v1['comment_score'] < 8) {
                $v1['star'] = 3;
            } elseif ($v1['comment_score'] >= 4 && $v1['comment_score'] < 6) {
                $v1['star'] = 2;
            } else {
                $v1['star'] = 1;
            }
        });
    }

    /**
     * 绑定标签
     *
     * @param $companyList
     */
    public static function bindTags(&$companyList)
    {
        if (!empty($companyList)) {
            $map['company_id'] = ['in', array_column($companyList, 'id')];
            $tagModle = new \Common\Model\Db\CompanyTags();
            #$tagList = $tagModle->getTags($map);
            $tagList = $tagModle->getLastNewTags($map);
            //处理逻辑,将得到公司对应标签数大于5的都移除
            $companyExistArr = [];
            foreach ($tagList as $k => $v) {
                if (array_key_exists($v['company_id'],$companyExistArr)) {
                    if (count($companyExistArr[$v['company_id']]) > 4) {
                        unset($tagList[$k]);
                    } else {
                        array_push($companyExistArr[$v['company_id']],$v['id']);
                    }
                } else {
                    $companyExistArr[$v['company_id']][] = $v['id'];
                }
            }
            array_walk($companyList, function (&$v1) use ($tagList) {
                $v1['tags'] = [];
                return array_map(function ($v2) use (&$v1){
                    if ($v1['id'] == $v2['company_id']) {
                        $v1['tags'][] = $v2;
                    }
                },$tagList);
            });
        }
    }

    /**
     * 绑定装修公司 专用券/通用券
     *
     * @param $companyList
     */
    public static function bindSpecialCard(&$companyList)
    {
        $limit = 2;
        $model = new \Common\Model\Db\CardModel();
        array_walk($companyList, function (&$v1) use ($model, $limit) {
            $v1['cardlist'] = S("Cache:Company:Card:".$v1['id']);
            if (!$v1['cardlist']) {
                $rand = random_int(3600*3, 3600*8);
                $v1['cardlist'] = $model->getSpecialCardById($v1['id'], $limit);
                if (count($v1['cardlist']) == 0) {
                    $v1['cardlist'] = 1;
                }
                S("Cache:Company:Card:".$v1['id'],$v1['cardlist'],$rand);
            }
        });
        
    }

    //根据装修公司id获取装修公司信息
    public function getCompanyInfoByComId($comid)
    {
        $model = new CompanyModel();
        if($comid){
            $result =  $model->getCompanyInfoByComId($comid);
            if(mb_strlen($result['jc']) > 8){
                $result['jc'] = mb_substr($result['jc'],0,8);
            }
            //评分星星
            if ($result['comment_score'] >= 9) {
                $result['star'] = 5;
            } elseif ($result['comment_score'] >= 8 && $result['comment_score'] < 9) {
                $result['star'] = 4;
            } elseif ($result['comment_score'] >= 6 && $result['comment_score'] < 8) {
                $result['star'] = 3;
            } elseif ($result['comment_score'] >= 4 && $result['comment_score'] < 6) {
                $result['star'] = 2;
            } else {
                $result['star'] = 1;
            }

            $result['logo'] = empty($result['logo'])?"https://".C('QINIU_DOMAIN')."/Public/default/images/default_logo.png":$result['logo'];
            return $result;
        }else{
            return false;
        }

    }
    
    //根据装修公司id和设计师id获取装修公司最新的
    public function getOtherDesignerByCompanyAndUserId($userid,$company_id)
    {
        $model = new TeamModel();
        $list = $model->getOtherDesignerByCompanyAndUserId($userid,$company_id);
        if($list){
            //截断风格
            foreach ($list as $key => $val){
                if(mb_strlen($val['fengge']) > 20){
                    $list[$key]['fengge'] = $cases[$key]['title1'] = mb_substr($val['fengge'],0,20).'...';
                }
                if($val["zw"] == '0'){
                    $list[$key]["zw"] = '';
                }
            }
            return $list;
        }else{
            return array();
        }

    }

    //公司前台是否显示
    public function companyShow($id){
        $model = new CompanyModel();
        $info = $model->getCompanyInfoByComId($id);
        if($info['is_show'] == 1){
            return true;
        }else{
            return false;
        }

    }

    // 公司是否是2.0会员
    public function isNewVip($comid)
    {
        if (empty($comid)) {
            return 0;
        }
        $userCompanyEmployeeModel = new UserCompanyEmployeeModel();
        $count = $userCompanyEmployeeModel->getEmployeeCount($comid);
        return $count ? $count : 0;

    }

    /**
     * 获取非会员排行榜
     * @param $limit  列表个数
     */
    public function getCompanyActivityRank($cs = '',$limit = 10)
    {
        $model = new CompanyModel();
        $list = [];
        if($limit){
            $list = $model->getNotVipCompanyActivityList($cs,$limit);
        }
        return $list;

    }

    /**
     * 获取推荐装修公司
     * @param string $cs
     * @param int $num
     */
    public function getRecommendCompany($cs = '', $num = 3)
    {
        $companyDb = new CompanyModel();
        //获取最近一个月量房的装修公司
        $where = [
            'r.time' => ['between', [strtotime('-1 month'), time()]],
            'c.fake' => ['EQ',0],
            'c.is_show' => ['EQ',1],
            'r.status' => ['EQ',1],
            'u.on' => ['EQ',2],
            'u.classid' => ['EQ',3],
        ];
        if (!empty($cs)) {
            $where['u.cs'] = ['eq', $cs];
            // $where['c.fake'] = ['eq', 0];
        }
        $company = $companyDb->getRecommendLiangfangList($where, $num);
        $companyId = array_column($company,'id');
        $count = count($companyId);
        if ($count < $num) {
            //如果不满足个数,取最新的量房公司,去除已经选择的
            unset($where['r.time']);
            if($count){
                $where['r.comid'] = ['not in',$companyId];
            }
            $new_company = $companyDb->getRecommendLiangfangList($where, ($num - $count));
            $company = array_merge($company,$new_company);
            $companyId = array_merge($companyId,array_column($new_company,'id'));
        }
        //删除量房条件
        if(count($companyId) < $num){
            // 如果量房的公司数量还不满足的话，获取本市的装修公司，去除已经选择的
            $where1 = [
                'u.on' => ['eq', 2],
                'c.fake' => ['eq', 1],
            ];
            if(count($companyId) > 0)
            {
                $where1['u.id'] = ['not in',$companyId];
            }
            if (!empty($cs)) {
                $where1['u.cs'] = ['eq', $cs];
            }
            $third_company = $companyDb->getRecommendList($where1,$num - count($companyId));
            $company = array_merge($company,$third_company);
            $companyId = array_merge($companyId,array_column($third_company,'id'));
        }
        //查询装修公司数据
        if(count($companyId) > 0){
            $info = $companyDb->getUserCompanyRank($companyId);
            $info = array_column($info,null,'comid');
            foreach ($company as &$v){
                if($v['fake'] == 0){
                    $v['casesnum'] = isset($info[$v['id']])?$info[$v['id']]['casesnum']:0;
                    $v['designnum'] = isset($info[$v['id']])?$info[$v['id']]['designnum']:0;
                    $v['ping'] = isset($info[$v['id']])?$info[$v['id']]['ping']:0;
                }else{
                    $v['casesnum'] = $v['case_count'];
                    $v['designnum'] = $v['team_count'];
                    $v['ping'] = $v['comment_count'];
                }
            }
            unset($v);
        }
        return $company;
    }

    //获取装修公司附属统计数据
    public function getCompanyRankInfo($companyId = [])
    {
        if (count($companyId) == 0) {
            return [];
        }
        $companyDb = new CompanyModel();
        $info = $companyDb->getUserCompanyRank($companyId);
        return array_column($info, null, 'comid');
    }

    /**
     * 获取累计新增保证金
     * @param string|array $company_id 公司id
     * @return array|mixed
     */
    public function getStatisticsCashDeposit($company_id)
    {
        if (count($company_id) == 0) {
            return [];
        }
        $logDb = new UserCompanyAccountLogModel();
        if (is_array($company_id)) {
            $list = $logDb->getStatisticsCashDepositByCom($company_id);
            return array_column($list, null, 'user_id');
        } else {
            $info = $logDb->getCashDepositInfoByCom($company_id);
            return $info;
        }
    }

    /**
     * 获取最新会员
     * @param string $cs 城市
     * @return mixed
     */
    public function getNewestVipCompany($cs = '')
    {
        $companyDb = new CompanyModel();
        $list = $companyDb->getNewestCompany($cs);
        return $list;
    }

    /**
     * 获取分站口碑列表
     * @param $cs
     * @param int $num
     * @return array
     */
    public function getSubKoubeiList($cs, $num = 8)
    {
        if (empty($cs)) {
            return [];
        }
        $companyDb = new CompanyModel();
        $list = $companyDb->getSubKoubeiRank($cs, $num);
        foreach ($list as $k => $v) {
            $list[$k]['logo'] = changeImgUrl($v['logo']) ?: 'https://' . C('QINIU_DOMAIN') . '/Public/default/images/default_logo.png';
        }
        return $list;
    }

    /**
     * 获取推荐装修公司模块
     * @param string $cs
     * @param int $num
     * @return array
     */
    public function getRecommendCompanyModule($cs = '', $num = 3)
    {
        $companyDb = new CompanyModel();
        $list = $companyDb->getCompanyByKoubei($cs, $num);
        $count = count($list);
        if ($count < $num) {
            $fakeCompany = $companyDb->getCompanyByKoubei($cs, $num - $count, false);
            $list = array_merge($list ?: [], $fakeCompany ?: []);
        }
        //获取指定数据(减少缓存数据)
        $returnList = [];
        foreach ($list as $k => $v) {
            $returnList[$k] = [
                'logo' => changeImgUrl($v['logo']) ?: 'https://' . C('QINIU_DOMAIN') . '/Public/default/images/default_logo.png',
                'id' => $v['id'],
                'jc' => $v['jc'],
                'city_name' => $v['cname'],
                'area_name' => $v['qz_area'],
                'bm' => $v['bm'],
                'design_num' => $v['designnum'],
                'cases_num' => $v['casesnum'],
                'ping' => $v['ping'],
            ];
        }
        return $returnList;
    }

    public function getRecommendCompanyByCaseNum($cs = '', $num = 3,$passId = [])
    {
        $companyDb = new CompanyModel();
        $list = $companyDb->getCompanyByCase($cs, $num, true, $passId);
        $count = count($list);
        if ($count < $num) {
            $fakeCompany = $companyDb->getCompanyByCase($cs, $num - $count, false , $passId);
            $list = array_merge($list ?: [], $fakeCompany ?: []);
        }
        //获取指定数据(减少缓存数据)
        $returnList = [];
        foreach ($list as $k => $v) {
            $returnList[$k] = [
                'logo' => changeImgUrl($v['logo']) ?: 'https://' . C('QINIU_DOMAIN') . '/Public/default/images/default_logo.png',
                'id' => $v['id'],
                'jc' => $v['jc'],
                'city_name' => $v['cname'],
                'area_name' => $v['qz_area'],
                'bm' => $v['bm'],
                'design_num' => $v['designnum'],
                'cases_num' => $v['casesnum'],
                'ping' => $v['ping'],
            ];
        }
        return $returnList;
    }

    /**
     *  @des:获取装企宣传广告图，数据结构较为特殊，比照原主业标签相关处理
     * @param $companyList
     */
    public static function bindBanner(&$companyList)
    {
        if (!empty($companyList)) {
            $map['userid'] = ['in', array_column($companyList, 'id')];
            $map['status'] = 1;
            $bannerModel = new \Common\Model\CompanyModel();
            $bannerList = $bannerModel->getCompanyBanner($map);
            //处理逻辑，将超过5张的部分移除
            $companyExistArr = [];
            foreach (@$bannerList as $k => $v) {
                $bannerList[$k]['img_path'] = C("QINIU_SCHEME") . "://" . C("QINIU_DOMAIN") . "/" . $bannerList[$k]['img_path'];
                $bannerList[$k]['img_path_to_detail'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/company_home/' . $v['userid'] . '/';
                if (array_key_exists($v['userid'], $companyExistArr)) {
                    if (count($companyExistArr[$v['userid']]) > 4) {
                        unset($bannerList[$k]);
                    } else {
                        array_push($companyExistArr[$v['userid']], $v['id']);
                    }
                } else {
                    $companyExistArr[$v['userid']][] = $v['id'];
                }
            }
            unset($companyExistArr);
            array_walk($companyList, function (&$v1) use ($bannerList) {
                $v1['img_path'] = [];
                return array_map(function ($v2) use (&$v1){
                    if ($v1['id'] == $v2['userid']) {

                        $v1['img_path'][] = $v2;

                    }
                },$bannerList);
            });
        }
    }

    /**
     * @des:获取装修公司广告图
     * @param $company_id
     * @return mixed
     */
    public function getBannerInfoByCompanyId($company_id)
    {
        $map['userid'] = array("EQ", $company_id);
        $map['status'] = array("EQ", 1);
        $bannerModel = new \Common\Model\CompanyModel();
        $data = $bannerModel->getCompanyBannerInfo($map, 5);
        $result = [];
        foreach (@$data as $k => $v) {
            $result[]['img_path'] = C("QINIU_SCHEME") . "://" . C("QINIU_DOMAIN") . "/" . $v['img_path'] . "-w400.jpg";
        }
        return $result;
    }

}