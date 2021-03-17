<?php
/**
 * 图库案例
 */
namespace Common\Model;
use Think\Model;
class CasesModel extends Model{
    protected $autoCheckFields = false; //设置autoCheckFields属性为false后，就会关闭字段信息的自动检测，因为ThinkPHP采用的是惰性数据库连接，只要你不进行数据库查询操作，是不会连接数据库的。

    /**
     * 获取主站最新的案例信息
     * @return [type] [description]
     */
    public function getIndexNewsCases($limit = 3,$cs =''){
        $map = array(
                "a.uon"    =>array("EQ",2),
                "a.classid"=>array("IN",array(1,2)),
                "a.isdelete"=>array("IN",array(1,3)),
                "a.on"=>array("EQ",1)
                    );
        if(!empty($cs)){
            $map["a.cs"] = array("EQ",$cs);
        }
        //生成子查询语句
        $buildSql = M("cases")->alias("a")
                    ->join("inner join qz_user as c on c.id = a.uid")
                    ->where($map)->field("a.*,c.jc,c.id as comid")
                    ->order("a.id desc")->limit($limit)->buildSql();

        return M("cases")->table($buildSql."as t")
               ->join("inner join qz_case_img as b on t.id = b.caseid")
               ->join("inner join qz_quyu as d on d.cid = t.cs")
               ->join("inner join qz_fengge as e on e.id = t.fengge")
               ->field("t.*,b.img_path,b.img_host,b.img,d.cname,d.bm,e.name as fname")
               ->group("b.caseid")
               ->order("b.img_on DESC")
               ->select();
    }

    /**
     * 获取效果图数量
     * @return [type] [description]
     */
    public function getIndexCaseImagesTotal($cs=''){
        //查询效果图数量, 案例删除后,图片数据并未删除,这里查询的时候忽略掉这个因素
        //直接查询有多少张图片记录数据当作案例效果图数量, 查询速度快

        //这里修改为用S方法取总数，缓存一天
        $case_img_num = S('Cache:Case:CaseImgNum');
        if(!$case_img_num){
            $case_img_num =  M("case_img")->count();
            S("Cache:Case:CaseImgNum",$case_img_num,86400);
        }
        return $case_img_num;

        // 真实案例 效果图数量 查出案例id后作为临时表然后就行inner join操作
        // 这样的查询方式很慢暂时不用
        /*
        if(!empty($cs)){
            $map["cs"] = array("EQ",$cs);
        }
        $buildSql = M("cases")->where($map)->field("id")->buildSql();
        return M("cases")->table($buildSql)->alias("a")
                              ->join("inner join qz_case_img as b on b.caseid = a.id")
                              ->field("a.id")
                              ->count();
        */
    }

    /**
     * 获取主站装修案例效果图
     * @return [type] [description]
     */
    public function getIndexCasesList($type ='',$limit = 20){
        //type 1.装修类型 2.户型 3.风格
        switch ($type) {
            case 1:
                $map = array(
                    "uon"    =>array("EQ",2),
                    "classid"=>array("IN",array(1,2)),
                    "isdelete"=>array("IN",array(1,3)),
                    "on"=>array("EQ",1)
                    );
                break;
            case 2:
                $map = array(
                    "uon"=>array("EQ",2),
                    "classid"=>array("IN",array(1,2)),
                    "isdelete"=>array("IN",array(1,3)),
                    "on"=>array("EQ",1)
                    );
                $where1 = array(
                        "shi" =>array("EQ",2),
                        "ting"=>array("EQ",1),
                        "on"=>array("EQ",1)
                        );
                $map["_complex"] = array(
                               array(
                                    "shi" =>array("EQ",2),
                                    "ting"=>array("EQ",1)
                                    ),
                               array(
                                    "shi" =>array("EQ",3),
                                    "ting"=>array("EQ",1)
                                    ),
                               array(
                                    "shi" =>array("EQ",4),
                                    "ting"=>array("EQ",2)
                                    ),
                               "_logic"=>"OR"
                                );
                break;
            case 3:
                $map = array(
                    "uon"     =>array("EQ",2),
                    "classid"=>array("IN",array(1,2)),
                    "fengge"  =>array("IN",array(1,2,3)),
                    "isdelete"=>array("IN",array(1,3)),
                    "on"=>array("EQ",1)
                    );
        }
        $buildSql = M("cases")->where($map)->order("time desc")->limit($limit)->buildSql();
        return M("cases")->table($buildSql." as a")
                         ->join("INNER JOIN qz_case_img as b on b.caseid = a.id")
                         ->join("inner join qz_quyu as d on d.cid = a.cs")
                         ->field("a.*,b.img_path,b.img_host,b.img,d.cname,d.bm")
                         ->group("b.caseid")->order("b.img_on = 2")
                         ->select();
    }

    /**
     * [getRegisterCount 获取发布案例的总数量]
     * @return [type]      [description]
    */
    public function getCasesCount($cs = '',$uid =""){
        $map = array(
                "isdelete"=>array("IN",array(1,3))
                     );
        if(!empty($cs)){
            $map["cs"] = array("EQ",$cs);
        }
        if(!empty($uid)){
            $map["uid"] = array("EQ",$uid);
        }
        return M("cases")->where($map)->count();
    }

