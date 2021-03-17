package logic

import (
    "github.com/gogf/gf/database/gdb"
    "qzpnp/app/model"
)

type PnpConfigLogic struct {
}

func NewPnpConfigLogic() *PnpConfigLogic{
    return &PnpConfigLogic{}
}

func (c *PnpConfigLogic)GetConfigInfo(option_name string)(option gdb.Map,err error) {
    option,err = model.NewPnpConfigModel().GetConfigInfo(option_name);
    return option,err
}

