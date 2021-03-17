package model

import (
    "admin-api/library/util"
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/database/gdb"
    "github.com/gogf/gf/g/util/gconv"
    "time"
)

type MsgSendLogModel struct {
}

func NewMsgSendLogModel() *MsgSendLogModel {
    return &MsgSendLogModel{}
}

// 表名
func (that *MsgSendLogModel) table() string {
    return "qz_msg_send_log"
}

// 配置数据表（带别名）
func (that *MsgSendLogModel) connect() *gdb.Model {
    return db.Table(that.table() + " as log")
}

func (that MsgSendLogModel)GetMsgSendLogCount(input g.MapStrStr) (int, error) {
    dbQuery := that.connect()
    that.setListWhere(dbQuery, input)
    dbQuery.InnerJoin("qz_msg_type as t", "t.id = log.type_id")
    dbQuery.InnerJoin("qz_sms_app as p", "p.slot = log.from_app")
    dbQuery.Fields("log.id")
    return dbQuery.Count()
}

// 获取消息模板列表
func (that *MsgSendLogModel) GetMsgSendLogList(input g.MapStrStr, offset int, limit int) (list gdb.List, err error){
    dbQuery := that.connect()
    that.setListWhere(dbQuery, input)

    field := "log.id,log.type_id,log.content,t.name type_name,log.send_position,p.name platform_name,log.send_type,log.created_at,u1.name operator"

    dbQuery.InnerJoin("qz_msg_type as t", "t.id = log.type_id")
    dbQuery.InnerJoin("qz_sms_app as p", "p.slot = log.from_app")
    //dbQuery.InnerJoin("qz_adminuser as u", "u.id = log.users")
    dbQuery.LeftJoin("qz_adminuser as u1", "u1.id = log.operator")
    dbQuery.Fields(field)
    dbQuery.OrderBy("log.id desc")
    dbQuery.Limit(offset, limit)

    ret, err := dbQuery.Select()
    return ret.ToList(), err
}

// 查询条件
func (that *MsgSendLogModel) setListWhere(dbQuery *gdb.Model, where g.MapStrStr) {
    // 消息内容
    if where["keyword"] != "" {
        keyword := gconv.String(where["keyword"])
        dbQuery.Where("log.`content` like ?", "%" + keyword + "%")
    }
    // 项目应用接入id
    if where["from_app"] != "" {
        dbQuery.Where("p.`id` = ?", where["from_app"])
    }
    // 消息类型ID
    if where["send_type"] != "" {
        dbQuery.Where("log.`send_type` = ?", where["send_type"])
    }
    // 消息类型ID
    if where["push_time"] != "" {
        pushtime := util.StrToTime(gconv.String(where["push_time"]))
        dbQuery.Where("log.`created_at` >= ?", pushtime)
        dbQuery.Where("log.`created_at` <= ?", pushtime + 86399)
    }else {
        dbQuery.Where("log.`created_at` >= ?", time.Now().AddDate(0, -1, 0).Unix())
        dbQuery.Where("log.`created_at` <= ?", time.Now().Unix())
    }
}