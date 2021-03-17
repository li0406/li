<?php
/**
 *  问答表 qz_ask
 */
namespace Common\Model;
use Think\Model;

class AskModel extends Model{
    protected $autoCheckFields = false; //设置autoCheckFields属性为false后，就会关闭字段信息的自动检测，因为ThinkPHP采用的是惰性数据库连接，只要你不进行数据库查询操作，是不会连接数据库的。

    // array(验证字段,验证规则,错误提示[,验证条件][,附加规则][,验证时间])
    protected $_validate = array(
        array('sub_category','require','请选择分类！',1,""),
        //array('content','0,1500','内容最多输入1500个字',1,'length'),
        array('title',"5,50",'标题请输入6-50个字',1,'length'),
        array('city','require','请选择城市',1,''),
        array('area','require','请选择所在区域',1,''),
    );

    //查询最新问题列表
    public function getNewQuestion($num){
        $map['visible'] ='0';
        $build = M('Ask')->field('id,uid,uid old_userid,sub_category,title,post_time,views,anwsers,status')->where($map)->order('post_time DESC')->limit("0,".$num)->buildSql();
        return M()->table($build)->alias('a')
                                 ->field('a.*, c.name AS sub_category_name, u.nickname AS user_name,u.nickname AS username, u.avatar logo , u.avatar AS user_logo')
                                 ->join('qz_ask_category c ON c.cid = a.sub_category')
                                 ->join('left join qz_ucenter_profile u ON u.uuid = a.uid')
                                 ->select();
    }

    //查询本周最新问题列表
    public function getHotAskByWeek($num){
        //修改为本周热门问题
        $time = strtotime(date('Y-m-d',time()))  - 86400 * 15 ;
        $map = array(
            "visible"=>'0',
            "post_time" => array('EGT',$time)
        );
        $s = M('Ask')->field('id,title,post_time,views,anwsers')->where($map)->order('views DESC')->limit("0,".$num)->select();
        return $s;
    }

    //查询最近回答列表 根据公司ID
    public function getNewAnwserByCompany($companyId,$limit=6){
        $map  = array(
           "uid" => array("EQ",$companyId)
        );
        return  M('ask')->field('id,title,post_time')
                        ->order('last_time DESC')
                        ->where($map)
                        ->limit("0,".$limit)
                        ->select();
    }

    /**
     * 查询问题、用户、种类列表  根据条件condition
     *
     * @param array $condition 条件
     * @param array $orderBy 排序
     * @param int $row 多少条
     * @return mixed
     */
    public function getQuestionAndUserList($condition = [], $orderBy = ['post_time' => 'DESC'], $limit = 10,$field = 'a.*, c.name AS sub_category_name, u.name AS user_name, u.logo AS user_logo')
    {
        //如果条件不为空
        return M('Ask')
            ->alias('a')
            ->field($field)
            ->where($condition)
            ->join('left join qz_ask_anwser as b on a.best_aid = b.id and a.id = b.qid')
            ->join('qz_ask_category c ON c.cid = a.sub_category')
            ->join('qz_user u ON u.id = a.uid')
            ->order($orderBy)
            ->limit($limit)
            ->select();
    }


    //查询问题列表  根据条件condition
    public function getQuestionList($condition,$orderBy,$start= 1,$row = 10){
        $map = array(
            "visible"         =>  array("NEQ",'1')
        );
        //如果条件不为空
        if(!empty($condition)){
            $map = array_merge($map,$condition);
        }
        $Ask = M('ask');
        $result = $Ask->field('id,cid,sub_category,title,post_time,views,status,anwsers')
                      ->order($orderBy)
                      ->limit($start.",".$row)
                      ->where($map)
                      ->select();
        return $result;
    }

