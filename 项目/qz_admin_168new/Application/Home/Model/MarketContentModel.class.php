<?php

namespace Home\Model;

use Think\Model;

/**
*   市场系统 内容管理
*/
class MarketContentModel extends Model
{

	protected $autoCheckFields = false;

	/**
	 * Gets the order number by Module.
	 *
	 * @param      <type>  $ids    The identifiers
	 */
	public function getOrderNumByModule($module){

		$map['src'] = array('EQ',$module);

		return M('content_order_stats')->where($map)->count();
	}

	/**
	 * Gets the order fendan number by module.
	 *
	 * @param      <type>  $module  The module
	 *
	 * @return     <type>  The order number by module.
	 */
	public function getFendanNumByModule($module){
		$map['o.on'] = array('EQ','4');
		$map['o.type_fw'] = array('EQ','1');
		$map['c.src'] = array('EQ',$module);

		return M('content_order_stats')->alias('c')
				->join('LEFT JOIN qz_orders as o on o.id = c.order_id')
				->where($map)
				->count();
	}

	//按时间取文章订单数发单数
	public function getOrdersNumByTime($map){
        unset($map['order']);
		$buidsql = M('www_article')->alias('a')
                  ->join('LEFT JOIN qz_www_article_class_rel as r on r.article_id = a.id')
				  ->field('a.id')
				  ->where($map)
				  ->buildSql();

		$buidsql = M()->table($buidsql)->alias('b')
				  ->field('b.*,count(co.content_id) as order_num,count(o.id) as fendans')
				  ->join("LEFT JOIN qz_content_order_stats as co on co.content_id = b.id AND co.src = '1'")
				  ->join("LEFT JOIN qz_orders as o on o.id = co.order_id AND o.type_fw = '1' GROUP BY b.id")
				  ->buildSql();

		return M()->table($buidsql)->alias('c')
				  ->field('sum(order_num) as order_num,sum(fendans) as fendan_num')
				  ->find();

	}

	//按时间取主站文章实际分单数
	public function getRealFenByTime($map){
        unset($map['order']);
		$buidsql = M('www_article')->alias('a')
                  ->join('LEFT JOIN qz_www_article_class_rel as r on r.article_id = a.id')
				  ->field('a.id')
				  ->where($map)
				  ->buildSql();

		$result = M()->table($buidsql)->alias('b')
				  ->field('b.*,count(n.order_id) as real_num')
				  ->join("LEFT JOIN qz_content_order_stats as co on co.content_id = b.id AND co.src = '1'")
				  ->join("LEFT JOIN qz_order_csos_new as n on co.order_id = n.order_id and n.order_on = 4")
				  ->join("LEFT JOIN qz_orders as o on o.id = co.order_id AND o.type_fw = '1' GROUP BY b.id")
				  ->select();
		return $result;
	}

	//按时间取分站文章订单数发单数
	public function getSubOrdersNumByTime($map){

		$buidsql = M('little_article')->alias('a')
				  ->field('a.id')
				  ->where($map)
				  ->buildSql();

		$buidsql = M()->table($buidsql)->alias('b')
				  ->field('b.*,count(co.content_id) as order_num,count(o.id) as fendans')
				  ->join("LEFT JOIN qz_content_order_stats as co on co.content_id = b.id AND co.src = '2'")
				  ->join("LEFT JOIN qz_orders as o on o.id = co.order_id AND o.type_fw = '1' GROUP BY b.id")
				  ->buildSql();

		return M()->table($buidsql)->alias('c')
				  ->field('sum(order_num) as order_num,sum(fendans) as fendan_num')
				  ->find();

	}

	//按时间取分站文章实际分单数
	public function getSubRealFenByTime($map){
        unset($map['order']);
		$buidsql = M('little_article')->alias('a')
				  ->field('a.id')
				  ->where($map)
				  ->buildSql();

		return  M()->table($buidsql)->alias('b')
				  ->field('b.*,count(n.order_id) as real_num')
				  ->join("LEFT JOIN qz_content_order_stats as co on co.content_id = b.id AND co.src = '2'")
				  ->join("LEFT JOIN qz_order_csos_new as n on co.order_id = n.order_id and n.order_on = 4")
				  ->join("LEFT JOIN qz_orders as o on o.id = co.order_id AND o.type_fw = '1' GROUP BY b.id")
				  ->select();
	}

