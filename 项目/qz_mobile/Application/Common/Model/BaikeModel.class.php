<?php

namespace Common\Model;

use Think\Model;

class BaikeModel extends Model
{

    //查询百科列表
    public function getList($condition = '', $orderBy, $row = 10)
    {
        $map["visible"] = array("EQ", '0');
        $map["remove"] = array("EQ", '0');
        if (!empty($condition)) {
            $map = array_merge($map, $condition);
        }
        //按分类
        if (!empty($condition['cateId'])) {
            $cid = $condition['cateId'];
            //Note: 为了减少逻辑，对于一级分类ID直接判断
            $categoryColumn = $cid <= 6 ? 'cid' : 'sub_category';
            $map[$categoryColumn] = array("EQ", $cid);
        }
        $Db = M('baike');
        $result = $Db->field('id,item,title,tags,tags_name,thumb,post_time,views')->order($orderBy)->limit("0," . $row)->where($map)->select();
        return $result;
    }

    //查询列表  根据条件condition
    public function getListByCategory($condition, $pagesize = 1, $pageRow = 10)
    {
        $map["a.remove"] = array("EQ", '0');
        if (isset($condition['visible'])) {
            $map['a.visible'] = $condition['visible'];
        } else {
            $map['a.visible'] = array("EQ", '0');
        }
        if (isset($condition['uid'])) {
            $map['a.uid'] = $condition['uid'];
        }
        //如果 cid 不为空
        if (!empty($condition['cateId'])) {
            $cid = $condition['cateId'];
            //Note: 为了减少逻辑，对于一级分类ID直接判断
            $categoryColumn = $cid <= 6 ? 'a.cid' : 'a.sub_category';
            $map[$categoryColumn] = array("EQ", $cid);
        }
        //如果关键词不为空
        if (isset($condition['keyword'])) {
            $map['a.title'] = array('like', '%' . $condition['keyword'] . '%');
        }
        if (isset($condition['dist'])) {
            $map['a.is_top'] = $condition['dist'];
        }
        //dump($map);
        $Db = M('baike');
        $count = $Db->alias("a")
            ->where($map)
            ->count();
        $result = $Db->alias("a")
            ->field('a.id,a.item,a.sub_category,a.thumb,a.description,a.title,a.post_time,a.views,a.visible,c.cid,c.name')
            ->join("inner join qz_baike_category as c on a.sub_category = c.cid")
            ->order($condition['orderBy'])
            ->limit($pagesize . "," . $pageRow)
            ->where($map)
            ->select();
        return array("result" => $result, "count" => $count);
    }

    //查询推荐百科列表 - 有分类
    public function getTopList($pageRow = 10)
    {
        $map['a.visible'] = array("EQ", '0');
        $map['a.thumb'] = array("NEQ", '');
        $map['a.is_top'] = array("EQ", '1');
        $map["a.remove"] = array("EQ", '0');
        //de($map);
        $Db = M('baike');
        $result = $Db->alias("a")
            ->field('a.id,a.item,a.sub_category,a.title,a.thumb,a.content,a.post_time,a.views,c.cid,c.name,c.url')
            ->join("inner join qz_baike_category as c on a.cid = c.cid")
            ->order('a.post_time DESC')
            ->limit("0," . $pageRow)
            ->where($map)
            ->select();
        return $result;
    }

    //取首页推荐分类
    public function getTopCategory($num)
    {
        $map['is_top'] = '1';
        $category = M('baike_category')->field('cid,url,name')->where($map)->limit("0," . $num)->select();
        foreach ($category as $k => $v) {
            $cid = $v['cid'];
            $subList = M('baike')->field('id,item,title,thumb,content')->where("sub_category = '$cid' and visible ='0' and `remove`='0' ")->order('views DESC')->limit("0,4")->select();
            foreach ($subList as $key => $value) {
                if ($key == '0') {
                    $category[$k]['bid'] = $value['id'];
                    $category[$k]['title'] = $value['title'];
                    $category[$k]['thumb'] = $value['thumb'];
                    $category[$k]['content'] = htmlToText($value['content']);
                } else {
                    $value['content'] = htmlToText($value['content']);
                    $category[$k]['subList'][] = $value;
                }
            }
        }
        return $category;
    }

