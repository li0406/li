<?php

namespace app\index\controller;

class Qrcode extends Base
{
    public function index()
    {
        $url = config("H5_HOST")."/qz/share?id=downloadButton&channel=qz-pc-a40";
        return redirect($url);
    }
}