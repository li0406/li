<?php
/**
 * Created by PhpStorm.
 * User: qzjsb
 * Date: 2019/5/21
 * Time: 14:05
 */

namespace Common\Model;


use Think\Model;

class CompanyTagsModel extends Model
{
    protected $tableName = 'company_tags';

    public function getTagsById($id){
        $map['a.company_id'] = array("EQ",$id);
        return $this->alias('a')->where($map)
            ->field('a.tag as tag_id,t.tag as tag_name')
            ->join("LEFT JOIN qz_company_relation_tag t on t.id = a.tag")
            ->order('t.tag_mode DESC,t.id DESC')
           # ->limit(5)
            ->select();
    }
}