package model

import (
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/database/gdb"
)

type PnpProviderModel struct {
    Id int `json:"id"`//id
    Slot string `json:"slot"` //唯一标识
    Name string `json:"name"` //名称
    Config string `json:"config"` //配置
    Remarks string `json:"remarks"`//备注
    Is_enabled int `json:"is_enabled"` //开启状态 1开启 2关闭
    Op_uid int `json:"op_uid"` //操作人ID
    Op_uname string `json:"op_uname"` //操作人名称
    Created_at int64 `json:"created_at"`
    Update_at   int64 `json:"update_at"`
}

func NewPnpProviderModel() *PnpProviderModel {
    return &PnpProviderModel{}
}

func (c *PnpProviderModel)getTableName() string {
    return "qz_pnp_provider"
}

func (c *PnpProviderModel)getTable() *gdb.Model {
    return db.Table("qz_pnp_provider a").Safe()
}

func (c *PnpProviderModel)GetProviderInfo(id string)(g.Map,error)  {
    res,err := c.getTable().Where("id = ?",id).Fields("slot,name,config,remarks,is_enabled").One()
    return res.ToMap(),err
}

func (c *PnpProviderModel)Save(data g.Map)  int64{
    res,err := g.DB().Table(c.getTableName()).Safe().Data(data).Save()
    if err != nil {
        return 0
    }
    lastId,_ := res.LastInsertId()
    return lastId
}

func (c *PnpProviderModel)GetProviderList() (gdb.List,error) {
    res,err := c.getTable().Fields("id,slot,name,op_uname,created_at,is_enabled").All()
    return res.ToList(),err
}

func (c *PnpProviderModel)GetProviderDropDownlist() (gdb.List,error) {
    res,err := c.getTable().Where("a.is_enabled = 1").Fields("slot,name,op_uname,created_at,is_enabled").All()
    return res.ToList(),err
}

func (c *PnpProviderModel)Update(id int,data g.Map)(bool,error)  {
    res,err := g.DB().Table(c.getTableName()).Where("id = ?",id).Data(data).Update()
    if err != nil {
        return false,err
    }
    ret,err := res.RowsAffected()
    return !!(ret > 0),err
}