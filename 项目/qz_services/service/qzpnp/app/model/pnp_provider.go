package model

import (
    "github.com/gogf/gf/database/gdb"
    "github.com/gogf/gf/frame/g"
)

type PnpProviderModel struct {

}

func NewPnpProviderModel() *PnpProviderModel  {
    return &PnpProviderModel{}
}

func  (c *PnpProviderModel)getTable() *gdb.Model {
    return g.DB().Table("qz_pnp_provider a").Safe()
}

func (c *PnpProviderModel)GetProviderInfo(slot string) (gdb.Map,error)  {
    res,e := c.getTable().Where("slot = ?",slot).Fields("config,is_enabled").One()
    return res.Map(),e
}
