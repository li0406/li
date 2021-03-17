<?php
/**
 *  微信令牌表
 */
namespace Common\Model\Logic;

class YyWxManagerLogicModel
{
    /**
     * 获取token令牌
     * @param string $appid
     * @return mixed
     * 
     */
    public function getWxInfoByWxid($wxid='')
    {
        if(empty($wxid)){
            return false;
        }
        $map['wxid'] = ['EQ', $wxid];
        return D("Common/Db/YyWxManage")->getInfoByMap($map);
    }
}