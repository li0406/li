package model

import (
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/database/gdb"
)

type PnpCityWhiteMapModel struct {
}

func NewPnpCityWhiteMapModel() *PnpCityWhiteMapModel {
    return &PnpCityWhiteMapModel{}
}

func (c *PnpCityWhiteMapModel)getTableName() string {
    return "qz_pnp_city_white_map"
}


func (c *PnpCityWhiteMapModel)getTable() *gdb.Model {
    return db.Table("qz_pnp_city_white_map a").Safe()
}

func (c *PnpCityWhiteMapModel)GetOpenCity()(gdb.List,error)  {
    res,err := c.getTable().InnerJoin("qz_quyu q","q.cid = a.city_id").
        Fields("q.cid,q.cname").All()
    return res.ToList(),err
}

func  (c *PnpCityWhiteMapModel)DelOpenCity()(bool,error)  {
    res,err := g.DB().Table(c.getTableName()).Delete()
    if err !=nil {
        return false,err
    }
    ret,err := res.RowsAffected()
    return !!(ret > 0),err
}

func (c *PnpCityWhiteMapModel)SaveAll(data gdb.List)(error)   {
    _,err := g.DB().Table(c.getTableName()).Data(data).Insert()
    if err != nil {
         return err
    }
    return  nil
}
