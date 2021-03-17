<?php

namespace Home\Model\Logic;

use Common\Enums\ApiConfig;
use Home\Model\XiaoguotuThreedimensionModel;
use Think\Exception;

class MeituLogicModel
{
    //qz_xiaoguotu_threedimension
    protected $threeDModel;

    protected $user;

    public function __construct()
    {
        $this->threeDModel = new XiaoguotuThreedimensionModel();
        $this->user = session('uc_userinfo');
    }

    /**
     * @param $ids
     * @throws Exception
     */
    public function multiPublish($ids)
    {
        try {
            //更新id
            $currentTimestamp = date('Y-m-d H:i:s',time());
            $data = [
                'status' => 1,
                'publish_time' => $currentTimestamp,
                'update_time' => $currentTimestamp,
                'create_time' => $currentTimestamp,
                'update_uid' => $this->user['id']
            ];
            $ret = $this->threeDModel->multiPublish($ids, $data);
            if (empty($ret)) {
                throw new Exception('保存失败', ApiConfig::PARAMETER_ILLEGAL);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }


    /**
     * 重新存储数据
     * @param $ids
     * @throws Exception
     */
    public function restore($ids)
    {
        try {
            M()->startTrans();
            //1.获取数据
            $data = $this->threeDModel->getMulti($ids);
            if(empty($data)){
                throw new Exception('数据不存在', ApiConfig::PARAMETER_ILLEGAL);
            }
            foreach ($data as $k => $v) {

                $oldId = $v['id'];
                //数据是否存在
                if (!$this->threeDModel->hasValue($oldId)) {
                    throw new Exception('数据不存在', ApiConfig::PARAMETER_ILLEGAL);
                }
                //2.添加数据

                $currentTimestamp = date('Y-m-d H:i:s',time());
                $v['statue'] = 1;
                $v['publish_time'] = $currentTimestamp;
                $v['create_time'] = $currentTimestamp;
                $v['update_time'] = $currentTimestamp;
                $v['update_uid'] = $this->user['id'];

                unset($v['id']);
                //4.删除旧数据
                $delRet = $this->threeDModel->drop($oldId);
                if (empty($delRet)) {
                    throw new Exception('删除数据失败', ApiConfig::PARAMETER_ILLEGAL);
                }
                $newId = $this->threeDModel->add($v);
                if (empty($newId)) {
                    throw new Exception('添加数据失败', ApiConfig::PARAMETER_ILLEGAL);
                }
                //3.修改图片关联id
                $updateImageRet = $this->threeDModel->restore($newId, $oldId);
                if (empty($updateImageRet)) {
                    throw new Exception('更新图片数据失败', ApiConfig::PARAMETER_ILLEGAL);
                }
            }
            M()->commit();
        } catch (Exception $e) {
            M()->rollback();
            throw $e;
        }
    }

}
