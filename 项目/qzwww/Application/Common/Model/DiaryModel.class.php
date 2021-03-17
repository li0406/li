<?php
/**
 * 装修公司详细信息 qz_user_company
 */
namespace Common\Model;
use Think\Model;
class DiaryModel extends Model{
    protected $tableName ="diary_info";


    //获取日记 风格 户型 头像信息 请务必做缓存
    public function getHotDiary($num){
        $result = $this->getHotDiaryUser($num,false);
        //获取风格
        $fengge = '';
        $temp = D('Meitu')->getFengge(30,true);
        foreach ($temp as $key => $value) {
            $fengge[$value['id']] = $value;
        }
        //获取户型
        $huxing = '';
        $temp = D('Meitu')->getHuxing(30,true);
        foreach ($temp as $key => $value) {
            $huxing[$value['id']] = $value;
        }

        $allHotDiary = array();
        foreach($result as $key => $v){
            unset($v['img_list']);
            $v['mianji'] = $v['mianji'].'㎡';
            $v['huxing'] = $huxing[$v['huxing']]['name'];
            $tempFengge = explode(',',$v['fengge']);
            $v['fengge'] = $fengge[$tempFengge['0']]['name'];
            $allHotDiary[] = $v;
        }
        return $allHotDiary;
    }

    /**
     * 添加新日记
     * @return [int] [添加是否成功 成功的时候返回id]
     */
    public function add_new_diary($data){
        //根据得到的first_type判断阶段 如果是
        $data['stage']=$this->get_stage_type($data['frist_type']);//取回第一级分类所属的进展阶段
        $res=M("diary_info")->add($data);
        return empty($res)?0:$res;
    }
    /**
     * 添加评论入库
     * @param array $data [评论数组]
     * @return [bool] [true or false]
     */
    public function add_diary_comment($data=array()){
        $result=M('diary_comment')->add($data);//添加评论
        return (bool)$result;//此处可能稍微费解 使用bool型强制转化将返回结果置为 true 或 false
    }

    /**
     * 获取系统推荐标签
     * @return [type] [数组列表]
     */
    public function get_recom_tags() {
        $map['type']=3;// 装修日记标签
        $map['istop']=1;//后台推荐的标签
        $list=M('tags')->field("id,name")->where($map)->select();//查询系统推荐标签
        return $list;
    }

    /**
     * 返回我的标签
     * @param  integer $id       [用户id]
     * @param  integer $diary_id [用户日记id]
     * @return [type]            [返回标签列表]
     */
    public function get_tags_type($id=0,$diary_id=0){
        #获取自己曾经使用过的标签
        $tags_list=M("diary_info")->alias('d')->field("DISTINCT t.id,t.name")->join(" qz_tags as t on d.tags=t.id ")->where(array("d.user_id"=>$id))->select();//获取我添加过的日记标签
        //由于用户可能多次使用一个标签 就过滤这个标签
        $id_list=array();
        foreach ($tags_list as $key => $value){
            if (in_array($value['id'],$id_list)){
                continue;
            }
            $id_list[]=$value['id'];
        }
        $system_tags=$this->get_recom_tags();//系统推荐标签
        foreach ($system_tags as $key => $v){
            if (in_array($v['id'],$id_list)){
                continue;
            }else{
                $tags_list[]=$v;
            }
        }
        return $tags_list;
    }

    /**
     * 从选择标签和自定义标签中返回应该入库的真实的标签
     * @param  integer $select_tags [所选择的标签id]
     * @param  string  $my_tags     [自定义标签]
     * @return [type]               [需要真实入库的标签id]
     */
    public function get_real_tags($select_tags=0,$my_tags=''){
        if ($my_tags==""){return $select_tags;}
        //自定义标签不为空 那么就去数据库查一下看看是否有这个标签了
        $map['name']=$my_tags;
        $map['type']=3;
        $res=M('tags')->field('id')->where(array('name'=>$my_tags))->find();//检查这个标签是否已经存在了
        if($res){return $res['id'];}//如果有标签则返回这个标签的id
        //此时说明没有标签 则我们要新新增一个标签
        $data['name']=$my_tags;//用户自定义的标签名称
        $data['type']=3;//用户自定义的标签类别
        $data['px']=0;//用户的默认排序为0
        $data['isTop']=0;//用户的默认不推荐
        $data['time']=time();//获取添加时间
        $data['uid']=0;//用户id设置为0
        $data['uname']="齐装网用户";//由齐装网用户添加
        $last_id=M('tags')->add($data);//插入这个标签
        return $last_id;//返回这个id
    }

