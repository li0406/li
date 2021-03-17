<?php
namespace Home\Model;

Use Think\Model;

class FriendlinkModel extends Model{
    protected $autoCheckFields = false;
    /**
     * 添加友情链接
     * @param [type] $data [description]
     */
    public function addLink($data){
        return M("friend_link")->add($data);
    }

    /**
     * 批量添加数据
     */
    public function addAllLink($data){
        return M("friend_link")->addAll($data);
    }

    /**
     * 获取友情链接列表数量
     * @return [type] [description]
     */
    public function getLinksListCount($cs="",$type="",$status="",$url="",$linkpage="",$monitor_status=0){
        if(!empty($cs)){
            $map["cs"] = array(array("EQ",$cs),array("NEQ",""));
        }else{
            $map["cs"] = array("NEQ","");
        }

        if(!empty($type)){
            $map["show_class"] = array("EQ",$type);
        }

        if ($status !== '') {
            if($status == 0 || $status == 1){
                $map["show_on"] = array("EQ",$status);
            }
        }

        if(!empty($url)){
            $map["link_url"] = array("LIKE","%$url%");
        }

        if(!empty($linkpage)){
            $map["link_page"] = array("EQ",$linkpage);
        }

        if (!empty($monitor_status)) {
            $map['monitor_status'] = intval($monitor_status);
        }
        return M("friend_link")->where($map)->count();
    }

    /**
     * 获取友情链接列表
     * @return [type] [description]
     */
    public function getLinksList($cs="",$type="",$pageIndex,$pageCount,$status,$url,$linkpage,$monitor_status=0){
        if(!empty($cs)){
            $map["a.cs"] = array(array("EQ",$cs),array("NEQ",""));
        }else{
            $map["a.cs"] = array("NEQ","");
        }
        if(!empty($type)){
            $map["a.show_class"] = array("EQ",$type);
        }
        if(!empty($url)){
            $map["a.link_url"] = array("LIKE","%$url%");
        }

        if ($status !== '') {
            if($status == 0 || $status == 1){
                $map["a.show_on"] = array("EQ",$status);
            }
        }

        if(!empty($linkpage)){
            $map["a.link_page"] = array("EQ",$linkpage);
        }

        if (!empty($monitor_status)) {
            $map['a.monitor_status'] = intval($monitor_status);
        }

        $build = M("friend_link")->alias("a")
                                 ->where($map)
                                 ->limit($pageIndex.",".$pageCount)
                                 ->order("a.link_id desc,a.show_on desc")
                                 ->buildSql();
        return M()->table($build)->alias('z')
                                 ->field("z.*,b.cname,b.bm,GROUP_CONCAT(c.cname) as gname, u.name AS adminuser_name")
                                 ->join("LEFT JOIN qz_quyu as b on b.cid = z.cs")
                                 ->join("LEFT JOIN qz_quyu as c on find_in_set(c.cid,z.show_cs)")
                                 ->join("LEFT JOIN qz_adminuser as u on u.id = z.adminuser_id")
                                 ->group("z.link_id")
                                 ->order("z.link_id DESC")
                                 ->select();
    }


