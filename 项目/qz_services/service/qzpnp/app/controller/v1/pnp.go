package v1

import (
	"fmt"
	"github.com/gogf/gf/encoding/gjson"
	"github.com/gogf/gf/frame/gmvc"
	uuid "github.com/satori/go.uuid"
	"qzpnp/app/enum"
	"qzpnp/app/logic"
	"qzpnp/app/model"
	"qzpnp/app/service"
	"qzpnp/library/util"
	"strconv"
	"strings"
	"time"
)

type PnpController struct {
	gmvc.Controller
	Pnp PnpStruct
}

type PnpStruct struct {
	Tel_a       string `json:"tel_a"`
	Tel_x       string `json:"tel_x"`
	Expire      int64  `json:"expire"`
	Provider    string `json:"provider"`
	Pnp_bind    int    `json:"pnp_bind"` //1.推送 2.解绑
	Config      string `json:"config"`
	Areacode    int    `json:"areacode"`
	SubId       string `json:"sub_id"`
	Order_id    string `json:"order_id"`
	Expire_date string `json:"expire_date"`
}

func NewPnpContorller() *PnpController {
	return &PnpController{}
}

func (c *PnpController) GetTelphoneByOrderId() {
	gjson := c.Request
	order_id := gjson.GetString("order_id")

	if order_id == "" {
		json, _ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
		c.Request.Response.WriteJson(json)
		c.Request.ExitAll()
	}

	//查询订单数据
	info, err := logic.NewOrderLogic().GetOrderInfo(order_id)

	if err != nil {
		json, _ := util.EncodeResponseJson(5000003, enum.CODE_5000003, nil)
		c.Request.Response.WriteJson(json)
		c.Request.ExitAll()
	}

	if info["tel"].(string) == "" {
		json, _ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
		c.Request.Response.WriteJson(json)
		c.Request.ExitAll()
	}
	tel := info["tel"].(string)

	//获取开启配置
	//开关设置
	sw, err := logic.NewPnpConfigLogic().GetConfigInfo("pnp_switch")
	if err != nil {
		json, _ := util.EncodeResponseJson(6000003, enum.CODE_6000003, nil)
		c.Request.Response.WriteJson(json)
		c.Request.ExitAll()
	}

	var data = make(map[string]interface{})
	data["protect"] = 2 //未保护

	//订单是否申请了显号
	res := logic.NewOrderLogic().GetOrderTelMap(order_id)
	if res != nil {
		if len(res) > 0 {
			//申请了显号
			if res["is_show"].(int) == 1 {
				data["tel"] = tel
				json, _ := util.EncodeResponseJson(0, enum.CODE_0, data)
				c.Request.Response.WriteJson(json)
				c.Request.ExitAll()
			}
		}
	}

	//开启保护
	if sw["pnp_option_value"].(string) == "1" {
		//通过订单电话查询tel_x的号码
		result, err := logic.NewPnpLogic().GetTelX(info["tel"].(string))
		if err != nil {
			json, _ := util.EncodeResponseJson(5000003, enum.CODE_5000003, nil)
			c.Request.Response.WriteJson(json)
			c.Request.ExitAll()
		}

		if len(result) > 0 && result["is_bind"].(int) == 1 {
			//获取当前时间
			now := time.Now().Local().Format("2006-01-02 15:04:05")
			current, _ := time.Parse("2006-01-02 15:04:05", result["expire_time"].(string))

			if current.String() > now {
				tel = result["tel_x"].(string)
				data["protect"] = 1
			}
		}
	}

	data["tel"] = tel
	json, _ := util.EncodeResponseJson(0, enum.CODE_0, data)
	c.Request.Response.WriteJson(json)
}

