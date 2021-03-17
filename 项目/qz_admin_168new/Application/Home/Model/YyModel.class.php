<?php

namespace Home\Model;
Use Think\Model;

class YyModel extends Model{

    protected $autoCheckFields = false;

    /**
     * 获取链接数量
     * @param  string $cid  城市ID
     * @param  string $href 链接地址
     * @return integer
     */
    public function getHrefCollectCount($cid = '', $href = '')
    {
        if (!empty($cid)) {
            $map['cid'] = $cid;
        }

        if (!empty($href)) {
            $map['href'] = array('LIKE', '%' . $href . '%');
        }

        return M('yy_href_collect')->where($map)->count();
    }

    /**
     * 获取页面链接列表
     * @param  string  $cid  城市ID
     * @param  string  $href 链接地址
     * @param  integer $start 查询开始位置
     * @param  integer $limit 查询数量
     * @return array
     */
    public function getHrefCollectList($cid = '', $href = '', $start = 0, $limit = 50)
    {
        if (!empty($cid)) {
            $map['h.cid'] = $cid;
        }

        if (!empty($href)) {
            $map['h.href'] = array('LIKE', '%' . $href . '%');
        }

        return M('yy_href_collect')->alias('h')
                                          ->field('h.*, q.cname')
                                          ->join('LEFT JOIN qz_quyu AS q ON q.cid = h.cid')
                                          ->where($map)
                                          ->limit($start, $limit)
                                          ->select();
    }

    /**
     * 新增页面分类
     * @param array $save 新增内容
     */
    public function addPageCategory($save = array())
    {
        if (empty($save)) {
            return false;
        }
        return M('yy_page_category')->add($save);
    }

    /**
     * 编辑页面分类
     * @param  integer $id   页面分类ID
     * @param  array   $save 页面分类内容
     * @return bool
     */
    public function editPageCategory($id = 0, $save = array())
    {
        if (empty($id) || empty($save)) {
            return false;
        }
        return M('yy_page_category')->where(array('id' => $id))->save($save);
    }

    /**
     * 获取页面分类
     */
    public function getPageCategory($id = 0, $parent = 0, $name = '', $status = 0)
    {
        if (!empty($id)) {
            $map['id'] = $id;
        }
        if (!empty($parent)) {
            $map['parent'] = $parent;
        }
        if (!empty($name)) {
            $map['name'] = $name;
        }
        if (!empty($status)) {
            $map['status'] = $status;
        }
        return M('yy_page_category')->where($map)->select();
    }

    /**
     * 获取页面分类
     */
    public function getPageCategoryList()
    {
        return M('yy_page_category')->where(array('status' => 1))->select();
    }

    /**
     * 根据父级分类获取子分类
     * @param  integer $parent 父级分类
     * @param  integer $status 子分类状态
     * @return array
     */
    public function getPageCategoryByParent($parent = 0, $status = 1)
    {
        if (empty($parent)) {
            return false;
        }
        $map = array(
            'parent' => $parent,
            'status' => $status
        );
        return M('yy_page_category')->where($map)->select();
    }

    /**
     * 新增版本
     * @param array $save 存储内容
     */
    public function addVersion($save = array())
    {
        return M('yy_version')->add($save);
    }

    /**
     * 编辑版本
     * @param  integer $id   版本ID
     * @param  array   $save 存储内容
     * @return bool
     */
    public function editVersion($id = 0, $save = array())
    {
        if (empty($id) || empty($save)) {
            return false;
        }
        return M('yy_version')->where(array('id' => $id))->save($save);
    }

    /**
     * 删除版本
     * @param  integer $id 版本ID
     * @return bool
     */
    public function deleteVersion($id = 0)
    {
        if (empty($id)) {
            return false;
        }
        return M('yy_version')->where(array('id' => $id))->delete();
    }

    /**
     * 根据ID获取版本
     * @param  integer $id 版本ID]
     * @return array
     */
    public function getVersionById($id = 0)
    {
        if (empty($id)) {
            return false;
        }
        return M('yy_version')->alias('v')
                                     ->field('v.*, z.id AS parent_category_id, z.name AS parent_category_name, c.name AS category_name')
                                     ->join('qz_yy_page_category AS c ON c.id = v.yy_page_category_id')
                                     ->join('qz_yy_page_category AS z ON z.id = c.parent')
                                     ->where(array('v.id' => $id))
                                     ->find();
    }

    public function getVersionByYyPageCategoryId($yy_page_category_id = 0){
        if (empty($yy_page_category_id)) {
            return false;
        }
        $map['yy_page_category_id'] = $yy_page_category_id;
        return M('yy_version')->where($map)->find();
    }

    /**
     * 检查版本号是否存在
     * @param  integer $notId        排序的版本号ID
     * @param  string  $version_number 版本号
     * @return bool
     */
    public function chechVersionNumberExist($notId = 0, $version_number = '')
    {
        if (!empty($notId)) {
            $map['id'] = array('NEQ', $notId);
        }
        $map['version_number'] = $version_number;
        $count = M('yy_version')->where($map)->count();
        return ($count > 0) ? true : false;
    }

