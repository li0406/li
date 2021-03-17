<?php


namespace Home\Model\Db;


use Think\Model;

class SubstationInfoModel extends Model
{
    protected $tableName = "substation_info";

    /**
     * 根据条件获取办事处信息总数量
     * @param $map
     * @return mixed
     */
    public function getSubstationListCount($map){
        return $this->where($map)->count();
    }

    /**
     * 根据条件获取办事处信息列表
     * @param $map
     * @param int $pageIndex
     * @param int $pageCount
     * @return mixed
     */
    public function getSubstationList($map,$pageIndex = 0,$pageCount = 20){

        return $this->where($map)
            ->order('add_time desc,id desc')
            ->limit($pageIndex,$pageCount)
            ->select();
    }

    /**
     * 根据id获取单条办事处信息
     * @param $id
     * @return mixed
     */
    public function getSubstationInfoById($id){
        $map = [];
        $map['id']= array('eq',$id);
        return $this->where($map)
            ->field('id,substation_name,city,tel,contact_name,address,sort_num,stat')
            ->find();
    }

    /**
     * 添加新的办事处信息
     * @param $data
     * @return mixed
     */
    public function addSubstation($data)
    {
        return $this->add($data);
    }


    /**
     * 修改办事处信息
     * @param $map
     * @param $savedata
     * @return bool
     */
    public function saveSubstation($map,$savedata)
    {
        return $this->where($map)->save($savedata);
    }


}