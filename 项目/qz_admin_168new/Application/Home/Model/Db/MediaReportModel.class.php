<?php
// +----------------------------------------------------------------------
// | MediaReportLogicModel
// +----------------------------------------------------------------------
namespace Home\Model\Db;

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
            ->order('sequence asc,create_time desc')
            ->select();
    }

    public function getInfo($map)
    {
        return $this
            ->field('id,title,description,url,image,sequence,publish_time,create_time,update_time')
            ->where($map)
            ->find();
    }

    public function saveinfo($data, $map)
    {
        if (empty($data['update_time'])) {
            $data['update_time'] = time();
        }
        if (!empty($map)) {
            //更新
            $result = $this->where($map)->save($data);
        } else {
            if (empty($data['create_time'])) {
                $data['create_time'] = time();
            }
            if (empty($data['admin_id'])) {
                $data['admin_id'] = session('uc_userinfo.id');
            }
            $result = $this->add($data);
        }
        return $result;
    }

    public function dellist($map)
    {
        return $this->where($map)->delete();
    }
}