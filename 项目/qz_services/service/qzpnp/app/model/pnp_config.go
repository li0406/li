package model

import (
    "github.com/gogf/gf/database/gdb"
    "github.com/gogf/gf/frame/g"
)

type PnpConfig struct {
}

func NewPnpConfigModel() *PnpConfig {
    return &PnpConfig{}
}

func (c *PnpConfig)getTable() *gdb.Model {
    return  g.DB().Table("qz_pnp_config a").Safe()
}

func (c *PnpConfig)GetConfigInfo(option_name string) (result gdb.Map,err error) {
     r,e := c.getTable().Where("pnp_option = ?",option_name).
        Fields("pnp_option_value").One()
    return r.Map(),e
}

