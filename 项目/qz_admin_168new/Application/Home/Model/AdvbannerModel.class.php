<?php

//通栏广告Model  -- 对应 qz_adv_banner "通栏广告表"

namespace Home\Model;
Use Think\Model;

class AdvbannerModel extends Model{
    protected $autoCheckFields = false;
    protected $tableName = "adv_banner";
    protected $_validate = array(
        array('start_time','require','请填写广告开始时间',1,"",1),
        array('end_time','require','请填写广告结束时间',1,"",1),
        array('img_url','require','请上传通栏广告图片',1,"",1),
        array('start_time','checkDate','开始时间不能超过结束时间',1,"callback",1),

    );


    /**
     * 取单个广告 - 根据id
     * @param  [type] $id [广告ID]
     * @return [type]     [description]
     */
    public function getBannerById($id,$module){
        $map = array(
            'a.id' => array("EQ",$id),
            'a.module' => array('LIKE',"%$module%")
        );
        $result= M('adv_banner')->alias('a')
                                ->field('a.*,q.cname')
                                ->join('LEFT JOIN qz_quyu AS q ON q.cid = a.city_id')
                                ->where($map)->find();
        if(!empty($result['city_id'])){
            $cityids = getMyCityIds();
            if(!in_array($result['city_id'],$cityids)){
                return false;
            }
        }
        return $result;
    }

    /**
     * 添加广告
     * @param [type] $data [description]
     */
    public function addBanner($data){
        $cityids = getMyCityIds();
        if(!empty($data['city_id'])){
            if(!in_array($data['city_id'],$cityids)){
                return false;
            }
        }
        return  M('adv_banner')->add($data);
    }

    /**
     * 编辑广告
     * @param [type] $id   [广告ID]
     * @param [type] $data [description]
     */
    public function editBanner($id,$data){
        $map = array(
            'id' => array("EQ",$id),
        );
        return  M('adv_banner')->where($map)->save($data);
    }

    /**
     * 删除广告
     * @param [type] $id   [广告ID]
     * @param [type] $data [description]
     */
    public function removeBanner($id,$data){
        $map = array(
            'id' => array("EQ",$id),
        );
        return  M('adv_banner')->where($map)->delete();
    }

    /**
     * 编辑美图固定广告
     * @param [type] $sort   [美图位置]
     * @param [type] $data [description]
     */
    /*public function editMeituBanner($sort,$data){
        $map = array(
            'sort' => array("EQ",$sort),
            'module' => array("EQ","home_meitu"),
        );
        return  M('adv_banner')->where($map)->save($data);
    }*/

    /**
     * 删除广告
     * @return [type] [description]
     */
    public function delBanner($id){
        $map = array(
            'id' => array("EQ",$id),
        );
        return  M('adv_banner')->where($map)->delete();
    }

    /**
     * 获取通栏广告的数量
     * @param  [type] $map   [查询条件]
     * @param  [type] $order [排序规则]
     * @return [type]        [description]
     */
    public function getBigBannerListCount($map){
        return M("adv_banner")->where($map)->count();
    }


