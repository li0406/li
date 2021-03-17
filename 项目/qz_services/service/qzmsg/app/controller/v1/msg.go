package v1

import (
	"github.com/gogf/gf/g"
	"github.com/gogf/gf/g/frame/gmvc"
	"github.com/gogf/gf/g/util/gconv"
	"github.com/gogf/gf/g/util/gvalid"
	"qzmsg/app/enum"
	"qzmsg/app/logic"
	"qzmsg/app/model"
	"qzmsg/app/service"
	ddrobot "qzmsg/app/service/robot/dingding"
	"qzmsg/library/util"
	"strings"
)

type MsgController struct {
	gmvc.Controller
}

// 后台用户消息推送
func (that *MsgController) SendSystemMsg() {
	input := &logic.MsgSendParam{
		TemplateId:that.Request.Get("template_id"),
		Users:that.Request.Get("users"),
		FromApp:that.Request.Get("from_app"),
		ActionId:that.Request.Get("action_id"),
		LinkParam:that.Request.Get("link_param"),
		Operator:that.Request.GetInt("operator"),
		SendPosition:that.Request.GetString("send_position"),
		ReplaceParams:that.Request.Get("replace_params"),
	}

	// 参数验证
	err := gvalid.CheckStruct(input,nil)
	if err != nil {
		json,_ := util.EncodeResponseJson(4000002,enum.CODE_4000002,err.Map()["required"])
		_ = that.Request.Response.WriteJson(json)
		that.Request.ExitAll()
	}

	// 验证应用项目
	smsAppModel := model.NewSmsAppModel()
	appInfo := smsAppModel.GetAppInfo(input.FromApp)
	if len(appInfo) == 0 || gconv.Int(appInfo["is_enable"]) == 0 {
		json,_ := util.EncodeResponseJson(4000004, enum.CODE_4000004, nil)
		_ = that.Request.Response.WriteJson(json)
		that.Request.ExitAll()
	}

	// 验证模板
	msgTemplateModel := model.NewMsgTemplateModel()
	tempInfo := msgTemplateModel.GetById(input.TemplateId)
	if len(tempInfo) == 0 {
		json,_ := util.EncodeResponseJson(4000001, "模板不存在", nil)
		_ = that.Request.Response.WriteJson(json)
		that.Request.ExitAll()
	}

	// 生成站内信
	msgLogic := logic.NewMsgLogic()
	response := msgLogic.CreatedSystemMsg(input)

	if g.Config().GetString("setting.APP_ENV") == "dev" {
		user := gconv.String(response["userIds"])
		notice := gconv.String(response["notice"])

		robot := ddrobot.NewRobotService();
		robot.SendMarkdownMessage("消息通知","### 消息通知 \n\n >[通知用户]："+ user + "\n\n >[通知内容]：" + notice + "\n\n")
	}

	// 通知用户-齐装消息系统
	sendService := service.NewSendService()
	sendService.NoticeSystemUser(response)

	// 通知用户-微信模板消息
	wechatLogic := logic.NewWechatOfficlalLogic()
	wechatLogic.SendTemplateMsg(response, "qzmsg")

	// 生成发送记录
	sendLogLogic := logic.NewSendLogLogic()
	sendLogLogic.AddLog("system", response)

	ret,_ := util.EncodeResponseJson(0, enum.CODE_0, nil)
	_ = that.Request.Response.WriteJson(ret)
	that.Exit()
}