func (c *PnpController) BindTelXByOrder() {
	gjson := c.Request
	order_id := gjson.GetString("order_id")

	if order_id == "" {
		json, _ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
		c.Request.Response.WriteJson(json)
		c.Request.ExitAll()
	}

	//查询订单是否绑定
	result := logic.NewOrderLogic().GetOrderTelMap(order_id)
	if len(result) > 0 {
		if result["is_bind"].(int) == 1 {
			json, _ := util.EncodeResponseJson(6000005, enum.CODE_6000005, nil)
			c.Request.Response.WriteJson(json)
			c.Request.ExitAll()
		}
	}

	//查询订单数据
	info, err := logic.NewOrderLogic().GetOrderInfo(order_id)

	if err != nil {
		json, _ := util.EncodeResponseJson(5000003, enum.CODE_5000003, nil)
		c.Request.Response.WriteJson(json)
		c.Request.ExitAll()
	}

	if info["tel"].(string) == "" {
		json, _ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
		c.Request.Response.WriteJson(json)
		c.Request.ExitAll()
	}

	//添加开发城市列表
	//查询该城市否是再开放城市之内
	is_open := logic.NewOrderLogic().GetOpenCity(info["cs"].(string))

	if !is_open {
		json, _ := util.EncodeResponseJson(6000006, enum.CODE_6000006, nil)
		c.Request.Response.WriteJson(json)
		c.Request.ExitAll()
	}

	//获取电话号的城市ID
	city_no := logic.NewPnpLogic().GetCityNo(info["cs"].(string))

	if city_no == "" {
		json, _ := util.EncodeResponseJson(6000006, enum.CODE_6000006, nil)
		c.Request.Response.WriteJson(json)
		c.Request.ExitAll()
	}

	tel := info["tel"].(string)
	code, msg := c.bindTel(tel, order_id, city_no)

	if code != 0 {
		json, _ := util.EncodeResponseJson(code, msg, nil)
		c.Request.Response.WriteJson(json)
		c.Request.ExitAll()
	}

	//添加订单和电话的绑定关系
	//获取未绑定订单的X号
	result = logic.NewPnpLogic().GetUnBindTelMap(info["tel"].(string))
	if len(result) == 0 {
		json, _ := util.EncodeResponseJson(4000001, enum.CODE_4000001, nil)
		c.Request.Response.WriteJson(json)
		c.Request.ExitAll()
	}

	if len(result) == 0 {
		json, _ := util.EncodeResponseJson(4000001, enum.CODE_4000001, nil)
		c.Request.Response.WriteJson(json)
		c.Request.ExitAll()
	}

	//添加订单和电话的绑定关系
	_, err = logic.NewOrderLogic().SetOrderTelMap(order_id, result["tel_x"].(string), info["tel"].(string), result["extra"].(string))
	if err != nil {
		json, _ := util.EncodeResponseJson(5000003, enum.CODE_5000003, nil)
		c.Request.Response.WriteJson(json)
		c.Request.ExitAll()
	}

	json, _ := util.EncodeResponseJson(0, enum.CODE_0, nil)
	c.Request.Response.WriteJson(json)
}

func (c *PnpController) BindTelXByTel() {
	gjson := c.Request
	tel := gjson.GetString("tel")
	if tel == "" {
		json, _ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
		c.Request.Response.WriteJson(json)
		c.Request.ExitAll()
	}
	c.bindTel(tel, "", "")
}

func (c *PnpController) bindTel(tel string, order_id string, city_no string) (int, string) {
	//隐号保护
	//获取开启配置
	sw, err := logic.NewPnpConfigLogic().GetConfigInfo("pnp_switch")
	if err != nil {
		return 4000002, enum.CODE_4000002
	}

	//开启保护
	if sw["pnp_option_value"].(string) == "1" {
		//有效时间
		result, err := logic.NewPnpConfigLogic().GetConfigInfo("pnp_expire")
		if err != nil {
			return 4000002, enum.CODE_4000002
		}
		expire, _ := strconv.Atoi(result["pnp_option_value"].(string))
		//默认go时间
		if expire == 0 {
			expire = 10 //默认10天
		}
		//转化成秒
		expire = 3600 * 24 * expire

		//供应商
		result, err = logic.NewPnpConfigLogic().GetConfigInfo("pnp_provider")
		if err != nil {
			return 4000002, enum.CODE_4000002
		}
		provider := result["pnp_option_value"].(string)

		//查询供应商信息
		result, err = logic.NewPnpLogic().GetProviderInfo(provider)
		if err != nil {
			return 4000002, enum.CODE_4000002
		}

		if result["is_enabled"] == "2" {
			return 6000001, enum.CODE_6000001
		}
		con := result["config"].(string)

		//实例化电话对象
		m := model.NewPnpTelephoneModel()
		duration, _ := time.ParseDuration("+" + strconv.Itoa(expire) + "s")
		t := util.TimeNextEarlyMorning(time.Now().Add(duration), 2)
		local, _ := time.LoadLocation("Local")
		current := t.Format("2006-01-02 15:04:05")
		tp, _ := time.ParseInLocation("2006-01-02 15:04:05", current, local)
		now := time.Now().Unix()
		exp_diff := tp.Unix() - now

		//未有绑定有效X码
		//获取tel_X号码
		result, err = logic.NewPnpLogic().GetNotUseTel(city_no)

		if err != nil {
			return 4000002, enum.CODE_4000002
		}

		if len(result) == 0 {
			return 6000002, enum.CODE_6000002
		}

		//号码绑定
		m.Tel_x = result["tel_x"].(string)
		m.Tel_a = tel
		m.Expire_time = current
		m.Is_bind = 2
		//添加绑定
		logic.NewPnpLogic().Update(m)

		pnpStruct := PnpStruct{
			Tel_a:       tel,
			Tel_x:       result["tel_x"].(string),
			Provider:    provider,
			Expire:      exp_diff,
			Pnp_bind:    1,
			Config:      con,
			Areacode:    result["city_no"].(int),
			Order_id:    order_id,
			Expire_date: current,
		}

		//定义通道
		ch := make(chan PnpStruct)
		f := make(chan int)
		go producer(ch, pnpStruct)
		go send_tel(ch, f)
		<-f
		return 0, enum.CODE_0
	}
	return 6000004, enum.CODE_6000004
}