    /**
     * 获取版本数量
     * @param  integer $cid_parent        大分类ID
     * @param  integer $cid               分类ID
     * @param  string  $version_number    版本号
     * @param  string  $version_title     版本标题
     * @param  string  $online_time_start 开始上线时间
     * @param  string  $online_time_end   结束上线时间
     * @return integer
     */
    public function getVersionCount($cid_parent = 0, $cid = 0, $version_number = '', $version_title = '', $online_time_start = '', $online_time_end = '')
    {
        if (!empty($cid_parent)) {
            $map['c.parent'] = $cid_parent;
        }
        if (!empty($cid)) {
            $map['v.yy_page_category_id'] = $cid;
        }
        if (!empty($version_number)) {
            $map['v.version_number'] = array('LIKE', '%' . $version_number . '%');
        }
        if (!empty($version_title)) {
            $map['v.version_title'] = array('LIKE', '%' . $version_title . '%');
        }
        if (!empty($online_time_start)) {
            $map['v.online_time'][] = array('EGT', date("Y-m-d H:i:s", strtotime($online_time_start)));
        }
        if (!empty($online_time_end)) {
            $map['v.online_time'][] = array('ELT', date("Y-m-d H:i:s", strtotime($online_time_end)));
        }
        return M('yy_version')->alias('v')
                                     ->join('qz_yy_page_category AS c ON c.id = v.yy_page_category_id')
                                     ->where($map)
                                     ->count();
    }

    /**
     * 获取版本数量
     * @param  integer $cid_parent        大分类ID
     * @param  integer $cid               分类ID
     * @param  string  $version_number    版本号
     * @param  string  $version_title     版本标题
     * @param  string  $online_time_start 开始上线时间
     * @param  string  $online_time_end   结束上线时间
     * @param  integer $start             开始位置
     * @param  integer $limit             查询数量
     * @return array
     */
    public function getVersionList($cid_parent = 0, $cid = 0, $version_number = '', $version_title = '', $online_time_start = '', $online_time_end = '', $start = 0, $limit = 20)
    {
        if (!empty($cid_parent)) {
            $map['c.parent'] = $cid_parent;
        }
        if (!empty($cid)) {
            $map['v.yy_page_category_id'] = $cid;
        }
        if (!empty($version_number)) {
            $map['v.version_number'] = array('LIKE', '%' . $version_number . '%');
        }
        if (!empty($version_title)) {
            $map['v.version_title'] = array('LIKE', '%' . $version_title . '%');
        }
        if (!empty($online_time_start)) {
            $map['v.online_time'][] = array('EGT', date("Y-m-d H:i:s", strtotime($online_time_start)));
        }
        if (!empty($online_time_end)) {
            $map['v.online_time'][] = array('ELT', date("Y-m-d H:i:s", strtotime($online_time_end)));
        }
        return M('yy_version')->alias('v')
                                     ->field('v.*, z.name AS parent_category_name, c.name AS category_name')
                                     ->join('qz_yy_page_category AS c ON c.id = v.yy_page_category_id')
                                     ->join('qz_yy_page_category AS z ON z.id = c.parent')
                                     ->where($map)
                                     ->limit($start, $limit)
                                     ->order('v.online_time DESC')
                                     ->select();
    }

    /**
     * 获取版本名称列表
     * @return array
     */
    public function getVersionNumberList()
    {
        return M('yy_version')->field('id, version_number')->select();
    }

    /**
     * 获取汇总数据数量
     * @param  array  $map 查询条件
     * @return int
     */
    public function getYySummaryCountGroupByUrl($map = array())
    {
        $build = M('yy_summary')->field('pv')->where($map)->group('url')->buildSql();
        return M()->table($build)->alias('z')->count();
    }

    /**
     * 获取汇总数据列表
     * @param  array  $map   查询条件
     * @param  string $order 排序
     * @param  array  $limit 数量限制
     * @return array
     */
    public function getYySummaryListGroupByUrl($map = array(), $order = '', $limit = array())
    {
        $db = M('yy_summary')->field('url,SUM(pv) AS pv,SUM(uv) AS uv,SUM(bounce_count) AS bounce_count,SUM(exit_count) AS exit_count,SUM(visit_time) AS visit_time,SUM(visit_count) AS visit_count,SUM(entry_count) AS entry_count,SUM(order_count) AS order_count,SUM(real_order_count) AS real_order_count')
                             ->where($map)
                             ->group('url');
        if (!empty($order)) {
            $db = $db->order($order);
        }
        if (false === $limit) {
            $result = $db->select();
        } else {
            $result = $db->limit($limit[0],$limit[1])->select();
        }
        return $result;
    }

    /**
     * 获取汇总数据列表
     * @param  array  $map   查询条件
     * @param  string $order 排序
     * @param  array  $limit 数量限制
     * @return array
     */
    public function getYySummaryListGroupByDay($map = array())
    {
        return M('yy_summary')->field('url,SUM(pv) AS pv,SUM(uv) AS uv,SUM(bounce_count) AS bounce_count,SUM(exit_count) AS exit_count,SUM(visit_time) AS visit_time,SUM(visit_count) AS visit_count,SUM(entry_count) AS entry_count,SUM(order_count) AS order_count,SUM(real_order_count) AS real_order_count,day')
                              ->where($map)
                              ->group('day')
                              ->order('day ASC')
                              ->select();
    }

