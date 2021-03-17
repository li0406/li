package v1

import (
    "admin-api/app/enum"
    "admin-api/app/logic"
    "bytes"
    "github.com/gogf/gf/g/util/gconv"
    "admin-api/library/util"
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/frame/gmvc"
    "github.com/gogf/gf/g/util/gvalid"
    "github.com/tealeg/xlsx"
)

type MsgAppSendController struct {
    gmvc.Controller
}

func (that *MsgAppSendController)MsgAppList()  {
    search := g.MapStrStr{
        "startTime":that.Request.GetString("start_time"),
        "endTime":that.Request.GetString("end_time"),
        "pushSystem":that.Request.GetString("system",1),//推送系统
        "pushLocation":that.Request.GetString("location"),//推送
        "pushType":that.Request.GetString("type"),
    }

    page := util.PageFormat(that.Request.GetInt("page"), 1)
    limit := util.PageFormat(that.Request.GetInt("limit"), 20)

    //获取列表
    appLogic := new(logic.MsgAppLogic)
    list,count := appLogic.GetAppList(search,page,limit)

    //设置返回值
    respData := make(map[string]interface{})
    respData["list"] = list
    respData["page"] = util.GetPage(page, limit, count)
    ret, _ := util.EncodeResponseJson(0, enum.CODE_0, respData)
    _ = that.Request.Response.WriteJson(ret)
}

func (that *MsgAppSendController) MsgAppSave() {
    input := g.MapStrStr{
        "id":           that.Request.GetString("id"),
        "name":         that.Request.GetString("name"),
        "title":        that.Request.GetString("title"),
        "subTitle":     that.Request.GetString("sub_title"),
        "body":         that.Request.GetString("body"),
        "img":          that.Request.GetString("img"),
        "skipKey":      that.Request.GetString("skip_key"),
        "skipUrl":      that.Request.GetString("skip_url"),      //跳转地址
        "targetGroups": that.Request.GetString("target_groups"), //目标人群 1.全部用户 2.部分用户 3.独立用户
        "pushType":     that.Request.GetString("push_type"),     //推送方式 1.立即推送 2.定时推送
        "pushTime":     that.Request.GetString("push_time"),     //推送时间
        "pushLocation": that.Request.GetString("push_location"), //推送App 1.齐装APP 2.装修说App
    }
    //获取筛选条件
    profile := map[string]string{
        "system": that.Request.GetString("system"), //系统 1.全部 2.IOS 3.Android
        "province": that.Request.GetString("province"),
        "city": that.Request.GetString("city"),
        "activeStartTime": that.Request.GetString("active_start_time"), //活跃开始时间
        "activeEndTime": that.Request.GetString("active_end_time"), //活跃结束时间
        "businessTag": that.Request.GetString("business_tag"), //业务标签 用,分隔(装修阶段,0:未设置/随便看看;1:准备装修;2:装修进行中;3:装修已结束)
        "isOrdered": that.Request.GetString("is_ordered"), //属性标签,1:未发单;2:已发单
        "deviceToken": that.Request.GetString("device_token"), //独立用户时保存需要发送的token
        "pushLocation": that.Request.GetString("push_location"), //推送App 1.齐装APP 2.装修说App
    }
    //验证数据
    if err := gvalid.CheckMap(input, logic.MsgAppSendValidateRules, logic.MsgAppSendValidateMsgs); err != nil {
        ret, _ := util.EncodeResponseJson(4000002, err.FirstString(), nil)
        _ = that.Request.Response.WriteJson(ret)
        that.Exit()
    }
    //添加数据
    appLogic := new(logic.MsgAppLogic)
    //添加/编辑消息类型
    status := appLogic.SaveMsgAppSend(input,profile)
    if status == true {
        ret, _ := util.EncodeResponseJson(0, enum.CODE_0, nil)
        _ = that.Request.Response.WriteJson(ret)
    } else {
        ret, _ := util.EncodeResponseJson(5000002, enum.CODE_5000002, nil)
        _ = that.Request.Response.WriteJson(ret)
    }
    that.Exit()
}