    //查询问答列表  根据条件condition
    public function getQListByCategory($condition,$pagesize= 1,$pageRow = 10){
        $map = array(
            "a.visible"         =>  array("EQ",'0')
        );
        //如果 cid 不为空
        if(!empty($condition['cateId'])){
          $cid = $condition['cateId'];
          //Note: 为了减少逻辑，对于一级分类ID直接判断
          $categoryColumn = $cid <= 6 ? 'a.cid' : 'a.sub_category';
          $category = array(
                    $categoryColumn     =>  array("EQ",$cid)
          );
          $map = array_merge($map,$category);
        }
        //如果有状态查询
        if(isset($condition['status'])){
            $status = array(
                    "a.status"         =>  array("EQ",$condition['status']),
            );
            $map = array_merge($map,$status);
        }
        //如果有条件
        if(isset($condition['anwsers'])){
            $where = array(
                    "a.anwsers"         =>  $condition['anwsers'],
            );
            $map = array_merge($map,$where);
        }
        //如果有用户限制
        if(isset($condition['uid'])){
            $where = array(
                    "a.uid"         =>  $condition['uid'],
            );
            $map = array_merge($map,$where);
        }
        //如果关键词不为空
        if(isset($condition['keyword'])){
            $map['a.title']  = array('like','%'.$condition['keyword'].'%');
        }
        if(isset($condition['dist'])){
            $map['a.is_distillate']  = $condition['dist'];
        }
        //de($map);
        $Ask = M('ask');
        $count = $Ask->alias("a")
                  ->where($map)
                  ->count();
        $result = $Ask->alias("a")
                      ->field('a.id,a.sub_category,a.title,a.description,a.post_time,a.last_time,a.anwsers,a.views,a.status,c.cid,c.name')
                      ->join("inner join qz_ask_category as c on a.sub_category = c.cid")
                      ->order($condition['orderBy'])
                      ->limit($pagesize.",".$pageRow)
                      ->where($map)
                      ->select();
        return array("result"=>$result,"count"=>$count);
    }

    //查询答案列表（包含用户信息） 根据问答ID
    public function getAnwserList($qid,$orderBy,$bestAid=''){
        $map = array(
            "a.visible"=>array("EQ",'0'),
            "a.qid"=>array("EQ",$qid),
            "a.content" => array('NEQ', "")
        );
        if(!empty($bestAid)){
           $map['a.id'] =  array("NOTIN",$bestAid);
        }
        return M('ask_anwser')->alias("a")
                      ->field('a.id,a.content,a.post_time,a.comments,a.agree,a.uid old_userid,u.nickname username,u.avatar logo')
//                      ->join("left join qz_user as u on a.uid = u.id")
                    ->join("left join qz_ucenter_profile as u on a.uid = u.uuid")    // todo 用inner join
                      ->order($orderBy)
                      //->limit($pagesize.",".$pageRow)
                      ->where($map)
                      ->select();
    }

    //查询评论列表（包含用户信息） 根据问答ID
    public function getCommentList($qid,$orderBy){
        $map = array(
                    "c.visible"=>array("EQ",'0'),
                    "c.qid"=>array("EQ",$qid)
        );
        return M('ask_comment')->alias("c")
                      ->field('c.*,u.id uid,u.name,u.logo,u.jc')
                      ->join("inner join qz_user as u on c.uid = u.id")
                      ->order($orderBy)
                      ->where($map)
                      ->select();
    }

    //查询点赞列表 根据用户ID，问题ID
    public function getAgreeList($qid,$uid){
        $map = array(
                    "qid"=>array("EQ",$qid),
                    "uid"=>array("EQ",$uid)
        );
        $result = M('ask_agree')->field('qid,aid')->where($map)->select();
        foreach ($result as $k => $v) {
            $newResult[$v['aid']] = $v;
        }
        return $newResult;
    }

    //查询取配置数据
    public function getOption($name,$update = false){
        //如果update为true则更新~
        if($update == true){
            S('C:OP:N:'.$name,null);
        }
        $s = OP($name,'yes');
        return unserialize($s);
    }

     //增加配置数据
    public function addOption($name,$value){
        $data['option_name'] = $name;
        $data['option_value'] = $value;
        $data['option_group'] = 'ask';
        $data['autoload'] = 'yes';
        $data['option_remark'] = 'Ask Module';
        return M("options")->add($data);
    }

    //修改配置数据
    public function updateOption($name,$value){
        $data['option_value'] = $value;
        $map = array(
          "option_name" => array("EQ",$name)
        );
        return M("options")->where($map)->save($data);
    }

    //查询最新回答列表 By 用户ID
    public function getNewAnwserByUid($uid,$orderBy,$pagesize= 1,$pageRow = 6){
        $map = array(
            "a.visible"=>'0',
            'a.uid'=>$uid
        );
        $result = M('ask_anwser')->alias("a")
                      ->field('a.qid,a.post_time,b.title')
                      ->join("inner join qz_ask as b on a.qid = b.id ")
                      ->order($orderBy)
                      ->where($map)
                      ->limit($pagesize.",".$pageRow)
                      ->select();
        //dump($result);
        return $result;
    }