	//获取所有编辑组帐号
	public function getEditUsers(){
		/*$buidsql = M('www_article')
				  ->field('userid')
				  ->group('userid')
				  ->buildSql();

		return M()->table($buidsql)->alias('w')
				  ->field('w.*,u.user,u.uid')
				  ->join('LEFT JOIN qz_adminuser as u ON u.id = w.userid')
				  ->where('u.stat = 1 AND u.uid IN (24,25,26,47)')
				  ->order('userid')
				  ->select();*/
        return  M("adminuser")->where('stat = 1 AND uid IN (24,25,26,47)')->field('id as userid,user,uid')->order('userid')->select();
	}

	//获取美图帐号列表
	public function getMeituUsers(){
		$buidsql = M('meitu')
				  ->field('uid')
				  ->group('uid')
				  ->buildSql();

		return M()->table($buidsql)->alias('m')
				  ->field('m.*,u.name')
				  ->join('LEFT JOIN qz_adminuser as u ON u.id = m.uid')
				  ->where('u.stat = 1 AND u.uid IN (6,24,25,88,26)')
				  ->order('u.id')
				  ->select();
	}

	//获取主站文章数量
	public function getWwwArticleCount($map = []){
		if(empty($map['a.state'])){
			$map['a.state'] = array('NEQ','-1');
		}
        unset($map['order']);
		$result = M('www_article')->alias('a')
								  ->join('INNER JOIN qz_www_article_class_rel as r on r.article_id = a.id')
								  ->join('INNER JOIN qz_www_article_class as c on c.id = r.class_id')
								  ->where($map)
								  ->count();
		return $result;
	}

	//获取主站文章列表
	public function getWwwArticleList($map = [],$start,$each){
        if(empty($map['a.state'])){
			$map['a.state'] = array('NEQ','-1');
		}
        $order = "b.id desc";
        if(!empty($map['order'])){
            $order = $map['order'];
            unset($map['order']);
        }

		$buidsql = M('www_article')->alias('a')
				  ->field('a.id,a.userid,a.state,a.keywords,a.title,a.addtime,r.class_id,a.search_ip,a.realview,a.pre_release,a.createtime')
				  ->join('LEFT JOIN qz_www_article_class_rel as r on r.article_id = a.id')
				  ->where($map)
				  ->buildSql();

		$result = M()->table($buidsql)->alias('b')
				  ->field('b.*,u.name AS uname,c.shortname,c.classname,count(co.content_id) as order_num,count(o.id) as fendans,c1.classname as secondtype,c2.classname as firsttype,count(n.order_id) as real_num')
				  ->join('LEFT JOIN qz_www_article_class as c on c.id = b.class_id ')
                  ->join('LEFT JOIN qz_www_article_class as c1 on c.pid = c1.id ')
                  ->join('LEFT JOIN qz_www_article_class as c2 on c1.pid = c2.id ')
				  ->join('LEFT JOIN qz_adminuser as u on u.id = b.userid')
				  ->join("LEFT JOIN qz_content_order_stats as co on co.content_id = b.id AND co.src = '1' ")
                  ->join("LEFT JOIN qz_order_csos_new as n on co.order_id = n.order_id and n.order_on = 4")
				  ->join("LEFT JOIN qz_orders as o on o.id = co.order_id AND o.type_fw = '1' ")
				  ->group('b.id')
				  ->order($order)
                  ->limit($start .','. $each)
				  ->select();
                  
		return $result;
	}

	//获取主站文章实际分量
	public function getWwwArticleRealFen($map = [],$start,$each,$order = 'a.id DESC'){
		if(empty($map['a.state'])){
			$map['a.state'] = array('NEQ','-1');
		}

		$buidsql = M('www_article')->alias('a')
				  ->field('a.id,a.userid')
				  ->where($map)
				  ->limit($start .','. $each)
				  ->order($order)
				  ->buildSql();
		$result = M()->table($buidsql)->alias('b')
				  ->field('b.*,count(n.order_id) as real_fen_num')
				  ->join("LEFT JOIN qz_content_order_stats as co on co.content_id = b.id AND co.src = '1' ")
				  ->join("LEFT JOIN qz_order_csos_new as n on n.order_id = co.order_id AND n.order_on = '4' ")
				  ->join("LEFT JOIN qz_orders as o on o.id = co.order_id AND o.type_fw = '1'")
				  ->group('b.id')
				  ->order('b.id DESC')
				  ->select();
		return $result;
	}

