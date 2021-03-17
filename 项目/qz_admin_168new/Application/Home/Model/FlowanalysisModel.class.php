<?php
/**
 * 后台用户
 */
namespace Home\Model;
use Think\Model;
class FlowanalysisModel extends Model{
    protected $autoCheckFields = false;


    /**
     * 检查频道名称是否存在
     * @param  string   $name     [频道名称]
     * @return array    $result   []
     */
    public function checkChannelName($name)
    {
        $where['id'] = $name;
        $result = M('yy_channel')->where($where)->find();
        return $result;
    }

    /**
     * 新增频道
     * @param  array    $data     [频道内容数组]
     * @return array    $result   []
     */
    public function addNewChannel($data)
    {
        return M('yy_channel')->add($data);
    }

    /**
     * 查询所有频道名称（作为搜索条件显示）
     * @param  void
     * @return array    $result   所有的频道名称
     */
    public function selectAllChannelNames()
    {
        $where['status'] = 1;
        $result = M('yy_channel')->where($where)->field('id,name')->select();
        return $result;
    }

    /**
     * 查询频道列表
     * @param  array        $keyword
     * @param  string       $start      页码
     * @param  string       $end      分页长度
     * @return array        $result   所有的频道名称
     */
    public function getChannelList($keyword,$start,$end)
    {
        if(!empty($keyword['name'])){
            $where['name'] = $keyword['name'];
        }
        if(!empty($keyword['id'])){
            $where['code'] = $keyword['id'];
        }
        if(!empty($keyword['url'])){
            $where['url'] = ['like','%'.trim($keyword['url']).'%'];
        }
        if(!empty($keyword['time'])){
            //$where['name'] = $keyword['name'];
            $times = explode(' - ', $keyword['time']);
            $startTime  = strtotime($times[0]);
            $endTime    = strtotime($times[1]);
            $endTime    = $endTime + 86400;
        }else{
            //日期为最近一个月
            $startTime = strtotime("-1 month");
            $endTime = time();
        }
        if(!empty($startTime) && !empty($endTime)){
             $where['add_time'] = array(
                        array('GT',$startTime),
                        array('LT',$endTime),
                        'and'
                    );
        }
        $where['status'] = 1;
        $order = 'edit_time desc';
        $result['list'] = M('yy_channel')->where($where)->order($order)->limit($start.",".$end)->select();
        $result['count'] = M('yy_channel')->where($where)->count();
        return $result;
    }

    /**
     * 删除频道
     * @param  string        $id        要删除的ID
     * @return string        $result   结果
     */
    public function delChannelNow($id)
    {
        if(!empty($id)){
            $where['id'] = $id;
            $data['status'] = 2;
            return M('yy_channel')->where($where)->save($data);
        }
    }

    /**
     * 根据ID查询频道
     * @param  string        $id        要查询的ID
     * @return string        $result    结果
     */
    public function getChannelById($id)
    {
        if(!empty($id)){
            $where['id'] = $id;
            return M('yy_channel')->where($where)->find();
        }
    }

    /**
     * 根据ID编辑频道
     * @param  string        $id        要编辑的频道ID
     * @param  array         $data      新频道数据
     * @return string        $result    结果
     */
    public function editChannelById($id,$data)
    {
        if(!empty($id)){
            $where['id'] = trim($id);
            $result = M('yy_channel')->where($where)->save($data);
            return $result;
        }
    }

    /**
     * 新增频道标签
     * @param  string        $id        要编辑的频道ID
     * @param  array         $data      新频道数据
     * @return string        $result    结果
     */
    public function addChannelTag($data)
    {
        $data['status'] = 1;
        $data['add_time'] = time();
        $data['edit_time'] = time();

        return M('yy_channel_tag')->add($data);
    }

    /**
     * 新增频道标签
     * @param  string        $id        要编辑的频道ID
     * @param  array         $data      新频道数据
     * @return string        $result    结果
     */
    public function saveTagChannels($data)
    {
        return M('yy_channel_tag_relation')->addAll($data);
    }

    /**
     * 查询所有的有效频道标签
     * @param  void
     * @return string        $result    结果
     */
    public function getAllChannelTags()
    {
        $where['status'] = 1;
        return M('yy_channel_tag')->where($where)->field('id,name,code')->select();
    }


    /**
     * 查询频道列表
     * @param  array        $keyword
     * @param  string       $start      页码
     * @param  string       $end      分页长度
     * @return array        $result   所有的频道名称
     */
    public function getChannelTagsList($keyword,$start,$end)
    {
        if(!empty($keyword['name'])){
            $where['t.name'] = $keyword['name'];
        }
        if(!empty($keyword['id'])){
            $where['t.code'] = $keyword['id'];
        }
        if(!empty($keyword['time'])){
            //$where['name'] = $keyword['name'];
            $times = explode(' - ', $keyword['time']);
            $startTime  = strtotime($times[0]);
            $endTime    = strtotime($times[1]);
            $endTime    = $endTime + 86400;
        }else{
            //日期为最近一个月
            $startTime = strtotime("-1 month");
            $endTime = time();
        }
        if(!empty($startTime) && !empty($endTime)){
             $where['t.add_time'] = array(
                        array('GT',$startTime),
                        array('LT',$endTime),
                        'and'
                    );
        }
        $where['status'] = 1;
        $order = 'edit_time desc';
        $result['list'] = M('yy_channel_tag')->alias('t')
                                ->join("qz_yy_channel_tag_relation as r on t.id = r.channel_tag_id")
                                ->field('t.*,count(r.id) as num')
                                ->where($where)
                                ->order($order)
                                ->group('t.id')
                                ->limit($start.",".$end)
                                ->select();
        $result['count'] = M('yy_channel_tag')->alias('t')->where($where)->count();
        return $result;
    }

