<?php

namespace Home\Model\Db;

use Think\Model;

class MeituV2Model extends Model
{
    protected $tableName = "tu";

    /**
     * 获取条件
     * @param array $params
     * @return array
     */
    public function getMap(array $params)
    {
        $map = [];
        $map['type'] = $params['type'] ?: 1;    //默认公装
        if (!empty($params['state'])) {
            $map['a.state'] = $params['state'];
        }
        if (!empty($params['keyword']) && is_numeric($params['keyword'])) {
            $map['a.id'] = $params['keyword'];
        } elseif (!empty($params['keyword'] && !is_numeric($params['keyword']))) {
            $map['a.title'] = ['like', "%{$params['keyword']}%"];
        }

        //公装分类
        if (!empty($params['pub_category'])) {
            $map['pub_category'] = (int)$params['pub_category'];
        }

        //家装分类
        if (!empty($params['space_category'])) {
            $map['home_space_category'] = (int)$params['space_category'];
        }

        if (!empty($params['part_category'])) {
            $map['home_part_category'] = (int)$params['part_category'];
        }

        if (!empty($params['style_category'])) {
            $map['home_style_category'] = (int)$params['style_category'];
        }

        if (!empty($params['layout_category'])) {
            $map['home_layout_category'] = (int)$params['layout_category'];
        }

        if (!empty($params['is_house_custom'])) {
            $map['is_house_custom'] = (int)$params['is_house_custom'];
        }
        return $map;
    }

    /**
     * 获取公装数量
     * @param $params
     * @return mixed
     */
    public function getPubCount($params)
    {
        $map = $this->getMap($params);
        $buildSql = $this->alias('a')->where($map)
            ->join('qz_tu_category c on c.id=a.pub_category')
            ->join('left join qz_adminuser au1 on au1.id=a.creator')
            ->join('left join qz_adminuser au2 on au2.id=a.operator')
            ->join('qz_tu_image ti on ti.meitu_id=a.id')
            ->field('a.id')
            ->group('a.id')
            ->buildSql();
        $count = $this->table($buildSql)->alias('t')->count();
        return $count;
    }

    /**
     * 获取公装列表
     * @param $params
     * @param $page
     * @param $perCount
     * @return mixed
     */
    public function getPubList($params, $page, $perCount)
    {
        $map = $this->getMap($params);
        $ret = $this->alias('a')->where($map)
            ->join('qz_tu_category c on c.id=a.pub_category')
            ->join('left join qz_adminuser au1 on au1.id=a.creator')
            ->join('left join qz_adminuser au2 on au2.id=a.operator')
            ->join('qz_tu_image ti on ti.meitu_id=a.id')
            ->field('a.*,c.name,au1.name creator_name,au2.name operator_name')
            ->order('a.created_at desc,a.id desc')
            ->group('a.id')
            ->limit($page, $perCount)
            ->select();
        return $ret;
    }

    /**
     * 获取公装详情
     * @param $id
     * @return mixed
     */
    public function getPubDetail($id)
    {
        $map['a.id'] = $id;
        $ret = $this->alias('a')->where($map)
            ->join('qz_tu_category c on c.id=a.pub_category')
            ->join('left join qz_adminuser au1 on au1.id=a.creator')
            ->join('left join qz_adminuser au2 on au2.id=a.operator')
            ->join('qz_tu_image ti on ti.meitu_id=a.id')
            ->field('a.*,c.name,au1.name creator_name,au2.name operator_name,ti.src image_src,ti.title image_title,ti.width image_width,ti.height image_height')
            ->find();
        return $ret;
    }


    /**
     * 获取家装数量
     * @param $params
     * @return mixed
     */
    public function getHomeCount($params)
    {
        $map = $this->getMap($params);
        $count = $this->alias('a')->where($map)
            ->count('a.id');

        return $count;
    }