func (c *PnpController) UnBindTelXByOrder() {
	gjson := c.Request
	order_id := gjson.GetString("order_id")

	if order_id == "" {
		json, _ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
		c.Request.Response.WriteJson(json)
		c.Request.ExitAll()
	}

	//查询订单的绑定的X号
	res := logic.NewOrderLogic().GetOrderAllTelMap(order_id)
	if len(res) == 0 {
		json, _ := util.EncodeResponseJson(4000001, enum.CODE_4000001, nil)
		c.Request.Response.WriteJson(json)
		c.Request.ExitAll()
	}

	tels := []PnpStruct{}
	for _, v := range res {
		tel := PnpStruct{
			Tel_x:    v["tel_x"].(string),
			Tel_a:    v["tel_a"].(string),
			SubId:    v["sub_id"].(string),
			Order_id: order_id,
		}
		tels = append(tels, tel)
	}

	code, msg := c.UnBindTel(tels)
	json, _ := util.EncodeResponseJson(code, msg, nil)
	c.Request.Response.WriteJson(json)
}

func (c *PnpController) UnbindTelXByTel() {
	gjson := c.Request
	tel := gjson.GetString("tel")
	//查询是否绑定
	result, err := logic.NewPnpLogic().GetAllTelX(tel)
	if err != nil {
		json, _ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
		c.Request.Response.WriteJson(json)
		c.Request.ExitAll()
	}

	tels := []PnpStruct{}
	for _, v := range result {
		tel := PnpStruct{
			Tel_x: v["tel_x"].(string),
			Tel_a: tel,
			SubId: v["extra"].(string),
		}
		tels = append(tels, tel)
	}

	code, msg := c.UnBindTel(tels)
	json, _ := util.EncodeResponseJson(code, msg, nil)
	c.Request.Response.WriteJson(json)
}

func (c *PnpController) GetTexInfo() {
	req := c.Request
	tel := req.GetString("tel")

	if tel == "" {
		json, _ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
		c.Request.Response.WriteJson(json)
		c.Request.ExitAll()
	}

	//供应商
	result, err := logic.NewPnpConfigLogic().GetConfigInfo("pnp_provider")
	if err != nil {
		json, _ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
		c.Request.Response.WriteJson(json)
		c.Request.ExitAll()
	}
	provider := result["pnp_option_value"].(string)
	//查询供应商信息
	res, err := logic.NewPnpLogic().GetProviderInfo(provider)

	config := res["config"].(string)

	switch provider {
	case "dongxin":
		dx := dongxin.NewDongXin()
		//获取毫秒
		nano := strconv.FormatInt(time.Now().UnixNano()/1e6, 10)
		//截取最后三位
		last_no := nano[len(nano)-3:]
		ts := time.Now().Format("20060102150405") + last_no
		//解析配置文件
		con, _ := gjson.DecodeToJson(config)
		dx.Request_url = con.GetString("url")
		send := dongxin.UnBindStruct{
			Ts:        ts,
			Appkey:    con.GetString("appkey"),
			Secretkey: con.GetString("secretkey"),
		}

		dx.Unbind = send
		dx.GetTexInfo(tel)
		break
	}
}

func (c *PnpController) UnBindTel(tels []PnpStruct) (int, string) {
	//供应商
	res, err := logic.NewPnpConfigLogic().GetConfigInfo("pnp_provider")
	if err != nil {
		return 4000002, enum.CODE_4000002
	}
	provider := res["pnp_option_value"].(string)

	//查询供应商信息
	res, err = logic.NewPnpLogic().GetProviderInfo(provider)
	if err != nil {
		return 4000002, enum.CODE_4000002
	}

	if len(res) == 0 {
		return 4000002, enum.CODE_4000002
	}

	if res["is_enabled"] == "2" {
		return 6000001, enum.CODE_6000001
	}
	con := res["config"].(string)

	for _, v := range tels {
		v.Config = con
		v.Pnp_bind = 2
		v.Provider = provider
		//定义通道
		ch := make(chan PnpStruct)
		f := make(chan int)
		go producer(ch, v)
		go send_tel(ch, f)
		<-f
	}
	return 0, enum.CODE_0
}

