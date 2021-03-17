<?php
namespace Common\Model\Logic;

use Common\Model\Db\CasesModel;
use Common\Model\Db\CompanyModel;


class CasesLogicModel
{
    protected $autoCheckFields = false;

    protected $class = [1=>'家装',2=>'公装',3=>'工地'];
    //获取有案例的风格分类
    public function getFenggeCategory($comid,$cs){
        $model  = new CasesModel();
        return $model->getFenggeCategory($comid,$cs);
    }

    //案例列表页
    public function getCasesListByComId($pageIndex,$pageCount,$comid,$SCHEME_HOST,$cityInfo,$category='',$class="")
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);
        $model  = new CasesModel();
        $count = $model->getCasesListByComIdCount($comid,$cityInfo['id'],$category,$class);
        if($count > 0){
            import('Library.Org.Page.MobilePage');
            //自定义配置项
            $config = array("prev", "next");
            $page =  new \MobilePage($pageIndex, $pageCount, $count, $config);
            $pageTmp =  $page->show2();
            $list = $model->getCasesListByComId(($page->pageIndex-1)*$pageCount,$pageCount,$comid,$cityInfo['id'],$category,$class);
            $cases = [];
            if(!empty($list)){
                $cases = $this->getCasesList($list,$SCHEME_HOST,$cityInfo);
            }

        }
        return array("list"=>$cases,"page"=>$pageTmp,"count"=>$count);
    }

    //整理案例显示数据
    public function getCasesList($list,$SCHEME_HOST,$cityInfo){
        foreach($list as $key=>$val){
            $cases[$key]['id'] = $val['id'];
            $cases[$key]['title'] = mbstr($val['title'],0,15);
            if($val['img_host']  == 'qiniu'){
                $cases[$key]['img_path'] =  $SCHEME_HOST["scheme_full"].C('QINIU_DOMAIN')."/".$val["img_path"]."-w640.jpg";
            }else{
                $cases[$key]['img_path'] =  $SCHEME_HOST["scheme_full"].C('STATIC_HOST1').$val["img_path"]."m_".$val["img"];
            }
            $cases[$key]['fengge'] = $val['fg'];
            $cases[$key]['mianji'] = $val['mianji'];
            $cases[$key]['jiage'] = $val['jiage'];
            $cases[$key]['hx'] = $val['hx'];
            $cases[$key]['href'] = $SCHEME_HOST['scheme_full'].$SCHEME_HOST['host'].'/'.$cityInfo['bm'].'/caseinfo/'.$val['id'].'.shtml';
            $cases[$key]['class'] = $this->class[$val['classid']];
        }
        return $cases;
    }

    public function getCaseInfo($id = '',$cs = ''){
        $caseInfo = D("Common/Cases")->getMobileCaseInfo($id,$cs);
        if(count($caseInfo) > 0){
            $scheme_host = getSchemeAndHost();
            
            foreach ($caseInfo as $key => $value) {
                if($value['img_host'] == 'qiniu'){
                    $value['img_path'] = $scheme_host["scheme_full"].C('QINIU_DOMAIN')."/".$value["img_path"]."-w640.jpg";
                }else{
                    $value['img_path'] = $scheme_host["scheme_full"].C('STATIC_HOST1').$value["img_path"]."m_".$value["img"];
                }
                if($key == 0){
                    $case = $value;
                    $case['huxing'] = isset($value['huxing'])?$value['huxing']:$value['lx'];
                    $case['class'] = $this->class[$value['classid']];
                }else{
                    if($value['status']<3){
                        $case["child"][] = $value['img_path'];
                    }

                }

            }
            $case['imgcount'] = count($case["child"]);
            return $case;
        }
        return null;
    }

    /**
     * 根据公司id获取对应的最新上传案例图片
     * @param $companyIds
     * @return mixed
     */
    public function getLastCaseImg($companyIds)
    {
        $formatted = [];
        if(!empty($companyIds)){
            $ret = D("Common/Cases")->getLastCaseImg($companyIds);
            foreach ($ret as $key => $value) {
                $formatted[$value['uid']] = $value;
            }
        }
        return $formatted;
    }

    // 获取推荐装修案例
    public function getRecommendCases($city_id, $limit = 20, $offset = 0){
        $casesModel = new CasesModel();
        $list = $casesModel->getRecommendCasesList($city_id, $limit, $offset);

        foreach ($list as $key => &$item) {
            $item["thumb"] = changeImgUrl($item["thumb"]);

            if (!empty($item["thumb"])) {
                $item["image"] = changeImgUrl($item["thumb"]);
            } else {
                $item["image"] = changeImgUrl($item["img_path"]);
            }
        }

        return $list;
    }
}