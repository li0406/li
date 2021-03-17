<?php
// +----------------------------------------------------------------------
// | CityQuote
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\db\Where;
use think\Model;

class SaleReportQuote extends Model
{
    /**
     * 是否需要自动写入时间戳 如果设置为字符串 则表示时间字段的类型
     * @var bool|string
     */
    protected $autoWriteTimestamp = true;

    /**
     * 创建时间字段 false表示关闭
     * @var false|string
     */
    protected $createTime = 'create_time';

    /**
     * 更新时间字段 false表示关闭
     * @var false|string
     */
    protected $updateTime = 'update_time';

    public function getCount($map)
    {
        return $this->where($map)->count('id');
    }

    public function getList($map = [], $page = null, $pageSize = null, $field = '*', $orderby = '')
    {
        if ($page == 0) {
            $offset = 0;
        } else {
            $offset = $pageSize * ($page - 1);
        }
        return $this->field($field)->where(new Where($map))->limit($offset,$pageSize)->order($orderby)->select();
    }

    public function getOne($map, $field = '*')
    {
        return $this->field($field)->where(new Where($map))->findOrEmpty();
    }

    public function saveOne($data, $map = [])
    {
        return $this->save($data, $map);
    }

    public function delOne($map)
    {
        return $this->where(new Where($map))->delete();
    }
}