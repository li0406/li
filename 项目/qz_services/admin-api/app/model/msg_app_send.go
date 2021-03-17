package model

import (
    "admin-api/library/util"
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/database/gdb"
    "github.com/gogf/gf/g/util/gconv"
    "time"
)

type MsgAppSendModel struct {
}

func NewMsgAppSendModel() *MsgAppSendModel {
    return &MsgAppSendModel{}
}

func (that *MsgAppSendModel) table() string {
    return "qz_msg_app_send"
}

// 配置数据表（带别名）
func (that *MsgAppSendModel) connect() *gdb.Model {
    return db.Table(that.table() + " as a")
}

func (that *MsgAppSendModel) MsgAppListCount(where g.MapStrStr) (int, error) {
    dbQuery := that.connect()
    that.listWhere(dbQuery, where)
    dbQuery.Fields("a.id")
    dbQuery.InnerJoin("qz_msg_app_send_profile p", "a.id = p.send_id")
    dbQuery.OrderBy("a.created_at desc")
    return dbQuery.Count()
}

func (that *MsgAppSendModel) MsgAppList(where g.MapStrStr, offset int, limit int) (gdb.List, error) {
    dbQuery := that.connect()
    that.listWhere(dbQuery, where)
    dbQuery.Fields("a.*,p.target_groups,p.system")
    dbQuery.InnerJoin("qz_msg_app_send_profile p", "a.id = p.send_id")
    dbQuery.OrderBy("a.created_at desc")
    dbQuery.Limit(offset, limit)
    ret, err := dbQuery.Select()
    return ret.ToList(), err
}

func (that *MsgAppSendModel) MsgAppAdd(save gdb.Map) int64 {
    lastId := int64(0)
    res, err := db.Table(that.table()).Data(save).Save()
    if err == nil {
        lastId, _ = res.LastInsertId()
    }
    return lastId
}

func (that *MsgAppSendModel) MsgAppUpdate(save gdb.Map, id interface{}) int64 {
    res, err := that.connect().Data(save).Where("id = ?", id).Update()
    r := int64(0)
    if err == nil {
        r, _ = res.RowsAffected()
    }
    return r
}

// 查询条件
func (that *MsgAppSendModel) listWhere(dbQuery *gdb.Model, where g.MapStrStr) {
    //是否启用
    if where["enabled"] != "" {
        dbQuery.Where("a.enabled=?", where["enabled"])
    }

    //开始时间 startTime
    if where["startTime"] == "" || where["endTime"] == "" {
        // 如果时间不传则默认查询时间为创建时间最近三个月
        dbQuery.Where("a.created_at>=?", time.Now().AddDate(0, -3, 0).Unix())
        dbQuery.Where("a.created_at<=?", time.Now().Unix() + 900)
    } else {
        // 如果传入查询时间则按推送时间查询
        dbQuery.Where("a.push_time>=?", util.StrToTime(gconv.String(where["startTime"])))
        dbQuery.Where("a.push_time<=?", util.StrToTime(gconv.String(where["endTime"])) + 86399)
    }

    //推送系统 pushSystem
    if where["pushSystem"] != "" && gconv.Int(where["pushSystem"]) != 0 {
        var sys [2]int
        switch where["pushSystem"] {
        case "1":
            sys = [2]int{1, 2}
            dbQuery.Where("p.system in(?)", sys)
            break
        case "2":
            sys = [2]int{1, 3}
            dbQuery.Where("p.system in(?)", sys)
            break
        }
    }
    //推送App pushLocation
    if where["pushLocation"] != "" {
        dbQuery.Where("a.push_location=?", where["pushLocation"])
    }
    //推送状态 pushType
    if where["pushType"] != "" {
        dbQuery.Where("a.push_type=?", where["pushType"])
    }
}

//保存app推送筛选数据
func (that *MsgAppSendModel) AddMsgAppSendProfile(save gdb.Map) int64 {
    lastId := int64(0)
    res, err := db.Table("qz_msg_app_send_profile").Data(save).Save()
    if err == nil {
        lastId, _ = res.LastInsertId()
    }
    return lastId
}

//删除
func (that *MsgAppSendModel) DelMsgAppSendProfile(sendId int64) int64 {
    num := int64(0)
    res, err := db.Table("qz_msg_app_send_profile").Where("send_id=?", sendId).Delete()
    if err == nil {
        num, _ = res.RowsAffected()
    }
    return num
}

func (that *MsgAppSendModel) GetMsgAppSendInfo(where g.Map, findField string) (gdb.Map, error) {
    res, err := that.connect().
        Fields(findField).
        InnerJoin("qz_msg_app_send_profile p", "a.id = p.send_id").
        Where(where).
        One()
    return res.ToMap(), err
}

func (that *MsgAppSendModel) MsgAppDel(id int) int64{
    save :=g.Map{
        "enabled":2,
    }
    res, err := that.connect().Data(save).Where("id = ?", id).Update()
    r := int64(0)
    if err == nil {
        r, _ = res.RowsAffected()
    }
    return r
}