    /**
     * 获取UV来源
     * @param  array  $map 查询条件]
     * @return array
     */
    public function getYyUvSourceGroupBySourceGroup($map = array())
    {
        if (empty($map)) {
            return false;
        }

        $build = M('yy_uv_source')->field('count,src')->where($map)->buildSql();
        return M()->table($build)
                  ->alias('s')
                  ->field('SUM(s.count) AS uv_count, g.`name`')
                  ->join('LEFT JOIN qz_order_source AS z ON z.src = s.src and z.type = 1')
                  ->join('LEFT JOIN qz_order_source_group AS g ON g.id = z.groupid')
                  ->group('g.id')
                  ->select();
    }

    /**
     * 查询的搜索的url，默认返回15条
     * @param  string   $href              查询的字符串
     * @param  integer  $limit             默认限制返回15条
     * @return array
     */
    public function findUrl($href,$limit = 15)
    {
        $map['href'] =  array('EQ',$href);
        //有限查看是否有完全匹配的数据，如果有完全匹配的数组，模糊匹配查询数量减少一个
        $complete = M('yy_href_collect')->where($map)->find();
        if (!empty($complete)) {
            $limit = $limit - 1;
        }

        //重新定义名字查询条件
        $map['href'] =  array('like','%'.$href.'%');
        $map['id'] = array('NEQ',$complete['id']);
        $result = M('yy_href_collect')->where($map)->limit($limit)->select();
        if (!empty($complete)) {
            array_unshift($result, $complete);
        }
        return $result;
    }

    /**
     * 根据选择的版本查询版本的上线时间
     * @param  integer $cid               版本ID
     * @return array
     */
    public function getVersionTime($vid)
    {
        $map['id'] =  array('EQ',$vid);
        $result = M('yy_version')->where($map)->find();
        return $result;
    }


    /**
     * 根据URL查询URL的详情
     * @param  string   $url              查询的url
     * @return array
     */
    public function getPageByUrl($url)
    {
        $map['url'] =  array('EQ',$url);
        $map['status'] = 1;
        $result = M('yy_page_manage')->where($map)->find();
        return $result;
    }

    /**
     * 根据URL的ID查询URL的详情
     * @param  string   $id              查询的id
     * @return array
     */
    public function getPageById($id)
    {
        $map['id'] =  array('EQ',$id);
        $map['status'] = 1;
        $result = M('yy_page_manage')->where($map)->find();
        return $result;
    }

    /**
     * 根据页面分类获取页面
     * @param  integer $categoryid 页面分类
     * @return
     */
    public function getYyPageManageByCategoryid($categoryid = 0) {
        if (empty($categoryid)) {
            return false;
        }
        $map['status'] = 1;
        $map['categoryid'] = $categoryid;
        return M('yy_page_manage')->where($map)->find();
    }

    /**
     * 检查新增的url是否有历史版本记录
     * @param  array   $data             查询的条件：url的ID和version的id
     * @return array
     */
    public function getVersionContentById($data)
    {
        $map['pageid'] =  array('EQ',$data['pageid']);
        $map['vid'] =  array('EQ',$data['vid']);
        $result = M('yy_page_version')->where($map)->find();
        return $result;
    }

    /**
     * 检查新增的url是否有历史版本记录
     * @param  int   $id             查询的条件：url对应ID
     * @return array
     */
    public function getUrlContentById($id)
    {
        $map['p.id'] =  array('EQ',$id);
        $result = M('yy_page_manage')->alias('p')
                                ->join("qz_yy_page_category as c1 on p.categoryid = c1.id")
                                ->join("qz_yy_page_category as c2 on c1.parent = c2.id")
                                ->field('p.name,p.url,p.text,p.vid,c1.name as cname,c2.name as pname')
                                ->where($map)->find();
        return $result;
    }

    /**
     * 检查新增的url是否有历史版本记录
     * @param  int   $id             查询的条件：url对应ID
     * @return array
     */
    public function getUrlVersionById($id)
    {
        $map['v.pageid'] =  array('EQ',$id);
        $result = M('yy_page_version')->alias('v')
                                ->join("qz_yy_version as o on v.vid = o.id")
                                ->field('v.id,o.version_number,o.online_time,o.remark')
                                ->order('v.id desc')
                                ->where($map)->select();
        return $result;
    }

    /**
     * 获取所有的页面标题
     * @param  void
     * @return array
     */
    public function getAllPageNames()
    {
        $map['m.status'] =  array('EQ',1);
        $result = M('yy_page_manage')->alias('m')
                                ->join("right join qz_yy_version as v on m.vid = v.id")
                                ->field('m.id,m.name')
                                ->group("m.name")->order('m.id desc')
                                ->where($map)->select();
        return $result;
    }

