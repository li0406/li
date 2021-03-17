<?php

//通栏广告Model  -- 对应 qz_adv_banner "通栏广告表"

namespace Home\Model;
Use Think\Model;

class AdvdesignerModel extends Model{
    protected $autoCheckFields = false;
    protected $tableName = "adv_designer";
    /**
     * 取单个广告 - 根据id
     * @param  [type] $id [广告ID]
     * @return [type]     [description]
     */
    public function getAdvDesignerById($id){
        $map = array(
            'd.id' => array("EQ",$id),
        );
        return M('adv_designer')->alias('d')
                                ->field('d.*,q.cname,u.jc,b.name')
                                ->join('LEFT JOIN qz_quyu AS q ON q.cid = d.city_id')
                                ->join('LEFT JOIN qz_user AS b ON b.id = d.uid')
                                ->join('LEFT JOIN qz_user AS u ON u.id = d.company_id')
                                ->where($map)->find();
    }

    /**
     * 添加广告
     * @param [type] $data [description]
     */
    public function addDesigner($data){
        return  M('adv_designer')->add($data);
    }

    public function editDesigner($id,$data){
        $map = array(
            'id' => array("EQ",$id),
        );
        return  M('adv_designer')->where($map)->save($data);
    }


    /**
     * 删除广告
     * @return [type] [description]
     */
    public function delAdvDesigner($id){
        $map = array(
            'id' => array("EQ",$id),
        );
        return  M('adv_designer')->where($map)->delete();
    }


    //更改状态
    public function setStatus($id,$type = '0'){
        $data['status'] = $type;
        $map['id'] = $id;
        return M("adv_designer")->where($map)->save($data);

    }

    public function getDesignerList($map,$pageIndex=1,$pageCount = 10,$order = 'id DESC')
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $map['d.id'] = array("GT",0);
        $Db = M('adv_designer');

        $count  = $Db->alias('d')->where($map)->count();
        $result = $Db->alias('d')->field('d.*,q.cname,u.jc,b.name,b.logo,k.bm')
                                 ->join('LEFT JOIN qz_quyu AS q ON q.cid = d.city_id')
                                 ->join('LEFT JOIN qz_user AS b ON b.id = d.uid')
                                 ->join('LEFT JOIN qz_user AS u ON u.id = d.company_id')
                                 ->join('LEFT JOIN qz_quyu AS k ON k.cid = b.cs')
                                 ->where($map)
                                 ->limit($pageIndex,$pageCount)
                                 ->order($order)
                                 ->select();

        return array("result"=>$result,"count"=>$count);
    }


    /**
     * 获取推荐设计师列表
     * @param  [type] $cs [城市ID]
     * @return [type]     [description]
     */
    public function getDesignerListPreview($cs,$limit = ''){
        $map = array(
            "status"=>array("EQ",1),
            "city_id"=>array("EQ",$cs)
                     );
        $buildSql = M("adv_designer")->where($map)->order("sort,id desc")->limit($limit)->buildSql();
        return M("adv_designer")->table($buildSql)->alias("t")
                                ->join("INNER JOIN qz_user as b on b.id = t.uid")
                                ->join("LEFT JOIN qz_team as c on c.userid = t.uid AND c.zt = '2' ")
                                ->join("INNER JOIN qz_quyu as d on d.cid = b.cs")
                                ->join("INNER JOIN qz_user as e on e.id = t.company_id")
                                ->field("t.*,c.zw,b.logo,d.bm,e.jc")
                                ->select();
    }

}