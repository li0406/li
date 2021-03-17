package model

import (
	"github.com/gogf/gf/database/gdb"
	"github.com/gogf/gf/frame/g"
)

//pnp_city_white_map
type PnpCityWhiteMapModel struct {
}

func NewPnpCityWhiteMapModel() *PnpCityWhiteMapModel {
	return &PnpCityWhiteMapModel{}
}
func (c *PnpCityWhiteMapModel) getTable() *gdb.Model {
	return g.DB().Table("qz_pnp_city_white_map a").Safe()
}

func (c *PnpCityWhiteMapModel) GetOpenCity() (gdb.List, error) {
	res, err := c.getTable().Fields("city_id").All()
	return res.List(), err
}
