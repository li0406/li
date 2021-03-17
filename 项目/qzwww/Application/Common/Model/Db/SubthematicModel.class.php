<?php
/**分站专题页*/
namespace Common\Model\Db;
use Think\Model;
class SubthematicModel extends Model
{
    protected $tableName = 'subthematic';

    public function getInfoByUrl($cs,$url){
        $where['a.cs'] = ['eq',$cs];
        $where['a.url'] = ['eq',$url];
        return $this->alias('a')
            ->field('a.id,a.tagid,a.sub_tagid,a.title,a.description,a.keywords,a.synopsis,t.name as tag_name,a.url,c.name as sub_tag_name')
            ->join('left join qz_tags as t on t.id = a.tagid')
            ->join('left join qz_sub_tag as c on c.id = a.sub_tagid')
            ->where($where)
            ->find();
    }


    public function getCompanyList($id,$limit=8){
        $where['sc.subthematic_id'] = ['eq',$id];
        return M('subthematic_company')->alias('sc')
            ->join('qz_user as u on u.id = sc.company_id')
            ->join('qz_user_company as uc on uc.userid = u.id')
            ->join('qz_area a on u.qx = a.qz_areaid')
            ->field('
            u.id,u.qc,u.jc,u.logo,a.qz_area,u.on ,uc.fake,
            case when u.on=2  then 1 when u.on=-1 then 2 when u.on=0 then 3 else 4 end onorder
            ')
            ->where($where)
            ->order('fake,onorder,u.id')
            ->limit($limit)
            ->select();
    }

    public function getCasesList($id,$limit=8){
        $where['sc.subthematic_id'] = ['eq',$id];
        $buildSql = M('subthematic_cases')->alias('sc')
            ->field('sc.case_id,c.mianji,c.fengge,c.huxing,u.jc,c.title,c.classid,c.leixing,c.time')
            ->join('qz_cases c on c.id = sc.case_id')
            ->join('qz_user u on u.id = c.uid')
            ->where($where)
            ->limit($limit)
            ->order('sc.case_id desc')
            ->buildSql();

        $buildSql2 = M('subthematic_cases')->table($buildSql)->alias('t')
            ->field('t.*,i.img,i.img_path,i.img_host,f.name as fg,h.name as hx,l.name as lx')
            ->join('left join qz_fengge f on f.id = t.fengge')
            ->join('left join qz_huxing h on h.id = t.huxing')
            ->join('left join qz_leixing l on l.id = t.leixing')
            ->join('left join qz_case_img i on i.caseid = t.case_id')
            ->order('t.case_id desc')
            ->buildSql();

        return M('subthematic_cases')->table($buildSql2)->alias('t2')
            ->field('t2.*')
            ->group('t2.case_id desc')
            ->select();
    }

    public function getZhuantiList($cs,$limit=10,$is_top){
        $where['a.cs'] = ['eq',$cs];
        if(!empty($is_top)){
            $where['a.is_top'] = ['eq',$is_top];
        }
        return  $this->alias('a')
            ->join('left join qz_tags as t on t.id = a.tagid')
            ->join('left join qz_sub_tag as c on c.id = a.sub_tagid')
            ->field('a.url,t.name as tag_name,c.name as sub_tag_name')
            ->where($where)->limit($limit)->order('a.id desc')->select();
    }

    public function getZhuantiListByTag($tags,$cs){
        $where['a.tagid'] = ['in',$tags];
        $where['a.cs'] = ['eq',$cs];
        if(!empty($tags)&&!empty($cs)){
            return $this->alias('a')->field('a.url,a.title')
                ->join('qz_tags as t on t.id = a.tagid')
                ->field('a.url,t.name as tag_name')
                ->where($where)
                ->select();
        }
    }

    public function getZhuantiListBySubTag($tags,$cs){
        $where['a.id'] = ['in',$tags];
        if(!empty($tags)&&!empty($cs)){
            return M('sub_tag')->alias('a')
                ->join('left join qz_subthematic as b on a.id = b.sub_tagid and b.cs='.$cs)
                ->field('b.url,a.name as tag_name')
                ->where($where)
                ->select();
        }
    }

    public function getSubTag($article_id){
        $map['a.article_id'] = ['eq',$article_id];
        $map['b.enabled'] = ['eq',1];
        return M('sub_tag_article')->alias('a')
            ->field('a.tag_id')
            ->join('qz_sub_tag as b on b.id = a.tag_id')
            ->where($map)
            ->order('a.created_at desc')
            ->select();
    }

    public function getSubTagByCompany($id,$limit=10){
        $map['a.company_id'] = ['eq',$id];
        $map['b.enabled'] = ['eq',1];

        return M('sub_tag_company')->alias('a')
            ->join('qz_sub_tag as b on b.id = a.tag_id')
            ->where($map)->field('tag_id')
            ->limit($limit)
            ->order('a.created_at desc')
            ->select();
    }

    public function getSubTagByCase($id,$limit=10){
        $map['a.case_id'] = ['eq',$id];
        $map['b.enabled'] = ['eq',1];
        return M('_sub_tag_case')->alias('a')
            ->field('a.tag_id')
            ->join('qz_sub_tag as b on b.id = a.tag_id')
            ->where($map)
            ->limit($limit)
            ->order('a.created_at desc')
            ->select();
    }

    public function getZhuantiListByCs($tags,$cs,$limit=10){
        if(!empty($tags)){
            $where['a.sub_tagid'] = ['not in',$tags];
        }
        $where['a.cs'] = ['eq',$cs];
        return $this->alias('a')
            ->join('qz_sub_tag as t on t.id = a.sub_tagid and t.enabled = 1')
            ->field('a.url,t.name as tag_name')
            ->where($where)
            ->limit($limit)
            ->select();
    }

    // 获取同省份下相同专题
    public function getSameCaseTag($cs, $title)
    {
        $map = [
            "cid" => array("eq", $cs)
        ];
        $buildSql = M("quyu")->where($map)->field("uid")->buildSql();

        $buildSql2 = M()->table($buildSql)->alias("t")
            ->field("t2.cid,t2.cname")
            ->join("qz_quyu t2 ON t2.uid = t.uid")
            ->buildSql();

        $map2 = [
            "a.cs" => array("neq", $cs),
        ];

        $list = $this->alias("a")
            ->where($map2)
            ->field("a.id,a.bm,a.url,t.cname,qt.`name` as tag_name,st.`name` as sub_tag_name")
            ->join("INNER JOIN" . $buildSql2 . " t on t.cid = a.cs")
            ->join("left join qz_tags qt on qt.id = a.tagid and qt.`name` like " . "'%" .$title . "'")
            ->join("left join qz_sub_tag as st on st.id = a.sub_tagid and st.`name` like " . "'%" . $title . "'")
            ->where("qt.id is not null or st.id is not null")
            ->limit(15)
            ->order("a.created_at, a.id")
            ->group("a.id")
            ->select();

        return $list ? $list : [];


    }

}