    //查询用户信息列表 By 用户ID  - 缓存10分钟
    public function getUsers($uid,$update = false){
        $map = array("u.id"=>array("IN",$uid));
        $result = M("user")->alias("u")
                          ->field('u.id,u.logo,u.qc,u.jc,u.ask_anwsers,u.ask_adopts,ask_agrees,q.bm')
                          ->join("inner join qz_quyu as q on u.cs = q.cid ")
                          ->where($map)
                          ->order(array("find_in_set(u.id, '$uid')"))
                          ->select();
      return $result;
    }

    //查询提问中图片列表 根据 fid
    public function getQuestionImg($id){
        $map["fid"] = $id;
        $map["type"] = '1';
        return M('ask_file')->field('type,qid,fid,path')->order('time DESC')->where($map)->select();
    }

    //查询回答中图片列表 根据 qid
    public function getAnwserImg($qid){
        $map['qid'] = $qid;
        $map["type"] = '2';
        return M('ask_file')->field('type,qid,fid,path')->order('time DESC')->where($map)->select();
    }

    //查询新增回答列表（包含用户和提问标题）
    public function getNewAnwsers($limit,$update){
        $map = array(
            "q.visible"=>array("EQ",'0'),
        );
        $result = S('Cache:Ask:newAnwsers:num'.$limit);
        if(empty($result)){
            $result = M('ask_anwser')->alias("a")
                ->field('a.content,a.post_time,a.uid,u.name,u.jc,q.title,q.id')
                ->join("left join qz_user as u on a.uid = u.id")
                ->join("left join qz_ask as q on (a.qid = q.id  and q.visible = '0') ")
                ->order('a.post_time DESC')
                ->limit("0,".$limit)
                //->where($map)
                ->select();
            S('Cache:Ask:newAnwsers:num'.$limit,$result,600);
        }
        return $result;
    }

    //查询本月有图的热门问题列表
    public function getHotQuestion($num){
        //修改为本月热门问题
        $monthtime = strtotime(date('Y-m-d',time()))  - 86400 * 30 ;
        $map = array(
            "a.visible"=>'0',
            "f.type"=>'1`',
            "a.post_time" => array('EGT',$monthtime)
        );
        $result = M('ask')->alias("a")
                      ->field('a.id,a.title,a.views,a.content,f.path')
                      ->join("left join qz_ask_file as f on a.id = f.fid")
                      ->order('a.anwsers DESC')
                      ->where($map)
                      ->limit("0,".$num)
                      ->select();
        //dump(M('ask')->getLastSql());
        return $result;
    }

    //查询有图的问题列表
    public function getImgQuestion($num){
        $map = array(
            "a.visible"=>'0',
            "f.type"=>'1`'
        );
        $result = M('ask')->alias("a")
                      ->field('a.id,a.title,a.views,a.content,f.path')
                      ->join("left join qz_ask_file as f on a.id = f.fid")
                      ->order('a.anwsers DESC')
                      ->where($map)
                      ->limit("0,".$num)
                      ->select();
        //dump(M('ask')->getLastSql());
        return $result;
    }

    //查询单个答案(包含用户信息) 根据 Anwser ID
    public function getAnwserById($aid){
        $map = array(
            "a.id"=>$aid,
            'a.visible'=>'0'
        );
        return M('ask_anwser')->alias("a")
                      ->field('a.*,u.id uid,u.name,u.jc,u.classid,u.logo,u.ask_anwsers,u.ask_adopts,u.ask_agrees,q.bm')
                      ->join("inner join qz_user as u on a.uid = u.id")
                      ->join("left join qz_quyu as q on u.cs = q.cid ")
                      ->where($map)
                      ->find();
    }


    //查询问答列表
    public function getAnwsersByUid($map,$pagesize= 1,$pageRow = 10){

        $map['a.visible'] = '0';

        $DB = M('ask_anwser');
        $count = $DB->alias("a")->where($map)->count();
        $result = $DB->alias("a")
                      ->field('a.id aid,a.uid anwseruid,a.content,a.post_time,a.agree,q.id,q.sub_category,q.username,q.title,q.best_aid,q.anwsers,c.name')
                      ->join("inner join qz_ask as q on q.id = a.qid")
                      ->join("inner join qz_ask_category as c on q.sub_category = c.cid")
                      ->order('a.post_time DESC')
                      ->limit($pagesize.",".$pageRow)
                      ->order($order)
                      ->where($map)
                      ->select();
        return array("result"=>$result,"count"=>$count);
    }


