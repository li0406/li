package model

import (
    "github.com/gogf/gf/g/database/gdb"
)

type QuyuModel struct {
}

func NewQuyuModel() *QuyuModel {
    return &QuyuModel{}
}

func (that *QuyuModel) table() string {
    return "qz_quyu"
}

// 配置数据表（带别名）
func (that *QuyuModel) connect() *gdb.Model {
    return db.Table(that.table() + " as q").Safe()
}

func (that *QuyuModel) GetProvince() (gdb.List, error) {
    dbQuery := db.Table("qz_province")
    dbQuery.Fields("qz_provinceid,qz_province,province_abc")
    ret, err := dbQuery.Select()
    return ret.ToList(), err
}

func (that *QuyuModel) GetQuyu() (gdb.List, error) {
    dbQuery := that.connect()
    dbQuery.Fields("cid,uid,cname").OrderBy("px_abc")
    ret, err := dbQuery.Select()
    return ret.ToList(), err
}

func (that *QuyuModel)GetPnpCity()(gdb.List, error)  {
    res,err :=that.connect().Where(" q.cid <> '000001'").
        OrderBy("px_abc").All()
    return res.ToList(),err
}
