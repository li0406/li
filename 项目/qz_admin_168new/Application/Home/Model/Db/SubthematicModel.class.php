<?php
namespace Home\Model\Db;
use Think\Model;
use Think\Db;

class SubthematicModel extends Model
{
    protected $tableName = "subthematic";

    /**
     * 根据条件获取分站的专题数量
     * @param $map
     * @return mixed
     */
    public function getSubthematicListByMapCount($map)
    {
        return $this->alias('subthematic')
            ->where($map)
            ->join('JOIN qz_tags as tags on tags.id = subthematic.tagid')
            ->count();
    }

    public function getSubthematicListByMapCountV2($map)
    {
        return $this->alias('subthematic')
            ->where($map)
            ->join('LEFT JOIN qz_sub_tag as tags on tags.id = subthematic.sub_tagid')
            ->count();
    }

    /**
     * 根据参数获取分站-专题页列表
     * @param $data
     */
    public function getSubthematicListByMap($map,$pageFirst,$pageCount)
    {
        return $this->alias('subthematic')
            ->where($map)
            ->field('subthematic.id,tags.name subthematic_name,subthematic.url,quyu.cname subthematic_zhandian,subthematic.is_top,subthematic.created_at,user.name editer_name')
            ->join('JOIN qz_tags as tags on tags.id = subthematic.tagid')
            ->join('JOIN qz_quyu as quyu on quyu.cid = subthematic.cs')
            ->join('JOIN qz_adminuser as user on user.id = subthematic.create_id')
            ->limit($pageFirst,$pageCount)
            ->order('subthematic.created_at desc,subthematic.id desc')
            ->select();

    }

    public function getSubthematicListByMapV2($map,$pageFirst,$pageCount)
    {
        return $this->alias('subthematic')
            ->where($map)
            ->field('subthematic.id,tags.name subthematic_name,subthematic.url,quyu.cname subthematic_zhandian,subthematic.is_top,subthematic.created_at,user.name editer_name,tags_old.name as old_subthematic_name')
            ->join('LEFT JOIN qz_sub_tag as tags on tags.id = subthematic.sub_tagid')
            ->join('LEFT JOIN qz_tags as tags_old on tags_old.id = subthematic.tagid')
            ->join('JOIN qz_quyu as quyu on quyu.cid = subthematic.cs')
            ->join('JOIN qz_adminuser as user on user.id = subthematic.create_id')
            ->limit($pageFirst,$pageCount)
            ->order('subthematic.created_at desc,subthematic.id desc')
            ->select();

    }
    
    //根据id获取专题信息
    public function getSubThematicInfoById($id)
    {
        $map = [];
        $map['id'] = $id;
        return $this->where($map)->find();
    }

    //根据条件获取专题信息
    public function getSubThematicInfoByMap($map)
    {
        if($map){
            return $this->where($map)->find();
        }else{
            return array();
        }
    }


    //添加新的专题页信息
    public function addThematic($data)
    {
        return $this->add($data);
    }

    //编辑专题页信息
    public function editThematic($data)
    {
        return $this->save($data);
    }
    
    //根据专题页id查询选择的装修公司
    public function getCompanyListByThematicId($id)
    {
        if($id){
            $map = [];
            $map['sub.subthematic_id'] = $id;
            $list = M('subthematic_company')->alias('sub')
                ->where($map)
                ->field('sub.id,user.id company_id,user.jc company_jc,user.on,companyinfo.fuwu,companyinfo.fake')
                ->join('JOIN qz_user as user on user.id = sub.company_id')
                ->join('JOIN qz_user_company companyinfo on companyinfo.userid = user.id')
                ->select();
            return $list;
        }else{
            return array();
        }
    }


    //根据专题页id查询选择的装修案例
    public function getCasesListByThematicId($id)
    {
        if($id) {
            $map = [];
            $map['subcases.subthematic_id'] = $id;
            $list = M('subthematic_cases')->alias('subcases')
                ->where($map)
                ->field('subcases.id,cases.id cases_id,cases.title cases_title')
                ->join('JOIN qz_cases as cases on cases.id = subcases.case_id')
                ->select();
            return $list;
        }else{
            return array();
        }
    }


    //删除选择的装修公司
    public function deleteChooseCompany($id){
        return M('subthematic_company')->where(array('id'=>$id))->delete();
    }

    //删除选择的案例
    public function deleteChooseCases($id){
        return M('subthematic_cases')->where(array('id'=>$id))->delete();
    }

    //根据条件查询专题选择的公司信息
    public function getSubThematicChooseCompanyByMap($map)
    {
        if($map){
            return M('subthematic_company')->where($map)->find();
        }else{
            return array();
        }
    }

    //添加新选择的装修公司数据
    public function addThematicCompany($data)
    {
        return M('subthematic_company')->add($data);
    }

    //添加新选择的装修公司数据(批量添加)
    public function addThematicCompanyList($data)
    {
        return M('subthematic_company')->addAll($data);
    }


    //根据条件查询专题选择的案例信息
    public function getSubThematicChooseCaseByMap($map)
    {
        if($map){
            return M('subthematic_cases')->where($map)->find();
        }else{
            return array();
        }
    }

    //添加新选择的装修公司数据
    public function addThematicCase($data)
    {
        return M('subthematic_cases')->add($data);
    }

    //添加新选择的装修公司数据(批量添加)
    public function addThematicCasesList($data)
    {
        return M('subthematic_cases')->addAll($data);
    }

    //删除专题
    public function deleteSubThematic($map){
        return M('subthematic')->where($map)->delete();
    }

    //删除选择的装修公司
    public function deleteSubThematicCompany($map){
        return M('subthematic_company')->where($map)->delete();
    }

    //删除选择的案例
    public function deleteSubThematicCases($map){
        return M('subthematic_cases')->where($map)->delete();
    }



}