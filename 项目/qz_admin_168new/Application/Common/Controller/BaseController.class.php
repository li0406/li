<?php

namespace Common\Controller;
use Think\Controller;

class BaseController extends Controller
{

    public function _initialize()
    {
    }

    //空操作
    public function _empty()
    {
        if (IS_AJAX) {
            header("content:application/json;chartset=uft-8");
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            echo json_encode(['status' => 0, 'info' => '您访问的页面被外星人抓走了  _(:3 」∠)_']);
        } else {
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            $this->error('您访问的页面被外星人抓走了  _(:3 」∠)_');
        }
        die();
    }

    public function _error($message = '你无权访问该页面!')
    {
        if (IS_AJAX) {
            header("content:application/json;chartset=uft-8");
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            echo json_encode(['status' => 0, 'info' => $message]);
        } else {
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            $this->error($message);
        }
        die();
    }
}