	//获取分站文章数量
	public function getSubArticleListCount($map){
        unset($map['order']);
		return M("little_article")->where($map)->alias("a")
									   ->join("join qz_infotype as b on a.classid = b.id and b.enabled = 1")
									   ->count();
	}

	//获取分站文章点击量
	public function getSubArticleUVNum($map){
        unset($map['order']);
		return M("little_article")->where($map)->alias("a")
									   ->join("join qz_infotype as b on a.classid = b.id and b.enabled = 1")
									   ->field('sum(a.realview) AS totalnum')
									   ->select();
	}

	//获取分站文章列表
	public function getSubArticleList($map,$pageIndex,$pageCount){

		if(empty($map['a.state'])){
			$map['a.state'] = array("NEQ","-1");
		}

        $order = "t.id desc";
        if(!empty($map['order'])){
            $order = $map['order'];
            unset($map['order']);
        }

		$buidsql = M('little_article')->alias('a')
				  ->field('a.id,a.state,a.authid,a.classid,a.title,a.cs,a.addtime,a.keywords,a.realview,b.name as typename,b.shortname,a.pre_release,a.createtime')
				  ->join('join qz_infotype as b on a.classid = b.id and b.enabled = 1')
				  ->where($map)
				  ->buildSql();
		$result = M()->table($buidsql)->alias('t')
				  ->field('t.*,u.name,q.cname,q.bm,count(co.content_id) as order_num,count(o.id) as fendans,count(n.order_id) as real_num')
				  ->join('join qz_adminuser as u on u.id = t.authid')
				  ->join("join qz_quyu as q on q.cid = t.cs")
				  ->join("LEFT JOIN qz_content_order_stats as co on co.content_id = t.id AND co.src = '2'")
                  ->join("LEFT JOIN qz_order_csos_new as n on co.order_id = n.order_id and n.order_on = 4")
				  ->join("LEFT JOIN qz_orders as o on o.id = co.order_id AND o.type_fw = '1'")
				  ->group('t.id')
				  ->order($order)
                  ->limit($pageIndex .','. $pageCount)
				  ->select();

		return $result;
	}

	//获取分站文章实际分单量
	private function getSubArticleRealFen($map,$pageIndex,$pageCount){

		$map['a.state'] = '2';

		$buidsql = M('little_article')->alias('a')
				  ->field('a.id')
				  ->where($map)
				  ->limit($pageIndex .','. $pageCount)
				  ->order('a.id DESC')
				  ->buildSql();
		return M()->table($buidsql)->alias('t')
				  ->field('t.*,count(n.order_id) as real_fen_num')
				  ->join("LEFT JOIN qz_content_order_stats as co on co.content_id = t.id AND co.src = '2'")
				  ->join("LEFT JOIN qz_order_csos_new as n on n.order_id = co.order_id AND n.order_on = '4' ")
				  ->join("LEFT JOIN qz_orders as o on o.id = co.order_id AND o.type_fw = '1'")
				  ->group('t.id')
				  ->order('t.id DESC')
				  ->select();
	}

    /**
     * 获取推送结果数据.
     *
     * @param      void
     *
     * @return     array  $result   推送结果统计数组
     */
    public function getLinkSubmitCon()
    {
    	$s_map['s_code'] = array('EQ',1);
    	$start_time = strtotime(date("Y-m-d"));
    	$end_time = strtotime((date("Y-m-d").' 23:59:59'));
    	$s_map['time'] = array('between',array($start_time,$end_time));
        $success = M("www_article_linksubmit")->where($s_map)->field("count(if(type = 1,id,null)) as o_success,count(if(type = 2,id,null)) as n_success")->select();
        $f_map['s_code'] = array('NEQ',1);
    	$f_map['time'] = $s_map['time'];
        $fall = M("www_article_linksubmit")->where($f_map)->field("count(if(type = 1,id,null)) as o_fall,count(if(type = 2,id,null)) as n_fall")->select();

        $result = array_merge($success[0],$fall[0]);
        return $result;
    }

