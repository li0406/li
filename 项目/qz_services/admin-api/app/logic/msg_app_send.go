package logic

import (
    "admin-api/app/model"
    "admin-api/library/util"
    "fmt"
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/database/gdb"
    "github.com/gogf/gf/g/util/gconv"
    "time"
)

type MsgAppLogic struct {
}

func NewMsgAppLogic() *MsgAppLogic {
    return &MsgAppLogic{}
}

// 验证规则
// 切片是有序的，map时无序的，故用切片定义
var MsgAppSendValidateRules = []string{
    "name@required",
    "body@required",
    "targetGroups@required",
    "pushType@required",
    "pushLocation@required",
}

// 自定义错误提示（单独定义方便维护）
var MsgAppSendValidateMsgs = map[string]interface{}{
    "name":         "请先填写消息描述",
    "body":         "请先填写消息内容",
    "targetGroups": "请先选择目标人群",
    "pushType":     "请先选择推送方式",
    "pushLocation": "请先选择推送APP",
}

/**
获取消息类型列表
*/
func (that *MsgAppLogic) GetAppList(search g.MapStrStr, page, limit int) (list g.List, count int) {
    msgAppModel := model.NewMsgAppSendModel()
    search["enabled"] = "1"
    //获取个数
    count, err := msgAppModel.MsgAppListCount(search)
    if count > 0 && err == nil {
        offset := util.GetPageOffset(page, limit)
        list, _ = msgAppModel.MsgAppList(search, offset, limit)
        for k, v := range list {
            switch v["target_groups"] {
                case 1:
                    list[k]["target_groups_name"] = "全部用户"
                    break
                case 2:
                    list[k]["target_groups_name"] = "部分用户"
                    break
                case 3:
                    list[k]["target_groups_name"] = "独立用户"
                    break
            }
            //到达率
            if gconv.Float64(v["arrive_num"]) > 0 {
                list[k]["arrive_lv"] = fmt.Sprintf("%.1f", gconv.Float64(v["arrive_num"])/gconv.Float64(v["push_num"])*100)
            } else {
                list[k]["arrive_lv"] = 0
            }
            //打开率
            if gconv.Float64(v["read_num"]) > 0 {
                list[k]["read_lv"] = fmt.Sprintf("%.1f", gconv.Float64(v["read_num"])/gconv.Float64(v["push_num"])*100)
            } else {
                list[k]["read_lv"] = 0
            }
        }
        return list, count
    }
    return nil, 0
}

func (that *MsgAppLogic) SaveMsgAppSend(input, profile map[string]string) (saveStatus bool) {
    nowTime := time.Now().Unix()
    msgAppModel := model.NewMsgAppSendModel()
    //消息发送表数据
    sendSave := g.Map{
        "name":          input["name"],
        "title":         input["title"],
        "sub_title":     input["subTitle"],
        "body":          input["body"],
        "img":           input["img"],
        "skip_key":      input["skipKey"],
        "skip_url":      input["skipUrl"],
        "push_type":     input["pushType"],
        "push_location": input["pushLocation"],
        "operator": model.AdminUser.UserId,
        "updated_at":    nowTime,
    }

    //推送方式
    if input["pushType"] == "2" {
        sendSave["push_time"] = util.StrToTime(gconv.String(input["pushTime"]))
    } else {
        sendSave["push_time"] = nowTime + 300
    }
    num := int64(0)
    if input["id"] == "" {
        //新增
        sendSave["created_at"] = nowTime
        //判断是否是全部用户,需要分别添加ios 和 安卓
        if input["targetGroups"] == "1"{
            for i := 0; i < 2; i++ {
                num = msgAppModel.MsgAppAdd(sendSave)
                if i == 0{
                    //第一次生成ios数据
                    profile["system"] = "2"
                }else {
                    //第二次生成Android数据
                    profile["system"] = "3"
                }
                saveStatus = that.SaveProfile(num, input, profile)
                if saveStatus == false {
                    return false
                }
            }
        }else {
            fmt.Println(sendSave)
            num = msgAppModel.MsgAppAdd(sendSave)
            saveStatus = that.SaveProfile(num, input, profile)
        }
    } else {
        //编辑
        num = msgAppModel.MsgAppUpdate(sendSave, input["id"])
        fmt.Println(num)
        saveStatus = that.SaveProfile(gconv.Int64(input["id"]), input, profile)
    }
    return saveStatus
}

