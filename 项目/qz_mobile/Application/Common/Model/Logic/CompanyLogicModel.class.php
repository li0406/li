<?php
namespace Common\Model\Logic;

use Common\Model\Db\CardModel;
use Common\Model\Db\CommentModel;
use Common\Model\Db\CompanyActivityModel;
use Common\Model\Db\CompanyImgModel;
use Common\Model\Db\CompanyModel;
use Common\Model\CompanyModel as CompanyModel2;
use Common\Model\Db\UserCompanyEmployeeModel;
use Common\Model\Db\UserModel;


class CompanyLogicModel
{
    protected $autoCheckFields = false;


    //新签返员工职位
    private $companyEmployeeZw = [
        1 => '客服',
        2 => '设计师',
        3 => '首席设计师',
        4 => '设计总监',
    ];

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

    //获取公司最新活动
    public function getCompanyActivity($id){
        $model = new CompanyActivityModel();
        $result = $model->getCompanyActivity('id,title',$id);
        if(!empty($result)){
            $result['title'] = mbstr($result['title'],0,16);
            return $result;
        }
    }

    //获取活动详情
    public function getEvent($id){
        $model = new CompanyActivityModel();
        $result = $model->getCompanyActivity('id,cid,title,text,views,start,end','',$id);
        if(!empty($result)){
            $result['text'] = isset( $result['text'] )?htmlspecialchars_decode($result['text']):'';
        }
        return $result;
    }

     /**
     * 获取用户信息
     * @param  [type] $id [用户编号]
     * @param  [type] $cs [所在城市]
     * @return [type]     [description]
     */
    public function getUserInfo($id,$cs){
        $cache_key = $id . ':' . $cs;
        $user = S('Cache:M:Companyhome:userInfo:' . $cache_key);
        if (!empty($user)) {
            return $user;
        }

        $result =  D("User")->getUserInfoById($id,$cs);
        $user = array();
        if($result[0]["id"] != 0){
            import('Library.Org.Util.Fiftercontact');
            $filter = new \Fiftercontact();
            $contact_q1 = OP('QZ_CONTACT_QQ1');
            $contact_q2 = OP('QZ_CONTACT_QQ2');
            $contact_t400 = OP("QZ_CONTACT_TEL400");
            foreach ($result as $key => $value) {
                if($key == 0){
                    $user["id"] = $value["id"];
                    $user["hengfu"] = $value["hengfu"];
                    $user["img_host"] = $value["img_host"];
                    $user["on"] = $value["on"];
                    $user["qc"] = $value["qc"];
                    $user["jc"] = $value["jc"];
                    $user["kouhao"] = $value["kouhao"];
                    $user["logo"] = $value["logo"];
                    $user["pv"] = $value["pv"];
                    $user["jianjie"] =$filter->fifter_contact($value["jianjie"]);
                    $user["jiawei"] = $value["jiawei"];
                    $user["fake"] = $value["fake"];
                    $user["nickname"] = empty($value["nickname"])== true?"家装咨询顾问":$value["nickname"];
                    $user["nickname1"] = empty($value["nickname1"])== true?"公装咨询顾问":$value["nickname1"];
                    $user["area"] = $value["area"];
                    $user["fw"] = $value["fw"];
                    $user["fg"] = $value["fg"];
                    $user["dcount"] = $value["dcount"];
                    $user["ccount"] = $value["ccount"];
                    $user["avgsj"] = round($value["avgsj"],1);
                    $user["avgfw"] = round($value["avgfw"],1);
                    $user["avgsg"] = round($value["avgsg"],1);
                    $user["avgscore"] = round(($value["avgsj"]+$value["avgfw"]+$value["avgsg"])/3,1);
                    $user["avgcount"] = round($value["avgcount"],1) == 0?1:round($value["avgcount"],1);
                    $user["casecount"] = $value["casecount"];
                    $user["video"] = $value["video"];
                    $user["qq"] =  empty($value["qq"]) ==true?$contact_q1:($value["on"] != 2 || $value["fake"] !=0)?$contact_q1:$value["qq"];
                    $user["qq1"] = empty($value["qq1"])==true?$contact_q2:($value["on"] != 2 || $value["fake"] !=0)?$contact_q2:$value["qq1"];
                    $user["dz"] = $value["dz"];
                    $user["cal"] = empty($value["cal"])?"":($value["on"] != 2 || $value["fake"] !=0)?"":$value["cal"];
                    $user["cals"] = empty($value["cals"])?$contact_t400:($value["on"] != 2 || $value["fake"] !=0)?$contact_t400:$value["cals"];
                    $user["tel"] = empty($value["tel"])?$contact_t400:($value["on"] != 2 || $value["fake"] !=0)?$contact_t400:$value["tel"];
                    $user["cs"] = $value["cs"];
                    $user["gm"] = $value["gm"];
                    $user["chengli"] = date("Y年m月",strtotime($value["chengli"]));
                    $user["good"] = round(($value["good"]/$value["newcount"])*100,2);
                    $user["oldgood"] =round(($value["oldgood"]/$value["oldcount"])*100,2);
                    $user["evaluation"] = $user["avgcuont"];
                    if($value["avgsj"] != 0 && $value["avgfw"] != 0 && $value["avgsg"] != 0){
                        $user["evaluation"] = round(($value["avgsj"]+$value["avgfw"]+$value["avgsg"])/3,2);
                    }
                }
                if(!empty($value["hid"])){
                    $sub = array(
                        "name"=>$value["shortname"],
                        "tel" =>$value["htel"],
                        "addr"=>$value["addr"],
                        "qq"=> empty($value["qq3"]) ==true?$contact_q1:($value["on"] != 2 || $value["fake"] !=0)?$contact_q1:$value["qq3"],
                        "qq1"=>empty($value["qq4"]) ==true?$contact_q1:($value["on"] != 2 || $value["fake"] !=0)?$contact_q1:$value["qq4"],
                        "nickname"=>empty($value["nickname2"])== true?"家装咨询顾问":$value["nickname2"],
                        "nickname1"=>empty($value["nickname3"])== true?"家装咨询顾问":$value["nickname3"]
                    );
                    $user["child"][] = $sub;
                }
            }
        }

        S('Cache:M:Companyhome:userInfo:' . $cache_key, $user, 900);

        return $user;
    }

