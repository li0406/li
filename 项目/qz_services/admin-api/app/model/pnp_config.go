package model

import (
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/database/gdb"
)

type PnpConfigModel struct {
    Option []PnpOption `json:"option"`
    Citys []PnpCitys `json:"citys"`
    Open_city []PnpCitys `json:"open_city"`
}

type PnpOption struct {
    Pnp_option string `json:"pnp_option"`
    Pnp_option_value string `json:"pnp_option_value"`
}

type PnpCitys struct {
    Cid string `json:"cid"`
    Cname string `json:"cname"`
}


func NewPnpConfigModel() *PnpConfigModel {
    return &PnpConfigModel{}
}

func (c *PnpConfigModel)getTableName() string {
    return "qz_pnp_config"
}


func (c *PnpConfigModel)getTable() *gdb.Model {
    return db.Table("qz_pnp_config a").Safe()
}


func (c *PnpConfigModel)GetAllConfig()(gdb.List, error)  {
    res,err :=c.getTable().Fields("pnp_option,pnp_option_value").All()
    return res.ToList(),err
}

func (c *PnpConfigModel)UpdaeByName(option_name string,data gdb.Map)(bool,error)  {
   res,err := g.DB().Table(c.getTableName()).Where("pnp_option = ?",option_name).Data(data).Update()
   if err != nil {
        return false,err
    }
   ret, err := res.RowsAffected()
   return !!(ret > 0), err
}