    //查询问答列表，包括分类和用户
    public function getQUCList($map,$pagesize= 1,$pageRow = 10){
        //如果 cid 不为空
        if(!empty($map['cateId'])){
          $cid = $map['cateId'];
          $categoryColumn = $cid <= 6 ? 'a.cid' : 'a.sub_category';
          $map[$categoryColumn] = array("EQ",$cid);
          unset($map['cateId']);
        }

        if(!empty($map['orderBy'])){
            $orderby = $map['orderBy'];
            unset($map['orderBy']);
        }
        //如果关键词不为空
        if(isset($map['keyword'])){
            $map['a.title']  = array('like','%'.$map['keyword'].'%');
            unset($map['keyword']);
        }

        $map['a.visible'] = '0';
        $DB = M('ask');
        $count = $DB->alias("a")->where($map)->count();
        $result = $DB->alias("a")
                      ->field('a.id,a.title,a.anwsers,a.views,a.post_time,c.name,c.cid,u.nickname username,u.avatar logo,a.uid as old_userid')
                      ->join("inner join qz_ask_category as c on a.sub_category = c.cid")
                      ->join("left join qz_ucenter_profile as u on u.uuid = a.uid")
                      ->order($orderby)
                      ->limit($pagesize.",".$pageRow)
                      ->where($map)
                      ->select();
        //dump(M()->getLastSql());
        return array("result"=>$result,"count"=>$count);
    }

    /**
     * Gets the question by category.
     *
     * @param      <type>  $map     The map
     * @param      <type>  $number  The number
     *
     * @return     <type>  The hot question.
     */
    public function getCategoryAsk($map,$number){

        if(!empty($map['orderBy'])){
            $orderby = $map['orderBy'];
            unset($map['orderBy']);
        }

        $map['a.visible'] = '0';

        return M('ask')->alias("a")
                      ->field('a.id,a.title,a.anwsers,a.views,a.post_time,a.content,c.name,c.cid')
                      ->join("inner join qz_ask_category as c on a.sub_category = c.cid")
                      ->order($orderby)
                      ->limit($number)
                      ->where($map)
                      ->select();
    }

    /**
     * Gets the new video.
     *
     * @param      <type>  $num    The number
     *
     * @return     <type>  The new video.
     */
    public function getNewVideo(){
        $map = array(
            'isdelete' => '0'
        );
        return M('article_vedio')->field('*')->where($map)->limit('1')->order('time DESC')->find();
    }

    //查询单个问题
    public function getAskByid($id){
        $map = array(
            "a.id"=>$id,
            'a.visible'=>'0'
        );
        return M('ask')->alias('a')
            ->field('a.*,p.avatar logo')
            ->join('left join qz_ucenter_profile p ON p.uuid = a.uid')
            ->where($map)->find();
    }


    //查询单个回答
    public function getAnwser($id){
        $map = array(
            "id"=>$id,
            'visible'=>'0'
        );
        return M('ask_anwser')->field('*')->where($map)->find();
    }

    //查询单个回复
    public function getComment($id){
        $map = array(
            "id"=>$id,
            'visible'=>'0'
        );
        return M('ask_comment')->field('*')->where($map)->find();
    }

    //查询用户是否回答过该问题
    public function isAnwserByQid($qid,$uid){
        $map = array(
            "qid"=>$qid,
            "uid"=>$uid,
            'visible'=>'0'
        );
        return M('ask_anwser')->where($map)->count();
    }

    //查询用户是否点赞过该提问
    public function isAgree($data){
        $map = array(
            "qid"=>$data['qid'],
            "aid"=>$data['aid'],
            "uid"=>$data['uid'],
        );
        return M('ask_agree')->where($map)->find();
    }

    //查询用户问答信息 根据UID
    public function getUserByAsk($uid){
        $map = array(
            "id"=>$uid,
        );
        return M('user')->field('ask_anwsers,ask_adopts,ask_agrees')->where($map)->find();
    }

    //答案点赞操作 ： $unSet = true 时，删除点赞数据
    public function setAgree($data,$unSet = '0'){
         $map = array(
            "qid"=>$data['qid'],
            "aid"=>$data['aid'],
            "uid"=>$data['uid'],
            );
            if($unSet == '1'){
                //限制只能删除一条数据
                return M('ask_agree')->where($map)->limit('1')->delete();
            }
            return M('ask_agree')->add($data);
    }

    //添加问题
    public function addQuestion($data){
        S('Cache:Ask:TopUser',NULL);
        return M("ask")->add($data);
    }

    //编辑问题
    public function edtiQuestion($id,$uid,$data){
        $map = array(
                "id"=>$id,
                "uid"=>$uid
                );
        return M("ask")->where($map)->save($data);
    }

