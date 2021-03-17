<?php
namespace Home\Model\Logic;

use Home\Model\Db\CooperateActivityBannerModel;
use Home\Model\Db\CooperateActivityCityModel;
use Home\Model\Db\CooperateActivityModel;

class CooperateLogicModel
{
    //获取顶部广告
    public function getBanner($type){
        $result = (new CooperateActivityBannerModel())->getBannerInfo($type);
        if(!empty($result[0]['id'])){
            $result = $result[0];
            $result['pc_url_quan'] = empty($result['pc_url'])?'':'"<img src=\"http://'.C('QINIU_DOMAIN').'/'.$result['pc_url'].'\" class=\"img-responsive\">"';
            $result['m_url_quan'] = empty($result['m_url'])?'':'"<img src=\"http://'.C('QINIU_DOMAIN').'/'.$result['m_url'].'\" class=\"img-responsive\">"';
            return $result;
        }
    }

    //获取活动列表
    public function getAcitivityList($get,$pageIndex = 1){
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = 20;
        $model = new CooperateActivityModel();
        $count = $model->getActivityListCount($get);
        $list = [];
        $show = '';
        if($count > 0){
            import('Library.Org.Util.Page');
            $p = new \Page($count, $pageCount);
            $show = $p->show();
            $list = $model->getActivityList($get, ($pageIndex - 1) * $pageCount, $pageCount);
            return ['list'=>$list,'page'=>$show];
        }

    }

    //获取单条活动信息
    public function getActivityInfo($id){
        $result = (new CooperateActivityModel())->getActivityInfo($id);
        if(!empty($result[0])){
            $result = $result[0];
            $result['start_at'] = date('Y-m-d',$result['start_at']);
            $result['end_at'] = date('Y-m-d',$result['end_at']);

            $result['business_logo_url'] = empty( $result['business_logo'])?'': '"<img src=\"http://'.C('QINIU_DOMAIN').'/'.$result['business_logo'].'\" class=\"img-responsive\">"';
            import('Library.Org.Util.Fiftercontact');
            $filter = new \Fiftercontact();
            $info['detail'] = $filter->filter_empty($result['detail']);
            return $result;
        }
    }

    //添加活动信息
    public function addInfo($data){
        $model = new CooperateActivityModel();

        $save['name'] = $data['name'];
        $save['start_at'] = strtotime($data['start_time']);
        $save['end_at'] = strtotime($data['end_time'].' 23:59:59');
        $save['prize'] =  $data['prize'];
        $save['detail'] =  htmlspecialchars_decode($data['detail']);
        $save['add_num'] =  empty($data['add_num'])?0:$data['add_num'];
        $save['business_name'] =  $data['sj_name'];
        $save['business_logo'] =  empty($data['logo'])?'custom/20190925/Ft35eTs3S_QZ-ouIPVMmBUsI-78Z':$data['logo'];
        $save['state'] =  empty($data['status'])?1:$data['status'];
        $save['px'] =  empty($data['px'])?0:$data['px'];
        $save['created_at'] = time();
        $save['updated_at'] =  time();
        M()->startTrans(); // 开启事务
        try{
            $result = $model->addActicity($save);
            if(!empty($data['city_ids'])){
                $save_ids = [];
                $data['city_ids'] = array_unique( array_filter($data['city_ids']));
                foreach($data['city_ids'] as $key=>$val){
                    $save_ids[$key]['aid'] = $result;
                    $save_ids[$key]['cid'] = $val;
                }
                (new CooperateActivityCityModel())->addCity($save_ids);
            }
            M()->commit(); // 事务提交
            return $result;
        } catch (\Exception $e) {
            // 回滚事务
            M()->rollback(); // 事务回滚
        }
    }

    //编辑活动信息
    public function editInfo($id,$data){
        $model = new CooperateActivityModel();
        $save['name'] = $data['name'];
        $save['start_at'] = strtotime($data['start_time']);
        $save['end_at'] = strtotime($data['end_time']);
        $save['prize'] =  $data['prize'];
        $save['detail'] =  htmlspecialchars_decode($data['detail']);
        $save['add_num'] =  empty($data['add_num'])?0:$data['add_num'];
        $save['business_name'] =  $data['sj_name'];
        $save['business_logo'] =  empty($data['logo'])?'custom/20190925/Ft35eTs3S_QZ-ouIPVMmBUsI-78Z':$data['logo'];
        $save['state'] =  empty($data['status'])?1:$data['status'];
        $save['px'] =  empty($data['px'])?0:$data['px'];
        $save['updated_at'] =  time();
        // 开启事务
        M()->startTrans();
        try{
            $result = $model->editActicity($id,$save);
            (new CooperateActivityCityModel())->deleteCity($id);
            if(!empty($data['city_ids'])){
                $save_ids = [];
                $data['city_ids'] = array_unique( array_filter($data['city_ids']));
                foreach($data['city_ids'] as $key=>$val){
                    $save_ids[$key]['aid'] = $id;
                    $save_ids[$key]['cid'] = $val;
                }
                (new CooperateActivityCityModel())->addCity($save_ids);
            }
            M()->commit(); // 事务提交
            return $result;
        } catch (\Exception $e) {
            M()->rollback();
        }


    }

    public function addBanner($type,$data){
        $model = new CooperateActivityBannerModel();
        $save['type'] = $type;
        $save['pc_url'] = $data['pc_url'];
        $save['m_url'] = $data['m_url'];
        $save['created_at'] = time();
        $save['updated_at'] = time();
        return $model->addBanner($save);
    }

    public function editBanner($type,$data){
        $model = new CooperateActivityBannerModel();
        $save['pc_url'] = $data['pc_url'];
        $save['m_url'] = $data['m_url'];
        $save['updated_at'] = time();
        return $model->updateBanner($type,$save);
    }

    public function changeState($id,$state){
        if($state == 1){
            $data['state'] = 2;
        }else{
            $data['state'] = 1;
        }
        $data['updated_at'] = time();
        $result = (new CooperateActivityModel())->editActicity($id,$data);
        return $result;
    }

    public function deleteActivity($id){
        //删除活动
        $result = (new CooperateActivityModel())->deleteActivity($id);
        //删除关联城市
        (new CooperateActivityCityModel())->deleteCity($id);
        return $result;
    }

    //获取城市信息
    public function getAllCity(){
        $city = S("Cache:cooperate:citys");
        if(!$city){
            $result = R("Api/getAllCity");
            $city = [];
            foreach ($result as $key => $value) {
                $city[$value['cid']]['cid'] = $value["cid"];
                $city[$value['cid']]['cname'] = $value["cname"];
            }
            array_unshift($city,["cid" => '0',"cname" => '- 全站 -']);
            S("Cache:cooperate:citys",$city,3600*24);
        }
        return $city;
    }
}
