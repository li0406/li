<?php

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

/**
*
*/
class TelcenterController extends HomeBaseController
{
    public function custom_service_tel()
    {
        //获取客服组长列表
        $users = D("Adminuser")->getKfList();
        if (I("get.begin") !== "") {
           $begin = I("get.begin");
        }
        if (I("get.end") !== "") {
           $end = I("get.end");
        }
        if (I("get.group") !== "") {
           $group = I("get.group");
        }
        if (I("get.id") !== "") {
           $id = I("get.id");
        }
        $list = $this->custome_service_tel_list($begin,$end,$group,$id);
        $this->assign("list",$list);
        $this->assign("users",$users);
        $this->display();
    }


    //联通云总机 通话录音
    public function cuctcallRecordUrl() {
        if ($_POST) {
            if (!empty($_POST['callid'])) {
                $cuctResult = $this->callRecordUrl($_POST['callid']);
                if ($cuctResult['resp']['respCode'] == '0') {
                    $surl = $cuctResult['resp']['callRecordUrl']['url'];
                    $eurl = authcode($surl, 'ENCODE');
                    $url  = '/telcenter/callRecordUrlcamouflage?url=' . urlencode($eurl);
                    $this->ajaxReturn(array('data'=>$url,'info'=>$cuctResult['msg'],'status'=>1));
                } else {
                    $this->ajaxReturn(array('data'=>$cuctResult['resp'],'info'=>$cuctResult['msg'],'status'=>0));
                }
            }
        } else {
            $this->ajaxReturn(array('data'=>"",'info'=>"提交的数据有问题",'status'=>0));
        }
    }

    /**
     * 联通云总机 包装 通话录音url
     * @param   get.url 联通云总机原始录音url地址
     * @return  包装后的url返回 录音媒体 or false
     */
    public function callRecordUrlcamouflage() {
        $url = $_GET['url'];
        //$url = urldecode($url);
        if (!empty($url)) {
            $durl = authcode($url);
            $options = array(
                     'http' => array(
                                 'timeout' => 20, //设置一个超时时间，单位为秒
                              )
            );
            $context = stream_context_create($options);
            $saudio  = file_get_contents($durl, false, $context);

            $size = strlen($saudio);
            $begin = 0;
            $end = $size - 1;
            header ( "Content-Type: audio/mpeg" ); //文件媒体类型 mp3
            // header ( 'Cache-Control: public, must-revalidate, max-age=0' );
            header ( "Pragma: no-cache" ); //禁止CDN缓存
            // header ( 'Accept-Ranges: bytes' );
            header ( "Content-Length:" . (($end - $begin) + 1) );
            header ("Content-Disposition:attachment;filename=".date("YmdHis").".mp3"); //下载后的文件名
            print $saudio; //打印到body
            die();
        } else {
            die('错误的callid');
        }

    }


    /**
     * 联通云总机 通过callid 取 通话录音
     * @param   $callid  callid
     * @return  api接口结果 or false
     */
    private function callRecordUrl($callid) {
        if (!empty($callid)) {
                $cuctResult = D('Telcuct')->callRecordUrl($callid);
                return $cuctResult;
        } else {
            return false;
        }
    }


     /**
     * 客服通话率统计
     * @param  [type] $begin [每个月的开始时间]
     * @param  [type] $end   [每个月的结束时间]
     * @param  [type] $begin [开始时间]
     * @param  [type] $end   [结束时间]
     * @param  [type] $group [客服组]
     * @param  [type] $id    [客服ID]
     * @return [type]        [description]
     */
    private function custome_service_tel_list($begin,$end,$group,$id)
    {
        if (empty($begin) && empty($end)) {
           $begin = date("Y-m-d H:i:s",mktime(0,0,0,date("m"),1,date("Y")));
           $end = date("Y-m-d H:i:s");
        }
        $time = strtotime($end);
        $monthBegin = date("Y-m-d",mktime(0,0,0,date("m", $time),1,date("Y",  $time)));
        $monthEnd = date("Y-m-d",mktime(0,0,0,date("m", $time),date("t", $time),date("Y",  $time)));
        $list = D("Adminuser")->custome_service_tel_list($monthBegin,$monthEnd,$begin,$end,$group,$id);
        foreach ($list as $key => $value) {
            //通话时分秒转换
            //通话时长
            $list[$key]["tel_all_sum"] = timediff($value["tel_all_sum"]);
            $list[$key]["tel_sum"] = timediff($value["tel_sum"]);
            //平均时长
            $list[$key]["tel_all_avg"] = timediff(round($value["tel_all_avg"],4));
            $list[$key]["tel_avg"] = timediff(round($value["tel_avg"],4));
            //呼出率
            $list[$key]["tel_rate"] = round($value["tel_count"]/$value["order_count"],4)*100;
            //呼通率
            $list[$key]["yx_tel_rate"] = round($value["yx_tel"]/$value["order_count"],4)*100;
            //接通拒绝率
            $list[$key]["check_rate"] = round($value["check_count"]/$value["yx_tel"],4)*100;
            //分单、赠单
            $list[$key]["fen_sum"] = timediff($value["fen_sum"]);
            $list[$key]["zen_sum"] = timediff($value["zen_sum"]);
            $list[$key]["fen_avg"] = timediff($value["fen_avg"]);
            $list[$key]["zen_avg"] = timediff($value["zen_avg"]);
        }
        return $list;
    }
}