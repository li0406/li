<?php

/**
 * 获取通话录音和播放
 * @Author: lovenLiu
 * @Email: lypeng9205@163.com
 * @Date:   2019-05-20 10:17:38
 * @Last Modified by:   liuyupeng
 * @Last Modified time: 2019-06-13 16:58:47
 */

namespace app\index\controller\v1;

use think\Request;
use think\Controller;

use app\common\model\logic\Telcuct;

Class Telcenter extends Controller {

    /**
     * 获取联通云总机 通话录音
     * @Author   liuyupeng
     * @DateTime 2019-05-20T10:46:26+0800
     */
    public function cuctcallRecordUrl(Request $request){
        $call_sid = $request->param('call_sid');
        $channel = $request->param('channel');

        if (empty($call_sid) || empty($channel)) {
            return json(sys_response(4000002));
        }

        switch ($channel) {
            case 'holly':
                $recordFile = model('db.logHollyTelcenter')->getRecordFileByCallSid($call_sid);
                if (!empty($recordFile)) {
                    $surl = $recordFile['record_file_url'];
                    $eurl = authcode($surl, 'ENCODE');
                    $url  = '/telcenter/recordurlcamouflage?url=' . urlencode($eurl);
                    $response = sys_response(0, '', ['url' => $url]);
                } else {
                    $response = sys_response(1000001, '获取通话录音地址失败');
                }

                break;
            
            default:
                $telcuct = new Telcuct();
                $cuctResult = $telcuct->callRecordUrl($call_sid);
                if ($cuctResult && $cuctResult['resp']['respCode'] == '0') {
                    $surl = $cuctResult['resp']['callRecordUrl']['url'];
                    $eurl = authcode($surl, 'ENCODE');
                    $url  = '/telcenter/recordurlcamouflage?url=' . urlencode($eurl);
                    
                    $response = sys_response(0, '', ['url' => $url]);
                } else {
                    $response = sys_response(1000001, $cuctResult['msg']);
                }
                break;
        }

        return json($response);
    }
    
    /**
     * 联通云总机 包装 通话录音url
     * @param   get.url 联通云总机原始录音url地址
     * @return  包装后的url返回 录音媒体 or false
     */
    public function callRecordUrlcamouflage(Request $request) {
        $url = $request->param('url');
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
}