func (that *MsgAppLogic) SaveProfile(id int64, input g.MapStrStr, profile g.MapStrStr) (bool) {
    nowTime := time.Now().Unix()
    profileData := g.Map{
        "send_id":           id,
        "target_groups":     input["targetGroups"],
        "system":            1,
        "province":          0,
        "city":              0,
        "active_start_time": 0,
        "active_end_time":   0,
        "business_tag":      "",
        "device_token":      "",
        "is_ordered":        0,
        "created_at":        nowTime,
        "updated_at":        nowTime,
    }
    //筛选部分用户的时候保留筛选数据
    fmt.Println(gconv.String(profile["activeStartTime"]))
    fmt.Println(util.StrToTime(gconv.String(profile["activeStartTime"])))
    //全部用户会被分成两次(ios/Android)添加
    if input["targetGroups"] == "1"{
        profileData["system"] = profile["system"]
    }
    if input["targetGroups"] == "2" {
        profileData["system"] = profile["system"]
        profileData["province"] = profile["province"]
        profileData["city"] = profile["city"]
        profileData["active_start_time"] = util.StrToTime(gconv.String(profile["activeStartTime"]))
        profileData["active_end_time"] =  util.StrToTime(gconv.String(profile["activeEndTime"]))
        profileData["business_tag"] = profile["businessTag"]
        profileData["is_ordered"] = profile["isOrdered"]
    }
    if input["targetGroups"] == "3" {
        profileData["device_token"] = profile["deviceToken"]
    }
    appSend := model.NewMsgAppSendModel()
    //删除原有数据
    _ = appSend.DelMsgAppSendProfile(id)
    //添加新的数据
    num := appSend.AddMsgAppSendProfile(profileData)
    if num > 0 {
        return true
    }
    return false
}

func (that *MsgAppLogic) GetMsgAppInfoById(id int) (info gdb.Map, err error) {
    msgAppModel := model.NewMsgAppSendModel()
    where := g.Map{
        "a.id": id,
    }
    info, err = msgAppModel.GetMsgAppSendInfo(where, "a.*,p.target_groups,p.system,p.province,p.city,p.active_start_time,p.active_end_time,p.business_tag,p.is_ordered,p.device_token")
    return info, err
}

func (that *MsgAppLogic) DelMsgApp(id int) (bool) {
    msgAppModel := model.NewMsgAppSendModel()
    num := msgAppModel.MsgAppDel(id)
    if num > 0 {
        return true
    } else {
        return false
    }
}

/**
获取消息类型列表
*/
func (that *MsgAppLogic) GetExportAppList(search g.MapStrStr) (list g.List) {
    msgAppModel := model.NewMsgAppSendModel()
    search["enabled"] = "1"
    //获取个数
    list, _ = msgAppModel.MsgAppList(search, 0, 0)
    for k, v := range list {
        switch v["target_groups"] {
        case 1:
            list[k]["target_groups_name"] = "全部用户"
            break
        case 2:
            list[k]["target_groups_name"] = "部分用户"
            break
        case 3:
            list[k]["target_groups_name"] = "独立用户"
            break
        }
        //到达率
        if gconv.Float64(v["arrive_num"]) > 0 {
            list[k]["arrive_lv"] = fmt.Sprintf("%.1f", gconv.Float64(v["arrive_num"])/gconv.Float64(v["push_num"])*100) + "%"
        } else {
            list[k]["arrive_lv"] = "0%"
        }
        //打开率
        if gconv.Float64(v["read_num"]) > 0 {
            list[k]["read_lv"] = fmt.Sprintf("%.1f", gconv.Float64(v["read_num"])/gconv.Float64(v["push_num"])*100) + "%"
        } else {
            list[k]["read_lv"] = "0%"
        }
        //创建时间
        list[k]["created_at"] = util.TimeToDate(gconv.Int64(v["created_at"]),19)
        //创建时间
        list[k]["push_time"] = util.TimeToDate(gconv.Int64(v["push_time"]),19)
        //发送状态
        switch gconv.Int(v["push_status"]) {
            case 1:
                list[k]["push_status_name"] = "未推送"
                fmt.Println(list[k]["push_status_name"])
                break
            case 2:
                list[k]["push_status_name"] = "已推送"
                break
        }
    }
    return list
}
