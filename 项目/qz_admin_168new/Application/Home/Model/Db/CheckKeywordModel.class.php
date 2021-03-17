<?php
/**
 * Created by PhpStorm.
 * User: jsb
 * Date: 2019/3/18
 * Time: 9:23
 */

namespace Home\Model\Db;


use Think\Model;

class CheckKeywordModel extends Model
{
	public function getkeywords()
	{
		return M('check_keyword')->select();
	}
	
	/**
	 * 根据条件获取文章的关键词数量
	 * @param $where
	 * @return mixed
	 */
	public function getArticleKeywordCount($where)
	{
		$where['a.title'] = ['EXP', "like concat('%', ck.keyword, '%')"];
		$field = 'count(*) count, ck.keyword,ck.id';
		$subQuery = $this->table('qz_www_article a,qz_check_keyword ck')
			->field($field)
			->where($where)
			->group('ck.keyword')->buildSql();
		$ret = $this->table('qz_check_keyword kk')
			->field('ifnull(temp.count,0) count ,kk.id,kk.keyword')
			->join("{$subQuery} temp on temp.id=kk.id", "left")
			->select();
		return $ret;
	}
	
	/**
	 * 根据条件获取问答的关键词数量
	 * @param $where
	 * @return mixed
	 */
	public function getAskKeywordCount($where)
	{
		$where['a.title'] = ['EXP', "like concat('%', ck.keyword, '%')"];
		$field = 'count(*) count, ck.keyword,ck.id';
		$subQuery = $this->table('qz_ask a,qz_check_keyword ck')
			->field($field)
			->where($where)
			->group('ck.keyword')->buildSql();
		$ret = $this->table('qz_check_keyword kk')
			->field('ifnull(temp.count,0) count ,kk.id,kk.keyword')
			->join("{$subQuery} temp on temp.id=kk.id", "left")
			->select();
		return $ret;
	}
	
	/**
	 * 根据条件获取百科标题的关键词数量
	 * @param $where
	 * @return mixed
	 */
	public function getBaikeTitleKeywordCount($where)
	{
		$where['a.title'] = ['EXP', "like concat('%', ck.keyword, '%')"];
		$field = 'count(*) count, ck.keyword,ck.id';
		$subQuery = $this->table('qz_baike a,qz_check_keyword ck')
			->field($field)
			->where($where)
			->group('ck.keyword')->buildSql();
		$ret = $this->table('qz_check_keyword kk')
			->field('ifnull(temp.count,0) count ,kk.id,kk.keyword')
			->join("{$subQuery} temp on temp.id=kk.id", "left")
			->select();
		return $ret;
	}
	
	/**
	 * 根据条件获取百科标题的关键词数量
	 * @param $where
	 * @return mixed
	 */
	public function getBaikeItemKeywordCount($where)
	{
		$where['a.item'] = ['EXP', "= ck.keyword"];
		$field = 'count(*) count, ck.keyword,ck.id';
		$subQuery = $this->table('qz_baike a,qz_check_keyword ck')
			->field($field)
			->where($where)
			->group('ck.keyword')->buildSql();
		$ret = $this->table('qz_check_keyword kk')
			->field('ifnull(temp.count,0) count ,kk.id,kk.keyword')
			->join("{$subQuery} temp on temp.id=kk.id", "left")
			->select();
		return $ret;
	}
}