package model

import (
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/database/gdb"
    "time"
)

type MsgTypeModel struct {
}

func NewMsgTypeModel() *MsgTypeModel {
    return &MsgTypeModel{}
}

func (that *MsgTypeModel) table() string {
    return "qz_msg_type"
}

// 配置数据表（带别名）
func (that *MsgTypeModel) connect() *gdb.Model {
    return db.Table(that.table() + " as t")
}

func (that *MsgTypeModel) MsgTypeListCount(where g.Map) (int, error) {
    res, err := that.connect().Where(where).
        Fields("t.id").
        InnerJoin("qz_adminuser u","u.id = t.creator").
        OrderBy("created_at desc").
        Count()
    return res,err
}

func (that *MsgTypeModel) MsgTypeList( where g.Map) (gdb.List, error) {
    res, err := that.connect().Where(where).
        Fields("t.*,u.user").
        InnerJoin("qz_adminuser u","u.id = t.creator").
        OrderBy("created_at desc").
        Select()
    if err == nil {
        return res.ToList(), nil
    }
    return nil, err
}

func (that *MsgTypeModel) MsgTypeAdd(save gdb.Map) int64 {
    lastId := int64(0)
    res, err := db.Table(that.table()).Data(save).Save()
    if err == nil{
        lastId, _ = res.LastInsertId()
    }
    return lastId
}

func (that *MsgTypeModel) MsgTypeUpdate(save gdb.Map,id interface{}) int64 {
    res, err := that.connect().Data(save).Where("id = ?",id).Update()
    r := int64(0)
    if err == nil{
        r, _ = res.RowsAffected()
    }
    return r
}

func (that *MsgTypeModel) MsgTypeInfo(id int) (gdb.Map, error) {
    res, err := that.connect().Where("id = ?", id).One()
    return res.ToMap(), err
}

func (that *MsgTypeModel) GetAll() (gdb.List, error) {
    where := gdb.Map{
        "enabled": 1,
    }
    res, err := that.connect().Where(where).Fields("id,slot,name").OrderBy("id desc").Select()
    if err == nil {
        return res.ToList(), nil
    }
    return nil, err
}

func (that *MsgTypeModel)MsgTypeInfoByWhere(where g.Map,findField string)(gdb.Map, error)  {
    res, err := that.connect().Fields(findField).Where(where).One()
    return res.ToMap(), err
}


func (that *MsgTypeModel) GetTypeAppList(typeIds g.Slice) (list gdb.List, err error) {
    ret,err := that.connect().
        Where("t.id IN(?)", typeIds).
        InnerJoin("qz_msg_type_app as a", "a.type_id = t.id").
        InnerJoin("qz_sms_app as app", "app.slot = a.app_slot").
        Fields("app.id,app.slot,app.`name`,a.type_id").
        Select()

    if err == nil {
        return ret.ToList(),err
    }

    return list,err
}

func (that *MsgTypeModel) DeleteTypeApp(typeId int64){
    _,_ = db.Table("qz_msg_type_app").Where("type_id", typeId).Delete()
}

func (that *MsgTypeModel) AddTypeApp(typeId int64, appSlot string){
    data := gdb.Map{
        "type_id": typeId,
        "app_slot": appSlot,
        "created_at": time.Now().Unix(),
    }

    _,_ = db.Table("qz_msg_type_app").Data(data).Insert()
}