    //采纳答案
    public function adoptAnwser($data){
        $map = array(
                "id"=>$data['id'],
        );
        return M("ask")->where($map)->save($data);
    }

    //添加答案
    public function addAnwser($data){
        return M("ask_anwser")->add($data);
    }

    //添加回复
    public function addComment($data){
        return M("ask_comment")->add($data);
    }

    //更新浏览量
    public function updateViews($id){
        return M("Ask")->where('id='.$id)->setInc('views');
    }

    //查询用户回答排行 缓存10分钟
    public function getTopUser($limit=6,$update = null){
/*        //先取缓存数据
        if($update){
            S('Cache:Ask:TopUser',NULL);
        }
        $askTopUser = S('Cache:Ask:TopUser');
*/
        if(empty($askTopUser)){
            $askTopUser = M('user')->alias("u")
                            ->field('u.id,u.name,u.jc,u.logo,u.ask_anwsers,u.ask_adopts,u.ask_agrees,q.bm')
                            ->join("inner join qz_quyu as q on u.cs = q.cid ")
                            ->order('u.ask_anwsers DESC')
                            ->limit("0,".$limit)
                            ->where('u.classid = 3')
                            ->select();
            //有效期10分钟
            //S('Cache:Ask:TopUser',$askTopUser,600);
        }
        return $askTopUser;
    }

    //查询当前城市用户回答排行
    public function getCityTopUser($limit=3,$city = null){
        //先取会员公司回答数
        $city = empty($city) ? '320621' : $city;
        $map['u.classid'] = array('eq',3);
        $map['u.cs'] = array('eq',$city);
        //$map['u.ask_anwsers'] = array('egt',1);
        $map['u.on'] = array('eq',2);
        $vipUser = M('user')->alias("u")
                        ->field('u.id,u.name,u.jc,u.logo,u.ask_anwsers,u.ask_adopts,u.ask_agrees,q.bm')
                        ->join("inner join qz_quyu as q on u.cs = q.cid ")
                        ->order('u.ask_anwsers DESC')
                        ->limit("0,".$limit)
                        ->where($map)
                        ->select();

        //如果当前城市会员公司数小于输出数 取本城市非会员公司
        //永远不会显示非会员公司！因为，每个城市不管理真假都有会员公司
        if(count($vipUser) < $limit){
            $num = $limit - count($vipUser);
            $newMap['u.classid'] = array('eq',3);
            $newMap['u.cs'] = array('eq',$city);
            $newMap['u.on'] = array('neq',2);
            $allUser = M('user')->alias("u")
                        ->field('u.id,u.name,u.jc,u.logo,u.ask_anwsers,u.ask_adopts,u.ask_agrees,q.bm')
                        ->join("inner join qz_quyu as q on u.cs = q.cid ")
                        ->order('u.ask_anwsers DESC')
                        ->limit("0,".$num)
                        ->where($newMap)
                        ->select();
            $newRes = array_merge($vipUser,$allUser);

            //如果会员公司和非会员公司都没有~~~ ：） 。。。肯定其它数据也是空。
            if(count($newRes) >= $limit){
                return $newRes;
            }else{
                $num = $limit - count($newRes);
                //取：除本城市之外所有会员公司
                $newMap2['u.classid'] = array('eq',3);
                $newMap2['u.cs'] = array('neq',$city);
                $newMap2['u.on'] = array('eq',2);
                $allVipUser = M('user')->alias("u")
                            ->field('u.id,u.name,u.jc,u.logo,u.ask_anwsers,u.ask_adopts,u.ask_agrees,q.bm')
                            ->join("inner join qz_quyu as q on u.cs = q.cid ")
                            ->order('u.ask_anwsers DESC')
                            ->limit("0,".$num)
                            ->where($newMap2)
                            ->select();
                return array_merge($allVipUser,$newRes);
            }
        }else{
            return $vipUser;
        }
    }

    //查询登录用户的最新回答
    public function getMyAnwser($uid,$orderBy,$pagesize= 1,$pageRow = 10){
        $map = array(
            "a.visible"=>'0',
            'a.uid'=>$uid
        );
        $Ask = M('ask_anwser');
        $result['count'] = $Ask->alias("a")->where($map)->count();
        $result['result'] = $Ask->alias("a")
                      ->field('a.qid,a.post_time,a.content,a.comments,a.agree,b.title,b.status,b.best_aid')
                      ->join("inner join qz_ask as b on a.qid = b.id ")
                      ->order($orderBy)
                      ->where($map)
                      ->limit($pagesize.",".$pageRow)
                      ->select();
        return $result;
    }

