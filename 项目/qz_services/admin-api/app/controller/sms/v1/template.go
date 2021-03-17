package v1

import (
	"admin-api/app/enum"
	"admin-api/app/logic"
	"admin-api/app/model"
	"admin-api/library/util"
	"bytes"
	"encoding/json"
	"fmt"
	"github.com/gogf/gf/g"
	"github.com/gogf/gf/g/database/gdb"
	"github.com/gogf/gf/g/frame/gmvc"
	"github.com/gogf/gf/g/util/gconv"
	"github.com/tealeg/xlsx"
	"time"
)

type TemplateController struct {
	gmvc.Controller
}

// 模板编辑参数接收结构体
type TempGatewaySaveQuery struct {
	Id int `json:"id"`
	ThirdTempId string `json:"third_temp_id"`
	Prepared int `json:"prepared"`
}


// 获取短信模板列表筛选项
func (that *TemplateController) Options () {
	// 短信类型
	smsTypeEnum := new(enum.SmsTypeEnum)
	smsTypeList := smsTypeEnum.GetList()

	// 短信签名列表
	smsSignModel := model.NewSmsSignModel()
	signList,_ := smsSignModel.GetEnabledList()

	// 三方通道列表
	smsTempGatewayModel := new(model.SmsTemplateGatewayModel)
	gatewayList,_ := smsTempGatewayModel.GetGatewayList()

	// 构造返回结构
	respData := make(map[string]interface{})
	respData["gateway_list"] = gatewayList
	respData["type_list"] = smsTypeList
	respData["sign_list"] = signList

	ret,_ := util.EncodeResponseJson(0, enum.CODE_0, respData)
	_ = that.Request.Response.WriteJson(ret)
	that.Exit()
}

// 获取短信模板列表
func (that *TemplateController) List () {
	// 分页参数
	page := that.Request.GetInt("page", 1)
	limit := that.Request.GetInt("limit", 20)

	// 查询条件Map
	where := make(map[string]string)
	where["type_id"] = that.Request.GetString("type")
	where["sign_id"] = that.Request.GetString("sign_id")
	where["gateway_id"] = that.Request.GetString("gateway_id")
	where["enabled"] = that.Request.GetString("enabled")
	where["content"] = that.Request.GetString("content")

	// 执行查询
	smsTempModel := new(model.SmsTemplateModel)
	count,err := smsTempModel.GetListCount(where)

	if err == nil {
		var offset int
		if offset = (page-1)*limit; offset < 0 {
			offset = 0
		}

		list,err := smsTempModel.GetList(where, offset, limit)
		if err == nil {
			// 列表格式化
			list = that.setFormatter(list)

			// 构造返回结构
			respData := make(map[string]interface{})
			respData["page"] = util.GetPage(page, limit, count)
			respData["list"] = list

			ret,_ := util.EncodeResponseJson(0, enum.CODE_0, respData)
			_ = that.Request.Response.WriteJson(ret)
			that.Exit()
		}
	}

	ret,_ := util.EncodeResponseJson(5000002, enum.CODE_5000002, nil)
	_ = that.Request.Response.WriteJson(ret)
	that.Exit()
}

// 获取短信模板导出
func (that *TemplateController) Export () {
	// 查询条件Map
	where := make(map[string]string)
	where["type_id"] = that.Request.GetString("type")
	where["sign_id"] = that.Request.GetString("sign_id")
	where["gateway_id"] = that.Request.GetString("gateway_id")
	where["enabled"] = that.Request.GetString("enabled")
	where["content"] = that.Request.GetString("content")

	// 执行查询
	smsTempModel := model.NewSmsTemplateModel()
	list,err := smsTempModel.GetList(where, 0, 0)
	if err == nil {
		// 格式化
		list = that.setFormatter(list)

		// 构造返回结构
		respData := make(map[string]interface{})
		respData["list"] = list

		ret,_ := util.EncodeResponseJson(0, enum.CODE_0, respData)
		_ = that.Request.Response.WriteJson(ret)
		that.Exit()
	}

	ret,_ := util.EncodeResponseJson(5000002, enum.CODE_5000002, nil)
	_ = that.Request.Response.WriteJson(ret)
	that.Exit()
}

