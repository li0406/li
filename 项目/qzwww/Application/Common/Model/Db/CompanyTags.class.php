<?php
// +----------------------------------------------------------------------
// | CompanyRelationTag 装修公司和标签关联表
// +----------------------------------------------------------------------
namespace Common\Model\Db;

use Think\Model;

class CompanyTags extends Model
{
    protected $tableName = 'company_tags';

    /**
     * 获取装修公司 公司标签
     *
     * @param $map
     * @return mixed
     */
    public function getTags($map)
    {
        return $this
            ->alias('a')
            ->where($map)
            ->field('a.company_id,b.id,b.tag')
            ->join('qz_company_relation_tag b on b.id = a.tag')
            ->select();
    }

    /**
     * @des:获取装修公司最新的标签
     */
    public function getLastNewTags($map)
    {
        return $this->alias('a')
            ->where($map)
            ->field('a.company_id,b.id,b.tag')
            ->join('qz_company_relation_tag b on b.id = a.tag')
            ->order('b.tag_mode DESC,b.id DESC')
            ->select();

    }
}