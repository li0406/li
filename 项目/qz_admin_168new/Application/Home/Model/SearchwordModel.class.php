<?php
/**
 * 配置表
 */
namespace Home\Model;
use Think\Model;
class SearchwordModel extends Model{
    protected $autoCheckFields = false;

    /*
     * [getOptionNameCC 获取某一项的配置值 是否获取缓存可控制]
     * @param  [str] $name [单独的配置项目名称]
     * @param  [str] $CC  [缓存控制开关,1获取缓存,0不获取缓存]
     * @return [array]       [单独一项的表一行记录]
     */
    public function getSearchWords($map, $start, $end)
    {
        if(!empty($map['time'])){
            $time = explode(' - ', $map['time']);
            $where['day'] = array(
                    array('EGT',date('Y-m-d',strtotime($time[0]))),
                    array('ELT',date('Y-m-d',strtotime($time[1]))),
                    'and'
                );
        }
        if($map['jingque'] == 1){
            if(!empty($map['word'])){
                $where['word'] = str_replace(' ', '+', $map['word']);
            } 
        }else{
            if(!empty($map['word'])){
                $where['word'] = array('like','%'.str_replace(' ', '+', $map['word']).'%');
            } 
        }
        
        if(!empty($map['cid'])){
            $where['module'] = $map['cid'];
        }
        $where['type'] = 2;//目前只查PC端
        if(!empty($end)){
            $limit = $start.','.$end;
        }
        $all = M('yy_searchword')->field('sum(count) as num,word,module')->where($where)->group('word')->order('num desc')->select();
        S("Cache:KeyWordsInfo:searchKeyWords",$all,15*60);
        $count = count($all);
        $list = M('yy_searchword')->field('sum(count) as num,word,module')->where($where)->group('word')->limit($limit)->order('num desc')->select();
        foreach ($list as $k => $v) {
            $list[$k]['word'] = urldecode($v['word']);
            $module = [
                1 => '装修公司',
                2 => '家居美图',
                3 => '案例',
                4 => '问答',
                5 => '百科',
                6 => '文章',
                7 => '视频'
            ];
            $list[$k]['part'] = $module[$v['module']];
        }

        return array('count'=>$count,'list'=>$list);
    }

 
}