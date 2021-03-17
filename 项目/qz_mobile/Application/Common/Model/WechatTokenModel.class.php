<?php
/**
 *  微信令牌表
 */
namespace Common\Model;
use Think\Model;
class WechatTokenModel extends Model
{
    protected $tableName = 'wechat_token';

    /**
     * 获取token令牌
     *
     * @param string $appid
     * @return mixed
     * 
     */
    public function getLastToken($appid='')
    {
        if(empty($appid)){
            return false;
        }
        $map          = [];
        $map['appid'] = ['EQ', $appid];
        return M("wechat_token")->where($map)->find();
    }

    /**
     * 更新token令牌
     * ps： 因为历史原因，这里忽略名称的不规范， 继续沿用addToken
     *
     * @param array $data
     * @return mixed
     */
    public function addToken($data)
    {
        if (empty($data['appid']) || empty($data['token']) ||  empty($data['expires_in'])) {
            return false;
        }
        $appid = $data['appid'];
        $lastTokenRow = self::getLastToken($appid);
        if (empty($lastTokenRow['token'])) {
            // 插入
            if (empty($data['info'])) {
                $data['info'] = '首次插入';
            }
            if (empty($data['updated_at'])) {
                $data['updated_at'] = date('Y-m-d H:i:s');
            }
            if (empty($data['created_at'])) {
                $data['created_at'] = date('Y-m-d H:i:s');
            }
            //dump($data);
            $result = M("wechat_token")->add($data);
        } else {
            //更新
            if (empty($data['info'])) {
                $data['info'] = '正常更新';
            }
            if (empty($data['updated_at'])) {
                $data['updated_at'] = date('Y-m-d H:i:s');
            }
            $map = [];
            $map['appid'] = ['EQ', $appid];
            $result = M("wechat_token")->where($map)->save($data);
        }
        if ($result) {
            return true;
        }
        return false;
    }
}