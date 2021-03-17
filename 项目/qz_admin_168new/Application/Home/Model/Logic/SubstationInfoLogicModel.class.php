<?php


namespace Home\Model\Logic;


use Home\Model\Db\SubstationInfoModel;

class SubstationInfoLogicModel
{

    /**
     * 获取办事处信息列表
     * @param int $stat
     * @param string $search_text
     * @param int $page
     * @return array
     */
    public function getSubstationList($stat = "",$search_text = "")
    {
        $model = new SubstationInfoModel();

        if ($search_text!="")
        {
            $where=array();//有关键字搜索的时候忽略启用状态搜索
            $where['substation_name']=array('like','%'.$search_text.'%');//匹配办事处名称
            $where['city']=array('like','%'.$search_text.'%');//匹配城市
            $where['tel']=array('like','%'.$search_text.'%');//匹配电话
            $where['_logic']='or';
            $map['_complex'] = $where;
        }
        if($stat != ""){
            $map['stat']=$stat;
        }

        //获取办事处总数量
        $count = $model->getSubstationListCount($map);

        //分页
        import('Library.Org.Util.Page');
        $page = new \Page($count, 20);
        $show = $page->show();
        $list = $model->getSubstationList($map, $page->firstRow, $page->listRows);

        $retutn = [];
        $retutn['list'] = $list ? $list : [];
        $retutn['page'] = $show;
        return $retutn;

    }

    /**
     * 根据id获取办事处信息
     * @param $id
     * @return mixed
     */
    public function getSubstationInfoById($id)
    {
        $model = new SubstationInfoModel();
        return $model->getSubstationInfoById($id);
    }

    /**
     * 修改办事处信息
     * @param $map
     * @param $savedata
     * @return bool
     */
    public function saveSubstation($map,$savedata)
    {
        $model = new SubstationInfoModel();
        $result = $model->saveSubstation($map,$savedata);
        if($result === false){
            return false;
        }else{
            return true;
        }
    }


    public function addSubstation($adddata)
    {
        $model = new SubstationInfoModel();
        $result = $model->addSubstation($adddata);
        if($result <= 0){
            return false;
        }else{
            return true;
        }

    }



}