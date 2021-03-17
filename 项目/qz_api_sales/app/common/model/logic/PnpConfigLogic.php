<?php

namespace app\common\model\logic;

use app\common\model\db\PnpConfig;

class PnpConfigLogic {

    public function getAxbConfig(){
        $options = [
            "pnp_axb_switch",
            "pnp_axb_expire",
            "pnp_axb_provider",
            "pnp_axb_com_tel_num",
        ];

        $pnpConfigModel = new PnpConfig();
        $list = $pnpConfigModel->geConfigByOptions($options);
        return array_column($list->toArray(), "pnp_option_value", "pnp_option");
    }
    
}