    //获取营业执照
    public function getBusinessLicence($id){
        $model = new CompanyModel();
        $result = $model->getBusinessLicence($id);
        $result['img1'] = empty($result['img1'])?'':"//".C('QINIU_DOMAIN')."/".$result['img1'];
        $result['img2'] = empty($result['img2'])?'':"//".C('QINIU_DOMAIN')."/".$result['img2'];
        $result['img3'] = empty($result['img3'])?'':"//".C('QINIU_DOMAIN')."/".$result['img3'];
        $result['img4'] = empty($result['img4'])?'':"//".C('QINIU_DOMAIN')."/".$result['img4'];
        return $result;
    }

    // 获取公司基本信息
    public function getCompanyBaseInfo($id)
    {
        $model = new CompanyModel();
        $companyInfo = $model->getCompanyBaseInfoById($id);
        return $companyInfo;
    }

    //获取公司标签
    public function getCompanyLimitTags($id)
    {
        $model = new CompanyModel();
        return $model->getCompanyTagsAtLimit($id, 3);
    }

    //获取公司标签
    public function getCompanyTags($id, $mode = 1){
        $model = new CompanyModel();
        return $model->getCompanyTags($id, $mode);
    }

    public function getStar($score){
        //计算星星
        if($score >= 9 ){
            $star = 5;
        }elseif($score >= 8 &&$score< 9){
            $star = 4;
        }elseif($score >= 6 && $score < 8){
            $star = 3;
        }elseif($score >= 4 && $score < 6){
            $star = 2;
        }else{
            $star = 1;
        }

        return $star;
    }

