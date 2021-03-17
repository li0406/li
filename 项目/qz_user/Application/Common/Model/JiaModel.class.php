<?php
/**
 *  小区表 qz_xiaoqu
 */
namespace Common\Model;
use Think\Model;

class JiaModel extends Model{
    protected $autoCheckFields = false; //设置autoCheckFields属性为false后，就会关闭字段信息的自动检测，因为ThinkPHP采用的是惰性数据库连接，只要你不进行数据库查询操作，是不会连接数据库的。

    /**
     * 通过城市的cid，获取城市所属的区域
     * @param  $city-城市id
     * @return 区域的列表  id,cid,cname,aid,aname,url
     */
    public function getAreaByCityId($city){
        $city = intval($city);
        /*$map['cid'] = intval($city);
        $map['aid'] = array('NEQ','');
        $area = M("xiaoqu_quyus")->where($map)->select();*/

        $map['fatherid'] = intval($city);
        $map_N['cid'] = intval($city);
        $area = M("area")->where($map)->select();
        $cityName = M('quyu')->field("cname")->where($map_N)->select();
        foreach ($area as $key => $value) {
            $arealist[$key]['cid'] = $value['fatherid'];
            $arealist[$key]['cname'] = $cityName['0']['cname'];
            $arealist[$key]['aid'] = $value['qz_areaid'];
            $arealist[$key]['aname'] = $value['qz_area'];
        }

        //dump($arealist);
        return $arealist;
    }

    /**
     * 通过城市和区域--获取小区
     * @param  $city-城市id；$qid-区域id
     * @return 小区的列表  id,cid,cname,aid,aname,url
     */
    public function getXiaoquByCityArea($cid,$qid,$pagesize = 1,$pageRow = 10){
        if($qid != '0'){
            $map_jia['qid'] = $qid;
        }

        $map_jia['cid'] = $cid;
        $pagesize = ($pagesize-1) * 10;
        $result = M("xiaoqu")->field("id,city,cid,quyu,qid,name,dizhi,mapx,mapy")->where($map_jia)->limit($pagesize,$pageRow)->order("casecount desc")->select();
        foreach ($result as $key => $value) {
            $map_logo['uid'] = $value['id'];
            $map_logo['type'] = '0';
            $logo = M("xiaoqu_imgs")->field("uid,description,img_path")->where($map_logo)->select();
            if(!empty($logo)){
                $result[$key]['logo_path'] = $logo['0']['img_path'];
                $result[$key]['logo_title'] = $logo['0']['description'];
            }else{
                    $result[$key]['logo_path'] = '';
                    $result[$key]['logo_title'] = '';
            }
        }
        return $result;
    }

    //通过城市区域--获取小区数量
    public function getXiaoquByCityAreaCount($cid,$qid){
        if($qid != '0'){
            $map_jia['qid'] = $qid;
        }
        $map_jia['cid'] = $cid;
        $result = M("xiaoqu")->where($map_jia)->count();
        return $result;
    }

    /**
     * indexye页面通过小区的id--获取和小区对应的案例
     */
    //通过小区的id--获取和小区对应的案例
    public function getCaseByXiaoqu($idList){
        $idList = intval($idList);
        $map = array(
                     'a.on' =>'1',
                     'a.isdelete' => '1'
                     );
        $result = M("cases")->alias("a")
                ->field("a.id,a.`on`,a.uid,a.classid,a.qx,a.title,a.mianji,a.zaojia,a.userid,a.time,a.cs,u.qc,f.`name` as fengge ")
                ->join(" INNER JOIN qz_xiaoqu as c on c.id = '$idList' ")
                ->join(" INNER JOIN qz_xiaoqu_relation as b on a.id = b.cases_id AND b.xiaoqu_id = c.id ")
                ->join(" INNER JOIN qz_user as u on a.uid = u.id")
                ->join(" INNER JOIN qz_fengge as f on f.id = a.fengge")
                ->order("time desc")
                ->where($map)
                ->select();
        return $result;
    }

    //通过输入名字查询小区数据
    public function getXiaoquByName($cid,$name,$pagesize = 1,$pageRow = 10){

        $map_search['cid'] = $cid;
        $map_search['name'] = array("LIKE","%$name%");
        $pagesize = ($pagesize -1) * 10;
        $result = D('xiaoqu')->field("id,city,cid,quyu,qid,name,dizhi,mapx,mapy")
                             ->where($map_search)
                             ->limit($pagesize,$pageRow)
                             ->order("casecount desc")
                             ->select();
        foreach ($result as $key => $value) {
            $map_logo['uid'] = $value['id'];
            $map_logo['type'] = '0';
            $logo = M("xiaoqu_imgs")->field("uid,description,img_path")->where($map_logo)->select();
            if(!empty($logo)){
                $result[$key]['logo_path'] = $logo['0']['img_path'];
                $result[$key]['logo_title'] = $logo['0']['description'];
            }else{
                $result[$key]['logo_path'] = '';
                $result[$key]['logo_title'] = '';
            }
        }
        return $result;
    }