// 短信模板是否启用设置
func (that *TemplateController) Enabled () {
	id := that.Request.GetPostString("id")
	enabled := that.Request.GetPostInt("enabled")

	// 缺少参数拒绝查询
	if id == "" {
		ret,_ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
		_ = that.Request.Response.WriteJson(ret)
		that.Exit()
	}

	// 查询
	smsTempModel := model.NewSmsTemplateModel()
	info := smsTempModel.GetById(id)

	// 查询不到结果
	if len(info) == 0 {
		ret,_ := util.EncodeResponseJson(4000001, "模板不存在", nil)
		_ = that.Request.Response.WriteJson(ret)
		that.Exit()
	}

	// 如果是启用操作，需要验证是否有启用的通道网关
	if enabled == 1 {
		smsTempGatewayModel := new(model.SmsTemplateGatewayModel)
		count, _ := smsTempGatewayModel.HasGatewayByTemplateId(id)
		if count == 0 {
			ret,_ := util.EncodeResponseJson(4000001, "请先开启/添加该通道网关", nil)
			_ = that.Request.Response.WriteJson(ret)
			that.Exit()
		}
	}

	// 执行更新操作
	err := smsTempModel.SetEnabled(id, enabled)
	if err == nil { // 更新成功
		ret,_ := util.EncodeResponseJson(0, enum.CODE_0, nil)
		_ = that.Request.Response.WriteJson(ret)
	} else { // 更新失败
		ret,_ := util.EncodeResponseJson(5000002, enum.CODE_5000002, nil)
		_ = that.Request.Response.WriteJson(ret)
	}

	// 添加操作日志
	logingLogic := logic.NewLogingLogic()
	logingLogic.AddLog(that.Request, "sms_template", "修改短信模板是否启用", id)

	that.Exit()
}

// 获取短信模板编辑信息
func (that *TemplateController) GetEditInfo () {
	id := that.Request.GetString("id")

	// 三方通道列表
	smsTempGatewayModel := new(model.SmsTemplateGatewayModel)
	gatewayList,_ := smsTempGatewayModel.GetGatewayList()

	if len(gatewayList) > 0 {
		var gatewayIds g.Slice
		for _,item := range gatewayList {
			gatewayIds = append(gatewayIds, item.Id)
		}

		// 根据三方通道ID查询所有分类
		gtmModel := model.NewGatewayTypeMapModel()
		tList := gtmModel.GetListByGatewayIds(gatewayIds)

		if len(tList) > 0 {
			for key,item := range gatewayList  {
				for _,li := range tList {
					if item.Id == li["gateway_id"] {
						gatewayList[key].TypeList = append(gatewayList[key].TypeList, li["type"].(int))
					}
				}
			}
		}
	}

	var info map[string]interface{}
	if id != "" { // 查询
		smsTempModel := model.NewSmsTemplateModel()
		info = smsTempModel.GetById(id)
		if len(info) == 0 { // 查询不到结果
			ret,_ := util.EncodeResponseJson(4000001, "模板不存在", nil)
			_ = that.Request.Response.WriteJson(ret)
			that.Exit()
		}

		// 查询已关联的三方通道网关
		tempIds := g.Slice{id}
		tempGatewayList,_ := smsTempGatewayModel.GetGatewayListByTempIds(tempIds)

		// 把已设置的通道网关信息写入列表
		for key,item := range gatewayList {
			for _,li := range tempGatewayList {
				if item.Id == li.GatewayId {
					gatewayList[key].Checked = 1
					gatewayList[key].Prepared = li.Prepared
					gatewayList[key].ThirdTempId = li.ThirdTempId
					break
				}
			}
		}
	}

	// 短信类型
	smsTypeEnum := new(enum.SmsTypeEnum)
	smsTypeList := smsTypeEnum.GetList()

	// 短信签名列表
	smsSignModel := model.NewSmsSignModel()
	signList,_ := smsSignModel.GetEnabledList()

	// 构造返回结构
	respData := make(map[string]interface{})
	respData["gateway_list"] = gatewayList
	respData["type_list"] = smsTypeList
	respData["sign_list"] = signList
	respData["info"] = info

	ret,_ := util.EncodeResponseJson(0, enum.CODE_0, respData)
	_ = that.Request.Response.WriteJson(ret)
	that.Exit()
}

