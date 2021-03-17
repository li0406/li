<?php
namespace Api\Common\Controller;
use Think\Controller;
class ApiBaseController extends Controller {
    public function _initialize(){

    }

    public function _empty() {
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        die();
    }

}