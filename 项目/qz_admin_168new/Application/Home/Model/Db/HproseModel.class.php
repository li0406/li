<?php
namespace Home\Model\Db;
use Think\Model;

class HproseModel extends Model{
    Protected $autoCheckFields = false;
    public function _initialize()
    {
        import("@.ORG.hprose-php.src.Hprose","",".php");
    }

    public function connect($Hprosehost)
    {
        $Hprose = new HproseHttpClient($Hprosehost);
        $this->Hprose = $Hprose;
    }

}