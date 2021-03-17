<?php
namespace Common\Model\Logic;
use Common\Model\Db\LogMainModel;
use Common\Model\Db\QuanWuRecordModel;

class QuanwuLogicModel
{
    public function addRecord($data){
        $model = new QuanWuRecordModel();
        import('Library.Org.Util.App');
        $app = new \App();
        $info['ip'] = $app->get_client_ip();
        $info['tel'] = remove_xss($data['tel']);
        $info['name'] = remove_xss($data['name']);
        $info['cs'] = remove_xss($data['cs']);
        $info['qx'] = remove_xss($data['qx']);
        $info['created_at'] = time();
        $info['updated_at'] = time();
        if(in_array($data['source'],[1,2])){
            $info['source'] = $data['source'];
        }
        $model->startTrans();
        //手机号是否已存在
        $recordCount = $model->recordCount($info['tel']);
        if($recordCount>0){
           $result = $model->updateInfo($info, $info['tel'] );
        }else{
           $result = $model->addInfo($info);
        }

        $log['time'] = time();
        $log['module'] = 'fb_qwdz';
        $log['log'] = json_encode($info);

        if(!empty($result)){
            $model->commit();
            //操作日志
            $log['level'] = 'success';
            (new LogMainModel())->addLog($log);
            return true;
        }else{
            $model->rollback();
            $log['level'] = 'fail';
            (new LogMainModel())->addLog($log);
            return false;
        }
    }
}