    /**
     * 装修日记图集入库
     * @param [type] $diary_id [日记id]
     * @param [type] $img_list [图片数组]
     */
    public function add_diary_img($diary_id,$img_list){
        foreach ($img_list as $k => $v){
            $img_data['diary_id']=$diary_id;//记录日记id
            $img_data['img_path']=$v;//存储图片路径
            $img_data['img_host']="qiniu";//新版默认是上传到七牛的图
            $img_data['stat']=1;//状态为1
            $res+=(bool)(M('diary_img')->add($img_data));
            //此处可能较为费解 用bool值转换返回的insert_id 这样就可以得到1或0 参与累加
        }
        //查询第一张图 然后把其他图设置为非封面图 第一张图设置为封面图 同时同步diary_info里面的logo
        $map['diary_id']=$diary_id;
        $map['stat']=1;//只查还能使用未被删除的图片
        $img_all_list=M('diary_img')->where($map)->select();
        $num=1;
        foreach ($img_all_list as $key => $value)
        {
            $img_on_data['img_on']=0;//默认都不是封面图
            if($num==1)
            {
                //第一张图要作为日记的封面图
                M('diary_info')->where(array('id'=>$diary_id))->save(array('img_logo'=>$value['img_path']));
                $img_on_data['img_on']=1;//第一张图是封面图
            }
            M('diary_img')->where(array('id'=>$value['id']))->save($img_on_data);
            $num++;
        }
        if ($res==count($img_list))
        {
            return true;
        }
        return false;
    }

    /**
     * 获取我的日记列表
     * @param  [type] $id [用户id]
     * @return [type]     [array('list'=>$list,'pageTmp'=>$pageTmp)]
     */
    public function get_my_diary_list($id){

        #获取我的所有装修日记
        $pageIndex = I("get.p") === ""?1:I("get.p");//如果没有传页码 就是第一页
        $pageCount = 10;//每页分10个
        $map['user_id']=$id;//查询我的装修日记
        $map['parent_id']=0;//查询首日记
        $map['stat'] = array('EQ','1');//查询我日记的状态
        $count=M("diary_info")->where($map)->count();
        if($count)
        {
            import('Library.Org.Page.Page');//导入分页类
            //自定义配置项
            $config  = array("prev","next");
            $page = new \Page($pageIndex,$pageCount,$count,$config);//实例化分页类
            $pageTmp =  $page->show();//得到分页
            $start=($page->pageIndex-1)*$pageCount;//起始页
            $buildSql=M("diary_info")->where($map)->limit($start.",".$pageCount)->order("id desc")->buildSql();//查询列表

            $buildSql = M("diary_info")->table($buildSql)->alias("t")
                                   ->join("LEFT JOIN qz_diary_info as i on i.parent_id = t.id ")
                                   ->order("cid desc")
                                   ->field("t.*,i.id as cid,i.title as childtitle")
                                   ->buildSql();

             $buildSql = M("diary_info")->table($buildSql)->alias("t1")
                                   ->group("t1.id")
                                   ->order("t1.id desc")
                                   ->field("t1.*,count(t1.cid) as childcount ")
                                   ->buildSql();

             $list = M("diary_info")->table($buildSql)->alias("t2")
                                    ->join("INNER JOIN qz_meitu_fengge as f on find_in_set(f.id,t2.fengge)")
                                    ->join("INNER JOIN qz_meitu_huxing as h on h.id = t2.huxing")
                                    ->join("left join qz_ucenter_profile as u on u.uuid = t2.user_id")
                                    ->join("left join qz_quyu as q on q.cid = u.city")
                                    ->field("t2.*,group_concat(f.name) as fg,h.name as hx,q.bm")
                                    ->group("t2.id")
                                    ->order("t2.diary_time desc")
                                    ->select();

            foreach ($list as $key => $value){
                // #遍历获取每篇日记下面所拥有子日记的进展阶段
                // $son_stage = M('diary_info')->field("group_concat(distinct stage) as stage")->where(array("parent_id"=>$value['id'],"stat"=>1))->find();
                // $list[$key]['son_stage']=array_filter(explode(',',$value['stage'].",".$son_stage['stage']));
                //获取当前日记的最新的子日记的信息

                if($value["childcount"] > 0){
                    $subMap = array(
                        "parent_id"=>array("EQ",$value["id"])
                                );
                }else{
                    $subMap = array(
                        "id"=>array("EQ",$value["id"])
                                );
                }

                $subSql =  M("diary_info")->where($subMap)->order("id desc")->buildSql();
                $subSql =  M("diary_info")->table($subSql)->alias("t")->group("parent_id")
                                          ->field("t.id,t.add_time,t.title ,t.content,t.first_type,t.second_type,t.diary_time")
                                          ->buildSql();
                $subSql =  M("diary_info")->table($subSql)->alias("t1")
                                         ->join("INNER JOIN qz_diary_type as b on b.id = t1.first_type")
                                         ->join("INNER JOIN qz_diary_type as c on c.id = t1.second_type")
                                         ->field("t1.*, b.type_name as first_name,c.type_name as second_name")
                                         ->buildSql();
                $sub =  M("diary_info")->table($subSql)->alias("t2")
                                          ->join("LEFT JOIN qz_diary_img as i on i.diary_id = t2.id")
                                          ->order("img_on desc")
                                          ->field("t2.*,i.img_host,i.img_path")
                                          ->select();
                //如果存在子日记，则添加到当前日记内
                foreach ($sub as $k => $val) {
                    if($k == 0){
                        $list[$key]["child"] =  $val;
                    }
                    $list[$key]["child"]["imgs"][] = array(
                                                "img_host"=>$val["img_host"],
                                                "img_path"=>$val["img_path"],
                                                         );
                }
            }
        }
        return compact("list","pageTmp");
    }