    //根据Uid和关键词取Tag列表
    public function getTagsByUid($uid,$keywords){
        //$map['uid'] = $uid;
        if(stristr($keywords,',')){
            $map['name'] =  array("IN",$keywords);
        }else{
            $map['name'] =  $keywords;
        }
        return M('tags')->where($map)->field('*')->order('time')->select();
    }

    //增加上传的图片
    public function addUploadImage($id,$qid,$type,$file){
        $data['type'] = $type;
        $data['qid'] = $qid;
        $data['fid'] = $id;
        $data['path'] = $file;
        $data['time'] = time();
        return M("ask_file")->add($data);
    }

    //取热门问题带答案
    public function getHotAsk($num){
        $map = array("q.visible"=>0);
        $buildSql = M('ask')->alias("q")
                            ->field("q.id,q.views,q.title")
                            ->order('q.views DESC')
                            ->limit("0,".$num)
                            ->buildSql();
        return  M('ask')->table($buildSql)->alias("t")
                        ->join("join qz_ask_anwser as a on t.id = a.qid and a.visible = 0")
                        ->group("t.id")->select();
    }

    //查询分类
    public function getCategory(){
        return M('ask_category')->field('cid,pid,name,order_id,count,title,keywords,description')->order('order_id')->select();
    }

    //获取问题分类 使用缓存
    public function getCategorys($cid = '',$update = false){
        //先取所有分类
        $tempCategory = S('Cache:Ask:AllCategory');
        if(empty($tempCategory)){
            $tempCategory = $this->getCategory();
            S('Cache:Ask:AllCategory',$tempCategory,86400);
        }

        $category = S('Cache:Ask:Category');

        if(empty($category) || $update == true){
            $category = array();
            if($tempCategory){
                //为了避免这个Bug，进行两次遍历，先取根数组，后期改进
                foreach ($tempCategory as $k => $v ){
                    if($v['pid'] == '0') {
                        $category[$v['cid']] = $v;
                        unset($k);
                    }
                }
                foreach ($tempCategory as $k => $v ){
                    if($v['pid'] != '0') {
                        $category[$v['pid']]['sub_cate'][] = $v;
                    }
                }
            }
            ksort($category);
            S('Cache:Ask:Category',$category,3600);
        }
        //根据 Cid 返回
        if(!empty($cid)){
            if(empty($tempCategory)){
                $tempCategory = D("Common/Ask")->getCategory();
            }
            foreach ($tempCategory as $k => $v ){
                if($v['cid'] == $cid) {
                    unset($tempCategory);
                    return $v;
                    exit;
                }
            }
        }
        unset($tempCategory);
        return $category;
    }

    //查询所有省份 有缓存 $update 为 true 则更新缓存
    public function getAllCityList($update = false){
    	//先取缓存数据
    	if($update){
    		F('allCityList',NULL);
    	}else{
    		$allCityList = F('allCityList');
    	}
    	if(empty($allCityList)){
    		$result = M("province")->alias("a")
	                            ->join("inner join qz_city as b on a.qz_provinceid = b.fatherid")
	                            ->field("a.qz_province,a.qz_provinceid,b.qz_city,b.qz_cityid,b.fatherid")
	                            ->order("a.id ASC ")
	                            //->where("b.qz_city != '县' ")
	                            //->cache('allCityList',6000)
	                            ->select();
	        $citys = array();

	        foreach ($result as $k => $v ){
	        	$citys[$v['qz_province']][] = array(
	        		'city' => $v['qz_city'],
	        		'cityid' => $v['qz_cityid'],
	        		'provinceid' => $v['qz_provinceid']
	        	);
			}
			$i = 0;
			foreach ($citys as $k => $v ){
				$area_id = $v['0']['provinceid'];
				$newCity[] = array(
						'area' => $k,
						'area_id' => $i,
						'provinceid' => $area_id,
						'city' => $v
				);
				$i++;
			}
			unset($citys);
			F('allCityList',$newCity);
			return $newCity;
		}
     	return $allCityList;
    }

    //查询所有省份 有缓存 $update 为 true 则更新缓存
    public function getAreaList($update = false){
    	return M('province')->field('id,qz_provinceid,qz_province')->order('id ASC')->select();
    }

