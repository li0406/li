package logic

import (
    "admin-api/app/model"
    "admin-api/library/util"
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/database/gdb"
)

type MsgSendLogLogic struct {
}

func NewMsgSendLogLogic() *MsgSendLogLogic {
    return &MsgSendLogLogic{}
}

// 获取消息模板列表
func (that *MsgSendLogLogic)GetMsgSendLogList(input g.MapStrStr, page int, limit int) (list gdb.List, count int) {
    msgLogModel := model.NewMsgSendLogModel()
    count,err := msgLogModel.GetMsgSendLogCount(input)
    if count > 0 && err == nil {
        offset := util.GetPageOffset(page, limit)
        list,err = msgLogModel.GetMsgSendLogList(input, offset, limit)

        if err == nil {
            typeIds := g.Slice{}
            for _,item := range list {
                exist := false
                for _,val := range typeIds {
                    if val == item["type_id"] {
                        exist = true
                        break
                    }
                }

                if exist == false {
                    typeIds = append(typeIds, item["type_id"])
                }
            }

            // 查询消息接收平台
            msgTypeModel := model.NewMsgTypeModel()
            typeAppList,_ := msgTypeModel.GetTypeAppList(typeIds)
            for key,item := range list {
                receive_app := g.List{}
                for _,val := range typeAppList {
                    if val["type_id"] == item["type_id"] {
                        receive_app = append(receive_app, val)
                    }
                }
                list[key]["receive_app"] = receive_app
            }
        }
    }

    return list, count
}
