<?php
// +----------------------------------------------------------------------
// | MediaReportLogicModel
// +----------------------------------------------------------------------
namespace Common\Model\Db;

use Think\Model;

class MediaReportModel extends Model
{
    protected $tableName = 'media_report';

    public function getCount($map)
    {
        return $this->where($map)->count('id');
    }

    public function getList($map, $offset = null, $length = null)
    {
        return $this
            ->field('id,title,description,url,image,sequence,publish_time,create_time,update_time')
            ->where($map)
            ->limit($offset, $length)
            ->order('sequence asc,publish_time desc')
            ->select();
    }
}