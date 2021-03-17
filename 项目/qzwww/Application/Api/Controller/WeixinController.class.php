<?php

namespace Api\Controller;

use Api\Common\Controller\ApiBaseController;

class WeixinController extends ApiBaseController{

    public function index()
    {
        exit();
    }

    /**
     * 诸葛装修微信服务号
     * @return [type] [description]
     */
    public function zhugezhuangxiu()
    {
        import('Library.Org.Util.Weixin');
        $weixin = new \Weixin('ZGZX');

        //验证账号
        if (isset($_GET['echostr'])) {
            $weixin->validate();
        }

        //验证是否是微信服务器过来的请求

        //推送消息
        $result = $weixin->getDataFromWeiXin();
        if ($result == false) {
            exit();
        }
        $data = $result['EventKey'];

        //推送主站文章内容
        if ($data['category'] == '1001') {
            $info = S('Api:Weixin:zhugezhuangxiu:' . $data['category'] . $data['key']);
            if (empty($info)) {
                $info = D('WwwArticle')->getArticleBriefById($data['key']);
                S('Api:Weixin:zhugezhuangxiu:' . $data['category'] . $data['key'], $info, 1800);
            }
            if (!empty($info)) {
                $message = array(
                    'touser' => $result['FromUserName'],
                    'msgtype' => 'news',
                    'news' => array(
                        'articles' => array(
                            array(
                                'title' => $info['title'],
                                'description' => $info['subtitle'],
                                'url' => 'http://m.'.C('QZ_YUMING').'/gonglue/'.$info['shortname'].'/'.$info['id'].'.html',
                                'picurl' => 'http://'.C('QINIU_DOMAIN').'/'.$info['face'].'-w240.jpg'
                            )
                        ),
                    ),
                );
            }
        }

        //推送微信专享文章
        if ($data['category'] == '1002') {
            $info = S('Api:Weixin:zhugezhuangxiu:' . $data['category'] . $data['key']);
            if (empty($info)) {
                $info = D('WeixinArticle')->getWeixinArticleById($data['key']);
                S('Api:Weixin:zhugezhuangxiu:' . $data['category'] . $data['key'], $info, 1800);
            }
            if (!empty($info)) {
                $message = array(
                    'touser' => $result['FromUserName'],
                    'msgtype' => 'news',
                    'news' => array(
                        'articles' => array(
                            array(
                                'title' => $info['title'],
                                'description' => $info['description'],
                                'url' => $info['url'],
                                'picurl' => 'http://'.C('QINIU_DOMAIN').'/'.$info['face'].'-w240.jpg'
                            )
                        ),
                    ),
                );
            }
        }

        //发送消息
        if (!empty($message)) {
            $weixin->sendMessage($message);
        }
        exit();
    }
}