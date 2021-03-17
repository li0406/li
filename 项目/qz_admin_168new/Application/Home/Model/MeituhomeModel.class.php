<?php
namespace Home\Model;
Use Think\Model;

/**
 * 美图首页管理
 */

class MeituhomeModel extends Model{

    protected $autoCheckFields = false;

    //查询 Item 列表
    public function getItemList($where){
        return M('meitu_home')->where($where)->select();      
    }

    //增加 Item
    public function addItem($data){
        return M("meitu_home")->add($data);
    }

    //删除 Item
    public function removeItem($id){
        $map['id'] = $id;
        return M("meitu_home")->where($map)->delete();
    }
    

    //查询 美图信息 按ID
    public function getMeituItemById($ids){
        $map['id'] = array('IN',$ids);
        return M('meitu')
                    ->field("*,FROM_UNIXTIME(time,'%Y-%m-%d %H:%i:%s') as times")
                    ->where($map)
                    ->select();
    }

    //查询 3D美图信息 按ID
    public function get3DMeituItemById($ids){
        $map['id'] = array('IN',$ids);
        return M('meitu')
                    ->field("*,FROM_UNIXTIME(time,'%Y-%m-%d %H:%i:%s') as times")
                    ->where($map)
                    ->select();
    }    


    //查询 美图 Item 列表
    public function getMeituItemList($where){
        return M('meitu_home')->alias('h')
                            ->field("h.*,FROM_UNIXTIME(h.last_time,'%Y-%m-%d %H:%i:%s') as times,group_concat(t.name) AS tagsname,m.title,m.keyword,m.uname,m.is_single")
                            ->join('LEFT JOIN qz_meitu AS m ON m.id = h.item_id')
                            ->join('LEFT JOIN qz_tags AS t ON find_in_set(t.id, m.tags)')
                            ->group('h.id')
                            ->where($where)
                            ->select();
    }

    //查询 工装美图 Item 列表
    public function getPubMeituItemList($where){
        return M('meitu_home')->alias('h')
                            ->field("h.*,FROM_UNIXTIME(h.last_time,'%Y-%m-%d %H:%i:%s') as times,m.title,m.keyword,m.uname,m.visible,m.is_single")
                            ->join('LEFT JOIN qz_pubmeitu AS m ON m.id = h.item_id')
                            ->where($where)
                            ->select();
    }

    //查询 3D美图 Item 列表
    public function getThreeDItemList($where){
        return M('meitu_home')->alias('h')
            ->field("h.*,x.title,x.update_time,group_concat(t.name) AS tagsname,u.name AS adminuser")
            ->join('LEFT JOIN qz_xiaoguotu_threedimension AS x ON x.id = h.item_id')
            ->join('LEFT JOIN qz_adminuser AS u ON u.id = x.adminuser_id')
            ->join('LEFT JOIN qz_tags AS t ON find_in_set(t.id, x.tags)')
            ->group('x.id')
            ->where($where)
            ->select();
    }


    //获取3D效果图数量
    public function get3DCount($where) {
        $map['x.status'] = 1;
        
        if (!empty($where['id'])) {
            $map['x.id'] = $where['id'];
        }
        if (!empty($where['title'])) {
            $map['x.title'] = array('LIKE', '%' . $where['title'] . '%');
        }
        if (!empty($where['adminuser_id'])) {
            $map['x.adminuser_id'] = intval($where['adminuser_id']);
        }
        if (!empty($where['fengge'])) {
            $map['x.fengge'] = intval($where['fengge']);
        }
        if (!empty($where['huxing'])) {
            $map['x.huxing'] = intval($where['huxing']);
        }

        return M('xiaoguotu_threedimension')->alias('x')->where($map)->count();
    }
    //获取3D效果图
    public function get3DList($where,$start = 0, $each = 10) {
        $map['x.status'] = 1;

        if (!empty($where['id'])) {
            $map['x.id'] = $where['id'];
        }        
        if (!empty($where['title'])) {
            $map['x.title'] = array('LIKE', '%' . $where['title'] . '%');
        }
        if (!empty($where['adminuser_id'])) {
            $map['x.adminuser_id'] = intval($where['adminuser_id']);
        }
        if (!empty($where['fengge'])) {
            $map['x.fengge'] = intval($where['fengge']);
        }
        if (!empty($where['huxing'])) {
            $map['x.huxing'] = intval($where['huxing']);
        }

        return M('xiaoguotu_threedimension')->alias('x')
                    ->field('x.*, group_concat(t.name) AS tagsname, u.name AS adminuser')
                    ->join('qz_tags AS t ON find_in_set(t.id, x.tags)')
                    ->join('qz_adminuser AS u ON u.id = x.adminuser_id')
                    ->where($map)
                    ->group('x.id')
                    ->limit($start, $each)
                    ->order("x.publish_time desc,x.id desc")
                    ->select();
    }


    //获取设计师 数量
    public function getMeituDesignerCount($condition){
        return M("meitu_designer")->where($condition)->count();
    }
    //获取设计师 列表
    public function getMeituDesignerList($condition,$pageIndex,$pageCount){
        return M("meitu_designer")->where($condition)->order("enabled desc,px desc")->limit($pageIndex,$pageCount)->select();
    }





















    //查询 案例标题
    public function getArticleTitle($ids){
        $map['id'] = array('IN',$ids);
        return M('www_article')->field('id,title')->where($map)->select();
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

    //取列表
    public function getBannerList($map,$pagesize= 1,$pageRow = 10){
        $count  = M('meitu_zt_banner')->where($map)->count();
        $result = M('meitu_zt_banner')
                            ->where($map)
                            ->order("time DESC")
                            ->limit($pagesize.",".$pageRow)
                            ->select();
        return array("result"=>$result,"count"=>$count);
    }

    //取 轮播
    public function getBanner($id){
        $map['id'] = $id;
        return M('meitu_zt_banner')->where($map)->find();
    }

    //增加 轮播
    public function addBanner($data){
        return M("meitu_zt_banner")->add($data);
    }

    //增加 轮播
    public function updateBanner($id,$data){
        return M("meitu_zt_banner")->where(array('id'=>$id))->save($data);
    }

    //删除 轮播
    public function removeBanner($id){
        $map['id'] = $id;
        return M("meitu_zt_banner")->where($map)->delete();
    }

    
}