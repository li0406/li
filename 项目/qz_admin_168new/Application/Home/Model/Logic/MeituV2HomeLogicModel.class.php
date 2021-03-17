<?php
/**
 * v2美图,分类
 */

namespace Home\Model\Logic;


use Home\Model\Db\MeituV2CategoryModel;
use Home\Model\Db\MeituV2HomeModel;
use Home\Model\Db\MeituV2Model;

class MeituV2HomeLogicModel
{
    protected $meituV2CategoryModel;

    protected $meituV2Model;
    protected $meituV2HomeModel;

    public function __construct()
    {
        $this->meituV2CategoryModel = new MeituV2CategoryModel();
        $this->meituV2Model = new MeituV2Model();
        $this->meituV2HomeModel = new MeituV2HomeModel();
    }


    /**
     * @param int $type
     */
    public function getMeituListByType($type = 1)
    {
        $list = $this->meituV2HomeModel->getMeituListByType($type);
        return $list ? $list : [];

    }


    public function getThreeDItemList($map)
    {
        if($map){
            $list = $this->meituV2HomeModel->getThreeDItemList($map);
            return $list ? $list : [];
        }else{
            return array();
        }
    }

    /**
     * 根据条件查询对应的美图id
     * @param int $type
     * @return mixed
     */
    public function getHomeMeituId($type = 1)
    {
        $map = [];
        $map['type'] = array('EQ',$type);
        return $this->meituV2HomeModel->getHomeMeituId($map);
    }

    /**
     * 批量添加
     * @param $adddata
     */
    public function addAlldata($adddata)
    {
        if($adddata){
            return $this->meituV2HomeModel->addAlldata($adddata);
        }else{
            return 0;
        }
    }

    /**
     * 根据美图list删除数据
     * @param $type  美图类型
     * @param $linklist   美图id  list
     * @return bool
     */
    public function deletedata($type,$linklist)
    {
        $map = [];
        $map['type'] = array('eq',$type);
        $map['link_id'] = array('in',$linklist);
        return $this->meituV2HomeModel->deletedata($map);
    }


    //获取 3D 列表并分页
    public function get3DMeituList($condition,$pageIndex=1,$pageCount = 10) {
        $count = D('Meituhome')->get3DCount($condition);
        if($count > $pageCount){
            import('Library.Org.Util.Page');
            $page = new \Page($count,$pageCount);
            $info['page'] =  $page->show();
            $pageIndex = $page->nowPage;
        }
        $info['list'] = D('Meituhome')->get3DList($condition,($pageIndex - 1)*$pageCount,$pageCount);
        return $info;
    }

    public function getTuHomeList($type)
    {
        $list = $this->meituV2HomeModel->getTuHomeList($type);
        foreach ($list as $key => $item) {
            $list[$key]["thumb_original"] = $item["thumb"];
            $list[$key]["thumb"] = "http://".C("QINIU_DOMAIN")."/".$item["thumb"]."-w240.jpg";
        }
        return $list;
    }

    public function editTuHomeInfo($id,$data)
    {
        return $this->meituV2HomeModel->editTuHomeInfo($id,$data);
    }

}