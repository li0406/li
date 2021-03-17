package logic

import (
	"github.com/gogf/gf/database/gdb"
	"qzpnp/app/model"
)

type OrderLogic struct {
}

func NewOrderLogic() *OrderLogic {
	return &OrderLogic{}
}

func (c *OrderLogic) GetOrderInfo(order_id string) (info gdb.Map, err error) {
	info, err = model.NewOrderModel().GetOrderInfo(order_id)
	return info, err
}

func (c *OrderLogic) SetOrderTelMap(order_id string, telx string, tela string, sub_id string) (int64, error) {
	m := gdb.Map{
		"tel_x":    telx,
		"order_id": order_id,
		"tel_a":    tela,
		"sub_id":   sub_id,
	}

	id, err := model.NewPnpOrderMapModel().SetOrderTelMap(m)
	return id, err
}

func (c *OrderLogic) GetOrderTelMap(order_id string) gdb.Map {
	res, err := model.NewPnpOrderMapModel().GetOrderTelMap(order_id)
	if err == nil {
		return res
	}
	return nil
}

func (c *OrderLogic) GetOrderAllTelMap(order_id string) gdb.List {
	res, err := model.NewPnpOrderMapModel().GetOrderAllTelMap(order_id)
	if err == nil {
		return res
	}
	return nil
}

func (c *OrderLogic) UpdateOrderTelBindState(tel_x string, sub_id string) (bool, error) {
	m := gdb.Map{
		"is_bind": 2,
	}
	id, err := model.NewPnpOrderMapModel().UpdateOrderTelBindState(tel_x, sub_id, m)
	return id, err
}

func (c *OrderLogic) UpdateOrderTelBindAllState(tel_a string) (bool, error) {
	m := gdb.Map{
		"is_bind": 2,
	}
	id, err := model.NewPnpOrderMapModel().UpdateOrderTelBindAllState(tel_a, m)
	return id, err
}

func (c *OrderLogic) GetOpenCity(city_id string) bool {
	res, err := model.NewPnpCityWhiteMapModel().GetOpenCity()
	if err != nil {
		return false
	}
	if len(res) > 0 {
		for _, v := range res {
			if v["city_id"] == city_id {
				return true
			}
		}
	}
	return false
}