    //美图 按时间取订单数和分单数
	public function getMeituOrdersNumByTime($condition){

		if(!empty($condition['keywords'])){
            $condition["_complex"] = array(
                'id' => array('LIKE', '%'.$condition['keywords'].'%'),
                'title' => array('LIKE', '%'.$condition['keywords'].'%'),
                '_logic' => 'OR'
            );
            unset($condition['keywords']);
        }

		$buidsql = M('meitu')
				  ->field('id,realview')
				  ->where($condition)
				  ->buildSql();

		$buidsql = M()->table($buidsql)->alias('b')
				  ->field('b.*,count(co.content_id) as order_num,count(o.id) as fendans,count(n.order_id) as reals')
				  ->join("LEFT JOIN qz_content_order_stats as co on co.content_id = b.id AND co.src = '3'")
				  ->join("LEFT JOIN qz_orders as o on o.id = co.order_id AND o.type_fw = '1'")
				  ->join("LEFT JOIN qz_order_csos_new as n on n.order_id = co.order_id and n.order_on = 4")
				  ->group("b.id")
				  ->buildSql();

		return M()->table($buidsql)->alias('c')
				  ->field('sum(order_num) as order_num,sum(fendans) as fendan_num,sum(reals) as real_num,sum(realview) as realview_num')
				  ->find();

	}

    /**
     * [getMeituList 获取美图列表]

     * @param  integer $pageIndex [开始页]
     * @param  integer $pageCount [每页数量]
     * @param  [type]  $params    [参数]
     * @return [type]             [description]
     */
    public function getMeituList($condition,$pageIndex=1,$pageCount = 16){

        if(!empty($condition['keywords'])){
            $condition["_complex"] = array(
                'id' => array('LIKE', '%'.$condition['keywords'].'%'),
                'title' => array('LIKE', '%'.$condition['keywords'].'%'),
                '_logic' => 'OR'
            );
            unset($condition['keywords']);
        }

        $sql = M('meitu')->where($condition)->order('time DESC')->limit($pageIndex,$pageCount)->buildSql();

        $result =M()->table($sql)->alias('b')
				  ->field('b.*,count(co.content_id) as order_num,count(o.id) as fendans,count(n.order_id) as real_num')
				  ->join("LEFT JOIN qz_content_order_stats as co on co.content_id = b.id AND co.src = '3' ")
				  ->join("LEFT JOIN qz_orders as o on o.id = co.order_id AND o.type_fw = '1' ")
				  ->join("LEFT JOIN qz_order_csos_new as n on n.order_id = co.order_id and n.order_on = 4")
				  ->group('b.id')
				  ->order('b.time DESC')
				  ->select();

        return $result;
    }

    /**
     * [getMeituList 获取美图列表]

     * @param  integer $pageIndex [开始页]
     * @param  integer $pageCount [每页数量]
     * @param  [type]  $params    [参数]
     * @return [type]             [description]
     */
    public function getMeituListByDay($condition,$pageIndex=1,$pageCount = 16){

        if(!empty($condition['keywords'])){
            $condition["_complex"] = array(
                'id' => array('LIKE', '%'.$condition['keywords'].'%'),
                'title' => array('LIKE', '%'.$condition['keywords'].'%'),
                '_logic' => 'OR'
            );
            unset($condition['keywords']);
        }

        $sql = M('meitu')->field('id,title,time,uid,state,realview')->where($condition)->order($order)->buildSql();

        $sql = M()->table($sql)->alias('b')
				  ->field('b.*,count(co.content_id) as order_num,count(n.order_id) as real_num')
				  ->join("LEFT JOIN qz_content_order_stats as co on co.content_id = b.id AND co.src = '3' ")
				  ->join("LEFT JOIN qz_order_csos_new as n on n.order_id = co.order_id and n.order_on = 4")
				  ->group('b.id')
				  ->order('b.id DESC')
				  ->buildSql();

		return M()->table($sql)
				->alias('c')
				->field('count(*) as meitu_num,sum(order_num) as order_nums,sum(real_num) as real_nums,sum(realview) AS ip_num,FROM_UNIXTIME(c.time,"%Y-%m-%d") AS days')
				->group('days')
				->select();
    }

    /**
     * [getMeituCount 获取美图数量]
     * @param  [type] $params  [参数]
     * @param  [type] $keyword [搜索关键字]
     * @return [type]          [description]
     */
    public function getMeituCount($condition){

        if(!empty($condition['keywords'])){
            $condition["_complex"] = array(
                'id' => array('LIKE', '%'.$condition['keywords'].'%'),
                'title' => array('LIKE', '%'.$condition['keywords'].'%'),
                '_logic' => 'OR'
            );
            unset($condition['keywords']);
        }

        return M('meitu')->where($condition)->count();
    }

}