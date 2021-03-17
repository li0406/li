package logic

import (
	"github.com/gogf/gf/database/gdb"
	"github.com/gogf/gf/frame/g"
	"qzpnp/app/model"
	"time"
)

type PnpLogic struct {
}

func NewPnpLogic() *PnpLogic {
	return &PnpLogic{}
}

func (c *PnpLogic) GetTelX(tel_a string) (result gdb.Map, err error) {
	result, err = model.NewPnpTelephoneModel().GetTelX(tel_a)
	return result, err
}

func (c *PnpLogic) GetAllTelX(tel_a string) (result gdb.List, err error) {
	result, err = model.NewPnpTelephoneModel().GetAllTelX(tel_a)
	return result, err
}

func (c *PnpLogic) GetNotUseTel(city_no string) (result gdb.Map, err error) {
	result, err = model.NewPnpTelephoneModel().GetNotUseTel(city_no)
	return result, err
}

func (c *PnpLogic) Update(m *model.PnpTelephoneModel) {
	data := g.Map{
		"tel_x":       m.Tel_x,
		"tel_a":       m.Tel_a,
		"expire_time": m.Expire_time,
		"is_bind":     m.Is_bind,
		"updated_at":  time.Now().Unix(),
		"extra":       m.Extra,
	}
	model.NewPnpTelephoneModel().UpdatePnp(m.Tel_x, data)
}

func (c *PnpLogic) GetProviderInfo(slot string) (gdb.Map, error) {
	return model.NewPnpProviderModel().GetProviderInfo(slot)
}

func (c *PnpLogic) UpdatePnpByOrder(m *model.PnpTelephoneModel) bool {
	data := g.Map{
		"tel_x":       m.Tel_x,
		"tel_a":       m.Tel_a,
		"expire_time": m.Expire_time,
		"is_bind":     m.Is_bind,
		"updated_at":  time.Now().Unix(),
		"extra":       "",
	}
	res, err := model.NewPnpTelephoneModel().UpdatePnpByOrder(m.Tel_x, m.Extra, data)
	if err != nil {
		return false
	}
	return res
}

func (c *PnpLogic) GetUnBindTelMap(tel string) g.Map {
	res, err := model.NewPnpTelephoneModel().GetUnBindTelMap(tel)
	if err != nil {
		return nil
	}
	return res
}

func (c *PnpLogic) GetTimeOutTel() g.List {
	now := time.Now().Format("2006-01-02 15:04:05")
	res, err := model.NewPnpTelephoneModel().GetTimeOutTel(now)
	if err != nil {
		return nil
	}
	return res
}

func (c *PnpLogic) GetCityNo(city_id string) string {
	m := map[string]string{
		"110100": "10",   //北京
		"500100": "10",   //重亲
		"620100": "931",  //兰州
		"320500": "0512", //苏州
	}

	return m[city_id]
}
