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
    "github.com/tealeg/xlsx"
)

type MsgTemplateController struct {
    gmvc.Controller
}

// 消息模板列表
func (that *MsgTemplateController) List() {
    page := util.PageFormat(that.Request.GetInt("page"), 1)
    limit := util.PageFormat(that.Request.GetInt("limit"), 20)

    input := g.MapStrStr{
        "keyword": that.Request.GetString("keyword"),
        "send_type": that.Request.GetString("send_type"),
        "type_id": that.Request.GetString("type_id"),
        "enabled": that.Request.GetString("enabled"),
    }

    // 请求数据
    msgTempLogic := logic.NewMsgTemplateLogic()
    list,count := msgTempLogic.GetMsgTemplateList(input, page, limit)

    // 处理返回结果
    respData := g.Map{
        "list": list,
        "page": util.GetPage(page, limit, count),
    }

    ret, _ := util.EncodeResponseJson(0, enum.CODE_0, respData)
    _ = that.Response.WriteJson(ret)
    that.Exit()
}

// 消息模板详情
func (that *MsgTemplateController) Detail(){
    id := that.Request.GetString("id")

    var tempInfo gdb.Map
    if id != "" {
        msgTempLogic := logic.NewMsgTemplateLogic()
        tempInfo = msgTempLogic.GetDetail(id)
        if len(tempInfo) == 0 {
            ret, _ := util.EncodeResponseJson(4000001, "消息内容不存在", nil)
            _ = that.Response.WriteJson(ret)
            that.Exit()
        }
    }

    // 获取消息类型
    msgTypeModel := model.NewMsgTypeModel()
    typeList,_ := msgTypeModel.GetAll()

    respData := g.Map{
        "detail": tempInfo,
        "type_list": typeList,
    }

    ret, _ := util.EncodeResponseJson(0, enum.CODE_0, respData)
    _ = that.Response.WriteJson(ret)
    that.Exit()
}

// 编辑模板信息
func (that *MsgTemplateController) Save() {
    id := that.Request.GetPostString("id")
    input := g.Map{
        "type_id": that.Request.GetPostInt("type_id"),
        "title": that.Request.GetPostString("title"),
        "notice": that.Request.GetPostString("notice"),
        "content": that.Request.GetPostString("content"),
        "image": that.Request.GetPostString("image"),
        "send_type": that.Request.GetPostInt("send_type"),
        "app_type": that.Request.GetPostInt("app_type"),
        "enabled": that.Request.GetPostInt("enabled"),
    }

    // 数据校验
    msgTempLogic := logic.NewMsgTemplateLogic()
    if err := msgTempLogic.ValidateMap(input); err != nil {
       ret,_ := util.EncodeResponseJson(4000002, err.FirstString(), nil)
       _ = that.Request.Response.WriteJson(ret)
       that.Exit()
    }

    // link_json参数解析
    var linkMapList []g.Map
    linkJson := that.Request.GetPostString("link_json")
    if linkJson != "" {
        err := json.Unmarshal([]byte(linkJson), &linkMapList)
        if err != nil {
            ret,_ := util.EncodeResponseJson(4000002, "link_json参数格式错误", nil)
            _ = that.Request.Response.WriteJson(ret)
            that.Exit()
        }
    }

    // 保存数据
    error_code,error_msg := msgTempLogic.SaveInfo(id, input, linkMapList)

    // 添加操作日志
    logingLogic := logic.NewLogingLogic()
    logingLogic.AddLog(that.Request, "sms_template", "编辑消息内容信息", id)

    ret, _ := util.EncodeResponseJson(error_code, error_msg, nil)
    _ = that.Response.WriteJson(ret)
    that.Exit()
}

// 启用、禁用模板信息
func (that *MsgTemplateController) Enabled() {
    id := that.Request.GetPostString("id")
    enabled := that.Request.GetPostInt("enabled")

    if id == "" || enabled == 0 {
        ret, _ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
        _ = that.Response.WriteJson(ret)
        that.Exit()
    }

    // 修改数据
    msgTempLogic := logic.NewMsgTemplateLogic()
    error_code,error_msg := msgTempLogic.SetEnabled(id, enabled)

    // 添加操作日志
    logingLogic := logic.NewLogingLogic()
    logingLogic.AddLog(that.Request, "sms_template", "修改消息内容是否启用", id)

    ret, _ := util.EncodeResponseJson(error_code, error_msg, nil)
    _ = that.Response.WriteJson(ret)
    that.Exit()
}

// 应用项目列表
func (that *MsgTemplateController) AppList(){
    type_id := that.Request.GetString("type_id")
    app_type := that.Request.GetString("app_type")

    appModel := model.NewAppModel()
    appList,err := appModel.GetListByType(app_type, type_id)
    if err == nil {
        respData := g.Map{
            "list": appList,
        }
        ret, _ := util.EncodeResponseJson(0, enum.CODE_0, respData)
        _ = that.Response.WriteJson(ret)
        that.Exit()
    }

    ret, _ := util.EncodeResponseJson(5000002, enum.CODE_5000002, nil)
    _ = that.Response.WriteJson(ret)
    that.Exit()
}

func (that *MsgTemplateController) Export () {
    input := g.MapStrStr{
        "keyword": that.Request.GetString("keyword"),
        "send_type": that.Request.GetString("send_type"),
        "type_id": that.Request.GetString("type_id"),
        "enabled": that.Request.GetString("enabled"),
    }

    msgTempLogic := logic.NewMsgTemplateLogic()
    list := msgTempLogic.GetExportList(input)

    // 生成文件对象
    file := xlsx.NewFile()
    sheet,_ := file.AddSheet("Sheet1")

    // 设置表头
    var row *xlsx.Row
    row = sheet.AddRow()
    row.AddCell().Value = "消息ID"
    row.AddCell().Value = "消息提醒内容"
    row.AddCell().Value = "消息标题"
    row.AddCell().Value = "消息内容"
    row.AddCell().Value = "应用平台/链接"
    row.AddCell().Value = "推送方式"
    row.AddCell().Value = "消息类型"
    row.AddCell().Value = "添加人"
    row.AddCell().Value = "添加时间"
    row.AddCell().Value = "是否启用"

    // 处理数据
    if len(list) > 0 {
        for _,item := range list {
            row = sheet.AddRow()
            row.AddCell().Value = item.Id
            row.AddCell().Value = item.Notice
            row.AddCell().Value = item.Title
            row.AddCell().Value = item.Content

            // 应用平台/链接
            if len(item.LinkList) > 0 {
                appLinkList := item.LinkList
                var appLinkName string
                for _,li := range appLinkList {
                    // 拼接通道网关名称
                    appLinkName += fmt.Sprintf("%s(%s);", li.AppName, li.Link)
                }

                row.AddCell().Value = appLinkName
            } else {
                row.AddCell().Value = ""
            }

            // 推送方式
            if item.SendType == 1 {
                row.AddCell().Value = "单发"
            } else {
                row.AddCell().Value = "群发"
            }

            row.AddCell().Value = item.TypeName
            row.AddCell().Value = item.CreatorName
            row.AddCell().Value = item.CreatedDate

            // 是否启用
            if item.Enabled == 1 {
                row.AddCell().Value = "是"
            } else {
                row.AddCell().Value = "否"
            }
        }
    }

    //_ = file.Save("file.xlsx")
    //that.Exit()

    var buffer bytes.Buffer
    _ = file.Write(&buffer)

    that.Request.Response.Write(buffer.String())
    that.Exit()
}