    //取手机版首页推荐
    public function getMobileTopCategory($num)
    {
        $map['is_top'] = '1';
        $category = M('baike_category')->field('cid,url,name')->where($map)->limit("0," . $num)->select();
        foreach ($category as $k => $v) {
            $cid = $v['cid'];
            $subList = M('baike')->field('id,item,title,thumb,content')->where("sub_category = '$cid' and visible ='0' and `remove`='0' ")->order('views DESC')->limit("0,3")->select();
            foreach ($subList as $key => $value) {
                $value['content'] = htmlToText($value['content']);
                $category[$k]['subList'][] = $value;
            }
        }
        return $category;
    }

    //查询推荐百科列表
    public function getTopBaike($pageRow = 10, $orderby = '')
    {
        $map['visible'] = array("EQ", '0');
        $map["remove"] = array("EQ", '0');
        $map["cid"] = array("EQ", 2);

        //$map['is_top']  = array("EQ",'1');
        $Db = M('baike');
        $orderby = empty($orderby) ? 'post_time DESC' : $orderby;

        $result = $Db->field('id,item,title,description,thumb,post_time,views,favorites')
            ->order($orderby)
            ->limit("0," . $pageRow)
            ->where($map)
            ->select();
        return $result;
    }

    //查询推荐百科列表
    public function getTopBaikev2($not_sub_ids='',$pageRow = 10, $orderby = '')
    {
        $map['visible'] = array("EQ", '0');
        $map["remove"] = array("EQ", '0');
        $map["cid"] = array("EQ", 2);
        if(!empty($not_sub_ids)){
            $map['sub_category'] = ['not in',$not_sub_ids];
        }

        //$map['is_top']  = array("EQ",'1');
        $Db = M('baike');
        $orderby = empty($orderby) ? 'post_time DESC' : $orderby;

        $result = $Db->field('id,item,title,description,thumb,post_time,views,favorites')
            ->order($orderby)
            ->limit("0," . $pageRow)
            ->where($map)
            ->select();
        return $result;
    }

    //查询单个百科
    public function getBaike($id, $visible = true)
    {
        $map['remove'] = array("EQ", '0');
        if ($visible == true) {
            $map['visible'] = array("EQ", '0');
        }
        $map["id"] = array("EQ", $id);
        return M('baike')->field('*')->where($map)->find();
    }

    //更新浏览量
    public function updateViews($id)
    {
        return M("baike")->where('id=' . $id)->setInc('views');
    }

    //查询分类按URL
    public function getCategoryByUrl($url)
    {
        $map["c.url"] = array("EQ", $url);
        return M('baike_category')->alias('c')->field('c.*,p.url as p_url')
            ->join('left join qz_baike_category p on p.cid = c.pid')
            ->where($map)
            ->find();
    }

    //查询分类根据Cid
    public function getCategoryByCid($cid)
    {
        //$map["pid"] = array("EQ",$cid);
        $map = [
            'pid' => array("EQ", $cid),
//            'cid' => array('IN', "1,7,8,9,2,15,16,17,19,20,24,3,33,43,44,45,46,52,4,57,58,59,62,66,67,6,70,71,72,73,13,81,82,83,84,85,105"),
//            '_logic' => 'AND'
        ];
        return M('baike_category')->field('*')->where($map)->order('order_id')->select();
    }

    /**
     * 根据pid查上级分类
     * @param  string $pid 父级分类
     * @return array  $result  查询结果
     */
    public function getFCateByPid($pid)
    {
        $map["cid"] = array("EQ", $pid);
        $result = M('baike_category')->field('*')->where($map)->find();
        return $result;
    }

    //查询分类
    public function getCategory($map = '')
    {
        if (!empty($map)) {
            return M('baike_category')->field('*')->where($map)->order('order_id')->select();
        }
        return M('baike_category')->field('*')->order('order_id')->select();
    }

    //删除
    public function remove($id, $uid)
    {
        $map = array(
            "id" => array("EQ", $id),
            "uid" => array("EQ", $uid),
        );
        return M("baike")->where($map)->delete();
    }

    //获取百科分类
    public function getCategorys()
    {
//        $where['cid'] = array('IN', "1,7,8,9,2,15,16,17,19,20,24,3,33,43,44,45,46,52,4,57,58,59,62,66,67,6,70,71,72,73,13,81,82,83,84,85,105");
        $where = [];
        $category = M('baike_category')->where($where)->field('*')->order('order_id')->select();
        return $category;
    }