    /**
     * 获取美图首页管理家装列表总数量
     * @param $params
     * @return mixed
     */
    public function getMeiluBoxListCount($params, $choosetype = 0)
    {
        $map = $this->getMap($params);
        $buildSql = $this->alias('a')->where($map)
            ->join('qz_tu_category c1 on c1.id=a.home_space_category')
            ->join('qz_tu_category c2 on c2.id=a.home_part_category')
            ->join('qz_tu_category c3 on c3.id=a.home_style_category')
            ->join('qz_tu_category c4 on c4.id=a.home_layout_category')
            ->join('left join qz_adminuser au1 on au1.id=a.creator')
            ->join('left join qz_adminuser au2 on au2.id=a.operator')
            ->join('qz_tu_image ti on ti.meitu_id=a.id')
            ->field('a.id')
            ->group('a.id')
            ->buildSql();
        if ($choosetype == 1) {
            $buildSql2 = $this->table($buildSql)->alias('t')
                ->join('left join qz_tu_home home on home.link_id = t.id and home.type = 2')
                ->field('home.id')
                ->where('home.id is not null')
                ->group('t.id')
                ->buildSql();
            $count = $this->table($buildSql2)->alias('t')->count();
        } elseif ($choosetype == 2) {
            $buildSql2 = $this->table($buildSql)->alias('t')
                ->join('left join qz_tu_home home on home.link_id = t.id and home.type = 2')
                ->field('home.id')
                ->where('home.id is null')
                ->group('t.id')
                ->buildSql();
            $count = $this->table($buildSql2)->alias('t')->count();
        } else {
            $count = $this->table($buildSql)->alias('t')->count();
        }
        return $count;
    }


    /**
     * 获取美图首页管理公装列表总数量
     * @param $params
     * @return mixed
     */
    public function getPubBoxListCount($params, $choosetype = 0)
    {
        $map = $this->getMap($params);
        $buildSql = $this->alias('a')->where($map)
            ->join('qz_tu_category c on c.id=a.pub_category')
            ->join('left join qz_adminuser au1 on au1.id=a.creator')
            ->join('left join qz_adminuser au2 on au2.id=a.operator')
            ->join('qz_tu_image ti on ti.meitu_id=a.id')
            ->field('a.id')
            ->group('a.id')
            ->buildSql();
        if ($choosetype == 1) {
            $buildSql2 = $this->table($buildSql)->alias('t')
                ->join('left join qz_tu_home home on home.link_id = t.id and home.type = 1')
                ->field('home.id')
                ->where('home.id is not null')
                ->group('t.id')
                ->buildSql();
            $count = $this->table($buildSql2)->alias('t')->count();
        } elseif ($choosetype == 2) {
            $buildSql2 = $this->table($buildSql)->alias('t')
                ->join('left join qz_tu_home home on home.link_id = t.id and home.type = 1')
                ->field('home.id')
                ->where('home.id is null')
                ->group('t.id')
                ->buildSql();
            $count = $this->table($buildSql2)->alias('t')->count();
        } else {
            $count = $this->table($buildSql)->alias('t')->count();
        }
        return $count;
    }


    /**
     * 获取家装列表
     * @param $params
     * @param $page
     * @param $perCount
     * @return mixed
     */
    public function getHomeList($params, $page, $perCount)
    {
        $map = $this->getMap($params);
        $subSql = $this->alias('a')->where($map)
            ->field('a.*')
            ->order('a.created_at desc,a.id desc')
            ->limit($page, $perCount)
            ->buildSql();

        $ret = M()->table($subSql)->alias("t")
            ->join('left join qz_adminuser au1 on au1.id=t.creator')
            ->join('left join qz_adminuser au2 on au2.id=t.operator')
            ->field("t.*,au1.`name` creator_name,au2.`name` operator_name")
            ->select();

        return $ret;
    }