    /**
     * 获取装修案例图片列表数量
     * @param  string $classid [description]
     * @param  string $huxing  [description]
     * @param  string $fengge  [description]
     * @param  string $jiage   [description]
     * @param  string $city    [description]
     * @return [type]          [description]
     */
    public function getCaseImagesListCount($classid = 1,$huxing='',$fengge='',$jiage='',$ys = "",$sm="",$keyword='',$city = '',$leixing=''){
        $map = array(
                "isdelete"=>array("IN",array(1,3)),
                "on"=>array("EQ",1)
                     );

        if(!empty($city)){
            $map["cs"] = array("EQ",$city);
        }

        if(!empty($keyword)){
            $map["title"] = array("LIKE","%".$keyword."%");
        }

        if(!empty($classid)){
            $map["classid"] = array("EQ",$classid);
        }

        if(!empty($huxing)){
            $map["huxing"] = array("EQ",$huxing);
        }
         if(!empty($fengge)){
            $map["fengge"] = array("EQ",$fengge);
        }
        if(!empty($jiage)){
            $map["zaojia"] = array("EQ",$jiage);
        }

        if(!empty($leixing)){
            $map["leixing"] = array("EQ",$leixing);
        }

        if(!empty($sm)){
            $map["mianji"] = array("ELQ",80);
        }

        if(!empty($ys)){
            if($ys == 1){
                $map["zaojia"] = array("IN","16,17,18,42");
            }elseif($ys == 2){
                $map["zaojia"] = array("IN","19,21,22,23,24,25");
            }
        }

        if(!empty($sm)){
            $map["mianji"] = array("ELT",80);
        }

        return  M("cases")->where($map)
                          ->count();
    }

    /**
     * 获取装修案例图片列表
     * @param  integer $pagesize [description]
     * @param  integer $pageRow  [description]
     * @param  string  $classid  [图片类型]
     * @param  string  $huxing   [户型]
     * @param  string  $fengge   [风格]
     * @param  string  $jiage    [价格]
     * @param  string  $city     [城市编号]
     * @param  string  $city     [公司编号]
     * @return [type]            [description]
     */
    public function getCaseImagesList($pagesize= 1,$pageRow = 10,$classid = "",$huxing='',$fengge='',$jiage='',$ys = "",$sm="",$keyword='',$city = '',$leixing=''){
        $map = array(
                "isdelete"=>array("IN",array(1,3)),
                "on"=>array("EQ",1)
                     );
        if(!empty($keyword)){
             $map["title"] = array("LIKE","%".$keyword."%");
        }

        if(!empty($classid)){
            $map["classid"] = array("EQ",$classid);
        }
         if(!empty($huxing)){
            $map["huxing"] = array("EQ",$huxing);
        }
         if(!empty($fengge)){
            $map["fengge"] = array("EQ",$fengge);
        }
        if(!empty($jiage)){
            $map["zaojia"] = array("EQ",$jiage);
        }
        if(!empty($city)){
            $map["cs"] = array("EQ",$city);
        }

        if(!empty($leixing)){
            $map["leixing"] = array("EQ",$leixing);
        }

        if(!empty($ys)){
            if($ys == 1){
                $map["zaojia"] = array("IN","16,17,18,42");
            }elseif($ys == 2){
                $map["zaojia"] = array("IN","19,21,22,23,24,25");
            }
        }

        if(!empty($sm)){
            $map["mianji"] = array("ELT",80);
        }

        //1.查询最新的案例信息
        $buildSql = M("cases")->where($map)
                              ->field("id,title,huxing,mianji as zarea,fengge,zaojia,time,uid")
                              ->order("id desc")->limit($pagesize.",".$pageRow)
                              ->buildSql();
        //2.查询案例相关的图片信息
        $buildSql = M("cases")->table($buildSql)->alias("a")
                              ->join("INNER JOIN qz_case_img as b on a.id = b.caseid")
                              ->field("a.*,b.img_path as src,b.img,b.img_host,b.img_on")
                              ->order("a.id desc,b.img_on desc")->buildSql();
        //3.查询案例封面图片
        $buildSql = M("cases")->table($buildSql)->alias("t")
                               ->group("t.id ")->order("t.id desc")->buildSql();

        return  M("cases")->table($buildSql." as t1")
                          ->join("LEFT JOIN qz_fengge as c on c.id = t1.fengge")
                          ->join("LEFT JOIN qz_jiage as d on d.id = t1.zaojia")
                          ->join("LEFT JOIN qz_user as  e on e.id = t1.uid")
                          ->join("LEFT JOIN qz_quyu as  f on f.cid = e.cs")
                          ->field("t1.*,c.name as zstyle,d.name as zcost,e.jc as writer,f.bm")
                          ->select();
    }

    /**
     * 获取案例封面图片
     * @param  [type] $caseid [案例编号]
     * @return [type]         [description]
     */
    public function getCaseCoverImg($caseid){
        $map = array(
                "caseid"=>array("IN",$caseid)
                     );
        $buildSql = M("case_img")->where($map)->order("img_on desc")->buildSql();

        return $buildSql = M("case_img")->table($buildSql)->alias("t")
                            ->group("caseid")
                            ->select();
    }

    /**
     * 根据案例编号获取案例的数量
     * @return [type] [description]
     */
    public function getCaseCount($id,$cs){
        $map["id"] = array("EQ",$id);
        $map["isdelete"] = array("IN",array(1,3));
        if(!empty($cs)){
            $map["cs"] = array("EQ",$cs);
        }
        return M("cases")->where($map)->count();
    }

