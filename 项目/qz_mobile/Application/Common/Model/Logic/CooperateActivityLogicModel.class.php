<?php


namespace Common\Model\Logic;


use Common\Model\Db\CooperateActivityModel;

class CooperateActivityLogicModel
{

    //根据城市id获取活动
    public function getCooperateActivity($cityid = '',$pageIndex = 1)
    {
        $model = new CooperateActivityModel();

        $map = [];
        $pageCount = 10;
        if($cityid){
//            $map['city.cid'] = array('eq',$cityid);
            $map['_string'] = 'city.cid = '.$cityid.' or city.cid is null';     //这里虽然是拼接，但是这个$cityid是获取过来的， 所以不会有sql注入的风险。
        }
        $nowtime = time();
        $map['activity.start_at'] = array("elt",$nowtime);
        $map['activity.end_at'] = array("egt",$nowtime);
        $map['activity.state'] = array('eq',1);

        $count = $model->getCooperateActivityListCount($map);

        $pageTmp = '';
        $list = [];
        if($count > 0){
            import('Library.Org.Page.MobilePage');
            //自定义配置项
            $config  = array("prev","next");
            //移动端分页
            $page = new \MobilePage($pageIndex, $pageCount, $count, $config, "html");
            $pageTmp = $page->show2();
            $list = $model->getCooperateActivityList($map,($page->pageIndex-1)*$pageCount,$pageCount);
            foreach ($list as $key => $val){
                if(!$val['citynames']){
                    $list[$key]['citynames'] = '全国';
                }
                $list[$key]['citynames'] = $val['citynames'] ? mbstr($val['citynames'],0,10) : '全国';
            }
        }

        $return = [];
        $return['list'] = $list ? $list : [];
        $return['page'] = $pageTmp ? $pageTmp : '';
        return $return;
    }


    //
    public function getCooperateActivityInfoById($id)
    {
        $model = new CooperateActivityModel();
        $info = $model->getCooperateActivityInfoById($id);
        if($info){
            $info['detail'] = htmlspecialchars($info['detail']);
            return $info ? $info : [];
        }else{
            return array();
        }

    }

    //获取活动页面顶部广告位 banner图片
    public function getCooperateActivityBanner()
    {
        $model = new CooperateActivityModel();
        $bannerinfo = $model->getCooperateActivityBanner();
        return $bannerinfo;

    }

}