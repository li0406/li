<?php
namespace Home\Model\Db;
Use Think\Model;
/**
 *
 */
class PnpTelephoneModel extends Model
{
    public function getTelInfoByTelx($tel)
    {
        $map = [
            "tel_x" => ["EQ",$tel],
        ];
        return $this->where($map)->field("tel_a")->find();
    }
}