    //查询所有城市 有缓存  * $update 为 true 则更新缓存
    public function getCityList($update = false){
    	if($update){
    		F('allCityList',NULL);
    	}else{
    		$allCityList = F('allCityList');
    	}
    	//如果数据为空
    	if(empty($allCityList)){
    		$result = M('city')->order('id ASC')->select();

    		foreach ($result as $k => $v ){
	        	$citys[$v['fatherid']][] = array(
	        		'city' => $v['qz_city'],
	        		'cityid' => $v['qz_cityid']
	        	);
			}

			F('allCityList',$citys);
			return $citys;
    	}else{
    		return $allCityList;
    	}
    }

    //根据城市ID取省份ID
    public function getProvinceIdByCityId($cid = ''){
        $myProvince = M('city')->where('qz_cityid = '.$cid)->field('fatherid')->find();
        return $myProvince['fatherid'];
    }

    //查询是否已存在此问题
    public function isUnique($title,$uid){
        $map['title'] = array('EQ',$title);
        $map['uid'] = array('EQ',$uid);
        return M('ask')->where($map)->field('id,title')->find();
    }


        /**
     * [getRelationQuestion 获取相关问题列表]
     * @param  string  $tags         [标签id]
     * @param  string  $keywords     [关键字]
     * @param  integer $sub_category [子分类]
     * @param  integer $cid          [大分类]
     * @param  integer $number       [获取数量]
     * @param  integer $out          [排除某个ID的问题]
     * @return [type]                [description]
     */
    public function getRelationQuestion($tags = '', $keywords = '', $sub_category = 0, $cid = 0, $number = 0, $out = 0){
        $md5 = md5($tags . $keywords . $sub_category . $cid . $number . $out);
        $result = S('Wenda:Relation:'.$md5);
        if (!empty($result)) {
            return $result;
        }
        $result = [];
        /*根据标签ID和关键字获取相关问答*/
        if (!empty($tags) || !empty($keywords)) {
            $map['a.visible'] = array('EQ', 0);
            if (!empty($out)) {
                $map['a.id'] = array('NEQ', intval($out));
            }
            //根据标签和关键字获取
            if(!empty($tags)){
                $tags = array_filter(explode(',', $tags));
                foreach ($tags as $key => $value) {
                    $string = $string . 'FIND_IN_SET("' . intval($value) . '",a.tags) OR ';
                }
            }
            //拼接关键字查询
            if(!empty($keywords)){
                $keys = array_filter(explode(',', $keywords));
                foreach ($keys as $key => $value) {
                    $string = $string . 'FIND_IN_SET("' . addslashes($value) . '",a.keywords) OR ';
                }
            }
            $string = rtrim($string, 'OR ');
            if(!empty($string) ){
                $map = array_merge($map, ['_string' => $string,'_logic' => 'AND']);
            }
            $firsts = M('ask')->alias("a")
                              ->field('a.id,a.sub_category,a.title,a.description,a.post_time,a.last_time,a.anwsers,a.views,a.status,c.pid AS cid,c.name')
                              ->join("inner join qz_ask_category as c on a.sub_category = c.cid")
                              ->order('id DESC')
                              ->limit($number)
                              ->where($map)
                              ->select();
            $result = $firsts;
        }

        /*根据子分类获取问答*/
        $number = $number - count($result);
        if ($number > 0 && !empty($sub_category)) {
            unset($map);
            $map['a.visible'] = array('EQ', 0);
            $ids = '';
            if(!empty($out)){
                $ids = intval($out) . ',';
            }
            foreach ($result as $key => $value) {
                $ids = $ids . $value['id'] . ',';
            }
            $map['a.id'] = array('NOT IN',trim($ids,','));
            $map['a.sub_category'] = array('EQ',$sub_category);
            $seconds = M('ask')->alias("a")
                               ->field('a.id,a.sub_category,a.title,a.description,a.post_time,a.last_time,a.anwsers,a.views,a.status,c.pid AS cid,c.name')
                               ->join("inner join qz_ask_category as c on a.sub_category = c.cid")
                               ->order('id DESC')
                               ->limit($number)
                               ->where($map)
                               ->select();
            $result = array_merge($result, $seconds);
        }
        /*根据大分类获取问答*/
        $number = $number - count($result);
        if ($number > 0 && !empty($cid)) {
            unset($map);
            $map['a.visible'] = array('EQ', 0);
            $ids = '';
            if(!empty($out)){
                $ids = intval($out) . ',';
            }
            foreach ($result as $key => $value) {
                $ids = $ids . $value['id'] . ',';
            }
            $map['a.id'] = array('NOT IN',trim($ids,','));
            $map['a.cid'] = array('EQ',$cid);
            $thirds = M('ask')->alias("a")
                              ->field('a.id,a.sub_category,a.title,a.description,a.post_time,a.last_time,a.anwsers,a.views,a.status,c.pid AS cid,c.name')
                              ->join("inner join qz_ask_category as c on a.sub_category = c.cid")
                              ->order('id DESC')
                              ->limit($number)
                              ->where($map)
                              ->select();
            $result = array_merge($result, $thirds);
        }

        S('Wenda:Relation:'.$md5, $result, 900);
        return $result;
    }

