<?php

namespace Sub\Controller;

use Sub\Common\Controller\SubBaseController;

class HeaderController extends SubBaseController
{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function headerCity()
    {
        R("Common/Header/headerCity");
        exit;
    }
}