    /**
     * 获取通栏广告的的列表信息
     * @param  [type] $map   [查询条件]
     * @param  [type] $order [排序规则]
     * @return [type]        [description]
     */
    public function getBigBannerList($pageIndex,$pageCount,$map,$order)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $buildSql = M("adv_banner")->where($map)->order($order)->limit($pageIndex.",".$pageCount)->buildSql();
        return M("adv_banner")->table($buildSql)->alias("a")
                              ->field("a.*,b.cname")
                              ->join("LEFT JOIN qz_quyu as b on b.cid = a.city_id")->select();
    }

    //更改状态
    public function setStatus($id,$type = '0'){
        $data['status'] = $type;
        $map['id'] = $id;
        return M("adv_banner")->where($map)->save($data);
    }

    //更改排序
    public function updateSort($id,$sort = '0'){
        $data['sort'] = $sort;
        $map['id'] = $id;
        return M("adv_banner")->where($map)->save($data);
    }

    public function getBannerList($map,$pageIndex=1,$pageCount = 10,$order = 'id DESC')
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $map['a.id'] > 0;
        $Db = M('adv_banner');
        $count  = $Db->alias('a')->where($map)->count();
        $result = $Db->alias('a')->field('a.*,q.cname,k.bm')
                                 ->join('LEFT JOIN qz_quyu AS q ON q.cid = a.city_id')
                                 ->join('LEFT JOIN qz_user AS u ON u.id = a.company_id')
                                 ->join('LEFT JOIN qz_quyu AS k ON k.cid = u.cs')
                                 ->where($map)
                                 ->limit($pageIndex,$pageCount)
                                 ->order($order)
                                 ->select();
        return array("result"=>$result,"count"=>$count);
    }

    public function getAdvBannerListOnly($map,$pageIndex=1,$pageCount = 10,$order = 'id DESC')
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $map['a.id'] > 0;
        $Db = M('adv_banner');
        $count  = $Db->alias('a')->where($map)->count();
        $result = $Db->alias('a')->field('a.*,k.bm')
                                 ->join('LEFT JOIN qz_quyu AS k ON k.cid = a.city_id')
                                 ->where($map)
                                 ->limit($pageIndex,$pageCount)
                                 ->order($order)
                                 ->select();
        return array("result"=>$result,"count"=>$count);
    }

    /**
     * 获取相同位置通栏的数量
     * @return [type] [description]
     */
    public function getBigBnnerPositionCount($position,$city_id){
        $map = array(
                "module"=>array("EQ",$position),
                "city_id"=>array("EQ",$city_id)
                     );
        return M("adv_banner")->where($map)->count();
    }

    //检查数量
    public function checkAdvBannerNum($module,$cityid){
        $map['city_id'] = $cityid;
        $map['module'] = $module;
        $map['status'] = 1;
        return M('adv_banner')->where($map)->count();
    }

    //获取轮播 判断规则：1轮播图片在有效时间内+2启用中+3会员有效
    public function getActiveAdvBanners($module,$cityid){
        $map['a.city_id'] = $cityid;
        $map['a.module'] = $module;
        $map['a.status'] = '1';
        $map['u.on'] = '2';
        return M("adv_banner")->alias("a")
                    ->field('a.*,u.on')
                    ->join("LEFT JOIN qz_user as u on a.company_id = u.id")
                    ->where($map)
                    ->order('end_time')
                    ->select();
    }

    //查找公司对应的简称
    public function getCompanyJc($id){
        $map['id'] = $id;
        return M('user')->field('jc')->where($map)->select();
    }

    //查找轮播显示日志
    public function getAdvBannerShowLog($map,$pagesize= 1,$pageRow = 10){
        $count  = M('adv_banner_showlog')->where($map)->count();
        $result = M('adv_banner_showlog')->field('*')
                    ->where($map)
                    ->order('dates')
                    ->limit($pagesize.",".$pageRow)
                    ->select();
        return array("result"=>$result,"count"=>$count);
    }

    //统计轮播显示日志
    public function countBannerShowLog($start,$end,$companyid){
        $map['cid'] = $companyid;
        $map['dates'] = array('between',array(date('Ymd',strtotime($start)),date('Ymd',strtotime($end))));
        return M('adv_banner_showlog')
                    ->where($map)
                    ->count();
    }

    //取轮播列表
    public function getAdvBannerList($map,$pagesize= 1,$pageRow = 10){
        $orderby = $map['orderBy'];
        unset($map['orderBy']);

        //如果搜索条件没有city_id时取所有的
        if(empty($map['a.city_id'])){
            if(!in_array(session("uc_userinfo.uid"),getAllCityManager())){
                $cityIds = getMyCityIds();
                $map['a.city_id'] = array('IN',implode(',',$cityIds));
            }
        }

        $Db = M('adv_banner');
        $count  = $Db->alias("a")->where($map)->count();
        $result = $Db->alias("a")->field('a.*,q.cname city_name')
                      ->join("LEFT JOIN ". C('DB_PREFIX') ."quyu as q on a.city_id = q.cid")
                      ->order($orderby)
                      ->limit($pagesize.",".$pageRow)
                      ->where($map)
                      ->select();
        return array("result"=>$result,"count"=>$count);
    }

    /**
     * 验证时间
     * @return [type] [description]
     */
    protected function checkDate($start){
        $end = I("post.end_time");
        if($start > strtotime($end)){
            return false;
        }
        return true;
    }
}

