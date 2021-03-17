package logic

import (
    "admin-api/app/model"
    "admin-api/library/util"
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/database/gdb"
    "github.com/gogf/gf/g/util/gconv"
    "time"
)

type MsgInAppLogic struct {
}

func NewMsgInAppLogic() *MsgInAppLogic {
    return &MsgInAppLogic{}
}

// 验证规则
// 切片是有序的，map时无序的，故用切片定义
var MsgInAppSendValidateRules = []string{
    "title@required",
    "body@required",
    "type_id@required",
    "app_type@required",
    "push_type@required",
    "send_type@required",
    "enabled@required",
}

// 自定义错误提示（单独定义方便维护）
var MsgInAppSendValidateMsgs = map[string]interface{}{
    "title":     "请先填写消息标题",
    "body":      "请先填写消息内容",
    "type_id":   "请先选择消息类型",
    "app_type":  "请先选择应用类型",
    "push_type": "请先选择推送方式",
    "send_type": "请先选择是否定时发送",
    "enabled":   "请先选择启用状态",
}

// 获取APP在线消息列表
func (that *MsgInAppLogic) GetMsgInAppList(search g.MapStrStr, page, limit int) (list g.List, count int) {
    msgInAppModel := model.NewMsgInAppModel()

    count, err := msgInAppModel.MsgInAppListCount(search)
    if count > 0 && err == nil {
        offset := util.GetPageOffset(page, limit)
        list, _ = msgInAppModel.MsgInAppList(search, offset, limit)
        for k, v := range list {
            switch v["push_type"] {
            case 1:
                list[k]["push_type_name"] = "群发"
                break
            case 2:
                list[k]["push_type_name"] = "单发"
                break
            case 3:
                list[k]["push_type_name"] = ""
                break
            }

            list[k]["created_at_string"] = time.Unix(gconv.Int64(v["created_at"]), 0).Format("2006-01-02 15:04:05") // 时间戳转时间
            list[k]["push_time_string"] = time.Unix(gconv.Int64(v["push_time"]), 0).Format("2006-01-02 15:04:05")   // 时间戳转时间

            // 获取每一个消息的已读数量
            zxsReadMsgModel := model.NewZxsReadMsgModel()
            readCount, _ := zxsReadMsgModel.MsgInAppReadCount(gconv.String(v["id"]))
            list[k]["read_count"] = readCount

            // 获取每一个在线消息的推送数量
            msgToAppLogModel := model.NewMsgToAppLogModel()
            sendCount, _ := msgToAppLogModel.MsgToAppSendCount(gconv.String(v["id"]))
            list[k]["send_count"] = sendCount

            // 获取消息跳转链接
            msgInAppLinkModel := model.NewMsgInAppLinkModel()
            linkList, _ := msgInAppLinkModel.GetLinkByMsgId(gconv.String(v["id"]))
            list[k]["link_list"] = linkList

        }
        return list, count
    }
    return nil, 0

}

// 获取单独的一条APP在线消息的基本信息
func (that *MsgInAppLogic) GetOnceMsgInAppBasicInfo(id string) gdb.Map {
    msgInAppModel := model.NewMsgInAppModel()
    MsgInfo := msgInAppModel.GetOnceMsgInfoById(id)
    if len(MsgInfo) > 0 {
        image := gconv.String(MsgInfo["image"]);
        if image != "" {
            qiniudomain := g.Config().GetString("qiniu.qzupload.domain")
            MsgInfo["image_full"] = qiniudomain + "/" + image
        }
    }

    return MsgInfo
}

func (that *MsgInAppLogic) SaveMsgAppSend(input map[string]string, linkMapList []g.Map) (saveStatus bool) {
    nowTime := time.Now().Unix()
    msgInAppModel := model.NewMsgInAppModel()

    //消息发送表数据
    sendSave := g.Map{
        "title":     input["title"],
        "type_id":   input["type_id"],
        "body":      input["body"],
        "img":       input["img"],
        "skip_key":  input["skip_key"],
        "skip_url":  input["skip_url"],
        "push_type": input["push_type"],
        //"push_location": input["push_location"],
        "send_type":  input["send_type"],
        "enabled":    input["enabled"],
        "app_type":   input["app_type"],
        "operator":   model.AdminUser.UserId,
        "updated_at": nowTime,
    }
    if (input["send_type"] == "1") && (input["push_time"] != "") {

        // 时间转时间戳
        datetime := input["push_time"]
        timeLayout := "2006-01-02 15:04:05"  //转化所需模板 (go的诞生时间)
        loc, _ := time.LoadLocation("Local") //获取时区
        tmp, _ := time.ParseInLocation(timeLayout, datetime, loc)
        timestamp := tmp.Unix() //转化为时间戳 类型是int64

        sendSave["push_time"] = timestamp
    } else {
        sendSave["push_time"] = nowTime
    }

    //新增
    if input["id"] == "" {
        sendSave["created_at"] = nowTime
        sendSave["id"] = msgInAppModel.MakeId()
        addresult := msgInAppModel.MsgInAppAdd(sendSave)
        if addresult == true {
            saveStatus = that.SaveMsgLink(gconv.String(sendSave["id"]), input, linkMapList)
        } else {
            saveStatus = false
        }
    } else {
        //编辑
        msgInAppModel.MsgInAppUpdate(sendSave, input["id"])
        saveStatus = that.SaveMsgLink(gconv.String(input["id"]), input, linkMapList)
    }

    return saveStatus
}

func (that *MsgInAppLogic) SaveMsgLink(id string, input g.MapStrStr, linkMapList []g.Map) (bool) {

    // 保存成功后同步保存关联消息链接数据
    // 1.把原有的消息链接数据删除（物理删除）
    msgInAppLinkModel := model.NewMsgInAppLinkModel()
    _ = msgInAppLinkModel.DeleteByMsgId(id)

    // 2.遍历新增当前消息链接关联数据
    if len(linkMapList) > 0 {
        for _, item := range linkMapList {
            app_slot := gconv.String(item["app_slot"])
            link := gconv.String(item["link"])

            // 新增
            if app_slot != "" && link != "" {
                _ = msgInAppLinkModel.AddRecord(id, app_slot, link)
            }
        }
    }

    return true
}

func (that *MsgInAppLogic) GetMsgDetail(id string) gdb.Map {
    msgInAppModel := model.NewMsgInAppModel()
    MsgInfo := msgInAppModel.GetOnceMsgInfoById(id)

    if len(MsgInfo) > 0 {
        msgInAppLinkModel := model.NewMsgInAppLinkModel()
        linkList, _ := msgInAppLinkModel.GetLinkByMsgId(gconv.String(MsgInfo["id"]))

        MsgInfo["push_time_string"] = time.Unix(gconv.Int64(MsgInfo["push_time"]), 0).Format("2006-01-02 15:04:05") // 时间戳转时间
        MsgInfo["link_json"] = linkList
        MsgInfo["image_full"] = ""

        image := gconv.String(MsgInfo["img"]);
        if image != "" {
            qiniudomain := g.Config().GetString("qiniu.qzupload.domain")
            MsgInfo["image_full"] = qiniudomain + "/" + image
        }
    }

    return MsgInfo
}