    public function getMeiluBoxList($params, $page, $perCount, $type = 2, $choosetype = 0)
    {
        $map = $this->getMap($params);
//        $buildSql = $this->alias('a')->where($map)
//            ->join('qz_tu_category c1 on c1.id=a.home_space_category')
//            ->join('qz_tu_category c2 on c2.id=a.home_part_category')
//            ->join('qz_tu_category c3 on c3.id=a.home_style_category')
//            ->join('qz_tu_category c4 on c4.id=a.home_layout_category')
//            ->join('left join qz_adminuser au1 on au1.id=a.creator')
//            ->join('left join qz_adminuser au2 on au2.id=a.operator')
//            ->join('qz_tu_image ti on ti.meitu_id=a.id')
//            ->field('a.*,c1.name space_name,c2.name part_name,c3.name style_name,c4.name layout_name,au1.name creator_name,au2.name operator_name,ti.src image_src,ti.title image_title')
//            ->order('a.publish_time desc,a.id desc')
//            ->group('a.id')
//            ->buildSql();
        $buildSql = $this->alias('a')->where($map)
            ->join('qz_tu_category c1 on c1.id=a.home_space_category')
            ->join('qz_tu_category c2 on c2.id=a.home_part_category')
            ->join('qz_tu_category c3 on c3.id=a.home_style_category')
            ->join('qz_tu_category c4 on c4.id=a.home_layout_category')
            ->field('a.*,c1.name space_name,c2.name part_name,c3.name style_name,c4.name layout_name')
            ->order('a.publish_time desc,a.id desc')
            ->group('a.id')
            ->buildSql();

        if ($choosetype == 1) {
            $buildSql = $this->table($buildSql)->alias('t')
                ->field('t.*,home.id sethome,case  when home.id is null  then 2 else 1 end  homeset')
                ->join('left join qz_tu_home home on home.link_id = t.id and home.type = ' . $type)
                ->where('home.id is not null')
                ->group('t.id')
                ->limit($page, $perCount)
                ->order('t.publish_time desc,t.id desc')
                ->buildSql();
        } elseif ($choosetype == 2) {
            $buildSql = $this->table($buildSql)->alias('t')
                ->field('t.*,home.id sethome,case  when home.id is null  then 2 else 1 end  homeset')
                ->join('left join qz_tu_home home on home.link_id = t.id and home.type = ' . $type)
                ->where('home.id is null')
                ->group('t.id')
                ->limit($page, $perCount)
                ->order('t.publish_time desc,t.id desc')
                ->buildSql();
        } else {
            $buildSql = $this->table($buildSql)->alias('t')
                ->field('t.*,home.id sethome,case  when home.id is null  then 2 else 1 end  homeset')
                ->join('left join qz_tu_home home on home.link_id = t.id and home.type = ' . $type)
                ->group('t.id')
                ->limit($page, $perCount)
                ->order('t.publish_time desc,t.id desc')
                ->buildSql();
        }
        $ret = $this->table($buildSql)->alias('a')
            ->join('left join qz_adminuser au1 on au1.id=a.creator')
            ->join('left join qz_adminuser au2 on au2.id=a.operator')
            ->join('qz_tu_image ti on ti.meitu_id=a.id')
            ->field('a.*,au1.name creator_name,au2.name operator_name,ti.src image_src,ti.title image_title')
            ->select();

        return $ret;
    }


    public function getPubMeiluBoxList($params, $page, $perCount, $type = 1, $choosetype = 0)
    {
        $map = $this->getMap($params);
        $buildSql = $this->alias('a')->where($map)
            ->join('qz_tu_category c on c.id=a.pub_category')
            ->join('left join qz_adminuser au1 on au1.id=a.creator')
            ->join('left join qz_adminuser au2 on au2.id=a.operator')
            ->join('qz_tu_image ti on ti.meitu_id=a.id')
            ->field('a.*,c.name,au1.name creator_name,au2.name operator_name,ti.src image_src,ti.title image_title')
            ->order('a.publish_time desc,a.id desc')
            ->group('a.id')
            ->buildSql();
        if ($choosetype == 1) {
            $ret = $this->table($buildSql)->alias('t')
                ->field('t.*,home.id sethome,case  when home.id is null  then 2 else 1 end  homeset')
                ->join('left join qz_tu_home home on home.link_id = t.id and home.type = ' . $type)
                ->where('home.id is not null')
                ->group('t.id')
                ->limit($page, $perCount)
                ->order('t.publish_time desc,t.id desc')
                ->select();
        } elseif ($choosetype == 2) {
            $ret = $this->table($buildSql)->alias('t')
                ->field('t.*,home.id sethome,case  when home.id is null  then 2 else 1 end  homeset')
                ->join('left join qz_tu_home home on home.link_id = t.id and home.type = ' . $type)
                ->where('home.id is null')
                ->group('t.id')
                ->limit($page, $perCount)
                ->order('t.publish_time desc,t.id desc')
                ->select();
        } else {
            $ret = $this->table($buildSql)->alias('t')
                ->field('t.*,home.id sethome,case  when home.id is null  then 2 else 1 end  homeset')
                ->join('left join qz_tu_home home on home.link_id = t.id and home.type = ' . $type)
                ->group('t.id')
                ->limit($page, $perCount)
                ->order('t.publish_time desc,t.id desc')
                ->select();
        }

        return $ret;
    }