    /**
     * 获取某个用户的日记数量
     * @param  integer $user_id [用户id]
     * @return [type]           [日记数量]
     */
    public function get_user_diary_count($user_id=0){
        $map['user_id']=$user_id;//属于该人的日记
        $map['stat']=1;//日记状态还在使用中
        $diary_count=M('diary_info')->where($map)->count();//获取该人日记数量
        return $diary_count+0;
    }

    /**
     * 获取进展阶段
     * @param  integer $frist_type [所选择的二级分类的id]
     * @return [type]              [返回所处的阶段id]
     */
    public function get_stage_type($frist_type=1){
        $type = array(1=>1,2=>2,3=>2,4=>2,5=>2,6=>2,7=>2,8=>3,);
        return !empty($type[$frist_type]) ? $type[$frist_type] : 1;
    }

    /**
     * 查询单个日记信息
     * @param  integer $diary_id     [日记id]
     * @param  boolean $follow_diary [是否查询后续日记]
     * @return [array]                [list]
     */
    public function get_one_diary_info($diary_id=0,$follow_diary=false){
        $map = array(
            'a.stat'=>1
        );
        //根据id获取日记信息
        if ($follow_diary){
            //需要查询所有的相关日记
            $map["_complex"] = array(
                            "a.id"=>array("EQ",$diary_id),
                            "a.parent_id"=>array("EQ",$diary_id),
                            "_logic"=>"OR"
                                     );
        }else{
            $map["a.id"]= array("EQ",$diary_id);
        }
          //查询到本篇日记
        $info =  M('diary_info')->where($map)->alias("a")
                 ->join("INNER JOIN qz_meitu_fengge as b on b.id = a.fengge")
                 ->join("INNER JOIN qz_meitu_huxing as c on c.id = a.huxing")
                 ->join("LEFT JOIN qz_diary_type as d on d.id  = a.second_type")
                 ->join("LEFT JOIN qz_user as u on u.id = a.company_id")
                 ->join("LEFT JOIN qz_quyu as q on q.cid = u.cs")
                 ->field("a.*,b.name as fg,c.name as hx,d.type_name as second_type_name,q.bm")
                 ->order("a.parent_id , a.diary_time desc")->select();
        return $info;
    }

    /**
     * 获取后续日记
     * @param  integer $id [日记id]
     * @return [array]     [list]
     */
    public function get_follow_diary($id=0){
        //据此再查找日记的续写日记的列表
        $map['a.parent_id']=$id+0;//该日记的后续日记
        $map['a.stat']=1;//还没被删除的日记
        $follow_diary=M('diary_info')->where($map)->alias("a")
                                     ->join("INNER JOIN qz_diary_type as d on d.id  = a.second_type")
                                     ->field("a.*,d.type_name as second_type_name")
                                     ->order('diary_time desc')->select();//查询我的这篇日记的后续日记
        return $follow_diary;//返回我的后续日记的数组列表
    }

