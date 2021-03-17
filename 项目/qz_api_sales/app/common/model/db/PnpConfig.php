<?php

namespace app\common\model\db;

use think\Model;
use think\db\Query;

class PnpConfig extends Model {

    public function geConfigByOptions($options){
        $map = new Query();
        $map->where("pnp_option", "in", $options);
        
        return $this->where($map)->select();
    }

}
