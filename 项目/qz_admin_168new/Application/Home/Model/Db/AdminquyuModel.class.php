<?php
namespace Home\Model\Db;
use Think\Model;
class AdminquyuModel extends Model{

    protected $autoCheckFields = false;

    //取列表
    public function getList($map){
        $result = M("quyu")->alias("a")->where($map)
               ->join("qz_province as c on c.qz_provinceid = a.uid")
               ->field("a.cid as cid,a.cname,a.uid,a.type,a.bm,a.px,a.px_abc,a.parent_city,a.little,a.manager,c.qz_province,c.qz_bigpart,c.qz_bigpart_name")
               ->order("a.px_abc")
               ->select();
        return $result;
    }

    //取所有省份
    public function getProvince($id){
        return M('province')->field('*')->order('qz_province')->select();
    }

    //查询单个城市
    public function getQuyu($map = '',$order = '',$id)
    {
        return M('quyu')->alias('a')->field('a.*,b.type as province_type')
            ->join('left join qz_province as b on b.qz_provinceid = a.uid')
            ->order($order)
            ->where($map)
            ->limit('1')
            ->select();
    }

     //查询单个区域
    public function getArea($map = '',$order = '',$limit = '1'){
        return M('area')->field('*')->order($order)->where($map)->limit($limit)->select();
    }

    //增加城市
    public function addQuyu($data){
        return M("quyu")->add($data);
    }

    //编辑城市
    public function editQuyu($id,$data){
        return M("quyu")->where(array('cid'=>$id))->save($data);
    }

    //编辑省份
    public function editProvince($province,$uid){
        return M('province')->where(array('qz_provinceid'=>$uid))->save($province);
    }

    //增加区域
    public function addArea($data){
        return M("area")->add($data);
    }

    //编辑区域
    public function editArea($id,$data){
        return M("area")->where(array('qz_areaid'=>$id))->save($data);
    }

    //记录日志
    public function addLog($map, $data) {
        if (empty($data)) {
            return false;
        }
        $adddata = array();
        $adddata['cid'] = $map['id'];
        $adddata['action'] = $map['action'];
        $adddata['username'] = $_SESSION['uc_userinfo']['name'];
        $adddata['admin_id'] = $_SESSION['uc_userinfo']['id'];
        $adddata['postdata'] = json_encode($map).','.json_encode($data);
        $adddata['addtime']  = time();
        return M('log_quyu')->add($adddata);
    }

    /**
     * 获取城市
     * @param $info
     * @return mixed
     */
    public function getCityInfo($info){
        $map["cname"] = array('EQ',trim($info));
        $result =  M('quyu')->field('cid')->where($map)->find();
        return $result["cid"];
    }

    /**
     * 获取区域
     * @param $info
     * @return mixed
     */
    public function getAreaInfo($info)
    {
        $map["qz_area"] =  array('EQ',trim($info));
        $result = M('area')->field('qz_areaid')->where($map)->find();
        return $result["qz_areaid"];
    }

    public function getExistOrder($id, $order)
    {
        $map["fatherid"] = array('EQ', $id);
        $map["orders"] = array('EQ', $order);
        return M('area')->where($map)->count();
    }

    /**
     * 获取城市信息
     *
     * @param array $map 查询条件
     * @return mixed
     */
    public function getCitys($map, $orderBy = '',$field = 'cid,cname')
    {
        return M('quyu')->field($field)->where($map)->order($orderBy)->select();
    }

    /**
     * 获取地区
     *
     * @param array $map 查询条件
     * @return mixed
     */
    public function getAreaList($map)
    {
        return M('area')->field('fatherid,qz_areaid as areaid,qz_area as area')->where($map)->select();
    }
}