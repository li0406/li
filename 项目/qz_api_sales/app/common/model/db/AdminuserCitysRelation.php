<?php
// +----------------------------------------------------------------------
// | AdminuserCitysRelation 管理人员和城市关联表
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\db\Where;
use think\Model;

class AdminuserCitysRelation extends Model
{
    protected $autoWriteTimestamp = false;
    protected $table = 'qz_adminuser_citys_relation';

    /**
     * 批量添加数据
     *
     * @param $data
     * @return \think\Collection
     */
    public function saveMuch($data)
    {
        return $this->allowField(true)->saveAll($data);
    }

    /**
     * 根据条件删除数据
     *
     * @param $where
     * @return bool
     */
    public function delMuch($where)
    {
        return $this->where(new Where($where))->delete();
    }

    /**
     * 获取用户关联相关城市
     *
     * @param $admin_id
     * @param string $field
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getRelaCitys($admin_id, $field = 'q.*')
    {
        if (empty($admin_id)) {
            return [];
        }
        return self::alias('a')
            ->where(new Where(['a.admin_id' => ['in', $admin_id]]))
            ->join('quyu q', 'q.cid = a.quyu_id')
            ->field($field)
            ->select()
            ->toArray();
    }
}