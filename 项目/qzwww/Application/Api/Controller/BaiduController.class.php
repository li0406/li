<?php

namespace Api\Controller;
use Api\Common\Controller\ApiBaseController;
use OAuth2Trait;

class BaiduController extends ApiBaseController
{
    public function _initialize()
    {
        import('Library.Org.OAuth.BaiduOAuth2');
        $appid = "i0rmlcMsuL4X5Lmf6gG2VAwbrqjKaZwv";
        $this->auth = new \Library\Org\OAuth\BaiduOAuth2($appid,"GSHWnKAQlvPnonV4CUa8mnCGo07FietL");
    }

    public function index()
    {
        $TOKEN  = 'qizuang';
        $strSignature = $this->getSHA1($TOKEN, $_GET['timestamp'], $_GET['nonce']);

        if ($strSignature == $_GET['signature']) {
            $content = file_get_contents('php://input');
            if (!empty($content)) {
                $content = json_decode($content,true);
                $toUserName = $content["ToUserName"];
                $fromUserName = $content["FromUserName"];
                $switch = D("Options")->getOptionNameBySql("xz_switch");
                if ($switch["option_value"]) {
                    $event =  $content["Event"];
                    if (!empty($event)) {
                        switch ( $event) {
                            case 'subscribe':
                                //获取关注回复
                                $info = D("BaiduReply")->getReplyByEvent($event);
                                if (count($info) > 0) {
                                    $this->sendCustomMessage($info["msgtype"],$info["content"],$fromUserName);
                                }
                                break;
                        }
                    } else {
                        //关键字回复
                        $keyword = $content["Content"];
                        //查询关键字
                        $info  = D("BaiduReply")->getReplyByKeyWord($keyword);

                        if (count($info) == 0) {
                            $info = D("BaiduReply")->getReplyByEvent("message");
                            $this->sendCustomMessage($info["msgtype"],$info["content"],$fromUserName);
                        } else {
                            foreach ($info as $key => $value) {
                                $this->sendCustomMessage($value["msgtype"],$value["content"],$fromUserName);
                            }
                        }
                    }
                    echo "";
                } else {
                    $response = $this->response("text",'请稍等片刻,客服妹子正在努力回复中...',$fromUserName,$toUserName);
                    echo $response;
                }
            } else {
                echo $_GET['echostr'];
            }
        } else {
            //校验失败
            echo 'failed';
        }
        die();
    }

    /**
     * 被动回复消息
     * @return [type] [description]
     */
    private function response($type,$content,$toUserName,$fromUserName)
    {
        switch ($type) {
            case 'text':
                $param = array(
                    "ToUserName" => $toUserName,
                    "FromUserName" => $fromUserName,
                    "CreateTime" => time(),
                    "MsgType" => "text",
                    "Content" => $content
                );
                break;
            case "image":
                $param = array(
                    "ToUserName" => $toUserName,
                    "FromUserName" => $fromUserName,
                    "CreateTime" => time(),
                    "MsgType" => "image",
                    "Image" => array(
                        "MediaId" => "$content"
                    )
                );
                break;
        }

        $param = json_encode($param);
        return $param;
    }

    /**
     * 发送客服消息
     * @param  [type] $toUserName [description]
     * @param  [type] $content   [description]
     * @return [type]             [description]
     */
    private function sendCustomMessage($type,$content,$toUserName)
    {
        $token  = $this->getToken();
        $url = "https://openapi.baidu.com/rest/2.0/cambrian/message/custom_send?access_token=".$token;
        $param = array(
            "touser" => $toUserName
        );
        switch ($type) {
            case 'text':
                $param["msgtype"] = "text";
                $param["text"] = array(
                    "content" => $content
                );
                break;
            case "image":
                $param["msgtype"] = "image";
                $param["image"] = array(
                    "media_id" => $content
                );
                break;
            case "mpnews":
                $param["msgtype"] = "mpnews";
                $param["mpnews"] = array(
                    "media_id" => $content
                );
                break;
        }
        $response =  $this->auth->getHttp($url,"POST",json_encode($param));
        return $response;
    }

    private function getSHA1($strToken, $intTimeStamp, $strNonce, $strEncryptMsg = '')
    {
        $arrParams = array(
            $strToken,
            $intTimeStamp,
            $strNonce,
        );
        if (!empty($strEncryptMsg)) {
            array_unshift($arrParams, $strEncryptMsg);
        }
        sort($arrParams, SORT_STRING);
        $strParam = implode($arrParams);
        return sha1($strParam);
    }

    /**
     * 获取token
     * @param  [type] $redirect_uri [description]
     * @return [type]               [description]
     */
    private function getToken()
    {
        $accessToken = D("Common/BaiduToken")->getToken();
        $time = time();

        if (count($accessToken) == 0 || $time > $accessToken["expires_in"]) {
            $appid = "i0rmlcMsuL4X5Lmf6gG2VAwbrqjKaZwv";
            $secret = "GSHWnKAQlvPnonV4CUa8mnCGo07FietL";

            $url =  "https://openapi.baidu.com/oauth/2.0/token?grant_type=client_credentials&client_id=$appid&client_secret=".$secret;
            $response = $this->auth->getHttp($url,"GET");
            if (empty($response["error"])) {
                $accessToken = $response["access_token"];
                $data = array(
                    "appid" => $appid,
                    "token" =>  $accessToken,
                    "expires_in" =>  $time + 3600,
                    "created_at" =>  $time
                );
            }
            D("Common/BaiduToken")->addToken($data);
        } else {
            $accessToken = $accessToken["token"];
        }

        return  $accessToken;
    }


    private function saveLog($content)
    {
        $path = dirname(dirname( dirname(dirname(__FILE__))))."\log.txt";
        $fp = fopen($path, "w+");
        fwrite($fp,$content);
        fclose($fp);
    }
}