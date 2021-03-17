<?php
/**
 * v2美图,分类
 */

namespace Home\Model\Logic;


use Common\Enums\ApiConfig;
use Home\Model\Db\MeituV2CategoryModel;
use Home\Model\Db\MeituV2Model;

class MeituV2CategoryLogicModel
{
    protected $meituV2CategoryModel;
    protected $meituV2Model;

    public function __construct()
    {
        $this->meituV2CategoryModel = new MeituV2CategoryModel();
        $this->meituV2Model = new MeituV2Model();
    }

    /**
     * 获取二级类别
     * @param int $type 1表示公装   2表示家装
     * @return mixed
     */
    public function getMeituLv2Category($type = 1)
    {
        $map = [];
        $map['parent'] = array('eq', $type);
        return $this->meituV2CategoryModel->getMeituLv2Category($map);
    }


    /**
     * 根据类型获取公家/家装的二级-三级所有分类
     * @param $type 1表示公装   2表示家装
     */
    public function getMeituCategoryGroupByType($type = 1)
    {
        $map = [];
        $map['parent'] = array('eq', $type);
        $lv2list =  $this->meituV2CategoryModel->getMeituLv2Category($map);
        foreach ($lv2list as $key => $value){
            $map = [];
            $map['parent'] = array('EQ',$value['id']);
            $map['is_enable'] = array('eq',1);
            $lv2list[$key]['child'] = $this->meituV2CategoryModel->getMeituLv2Category($map);
        }
//        dump($lv2list[0]);die;
        return $lv2list ? $lv2list : array();
    }

    /**
     * 根据get参数获取分类列表（三级分类）
     * @param $param    get参数
     * @param int $type 1表示工装。 2表示家装
     */
    public function getMeituCategoryList($param, $type = 1)
    {
        $map = [];
        if (isset($param['categoryname']) && $param['categoryname']) {
            $map['lv3.name'] = array('like', '%' . $param['categoryname'] . '%');
        }

        //获取二级分类
        $where = [];
        $where['parent'] = $type;
        $lv2list = $this->meituV2CategoryModel->getMeituLv2Category($where);
        $parentidlist = [];
        foreach ($lv2list as $key => $value){
            $parentidlist[] = intval($value['id']);
        }

        if($parentidlist){
            $map['lv3.parent'] = array('in',$parentidlist);
        }


        if($param['type'] && $param['type']) {
            $map['lv3.parent'] = $param['type'];
        }


        $show = '';
        $list = [];


        $count = $this->meituV2CategoryModel->getMeituCategoryListCount($map);
        $pageCount = 10;
        if($count > 0){
            import('Library.Org.Util.Page');
            $p = new \Page($count, $pageCount);
            $show = $p->show();
            $list = $this->meituV2CategoryModel->getMeituCategoryList($map, intval($p->nowPage - 1)*$pageCount, $p->listRows);
        }

        $return = [];
        $return['list'] = $list;
        $return['page'] = $show;
        return $return;

    }


    /**
     * 验证分类的一些参数
     * @param $data
     */
    public function checkCategoryData($data,$type = 1)
    {
        $data['id'] = $data['id'] ? $data['id'] : 0;
        //分类名称不能重复
        if($data['name']){
            $map = [];
            //获取二级分类
            $where = [];
            $where['parent'] = $type;
            $lv2list = $this->meituV2CategoryModel->getMeituLv2Category($where);
            $parentidlist = [];
            foreach ($lv2list as $key => $value){
                $parentidlist[] = intval($value['id']);
            }

            if($parentidlist){
                $map['parent'] = array('in',$parentidlist);
            }
            $map['name'] = array('eq',$data['name']);
            if($data['id'] > 0){
                $map['id'] = array('neq',$data['id']);
            }
            $result = $this->meituV2CategoryModel->getMeituLv2Category($map);
            if($result && $result[0]['id'] != $data['id']){
                $return = [];
                $return['error_code'] = ApiConfig::PARAMETER_ILLEGAL;
                $return['error_msg'] = '已存在该分类，请重新输入';
                return $return;
            }
        }

        //url不能重复
        if($data['url']){
            $map = [];
            $map['url'] = array('eq',$data['url']);
            if($data['id'] > 0){
                $map['id'] = array('neq',$data['id']);
            }
            $result = $this->meituV2CategoryModel->getMeituLv2Category($map);
            if($result && $result[0]['id'] != $data['id']){
                $return = [];
                $return['error_code'] = ApiConfig::PARAMETER_ILLEGAL;
                $return['error_msg'] = '已存在该url，请重新输入';
                return $return;
            }
        }

        return true;

    }


    /**
     * 根据id获取分类信息
     * @param $id
     * @return mixed
     */
    public function getCategoryInfoById($id)
    {
        return $this->meituV2CategoryModel->getCategoryInfoById($id);
    }


    /**
     * 添加分类
     * @param $data
     */
    public function addCategory($data)
    {
        $result = $this->meituV2CategoryModel->addCategory($data);
        if($result ===false){
            return false;
        }else{
            return true;
        }
    }

    /**
     * 编辑分类
     * @param $id
     * @param $data
     */
    public function editCategory($id,$data)
    {
        $result = $this->meituV2CategoryModel->editCategory($id,$data);
        if($result ===false){
            return false;
        }else{
            return true;
        }

    }

    /**
     * 删除分类
     * @param $id
     */
    public function deleteCategory($id)
    {
        return $this->meituV2CategoryModel->deleteCategory($id);
    }


    /**
     * 验证分类下是否有美图
     * @param $category_id
     * @return mixed
     */
    public function checkCategoryHadMeitu($category_id)
    {
        $map = [];

        $info =  $this->meituV2CategoryModel->getCategoryInfoById($category_id);
        if(in_array(intval($info['parent']),array(7,8,9,10))){      //家装
            if(intval($info['parent']) == 7){       //空间
                $map['tu.home_space_category'] = $category_id;
            }elseif(intval($info['parent']) == 8) {       //局部
                $map['tu.home_part_category'] = $category_id;
            }elseif(intval($info['parent']) == 9) {       //风格
                $map['tu.home_style_category'] = $category_id;
            }else{       //户型
                $map['tu.home_layout_category'] = $category_id;
            }
            $had = $this->meituV2Model->checkCategoryHadMeitu($map);
            if($had){
                return true;
            }else{
                false;
            }
        }elseif(in_array(intval($info['parent']),array(3,4,5,6))){  //公装
            $map['tu.pub_category'] = $category_id;
            $had = $this->meituV2Model->checkCategoryHadMeitu($map);
            if($had){
                return true;
            }else{
                false;
            }
        }else{
            return false;
        }
    }

}