    /**
     * 查询页面URL总数量
     * @param  array $keyword   $map  查询条件  name  version online_time url cid
     * @return array
     */
    public function getPageManageNum($keyword)
    {
        $map['v.online_time'] = array(
                                    array('GT',$keyword['start']),
                                    array('LT',$keyword['end'])
                                );
        if(!empty($keyword['version'])){
            $map['m.vid'] = $keyword['version'];
        }
        if(!empty($keyword['name'])){
            $map['m.name'] = $keyword['name'];
        }
        if(!empty($keyword['url'])){
            $map['m.url'] = $keyword['url'];
        }
        if(!empty($keyword['cid']) && $keyword['cid'] != 'null'){
            $map['m.categoryid'] = $keyword['cid'];
        }else{
            if(!empty($keyword['cid_parent'])){
                $where['parent'] = $keyword['cid_parent'];
                $where['status'] = 1;
                $cates = M('yy_page_category')->where($where)->select();
                foreach ($cates as $k => $v) {
                    $ids[] = $v['id'];
                }
                if(!empty($ids)){
                    $map['m.categoryid'] = array('IN',$ids);
                }
            }
        }
        $map['m.status'] = 1;
        $result = M('yy_page_manage')->alias('m')
                                        ->join('qz_yy_version as v on m.vid = v.id')
                                        ->join('qz_yy_page_category as c1 on m.categoryid = c1.id')
                                        ->join('qz_yy_page_category as c2 on c2.id = c1.parent')
                                        ->where($map)
                                        ->count();
        return $result;
    }

    /**
     * 获取分页的页面URL内容
     * @param  array   $map  查询条件  name  version start end url cid
     * @return array
     */
    public function getPageManageContent($keyword,$order,$start=null,$end=null)
    {
        $map['v.online_time'] = array(
                                    array('GT',$keyword['start']),
                                    array('LT',$keyword['end'])
                                );
        if(!empty($keyword['version'])){
            $map['m.vid'] = $keyword['version'];
        }
        if(!empty($keyword['name'])){
            $map['m.name'] = $keyword['name'];
        }
        if(!empty($keyword['url'])){
            $map['m.url'] = $keyword['url'];
        }
        if(!empty($keyword['cid']) && $keyword['cid'] != 'null'){
            $map['m.categoryid'] = $keyword['cid'];
        }else{
            if(!empty($keyword['cid_parent'])){
                $where['parent'] = $keyword['cid_parent'];
                $where['status'] = 1;
                $cates = M('yy_page_category')->where($where)->select();
                foreach ($cates as $k => $v) {
                    $ids[] = $v['id'];
                }
                if(!empty($ids)){
                    $map['m.categoryid'] = array('IN',$ids);
                }
            }
        }
        $map['m.status'] = 1;
        $result = M('yy_page_manage')->alias('m')
                                    ->join('qz_yy_version as v on m.vid = v.id')
                                    ->join('qz_yy_page_category as c1 on m.categoryid = c1.id')
                                    ->join('qz_yy_page_category as c2 on c2.id = c1.parent')
                                    ->field("m.id,m.name,m.url,v.version_number,v.online_time,c1.name as cname,c2.name as pname")
                                    ->where($map)
                                    ->limit($start.",".$end)
                                    ->order('v.online_time desc')
                                    ->select();
        return $result;
    }

    /**
     * 删除单条URL
     * @param
     * @return array
     */
    public function delPageById($map)
    {
        $data['status'] = 2;
        $result = M('yy_page_manage')->where($map)->save($data);
        return $result;
    }

    /**
     * 查询子分类
     * @param string    $bigcate   父级分类ID
     * @return array    $result    查询结果
     */
    public function getChildCate($bigcate)
    {
        if(!empty($bigcate)){
            $where['parent'] = $bigcate;
        }
        $where['status'] = 1;
        $result = M('yy_page_category')->where($where)->select();
        return $result;
    }