    public function getXiaoquByNameCount($cid,$name){
        $map_search['cid'] = $cid;
        $map_search['name'] = array("LIKE","%$name%");
        $result = D('xiaoqu')->where($map_search)->count();
        return $result;
    }

    //根据小区id查找小区
    public function getXiaoquById($id){
        $map['id'] = intval($id);
        $result = D('xiaoqu')->where($map)->select();
        $bm = D('Area')->getCityInfoById($result['0']['cid']);
        $result['0']['bm'] = $bm['bm'];
        foreach ($result as $key => $value) {
            $map_img['uid'] = $value['id'];
            $logo = M("xiaoqu_imgs")->field("uid,description,img_path,type,time")->where($map_img)->order("type asc")->select();
            foreach ($logo as $k => $v) {
                if($v['type'] == '0'){
                    $result[$key]['logo_path'] = $v['img_path'];
                    $result[$key]['logo_title'] = $v['description'];
                    $logo[$k]['typeName'] = "封面图";
                    //array_shift($logo);
                }else if($v['type'] == '1'){
                    $logo[$k]['typeName'] = "户型图";
                }else if($v['type'] == '2'){
                    $logo[$k]['typeName'] = "实景图";
                }else{
                    $logo[$k]['typeName'] = "效果图";
                }
            }
            $result[$key]['img'] = $logo;
        }
        return $result;
    }

    //详情页面 通过小区的id--获取和小区
    //对应的案例
    public function getCaseById($idList,$classid = null,$pagesize = 1,$pageRow = 6){
        //$classid 区分 家装案例、在建案例、公装案例
        //  1-家装案例    2-在建案例、公装案例
        $idList = intval($idList);
        if($classid != null){
            $map['a.classid'] = $classid == '1' ? array('eq',1) : array('neq',1);
        }
        $pagesize = ($pagesize-1) * 6;
        $result = M("cases")->alias("a")
                  ->field("a.id,a.`on`,a.uid,a.classid,a.qx,a.title,a.mianji,a.zaojia,a.userid,a.time,a.cs,u.qc,f.`name` as fengge")
                  ->join(" INNER JOIN qz_xiaoqu as c on c.id = '$idList' ")
                  ->join(" INNER JOIN qz_xiaoqu_relation as b on a.id = b.cases_id AND b.xiaoqu_id = c.id ")
                  ->join(" INNER JOIN qz_user as u on a.uid = u.id")
                  ->join(" INNER JOIN qz_fengge as f on f.id = a.fengge")
                  ->where($map)
                  ->order("time desc")
                  ->limit($pagesize,$pageRow)
                  ->select();

        foreach ($result as $key => $value) {
                $logo = D("Cases")->getCaseCoverImg($value['id']);
                $result[$key]['logo'] = $logo['0'];
        }

        return $result;
    }
    //--根据小区id，查询案例数据
    /*public function getCaseByIdCount($id){
        $data['xiaoqu_id'] = $id;
        $result = M("xiaoqu_relation")->where($data)->count();
        return $result;
    }*/
    //--根据小区id，查询案例数据
    public function getCaseByIdCount($id,$classid = null){
        $id = intval($id);
      //$classid 区分 家装案例、在建案例、公装案例
        //  1-家装案例    2-在建案例、公装案例
        if($classid != null){
            $map['a.classid'] = $classid == '1' ? array('eq',1) : array('neq',1);
        }
        $result = M('cases')->alias("a")
                            ->join(" INNER JOIN qz_xiaoqu_relation as b on a.id = b.cases_id AND b.xiaoqu_id = '$id' ")
                            ->where($map)
                            ->count();
        return $result;
    }

    //--map随机查询2个小区数据
    public function getXiaoquByRand($qid){
        $map_jia['qid'] = $qid;
        //$sql = "select id,cid,qid,name,dizhi,logo from qz_xiaoqu WHERE qid = '$qid' ORDER BY RAND() LIMIT 4";
        $result = M("xiaoqu")->field("id,cid,qid,name,dizhi")
                             ->where($map_jia)
                             ->order('rand()')
                             ->limit('4')
                             ->select();
        foreach ($result as $key => $value) {
            $map_logo['uid'] = $value['id'];
            $map_logo['type'] = '0';
            $logo = M("xiaoqu_imgs")->field("uid,description,img_path")->where($map_logo)->select();
            if(!empty($logo)){
                $result[$key]['logo_path'] = $logo['0']['img_path'];
                $result[$key]['logo_title'] = $logo['0']['description'];
            }
        }
        return $result;
    }

}
