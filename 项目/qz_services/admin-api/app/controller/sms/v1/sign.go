package v1

import (
	"admin-api/app/enum"
	"admin-api/app/logic"
	"admin-api/app/model"
	"admin-api/library/util"
	"github.com/gogf/gf/g/frame/gmvc"
)

type SignController struct {
	gmvc.Controller
}

// 获取签名列表
func (that *SignController) List () {
	smsSignModel := model.NewSmsSignModel()
	list,err := smsSignModel.GetList()

	if err == nil {
		respData := make(map[string]interface{})
		respData["list"] = list

		ret,_ := util.EncodeResponseJson(0, enum.CODE_0, respData)
		_ = that.Request.Response.WriteJson(ret)
	} else { // 查询失败
		ret,_ := util.EncodeResponseJson(5000002, enum.CODE_5000002, nil)
		_ = that.Request.Response.WriteJson(ret)
	}
	that.Exit()
}

// 设置签名启用状态
func (that *SignController) Enabled () {
	id := that.Request.GetPostInt("id")
	enabled := that.Request.GetPostInt("enabled")

	// 缺少参数拒绝查询
	if id == 0 {
		ret,_ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
		_ = that.Request.Response.WriteJson(ret)
		that.Exit()
	}

	// 查询
	smsSignModel := model.NewSmsSignModel()
	info := smsSignModel.GetById(id)

	// 查询不到结果
	if len(info) == 0 {
		ret,_ := util.EncodeResponseJson(4000001, "签名不存在", nil)
		_ = that.Request.Response.WriteJson(ret)
		that.Exit()
	}

	// 执行更新操作
	err := smsSignModel.SetEnabled(id, enabled)
	if err == nil { // 更新成功
		ret,_ := util.EncodeResponseJson(0, enum.CODE_0, nil)
		_ = that.Request.Response.WriteJson(ret)
	} else { // 更新失败
		ret,_ := util.EncodeResponseJson(5000002, enum.CODE_5000002, nil)
		_ = that.Request.Response.WriteJson(ret)
	}

	// 添加操作日志
	logingLogic := logic.NewLogingLogic()
	logingLogic.AddLog(that.Request, "sms_sign", "修改短信签名是否启用", id)

	that.Exit()
}

// 编辑签名信息
func (that *SignController) Save () {
	id := that.Request.GetPostInt("id")
	name := that.Request.GetPostString("name")
	enabled := that.Request.GetPostInt("enabled")

	if name == "" {
		ret,_ := util.EncodeResponseJson(4000002, "请输入签名", nil)
		_ = that.Request.Response.WriteJson(ret)
		that.Exit()
	}

	// 实例化模型
	smsSignModel := model.NewSmsSignModel()

	// 签名唯一性验证
	info := smsSignModel.GetByName(name)
	if len(info) > 0 && info["id"] != id {
		ret,_ := util.EncodeResponseJson(4000003, "签名已存在", nil)
		_ = that.Request.Response.WriteJson(ret)
		that.Exit()
	}

	var nums int64 // 新增ID或影响的条数
	if id == 0 { // 新增
		creator := model.AdminUser.UserId
		nums,_ = smsSignModel.AddRecord(name, enabled, creator)
	} else { // 编辑
		nums,_ = smsSignModel.EditRecord(id, name, enabled)
	}

	if nums > 0 { // 执行成功
		ret,_ := util.EncodeResponseJson(0, enum.CODE_0, nil)
		_ = that.Request.Response.WriteJson(ret)
	} else { // 执行失败
		ret,_ := util.EncodeResponseJson(5000002, enum.CODE_5000002, nil)
		_ = that.Request.Response.WriteJson(ret)
	}

	// 添加操作日志
	logingLogic := logic.NewLogingLogic()
	logingLogic.AddLog(that.Request, "sms_sign", "编辑短信签名信息", id)

	that.Exit()
}

func (that *SignController)GetSignList() {
	 signLogic := new(logic.SmsSignLogic)
	 res := signLogic.GetSignList()
	 ret,_ := util.EncodeResponseJson(0, enum.CODE_0, res)
	 _ = that.Request.Response.WriteJson(ret)
}