    /**
     * 查询页面指标
     * @param   array       $map        查询条件
     * @param   string      $order      排序方式
     * @param   string      $start      分页开始
     * @param   string      $end        分页长度
     * @return  array       $result     查询结果
     */
    public function getUrlDatasContent($map,$order,$start,$end)
    {
        if(!empty($map['name'])){
            $where['m.name'] = $map['name'];
        }
        if(!empty($map['pageaddr'])){
            $where['m.url'] = $map['pageaddr'];
        }
        if(!empty($map['v_start'])){
            $where['v.online_time'] = array(
                    array('GT',$map['v_start']),
                    array('LT',$map['v_end']),
                    'and'
                );
        }
        if(!empty($map['ctype']) && $map['ctype'] != 'null'){
            $where['m.categoryid'] = $map['ctype'];
        }else{
            if(!empty($map['ptype'])){
                $where1['parent'] = $keyword['ptype'];
                $where1['status'] = 1;
                $cates = M('yy_page_category')->where($where1)->select();
                foreach ($cates as $k => $v) {
                    $ids[] = $v['id'];
                }
                if(!empty($ids)){
                    $where['m.categoryid'] = array('IN',$ids);
                }
            }
        }
        $where['m.status'] = 1;
        //总条数
        $count = M('yy_page_manage')->alias('m')
                                    ->join('qz_yy_version as v on m.vid = v.id')
                                    ->join('qz_yy_page_category as c1 on m.categoryid = c1.id')
                                    ->join('qz_yy_page_category as c2 on c2.id = c1.parent')
                                    ->where($where)
                                    ->count();

        //查询要统计的URL
        $urls = M('yy_page_manage')->alias('m')
                                    ->join('qz_yy_version as v on m.vid = v.id')
                                    ->join('qz_yy_page_category as c1 on m.categoryid = c1.id')
                                    ->join('qz_yy_page_category as c2 on c2.id = c1.parent')
                                    ->field("m.id,m.name,m.url,v.version_number,v.online_time,c1.name as cname,c2.name as pname")
                                    ->where($where)
                                    ->limit($start.",".$end)
                                    ->select();

        if(!empty($urls)){
            foreach ($urls as $k => $v) {
                $url = $v['url'];
                //先生成查询条件，两种不同的查询方式:
                //1，上线日期前后时间对比
                $urls[$k]['day'] = date('Y-m-d',strtotime($v['online_time']));
                if(!empty($map['before']) && !empty($map['after'])){
                    //生成对比时间
                    $start_time1 = date('Y-m-d',(strtotime($v['online_time']) - ($map['before']*24*3600)));//X天前
                    $end_time1 = date('Y-m-d',strtotime($v['online_time']));//上线天的数据（不包含上线那天）
                    $start_time2 = date('Y-m-d',strtotime($v['online_time']));//上线版本下一天
                    $end_time2 = date('Y-m-d',(strtotime($v['online_time']) + ($map['after']*24*3600)));//上线版本下一天

                    $search_map1['url'] = $url;
                    $search_map1['day'] = array(
                            array('EGT',$start_time1),
                            array('LT',$end_time1),
                            'and'
                        );
                    $search_map2['url'] = $url;
                    $search_map2['day'] = array(
                            array('EGT',$start_time2),
                            array('LT',$end_time2),
                            'and'
                        );
                    $time1 = (strtotime($end_time1)-24*3600);
                    $time2 = (strtotime($end_time2)-24*3600);
                    //统计对比时间
                    $urls[$k]['starttime']  = date('Y/m/d',strtotime($start_time1)).' - '.date('Y/m/d',$time1);
                    $urls[$k]['endtime']    = date('Y/m/d',strtotime($start_time2)).' - '.date('Y/m/d',$time2);
                }
                //2，指定时间段内的数据对比
                if(!empty($map['freestart_s']) && !empty($map['freeend_s'])){
                    $search_map1['url'] = $url;
                    $search_map1['day'] = array(
                            array('EGT',$map['freestart_s']),
                            array('LT',$map['freestart_e']),
                            'and'
                        );
                    $search_map2['url'] = $url;
                    $search_map2['day'] = array(
                            array('GT',$map['freeend_s']),
                            array('ELT',$map['freeend_e']),
                            'and'
                        );
                    //统计对比时间
                    $urls[$k]['starttime']  = date('Y/m/d',strtotime($map['freestart_s'])).' - '.date('Y/m/d',strtotime($map['freestart_e']));
                    $urls[$k]['endtime']    = date('Y/m/d',strtotime($map['freeend_s'])).' - '.date('Y/m/d',strtotime($map['freeend_e']));
                }
                if(!empty($search_map1) && !empty($search_map2)){
                    $predata = M("yy_summary")->where($search_map1)
                                                ->field("url,sum(pv) as all_pv,sum(uv) as all_uv,sum(bounce_count) as all_bounce_count,sum(exit_count) as all_exit_count,sum(visit_time) as all_visit_time,sum(visit_count) as all_visit_count,sum(entry_count) as all_entry_count,sum(order_count) as all_order_count,sum(real_order_count) as all_real_order_count")
                                                ->select();
                    $lastdata = M("yy_summary")->where($search_map2)
                                                ->field("url,sum(pv) as all_pv,sum(uv) as all_uv,sum(bounce_count) as all_bounce_count,sum(exit_count) as all_exit_count,sum(visit_time) as all_visit_time,sum(visit_count) as all_visit_count,sum(entry_count) as all_entry_count,sum(order_count) as all_order_count,sum(real_order_count) as all_real_order_count")
                                                ->select();
                }


                //计算结果
                //日均PV      pv
                $urls[$k]['predata']['pv']  = $predata[0]['all_pv'];
                $urls[$k]['lastdata']['pv'] = $lastdata[0]['all_pv'];
                $urls[$k]['p_pv'] = round((($lastdata[0]['all_pv']-$predata[0]['all_pv'])/$predata[0]['all_pv'])*100,2);//PV两增长百分比

                //日均UV      uv
                $urls[$k]['predata']['uv']  = $predata[0]['all_uv'];
                $urls[$k]['lastdata']['uv'] = $lastdata[0]['all_uv'];
                $urls[$k]['p_uv'] = round((($lastdata[0]['all_uv']-$predata[0]['all_uv'])/$predata[0]['all_uv'])*100,2);//UV两增长百分比

                //跳出率  跳出次数/PV     bounce_count 跳出次数
                $urls[$k]['predata']['tiaochu']  = round(($predata[0]['all_bounce_count']/$predata[0]['all_pv'])*100,2);
                $urls[$k]['lastdata']['tiaochu'] = round(($lastdata[0]['all_bounce_count']/$lastdata[0]['all_pv'])*100,2);
                $urls[$k]['p_tiaochu'] = round((($urls[$k]['lastdata']['tiaochu']-$urls[$k]['predata']['tiaochu'])/$urls[$k]['predata']['tiaochu'])*100,2);//跳出率两增长百分比

                //退出率   退出数/PV     exit_count 退出次数
                $urls[$k]['predata']['tuichu']  = round(($predata[0]['all_exit_count']/$predata[0]['all_pv'])*100,2);
                $urls[$k]['lastdata']['tuichu'] = round(($lastdata[0]['all_exit_count']/$lastdata[0]['all_pv'])*100,2);
                $urls[$k]['p_tuichu'] = round((($urls[$k]['lastdata']['tuichu']-$urls[$k]['predata']['tuichu'])/$urls[$k]['predata']['tuichu'])*100,2);//退出率两增长百分比

                //访问时长  总时间/PV   visit_time 访问总时间
                $urls[$k]['predata']['shichang']  = round(($predata[0]['all_visit_time']/$predata[0]['all_pv']),2);
                $urls[$k]['lastdata']['shichang'] = round(($lastdata[0]['all_visit_time']/$lastdata[0]['all_pv']),2);
                $urls[$k]['p_shichang'] = round((($urls[$k]['lastdata']['shichang']-$urls[$k]['predata']['shichang'])/$urls[$k]['predata']['shichang'])*100,2);//访问时长增长百分比

                //日均发单量  order_count
                $urls[$k]['predata']['fdl']  = $predata[0]['all_order_count'];
                $urls[$k]['lastdata']['fdl'] = $lastdata[0]['all_order_count'];
                $urls[$k]['p_fdl'] = round((($lastdata[0]['all_order_count']-$predata[0]['all_order_count'])/$predata[0]['all_order_count'])*100,2);//发单量增长百分比

                //日均实际分单量 real_order_count
                $urls[$k]['predata']['rfdl']  = $predata[0]['all_real_order_count'];
                $urls[$k]['lastdata']['rfdl'] = $lastdata[0]['all_real_order_count'];
                $urls[$k]['p_rfdl'] = round((($lastdata[0]['all_real_order_count']-$predata[0]['all_real_order_count'])/$predata[0]['all_real_order_count'])*100,2);//日均实际分单量增长百分比

                //发单转化率  发单量/UV
                $urls[$k]['predata']['fdzhl']  = round(($predata[0]['all_order_count']/$predata[0]['all_uv'])*100);
                $urls[$k]['lastdata']['fdzhl'] = round(($lastdata[0]['all_order_count']/$lastdata[0]['all_uv'])*100);
                $urls[$k]['p_fdzhl'] = round((($urls[$k]['lastdata']['fdzhl']-$urls[$k]['predata']['fdzhl'])/$urls[$k]['predata']['fdzhl'])*100,2);//发单转化率增长百分比

                //实际分单转化率 实际分单量/UV*100%
                $urls[$k]['predata']['sjzhl']  = ($predata[0]['all_real_order_count']/$predata[0]['all_uv'])*100;
                $urls[$k]['predata']['sjzhl'] = number_format($urls[$k]['predata']['sjzhl'],'4');
                $urls[$k]['lastdata']['sjzhl'] = ($lastdata[0]['all_real_order_count']/$lastdata[0]['all_uv'])*100;
                $urls[$k]['lastdata']['sjzhl'] = number_format($urls[$k]['lastdata']['sjzhl'],'4');
                $urls[$k]['p_sjzhl'] = (($urls[$k]['lastdata']['sjzhl']-$urls[$k]['predata']['sjzhl'])/$urls[$k]['predata']['sjzhl'])*100;
                $urls[$k]['p_sjzhl'] = number_format($urls[$k]['p_sjzhl'],'4');//发单转化率增长百分比

                //入口页次数   entry_count
                $urls[$k]['predata']['rukou']  = $predata[0]['order_countentry_count'];
                $urls[$k]['lastdata']['rukou'] = $lastdata[0]['order_countentry_count'];
                $urls[$k]['p_rukou'] = round((($urls[$k]['lastdata']['rukou']-$urls[$k]['predata']['rukou'])/$urls[$k]['predata']['rukou'])*100,2);//发单转化率增长百分比

                //退出页次数
                $urls[$k]['predata']['tcycx']  = $predata[0]['all_exit_count'];
                $urls[$k]['lastdata']['tcycx'] = $lastdata[0]['all_exit_count'];
                $urls[$k]['p_tcycx'] = round((($urls[$k]['lastdata']['tcycx']-$urls[$k]['predata']['tcycx'])/$urls[$k]['predata']['tcycx'])*100,2);//退出率两增长百分比

            }
        }
        $result['count'] = $count;
        $result['urls'] = $urls;
        //自定义时间段查询
        return $result;
    }

