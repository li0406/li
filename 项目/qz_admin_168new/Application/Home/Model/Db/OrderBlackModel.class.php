<?php
// +----------------------------------------------------------------------
// | OrderBlackLogicModel
// +----------------------------------------------------------------------
namespace Home\Model\Db;


use Think\Model;

class OrderBlackModel extends Model
{
    protected $tableName = 'order_blacklist';

    public function getOne($map, $field = '*')
    {
        return M('order_blacklist')->where($map)->field($field)->find();
    }

    public function getCount($map)
    {
        $buildSql = M('user')->alias('u')
            ->join('qz_user_company c on c.userid = u.id')
            ->where(['classid' => array('IN',array(3,6))])
            ->field('u.id,u.jc,u.qc,u.`on`,c.fake')
            ->buildSql();
        return M('order_blacklist')->alias('a')
            ->join('left join '.$buildSql.' u on u.id = a.company_id')
            ->where($map)
            ->count('a.id');
    }

    public function getList($map, $offset = 0, $length = null, $field = 'a.*,u.jc,u.qc,u.fake,u.`on`')
    {
        $buildSql = M('user')->alias('u')
            ->join('qz_user_company c on c.userid = u.id')
            ->where(['u.classid' => array('IN',array(3,6))])
            ->field('u.id,u.jc,u.qc,u.`on`,c.fake')
            ->buildSql();
        return M('order_blacklist')->alias('a')
            ->join('left join '.$buildSql.' u on u.id = a.company_id')
            ->field($field)
            ->where($map)
            ->limit($offset, $length)
            ->order('optime desc')
            ->select();
    }

    public function saveOne($data, $map = [])
    {
        if (!empty($map)) {
            return M('order_blacklist')->where($map)->save($data);
        }
        return M('order_blacklist')->add($data);
    }

    public function saveAll($data)
    {
        return M('order_blacklist')->addAll($data);
    }

    public function delOne($map)
    {
        return M('order_blacklist')->where($map)->delete();
    }

    public function getOnlySelfList($map, $offset = null, $listRows = null, $field = '*')
    {
        return M('order_blacklist')->field($field)->where($map)->limit($offset, $listRows)->select();
    }
}