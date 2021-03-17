<?php

namespace Home\Model;
Use Think\Model;

/**
*
*/
class CommentModel extends Model
{
    protected $autoCheckFields = false;

    /**
     * @param  array            $map             查询条件数组
     * @return string           $result          查询结果
     */
    public function getCommentAllNum($map)
    {
        //公司
        if(!empty($map["u.company"])){
            $company = $map["u.company"];
        }
        unset($map["u.company"]);
        //排序
        if($map['order'] == 1){
            $order = 'c.time DESC';
        }else{
           $order = 'c.time ASC'; 
        }
        unset($map['order']);
        if(!empty($company)){
            $result = M("comment")->alias("c")
                            ->join("qz_user as u on c.comid = u.id and (u.id = '$company' or u.user = '$company')")
                            ->where($map)
                            ->count();
        }else{
            $result = M("comment")->alias("c")->join("qz_user as u on c.comid = u.id")->where($map)->count();
        }
        
        return $result;
    }

    /**
     * @param  array            $map             修改讲师的ID
     * @return string           $result          查询结果
     */
    public function getCommentList($map,$start,$end)
    {
        //公司
        if(!empty($map["u.company"])){
            $company = $map["u.company"];
        }
        unset($map["u.company"]);
        //排序
        if($map['order'] == 1){
            $order = 'c.time DESC';
        }else{
           $order = 'c.time ASC'; 
        }
        unset($map['order']);
        if(!empty($company)){
            $result = M("comment")->alias("c")
                            ->join("qz_user as u on c.comid = u.id and (u.id = '$company' or u.user = '$company')")
                            ->join("qz_quyu as q on c.cs = q.cid")
                            ->where($map)
                            ->field("c.*,u.on as companytype,u.user as companyname,q.cname as cname,q.bm")
                            ->limit($start.",".$end)
                            ->order($order)
                            ->select();
        }else{
            $result = M("comment")->alias("c")
                                ->join("qz_user as u on c.comid = u.id")
                                ->join("qz_quyu as q on c.cs = q.cid")
                                ->where($map)
                                ->field("c.*,u.on as companytype,u.user as companyname,q.cname as cname,q.bm")
                                ->limit($start.",".$end)
                                ->order($order)
                                ->select();
        }
//echo M()->getLastSql();die;
        return $result;
    }

    /**
     *  推荐/取消推荐
     * @param  array            $map             查询条件数组
     * @param  data             $data            修改的推荐状态数组
     * @return string           $result          查询结果
     */
    public function setRecommend($map,$data)
    {
        return M("comment")->where($map)->save($data);
    }

    /**
     *  审核/取消审核
     * @param  array            $map             查询条件数组
     * @param  data             $data            修改的审核状态数组
     * @return string           $result          查询结果
     */
    public function setVerify($map,$data)
    {
        return M("comment")->where($map)->save($data);
    }

    /**
     *  更新公司评论数
     *  $id 评论id
     */
    public function checkCompanyCountByCompanyId($map)
    {
        $where['id'] = $map;
        //获取所有评论对应的公司id
        $comments = $this->field('id,comid')->where($where)->select();
        //查询每个公司评论数
        $request = array();
        foreach ($comments as $key => $comment) {
            $w['comid'] = array('eq', $comment['comid']);
            $w['isveritfy'] = array('eq', 0);
            //查询对应公司的评论数
            $count = $this->field('count(id) as count')->where($w)->find();
            $comment_score = $this->getCompanyPingfen($comment['comid']); //获取装修公司评分
            $data = array(
                'comment_count' => $count['count'],
                'comment_score'=>$comment_score['score'],
            );
            //更新对应公司的评论数
            M('user_company')->where('userid = ' . $comment['comid'])->save($data);
        }
        return $request;
    }



    /**
     * 根据评论id获取评论图片
     * @param $id
     * @param $comid
     * @return mixed
     */
    public function getCommentImgsById($id,$comid){
        $map = array(
            "a.id"=>array("EQ",$id),
        );
        return M("comment")->where($map)->alias("a")
            ->join("LEFT JOIN qz_comment_img as img on img.commentid = a.id")
            ->field("img.imgurl")
            ->select();
    }

    public function getCommentInfo($id,$comid)
    {
        $map = array(
            "a.id"=>array("EQ",$id),
        );
        return M("comment")->where($map)->alias("a")
            ->field("a.id,a.name,a.text content,a.time")
            ->find();

    }


    //根据条件获取评论数量
    public function getCommentCountByMap($map)
    {
        if($map){
            return $this->where($map)->count();
        }else{
            return 0;
        }
    }

    //获取装修公司评分
    public function getCompanyPingfen($comid)
    {
        $map = [];
        $map['isveritfy'] = 0;
        $map['comid'] = $comid;
        return $this->field('avg(if(fw != 0,((fw+sg+sj)/3),null)) as score')->where($map)->find();
    }

    //获取单条评价的基础信息
    public function getCommentInfoById($id)
    {
        if($id){
            $map = [];
            $map['id'] = $id;
            return $this->where($map)->find();
        }else{
            return false;
        }
    }

    public function getCommentById($id)
    {
        if($id){
            $map = [];
            if(is_array($id)){
                $map['c.id'] = array('in',$id);
            }else{
                $map['c.id'] = $id;
            }
            return $this->where($map)->alias('c')
                ->join("qz_ucenter_profile as u on c.userid = u.uuid")
                ->select();
        }else{
            return false;
        }
    }

    //删除评价
    public function deleteCommentById($id){
        if($id){
            $map = [];
            $map['id'] = $id;
            return $this->where($map)->delete();
        }else{
            return false;
        }
    }

    //删除评价图片
    public function deleteCommentImgs($id)
    {
        if($id){
            $map = [];
            $map['commentid'] = $id;
            return M('comment_img')->where($map)->delete();
        }else{
            return false;
        }
    }

}