    /**
     * 根据案例编号获取案例信息
     * @param  string $id [编号]
     * @param  string $cs [所在城市]
     * @return [type]     [description]
     */
    public function getCaseInfoById($id='',$cs =''){
        $map["on"] = array("EQ",1);
        if(!empty($id)){
            $map["id"] = array("EQ",$id);
        }
        $map["isdelete"] = array("IN",array(1,3));
        if(!empty($cs)){
            $map["cs"] = array("EQ",$cs);
        }
        $nextMap = $preMap = $map;
        $preMap["id"] = array("LT",$id);
        $nextMap["_string"] = $preMap["_string"] = "classid = @i";
        $nextMap["id"] = array("GT",$id);
        //1.查找当前的案例信息及相邻的案例信息
        //上一个案例
        $preSql = M("cases")->where($preMap)->field("id,uid,title,fengge,time,mianji,classid,shi,ting,wei,huxing,userid,user,'prv' as action,text,cs")->order("id desc")->limit(1)->buildSql();
        //下一个案例
        $nextSql = M("cases")->where($nextMap)->field("id,uid,title,fengge,time,mianji,classid,shi,ting,wei,huxing,userid,user,'next' as action,text,cs")->limit(1)->buildSql();

        $buildSql = M("cases")->where($map)
                              ->field("id,uid,title,fengge,time,mianji,@i:=classid as classid,shi,ting,wei,huxing,userid,user,'now' as action,text,cs")
                              ->union($preSql,true)
                              ->union($nextSql,true)
                              ->buildSql($nextSql);
        //2.取出案例的其他相关信息,设计师的信息
        $buildSql = M("cases")->table($buildSql)->alias("t")
                              ->join("LEFT JOIN qz_user as b on t.userid = b.id ")
                              ->join("LEFT JOIN qz_fengge as g on g.id = t.fengge")
                              ->join("LEFT JOIN qz_huxing as h on h.id = t.huxing")
                              ->field("t.*,g.name as gname,h.name as hname")
                              ->group("t.action")
                              ->buildSql();

        //3.取出相关的装修公司信息
        $buildSql =  M("cases")->table($buildSql)->alias("t2")
                               ->join("LEFT JOIN ( SELECT count(*) as groupCount ,comid from  qz_team group by comid )as c on c.comid = t2.uid")
                               ->join("LEFT JOIN qz_user as u on t2.uid = u.id ")
                               ->join("LEFT JOIN qz_user_company as k on k.userid = t2.uid")
                               ->group("t2.id")
                               ->join("LEFT JOIN (select count(id) as commentcount,comid from  qz_comment group by comid )as l on l.comid =t2.uid")
                               ->field("t2.*,l.commentcount,c.groupCount,u.case_count as casecount,u.qc,u.jc,u.`on`,k.fake")
                               ->buildSql();
        //4.取出案例的案例图片
        return M("cases")->table($buildSql)->alias("t1")
                                           ->join("LEFT JOIN qz_case_img  d on t1.id = d.caseid")
                                           ->field("t1.*,d.img,d.img_host,d.img_path,d.img_on")
                                           ->order("img_on desc,px")
                                           ->select();

    }

    /**
     * 获取案例图片列表
     * @param  string $comid   [公司编号]
     * @param  string $cs      [城市信息]
     * @param  string $classid [类别]
     * @return [type]          [description]
     */
    public function getCasesListByComIdCount($comid ='',$cs ='',$classid='',$type ='',$on = ""){
        $map = array(
                "cs"=>array("EQ",$cs),
                "uid"=>array("EQ",$comid),
                "isdelete"=>array("IN",array(1,3))
                     );
        if(!empty($on)){
            $map["on"] = array("EQ",$on);
        }

        if(!empty($classid)){
            $map["classid"] = array("IN",$classid);
        }

        if(!empty($classid) && !empty($type)){
            if( $classid == 2){
                $map["leixing"] = array("EQ",$type);
            }else{
                $map["huxing"] = array("EQ",$type);
            }
        }

       return  M("cases")->where($map)
                         ->count();

    }

