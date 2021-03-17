<?php
/**
 * 业主点评表 Comment
 */
namespace Common\Model;
use Think\Model;
class CommentModel extends Model{
    protected $autoCheckFields = false; //设置autoCheckFields属性为false后，就会关闭字段信息的自动检测，因为ThinkPHP采用的是惰性数据库连接，只要你不进行数据库查询操作，是不会连接数据库的。

    /**
     * [getRegisterCount 获取业主评论数量]
     * @return [type]      [description]
    */
    public function getCommentCount($cs){
        if(!empty($cs)){
            $map["cs"] = array("EQ",$cs);
        }
        return M("comment")->where($map)->count();
    }

    /**
     * 获取最新的业主点评
     * @param  integer $limit     [description]
     * @param  string  $city      [城市编号]
     * @param  string  $userCount [城市会员数量]
     * @return [type]             [description]
     */
    public function getNewComment($limit = 10,$city = '',$userCount = 0){
        $map = array(
                "a.recommend" =>array("EQ",1),
                "b.classid"=>array("EQ","1"),
                "c.on"=>array("EQ",2),
                "e.fake"=>array("EQ",0)
                    );
        if(!empty($city)){
            $map["a.cs"] = array("EQ",$city);
        }
        $buildSql = M("comment")->where($map)->alias("a")
                                 ->join("INNER JOIN qz_user as c on a.comid = c.id")
                                 ->join("INNER JOIN qz_user_company as e on e.userid = c.id")
                                 ->join("INNER JOIN qz_quyu as d on d.cid = c.cs")
                                 ->join("INNER JOIN qz_user as b on a.userid = b.id")
                                 ->order("a.time desc")
                                 ->field("a.*,c.id as cid,b.name as username,b.sex,b.logo,c.jc,c.qc,d.bm")
                                 ->buildSql();

        //根据会员数量,大于5家的取每家最新的
        switch ($userCount) {
            default:
                return   M("comment")->table($buildSql)->alias("t")->limit($limit)->group("t.comid")->order("t.time desc")->select();
                break;
            case "1":
                //1家会员取1家会员的全部评论
                return  M("comment")->table($buildSql)->alias("t")->limit($limit)->order("t.time desc")->select();
                break;
            case "2":
            case "3":
            case "4":
                $result = M("comment")->table($buildSql)->alias("t")->limit($userCount)->group("t.comid")->order("t.time desc")->select();
                $offset = $limit - $userCount;//评论数差
                foreach ($result as $key => $value) {
                    $ids[] = $value['id'];
                }
                if(count($ids) > 0){
                    $map = array(
                            "a.id"=>array("NOT IN",$ids),
                            "a.recommend" =>array("EQ",1),
                            "b.classid"=>array("EQ","1"),
                            "c.on"=>array("EQ",2),
                            "e.fake"=>array("EQ",0)
                                 );
                    if(!empty($city)){
                        $map["a.cs"] = array("EQ",$city);
                    }
                    $buildSql = M("comment")->where($map)->alias("a")
                                     ->join("INNER JOIN qz_user as c on a.comid = c.id")
                                     ->join("INNER JOIN qz_user_company as e on e.userid = c.id")
                                     ->join("INNER JOIN qz_quyu as d on d.cid = c.cs")
                                     ->join("INNER JOIN qz_user as b on a.userid = b.id")
                                     ->order("a.time desc")
                                     ->field("a.*,c.id as cid,b.name as username,b.sex,b.logo,c.jc,c.qc,d.bm")
                                     ->buildSql();

                    $sub = M("comment")->table($buildSql)->alias("t")->limit($offset)->select();
                    $result = array_merge($result,$sub);
                    return $result;
                }
                break;
        }
    }

    /**
     * 获取装修公司评论数
     * @param  string $comid [公司编号]
     * @param  string $cs    [所在城市]
     * @return [type]        [description]
     */
    public function getCommentByComIdCount($comid="",$cs="",$type=''){
        $map = array(
                "a.comid"=>array("EQ",$comid),
                "a.isveritfy"=>array("EQ",0)
                     );
        switch ($type){
            case 0 :
                $order = "company_recommend desc,time desc";
                break;
            case 1 :
                $map['a.company_recommend'] = array("EQ",2);
                break;
            case 2 :
                $order = "time desc";
                break;
            case 3 :
                $map['a.step'] = array("EQ","开工阶段");
                break;
            case 4 :
                $map['a.step'] = array("EQ","水电阶段");
                break;
            case 5 :
                $map['a.step'] = array("EQ","泥木阶段");
                break;
            case 6 :
                $map['a.step'] = array("EQ","油漆阶段");
                break;
            case 7 :
                $map['a.step'] = array("EQ","竣工阶段");
                break;
            default  :
                $order = "company_recommend desc,time desc";
                break;
        }

        return M("comment")->where($map)->alias("a")
                                ->join("LEFT JOIN qz_ucenter_profile as b on a.userid = b.uuid")
                                ->count();
    }

