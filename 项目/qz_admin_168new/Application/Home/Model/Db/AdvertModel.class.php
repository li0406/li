<?php

namespace Home\Model\Db;
use Think\Model;
class AdvertModel extends Model
{
    public function getAdvertCount($where)
    {
        return $this->alias('a')
            ->join('qz_advert_position p on p.id = a.location_id')
            ->join("left join qz_advert_position_option as pos on pos.id = p.position_id and pos.`level` = 4")
            ->join("left join qz_advert_position_option as page on page.id = pos.parentid and page.`level` = 3")
            ->join("left join qz_advert_position_option as module on module.id = page.parentid and module.`level` = 2")
            ->join("left join qz_advert_position_option as plat on plat.id = module.parentid and plat.`level` = 1")
            ->where($where)
            ->count();
    }

    public function getAdvertList($where, $field = 'a.*', $page, $page_count)
    {
        return $this->alias('a')
            ->field($field)
            ->join('qz_advert_position p on p.id = a.location_id')
            ->join("left join qz_advert_position_option as pos on pos.id = p.position_id and pos.`level` = 4")
            ->join("left join qz_advert_position_option as page on page.id = pos.parentid and page.`level` = 3")
            ->join("left join qz_advert_position_option as module on module.id = page.parentid and module.`level` = 2")
            ->join("left join qz_advert_position_option as plat on plat.id = module.parentid and plat.`level` = 1")
            ->where($where)
            ->order('a.start_time desc')
            ->limit($page, $page_count)
            ->select();
    }

    public function getAdvertDetail($where, $field = 'a.*')
    {
        return $this->alias('a')
            ->field($field)
            ->join('qz_advert_position p on p.id = a.location_id')
            ->join("left join qz_advert_position_option as pos on pos.id = p.position_id and pos.`level` = 4")
            ->join("left join qz_advert_position_option as page on page.id = pos.parentid and page.`level` = 3")
            ->join("left join qz_advert_position_option as module on module.id = page.parentid and module.`level` = 2")
            ->join("left join qz_advert_position_option as plat on plat.id = module.parentid and plat.`level` = 1")
            ->where($where)
            ->find();
    }

    public function addAdv($data){
        return $this->add($data);
    }
    public function updateAdv($where,$data){
        return $this->where($where)->save($data);
    }

    public function saveAdvCity($save)
    {
        return M('advert_city')->addAll($save);
    }

    /**
     * 根据条件删除广告投放城市关联表数据
     * @param $where
     * @return mixed
     */
    public function delAdvCity($where)
    {
        return M('advert_city')->where($where)->delete();
    }


    public function saveAdvImg($save)
    {
        return M('advert_img')->addAll($save);
    }

    /**
     * 根据条件删除广告图片
     * @param $where
     * @return mixed
     */
    public function delAdvImg($where)
    {
        return M('advert_img')->where($where)->delete();
    }

    public function saveAdvJs($save)
    {
        return M('advert_js')->addAll($save);
    }

    /**
     * 根据条件删除JS广告的 JS
     * @param $where
     * @return mixed
     */
    public function delAdvJs($where)
    {
        return M('advert_js')->where($where)->delete();
    }

    /**
     * 根据广告id返回广告js
     * @param $id
     * @return mixed
     */
    public function getJsByAdvId($id)
    {
        return M('advert_js')->where(['advert_id'=>['eq',$id]])->select();
    }

    public function getImgByAdvId($id){
        return M('advert_img')->where(['advert_id'=>['eq',$id]])->select();
    }
    public function getCityByAdvId($id,$field = 'ac.*'){
        return M('advert_city')->alias('ac')
            ->field($field)
            ->join('qz_quyu q on q.cid = ac.cs')
            ->join('qz_advert as a on a.id = ac.advert_id')
            ->where(['ac.advert_id'=>['eq',$id]])
            ->select();
    }
}