    /**
     * 获取部门数据
     */
    public function getDepartmentConn($id = 0, $parent = 0, $name = '', $status = 0)
    {
        if (!empty($id)) {
            $map['id'] = $id;
        }
        if (!empty($parent)) {
            $map['parent'] = $parent;
        }
        if (!empty($name)) {
            $map['name'] = $name;
        }
        if (!empty($status)) {
            $map['status'] = $status;
        }
        return M('yy_department')->where($map)->select();
    }

    /**
     * 新增页面分类
     * @param array $save 新增内容
     */
    public function addNewDepartment($save = array())
    {
        if (empty($save)) {
            return false;
        }
        return M('yy_department')->add($save);
    }

    /**
     * 获取部门列表
     */
    public function getDepartmentList()
    {
        return M('yy_department')->where(array('status' => 1))->select();
    }

    /**
     * 编辑部门信息
     * @param  integer $id   部门ID
     * @param  array   $save 部门内容
     * @return bool
     */
    public function editDepartment($id = 0, $save = array())
    {
        if (empty($id) || empty($save)) {
            return false;
        }
        return M('yy_department')->where(array('id' => $id))->save($save);
    }

    /**
     * 根据父级查询下级部门
     * @param  integer $parent 父级部门ID
     * @param  integer $status 下级部门状态
     * @return array
     */
    public function getDepartmentByParent($parent = 0, $status = 1)
    {
        if (empty($parent)) {
            return false;
        }
        $map = array(
            'parent' => $parent,
            'status' => $status
        );
        return M('yy_department')->where($map)->select();
    }

