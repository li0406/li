<?php
// +----------------------------------------------------------------------
// | NewMediaGroupModel
// +----------------------------------------------------------------------
namespace Home\Model\Db;

use Think\Model;

class NewMediaUserSrcLogModel extends Model
{
    protected $trueTableName = 'qz_new_media_user_src_log';

    public function getList($map, $offset, $length, $with_op_admin_id = false)
    {
        $result = M('new_media_user_src_log')->where($map)->limit($offset, $length)->order('create_time desc,id desc')->select();

        if ($with_op_admin_id) {
            $op_admins_ids = array_column($result, 'op_admin_id');
            $op_admins = [];
            if ($op_admins_ids) {
                $op_admins = AdminuserModel::getAdminByMap(['id' => ['in', $op_admins_ids]], 'id,name');
            }

            foreach ($result as $k => $v) {
                $result[$k]['op_admin'] = [];
                foreach ($op_admins as $k1 => $v1) {
                    if ($v['op_admin_id'] == $v1['id']) {
                        $result[$k]['op_admin'] = $v1;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 统计数量
     *
     * @param $map
     * @return mixed
     */
    public function getCount($map)
    {
        $count = M('new_media_user_src_log')->where($map)->count('id');
        return $count;
    }

    /**
     * 添加数据
     *
     * @param $data
     * @return bool|mixed
     */
    public function addLog($data)
    {
        return M('new_media_user_src_log')->add($data);
    }
}