func producer(c chan PnpStruct, s PnpStruct) {
	defer close(c) //关闭通道
	c <- s         //将s值赋值给通道C
}

func send_tel(c chan PnpStruct, f chan int) {
	for {
		if v, ok := <-c; ok {
			if v.Pnp_bind == 1 {
				//绑定
				switch v.Provider {
				case "dongxin":
					//东信
					dx := dongxin.NewDongXin()
					//获取毫秒
					nano := strconv.FormatInt(time.Now().UnixNano()/1e6, 10)
					//截取最后三位
					last_no := nano[len(nano)-3:]
					ts := time.Now().Format("20060102150405") + last_no
					sub_ts := time.Now().Format("20060102150405")
					sendExtra := dongxin.SendBindExtra{
						Callrecording: "1",
					}
					//解析配置文件
					con, _ := gjson.DecodeToJson(v.Config)

					//创建UUID
					u := uuid.NewV4()

					//封装数据
					dx.Request_url = con.GetString("url")
					send := dongxin.SendBindStruct{
						Tela:       v.Tel_a,
						Telx:       v.Tel_x,
						Expiration: strconv.FormatInt(v.Expire, 10),
						Ts:         ts,
						Subts:      sub_ts,
						Name:       "陆冬冬",
						Cardtype:   "0",
						Cardno:     "340522198908048793",
						Areacode:   strconv.Itoa(v.Areacode),
						Appkey:     con.GetString("appkey"),
						Secretkey:  con.GetString("secretkey"),
						Extra:      sendExtra,
						RequestId:  strings.Replace(u.String(), "-", "", -1),
						Remark:     v.Order_id,
					}
					dx.Send = send
					code, ex, err := dx.SendBind()
					if err == nil {
						if code == 0 {
							fmt.Println(ex)
							bind_callback(v.Tel_a, v.Tel_x, v.Expire_date, ex)
						} else {
							fmt.Println(ex)
						}
					}
					break
				}
			} else if v.Pnp_bind == 2 {
				//解绑
				switch v.Provider {
				case "dongxin":
					//东信
					dx := dongxin.NewDongXin()
					//获取毫秒
					nano := strconv.FormatInt(time.Now().UnixNano()/1e6, 10)
					//截取最后三位
					last_no := nano[len(nano)-3:]
					ts := time.Now().Format("20060102150405") + last_no
					//解析配置文件
					con, _ := gjson.DecodeToJson(v.Config)

					dx.Request_url = con.GetString("url")
					send := dongxin.UnBindStruct{
						Ts:        ts,
						Appkey:    con.GetString("appkey"),
						Secretkey: con.GetString("secretkey"),
						Subid:     v.SubId,
					}

					dx.Unbind = send
					code, ex, err := dx.SendUnBind()

					if err == nil {
						if code == 0 {
							if v.Order_id == "" {
								//解绑所有绑定的X号
								unbind_callback(v.Tel_x, v.Tel_a)
							} else {
								//解绑订单的X号
								unbind_order_callback(v.Tel_x, v.SubId)
							}
						} else {
							fmt.Println(ex)
						}
					}
					break
				}
			}
		} else {
			break
		}
	}
	f <- 1
}

func bind_callback(tel string, telx string, expire_time string, extra string) {
	//实例化电话对象
	m := model.NewPnpTelephoneModel()
	//已绑定有效X号
	//号码绑定
	m.Tel_x = telx
	m.Tel_a = tel
	m.Is_bind = 1
	m.Extra = extra
	m.Expire_time = expire_time
	//更新绑定时间
	logic.NewPnpLogic().Update(m)
}

func unbind_callback(tel_x string, tel_a string) {
	//实例化电话对象
	m := model.NewPnpTelephoneModel()
	m.Tel_x = tel_x
	m.Tel_a = ""
	m.Expire_time = time.Now().Local().Format("2006-01-02 15:04:05")
	m.Is_bind = 2
	m.Extra = ""
	logic.NewPnpLogic().Update(m)
	//更新绑定状态
	logic.NewOrderLogic().UpdateOrderTelBindAllState(tel_a)
}

func unbind_order_callback(tel_x string, sub_id string) {
	m := model.NewPnpTelephoneModel()
	m.Tel_x = tel_x
	m.Tel_a = ""
	m.Expire_time = time.Now().Local().Format("2006-01-02 15:04:05")
	m.Is_bind = 2
	m.Extra = sub_id
	logic.NewPnpLogic().UpdatePnpByOrder(m)
	//更新绑定状态
	logic.NewOrderLogic().UpdateOrderTelBindState(tel_x, sub_id)
}
