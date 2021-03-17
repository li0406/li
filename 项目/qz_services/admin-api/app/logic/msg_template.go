package logic

import (
    "admin-api/app/enum"
    "admin-api/app/model"
    "admin-api/library/util"
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/database/gdb"
    "github.com/gogf/gf/g/util/gconv"
    "github.com/gogf/gf/g/util/gvalid"
    "time"
)

type MsgTemplateLogic struct {

}

func NewMsgTemplateLogic() *MsgTemplateLogic {
    return &MsgTemplateLogic{}
}

// 验证规则
// 切片是有序的，map时无序的，故用切片定义
var MsgTempValidateRules = []string {
    "content@required",
    "send_type@required",
    "app_type@required",
    "type_id@min:1",
    "enabled@min:1",
}

// 自定义错误提示（单独定义方便维护）
var MsgTempValidateMsgs = g.Map {
    "content" : "请先填写消息内容",
    "notice": "请先填写提醒内容",
    "title": "请先填写消息标题",
    "send_type": "请先选择推送方式",
    "app_type": "请先选择发送位置",
    "type_id" : map[string]string {
        "min": "请先选择消息类型",
    },
    "enabled":  map[string]string {
        "min": "请先选择启用状态",
    },
}

func (that *MsgTemplateLogic) ValidateMap(data g.Map) *gvalid.Error {
    return gvalid.CheckMap(data, MsgTempValidateRules, MsgTempValidateMsgs)
}

// 获取消息模板列表
func (that *MsgTemplateLogic)GetMsgTemplateList(input g.MapStrStr, page int, limit int) (list []model.MsgTemplateListStruct, count int) {
    msgTempModel := model.NewMsgTemplateModel()
    count,err := msgTempModel.GetListCount(input)
    if count > 0 && err == nil {
        offset := util.GetPageOffset(page, limit)
        list,_ = msgTempModel.GetList(input, offset, limit)
        list = that.setFormatter(list)
    }

    return list, count
}

// 获取导出数据
func (that *MsgTemplateLogic) GetExportList(input g.MapStrStr) (list []model.MsgTemplateListStruct) {
    msgTempModel := model.NewMsgTemplateModel()
    list,_ = msgTempModel.GetList(input, 0, 0)
    list = that.setFormatter(list)

    return list
}

// 消息模板详情
func (that *MsgTemplateLogic) GetDetail(id string) gdb.Map {
    msgTempModel := model.NewMsgTemplateModel()
    tempInfo := msgTempModel.GetById(id)

    if len(tempInfo) > 0 {
        smsTempLinkModel := model.NewMsgTemplateLinkModel()
        linkList,_ := smsTempLinkModel.GetLinkByTemplateIds(g.Slice{tempInfo["id"]})

        tempInfo["link_list"] = linkList
        tempInfo["image_full"] = ""

        image := gconv.String(tempInfo["image"]);
        if image != "" {
            qiniudomain := g.Config().GetString("qiniu.qzupload.domain")
            tempInfo["image_full"] = qiniudomain + "/" + image
        }
    }

    return tempInfo
}

// 设置模板消息启用状态
func (that *MsgTemplateLogic) SetEnabled(id string, enabled int) (int, string){
    msgTempModel := model.NewMsgTemplateModel()
    tempInfo := msgTempModel.GetById(id)

    if len(tempInfo) == 0 {
        error_msg := "消息模板不存在"
        return 4000001,error_msg
    }

    if gconv.Int(tempInfo["type_enabled"]) == 2 && enabled == 1 {
        error_msg := "请先开启该消息内容使用的消息类型"
        return 5000003,error_msg
    }

    if err := msgTempModel.SetEnabled(id, enabled); err != nil {
        return 5000002,enum.CODE_5000002
    }

    return 0,enum.CODE_0
}

// 编辑消息模板信息
func (that *MsgTemplateLogic) SaveInfo(id string, input g.Map, linkMapList []g.Map) (int, string){
    msgTempModel := model.NewMsgTemplateModel()

    if id == "" {
        input["creator"] = model.AdminUser.UserId
        input["created_at"] = time.Now().Unix()
        id = msgTempModel.MakeId()
    }

    input["id"] = id
    input["updated_at"] = time.Now().Unix()
    if err := msgTempModel.Save(input); err != nil {
        return 5000002,enum.CODE_5000002
    }

    // 保存成功后同步保存关联模板链接数据
    // 1.把原有的模板链接数据删除（物理删除）
    smsTempLinkModel := model.NewMsgTemplateLinkModel()
    _ = smsTempLinkModel.DeleteByTemplateId(id)

    // 2.遍历新增当前模板链接关联数据
    if len(linkMapList) > 0 {
        for _,item := range linkMapList {
            app_slot := gconv.String(item["app_slot"])
            link := gconv.String(item["link"])

            // 新增
            if app_slot != "" && link != "" {
                _ = smsTempLinkModel.AddRecord(id, app_slot, link)
            }
        }
    }

    return 0,enum.CODE_0
}

// 格式化列表
func (that *MsgTemplateLogic)setFormatter(list []model.MsgTemplateListStruct) []model.MsgTemplateListStruct {
    if len(list) > 0 {
        qiniudomain := g.Config().GetString("qiniu.qzupload.domain")

        // 获取所有模板ID
        var tempIds g.Slice
        for key,item := range list {
            tempIds = append(tempIds, item.Id)

            if item.Image != "" {
                list[key].ImageFull = qiniudomain + "/" + item.Image
            }
        }

        // 根据模板ID查询模板链接
        smsTempLinkModel := model.NewMsgTemplateLinkModel()
        linkList,_ := smsTempLinkModel.GetLinkByTemplateIds(tempIds)

        if len(linkList) > 0 {
            for key,item := range list  {
                for _,li := range linkList {
                    if item.Id == li.TemplateId {
                        list[key].LinkList = append(list[key].LinkList, li)
                    }
                }
            }
        }
    }

    return list
}