    /**
     * 获取首页百科列表
     * @param  array $condition 默认分类
     * @param  string $pagesize 页码
     * @param  string $pagenow 分页长度
     * @return array  $result       查询结果及分页
     */
    public function getFirstBKList($condition, $pagesize = 1, $pageRow = 10)
    {
        $map['a.remove'] = array("EQ", '0');
        if (isset($condition['visible'])) {
            $map['a.visible'] = $condition['visible'];
        } else {
            $map['a.visible'] = array("EQ", '0');
        }
        if (isset($condition['uid'])) {
            $map['a.uid'] = $condition['uid'];
        }
        //如果 cid 不为空
        if (!empty($condition['cateId'])) {
            $cid = $condition['cateId'];
            //Note: 为了减少逻辑，对于一级分类ID直接判断
            $categoryColumn = $cid <= 6 ? 'a.cid' : 'a.sub_category';
            $map[$categoryColumn] = array("EQ", $cid);
        }
        //如果关键词不为空
        if (isset($condition['keyword'])) {
            $map['a.title'] = array('like', '%' . $condition['keyword'] . '%');
        }
        if (isset($condition['dist'])) {
            $map['a.is_top'] = $condition['dist'];
        }
        $map["a.remove"] = array("EQ", '0');
        //dump($map);
        $Db = M('baike');
        $count = $Db->alias("a")
            ->where($map)
            ->count();
        //如果选中了一级分配
        if (isset($condition['topCateId'])) {
            $map['c1.cid'] = ['eq', $condition['topCateId']];
        }
        $result = $Db->alias("a")
            ->field('a.id,a.item,a.sub_category,a.thumb,a.description,a.title,a.post_time,a.views,a.visible,c.cid,c.name')
            ->join("inner join qz_baike_category as c on a.sub_category = c.cid")
            ->join("inner join qz_baike_category as c1 on c.pid = c1.cid")
            ->order($condition['orderBy'])
            ->limit($pagesize . "," . $pageRow)
            ->where($map)
            ->order("a.post_time desc")
            ->select();
        return array("result" => $result, "count" => $count);
    }

    /**
     * 获取热门百科
     * @param  string $num 查询数量
     * @return array  $result       查询结果
     */
    public function getHotList($num = '')
    {
        $map['a.visible'] = array("EQ", '0');
        $map['a.thumb'] = array("NEQ", '');
        $map['a.is_top'] = array("EQ", '1');
        $map["a.remove"] = array("EQ", '0');
        //de($map);
        $Db = M('baike');
        $result = $Db->alias("a")
            ->field('a.id,a.item,a.sub_category,a.title,a.thumb,a.content,a.post_time,a.views,c.cid,c.name,c.url')
            ->join("inner join qz_baike_category as c on a.cid = c.cid")
            ->order('a.views DESC')
            ->limit("0," . $num)
            ->where($map)
            ->select();
        foreach ($result as $k => $v) {
            $result[$k]['content'] = htmlToText($v['content']);
        }
        return $result;
    }


    //查询列表  根据条件condition
    public function getListByCategoryForRss($limit = 50)
    {
        $map["a.remove"] = array("EQ", '0');
        $map['a.visible'] = array("EQ", '0');
        $map['a.cid'] = array("IN", '2,3,13');

        //dump($map);
        $Db = M('baike');
        $result = $Db->alias("a")
            ->field('a.id,a.description,a.title,a.content,a.post_time')
            ->join("inner join qz_baike_category as c on a.sub_category = c.cid")
            ->order('post_time desc,id desc')
            ->where($map)
            ->limit($limit)
            ->select();
        return $result;
    }

    /**
     * 获取热门百科
     * @param  string $num 查询数量
     * @return array  $result       查询结果
     */
    public function getNewList($limit = 8,$id)
    {
        $map['a.visible'] = array("EQ", '0');
        $map["a.remove"] = array("EQ", '0');
        $map["a.id"] = array("NEQ",$id);

        //de($map);
        $Db = M('baike');
        return $Db->alias("a")
            ->field('a.title,a.id')
            ->order('a.post_time DESC')
            ->limit($limit)
            ->where($map)
            ->select();
    }

    // 获取分站首页
    public function getHomeBaike($limit = 3)
    {
        $map = [
            "a.visible" => array("eq", "0"),
            "a.`remove`" => array("eq", "0"),
            "c1.cid" => array("in", [13,2]),
        ];

        return M("baike")->alias("a")
            ->where($map)
            ->field("a.id,a.item,a.title,a.thumb,a.post_time,c1.name as category_name")
            ->join("INNER JOIN qz_baike_category AS c ON a.sub_category = c.cid")
            ->join("INNER JOIN qz_baike_category AS c1 ON c.pid = c1.cid")
            ->order("a.post_time DESC")
            ->limit($limit)
            ->select();

    }

}