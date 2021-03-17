<?php
namespace Home\Model;
Use Think\Model;

class MeituztModel extends Model{

    protected $autoCheckFields = false;

    //取列表
    public function getList($map,$pagesize= 1,$pageRow = 10){
        $count  = M('meitu_zt')->alias("a")->where($map)->count();
        $result = M('meitu_zt')->alias("a")
                            ->field('a.*,u.name,u2.name as last_username')
                            ->join("left join qz_adminuser as u on u.id = a.uid")
                            ->join("left join qz_adminuser as u2 on u2.id = a.last_uid")
                            ->where($map)
                            ->order("id DESC")
                            ->limit($pagesize.",".$pageRow)
                            ->select();

        return array("result"=>$result,"count"=>$count);
    }


    //查询 美图标题
    public function getMeituTitle($ids){
        $map['id'] = array('IN',$ids);
        return M('meitu')->field('id,title')->where($map)->select();
    }

    //查询 案例标题
    public function getCaseTitle($ids){
        $map['a.id'] = array('IN',$ids);
        return M('cases')->alias('a')
                ->field('a.id,a.title,a.mianji,g.name as fengge,h.name AS huxing,q.bm')
                ->join("LEFT JOIN qz_fengge as g on g.id = a.fengge")
                ->join("LEFT JOIN qz_huxing as h on h.id = a.huxing")
                ->join("LEFT JOIN qz_quyu as q on q.cid = a.cs")
                ->where($map)
                ->select();
    }

    //查询 案例标题
    public function getArticleTitle($ids){
        $map['b.id'] = array('IN',$ids);
        return M('www_article')->field('b.id,b.title,c.shortname')->alias('b')
            ->join('LEFT JOIN qz_www_article_class_rel as r on r.article_id = b.id')
            ->join('LEFT JOIN qz_www_article_class as c on c.id = r.class_id ')
            ->where($map)->select();
    }

    //增加 专题
    public function addMeituZt($data){
        return M("meitu_zt")->add($data);
    }

    //增加 专题的条目
    public function addMeituZtItem($data){
        return M("meitu_zt_item")->add($data);
    }

    //删除 专题
    public function removeZt($id){
        $map['id'] = $id;
        return M("meitu_zt")->where($map)->delete();
    }

    //删除 专题的条目
    public function removeZtItem($id){
        $map['zid'] = $id;
        return M("meitu_zt_item")->where($map)->delete();
    }

    //删除 专题的条目 按类型
    public function removeZtItemByType($id,$type){
        $map['zid'] = $id;
        $map['type'] = $type;
        return M("meitu_zt_item")->where($map)->delete();
    }

    //取单个专题
    public function getSpecial($id){
        $map['id'] = $id;
        return M('meitu_zt')->where($map)->find();
    }

    //取多个专题
    public function getSpaceials($map){
        return M("meitu_zt_item")->where($map)->select();
    }

    //删除
    public function updateMeituZt($id,$data){
        return M("meitu_zt")->where(array('id'=>$id))->save($data);
    }

    /**
     * 取banner列表
     * @param $map(筛选条件)
     * @param int $pageNum(页码)
     * @param int $pageSize(每页数量)
     * @return array
     */
    public function getBannerList($map, $pageNum = 1, $pageSize = 10,$order ='time DESC' )
    {
        $count = M('meitu_zt_banner')->where($map)->count();
        $result = M('meitu_zt_banner')
            ->where($map)
            ->order($order)
            ->page("$pageNum,$pageSize")
            ->select();
        return array("result" => $result, "count" => $count);
    }

    //取banner轮播
    public function getBanner($id)
    {
        $map['id'] = $id;
        return M('meitu_zt_banner')->where($map)->find();
    }

    //增加banner轮播
    public function addBanner($data)
    {
        return M("meitu_zt_banner")->add($data);
    }

    //增加banner轮播
    public function updateBanner($id, $data)
    {
        return M("meitu_zt_banner")->where(array('id' => $id))->save($data);
    }

    //删除banner轮播
    public function removeBanner($ids)
    {
        if (empty($ids)){
            return false;
        }
        if (is_array($ids)){
            $ids = implode(',',$ids);
        }
        $map['id'] = ['in',(string)$ids];
        return M("meitu_zt_banner")->where($map)->delete();
    }


    /**
     * [getmeituztByName 根据标题名获取标题]
     * @param  [type]  $name  [标题名]
     * @param  integer $limit [获取条数]
     * @param  string  $istop [是否推荐]
     * @param  string  $order [排序]
     * @return [type]         [标题数组]
     */
    public function getmeituztByName($title, $limit = 15, $is_home = '', $order = 'is_home DESC'){
        $map['title'] =  array('EQ',$title);
        if(!empty($is_home) && ($is_home == 1 || $is_home == 2)){
            $map['is_home'] = intval($is_home);
        }
        //有限查看是否有完全匹配的数据，如果有完全匹配的数组，模糊匹配查询数量减少一个
        $complete = M('meitu_zt')->where($map)->find();
        if (!empty($complete) && !empty($limit)) {
            $limit = $limit - 1;
        }

        //重新定义名字查询条件
        $map['title'] =  array('like','%'. $title .'%');
        $result = M('meitu_zt')->where($map)->limit($limit)->order($order)->select();
        if (!empty($complete)) {
            array_unshift($result, $complete);
        }
        return $result;
    }

}