    /**
     * 获取某篇日记的图集
     * @param  int $id [日记di]
     * @return [array] [图集数组]
     */
    public function get_one_diary_img($id=0,$json=false){
        $map['stat']=1;
        $map['diary_id']=$id;
        if (!$json){
            return M('diary_img')->where($map)->select();//如果是想取列表  就给列表
        }
        //如果是想取json的就给json编码的
        $img_list=M('diary_img')->field("id,img_path,img_host, img_on ")->where($map)->select();//取日记的图片
        foreach ($img_list as $key => $value){
            //如果是七牛的图片 就把 //staticqn.qizuang.com/ 改为空字符串 统一保证无staticqn.qizuang.com域名
            if ($value['img_host']=="qiniu")
            {
                $img_list[$key]['img_path']=str_replace("//".C("QINIU_DOMAIN")."/","",$value['img_path']);
            }else
            {
                $img_list[$key]['img_path']="upload/company/m_".$value['img_path'];
            }
        }
        return json_encode($img_list);
    }

    /**
     * 删除日记图片
     * @param  [type] $img_id  [图片id]
     * @param  [type] $user_id [用户id]
     * @return [type]          [bool]
     */
    public function del_diary_img($img_id,$user_id){
        #通过id删除图片 但是要检测该用户是否是图片的主人
        $info=M('diary_img')->find($img_id);//查询该图片是否存在
        if ($info){
            //图片确实存在 那么查询图片主人
            $map['user_id']=$user_id;
            $map['id']=$info['diary_id'];
            $count=M("diary_info")->where($map)->count();//是否属于该人
            if ($count>0){
                $res=M('diary_img')->where(array('id'=>$img_id))->save(array('stat'=>0));
                return !empty($res)?true:false;
            }
        }
        return false;
    }
    /**
     * 检查是否是最后一张图
     * @param  [int]  $img_id [图片id]
     * @return [type]         [bool]
     */
    public function check_is_last_img($img_id){
        $info=M('diary_img')->find($id);//查询该图片是否存在
        if ($info){
            $map['diary_id']=$info['diary_id'];//查询日记id
            $map['stat']=1;
            $img_count=M('diary_img')->where($map)->count()+0;
            if($img_count<=1)
            {
                //这已经是最后一张图了 不能删除
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * 获取日记分类
     * @return [type] [返回日记分类]
     */
    public function get_diary_type(){
        $diary_type=S('Cache:Diary:Type');//取缓存中的分类
        if (empty($diary_type)){
            $diary_type=M('diary_type')->where(array('stat'=>1))->select();//查询出所有分类
            S('Cache:Diary:Type',$diary_type,3600*24*30);//这个分类的属性很少变 我们定义成一个月
        }
        foreach ($diary_type as $key => $v){
            if ($v['level']==1 && $v['parent_id']==0){
                $list[]=$v;//当取到第一级的时候把它塞入list数组
                unset($diary_type[$key]);//第一级分类去除 减少后面的无谓的遍历
            }
        }
        foreach ($list as $k=>$v){
            foreach ($diary_type as $value){
                if($value['parent_id']==$v['id']){
                    $list[$k]['child'][]=$value;//如果当前的第二级分类已经找到 就赋值给外层数组
                }
            }
        }
        return $list;
    }

    /**
     * 通过日记类别id获取日记类别名称
     * @param  integer $id [类别id]
     * @return [type]      [类别名称]
     */
    public function get_diary_type_name_by_id($id=0){
        $res=M('diary_type')->find($id);
        if($res){
            return $res['type_name'];
        }
        return "";
    }

    /**
     * 获取装修达人列表
     * @param  [int]  $limit [数量]
     * @return [array] [list]
     */
    public function get_decoration_master($limit=5){
        #获取装修达人
        $map['a.parent_id'] = 0;//不是后续日记
        $map['a.stat'] = 1;//仍然在使用的日记
        $buildSql = M('diary_info')->where($map)->alias("a")
                                   ->join("INNER JOIN qz_diary_info as b on b.parent_id = a.id")
                                   ->field("count(b.parent_id) as diary_count,a.user_id")
                                   ->group("b.parent_id")->having('diary_count > 10')
                                   ->buildSql();
        $list = M('diary_info')->table($buildSql)->limit($limit)->alias("t")->order("diary_count desc")->select();

        foreach ($list as $key => $v)
        {
//            $user_info=M('user')->field('logo,user as name')->find($v['user_id']);
            $user_info=M('ucenter_profile')->field('avatar as logo,nickname as name')->where(array("uuid"=>$v['user_id']))->find();
            $list[$key]['user_info']=$user_info;
        }
        return $list;
    }

    /**
     * 获取热门装修日记
     * @param  integer $limit [显示数量]
     * @return [array]         [list]
     */
    public function get_hot_diary_list($limit=5){
        $map['a.parent_id'] = 0;//不是后续日记
        $map['a.stat'] = 1;//仍然在使用的日记
        $map['a.istop'] = 1;
        $buildSql = M('diary_info')->where($map)->alias("a")
                                   ->join("INNER JOIN qz_diary_info as b on b.parent_id = a.id")
                                   ->order("b.page_view desc")
                                   ->field("b.*")
                                   ->buildSql();
        $list = M('diary_info')->table($buildSql)->alias("t")->group("t.parent_id")->limit($limit)->select();

        //查询出来列表后 去图集表遍历取图片
        foreach ($list as $key => $v){
            $list[$key]['img_list']=$this->get_diary_img_by_id($v['id']);
        }
        return $list;
    }

    /**
     * 通过id获取日记图集
     * @param  [type] $diary_id [日记id]
     * @return [type]           [图集列表]
     */
    public function get_diary_img_by_id($diary_id,$limit = ''){
        $map['diary_id']=$diary_id;//关联这篇日记id
        $mapp['stat']=1;//图片仍然在使用状态
        $list=M('diary_img')->where($map)->limit($limit)->select();
        return $list;
    }

    /**
     * 查询文章列表
     * @return [array] [list]
     */
    public function get_all_diary_list($limit = '',$cid =''){
        #获取我的所有装修日记
        $p = intval(I("get.p"));//接收页码
        $pageIndex = $p>=1?$p:1;//如果没有传页码 就是第一页
        $pageCount = 10;//每页分10个
        $map['t.parent_id'] = 0;//查询首日记
        $map['t.stat'] = 1;//查询我日记的状态
        //$map['istop'] = 1;

        $list = I("get.list");
        $reg = '/[a-z]\d+/';
        preg_match_all($reg, $list, $m);
        if(count($m[0]) > 0){
            foreach ($m[0] as $key => $value) {
                $m = preg_split('/[0-9]/', $value);
                $key = trim($m[0]);
                $v =  preg_split('/[a-z]/', $value);
                $params[$key] = $v[1];
            }
        }

        foreach ($params as $key => $value) {
            $value = remove_xss($value);
            if($key == "f" && $value != 0){
                $map["_complex"] = array(
                                    "find_in_set($value,t.fengge)"
                                          );
               continue;
            }

            if($key == "h"&& $value != 0){
                $map["t.huxing"] = array("EQ",$value);
                continue;
            }

            if($key == "m" && $value != 0){
                switch ($value) {
                    case "1":
                        $map["t.mianji"] = array("LT",60);
                        break;
                    case "2":
                        $map["t.mianji"] = array("BETWEEN",'60,80');
                        break;
                    case "3":
                        $map["t.mianji"] = array("BETWEEN",'81,100');
                        break;
                    case "4":
                        $map["t.mianji"] = array("BETWEEN",'101,120');
                        break;
                    case "5":
                       $map["t.mianji"] = array("BETWEEN",'121,150');
                        break;
                    case "6":
                       $map["t.mianji"] = array("GT",'150');
                        break;
                }
                 continue;
            }

            if($key == "s" && $value != 0){
                $map["b.second_type"] = array("EQ",$value);
                unset($map['t.parent_id']);
                continue;
            }
        }
        $csql = M("diary_info")->alias("t")
                  ->field('t.id')
                  ->join('INNER JOIN qz_diary_info as b ON b.parent_id = t.id AND b.stat = 1')
                  ->where($map)
                  ->group('t.user_id')
                  ->buildSql();
        $count = M("diary_info")->alias("x")
                                ->table($csql)
                                ->count();
        if($count)
        {
            import('Library.Org.Page.Page');//导入分页类
            //自定义配置项
            $config  = array("prev","next");
            $page = new \Page($pageIndex,$pageCount,$count,$config);//实例化分页类
            $pageTmp =  $page->show();//得到分页
            $start=($page->pageIndex-1)*$pageCount;//起始页

            $buildSql = M("diary_info")->alias("t")
                        ->field('t.id,t.user_id,t.title,t.diary_count,t.mianji,t.fengge,t.huxing,t.first_type,t.page_view,t.concern_count,b.id as subid,b.title as subtitle,b.content,b.second_type,b.diary_time,b.stage')
                        ->join('INNER JOIN qz_diary_info as b on b.parent_id = t.id and b.stat = 1 ')
                        ->where($map)
                        ->order("t.diary_time desc")
                        ->buildSql();

            $buildSql = M("diary_info")
                            ->table($buildSql)
                            ->alias("t")
                            ->order("diary_time desc")
                            ->buildSql();

            $list = M("diary_info")
                        ->table($buildSql)
                        ->alias("t")
                        ->group('t.user_id')
                        ->order("diary_time desc")
                        ->limit($start.",".$pageCount)
                        ->select();

            import('Library.Org.Util.Fiftercontact');
            $filter = new \Fiftercontact();

            foreach ($list as $key => $v){
                $user_info=M('ucenter_profile')->field('avatar as logo,nickname as name')->where(array("uuid"=>$v['user_id']))->find();//获取用户信息
                if(empty($user_info)){
                    $user_info=M('user')->field('logo,`name`')->where(array("id"=>$v['user_id']))->find();//获取用户信息
                }
                $list[$key]['user_info']=$user_info;//将用户信息放入数组
                $list[$key]['img_list']=$this->get_diary_img_by_id($v['subid'],$limit);//获取图片列表
                $list[$key]['fengge']=array_filter(explode(',',$v['fengge']));
                $list[$key]['content'] = $filter->filter_common($list[$key]['content'],array("filter_script",array("filter_sensitive_words",array(2,3,5)),"filter_link","filter_url"));
            }
        }
        return compact("list","pageTmp");
    }

    /**
     * 手机端查询文章列表
     * @return [array] [list]
     */
    public function get_all_diary_list_api($limit = '',$cid =''){
        #获取我的所有装修日记
        $p = intval(I("get.p"));//接收页码
        $pageIndex = $p>=1?$p:1;//如果没有传页码 就是第一页
        $pageCount = 10;//每页分10个
        //$map['parent_id']=0;//查询首日记
        $map['stat'] = 1;//查询我日记的状态
        $map['parent_id'] = 0;
        $map['istop'] = 1;
        $list = I("get.list");
        $reg = '/[a-z]\d+/';
        preg_match_all($reg, $list, $m);
        if(count($m[0]) > 0){
            foreach ($m[0] as $key => $value) {
                $m = preg_split('/[0-9]/', $value);
                $key = trim($m[0]);
                $v =  preg_split('/[a-z]/', $value);
                $params[$key] = $v[1];
            }
        }
        foreach ($params as $key => $value) {
            $value = remove_xss($value);
            if($key == "f" && $value != 0){
                $map["_complex"] = array(
                                    "find_in_set($value,fengge)"
                                          );
               continue;
            }
            if($key == "h"&& $value != 0){
                $map["huxing"] = array("EQ",$value);
                continue;
            }
            if($key == "m" && $value != 0){
                switch ($value) {
                    case "1":
                        $map["mianji"] = array("LT",60);
                        break;
                    case "2":
                        $map["mianji"] = array("BETWEEN",'60,80');
                        break;
                    case "3":
                        $map["mianji"] = array("BETWEEN",'81,100');
                        break;
                    case "4":
                        $map["mianji"] = array("BETWEEN",'101,120');
                        break;
                    case "5":
                       $map["mianji"] = array("BETWEEN",'121,150');
                        break;
                    case "6":
                       $map["mianji"] = array("GT",'150');
                        break;
                }
                 continue;
            }
            if($key == "s" && $value != 0){
                $map["second_type"] = array("EQ",$value);
                continue;
            }
        }
        if(!empty($cid)){
            $category = "AND b.first_type = '$cid' ";
            //$map["first_type"] = array("EQ",$cid);
        }
        $buildSql = M("diary_info")->where($map)->group("user_id")->buildSql();//获取总数量
        $count =  M("diary_info")->table($buildSql)->alias("t")->count();
        if($count)
        {
            import('Library.Org.Page.Page');//导入分页类
            //自定义配置项
            $config  = array("prev","next");
            $page = new \Page($pageIndex,$pageCount,$count,$config);//实例化分页类
            $pageTmp =  $page->show();//得到分页
            $start=($page->pageIndex-1)*$pageCount;//起始页
            $buildSql = M("diary_info")->where($map)->order("id desc")->buildSql();//查询列表
            $buildSql = M("diary_info")->table($buildSql)->alias("t")
                                       ->join("INNER JOIN qz_diary_info as b on b.parent_id = t.id and b.stat = 1 $category")
                                       ->field("b.*,t.diary_count as pcount")
                                       ->limit($start.",".$pageCount)
                                       ->order("b.diary_time desc")
                                       ->buildSql();
            $list =  M("diary_info")->table($buildSql)->group("t1.parent_id")->alias("t1")->order("t1.parent_id desc")->select();
            //dump(M()->getLastSql());
            import('Library.Org.Util.Fiftercontact');
            $filter = new \Fiftercontact();
            foreach ($list as $key => $v)
            {
                $user_info=M('user')->field('logo,user as name')->find($v['user_id']);//获取用户信息
                $list[$key]['user_info']=$user_info;//将用户信息放入数组
                $list[$key]['img_list']=$this->get_diary_img_by_id($v['id'],$limit);//获取图片列表
                $list[$key]['fengge']=array_filter(explode(',',$v['fengge']));
                $list[$key]['content'] = htmlspecialchars(strip_tags($list[$key]['content']));
            }
        }
        return compact("list","pageTmp");
    }

    /**
     *  获取某篇日记的评论
     * @param  integer $diary_id [日记di]
     * @return [array]           [list]
     */
    public function get_one_diary_comment($diary_id=0){
        $map['diary_id'] = $diary_id;//关联这篇日记id
        $map['parent_id'] = array("EQ",0);
        $map['stat']=1;//评论仍然在使用状态
        $buildSql = M('diary_comment')->where($map)->buildSql();
        $list=M('diary_comment')->table($buildSql)->alias("a")
                                ->join("LEFT JOIN qz_diary_comment as b on b.parent_id = a.id and b.stat = 1")
                                ->field("a.*,b.logo as ulogo,b.user_id as uid,b.user_name as uname,b.content as ucontent,b.parent_id as uparent,b.add_time as utime,b.author_name as uauthor_name,b.author_id as uauthor_id")
                                ->select();//获取日记评论列表

        foreach ($list as $k => $v)
        {

            if(!array_key_exists($v["id"], $new_list) && $v["parent_id"] == 0)
            {
                $new_list[$v['id']]=$v;//如果是第一级评论 直接塞入数组
            }

            if(!empty($v["uid"])){
                $new_list[$v['uparent']]['child'][]=$v;//如果是第二级评论 则塞入子元素数组
            }

            unset($new_list[$k]);
        }
        return $new_list;
    }

    /**
     * 获取某篇日记的评论数目
     * @param  integer $diary_id [日记id]
     * @return [int]             [评论数目]
     */
    public function get_one_diary_comment_count($diary_id=0){
        $map['diary_id']=$diary_id;//关联这篇日记id
        $map['stat']=1;//评论仍然在使用状态
        $count=M('diary_comment')->where($map)->count();//获取日记评论列表
        return $count+0;//强制转整型
    }

    /**
     * 修改日记的浏览量/关注量/回复量
     * @param  integer $diary_id [日记id]
     * @return [type]            [说明]
     */
    public function diary_page_view_add($diary_id=0,$colum = "page_view"){
        $map['id']=$diary_id;//关联这篇日记id
        $map['stat']=1;//评论仍然在使用状态

        M('diary_info')->where($map)->setInc($colum);//获取日记评论列表
    }


    /**
     * 获取其他日记的数量
     * @param  [type] $id       [用户编号]
     * @param  [type] $diary_id [当前日记编号]
     * @return [type]           [description]
     */
    public function getOtherDiaryCount($id,$diary_id){
        $map = array(
                "user_id"=>array("EQ",$id),
                "id"=>array("NOT IN",$diary_id),
                "parent_id"=>array("EQ",0),
                "stat"=>array("EQ",1)
        );
        return M('diary_info')->where($map)->count();
    }

    /**
     * 查询主题日记信息
     * @param  [type] $id [日记编号]
     * @return [type]     [description]
     */
    public function getDiaryTheme($id){
         $map = array(
                "id"=>array("EQ",$id),
                "parent_id"=>array("EQ",0)
                     );
        return M('diary_info')->where($map)->find();
    }

     /**
     * 查询主题日记信息
     * @param  [type] $id [日记编号]
     * @return [type]     [description]
     */
    public function editDiary($id,$data){
         $map = array(
                "id"=>array("EQ",$id)
                     );
        return M('diary_info')->where($map)->save($data);
    }


    /**
     * 获取热门装修日记用户列表
     * @param  integer $limit [显示数量]
     * @return [array]         [list]
     */
    public function getHotDiaryUser($limit=5,$week = true,$istop = true){
        $map['a.parent_id'] = 0;//不是后续日记
        $map['a.stat'] = 1;//仍然在使用的日记
        if($istop == true){
            $map['a.istop'] = 1;
        }
        if($week == true){
            //最近一周
            $time = strtotime(date('Y-m-d',time()))  - 86400 * 7 ;
            $map['a.diary_time'] = array('EGT',$time);
        }
        $list = M('diary_info')->where($map)->alias("a")
                                   ->join("INNER JOIN qz_user as u on a.user_id = u.id")
                                   ->order("a.page_view desc")
                                   ->limit('0,'.$limit)
                                   ->field("a.id,a.user_id,a.title,a.add_time,a.mianji,a.fengge,a.huxing,u.name,u.logo")
                                   ->select();
        //dump(M()->getLastSql());
        return $list;
    }


    //随机获取日记
    public function getRandDiary($id,$limit){
        $map = array(
            "id"=>array("NOT IN",$id),
            "parent_id"=>array("EQ",0),
            "stat"=>array("EQ",1)
        );
        $result = M('diary_info')->where($map)->order("rand() ")->limit('0,'.$limit)->select();
        foreach ($result as $k => $v) {
            $submap['diary_id'] = $v['id'];
            $submap['stat'] = '1';
            $s = M('diary_img')->where($submap)->order("img_on desc")->limit('0,3')->select();
            if(empty($s)){
                $result[$k]['child'] = $v['img_logo'];
            }else{
                $result[$k]['child'] = $s;
            }
        }
        return $result;
    }

    /**
     * 获取与当前日记相关联的日记信息
     * @param  [type] $parent_id [主题日记编号]
     * @param  [type] $id        [当前日记编号]
     * @param  [type] $limit     [获取数量]
     * @return [type]            [description]
     */
    public function getOtherDiary($parent_id,$id,$limit){
        //前几篇日记
        $prevMap = array(
                "id"=>array("LT",$id),
                "parent_id" =>array("EQ",$parent_id),
                "stat"=>array("EQ",1)
                     );
        //后几篇日记
        $nextMap = array(
                "id"=>array("GT",$id),
                "parent_id" =>array("EQ",$parent_id),
                "stat"=>array("EQ",1)
                     );
        $prevSql = M('diary_info')->where($prevMap)->order("id desc")->limit($limit)->buildSql();
        $nextSql = M('diary_info')->where($nextMap)->limit($limit)->buildSql();
        //取出相关日记信息
        $buildSql = M('diary_info')->table($prevSql)->alias("t")
                              ->union($nextSql,true)
                              ->buildSql();
        //取出相关日记的图片信息
        $buildSql = M('diary_info')->table($buildSql)->alias("t1")
                                   ->join("LEFT JOIN qz_diary_img as i on i.diary_id = t1.id and i.stat = 1")
                                   ->field("t1.*,i.img_path,img_host")
                                   ->order("img_on desc")
                                   ->buildSql();
        return M('diary_info')->table($buildSql)->alias("t2")
                                                ->select();
    }

    //随机获取日记
    public function getMRandDiary($id,$limit){
        $map = array(
            "id"=>array("NOT IN",$id),
            "parent_id"=>array("NEQ",0),
            "stat"=>array("EQ",1)
        );
        $result = M('diary_info')->where($map)->order("rand() ")->limit('0,'.$limit)->select();
        foreach ($result as $k => $v) {
            $submap['diary_id'] = $v['id'];
            $submap['stat'] = '1';
            $s = M('diary_img')->where($submap)->order("img_on desc")->limit('0,3')->select();
            if(empty($s)){
                $result[$k]['child'] = $v['img_logo'];
            }else{
                $result[$k]['child'] = $s;
            }
        }
        return $result;
    }

    /**
     * 获取最新的评论信息
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public function getLastPostComment($uid)
    {
        $map = array(
            "user_id"=>array("EQ",$uid)
        );
        return  M('diary_comment')->where($map)->field("add_time")->order("id desc")->find();
    }

    /**
     * 获取最新的浏览量最高的日记
     * @param  [type] $limit [description]
     * @return [type]        [description]
     */
    public function getTopPVDirayList($limit)
    {
        $map = array(
            "a.stat" => array("EQ",1),
            "a.parent_id" => array("NEQ",0),
            "a.istop" => array("EQ",1)
        );

        return  M('diary_info')->where($map)->alias("a")
                               ->order("page_view desc")
                               ->limit($limit)->field("a.id,a.title,stage,img_logo,page_view,content")
                               ->select();

    }

    /**
     * 获取最新的日记
     * @param  [type] $limit [description]
     * @return [type]        [description]
     */
    public function getNewList($limit)
    {
        $map = array(
            "a.stat" => array("EQ",1),
            "a.review" => array("EQ",1),
            "a.parent_id" => array("NEQ",0)
        );
        return  M("diary_info a")
            ->join("INNER JOIN qz_meitu_fengge as f on find_in_set(f.id,a.fengge)")
            ->join("INNER JOIN qz_meitu_huxing as h on h.id = a.huxing")
            ->field('a.id,a.title,a.add_time,xiaoqu,CONCAT_WS("",xiaoqu,mianji,"㎡",h.name,group_concat(f.name separator "")) as xf')
            ->where($map)
            ->group("a.id")
            ->order('add_time DESC')
            ->limit($limit)
            ->select();

    }
}