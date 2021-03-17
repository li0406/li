package model

import (
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/database/gdb"
)

type PnpTelephoneModel struct {

}
func NewPnpTelephoneModel() *PnpTelephoneModel {
    return &PnpTelephoneModel{}
}

func (c *PnpTelephoneModel)getTableName() string {
    return "qz_pnp_telephone"
}


func (c *PnpTelephoneModel)getTable() *gdb.Model {
    return db.Table("qz_pnp_telephone a").Safe()
}

func (c *PnpTelephoneModel)GetPnpTelListCount(tel string,city string,status int)(int,error)  {
    m := g.Map{}
    if tel != "" {
        m["a.tel_x "] = tel
    }

    if city != "" {
        m["a.city_name"] = city
    }

    if status != 0 {
        m["a.is_bind"] = status
    }

    res,err :=c.getTable().Where(m).OrderBy("id").Count()

    return res,err
}

func (c *PnpTelephoneModel)GetPnpTelList(tel string,city string,status int,pageIndex int,pageCount int)(g.List,error)  {
    m := g.Map{}
    if tel != "" {
        m["a.tel_x "] = tel
    }

    if city != "" {
        m["a.city_name"] = city
    }

    if status != 0 {
        m["a.is_bind"] = status
    }

    res,err :=c.getTable().Where(m).OrderBy("id").Limit(pageIndex,pageCount).Fields("city_name,expire_time,is_bind,tel_x,tel_a,updated_at").All()
    return res.ToList(),err
}

func (c *PnpTelephoneModel)GetPnpDropDownCity()(g.List,error)  {
    res,err := c.getTable().Fields("city_name").GroupBy("city_name").All()
    return res.ToList(),err
}