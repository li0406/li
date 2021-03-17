<?php


namespace Home\Model\Db;


use Think\Model;

class MeituV2HomeModel extends Model
{
    protected $tableName = "tu_home";


    /**
     * 获取家装/公装的首页推荐
     * @param int $type     1表示公装  2表示家装
     * @return mixed
     */
    public function getMeituListByType($type = 1)
    {
        $map = [];
        $map['tuhome.type'] = array('eq',$type);
        $map['meitu.state'] = array('eq',3);
        return $this->alias('tuhome')
            ->where($map)
            ->field('meitu.id,meitu.title,meitu.keyword,meitu.publish_time,adminuser.name created_name')
            ->join('qz_tu meitu on meitu.id = tuhome.link_id')
            ->join('LEFT JOIN qz_adminuser adminuser on adminuser.id = meitu.creator')
            ->order('meitu.publish_time desc,meitu.id desc')
            ->limit(8)
            ->select();

    }



    //查询 3D美图 Item 列表
    public function getThreeDItemList($where){
        return $this->alias('h')
            ->field("h.*,x.title,x.update_time,x.publish_time,group_concat(t.name) AS tagsname,u.name AS adminuser")
            ->join('LEFT JOIN qz_xiaoguotu_threedimension AS x ON x.id = h.link_id')
            ->join('LEFT JOIN qz_adminuser AS u ON u.id = x.adminuser_id')
            ->join('LEFT JOIN qz_tags AS t ON find_in_set(t.id, x.tags)')
            ->group('x.id')
            ->order('x.publish_time desc,x.id desc')
            ->where($where)
            ->select();
    }

    /**
     * 根据条件查询对应的美图id
     * @param $map
     * @return mixed
     */
    public function getHomeMeituId($map)
    {
        return $this->where($map)->field('link_id')->select();
    }

    /**
     * 批量添加
     * @param $adddata
     * @return bool|string
     */
    public function addAlldata($adddata)
    {
        return $this->addAll($adddata);
    }


    /**
     * 删除数据
     * @param $map
     * @return mixed
     */
    public function deletedata($map)
    {
        return $this->where($map)->delete();
    }


    public function getTuHomeList($type)
    {
        $map = array(
            "type" => array("EQ",$type)
        );
        return $this->where($map)->order("id")->select();
    }

    public function editTuHomeInfo($id,$data)
    {
        $map = [
            "id" => ["EQ",$id]
        ];
        return $this->where($map)->save($data);
    }
}