    /**
     * 删除频道标签
     * @param  string       $id         要删除的ID
     * @return array        $result
     */
    public function delTagNow($id)
    {
        //删除qz_yy_channel_tag_relation 表内容
        $where['channel_tag_id'] = trim($id);
        $res = M('yy_channel_tag_relation')->where($where)->delete();
        //删除qz_yy_channel_tag表内容  status=2
        $t_where['id'] = trim($id);
        $t_data['status'] = 2;
        return M('yy_channel_tag')->where($t_where)->save($t_data);
    }

    /**
     * 查询频道标签
     * @param  string       $id         要查询的ID
     * @return array        $result
     */
    public function getTagById($id)
    {
        $where['id'] = $id;
        return M('yy_channel_tag')->where($where)->find();
    }

    /**
     * 根据标签ID查询下属频道
     * @param  string       $id         要查询的ID
     * @return array        $result
     */
    public function getCheckedChannel($id)
    {
        if(!empty($id)){
            $where['r.channel_tag_id'] = $id;
            //return M('yy_channel_tag_relation')->where($where)->select();
            $result = M('yy_channel_tag_relation')->alias('r')
                                ->join('qz_yy_channel as c on r.channel_id = c.id')
                                ->where($where)
                                ->field('r.*,c.add_time as ctime')
                                ->select();
            return $result;
        }
    }

    //获取频道标签和频道
    public function getChannelTagAndChannel()
    {
        $map = array(
            'c.status' => 1,
            't.status' => 1,
        );
        $temp = M('yy_channel_tag')->alias('t')
                                   ->field('t.id AS yy_channel_tag_id, t.name AS yy_channel_tag_name, c.id AS yy_channel_id, c.name AS yy_channel_name')
                                   ->join('qz_yy_channel_tag_relation AS r ON r.channel_tag_id = t.id')
                                   ->join('qz_yy_channel AS c ON c.id = r.channel_id')
                                   ->select();
        $tag = $channel = array();
        $tag[] = array(
            'id' => 0,
            'text' => '请选择'
        );
        $channel[] = array(
            'id' => 0,
            'text' => '请选择'
        );
        foreach ($temp as $key => $value) {
            //以频道标签为维度统计
            if (empty($tag[$value['yy_channel_tag_id']])) {
                $tag[$value['yy_channel_tag_id']] = array(
                    'id' => $value['yy_channel_tag_id'],
                    'text' => $value['yy_channel_tag_name'],
                    'relation' => array()
                );
                $tag[$value['yy_channel_tag_id']]['relation'][] = array(
                    'id' => 0,
                    'text' => '请选择'
                );
            }
            $tag[$value['yy_channel_tag_id']]['relation'][] = array(
                'id' => $value['yy_channel_id'],
                'text' => $value['yy_channel_name']
            );
            //以频道为维度统计
            if (empty($channel[$value['yy_channel_id']])) {
                $channel[$value['yy_channel_id']] = array(
                    'id' => $value['yy_channel_id'],
                    'text' => $value['yy_channel_name'],
                    'relation' => array()
                );
                $channel[$value['yy_channel_id']]['relation'][] = array(
                    'id' => 0,
                    'text' => '请选择'
                );
            }
            $channel[$value['yy_channel_id']]['relation'][] = array(
                'id' => $value['yy_channel_tag_id'],
                'text' => $value['yy_channel_tag_name']
            );
        }


        //单独获取频道和标签，补充没有关联的频道和标签
        $temp = M('yy_channel_tag')->where(array('status' => 1))->select();
        foreach ($temp as $key => $value) {
            if (empty($tag[$value['id']])) {
                $tag[$value['id']] = array(
                    'id' => $value['id'],
                    'text' => $value['name'],
                    'relation' => array(
                        array(
                            'id' => 0,
                            'text' => '请选择'
                        )
                    )
                );
            }
        }
        $temp = M('yy_channel')->where(array('status' => 1))->select();
        foreach ($temp as $key => $value) {
            if (empty($channel[$value['id']])) {
                $channel[$value['id']] = array(
                    'id' => $value['id'],
                    'text' => $value['name'],
                    'relation' => array(
                        array(
                            'id' => 0,
                            'text' => '请选择'
                        )
                    )
                );
            }
        }

        $sortTag = $tag;
        $sortChannel = $channel;
        sort($sortTag);
        sort($sortChannel);

        return array(
            'reorder'  => array('tag' => $sortTag, 'channel' => $sortChannel),
            'original' => array('tag' => $tag, 'channel' => $channel),
        );
    }


