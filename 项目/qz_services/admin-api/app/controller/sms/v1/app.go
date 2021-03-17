//  项目应用接入
package v1

import (
	"admin-api/app/enum"
	"admin-api/app/logic"
	"admin-api/app/model"
	"admin-api/library/util"
	"github.com/gogf/gf/g/frame/gmvc"
	"github.com/gogf/gf/g/util/gconv"
	"github.com/gogf/gf/g/util/gvalid"
	"strings"
)

type AppController struct {
	gmvc.Controller
}

var logicApp = logic.NewAppService()

//  列表
func (ap *AppController) List() {
	list, err := logicApp.List()
	var ret []byte
	if err != nil {
		ret, _ = util.EncodeResponseJson(5000002, enum.CODE_5000002, nil)
	} else {
		data := make(map[string]interface{})
		data["list"] = list
		ret, _ = util.EncodeResponseJson(0, enum.CODE_0, data)
	}
	_ = ap.Response.WriteJson(ret)
	ap.Exit()
}

//  详情
func (ap *AppController) Detail() {
	id := ap.Request.GetInt("id")
	//验证id合法性
	rule := "required|integer|min:1"
	if e := gvalid.Check(id, rule, nil); e != nil {
		json, _ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
		_ = ap.Response.WriteJson(json)
		ap.Exit()
	}

	//是否存在
	isExist, err := logicApp.HasById(id)
	if !isExist || err != nil {
		json, _ := util.EncodeResponseJson(4000004, enum.CODE_4000004, nil)
		_ = ap.Response.WriteJson(json)
		ap.Exit()
	}

	//获取数据
	list, err := logicApp.Detail(id)
	var ret []byte
	if err != nil {
		ret, _ = util.EncodeResponseJson(5000002, enum.CODE_5000002, nil)
	} else {
		ret, _ = util.EncodeResponseJson(0, enum.CODE_0, list)
	}
	_ = ap.Response.WriteJson(ret)
	ap.Exit()
}

//  是否启用网关
func (ap *AppController) Enable() {
	//实例化
	App := model.NewAppModel()
	var err error
	var success bool
	//获取请求数据
	err = ap.Request.GetPostToStruct(App)
	if err != nil {
		json, _ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
		_ = ap.Response.WriteJson(json)
		ap.Exit()
	}
	//验证id合法性
	rule := "required|integer|min:1"
	if e := gvalid.Check(App.ID, rule, nil); e != nil {
		json, _ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
		_ = ap.Response.WriteJson(json)
		ap.Exit()
	}
	//是否存在
	isExist, err := logicApp.HasById(App.ID)
	if !isExist || err != nil {
		json, _ := util.EncodeResponseJson(4000004, enum.CODE_4000004, nil)
		_ = ap.Response.WriteJson(json)
		ap.Exit()
	}

	//更新
	success, err = logicApp.Enable(App)

	//判断并响应
	if success == false {
		json, _ := util.EncodeResponseJson(5000003, enum.CODE_5000003, nil)
		_ = ap.Response.WriteJson(json)
		ap.Exit()
	}

	// 添加操作日志
	logingLogic := logic.NewLogingLogic()
	logingLogic.AddLog(ap.Request, "sms_app", "修改应用是否启用", App.ID)

	ret, _ := util.EncodeResponseJson(0, enum.CODE_0, nil)
	_ = ap.Response.WriteJson(ret)
	ap.Exit()
}

//  保存
func (ap *AppController) Save() {
	//实例化
	App := model.NewAppModel()
	var err error
	var success bool
	//获取请求数据
	err = ap.Request.GetPostToStruct(App)
	if err != nil {
		json, _ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
		_ = ap.Response.WriteJson(json)
		ap.Exit()
	}

	//数据验证
	AppValidate := logic.AppValidate{
		ID:       App.ID,
		Name:     App.Name,
		Note:     App.Note,
		IsEnable: App.IsEnable,
		AppType: App.AppType,
	}

	if e := gvalid.CheckStruct(AppValidate, nil); e != nil {
		json, _ := util.EncodeResponseJson(4000002, e.FirstString(), nil)
		_ = ap.Response.WriteJson(json)
		ap.Exit()
	}
	//名称是否存在
	isExistName, err := logicApp.HasByName(App.ID, App.Name)
	if err != nil {
		json, _ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
		_ = ap.Response.WriteJson(json)
		ap.Exit()
	}

	if isExistName {
		json, _ := util.EncodeResponseJson(4000003, "该应用名称已存在", nil)
		_ = ap.Response.WriteJson(json)
		ap.Exit()
	}

	//是否存在id
	info, _ := logicApp.GetById(App.ID)

	if len(info) != 0 && App.ID == 0 {
		json, _ := util.EncodeResponseJson(5000003, enum.CODE_5000003, nil)
		_ = ap.Response.WriteJson(json)
		ap.Exit()
	}

	if len(info) == 0 {
		//保存
		App.Slot = logicApp.SetSlot()
		success, err = logicApp.Save(App)
	} else {
		//更新
		success, err = logicApp.Update(App)
		App.Slot = gconv.String(info["slot"])
	}

	// 保存接入类型
	if err == nil && success == true {
		smsAppAccessModel := model.NewSmsAppAccessModel()
		_ = smsAppAccessModel.DeleteByAppSlot(App.Slot)

		access_type := ap.Request.GetString("access_type")
		if access_type != "" {
			accessTypes := strings.Split(access_type, ",")
			for _,atype := range accessTypes {
				_ = smsAppAccessModel.AddRecord(App.Slot, gconv.Int64(atype))
			}
		}
	}

	if err != nil {
		json, _ := util.EncodeResponseJson(5000003, enum.CODE_5000003, nil)
		_ = ap.Response.WriteJson(json)
		ap.Exit()
	}

	//判断并响应
	if success == false {
		json, _ := util.EncodeResponseJson(5000003, enum.CODE_5000003, nil)
		_ = ap.Response.WriteJson(json)
		ap.Exit()
	}

	// 添加操作日志
	logingLogic := logic.NewLogingLogic()
	logingLogic.AddLog(ap.Request, "sms_app", "编辑应用信息", App.ID)

	ret, _ := util.EncodeResponseJson(0, enum.CODE_0, nil)
	_ = ap.Response.WriteJson(ret)
	ap.Exit()
}
