package v1

import (
    "admin-api/app/enum"
    "admin-api/app/logic"
    "admin-api/app/model"
    "admin-api/library/util"
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/frame/gmvc"
    "github.com/gogf/gf/g/util/gconv"
    "github.com/gogf/gf/g/util/gvalid"
)

type MsgTypeController struct {
    gmvc.Controller
}

type ParamStruct struct {
    Id   int
    Name string
}

//列表页
func (that *MsgTypeController) TypeList() {
    typeLogic := new(logic.MsgTypeLogic)
    //获取列表
    list := typeLogic.GetTypeList()
    //设置返回值
    respData := make(map[string]interface{})
    respData["list"] = list
    ret, _ := util.EncodeResponseJson(0, enum.CODE_0, respData)
    _ = that.Request.Response.WriteJson(ret)
}

//新增/编辑
func (that *MsgTypeController) SaveMsgType() {
    input := g.Map{
        "id":           that.Request.GetInt("id"),
        "slot":         that.Request.GetString("slot"),
        "name":         that.Request.GetString("name"),
        "receive_type": that.Request.GetInt("receive_type"),
        "remind_type":  that.Request.GetInt("remind_type"),
        "app_list":     that.Request.GetString("app_list"),
        "enabled":      that.Request.GetInt("enabled"),
    }

    //验证数据
    if err := gvalid.CheckMap(input, logic.MsgTypeValidateRules, logic.MsgTypeValidateMsgs); err != nil {
        ret, _ := util.EncodeResponseJson(4000002, err.FirstString(), nil)
        _ = that.Request.Response.WriteJson(ret)
        that.Exit()
    }

    typeLogic := new(logic.MsgTypeLogic)
    //验证当前类型是否被模板使用
    tmp := typeLogic.GetMsgTmpByType(gconv.Int(input["id"]))
    if len(tmp) > 0 && gconv.Int(input["enabled"]) == 2 {
        ret, _ := util.EncodeResponseJson(4000003, "该消息类型有关联的消息内容，请先取消关联后，再关闭该消息类型", nil)
        _ = that.Request.Response.WriteJson(ret)
        that.Exit()
    }

    //验证唯一标识
    info,_ := typeLogic.GetMsgTypeInfoBySlot(gconv.String(input["slot"]))
    if len(info) > 0 && info["id"] != input["id"]{
        ret, _ := util.EncodeResponseJson(4000003, enum.CODE_4000003, nil)
        _ = that.Request.Response.WriteJson(ret)
        that.Exit()
    }

    //添加/编辑消息类型
    status := typeLogic.SaveMsgType(input)
    if status == true {
        ret, _ := util.EncodeResponseJson(0, enum.CODE_0, nil)
        _ = that.Request.Response.WriteJson(ret)
    } else {
        ret, _ := util.EncodeResponseJson(5000002, enum.CODE_5000002, nil)
        _ = that.Request.Response.WriteJson(ret)
    }

    that.Exit()
}

//更改状态
func (that *MsgTypeController) ChangeMsgType() {
    id := that.Request.GetPostInt("id")
    if id == 0 {
        ret, _ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
        _ = that.Request.Response.WriteJson(ret)
        that.Exit()
    }
    //验证当前类型是否存在
    typeLogic := new(logic.MsgTypeLogic)
    info,err := typeLogic.GetMsgTypeInfo(id)
    if err != nil {
        ret, _ := util.EncodeResponseJson(4000002, enum.CODE_4000004, nil)
        _ = that.Request.Response.WriteJson(ret)
        that.Exit()
    }
    //验证当前类型是否被模板使用
    tmp := typeLogic.GetMsgTmpByType(id)
    if len(tmp) > 0 && gconv.Int(info["enabled"]) == 1 {
        ret, _ := util.EncodeResponseJson(4000003, "该消息类型有关联的消息内容，请先取消关联后，再关闭该消息类型", nil)
        _ = that.Request.Response.WriteJson(ret)
        that.Exit()
    }
    //更新状态
    status :=typeLogic.ChangeMsgType(id, gconv.Int(info["enabled"]))
    if status == true {
        ret, _ := util.EncodeResponseJson(0, enum.CODE_0, nil)
        _ = that.Request.Response.WriteJson(ret)
    } else {
        ret, _ := util.EncodeResponseJson(5000002, enum.CODE_5000002, nil)
        _ = that.Request.Response.WriteJson(ret)
    }
    that.Exit()
}

//获取类型数据
func (that *MsgTypeController)GetMsgType()  {
    id := that.Request.GetInt("id")

    //验证当前类型是否存在
    typeLogic := new(logic.MsgTypeLogic)
    info,_ := typeLogic.GetMsgTypeInfo(id)

    appModel := model.NewAppModel()
    appList,_ := appModel.GetAll()

    if len(appList) > 0 {
        typeAppList := gconv.Maps(info["app_list"])
        for key,app := range appList {
            appList[key]["checked"] = 0
            if len(typeAppList) > 0 {
                for _,item := range typeAppList {
                    if gconv.String(item["slot"]) == gconv.String(app["slot"]) {
                        appList[key]["checked"] = 1
                    }
                }
            }
        }
    }

    //设置返回值
    respData := make(map[string]interface{})
    respData["info"] = info
    respData["app_list"] = appList

    ret, _ := util.EncodeResponseJson(0, enum.CODE_0, respData)
    _ = that.Request.Response.WriteJson(ret)
}

func (that *MsgTypeController) GetAll(){
    msgTypeModel := model.NewMsgTypeModel()
    typeList,_ := msgTypeModel.GetAll()

    respData := g.Map{
        "list": typeList,
    }
    ret, _ := util.EncodeResponseJson(0, enum.CODE_0, respData)
    _ = that.Request.Response.WriteJson(ret)
}
