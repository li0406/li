<?php
namespace Mobile\Controller;
use Mobile\Common\Controller\MobileBaseController;
class MuserController extends MobileBaseController{
    public function index(){
       $url = $this->SCHEME_HOST['scheme_full']."old.".$this->SCHEME_HOST['domain'].$_SERVER["REQUEST_URI"];
       header( "HTTP/1.1 301 Moved Permanently" );
       header( "Location:".$url);
    }
}