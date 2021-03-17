<?php
namespace Sub\Controller;
use Sub\Common\Controller\SubBaseController;
class SpecialController extends SubBaseController
{
    public function tuTopLayer()
    {
        echo  R("Home/Special/tuTopLayer");
        die();
    }
}