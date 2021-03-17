<?php
/**
 *  调用三方服务API  三方服务的token令牌表
 */

namespace Home\Model\Db;

use Think\Model;

class ThreeTokenModel extends Model
{
    protected $tableName = 'three_token';

    /**
     * 获取token令牌
     *
     * @param string $appid appid
     * @param string $type
     *
     * @return bool|mixed
     */
    public function getToken($appid = '', $type = '')
    {
        if (empty($appid) || empty($type)) {
            return false;
        }
        $map          = [];
        $map['appid'] = ['EQ', $appid];
        $map['type']  = ['EQ', $type];
        return $this->where($map)->find();
    }

    /**
     *
     * 更新token令牌
     *
     *
     * add 和 update合用
     * 通过 appid type
     * 没有数据就add
     * 有数据那么就会是update
     *
     * @param array $data
     *
     * @return mixed
     */
    public function addToken($data)
    {

        if (!empty($data['expires_in']) && empty($data['invalid_time'])) {
            $data['invalid_time'] = date('Y-m-d H:i:s', $data['invalid_time']);
        }

        if (!empty($data['invalid_time']) && empty($data['expires_in'])) {
            $data['expires_in'] = strtotime($data['invalid_time']);
        }

        if (empty($data['appid']) || empty($data['token']) || empty($data['expires_in'])) {
            return false;
        }

        $appid        = $data['appid'];
        $type         = $data['type'];
        $lastTokenRow = self::getToken($appid, $type);

        if (empty($lastTokenRow['token'])) {
            // 插入
            if (empty($data['remarks'])) {
                $data['remarks'] = '首次插入';
            }
            if (empty($data['updated_at'])) {
                $data['updated_at'] = date('Y-m-d H:i:s');
            }
            if (empty($data['created_at'])) {
                $data['created_at'] = date('Y-m-d H:i:s');
            }
            $result = $this->add($data);
        } else {
            //更新
            if (empty($data['remarks'])) {
                $data['remarks'] = '正常更新';
            }
            if (empty($data['updated_at'])) {
                $data['updated_at'] = date('Y-m-d H:i:s');
            }
            $map          = [];
            $map['appid'] = ['EQ', $appid];
            $map['type']  = ['EQ', $type];
            $result       = $this->where($map)->save($data);
        }
        if ($result) {
            return true;
        }
        return false;
    }
}