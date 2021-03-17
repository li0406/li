package model

import "github.com/gogf/gf/g/database/gdb"

type PnpOrderMapModel struct {

}
func NewPnpOrderMapModel() *PnpOrderMapModel {
    return &PnpOrderMapModel{}
}

func (c *PnpOrderMapModel)getTableName() string {
    return "qz_pnp_order_map"
}


func (c *PnpOrderMapModel)getTable() *gdb.Model {
    return db.Table("qz_pnp_order_map a").Safe()
}

func (c *PnpOrderMapModel)GetTelOrderListCount(tel_x string,order_id string)(int,error)  {
    m := gdb.Map{
        "a.tel_x":tel_x,
    }

    if order_id != "" {
        m["a.order_id"] = order_id
    }
    res,err :=c.getTable().Where(m).
        InnerJoin("qz_pnp_telephone b","b.tel_x = a.tel_x").Count()
    return res,err
}


func (c *PnpOrderMapModel)GetTelOrderList(tel_x string,order_id string,pageIndex int,pageCount int)(gdb.List,error)  {
    m := gdb.Map{
        "a.tel_x":tel_x,
    }

    if order_id != "" {
        m["a.order_id"] = order_id
    }
    res,err :=c.getTable().Where(m).
        InnerJoin("qz_pnp_telephone b","b.tel_x = a.tel_x").Limit(pageIndex,pageCount).
        Fields("a.tel_x,a.tel_a,a.order_id,b.city_name,a.is_bind").OrderBy("a.id desc").All()
    return res.ToList(),err
}

func (c *PnpOrderMapModel)GetVoiceList(order_id string)(gdb.List,error)  {
    res,err := c.getTable().Where("a.order_id = ?",order_id).
                InnerJoin("qz_pnp_tel_callback b","b.remark = a.order_id and a.tel_x = b.tel_x").
                OrderBy("b.id desc").
                GroupBy("b.subid,b.callid").Fields("releasedir,created_at,b.tel_b,a.tel_a,record_url_save").
                All()
    return res.ToList(),err
}