    /**
     * 删除部门
     * @param  integer  $id     部门ID
     * @param  array    $save   修改的参数
     * @param  int      $level  部门级别
     * @return bool
     */
    public function delDepartment($id = 0, $save = array(), $level)
    {
        //1为顶级部门   2为二级部门  3为三级部门
        if (empty($id) || empty($save) || empty($level)) {
            return false;
        }
        if($level != 3){
            //有下级分类时
            $result = $this->getDepartmentByParent($id);
            foreach ($result as $k => $v) {
                $ids[] = $v['id'];
            }
            if($level == 1){
                //是顶级部门
                if(!empty($ids)){
                    $where = [
                        array(
                            'id' => array('EQ',$id)
                        ),
                        array(
                            'id' => array('IN',$ids)
                        ),
                        array(
                            'parent' => array('IN',$ids)
                        ),
                        '_logic' => 'OR'
                    ];
                }else{
                    $where['id'] = $id;
                }

            }else if($level == 2){
                //二级部门
                if(!empty($ids)){
                    $where = [
                        array(
                            'id' => array('EQ',$id)
                        ),
                        array(
                            'id' => array('IN',$ids)
                        ),
                        '_logic' => 'OR'
                    ];
                }else{
                    $where['id'] = $id;
                }
            }
        }else{
            $where['id'] = $id;
        }
        $res = M('yy_department')->where($where)->save($save);
        return $res;
    }

    /**
     * 获取所有部门
     */
    public function getDeptList()
    {
        return M('yy_department')->where(array('status' => 1))->select();
    }

    /**
     * 获取微信号数据
     * @param  int      $id         id
     * @param  string   $appid      微信APPID
     * @param  string   $wxname     微信名称
     * @param  string   $wxmanager  微信管理员
     * @param  int      $status     状态
     * @return array    $result
     *
     */
    public function getWXConn($id = 0, $appid = '', $wxname = '' , $wxmanager = '', $status = 1)
    {
        if (!empty($id)) {
            $map['id'] = $id;
        }
        if (!empty($appid)) {
            $map['appid'] = $appid;
        }
        if (!empty($wxname)) {
            $map['wxname'] = $wxname;
        }
        if (!empty($wxmanager)) {
            $map['wxmanager'] = $wxmanager;
        }
        if (!empty($status)) {
            $map['status'] = $status;
        }
        return M('yy_wx_manage')->where($map)->select();
    }

    /**
     * 新增微信公众号
     * @param array $save 新增内容
     */
    public function addNewWX($save = array())
    {
        if (empty($save)) {
            return false;
        }
        return M('yy_wx_manage')->add($save);
    }


    /**
     * 获取微信数量
     * @param  array $data        查询条件
     * @return integer
     */
    public function getWXNumCount($data)
    {
        if (!empty($data['appid'])) {
            $map['w.appid'] = $data['appid'];
        }
        if (!empty($data['appsecret'])) {
            $map['w.appsecret'] = $data['appsecret'];
        }
        if (!empty($data['dept'])) {
            $map['d.id'] = $data['dept'];
        }
        if (!empty($data['wxmanager'])) {
            $map['w.wxmanager'] = $data['wxmanager'];
        }
        if (!empty($data['regtime'])) {
            //注册日期
            $times = explode(' - ', $data['regtime']);
            $map['w.regtime'] = [array('EGT',strtotime($times[0])),array('ELT',strtotime($times[1])),'AND'];
        }
        if (!empty($data['auttime'])) {
            //认证日期
            $times = explode(' - ', $data['auttime']);
            $map['w.auttime'] = [array('EGT',strtotime($times[0])),array('ELT',strtotime($times[1])),'AND'];
        }
        $map['w.status'] = 1;
        return M('yy_wx_manage')->alias('w')
                            ->join('qz_yy_department AS d ON d.id = w.wxdept')
                            ->where($map)
                            ->count();
    }

