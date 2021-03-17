<?php

namespace Home\Model;

Use Think\Model;

class AppletCarouselModel extends Model
{
    public function getList($map)
    {
        $page = 0;
        $pageSize = 20;
        if (isset($map['page']) && !empty($map['page'])) {
            $page = $map['page'];
        }
        if (isset($map['pageSize']) && !empty($map['pageSize'])) {
            $page = $map['pageSize'];
        }
        return $this
            ->page($page, $pageSize)
            ->order('`order` asc')
            ->select();
    }

    public function getAppletCarouselCount()
    {
        return $this->count();
    }

    public function getDataById($id)
    {
         return $this->where(['id' => $id])->find();
    }

    public function delDataById($id)
    {
        return $this->where(['id' => $id])->delete();
    }

    public function updateAppletCarousel($map,$data)
    {
        return $this->where($map)->save($data);
    }

    public function addAppletCarousel($data)
    {
        return $this->add($data);
    }

}

