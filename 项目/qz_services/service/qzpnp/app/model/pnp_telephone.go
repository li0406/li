package model

import (
	"github.com/gogf/gf/database/gdb"
	"github.com/gogf/gf/frame/g"
)

type PnpTelephoneModel struct {
	Id          int    `json:"id"`
	Tel_x       string `json:"tel_x"`
	Tel_a       string `json:"tel_a"`
	Expire_time string `json:"expire_time"`
	Is_bind     int    `json:"is_bind"`
	Extra       string `json:"extra"`
}

func NewPnpTelephoneModel() *PnpTelephoneModel {
	return &PnpTelephoneModel{}
}

func (c *PnpTelephoneModel) getTable() *gdb.Model {
	return g.DB().Table("qz_pnp_telephone a").Safe()
}

func (c *PnpTelephoneModel) GetTelX(tel_a string) (result gdb.Map, err error) {
	r, e := c.getTable().Where("a.tel_a = ?", tel_a).
		Fields("tel_x,expire_time,is_bind,city_no,extra").One()
	return r.Map(), e
}

func (c *PnpTelephoneModel) GetAllTelX(tel_a string) (result gdb.List, err error) {
	r, e := c.getTable().Where("a.tel_a = ?", tel_a).
		Fields("id,tel_x,expire_time,is_bind,city_no,extra").All()
	return r.List(), e
}

func (c *PnpTelephoneModel) UpdatePnp(tel_x string, data gdb.Map) (bool, error) {
	result, err := c.getTable().Where("tel_x = ?", tel_x).Data(data).Unscoped().Update()
	if err != nil {
		return false, err
	}
	ret, err := result.RowsAffected()
	return !!(ret > 0), err
}

func (c *PnpTelephoneModel) UpdatePnpByOrder(tel_x string, sub_id string, data gdb.Map) (bool, error) {
	result, err := c.getTable().Where("tel_x = ? and extra = ?", g.Slice{tel_x, sub_id}).Data(data).Unscoped().Update()
	if err != nil {
		return false, err
	}
	ret, err := result.RowsAffected()
	return !!(ret > 0), err
}

func (c *PnpTelephoneModel) GetNotUseTel(city_no string) (result gdb.Map, err error) {
	m := g.Map{
		"is_bind": 2,
	}
	if city_no != "" {
		m["city_no"] = city_no
	}

	r, e := c.getTable().Where(m).Order("expire_time,id").Fields("id,tel_x,expire_time,is_bind,city_no").One()
	return r.Map(), e
}

func (c *PnpTelephoneModel) GetUnBindTelMap(tel string) (g.Map, error) {
	res, err := c.getTable().Where("a.tel_a =? and b.order_id is null", tel).
		LeftJoin("qz_pnp_order_map b", "b.tel_x = a.tel_x and b.sub_id = a.extra").
		Fields("a.tel_x,a.tel_a,a.extra").One()
	return res.Map(), err

}

func (c *PnpTelephoneModel) GetTimeOutTel(t string) (g.List, error) {
	res, err := c.getTable().Where("a.expire_time <= ?", t).
		InnerJoin("qz_pnp_order_map b ", "a.tel_a = b.tel_a and a.tel_x = b.tel_x and a.extra = b.sub_id").
		Fields("a.tel_x,a.tel_a,a.extra,b.order_id").All()
	return res.List(), err
}