    /**
     * 获取微信列表
     * @param  array    $data           查询条件
     * @param  intger   $start
     * @param  intger   $limit          大分类ID
     * @return array
     */
    public function getWXList($data, $start = 0, $limit = 20)
    {
        if (!empty($data['appid'])) {
            $map['w.appid'] = $data['appid'];
        }
        if (!empty($data['appsecret'])) {
            $map['w.appsecret'] = $data['appsecret'];
        }
        if (!empty($data['dept'])) {
            $map['d.id'] = $data['dept'];
        }
        if (!empty($data['wxmanager'])) {
            $map['w.wxmanager'] = $data['wxmanager'];
        }
        if (!empty($data['regtime'])) {
            //注册日期
            $times = explode(' - ', $data['regtime']);
            $map['w.regtime'] = [array('EGT',strtotime($times[0])),array('ELT',strtotime($times[1])),'AND'];
        }
        if (!empty($data['auttime'])) {
            //认证日期
            $times = explode(' - ', $data['auttime']);
            $map['w.auttime'] = [array('EGT',strtotime($times[0])),array('ELT',strtotime($times[1])),'AND'];
        }
        $map['w.status'] = 1;
        return M('yy_wx_manage')->alias('w')
                                    ->field('w.*, d.name as deptname,d1.name as firstname')
                                    ->join('qz_yy_department AS d ON d.id = w.wxdept')
                                    ->join('qz_yy_department AS d1 ON d1.id = d.parent')
                                    ->where($map)
                                    ->limit($start, $limit)
                                    ->order('w.id DESC')
                                    ->select();
    }

    /**
     * 获取微信列表
     * @param  intger   $id          微信ID
     * @return array
     */
    public function getWXConnById($id)
    {
        if(!empty($id)){
            $map['w.id']      = $id;
            $map['w.status']  = 1;
            return M('yy_wx_manage')->alias('w')
                                    ->field('w.*,d1.id as topdept')
                                    ->join('qz_yy_department AS d ON d.id = w.wxdept')
                                    ->join('qz_yy_department AS d1 ON d1.id = d.parent')
                                    ->where($map)
                                    ->find();
        }
    }

    /**
     * 获取微信
     * @param  string   $appid          微信appid
     * @param  string   $appsecret      微信appsecret
     * @return array
     */
    public function getWXConnByAppid($appid,$appsecret)
    {
        if(!empty($appid) && !empty($appsecret)){
            $map['w.appid']      = $appid;
            $map['w.appsecret']      = $appsecret;
            $map['w.status']  = 1;
            return M('yy_wx_manage')->alias('w')
                                    ->field('w.*')
                                    ->join('qz_yy_department AS d ON d.id = w.wxdept')
                                    ->where($map)
                                    ->find();
        }
    }

    /**
     * 编辑微信信息
     * @param  integer $id   微信ID
     * @param  array   $save 部门内容
     * @return bool
     */
    public function editWXConn($id = 0, $save = array())
    {
        if (empty($id) || empty($save)) {
            return false;
        }
        return M('yy_wx_manage')->where(array('id' => $id))->save($save);
    }

    /**
     * 根据部门名称查询部门
     * @param  string   $name       部门名称
     * @return array    $result
     */
    public function getDeptByName($name)
    {
        if(!empty($name)){
            $map['name'] = $name;
            $map['status'] = 1;
            return M('yy_department')->where($map)->find();
        }
    }

    /**
     * 更新yy_statistics数据
     * @param  string $day    [description]
     * @param  string $url_id [description]
     * @param  string $src    [description]
     * @param  string $type   [description]
     * @param  string $field  [description]
     * @return [type]         [description]
     */
    public function editYyStatisticsOrderCount($day="",$url_id="",$src="",$type="",$field = "order_count")
    {
        $map = array(
            "day" => array("EQ",$day),
            "url_id" => array("EQ",$url_id),
            "order_source_src" => array("EQ",$src)
        );
        $model = M("yy_statistics");
        $model->where($map);
        if ($type == 1) {
           return $model->setInc($field);
        } else {
           return $model->setDec($field);
        }
    }

    /**
     * 更新yysummary数据
     * @param  string $day    [description]
     * @param  string $url_id [description]
     * @param  string $src    [description]
     * @param  string $type   [description]
     * @return [type]         [description]
     */
    public function editYySummaryOrderCount($day="",$url="",$type="",$field = "order_fen_count")
    {
        $map = array(
            "day" => array("EQ",$day),
            "url" => array("EQ",$url)
        );
        $model = M("yy_summary");
        $model->where($map);
        if ($type == 1) {
           return $model->setInc($field);
        } else {
           return $model->setDec($field);
        }
    }

    /**
     * 更新yy_src_statistics数据
     * @param  string $day    [description]
     * @param  string $url_id [description]
     * @param  string $src    [description]
     * @param  string $type   [description]
     * @return [type]         [description]
     */
    public function editYySrcStatisticsOrderCount($day="",$src="",$type="",$field = "order_fen_count")
    {
        $map = array(
            "day" => array("EQ",$day),
            "order_source_src" => array("EQ",$src)
        );
        $model = M("yy_src_statistics");
        $model->where($map);
        if ($type == 1) {
           return $model->setInc($field);
        } else {
           return $model->setDec($field);
        }
    }

    public function addOrdersInfo($data)
    {
        return M("yy_order_info")->add($data);
    }

    /**
     * 删除渠道订单采集数据
     * @param  [type] $ids [description]
     * @return [type]      [description]
     */
    public function delSrcListByIds($ids)
    {
        $map = array(
            "oid" => array("IN",$ids)
        );
        return M("yy_order_info")->where($map)->delete();
    }
}