    /**
     * 获取家装详情
     * @param $id
     * @return mixed
     */
    public function getHomeDetail($id)
    {
        $map['a.id'] = $id;
        $ret = $this->alias('a')->where($map)
            ->join('qz_tu_category c1 on c1.id=a.home_space_category')
            ->join('qz_tu_category c2 on c2.id=a.home_part_category')
            ->join('qz_tu_category c3 on c3.id=a.home_style_category')
            ->join('qz_tu_category c4 on c4.id=a.home_layout_category')
            ->join('left join qz_adminuser au1 on au1.id=a.creator')
            ->join('left join qz_adminuser au2 on au2.id=a.operator')
            ->join('qz_tu_image ti on ti.meitu_id=a.id')
            ->field('a.*,c1.name space_name,c2.name part_name,c3.name style_name,c4.name layout_name,au1.name creator_name,au2.name operator_name,ti.src image_src,ti.title image_title,ti.width image_width,ti.height image_height')
            ->find();
        return $ret;
    }

    /**
     * 删除
     * @param $id
     * @return mixed
     */
    public function del($id)
    {
        $map['id'] = $id;
        $ret = $this->where($map)->delete();
        return $ret;
    }


    /**
     * 验证分类下是否有美图
     * @param $map
     * @return mixed
     */
    public function checkCategoryHadMeitu($map)
    {
        return $this->alias('tu')
            ->where($map)
            ->field('id')
            ->find();
    }

    /**
     * 批量更新数据
     * @param $ids
     * @param array $data
     * @return bool
     */
    public function multiPublish($ids, array $data)
    {
        $map['id'] = ['in', $ids];
        return $this->where($map)->data($data)->save();
    }

    /**
     * 批量添加数据
     * @param array $data
     * @return bool|string
     */
    public function multiAdd(array $data)
    {
        return $this->addAll($data);
    }

    public function getMulti($ids)
    {
        $map['id'] = ['in', $ids];
        return $this->where($map)->select();
    }


    /**
     * 获取美图账号列表
     * @return mixed
     */
    public function getMeituUsers()
    {
        $buidsql = M('tu')
            ->field('creator uid')
            ->group('creator')
            ->buildSql();

        return M()->table($buidsql)->alias('m')
            ->field('m.*,u.name')
            ->join('LEFT JOIN qz_adminuser as u ON u.id = m.uid')
            ->where('u.stat = 1 AND u.uid IN (6,24,25,88,26)')
            ->order('u.id')
            ->select();

    }


    /**
     * 查询满足条件的美图数量
     * @param $condition
     * @return mixed
     */
    public function getMarketContentMeituCount($condition)
    {
        return $this->where($condition)->count();
    }

    /**
     * [getMarketContentMeituList 获取美图列表]
     * @param integer $pageIndex [开始页]
     * @param integer $pageCount [每页数量]
     * @param  [type]  $params    [参数]
     * @return [type]             [description]
     */
    public function getMarketContentMeituList($condition, $pageIndex = 1, $pageCount = 20)
    {
        $buildsql = $this->where($condition)->field('id,title,type,state,publish_time,creator,operator,created_at,updated_at')->order('created_at DESC,id DESC')->limit($pageIndex, $pageCount)->buildSql();

        $result = M()->table($buildsql)->alias('b')
            ->field('b.*,u.name as uname')
            ->join('LEFT JOIN  qz_adminuser as u on u.id = b.creator')
            ->order('b.created_at DESC,b.id DESC')
            ->select();

        return $result;
    }

    /**
     * [getMarketContentMeituByDay 获取美图列表 折线图数据]
     * @param integer $pageIndex [开始页]
     * @param integer $pageCount [每页数量]
     * @param  [type]  $params    [参数]
     * @return [type]             [description]
     */
    public function getMarketContentMeituByDay($condition, $pageIndex = 1, $pageCount = 16)
    {

        $sql = $this->field('id,title,created_at,creator,state')->where($condition)->order('created_at,id')->buildSql();
        return M()->table($sql)
            ->alias('c')
            ->field('count(*) as meitu_num,FROM_UNIXTIME(c.created_at,"%Y-%m-%d") AS days')
            ->group('days')
            ->select();
    }


    //美图 按时间取发单数
    public function getMeituOrdersNumByTime($condition)
    {
        $buidsql = $this->field('id')->where($condition)->buildSql();

        $buidsql = M()->table($buidsql)->alias('b')
            ->field('b.*,count(co.content_id) as order_num,count(o.id) as fendans')
            ->join("LEFT JOIN qz_content_order_stats as co on co.content_id = b.id AND co.src = '3'")
            ->join("LEFT JOIN qz_orders as o on o.id = co.order_id AND o.type_fw = '1'")
            ->group("b.id")
            ->buildSql();

        return M()->table($buidsql)->alias('c')
            ->field('sum(order_num) as order_num')
            ->find();

    }

}