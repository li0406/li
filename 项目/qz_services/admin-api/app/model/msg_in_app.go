package model

import (
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/database/gdb"
    "github.com/gogf/gf/g/util/gconv"
    "time"
)

type MsgInAppModel struct {
}

func NewMsgInAppModel() *MsgInAppModel {
    return &MsgInAppModel{}
}

func (that *MsgInAppModel) table() string {
    return "qz_msg_in_app"
}

// 配置数据表（带别名）
func (that *MsgInAppModel) connect() *gdb.Model {
    return db.Table(that.table() + " as a")
}

// 统计满足条件的数量
func (that *MsgInAppModel) MsgInAppListCount(where g.MapStrStr) (int, error) {
    dbQuery := that.connect()
    that.listWhere(dbQuery, where)
    dbQuery.Fields("a.id")
    dbQuery.InnerJoin("qz_msg_type t", "t.id = a.type_id")
    //dbQuery.OrderBy("a.created_at desc")
    return dbQuery.Count()
}

// 列表
func (that *MsgInAppModel) MsgInAppList(where g.MapStrStr, offset int, limit int) (gdb.List, error) {
    dbQuery := that.connect()
    that.listWhere(dbQuery, where)
    dbQuery.Fields("a.id,a.title,a.body,a.push_type,a.push_status,a.created_at,a.push_time,a.send_type,t.name leixing_name,au.name as operator_name")
    dbQuery.InnerJoin("qz_msg_type t", "a.type_id = t.id")
    dbQuery.InnerJoin("qz_adminuser au", "a.operator = au.id")
    dbQuery.OrderBy("a.created_at desc")
    dbQuery.Limit(offset, limit)
    ret, err := dbQuery.Select()
    return ret.ToList(), err
}

// 根据id获取单个消息基本信息
func (that *MsgInAppModel) GetOnceMsgInfoById(id string) gdb.Map {
    dbQuery := that.connect()
    dbQuery.Where("a.id=?", id)
    r, _ := dbQuery.One()
    return r.ToMap()
}

// 根据id获取单个消息详细信息
func (that *MsgInAppModel) GetMsgDetail(id string) gdb.Map {
    dbQuery := that.connect()
    dbQuery.Where("a.id=?", id)
    r, _ := dbQuery.One()
    return r.ToMap()
}


// 查询条件
func (that *MsgInAppModel) listWhere(dbQuery *gdb.Model, where g.MapStrStr) {
    //是否启用
    if where["enabled"] != "" {
        dbQuery.Where("a.enabled=?", where["enabled"])
    }

    // 消息标题 模糊查询
    if where["title"] != "" {
        dbQuery.Where("a.`title` like ?", "%"+where["title"]+"%")
    }

    //推送App pushLocation
    if where["pushLocation"] != "" {
        dbQuery.Where("a.push_location=?", where["pushLocation"])
    }
    // 推送方式，默认1，1:群发；2:单发
    if where["pushType"] != "" {
        dbQuery.Where("a.push_type=?", where["pushType"])
    }
}

// 新增APP应用内消息
func (that *MsgInAppModel) MsgInAppAdd(save gdb.Map) bool {
    _, err := db.Table(that.table()).Data(save).Save()
    if err == nil {
        return true
    }
    return false
}

//更新表数据
func (that *MsgInAppModel) MsgInAppUpdate(save gdb.Map, id interface{}) int64 {
    res, err := that.connect().Data(save).Where("id = ?", id).Update()
    r := int64(0)
    if err == nil {
        r, _ = res.RowsAffected()
    }
    return r
}

// 生成一个ID
func (that *MsgInAppModel) MakeId() string {
    var prefix string = "1001"
    var date string = time.Unix(time.Now().Unix(), 0).Format("20060102")

    r, _ := that.connect().Fields("id").OrderBy("id desc").One()
    if maxInfo := r.ToMap(); len(maxInfo) > 0 {
        maxId := maxInfo["id"].(string)
        prefix = maxId[8:]
        prefix = gconv.String(gconv.Int(prefix) + 1)
    }

    var newId string = date + prefix // 新ID
    r, _ = that.connect().Fields("id").Where("id = ?", newId).One()
    if newInfo := r.ToMap(); len(newInfo) > 0 {
        // 新ID已存在重新生成
        newId = that.MakeId()
    }

    return newId
}