    /**
     * 获取推荐的问答
     * @return array
     */
    public function getTopNewWenDa($limit)
    {
        $map = array(
           "a.is_distillate" => array("EQ",1),
           "a.visible" => array("EQ",0)
        );
        return M('ask')->where($map)->alias("a")
                       ->join("join qz_ask_category b on b.cid = sub_category")
                       ->join("join qz_user u on u.id = a.uid")
                       ->field("a.id,title,post_time,anwsers,b.name as category_name,u.name,u.logo")->limit($limit)->order("a.id desc")->select();
    }
	
	
	
	/**
	 * 问答专题页 map
	 * @param $keywords
	 * @return array
	 */
	public function getAskFenCiMap($keywords)
	{
		$map = [
			"visible" => ["EQ", 0],
			"review" => ["EQ", 1],
			"anwsers" => ["NEQ", 0],
		];
		if (!is_array($keywords)) {
			return $map;
		}
		
		foreach ($keywords as $key => $value) {
			$value = trim($value);
			$title[] = array("LIKE", "%$value%");
		}
		
		$title[] = "OR";
		$map['title'] = $title;
		return $map;
	}
	/**
	 * 获取问题数量
	 * @param $keywords
	 * @return array
	 */
	public function getAskByFenCiCount($keywords)
	{
		$map = $this->getAskFenCiMap($keywords);
		$count = M("ask")->alias("a")
			->where($map)
			->count();
		return $count;
	}
	
	/**
	 * 获取问题
	 * @param $keywords
	 * @param $page
	 * @param $perCount
	 * @return array
	 */
	public function getAskByFenCi($keywords, $page, $perCount)
	{
		$map = $this->getAskFenCiMap($keywords);
		
		$ret = M("ask")->alias("a")
			->where($map)
			->field("a.id,a.title,a.anwsers")
			->order("a.post_time desc,a.id desc")
			->limit($perCount)
			->page($page)
			->select();
		return $ret;
	}
	
	/**
	 * 获取回答的数量
	 * @param $askId
	 * @return mixed
	 */
	public function getAnswerCountByAskId($askId)
	{
		$map = [
			"aa.qid" => ["EQ", $askId],
			"aa.visible" => ["EQ", '0'],
			"aa.content" => ["NEQ", ''],
		];
		$ret = M("ask_anwser")->alias("aa")
			->where($map)
			->join("inner join qz_user as u on aa.uid = u.id")
			->join("left join qz_quyu as q on u.cs = q.cid ")
			->count();
		return $ret;
	}
	
	/**
	 * 获取最新的一条回答
	 * @param $askId
	 * @return mixed
	 */
	public function getAnswerByAskId($askId)
	{
		$map = [
			"aa.qid" => ["EQ", $askId],
			"aa.visible" => ["EQ", '0'],
			"aa.content" => ["NEQ",''],
		];
		$ret = M("ask_anwser")->alias("aa")
			->where($map)
			->field("aa.id,aa.content,aa.create_time")
			->join("inner join qz_user as u on aa.uid = u.id")
			->join("left join qz_quyu as q on u.cs = q.cid ")
			->order("aa.post_time desc,aa.id desc")
			->limit(1)
			->find();
		return $ret;
	}

	public function getHomeAsk($cids, $limit = 3)
    {
        $map = [
            "a.cid" => array("in", $cids),
            "a.visible" => array("eq", "0")
        ];

        return M("ask")->alias("a")
            ->where($map)
            ->field("a.id,a.title,a.username as old_username,a.anwsers,a.post_time,c.cid,c.name as sub_category_name,u.nickname as username,u.avatar as logo")
            ->join("INNER JOIN qz_ask_category AS c ON a.sub_category = c.cid")
            ->join("LEFT JOIN qz_ucenter_profile AS u ON u.uuid = a.uid")
            ->order("a.post_time DESC")
            ->limit($limit)
            ->select();

    }


}