    /**
     * 根据装修公司编号获取最新的案例
     * @param  string $comid [公司编号]
     * @param  string $cs    [所在城市]
     * @param  string $classid    [案例类型]
     * @param  string $on    [审核状态]
     * @return [type]        [description]
     */
    public function getCasesListByComId($pageIndex = 0,$pageCount = 10,$comid ='',$cs ='',$classid='',$type='',$on = "")
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);
        $map = array(
                "cs"=>array("EQ",$cs),
                "uid"=>array("EQ",$comid),
                "isdelete"=>array("IN",array(1,3))
                     );
        if(!empty($on)){
            $map["on"] = array("EQ",$on);
        }

        if(!empty($classid)){
            $map["classid"] = array("IN",$classid);
        }

        if(!empty($classid) && !empty($type)){
            if( $classid == 2){
                $map["leixing"] = array("EQ",$type);
            }else{
                $map["huxing"] = array("EQ",$type);
            }
        }
        $buildSql = M("cases")->where($map)
                              ->order("id desc")
                              ->limit($pageIndex.",".$pageCount)
                              ->field("id,title,mianji,fengge,zaojia,time,userid,huxing,leixing,isdelete,on")
                              ->buildSql();
        $buildSql = M("cases")->table($buildSql)->alias("t")
                              ->join("INNER JOIN qz_jiage as b on b.id = t.zaojia")
                              ->join("INNER JOIN qz_fengge as c on c.id = t.fengge")
                              ->join("LEFT JOIN  qz_user as d on d.id = t.userid")
                              ->join("LEFT JOIN qz_huxing as e on e.id = t.huxing")
                              ->join("LEFT JOIN qz_leixing as f on f.id = t.leixing")
                              ->field("t.*,b.name as jiage,c.name as fg,d.name as dname,e.name as hx,f.name as lx")
                              ->buildSql();
        $buildSql = M("cases")->table($buildSql)->alias("t1")
                              ->field("t1.id as id,t1.on, t1.title,t1.mianji,t1.fg,t1.jiage,t1.lx,t1.time,d.img,d.img_host,d.img_path,t1.hx,t1.dname,t1.isdelete")
                              ->join("LEFT JOIN qz_case_img as d on d.caseid = t1.id")
                              ->order("d.img_on desc,d.px")
                              ->buildSql();
        return M("cases")->table($buildSql)->alias("t2")
                              ->group("t2.id")->order("t2.id desc")
                              ->select();
    }


    /**
     * 根据装修公司编号获取最新的案例
     * @param  string $comid [公司编号]
     * @param  string $cs    [所在城市]
     * @param  string $classid    [案例类型]
     * @param  string $on    [审核状态]
     * @return [type]        [description]
     */
    public function getThreedListByComId($pageIndex = 0,$pageCount = 10,$comid ='',$cs ='',$classid='',$type='',$on = "")
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);
        $map = array(
                "cs"=>array("EQ",$cs),
                "uid"=>array("EQ",$comid),
                "isdelete"=>array("IN",array(1,3))
                     );
        if(!empty($on)){
            $map["on"] = array("EQ",$on);
        }

        if(!empty($classid)){
            $map["classid"] = array("IN",$classid);
        }

        if(!empty($classid) && !empty($type)){
            if( $classid == 2){
                $map["leixing"] = array("EQ",$type);
            }else{
                $map["huxing"] = array("EQ",$type);
            }
        }
        $buildSql = M("cases")->where($map)
                              ->order("id desc")
                              ->limit($pageIndex.",".$pageCount)
                              ->field("id,title,mianji,fengge,time,userid,status,isdelete,on")
                              ->buildSql();

        return M("cases")->table($buildSql)->alias("t1")
                              ->distinct(true)
                              ->field("t1.*,y.status ystatus,y.reason yreason,c.name as fg,d.name as dname")
                              ->join("INNER JOIN qz_fengge as c on c.id = t1.fengge")
                              ->join("LEFT JOIN qz_case_review as y on y.caseid = t1.id")
                              ->join("LEFT JOIN  qz_user as d on d.id = t1.userid")
                              ->order("t1.id desc")
                              ->select();
    }

    /**
     * 获取设计师的最新2个案例信息
     * @param  string $ids [description]
     * @return [type]      [description]
     */
    public function getDesinerCase($ids=""){
        $map = array(
                "userid"=>array("IN",$ids),
                "isdelete"=>array("EQ",1),
                //因为考虑到数据量的问题,添加一个最新案例的最早时间
                "time"=>array("EGT",strtotime("-1 month"))
                     );
        //1.查询出用户的案例编号
        $buildSql = M("cases")->where($map)
                         ->buildSql();
        //2.条件子查询,查询每个设计师的最新2个案例
        $map["_string"] = "a.userid = b.userid AND a.id < b.id ";
        $sql = M("cases")->where($map)->alias("b")
                         ->field("count(id)")
                         ->buildSql();
        $map = array(
                $sql=>array("LT",2)
                     );
        $buildSql  = M("cases")->table($buildSql)->alias("a")
                               ->field("a.id,a.userid ")
                               ->where($map)->buildSql();
        //3.查询出案例的封面图片
        $buildSql = M("case_img")->alias("m")
                     ->join("INNER JOIN $buildSql as t on t.id = m.caseid")
                     ->order("m.caseid desc,m.img_on DESC")
                     ->field("m.*,t.userid")
                     ->buildSql();
        return M("case_img")->table($buildSql)->alias("t1")
                            ->group("caseid")
                            ->select();

    }

    /**
     * 获取装修公司上传案例的图片类型获取案例信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getCasesClass($id='',$cs = '',$classid = 1){
        $map = array(
                "uid"=>array("EQ",$id),
                "cs"=>array("EQ",$cs),
                "classid"=>array("EQ",$classid),
                "on"=>array("EQ",1)
                     );
        switch ($classid) {
            case '1':
            case '3':
                $map["b.id"] = array("BETWEEN",array(10,15));
                $buildSql = M("cases")->where($map)->alias("a")
                                      ->join("INNER join qz_huxing as b on a.huxing = b.id")
                                      ->field("a.classid, a.id,b.`name`,a.huxing,count(a.id) as count")
                                      ->group("a.huxing")
                                      ->buildSql();
                break;
            case '2':
                $map["leixing"] = array("EXP","IS NOT NULL");
                $buildSql = M("cases")->where($map)->alias("a")
                                      ->join("INNER join qz_leixing as b on a.leixing = b.id")
                                      ->field("a.classid, a.id,b.`name`,a.leixing,count(a.id) as count")
                                      ->group("a.leixing")
                                      ->buildSql();
                break;
        }
        return M("cases")->table($buildSql)->alias("t")->select();
    }
     /**
     * 获取设计师的相关案例作品数
     * @param  [type] $id [设计师编号]
     * @return [type]     [description]
     */
    public function getDesingerCaseInfoCount($id){
        $map = array(
                "userid"=>array('EQ',$id),
                "isdelete"=>array("EQ",1)
                     );
        return M("cases") ->where($map)
                          ->count();
    }

    /**
     * 获取设计师的相关案例作品
     * @param  [type] $id [设计师编号]
     * @return [type]     [description]
     */
    public function getDesingerCaseInfo($id,$pageIndex=1,$pageCount=10)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $map = array(
                "userid"=>array('EQ',$id),
                "isdelete"=>array("EQ",1)
                     );
        //获取设计师的案例信息
        $buildSql = M("cases")->where($map)
                              ->limit($pageIndex.",".$pageCount)
                              ->order("time desc")
                              ->field("`id`,time,mianji,title,zaojia,fengge,shi,ting,wei,huxing")
                              ->buildSql();
        //获取案例的其他信息
        $buildSql = M("cases")->table($buildSql)->alias("t2")
                              ->join("INNER JOIN qz_fengge as c on c.id = t2.fengge")
                              ->join("INNER JOIN qz_jiage  as d on d.id = t2.zaojia")
                              ->join("LEFT JOIN  qz_huxing as e on e.id = t2.huxing")
                              ->field("t2.id as cid,t2.time,t2.mianji,t2.title,t2.shi,t2.ting,t2.wei,c.name as fg,d.name as zj,e.name as huxing")
                              ->buildSql();
        // //获取案例的封面信息
        $buildSql = M("case_img")->alias("a")
                            ->join("INNER JOIN".$buildSql." as t on a.caseid = t.cid")
                            ->order("img_on desc")
                            ->buildSql();

        return M("case_img")->table($buildSql)->alias("t1")
                            ->group("t1.caseid")
                            ->order("t1.time desc")
                            ->select();

    }

    /**
     * 根据编号获取案例的单独信息
     * @return [type] [description]
     */
    public function getSingleById($id,$comid ="",$userid =""){
        $map = array(
                "a.id"=>array("EQ",$id)
                     );
        if(!empty($comid)){
            $map["a.uid"] = array("EQ",$comid);
        }

        if(!empty($userid)){
            $map["a.userid"] = array("EQ",$userid);
        }
        return M("cases")->where($map)->alias("a")
                         ->join("INNER JOIN qz_quyu as b on b.cid = a.cs")
                         ->field("a.*,b.bm")
                         ->find();
    }

    /**
     * 添加案例
     */
    public function addCase($data){
        //M("cases")->add($data);
        //$this->relationXiaoQuByCaseName($data['title'],$lastid,$data['cs']);
        return M("cases")->add($data);
    }

    /**
     * 编辑案例
     * @return [type] [description]
     */
    public function editCase($id,$data){
        $map = array(
                "id"=>array("EQ",$id)
                     );
        //案例匹配相关
        /*
        $res = M('cases')->field('title,cs')->where($map)->find();
        if($data['isdelete'] != '1'){
            $this->unrelationXiaoQuByCaseName($res['title'],$id,$res['cs']);
        }else if($res['title'] != $data['title']){
            $this->unrelationXiaoQuByCaseName($res['title'],$id,$res['cs']);
            $this->relationXiaoQuByCaseName($data['title'],$id,$data['cs']);
        } */
        return M("cases")->where($map)->save($data);
    }

    /**
     * 获取案例的基本信息及图片
     * @param  [type]  $id         [案例编号]
     * @param  string  $comid      [公司编号]
     * @param  boolean $isdesinger [是否是设计师]
     * @return [type]              [description]
     */
    public function getCaseInfoAndImgs($id,$comid="",$isdesinger = false){
        $map = array(
                "a.id" => array("EQ",$id),
                "a.isdelete"=>array("IN",array(1,3))
                     );
        if(!empty($comid)){
            $map["a.uid"] = array("EQ",$comid);
        }

        if($isdesinger){
            $map["isdelete"] = array("EQ",1);
        }

        $buildSql = M("cases")->where($map)->alias("a")
                              ->join("LEFT JOIN qz_fengge as c on c.id = a.fengge")
                              ->join("INNER JOIN qz_jiage as d on d.id = a.zaojia")
                              ->join("LEFT JOIN qz_leixing as e on e.id = a.leixing")
                              ->join("LEFT JOIN qz_fangshi as f on f.id = a.fangshi")
                              ->join("LEFT JOIN qz_huxing as h on h.id = a.huxing")
                              ->field("a.*,c.name as fg,d.name as jiage,e.name as lx,f.name as fs,h.name as hx")
                              ->buildSql();
        return M("cases")->table($buildSql)->alias("t")
                         ->join("INNER JOIN qz_case_img as i on i.caseid = t.id")
                         ->field("t.*,i.img,i.img_host,i.img_path,i.img_on,i.id as img_id")
                         ->order("i.px")
                         ->select();
    }

    /**
     * 获取精彩推荐的文章信息
     * @param  [type] $cs [所在城市]
     * @param  [type] $classid [案例类别]
     * @return [type]     [description]
     */
    public function getRecommendCases($cs="",$classid=""){
        //案例类别取 家装和公装
        $map = array(
                "classid"=>array("IN",array(1,2))
                     );
        //如果有城市编号,取当前城市
        if(!empty($cs)){
            $map["cs"] = array("EQ",$cs);
        }

        if(!empty($classid)){
            $map["classid"] = array("EQ",$classid);
        }
        //1.取出最新的前100个案例
        $buildSql = M("cases")->where($map)
                             ->limit(100)
                             ->order("time desc")
                             ->field("id,cs,title,fengge")
                             ->buildSql();
        //2.取出关联的案例图片信息
        $buildSql = M("cases")->table($buildSql)->alias("t")
                             ->join("INNER JOIN qz_case_img as b on t.id = b.caseid")
                             ->field("t.*,b.img_path,b.img,b.img_host")
                             ->order("b.img_on desc,px")
                             ->buildSql();
        return M("cases")->table($buildSql)->alias("t1")
                        ->join("INNER JOIN qz_quyu as c on c.cid = t1.cs")
                        ->join("INNER JOIN qz_fengge as d on d.id = t1.fengge")
                        ->group("t1.id")
                        ->field("t1.*,c.bm,d.name as fengge")
                        ->select();

    }

    /**
     *  获取设计师作品列表数量
     * @param  [type] $id    [用户编号]
     * @param  string $comid [装修公司编号]
     * @return [type]        [description]
     */
    public function getDesinerCaseListCount($id,$comid = ""){
        $map = array(
                "userid"=>array("EQ",$id),
                "uid"=>array(
                        array("EQ",""),
                        array("EXP","is null"),
                        "OR"
                             ),
                "isdelete"=>'1'
                     );
        if(!empty($comid)){
            $map["uid"] = array(
                            array("EQ",""),
                            array("EXP","is null"),
                            array("EQ",$comid),
                            "OR"
                        );
        }

        return M("cases")->where($map)
                         ->count();
    }

   /**
    * 获取设计师作品列表
    * @param  [type] $id [用户编号]
    * @return [type]     [description]
    */
    public function getDesinerCaseList($id,$comid = "",$pageIndex,$pageCount)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $map = array(
                "userid"=>array("EQ",$id),
                "uid"=>array(
                        array("EQ",""),
                        array("EXP","is null"),
                        "OR"
                        ),
                "isdelete"=>array("EQ",1)
                     );
        if(!empty($comid)){
            $map["uid"] = array(
                    array("EQ",""),
                    array("EXP","is null"),
                    array("EQ",$comid),
                    "OR"
                );
        }
        //1.查询相关的案例信息
        $buildSql = M("cases")->where($map)
                              ->order("id desc")
                              ->limit($pageIndex.",".$pageCount)
                              ->field("title,id,time,cs,fengge,on,identity")
                              ->buildSql();
       //2.查询出相关的案例的其他信息
       $buildSql = M("cases")->table($buildSql)->alias("t")
                             ->join("INNER JOIN qz_quyu as b on b.cid = t.cs")
                             ->join("INNER JOIN qz_fengge as c on c.id = t.fengge")
                             ->field("t.title,t.id,t.time,b.bm,c.name as fengge,t.on,t.identity")
                             ->buildSql();
        //3.取出相关的图片信息
         $buildSql = M("cases")->table($buildSql)->alias("t1")
                               ->join("LEFT JOIN qz_case_img as img on img.caseid = t1.id")
                               ->order("img_on desc,px")
                               ->field("t1.*,img_path,img,img_host")
                               ->buildSql();
        return  M("cases")->table($buildSql)->alias("t1")
                          ->order("t1.id desc")
                          ->group("t1.id")
                          ->select();
    }

    /**
     * 获取移动版的案例详情信息
     * @param  [type] $id [案例编号]
     * @param  [type] $cs [所在城市]
     * @return [type]     [description]
     */
    public function getMobileCaseInfo($id,$cs){
        $map = array(
                "a.cs"=>array("EQ",$cs),
                "a.id"=>array("EQ",$id),
                "a.on"=>array("EQ",1)
                     );
        //1.获取案例的信息
        $buildSql = M("cases")->where($map)->alias("a")
                   ->field("a.title,a.id,a.mianji,a.classid,a.shi,a.ting,a.wei,b.name as dname,b.id as uid,c.name as fengge,d.name as huxing")
                   ->join("INNER JOIN qz_user as b on b.id = a.userid")
                   ->join("INNER JOIN qz_fengge as c on c.id = a.fengge")
                   ->join("LEFT JOIN qz_huxing as d on d.id = a.huxing")
                   ->buildSql();
        //2.查询案例的相关图片
       return M("cases")->table($buildSql)->alias("t")
                              ->join("INNER JOIN qz_case_img as e on e.caseid = t.id")
                              ->order("img_on desc,e.px")
                              ->field("t.*,e.img,e.img_host,e.img_path,e.px")
                              ->select();
    }


    //获取相关美图
    public function getRelatedCase($map){
        $where = array(
            "name" => array("EQ",$map),
        );
        $fengge = M("fengge")->field('id,name')->where($where)->find();
        unset($map);
        $map['c.fengge'] = $fengge['id'];
        $result = M("cases")->alias("c")->field('c.id,c.title,c.cs,q.cname,q.bm')
        ->join("INNER JOIN qz_quyu as q on c.cs = q.cid")
        ->where($map)->limit('0,9')->order("c.id desc")->select();

        foreach ($result as $key => $value) {
            $submap['caseid'] = $value['id'];
            $imgList = M("case_img")->field('id,img,img_path')->where($submap)->order("px desc")->select();
            //dump()
            $result[$key]['img_path'] = $imgList['0']['img_path'];
            $result[$key]['img'] = $imgList['0']['img'];
            $result[$key]['count'] = count($imgList);
            unset($imgList);
        }
        return $result;
    }


   /**
    * 获取城市的最新案例
    * @param  [type] $cs    [当前城市编号]
    * @param  [type] $limit [获取数量]
    * @param  [type] $max   [最大数量]
    * @return [type]        [description]
    */
    public function getNewCasesWithCity($cs,$limit,$max){
        $map = array(
                "cs"=>array("EQ",$cs),
                "uon"=>array("EQ",2),
                "on"=>array("EQ",1),
                "classid"=>array("IN",array(1,2)),
                "isdelete"=>array("EQ",1)
                     );
        //1.查询该城市的上传案例的公司编号
        $buildSql = M("cases")->where($map)->order("time desc")
                              ->buildSql();
        $result = M("cases")->table($buildSql)->alias("t")->group("uid")
                              ->field("t.uid")
                              ->order("time desc")
                              ->select();
        if(count($result) > 0){
            //2.取出每个公司的前几套案例
            $cases = array();
            foreach ($result as $key => $value) {
                $map = array(
                        "uid"=>array("EQ",$value['uid']),
                        "classid"=>array("IN",array(1,2))
                             );
                $buildSql = M("cases")->where($map)->order("time desc")
                                ->limit($max)
                                ->buildSql();
                $res =  M("cases")->table($buildSql)->alias("t")
                                  ->join("inner join qz_quyu as d on d.cid = t.cs")
                                  ->join("inner join qz_fengge as e on e.id = t.fengge")
                                  ->field("t.*,d.cname,d.bm,e.name as fname")
                                  ->select();
                if(count($res) > 0){
                    $cases = array_merge($cases,$res);
                }
            }
            //3.如果案例数不足的,取非会员的案例补充
            if(count($cases) < $limit){
                $map = array(
                    "classid"=>array("IN",array(1,2)),
                    "isdelete"=>array("EQ",1),
                    "cs"=>array("EQ",$cs),
                    "uon"=>array("NEQ",2),
                    "on"=>array("EQ",1)
                             );
                $offset = $limit -count($cases);
                $buildSql = M("cases")->where($map)->order("time desc")
                                 ->limit($offset)
                                 ->buildSql();

                $res =  M("cases")->table($buildSql)->alias("t")
                                  ->join("inner join qz_quyu as d on d.cid = t.cs")
                                  ->join("inner join qz_fengge as e on e.id = t.fengge")
                                  ->field("t.*,d.cname,d.bm,e.name as fname")
                                  ->select();
                if(count($res) > 0){
                    $cases = array_merge($cases,$res);
                }
            }
            //3.将取出的案例排序
            $edition = array();
            foreach ($cases as $key => $value) {
                $edition[] = $value["time"];
            }
            array_multisort($edition, SORT_DESC, $cases);
            $cases = array_slice($cases, 0,$limit);
            foreach ($cases as $key => $value) {
                if(!array_key_exists($value["id"], $cases)){
                    $cases[$value["id"]] = $value;
                    $ids[] = $value["id"];
                    unset($cases[$key]);
                }
            }
            //3.获取案例的图片信息
            $map = array(
                        "caseid"=>array("IN",$ids)
                             );
            $buildSql = M("case_img")->where($map)->order("img_on desc,px")->buildSql();
            $imgs = M("case_img")->table($buildSql)->alias("t")->group("caseid")
                                    ->field("t.caseid,t.img,t.img_host,t.img_path")
                                    ->select();
            foreach ($imgs as $key => $value) {
                $cases[$value["caseid"]]["img"] = $value["img"];
                $cases[$value["caseid"]]["img_host"] = $value["img_host"];
                $cases[$value["caseid"]]["img_path"] = $value["img_path"];
            }
        }
        return $cases;
    }

    /**
     * 获取相同类型的第一个/最后一个案例案例信息
     * @param  [type] $caseid [description]
     * @param  string $class  [description]
     * @return [type]         [description]
     */
    public function getFirstCaseInfo($class="",$cs="",$order = "asc"){
        $map = array(
                "on"=>array("eq",1),
                "cs"=>array("EQ",$cs)
                     );
        if(!empty($class)){
            $map["classid"] = array("EQ",$class);
        }

        M("cases")->where($map)->limit(1);
        if($order == "asc"){
            M("cases")->order("id");
        }else{
            M("cases")->order("id desc");
        }
        $buildSql = M("cases")->buildSql();
        $buildSql = M("cases")->table($buildSql)->alias("t")
                          ->join("INNER JOIN qz_case_img as b on t.id = b.caseid")
                          ->field("t.*,b.img_path,b.img,b.img_host,b.img_on")
                          ->order("img_on desc,px")->buildSql();
        return  M("cases")->table($buildSql)->alias("t1")
                          ->limit(1)->find();

    }

    /**
     * 新增案例时通过小区和案例id进行关联
     * @param  [type]  $caseid   [案例ID]
     * @param  [type]  $xiaoquid [小区ID]
     * @return [type]            [description]
     */
    public function relationXiaoQuAdd($caseid,$xiaoquid){
        if((!empty($xiaoquid))&&$xiaoquid!='default'){
            //小区案例数目增加1
            M('xiaoqu')->where(array('id'=>$xiaoquid))->setInc('casecount');
            //小区案例关联表进行关联
            $data['xiaoqu_id'] = $xiaoquid;
            $data['cases_id'] = $caseid;
            M('xiaoqu_relation')->add($data);
        }
    }

    /**
     * 编辑案例时通过小区和案例id进行关联
     * @param  [type]  $caseid   [案例ID]
     * @param  [type]  $xiaoquid [小区ID]
     * @return [type]            [description]
     */
    public function relationXiaoQuEdit($caseid,$xiaoquid){
        //小区id不为空有两种情况，1是没有动小区名字 2是动了小区名字后没有找到该小区,小区id默认为default，如果不为default则解除绑定
        if($xiaoquid != 'default'){
            if(empty($xiaoquid)){
                $result = M('xiaoqu_relation')->where(array('cases_id'=>$caseid))->find();
                $xqid = $result['xiaoqu_id'];
                if(!empty($xqid)){
                    M('xiaoqu_relation')->where(array('cases_id'=>$caseid))->delete();
                    M('xiaoqu')->where(array('id'=>$xqid))->setDec('casecount');
                }
            }else{
                $result = M('xiaoqu_relation')->where(array('cases_id'=>$caseid))->find();
                $xqid = $result['xiaoqu_id'];
                if(!empty($xqid)){
                    M('xiaoqu_relation')->where(array('cases_id'=>$caseid))->delete();
                    M('xiaoqu')->where(array('id'=>$xqid))->setDec('casecount');
                }
                $this->relationXiaoQuAdd($caseid,$xiaoquid);
            }
        }
    }

    /**
     * 删除案例时进行小区解绑
     * @param  [type]  $caseid   [案例ID]
     * @return [type]            [description]
     */
    public function relationXiaoQuDel($caseid){
        $result = M('xiaoqu_relation')->where(array('cases_id'=>$caseid))->find();
        $xiaoquid = $result['xiaoqu_id'];
        M('xiaoqu_relation')->where(array('cases_id'=>$caseid))->delete();
        M('xiaoqu')->where(array('id'=>$xiaoquid))->setDec('casecount');
    }
    /**
     * 恢复案例时进行小区绑定
     * @param  [type]  $caseid   [案例ID]
     * @return [type]            [description]
     */
    public function relationXiaoQuReset($caseid){
        $result = M('cases')->where(array('id'=>$caseid))->find();
        $map['cs'] = $result['cs'];
        $map['name'] = $result['title'];
        $xiaoqu = M('xiaoqu')->field('id')->where($map)->find();
        if(!empty($xiaoqu)){
            $data['cases_id'] = $caseid;
            $data['xiaoqu_id'] = $xiaoqu['id'];
            M('xiaoqu_relation')->add($data);
            M('xiaoqu')->where(array('id'=>$xiaoquid))->setInc('casecount');
        }
    }

    //审核不通过原因中文化
    //1广告，2联系方式，3LOGO或水印，4涉黄，5涉暴，6涉及国家机关或领导人',
    public function changReasonToCN($newarray){
        $cnwords = array();
        foreach ($newarray as $key => $value){
            $newword = '';
            if($value['status'] == 3){
                if(!empty($value['ystatus']) && isset($value['ystatus'])){
                    $cnwords = explode(',',$value['ystatus']);
                    for($i=0;$i<count($cnwords);$i++){
                        if($i==0){
                            if($cnwords[$i] == 1){
                                $newword = '含有广告';
                            }elseif($cnwords[$i] == 2){
                                $newword = '含有联系方式';
                            }elseif($cnwords[$i] == 3){
                                $newword = '禁用的LOGO或水印';
                            }elseif($cnwords[$i] == 4){
                                $newword = '涉黄';
                            }elseif($cnwords[$i] == 5){
                                $newword = '涉暴';
                            }elseif($cnwords[$i] == 6){
                                $newword = '涉及国家机关或领导人';
                            }
                        }else{
                            if($cnwords[$i] == 1){
                                $newword = $newword. ',含有广告';
                            }elseif($cnwords[$i] == 2){
                                $newword = $newword. ',含有联系方式';
                            }elseif($cnwords[$i] == 3){
                                $newword = $newword. ',禁用的LOGO或水印';
                            }elseif($cnwords[$i] == 4){
                                $newword = $newword. ',涉黄';
                            }elseif($cnwords[$i] == 5){
                                $newword = $newword. ',涉暴';
                            }elseif($cnwords[$i] == 6){
                                $newword = $newword. ',涉及国家机关或领导人';
                            }
                        }
                    }
                }
                if(!empty($value['yreason']) && isset($value['yreason'])){
                    if($newword){
                        $newword = $newword . ','.$value['yreason'];
                    }else{
                        $newword = $value['yreason'];
                    }
                }
            }
            $newarray[$key]['reasoncn'] = $newword;
        }
        return $newarray;
    }

    /**
     *
     *删除cases表中的数据
     *删除case_img 表中的数据
     *删除case_review 表中的数据
     */
    public function deleteCaseById($id){
        $res = M('cases')->where(array('id' => $id))->delete();
        if($res){
            M('case_img')->where(array('caseid' => $id))->delete();
            M('case_review')->where(array('caseid' => $id))->delete();
            return array('info' => '操作成功','status' => 1);
        }else{
            return array('info' => '操作失败','status' => 0);
        }
    }




}