// 短信模板编辑
func (that *TemplateController) Save () {
	id := that.Request.GetPostString("id")
	tempData := gdb.Map{
		"type": that.Request.GetPostInt("type"),
		"sign_id": that.Request.GetPostInt("sign_id"),
		"content": that.Request.GetPostString("content"),
		"use_scene": that.Request.GetPostString("use_scene"),
		"enabled": that.Request.GetPostInt("enabled"),
		"encrypt": that.Request.GetPostInt("encrypt"),
		"updated_at": time.Now().Unix(),
	}

	// 数据校验
	templateLogic := new(logic.TemplateLogic)
	if err := templateLogic.ValidateMap(tempData); err != nil {
		ret,_ := util.EncodeResponseJson(4000002, err.FirstString(), nil)
		_ = that.Request.Response.WriteJson(ret)
		that.Exit()
	}

	// gateway_json参数解析
	var gatewayMapList []map[string]interface{}
	gatewayJson := that.Request.GetPostString("gateway_json")
	err := json.Unmarshal([]byte(gatewayJson), &gatewayMapList)
	if err != nil {
		ret,_ := util.EncodeResponseJson(4000002, "gateway_json参数格式错误", nil)
		_ = that.Request.Response.WriteJson(ret)
		that.Exit()
	}

	// 实例化模型
	smsTempModel := new(model.SmsTemplateModel)

	if id == "" { // 新增时追加数据
		tempData["creator"] = model.AdminUser.UserId
		tempData["created_at"] = time.Now().Unix()

		// 生成模板ID
		id = smsTempModel.MakeId()
	}

	// 保存
	tempData["id"] = id
	err = smsTempModel.Save(tempData)
	if err != nil { // 保存失败返回
		ret,_ := util.EncodeResponseJson(5000002, enum.CODE_5000002, nil)
		_ = that.Request.Response.WriteJson(ret)
		that.Exit()
	}

	// 保存成功后同步保存关联三方网关数据
	// 1.把原有的模板三方网关关联数据删除（物理删除）
	smsTempGatewayModel := new(model.SmsTemplateGatewayModel)
	_ = smsTempGatewayModel.DeleteByTempId(id)

	// 2.遍历新增当前模板三方网关关联数据
	for _,item := range gatewayMapList {
		itemId := gconv.Int(item["id"])
		prepared := gconv.Int(item["prepared"])
		thirdTempId := gconv.String(item["third_temp_id"])

		// 新增
		err = smsTempGatewayModel.AddRecord(id, itemId, thirdTempId, prepared)
	}

	// 添加操作日志
	logingLogic := logic.NewLogingLogic()
	logingLogic.AddLog(that.Request, "sms_template", "编辑短信模板信息", id)

	ret,_ := util.EncodeResponseJson(0, enum.CODE_0, nil)
	_ = that.Request.Response.WriteJson(ret)
	that.Exit()
}