    //获取设计师
    public function getDesignerList($id,$limit){
        $model = new UserModel();
        $result['count'] = $model->getDesignerListCountByCompany($id);
        if($result['count']>0){
            $result['list'] = $model->getDesignerListByCompany($id,$limit);
        }

        return $result;
    }

    //获取新签返设计师
    public function getNewDesignerList($id,$pageIndex,$limit){
        $model = new UserCompanyEmployeeModel();
        $result['count'] = $model->getNewDesignerListCountByCompany($id);
        if($result['count']>0){
            import('Library.Org.Page.MobilePage');
            //自定义配置项
            $config = array("prev", "next");
            //移动端分页
            $page = new \MobilePage($pageIndex, $limit, $result['count'], $config, "html");
            $pageTmp = $page->show2();
            $result['list'] = $model->getNewDesignerListByCompany($id,($pageIndex-1)*$limit,$limit);
            foreach ($result['list'] as $key=>$value){
                $result['list'][$key]['zw'] = $this->companyEmployeeZw[$value['position']];
            }
            $result['page'] = $pageTmp;
        }
        return $result;
    }

    /**
     * 获取业主评论
     * @param  [type] $id    [公司编号]
     * @param  [type] $cs    [所在城市]
     * @param  [type] $limit [显示数量]
     * @param  [type] $reply [是否显示回复]
     * @return [type]        [description]
     */
    public function getComments($id,$cs,$pageIndex,$pageCount,$reply)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);
        $model = new CommentModel();
        $count = $model->getCommentByComIdCount($id,$cs);
        if($count > 0){
            import('Library.Org.Page.LitePage');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \LitePage($pageIndex,$pageCount,$count,$config);
            $pageTmp =  $page->show();

            $comments = $model->getCommentByComId($id,$cs,($page->pageIndex-1)*$pageCount,$pageCount,$reply);
            foreach ($comments as $key => $value) {
                $total = $value["count"];
                if($value["sj"] != 0 && $value["fw"]!= 0 && $value["sg"]!= 0){
                    $total = round((($value["sj"]+$value["fw"]+$value["sg"])/3),1);

                }
                $comments[$key]["totalCount"] = $total;
                if(!$value['logo']){
                    $comments[$key]['logo'] = changeImgUrl('public/default/images/default_avator.jpg');
                }else{
                    $comments[$key]['logo'] = changeImgUrl($value['logo'], 2);
                }

                $imglist = $model->getCommentImgsByCommentid($value['id']);
                $showlist = [];
                if($imglist){
                    foreach ($imglist as $k => $v){
                        $showlist[$k] = changeImgUrl($v['imgurl'],2);
                    }
                }
                $comments[$key]['comment_imgs'] = $showlist;
                $comments[$key]['comment_countimgs'] = count($showlist);
            }

            return array("comments"=>$comments,"page"=>$pageTmp,"count"=>$count);
        }
        return null;
    }

    /**
     * 获取公司列表的评分
     * @param array $companyIds
     * @return mixed
     */
    public function getCommentScores(array $companyIds)
    {
        $ret = D('comment')->getCommentScores($companyIds);
        $formatted = [];
        foreach ($ret as $key=>$value) {
            $formatted[$value['comid']] = $value;
            $formatted[$value['comid']]["evaluation"] = $value["avgcuont"];
            if($value["avgsj"] != 0 && $value["avgfw"] != 0 && $value["avgsg"] != 0){
                $formatted[$value['comid']]["evaluation"] = round(($value["avgsj"]+$value["avgfw"]+$value["avgsg"])/3,2);
            }
            $formatted[$value['comid']]['star'] = $this->getStar($formatted[$value['comid']]["evaluation"] );
        }
        return $formatted;
    }


    /**
     * 口碑排行
     * @param $map
     * @param $pageIndex
     * @param $pageCount
     * @return array
     */
    public function getStarList($map, $pageIndex, $pageCount)
    {
        $companymodel = new CompanyModel();
        return $companymodel->getStarList($map, $pageIndex, $pageCount);
    }

    /**
     * 量房排行
     * @param $map
     * @param $pageIndex
     * @param $pageCount
     * @return mixed
     */
    public function getLiangfangList($map, $pageIndex, $pageCount)
    {
        $companymodel = new CompanyModel();
        return $companymodel->getLiangfangList($map, $pageIndex, $pageCount);
    }

    /**
     * 最新签单排行
     * @param $map
     * @param $pageIndex
     * @param $pageCount
     * @return mixed
     */
    public function getQiandanList($map, $pageIndex, $pageCount)
    {
        $companymodel = new CompanyModel();
        return $companymodel->getQiandanList($map, $pageIndex, $pageCount);
    }

    /**
     * 综合实力排行
     * @param $map
     * @param $pageIndex
     * @param $pageCount
     * @return mixed
     */
    public function getShiliList($map, $pageIndex, $pageCount)
    {
        $companymodel = new CompanyModel();
        return $companymodel->getShiliList($map, $pageIndex, $pageCount);
    }

    /**
     * 获取推荐
     * @param $map
     * @param int $count
     * @return mixed
     */
    public function getRecommendList($map,$count=0)
    {
        $companymodel2 = new CompanyModel2();
        return $companymodel2->getRecommendList($map,$count);

    }

    public function getCompanyCount($map)
    {
        $companymodel2 = new CompanyModel2();
        $count =  $companymodel2->getCompanyCount($map);
        return $count;

    }

    /**
     * 根据装修公司id获取装修公司的评论星级
     * @param $comId
     * @return array|mixed
     */
    public function getCompanyScoreInfo($comId)
    {
        $model = new CompanyModel();
        $value = $model->getCompanyScoreInfo($comId);
        $user = [];
        $user["avgscore"] = round(($value["avgsj"]+$value["avgfw"]+$value["avgsg"])/3,1);
        $user["avgcount"] = round($value["avgcount"],1) == 0?1:round($value["avgcount"],1);
        $user["evaluation"] = $user["avgcuont"];
        if($value["avgsj"] != 0 && $value["avgfw"] != 0 && $value["avgsg"] != 0){
            $user["evaluation"] = round(($value["avgsj"]+$value["avgfw"]+$value["avgsg"])/3,2);
        }
        $comment_score = $user["evaluation"] ? $user["evaluation"] : 0;

        return $comment_score;
    }

    /**
     * 根据装修公司id获取装修公司的评论星级
     * @param $comId
     * @return array|mixed
     */
    public function getCompanyAllScoreInfo($comIds)
    {
        $model = new CompanyModel();
        $list = $model->getCompanyAllScoreInfo($comIds);

        $ret = [];
        foreach ($list as $key => $value) {
            $user = [];
            $user["avgscore"] = round(($value["avgsj"] + $value["avgfw"] + $value["avgsg"]) / 3, 1);
            $user["avgcount"] = round($value["avgcount"], 1) == 0 ? 1 : round($value["avgcount"], 1);
            $user["evaluation"] = $user["avgcount"];
            if($value["avgsj"] != 0 && $value["avgfw"] != 0 && $value["avgsg"] != 0){
                $user["evaluation"] = round(($value["avgsj"] + $value["avgfw"] + $value["avgsg"]) / 3, 2);
            }

            $ret[$value["comid"]] = $user["evaluation"] ? $user["evaluation"] : 0;
        }

        return $ret;
    }

    /**
     * 获取口碑列表
     * @param $cs
     * @param int $num
     * @return array
     */
    public function getKoubeiList($cs, $num = 8)
    {
        $companyDb = new CompanyModel();
        $list = $companyDb->getSubKoubeiRank($cs, $num);
        foreach ($list as $k => $v) {
            $list[$k]['logo'] = changeImgUrl($v['logo']) ?: 'https://' . C('QINIU_DOMAIN') . '/Public/default/images/default_logo.png';
        }
        return $list;
    }

    public function getCompanyEmployee($company_id)
    {
        $employeeDb = new UserCompanyEmployeeModel();
        return $employeeDb->getNewDesignerListCountByCompany($company_id);
    }

    public function getCompanyBannersList($company_id, $limit=5)
    {
        $model = new CompanyModel();
        $list = $model->getCompanyBannersById($company_id, $limit);

        foreach ($list as $k => &$v) {
            $v['img_path'] = changeImgUrl($v['img_path']);
        }

        return $list;
    }

    public function getCompanyActivityList($companyId)
    {

        $model = new CompanyModel();
        $list = $model->getActivityByComId($companyId);

        return $list ?: [];
    }

    // 获取文章模块推荐装修公司
    public function getRecommendCompanyList($city_id, $limit = 20, $offset = 0){
        $companyModel = new CompanyModel();
        $list = $companyModel->getRecommendCompanyList($city_id, $limit, $offset);

        if (count($list) > 0) {
            $list = $this->setRecommendCompanyFormatter($list);
        }

        return $list;
    }

    // 推荐装修公司数据格式处理
    private function setRecommendCompanyFormatter($list){
        if (count($list) > 0) {
            $companyIds = array_column($list, "id");
            $scores = $this->getCompanyAllScoreInfo($companyIds);

            $companyActivityModel = new CompanyActivityModel();
            $activityList = $companyActivityModel->getCompanyFirstActivity($companyIds);
            $activityList = array_column($activityList, null, "cid");

            $cardModel = new CardModel();
            $cardList = $cardModel->getCompanyFirstCard($companyIds);
            $cardList = array_column($cardList, null, "com_id");

            foreach ($list as $key => &$item) {
                $item["haoping_rate"] = $item["ping"] > 0 ? round($item["haoping"] / $item["ping"] * 100, 2) : 0;
                $item["comment_score"] = $scores[$item["id"]] ? : $item["comment_score"];
                $item["star"] = $this->getStar($item["comment_score"]);

                if (empty($item["logo"])) {
                    $item["logo"] = changeImgUrl("Public/default/images/default_logo.png");
                }

                $item["activity"] = $activityList[$item["id"]];
                $item["card_info"] = $cardList[$item["id"]];
            }
        }

        return $list;
    }

    public function getCompanyOther($id)
    {
        $companyModel = new CompanyModel();
        $other = $companyModel->getCompanyOtherInfo($id);
        if(empty($other)) return [];

        if(!$other['ping']){
            $other['haopinglv'] = 0;
        }else{
            $other['haopinglv'] = round($other['haoping']/$other['ping'], 4);
        }

        return $other;
    }

    public function getCompanyDesigner($ids){
        $companyModel = new CompanyModel();
        return $companyModel->getNewDesignerByCompanyId($ids);
    }

    /**
     * 获取随机的几家公司(综合排序)
     */
    public function getZongheShiliList($condition=[], $limit=4)
    {
        $cacheParam = array(
            "limit" => $limit,
            "cs" => $condition['cs']
        );

        $cacheMapKey = md5(serialize($cacheParam));
        $result = S("Cache:Mobile:zhixuanCompanyList:" . $cacheMapKey);

        if(empty($result)){
            $pageStart = 0;
            $companymodel = new CompanyModel();

            $condition['true_user'] = 1;
            $count = $companymodel->getZongheShiliCount($condition);
            if($count < 4){
                unset($map['true_user']);
            }else{
                $totalPage = ceil($count/$limit);
                $pageRand = rand(1, $totalPage);

                $pageStart = ($pageRand-1)*$limit;    
            }

            $result = $companymodel->getZongheShiliList($condition, $pageStart, $limit);
            S("Cache:Mobile:zhixuanCompanyList:" . $cacheMapKey, $result,  random_int(300, 600));
        }

        return $result;
    }
}