func (that *MsgAppSendController) GetMsgAppInfo()  {
    id := that.Request.GetInt("id")
    if id == 0 {
        ret, _ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
        _ = that.Request.Response.WriteJson(ret)
        that.Exit()
    }
    appLogic := logic.NewMsgAppLogic()
    info,_ := appLogic.GetMsgAppInfoById(id)
    //设置返回值
    respData := make(map[string]interface{})
    respData["info"] = info
    ret, _ := util.EncodeResponseJson(0, enum.CODE_0, respData)
    _ = that.Request.Response.WriteJson(ret)
}

func (that *MsgAppSendController) MsgAppDel()  {
    id := that.Request.GetInt("id")
    if id == 0 {
        ret, _ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
        _ = that.Request.Response.WriteJson(ret)
        that.Exit()
    }
    //删除状态
    appLogic := logic.NewMsgAppLogic()
    status :=appLogic.DelMsgApp(id)
    if status == true {
        ret, _ := util.EncodeResponseJson(0, enum.CODE_0, nil)
        _ = that.Request.Response.WriteJson(ret)
    } else {
        ret, _ := util.EncodeResponseJson(5000002, enum.CODE_5000002, nil)
        _ = that.Request.Response.WriteJson(ret)
    }
    that.Exit()
}

// 导出Excel（返回数据流）
func (that *MsgAppSendController) MsgAppExportExcel () {
    // 查询条件Map
    search := g.MapStrStr{
        "startTime":that.Request.GetString("start_time"),
        "endTime":that.Request.GetString("end_time"),
        "pushSystem":that.Request.GetString("system",1),//推送系统
        "pushLocation":that.Request.GetString("location"),//推送
        "pushType":that.Request.GetString("type"),
    }

    appLogic := new(logic.MsgAppLogic)
    //获取列表
    list :=appLogic.GetExportAppList(search)

    // 生成文件对象
    file := xlsx.NewFile()
    sheet,_ := file.AddSheet("Sheet1")

    // 设置表头
    var row *xlsx.Row
    row = sheet.AddRow()
    row.AddCell().Value = "消息ID"
    row.AddCell().Value = "消息创建时间"
    row.AddCell().Value = "消息描述"
    row.AddCell().Value = "消息内容"
    row.AddCell().Value = "目标人群"
    row.AddCell().Value = "发送时间"
    row.AddCell().Value = "计划发送"
    row.AddCell().Value = "到达数"
    row.AddCell().Value = "到达率"
    row.AddCell().Value = "打开数"
    row.AddCell().Value = "打开率"
    row.AddCell().Value = "状态"

    // 处理数据
    if len(list) > 0 {
       for _,item := range list {
           row = sheet.AddRow()
           row.AddCell().Value = gconv.String(item["id"])
           row.AddCell().Value = gconv.String(item["created_at"])
           row.AddCell().Value = gconv.String(item["name"])
           row.AddCell().Value = gconv.String(item["body"])
           row.AddCell().Value = gconv.String(item["target_groups_name"])
           row.AddCell().Value = gconv.String(item["push_time"])
           row.AddCell().Value = gconv.String(item["push_num"])
           row.AddCell().Value = gconv.String(item["arrive_num"])
           row.AddCell().Value = gconv.String(item["arrive_lv"])
           row.AddCell().Value = gconv.String(item["read_num"])
           row.AddCell().Value = gconv.String(item["read_lv"])
           row.AddCell().Value = gconv.String(item["push_status_name"])
       }
    }
    //_ = file.Save("file.xlsx")
    //that.Exit()
    var buffer bytes.Buffer
    _ = file.Write(&buffer)

    that.Request.Response.Write(buffer.String())
    that.Exit()
}