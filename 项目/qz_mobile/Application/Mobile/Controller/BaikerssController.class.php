<?php
/**
 * 移动版 - 百科RSS订阅
 */
namespace Mobile\Controller;

use Think\Controller;
use Mobile\Common\Controller\MobileBaseController;

class BaikerssController extends MobileBaseController
{
    //分类
    public function category()
    {
        header("Content-type:text/xml;charset=utf-8");
        $result = D("Common/Baike")->getListByCategoryForRss();
        $output = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><rss version=\"2.0\"><channel>
                   <title>齐装网</title><link>".$this->SCHEME_HOST['scheme_host']."</link>
                   <description>齐装网装修百科是一部内容开放、自由的装修百科全书，旨在创造一个涵盖所有装修领域知识、服务所有互联网用户的中文知识性装修百科全书。</description>";
        foreach ($result as $article) {
            $article['content'] = iconv('UTF-8', 'UTF-8//IGNORE', $article['content']);
            $output .="<item>
                       <title><![CDATA[{$article['title']}]]></title>
                       <link>".$this->SCHEME_HOST['scheme_host']."/baike/{$article['id']}.html</link>
                       <description><![CDATA[{$article['description']}]]></description>
                       <pubDate>".date('D, d M Y H:i:s T',$article['post_time'])."</pubDate>
                       </item>";
        }
        $output .= "</channel></rss>";

        echo $output;die();
    }
}