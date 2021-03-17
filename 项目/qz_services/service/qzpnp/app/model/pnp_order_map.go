package model

import (
	"github.com/gogf/gf/database/gdb"
	"github.com/gogf/gf/frame/g"
)

type PnpOrderMapModel struct {
}

func NewPnpOrderMapModel() *PnpOrderMapModel {
	return &PnpOrderMapModel{}
}

func (c *PnpOrderMapModel) getTableName() string {
	return "qz_pnp_order_map"
}

func (c *PnpOrderMapModel) getTable() *gdb.Model {
	return g.DB().Table("qz_pnp_order_map a").Safe()
}

func (c *PnpOrderMapModel) SetOrderTelMap(m gdb.Map) (int64, error) {
	res, err := g.Table(c.getTableName()).Data(m).Safe().Insert()
	if err != nil {
		return 0, err
	}
	r, err := res.LastInsertId()
	return r, err
}

func (c *PnpOrderMapModel) GetOrderTelMap(order_id string) (gdb.Map, error) {
	res, err := c.getTable().Where("order_id = ?", order_id).Order("id desc").One()
	if err == nil {
		return res.Map(), err
	}
	return nil, err
}

func (c *PnpOrderMapModel) GetOrderAllTelMap(order_id string) (gdb.List, error) {
	res, err := c.getTable().Where("order_id = ? and is_bind = 1", order_id).All()
	if err == nil {
		return res.List(), err
	}
	return nil, err
}

func (c *PnpOrderMapModel) UpdateOrderTelBindState(tel_x string, sub_id string, data gdb.Map) (bool, error) {
	result, err := c.getTable().Where("tel_x = ? and sub_id = ?", g.Slice{tel_x, sub_id}).Data(data).Update()
	res, err := result.RowsAffected()
	return !!(res > 0), err
}

func (c *PnpOrderMapModel) UpdateOrderTelBindAllState(tel_a string, data gdb.Map) (bool, error) {
	result, err := c.getTable().Where("tel_a = ? ", g.Slice{tel_a}).Data(data).Update()
	res, err := result.RowsAffected()
	return !!(res > 0), err
}
