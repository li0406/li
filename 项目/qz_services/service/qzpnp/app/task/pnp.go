package task

import (
	"fmt"
	v1 "qzpnp/app/controller/v1"
	"qzpnp/app/logic"
)

type PnpTask struct {
}

func NewPnpTask() *PnpTask {
	return &PnpTask{}
}

func (c *PnpTask) Run() {
	c.GetTimeOutTel()
}

func (c *PnpTask) GetTimeOutTel() {
	type PnpStruct struct {
		Tel_a string `json:"tel_a"`
		Tel_x string `json:"tel_x"`
		SubId string `json:"sub_id"`
	}
	//解除绑定时间到期的tal_x号码
	res := logic.NewPnpLogic().GetTimeOutTel()
	fmt.Println(res)
	if len(res) > 0 {
		con := v1.NewPnpContorller()
		tels := []v1.PnpStruct{}
		for _, v := range res {
			tel := v1.PnpStruct{
				Tel_x:    v["tel_x"].(string),
				Tel_a:    v["tel_a"].(string),
				SubId:    v["extra"].(string),
				Order_id: v["order_id"].(string),
			}
			tels = append(tels, tel)
		}
		//解除电话绑定状态
		con.UnBindTel(tels)
	}
}
