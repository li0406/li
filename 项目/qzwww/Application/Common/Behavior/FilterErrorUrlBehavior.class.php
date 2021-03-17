<?php

namespace Common\Behavior;
use Think\Behavior;

class FilterErrorUrlBehavior extends Behavior
{
    public function run(&$params)
    {
        $uri = $_SERVER['REQUEST_URI'];
        //以.html结尾的
        $reg = '/\/\.html/';
        preg_match($reg,$uri,$m);
        
        if (count($m) > 0) {
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            die();
        }
        return true;
    }
}