// 装修公司用户消息推送
func (that *MsgController) SendCompanyMsg() {
	input := &logic.MsgSendParam{
		TemplateId:that.Request.Get("template_id"),
		Users:that.Request.Get("users"),
		Employees:that.Request.Get("employees"),
		FromApp:that.Request.Get("from_app"),
		ActionId:that.Request.Get("action_id"),
		LinkParam:that.Request.Get("link_param"),
		Operator:that.Request.GetInt("operator"),
		SendPosition:that.Request.GetString("send_position"),
		ReplaceParams:that.Request.Get("replace_params"),
	}

	// 参数验证
	err := gvalid.CheckStruct(input,nil)
	if err != nil {
		json,_ := util.EncodeResponseJson(4000002, enum.CODE_4000002, err.Map()["required"])
		_ = that.Request.Response.WriteJson(json)
		that.Request.ExitAll()
	}

	// 验证公司ID
	if input.Users == "" || input.Users == "0" {
		json,_ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
		_ = that.Request.Response.WriteJson(json)
		that.Request.ExitAll()
	}

	// 验证应用项目
	smsAppModel := model.NewSmsAppModel()
	appInfo := smsAppModel.GetAppInfo(input.FromApp)
	if len(appInfo) == 0 || gconv.Int(appInfo["is_enable"]) == 0 {
		json,_ := util.EncodeResponseJson(4000004, enum.CODE_4000004, nil)
		_ = that.Request.Response.WriteJson(json)
		that.Request.ExitAll()
	}

	// 验证模板
	msgTemplateModel := model.NewMsgTemplateModel()
	tempInfo := msgTemplateModel.GetById(input.TemplateId)
	if len(tempInfo) == 0 {
		json,_ := util.EncodeResponseJson(4000001, "模板不存在", nil)
		_ = that.Request.Response.WriteJson(json)
		that.Request.ExitAll()
	}

	var response g.Map
	if (input.Employees != "" && input.Employees != "0") {
		userIds := strings.Split(input.Users, ",")
		if len(userIds) > 1  {
			json,_ := util.EncodeResponseJson(4000002, "装修公司参数有误", nil)
			_ = that.Request.Response.WriteJson(json)
			that.Request.ExitAll()
		}

		// 生成站内信
		msgLogic := logic.NewMsgLogic()
		response = msgLogic.CreatedCompanyEmployeeMsg(input)
	} else {
		// 生成站内信
		msgLogic := logic.NewMsgLogic()
		response = msgLogic.CreatedCompanyMsg(input)
	}

	appEnv := g.Config().GetString("setting.APP_ENV")
	if appEnv == "dev" || appEnv == "test" {
		user := gconv.String(response["userIds"])
		employee := gconv.String(response["employeeIds"])
		notice := gconv.String(response["notice"])

		robot := ddrobot.NewRobotService();
		robot.SendMarkdownMessage("消息通知","### 装修公司消息通知 \n\n >[通知用户]："+ user + "\n\n >[通知员工]："+ employee + "\n\n >[通知内容]：" + notice + "\n\n")
	}

	// 通知用户-齐装消息系统
	//sendService := service.NewSendService()
	//sendService.NoticeCompanyUser(response)

	// 生成发送记录
	sendLogLogic := logic.NewSendLogLogic()
	sendLogLogic.AddLog("company", response)

	ret,_ := util.EncodeResponseJson(0, enum.CODE_0, nil)
	_ = that.Request.Response.WriteJson(ret)
}


// C端用户消息推送
func (that *MsgController) SendUserMsg(){
	input := &logic.MsgSendParam{
		TemplateId:that.Request.Get("template_id"),
		Users:that.Request.Get("users"),
		FromApp:that.Request.Get("from_app"),
		ActionId:that.Request.Get("action_id"),
		LinkParam:that.Request.Get("link_param"),
		Operator:that.Request.GetInt("operator"),
		SendPosition:that.Request.GetString("send_position"),
		ReplaceParams:that.Request.Get("replace_params"),
	}

	// 参数验证
	err := gvalid.CheckStruct(input,nil)
	if err != nil {
		json,_ := util.EncodeResponseJson(4000002,enum.CODE_4000002,err.Map()["required"])
		_ = that.Request.Response.WriteJson(json)
		that.Request.ExitAll()
	}

	// 验证模板
	msgTemplateModel := model.NewMsgTemplateModel()
	tempInfo := msgTemplateModel.GetById(input.TemplateId)
	if len(tempInfo) == 0 {
		json,_ := util.EncodeResponseJson(4000001, "模板不存在", nil)
		_ = that.Request.Response.WriteJson(json)
		that.Request.ExitAll()
	}

	// 验证应用项目
	smsAppModel := model.NewSmsAppModel()
	appInfo := smsAppModel.GetAppInfo(input.FromApp)
	if len(appInfo) == 0 || gconv.Int(appInfo["is_enable"]) == 0 {
		json,_ := util.EncodeResponseJson(4000004, enum.CODE_4000004, nil)
		_ = that.Request.Response.WriteJson(json)
		that.Request.ExitAll()
	}

	// 生成站内信
	msgLogic := logic.NewMsgLogic()
	response := msgLogic.CreatedUserMsg(input)

	if g.Config().GetString("setting.APP_ENV") == "dev" {
		user := gconv.String(response["userIds"])
		notice := gconv.String(response["notice"])

		robot := ddrobot.NewRobotService();
		robot.SendMarkdownMessage("消息通知","### 消息通知 \n\n >[通知用户]："+ user + "\n\n >[通知内容]：" + notice + "\n\n")
	}

	// 生成发送记录
	sendLogLogic := logic.NewSendLogLogic()
	sendLogLogic.AddLog("user", response)

	ret,_ := util.EncodeResponseJson(0, enum.CODE_0, nil)
	_ = that.Request.Response.WriteJson(ret)
}

