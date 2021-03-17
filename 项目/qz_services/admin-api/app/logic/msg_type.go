package logic

import (
    "admin-api/app/model"
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/database/gdb"
    "github.com/gogf/gf/g/util/gconv"
    "strings"
    "time"
)

type MsgTypeLogic struct {

}

// 验证规则
// 切片是有序的，map时无序的，故用切片定义
var MsgTypeValidateRules = []string {
    "slot@required",
    "name@required",
    "receive_type@required",
    "remind_type@required",
    "app_list@required",
}

// 自定义错误提示（单独定义方便维护）
var MsgTypeValidateMsgs = map[string]interface{} {
    "slot" : "请先填写唯一标识",
    "name" : "请先填写消息类型",
    "receive_type" : "请先选择接收方式",
    "remind_type" : "请先选择提醒方式",
    "app_list" : "请先选择项目应用",
}

/**
获取消息类型列表
 */
func (that *MsgTypeLogic) GetTypeList() (list gdb.List) {
    msgTypeModel := model.NewMsgTypeModel()
    where := gdb.Map{
        //"t.enabled": 1,
    }
    list, _ = msgTypeModel.MsgTypeList(where)

    var typeIds g.Slice
    for k, v := range list {
        switch v["receive_type"] {
        case 1:
            list[k]["receive_type_name"] = "实时接收"
            break
        case 2:
            list[k]["receive_type_name"] = "延后接收"
            break
        }
        switch v["remind_type"] {
        case 1:
            list[k]["remind_type_name"] = "数量提醒"
            break
        case 2:
            list[k]["remind_type_name"] = "弹窗提醒"
            break
        }
        //设置系统消息
        list[k]["is_system"] = 0
        if v["slot"] == "system"{
            list[k]["is_system"] = 1
        }

        typeIds = append(typeIds, v["id"])
        list[k]["app_list"] = nil
    }

    if len(typeIds) > 0 {
        appList,err := msgTypeModel.GetTypeAppList(typeIds)
        if err == nil && len(appList) > 0 {
            for key,item := range list {
                var appLi []g.Map
                for _,app := range appList {
                    if gconv.String(item["id"]) == gconv.String(app["type_id"]) {
                        appLi = append(appLi, app)
                    }
                }

                list[key]["app_list"] = appLi
            }
        }
    }

    return list
}

func (that *MsgTypeLogic) SaveMsgType(input gdb.Map) bool {
    saveData := gdb.Map{
        "slot" :input["slot"],
        "name" :input["name"],
        "receive_type" :input["receive_type"],
        "remind_type" :input["remind_type"],
        "enabled" :input["enabled"],
        "creator" :model.AdminUser.UserId,
        "updated_at": time.Now().Unix(),
    }

    msgTypeModel := model.NewMsgTypeModel()
    type_id := gconv.Int64(input["id"])

    num := int64(0)
    if type_id == 0 {
        //新增
        saveData["created_at"] = time.Now().Unix()
        num = msgTypeModel.MsgTypeAdd(saveData)
        type_id = num
    } else { //编辑
        num = msgTypeModel.MsgTypeUpdate(saveData, input["id"])
    }

    if num > 0 {
        msgTypeModel.DeleteTypeApp(type_id)

        typeList := gconv.String(input["app_list"])
        appSlotList := strings.Split(typeList, ",")
        if len(appSlotList) > 0 {
            for _,slot := range appSlotList {
                msgTypeModel.AddTypeApp(type_id, slot)
            }
        }

        return true
    }

    return false
}

func (that *MsgTypeLogic) GetMsgTypeInfo(id int) (info gdb.Map, err error) {
    msgTypeModel := model.NewMsgTypeModel()
    info,err = msgTypeModel.MsgTypeInfo(id)
    if err == nil && len(info) > 0 {
        typeIds := g.Slice{info["id"]}
        appList,_ := msgTypeModel.GetTypeAppList(typeIds)
        info["app_list"] = appList
    }
    return info,err
}

func (that *MsgTypeLogic) GetMsgTypeInfoBySlot(slot string) (info gdb.Map, err error) {
    msgTypeModel := model.NewMsgTypeModel()
    where := g.Map{
        "slot":slot,
    }
    info,err = msgTypeModel.MsgTypeInfoByWhere(where,"id")
    return info,err
}

func (that *MsgTypeLogic)ChangeMsgType(id interface{},status int) bool  {
    msgTypeModel := model.NewMsgTypeModel()
    data := gdb.Map{}
    data["enabled"] = 1
    if status == 1 {
        data["enabled"] = 2
    }

    num := msgTypeModel.MsgTypeUpdate(data,id)
    return gconv.Bool(num)
}

func (that *MsgTypeLogic)GetMsgTmpByType(id int) (info gdb.Map) {
    msgTmpModel := model.NewMsgTemplateModel()
    info = msgTmpModel.GetByType(id)
    return info
}

