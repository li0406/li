<?php

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;

class VideoController extends HomeBaseController
{
    /**
    *  装修公司首页视频
    */
    public function index()
    {
        if (IS_POST) {
            $id       = intval($_POST['comid']);
            $url      = trim($_POST['url']);//过滤URL中由于粘贴不慎带来的空格
            $ori_from = trim($_POST['ori_from']);//接收视频来源
            if (empty($id) ||  empty($ori_from) ) {
               $this->ajaxReturn(array('status' => 0, 'info' => '传入参数不完整!'));
            }

            //判断装修公司是否存在
            $temp = D('User')->getUserByIdAndClassid($id, 3);
            if (empty($temp)) {
                $this->ajaxReturn(array('status' => 0, 'info' => '未找到该公司！'));
            }

            //判断是否和原链接一样
            if ($temp['video'] == $url) {
                $this->ajaxReturn(array('status' => 0, 'info' => '视频链接已经是该链接了，无需再次修改！'));
            }

            //如果传过来的url视频地址为空 则说明是要把视频下下来
            if (empty($url)) {
                //把视频下下来
                D('User')->setUserVideoByUserId($id, $url);
                $this->ajaxReturn(array('status' => 1, 'info' => '该公司视频下架成功!'));
            }

            if ($ori_from == 'youku'){
                //处理url地址 为flash
                if (substr($url, -3) != "swf") {
                    if (preg_match('/youku/', $url)) {
                        if (preg_match('/^.*id_([^.]+).*/', $url, $videoid)) {
                           $url = 'http://player.youku.com/player.php/sid/'.$videoid[1].'/v.swf';
                        } else {
                            $this->ajaxReturn(array('status' => 0, 'info' => '更新广告失败！优酷swf未找到!'));
                        }
                    }
                }
            } else if ($ori_from == 'tengxun') {
                # 腾讯视频来袭！  swf不处理 如果文件后缀是html  在腾讯视频的处理中是可以尝试用http://cache.tv.qq.com/qqplayerout.swf?vid=视频vid来处理的
                # 1 单个视频直接写入html文件名为vid  http://v.qq.com/boke/page/v/q/8/v01285a5sq8.html
                # 2 类似与多组视频推荐中后面传参vid  http://v.qq.com/cover/o/ol4jxavj8ef7kcx.html?vid=z00159mcjpt
                # 使用pathinfo函数处理即可
                //截取url信息处理  返回数组四个元素 例如http://v.qq.com/cover/5/5gebupsd23bjkqu.html?vid=a0015xoe339 可以返回
                //  'dirname' => string 'http://v.qq.com/cover/5' (length=23)
                //  'basename' => string '5gebupsd23bjkqu.html?vid=a0015xoe339' (length=36)
                //  'extension' => string 'html?vid=a0015xoe339' (length=20)
                //  'filename' => string '5gebupsd23bjkqu' (length=15)
                $url_info = pathinfo($url);//规范化处理URL
                $res = parse_url($url);//换url函数处理
                //再次检测来源
                if(strpos($res['host'],'qq.com') === false)
                {
                    //说明此时是并非真实的采用了腾讯的页面视频 强制禁止
                    $this->ajaxReturn(array('status' => 0, 'info' => '视频来源并非来自腾讯视频!'));
                }
                //查找extension中是否有swf如果有则说明这样的地址已经处理过了 不需要再次处理
                $is_swf = strpos($url_info['extension'],'swf')!==false?1:0;//如果有swf为1 否则为0
                if($is_swf != 1)
                {
                    if($url_info['extension']=="html")
                    {
                        //说明此时用的是第1种模式
                        $url='http://cache.tv.qq.com/qqplayerout.swf?vid='.$url_info['filename'];//取文件名作为vid
                    } else {
                        //说明是第二种模式
                        $res=parse_url($url);//换url函数处理
                        parse_str($res['query'],$parr);//将query传参处理为数组 这样避免字符串截取带来问题 也避免多项传参
                        //得到parr索引的vid  为什么这样处理 为了防止出现 html?vid=a0015xoe339&s=***&h=*** 这样的多项传值
                        $url='http://cache.tv.qq.com/qqplayerout.swf?vid='.$parr['vid'];
                    }
                }
            }

            if (false === D('User')->setUserVideoByUserId($id, $url)) {
                $this->ajaxReturn(array('status' => 0, 'info' => '更新广告失败！#01'));
            }
            $this->ajaxReturn(array('status' => 1, 'info' => '修改成功~'));
        }
        $this->display();
    }
}