// 格式化
func (that *TemplateController) setFormatter (list []model.SmsTemplateListStruct) []model.SmsTemplateListStruct {
	if len(list) > 0 {
		// 获取所有短信模板ID
		var tempIds g.Slice
		for _,item := range list {
			tempIds = append(tempIds, item.Id)
		}

		// 根据模板ID查询三方网关通道
		smsTempGatewayModel := new(model.SmsTemplateGatewayModel)
		gatewayList,_ := smsTempGatewayModel.GetGatewayListByTempIds(tempIds)

		if len(gatewayList) > 0 {
			// 去除所有的三方通道ID
			var gatewayIds g.Slice
			for _,item := range gatewayList {
				gatewayIds = append(gatewayIds, item.GatewayId)
			}

			// 根据三方通道ID查询所有分类
			gtmModel := model.NewGatewayTypeMapModel()
			tList := gtmModel.GetListByGatewayIds(gatewayIds)

			if len(tList) > 0 {
				for key,item := range gatewayList  {
					for _,li := range tList {
						if item.GatewayId == li["gateway_id"] {
							gatewayList[key].TypeList = append(gatewayList[key].TypeList, li["type"].(int))
						}
					}
				}
			}
		}

		smsTypeEnum := new(enum.SmsTypeEnum)
		for key,item := range list {
			if len(gatewayList) > 0 {
				for _,li := range gatewayList {
					if item.Id == li.TempId {
						list[key].GatewayList = append(list[key].GatewayList, li)
					}
				}
			}

			// 短信模板类型
			list[key].TypeName = smsTypeEnum.GetName(item.Type)
		}
	}

	return list
}

// 导出Excel（返回数据流）
func (that *TemplateController) ExportExcel () {
	// 查询条件Map
	where := make(map[string]string)
	where["type_id"] = that.Request.GetString("type")
	where["sign_id"] = that.Request.GetString("sign_id")
	where["gateway_id"] = that.Request.GetString("gateway_id")
	where["enabled"] = that.Request.GetString("enabled")
	where["content"] = that.Request.GetString("content")

	// 执行查询
	smsTempModel := new(model.SmsTemplateModel)
	list,_ := smsTempModel.GetList(where, 0, 0)
	list = that.setFormatter(list) // 格式化数据


	// 生成文件对象
	file := xlsx.NewFile()
	sheet,_ := file.AddSheet("Sheet1")

	// 设置表头
	var row *xlsx.Row
	row = sheet.AddRow()
	row.AddCell().Value = "模板ID"
	row.AddCell().Value = "签名"
	row.AddCell().Value = "模板内容"
	row.AddCell().Value = "模板类型"
	row.AddCell().Value = "使用场景"
	row.AddCell().Value = "是否脱敏"
	row.AddCell().Value = "通道网关"
	row.AddCell().Value = "添加时间"
	row.AddCell().Value = "是否启用"

	// 处理数据
	if len(list) > 0 {
		for _,item := range list {
			row = sheet.AddRow()
			row.AddCell().Value = item.Id
			row.AddCell().Value = item.SignName
			row.AddCell().Value = item.Content
			row.AddCell().Value = item.TypeName
			row.AddCell().Value = item.UseScene

			// 是否脱敏
			if item.Encrypt == 1 {
				row.AddCell().Value = "是"
			} else {
				row.AddCell().Value = "否"
			}

			// 通道网关
			if len(item.GatewayList) > 0 {
				gatewayList := item.GatewayList
				var gatewayName string
				for k,li := range gatewayList {
					if k > 0 {
						gatewayName += ","
					}

					// 拼接通道网关名称
					gatewayName += fmt.Sprintf("%s(%s,%s)", li.Name, li.ThirdTempId, gconv.String(li.Prepared))
				}

				row.AddCell().Value = gatewayName
			} else {
				row.AddCell().Value = ""
			}

			// 创建时间
			row.AddCell().Value = item.CreatedAt

			// 是否启用
			if item.Enabled == 1 {
				row.AddCell().Value = "是"
			} else {
				row.AddCell().Value = "否"
			}
		}
	}

	var buffer bytes.Buffer
	_ = file.Write(&buffer)

	that.Request.Response.Write(buffer.String())
	that.Exit()
}