    /**
     * 获取友情链接列表所有数据
     * @param  string $cs 城市id
     * @param  int    $type 链接类型
     * @param  string $linkpage 链接页面
     * @param  int    $status 推荐状态
     * @param  string $url 链接地址
     * @return array
    */
    public function getAllLinks($cs='', $type='', $linkpage='', $status='', $url='')
    {
        if(!empty($cs)){
            $map["a.cs"] = array(array("EQ",$cs),array("NEQ",""));
        }else{
            $map["a.cs"] = array("NEQ","");
        }
        if(!empty($type)){
            $map["a.show_class"] = array("EQ",$type);
        }
        if(!empty($linkpage)){
            $map["a.link_page"] = array("EQ",$linkpage);
        }
        if ($status !== '') {
            if($status == 0 || $status == 1){
                $map["a.show_on"] = array("EQ",$status);
            }
        }
        if(!empty($url)){
            $map["a.link_url"] = array("LIKE","%$url%");
        }
        $build = M("friend_link")->alias("a")
                                 ->where($map)
                                 ->order("a.link_id desc,a.show_on desc")
                                 ->buildSql();
        return M()->table($build)->alias('z')
                                 ->field("b.cname,
                                          flc.link_page_name,
                                          CASE z.show_class WHEN 1 THEN '友情链接' WHEN 2 THEN '热门城市' WHEN 3 THEN '热门标签'WHEN 4 THEN  '附近城市' WHEN 5 THEN '区域装修公司' ELSE '未知' END AS show_class,
                                          z.link_name,
                                          z.link_url,
                                          CASE z.show_on WHEN 0 THEN '无效' WHEN 1 THEN '有效' ELSE '未知' END as show_on,
                                          z.addtime,
                                          CONCAT('http://',b.bm,'.qizuang.com') as city_url"
                                          )
                                 ->join("LEFT JOIN qz_quyu as b on b.cid = z.cs")
                                 ->join("LEFT JOIN qz_quyu as c on find_in_set(c.cid,z.show_cs)")
                                 ->join("LEFT JOIN qz_friend_link_category as flc on flc.link_page = z.link_page")
                                 ->group("z.link_id")
                                 ->order("z.link_id DESC")
                                 ->select();
    }

    /**
     * 根据城市编号查询链接信息
     * @return [type] [description]
     */
    public function getLinksInfoByCity($cs,$type){
        $map = array(
                "cs"=>array("EQ",$cs),
                "show_class"=>array("EQ",$type)
                    );
        return M("friend_link")->where($map)->select();
    }

    /**
     * 编辑链接
     * @return [type] [description]
     */
    public function editLink($id,$data){
        $map = array(
                "link_id"=>array("EQ",$id)
                     );
        return M("friend_link")->where($map)->save($data);
    }




    /**
     * 删除链接
     * @return [type] [description]
     */
    public function remove($id){
        $map = array(
            "link_id"=>array("EQ",$id)
        );
        return M("friend_link")->where($map)->delete();
    }

    /**
     * 根据编号查询链接信息
     * @return [type] [description]
     */
    public function getLinksInfoById($id){
        $map = array(
                "link_id"=>array("EQ",$id)
                    );
        return M("friend_link")->where($map)->alias("a")
                               ->join("INNER JOIN qz_quyu as b on b.cid = a.cs")
                               ->field("a.*,b.cname")
                               ->find();
    }

    /**
     * 通过链接地址和类型获取链接
     * @param  string  $link_url   链接地址
     * @param  integer $show_class 类型
     * @return array
     */
    public function getFriendLinkByLinkUrl($link_url = '', $show_class = 1)
    {
        if (empty($link_url)) {
            return false;
        }
        $map['link_url'] = $link_url;
        if (!empty($show_class)) {
            $map['show_class'] = $show_class;
        }
        return M('friend_link')->where($map)->select();
    }

    /**
     * 通过链接名称和类型获取链接
     * @param  string  $link_name  名称
     * @param  integer $show_class 类型
     * @return array
     */
    public function getFriendLinkByLinkName($link_name = '', $show_class = 1)
    {
        if (empty($link_name)) {
            return false;
        }
        $map['link_name'] = $link_name;
        if (!empty($show_class)) {
            $map['show_class'] = $show_class;
        }
        return M('friend_link')->where($map)->select();
    }

    /**
     * 通过域名和城市获取友链
     * @param  string  $domain     域名
     * @param  string  $cs         城市
     * @param  integer $show_class 类型
     * @return
     */
    public function getFriendLinkByDomainAndCs($domain = '', $cs = '', $show_class = 1)
    {
        if (empty($domain)) {
            return false;
        }
        $map['link_url'] = array('LIKE', '%' . $domain . '%');
        if (!empty($cs)) {
            $map['cs'] = $cs;
        }
        if (!empty($show_class)) {
            $map['show_class'] = $show_class;
        }
        return M('friend_link')->where($map)->select();
    }
}