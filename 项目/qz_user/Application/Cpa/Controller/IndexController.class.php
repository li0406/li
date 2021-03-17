<?php

namespace Cpa\Controller;

use Cpa\Common\Controller\JiajuBaseController;

class IndexController extends JiajuBaseController
{
    public function index()
    {
        $url = 'http://'.$_SERVER['HTTP_HOST'].'/user/index';
        if (is_ssl()){
            $url = 'https://'.$_SERVER['HTTP_HOST'].'/user/index';
        }
        header("Location: ".$url);
        die();
    }
}
