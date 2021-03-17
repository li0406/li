package v1

import (
	"github.com/gogf/gf/net/ghttp"
	"github.com/gogf/gf/util/gconv"
	"passport/app/enum"
	"passport/app/service/auth"
	"passport/library/util"
	"time"
)

type AuthController struct {
}

type Person struct {
	Name     string `json:"name"`
	Nickname string `json:"nickname"`
}

const INT64_MAX = int64(^uint(0) >> 1)

// 根据uuid获取 token和jwt
func (c *AuthController) Get(r *ghttp.Request) {
	//0. 获取uuid
	uuid := r.GetPostInt64("uuid")
	phone := r.GetPostString("phone")
	addr := r.GetPostString("addr")
	device := r.GetPostString("device")
	data := make(map[string]interface{})
	code := 4000004
	msg := enum.CODE_4000004

	if !gconv.Bool(uuid) || !gconv.Bool(addr) || !gconv.Bool(phone) || !gconv.Bool(device) {
		code = 4000002
		msg = enum.CODE_4000002
		util.Json(r, code, msg, nil)
	}

	if uuid > INT64_MAX {
		code = 4000005
		msg = enum.CODE_4000005
		util.Json(r, code, msg, nil)
	}

	//3.生成jwt
	passportJwt := auth.NewPassportJwt()
	passportJwt.PassportClaims.Uuid = uuid
	passportJwt.PassportClaims.Device = device
	passportJwt.PassportClaims.Phone = phone
	passportJwt.PassportClaims.Addr = addr
	token, err := passportJwt.Create()
	seeder := passportJwt.GetSeeder()
	if err != nil {
		util.Json(r, 5000003, err.Error(), nil)
	}

	//2.生成ticket
	ticker := auth.NewTicker()
	ticket, err := ticker.Set(seeder)
	if err != nil {
		util.Json(r, 5000003, err.Error(), nil)
	}
	//4.保存到redis
	_, err = ticker.Save(ticket, token, time.Duration(passportJwt.PassportClaims.Refresh_expire*1000*1000*1000))
	if err != nil {
		util.Json(r, 5000003, err.Error(), nil)
	}

	//5.返回数据
	data["ticket"] = ticket
	data["token"] = token
	util.Json(r, 0, enum.CODE_0, data)
}

// 刷新
func (c *AuthController) Refresh(r *ghttp.Request) {
	//1.获取ticket
	ticket := r.GetPostString("ticket")
	data := make(map[string]interface{})
	ticker := auth.NewTicker()
	passportJwt := auth.NewPassportJwt()
	code := 4000002
	msg := enum.CODE_4000002

	if !gconv.Bool(ticket) {
		code = 4000002
		msg = enum.CODE_4000002
		util.Json(r, code, msg, nil)
	}
	//取出redis中的数据
	token, err := ticker.Get(ticket)
	if err != nil {
		util.Json(r, 4000002, err.Error(), nil)
	}
	if !gconv.Bool(token) {
		code = 4000002
		msg = enum.CODE_4000002
		util.Json(r, code, msg, nil)
	}

	//解析
	passportClaims, err := passportJwt.Parse(token)
	if err != nil {
		util.Json(r, 5000003, err.Error(), nil)
	}

	if gconv.Bool(passportClaims.Uuid) == false {
		util.Json(r, 5000003, "解析token失败", nil)
	}

	passportJwt.PassportClaims.Uuid = passportClaims.Uuid
	passportJwt.PassportClaims.Phone = passportClaims.Phone
	passportJwt.PassportClaims.Addr = passportClaims.Addr
	passportJwt.PassportClaims.Device = passportClaims.Device
	//创建
	token, err = passportJwt.Create()
	if err != nil {
		util.Json(r, 5000003, err.Error(), nil)
	}

	//保存到redis
	_, err = ticker.Save(ticket, token, time.Duration(passportJwt.PassportClaims.Refresh_expire*1000*1000*1000))
	if err != nil {
		util.Json(r, 5000003, err.Error(), nil)
	}
	//返回数据
	data["ticket"] = ticket
	data["token"] = token
	util.Json(r, 0, enum.CODE_0, data)
}

// 删除
func (c *AuthController) Drop(r *ghttp.Request) {
	//1.获取ticket
	ticker := auth.NewTicker()
	code := 4000002
	msg := enum.CODE_4000002

	params := r.GetPostStrings("ticket")
	if !gconv.Bool(params) {
		code = 4000002
		msg = enum.CODE_4000002
		util.Json(r, code, msg, nil)
	}

	for _, value := range params {
		//2.判断是否存在
		_, err := ticker.Exist(value)
		if err == nil {
			//util.Json(r, 5000003, err.Error(), nil)
			//删除
			_, _ = ticker.Del(value)
		}
		//if err != nil {
		//	util.Json(r, 5000003, err.Error(), nil)
		//}
	}

	util.Json(r, 0, enum.CODE_0, nil)
}

// 解析
func (c *AuthController) Parse(r *ghttp.Request) {
	//1.获取ticket
	token := r.GetPostString("token")
	code := 4000002
	msg := enum.CODE_4000002
	passportJwt := auth.NewPassportJwt()

	if !gconv.Bool(token) {
		code = 4000002
		msg = enum.CODE_4000002
		util.Json(r, code, msg, nil)
	}
	//2.判断是否存在
	passportClaims, err := passportJwt.Parse(token)
	if err != nil {
		util.Json(r, 5000003, err.Error(), nil)
	}

	util.Json(r, 0, enum.CODE_0, passportClaims)
}