    public function getChannelByChannelTagId($channelTagId = 0)
    {
        $map = array(
            'c.status' => '1',
            't.status' => '1',
            't.id' => $channelTagId
        );
        return M('yy_channel_tag')->alias('t')
                                  ->field('c.url')
                                  ->join('qz_yy_channel_tag_relation AS r ON r.channel_tag_id = t.id')
                                  ->join('qz_yy_channel AS c ON c.id = r.channel_id')
                                  ->where($map)
                                  ->select();
    }

    public function getChannelByChannelId($channelId = 0)
    {
        $map = array(
            'status' => '1',
            'id' => $channelId
        );
        return M('yy_channel')->field('url')->where($map)->find();
    }
    /**
     * 修改频道标签
     * @param  string       $id         要修改的标签ID
     * @param  string       $name       修改的名称
     * @param  array        $channels   修改的频道
     * @param  array        $checkids   默认显示参数
     * @return array        $result
     */
    public function editTagNow($id,$name,$channels)
    {
        //先修改
        $where['id'] = trim($id);
        $data['name'] = trim($name);
        $data['edit_time'] = time();
        $result = M('yy_channel_tag')->where($where)->save($data);

        //再删除yy_channel_tag_relation表历史记录
        $d_where['channel_tag_id'] = $id;
        M('yy_channel_tag_relation')->where($d_where)->delete();
        //添加新的频道记录
        foreach ($channels as $k => $v) {
            $save['channel_tag_id'] = trim($id);
            $save['channel_id']     = trim($v);
            $save['add_time']       = time();
            $save['edit_time']      = time();
            $saveData[] = $save;
        }

        $result = M('yy_channel_tag_relation')->addAll($saveData);

        return $result;
    }


    /**
     * 查询标签下属频道
     * @param  string       $id         要查询的标签ID
     * @return array        $result
     */
    public function getAllChannels($id)
    {
        $where['c.status'] = 1;
        $where['r.channel_tag_id'] = trim($id);

        $list = M('yy_channel_tag_relation')->alias('r')
                            ->join('qz_yy_channel as c on r.channel_id = c.id')
                            ->where($where)
                            ->select();
        $count = count($list);

        //查询标签属性
        $t_where['id'] = $id;
        $tag = M('yy_channel_tag')->where($t_where)->find();

        $result['list'] = $list;
        $result['count'] = $count;
        $result['tag'] = $tag;
        return $result;
    }

    /**
     * 获取流量趋势分析列表
     * @param  integer $type    链接类型
     * @param  integer $city    城市ID
     * @param  array   $urlLike URl模糊查询
     * @param  string  $start   开始时间
     * @param  string  $end     结束时间
     */
    public function getFlowTrendList($type = 0, $city = 0, $urlLike = array(), $start = '', $end = '')
    {
        //构造子查询
        $map = array(
            'status' => 1
        );
        if (!empty($type)) {
            $map['type'] = array('IN', $type);
        }
        if (!empty($city)) {
            $map['cid'] = $city;
        }
        if (!empty($urlLike)) {
            $like = array();
            foreach ($urlLike as $key => $value) {
                $like[] = str_replace("*","%",$value);
            }
            $map['url'] =array('LIKE', $like, 'OR');
        }
        $build = M('yy_site_url')->field('id')->where($map)->buildSql();

        //查询数据
        if (empty($start) || empty($end)) {
            return false;
        }
        $where = array(
            'day' => array(
                array('EGT', $start),
                array('ELT', $end),
            ),
            '_string' => ' url_id in ' . $build
        );
        return M('yy_statistics')->field('day,order_source_src,SUM(uv) AS uv,SUM(pv) AS pv,SUM(bounce_count) AS bounce_count,SUM(exit_count) AS exit_count,SUM(visit_time) AS visit_time,SUM(visit_count) AS visit_count,SUM(entry_count) AS entry_count,SUM(order_count) AS order_count,SUM(order_fen_count) AS order_fen_count,SUM(order_real_fen_count) AS order_real_fen_count')->where($where)->group('day,order_source_src')->select();
    }

    /**
     * 查询最新频道code
     * @param  string       $id         要查询的标签ID
     * @return array        $result
     */
    public function getLastCode()
    {
        $time = date('Ymd',time());
        $where['code'] = ['like',$time.'%'];
        $result = M('yy_channel')->where($where)->order('add_time desc')->limit(1)->select();
        return $result;
    }

    /**
     * 查询最新频道标签code
     * @param  string       $id         要查询的标签ID
     * @return array        $result
     */
    public function getLastTagCode()
    {
        $time = date('Ymd',time());
        $where['code'] = ['like',$time.'%'];
        $result = M('yy_channel_tag')->where($where)->order('add_time desc')->limit(1)->select();
        return $result;
    }
}