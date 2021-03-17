<?php


/**
*
*/
namespace Mobile\Controller;
use Mobile\Common\Controller\MobileBaseController;
class DesignerController extends MobileBaseController
{
    public function index()
    {
        $id = I("get.id");
        switch ($id) {
            case '1':
                $template = 'desone';
                break;
            case '2':
                # code...
                $template = 'destwo';
                break;
            case '3':
                # code...
                $template = 'desthree';
                break;
            default:
                $this->_empty();
                break;
        }

        $this->display($template);
    }
}