    /**
     *  获取装修公司的评论
     * @param  string  $comid [公司编号]
     * @param  string  $cs    [所在城市]
     * @param  integer $limit [显示数量]
     * @param  boolean $reply [是否显示回复]
     * @return [type]         [description]
     */
    public function getCommentByComId($comid="",$cs="",$pageIndex =0,$pageCount=10,$reply = false,$type='')
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);
        $order = "a.company_recommend desc,a.time desc";
        $map = array(
                "a.comid"=>array("EQ",$comid),
                "a.isveritfy"=>array("EQ",0)
                     );
        switch ($type){
            case 0 :
                $order = "a.company_recommend desc,a.time desc";
                break;
            case 1 :
                $map['a.company_recommend'] = array("EQ",2);
                break;
            case 2 :
                $order = "a.time desc";
                break;
            case 3 :
                $map['a.step'] = array("EQ","开工阶段");
                break;
            case 4 :
                $map['a.step'] = array("EQ","水电阶段");
                break;
            case 5 :
                $map['a.step'] = array("EQ","泥木阶段");
                break;
            case 6 :
                $map['a.step'] = array("EQ","油漆阶段");
                break;
            case 7 :
                $map['a.step'] = array("EQ","竣工阶段");
                break;
            default  :
                $order = "a.company_recommend desc,a.time desc";
                break;
        }
        $buildSql = M("comment")->where($map)->alias("a")
                                ->field('a.*')
                                //->join("INNER JOIN qz_user as ax on a.userid = ax.id")
                                ->order($order)
                                ->limit($pageIndex.",".$pageCount)
                                ->buildSql();

        $buildSql = M("comment")->table($buildSql)->alias("t")
                           ->join("LEFT JOIN qz_ucenter_profile as b on t.userid = b.uuid")
                           ->field("t.*,t.name as bname,b.avatar as logo")
                           ->buildSql();

        if($reply){
            return M("comment")->table($buildSql)->alias("t1")
                               ->join("LEFT JOIN qz_reply as c on c.commid = t1.id")
                               ->field("t1.*,c.rptxt,c.time as reply_time")
                               ->select();
        }else{
            return M("comment")->table($buildSql)->alias("t1")->select();
        }
    }

    /**
     * 保存评论
     * @param [type] $data [description]
     */
    public function setComment($data){
        return M("comment")->add($data);
    }


    /**
     * 获取评论信息
     * @param  [type] $id    [评论编号]
     * @param  [type] $comid [公司编号]
     * @return [type]        [description]
     */
    public function getCommentInfoById($id,$comid){
        $map = array(
                "a.id"=>array("EQ",$id),
                "a.comid"=>array("EQ",$comid)
                     );
        return M("comment")->where($map)->alias("a")
                           ->join("LEFT JOIN qz_reply as c on c.commid = a.id")
                           ->field("a.id,a.name,a.sj,a.sg,a.fw,a.count,c.rptxt")
                           ->find();
    }

     /**
     * 获取业主的评论信息的数量
     * @param  [type] $id    [用户编号]
     * @return [type] [description]
     */
    public function getCommentInfoByUserIdCount($id){
        $map = array(
                "userid"=>array("EQ",$id)
                     );
        //1.查询相关的评论信息
       return M("comment")->where($map)
                          ->count();
    }

    /**
     * 获取业主的评论信息
     * @param  [type] $id    [用户编号]
     * @return [type] [description]
     */
    public function getCommentInfoByUserId($id,$pageIndex,$pageCount)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $map = array(
                "userid"=>array("EQ",$id)
                     );
        //1.查询相关的评论信息
        $buildSql = M("comment")->where($map)
                                ->order("id desc")
                                ->limit($pageIndex.",".$pageCount)
                                ->buildSql();
        //2. 查询和评论相关的其他信息
        return M("comment")->table($buildSql)->alias("a")
                           ->join("INNER JOIN qz_user as u on u.id = a.comid")
                           ->join("INNER JOIN qz_quyu as b on b.cid = a.cs")
                           ->join("LEFT JOIN qz_reply as c on c.commid = a.id")
                           ->field("a.id,a.name,a.text,a.time,a.sj,a.sg,a.fw,a.count,c.rptxt,u.qc,b.bm,u.id as comid")
                           ->select();
    }

    /**
     * 编辑评论
     * @return [type] [description]
     */
    public function editComment($id,$userid,$data){
        $map = array(
                "id"=>array("EQ",$id),
                "userid"=>array("EQ",$userid)
                     );
        return M("comment")->where($map)->save($data);
    }

    //取最近一个月评论榜
    public function getTopCommentsByCity($cityid,$limit){
        !empty($cityid) && $map['u.cs'] = array('eq',$cityid);
        $monthtime = strtotime(date('Y-m-d',time()))  - 86400 * 30;
        $map['u.classid'] = array('eq',3);
        $map['u.on'] = array('eq',2);
        $map['p.is_show'] = array('eq',1);

        // feature-1009    合肥首页/分站排除会员40165
        if (intval($cityid) == 340100) {
            $map['u.id'] = array('not in', array('40165'));
        }


        $buildSql = M('user')->alias("u")
                        ->field('u.id,u.jc,q.bm,u.logo,p.fake,p.comment_score,p.comment_count')
                        ->join("inner join qz_quyu as q on u.cs = q.cid ")
                        ->join("inner join qz_user_company as p on u.id = p.userid")
                        ->order('p.comment_score+0 DESC,comment_count DESC')
                        ->limit("0,15")
                        ->where($map)
                        ->buildSql();

        return M("user")->table($buildSql)->alias("t1")
                                ->field('t1.*,count(c.id) as ccount')
                                ->join("LEFT join qz_comment as c on t1.id = c.comid and c.time >= '$monthtime' ")
                                ->group('t1.id')
                                ->order('fake ASC,comment_score+0 DESC,ccount DESC,comment_count DESC')
                                ->limit("0,".$limit)
                                ->select();
    }


    /**
     * 通过ids获取装修公司评论]
     * @param  [type] $ids [description]
     */
    public function getCommentByComs($ids)
    {
        $map = array(
            'comid'   => array('IN',$ids),
            'company_recommend'   => array('eq',2),
        );
        $buildSql = M('comment')
            ->field('id,comid,text,company_recommend')
            ->where($map)
            ->order('company_recommend desc,time desc')
            ->buildSql();
        $result = M("comment")->table($buildSql)->alias("t")->group("t.comid")->select();
        $info = [];
        foreach ($result as $key => $value) {
            if(empty($info[$value['comid']])){
                $info[$value['comid']] = $value;
            }
        }
        return $info;
    }


    //获取三天内的评论数
    public function getThreeCommentCount($condition='',$pagesize= 1,$pageRow = 10){

        $map["classid"] = array("EQ",3);

        if(!empty($condition['cs'])){
            $map["cs"] = array("EQ",$condition['cs']);
        }

        if(!empty($condition['keyword'])){
            $map['qc']  = array('LIKE','%'.$condition['keyword'].'%');
        }
        //认证
        if(!empty($condition['vip'])){
            $map["on"] = array("EQ",2);
        }
        if(!empty($condition['fw'])){
            $fw = $condition['fw'];
            $map["_string"] = "FIND_IN_SET($fw,q.quyu)";
        }
        if(!empty($condition['fg'])){
            $fg = $condition['fg'];
            $map["_string"] = "FIND_IN_SET($fg,q.fengge)";
        }
        if(!empty($condition['gm'])){
            $map["q.guimo"] = array("EQ",$condition['gm']);
        }

        $isSale = !empty($condition['sale']) ? "AND a.sales_count > '0'  " : '';

        $orderby = $condition['orderby'];
        unset($condition['orderby']);
        $timestamp = $this->getWeek();
        $where['time'] = ['between',array($timestamp['start'], $timestamp['end'])];
        $result = M("comment")->where($where)->field("comid, count(id) as shuliang")->order('shuliang desc, time desc')->group('comid')->select();
        return $result;
    }

    //获取当期周的上一周起始天数的时间戳
    private function getWeek(){
        date_default_timezone_set("Asia/Shanghai");
        $year = date("Y");
        //当前周的上一周
        $week = date("W")-1;
        $weeks = date("W", mktime(0, 0, 0, 12, 28, $year));
        if($week > $weeks || $week <= 0){
            $week = 1;
        }

        if($week < 10){
            $week = '0' . $week; // 注意：一定要转为 2位数，否则计算出错
        }
        //返回当前周的上一周的第一天和最后一天的时间戳
        $timestamp['start'] = strtotime($year . 'W' . $week);
        $timestamp['end'] = strtotime('+1 week -1 day', $timestamp['start']);
        return $timestamp;
    }

    /**
     * 获取公司列表的评分
     * @param array $companyIds
     * @return mixed
     */
    public function getCommentScores(array $companyIds)
    {
        $map = array(
            "t1.comid" => array("in", $companyIds),
            "t1.isveritfy" => array("EQ", 0)
        );
        $buildSql = $this->alias('t1')->where($map)->field("*")->buildSql();
        $ret = $this->table($buildSql)->alias('g')
            ->field("g.comid,count(g.id) as ccount,count(IF(g.sj <>0,g.id,null)) as newcount,count(IF(g.sj =0,g.id,null)) as oldcount, count(IF(g.count>=9 and g.sj = 0,g.count,null)) as oldgood, count(IF(g.sj>=9 and g.fw>=9 and g.sg >=9,g.id,null)) as good, avg(IF(g.sj<>0,g.sj,null)) avgsj,avg(IF(g.fw<>0,g.fw,null)) avgfw,avg(IF(g.sg<>0,g.sg,null)) avgsg,avg(IF(g.sj=0,g.count,null)) avgcount")
            ->group('g.comid')->select();
        return $ret;
    }
}