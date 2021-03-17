package model

import (
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/database/gdb"
)

type App struct {
    ID           int    `json:"id"`            //id
    Slot         string `json:"slot"`          //唯一标识
    Name         string `json:"name"`          //应用名称
    Note         string `json:"note"`          //应用说明
    IsEnable     uint8  `json:"is_enable"`     //是否启用,0:不启用,1:启用
    Creator      string `json:"creator"`       //  创建人
    CreatorName  string `json:"creator_name"`  //  创建人
    Operator     string `json:"operator"`      //操作人
    OperatorName string `json:"operator_name"` //操作人
    CreatedAt    string `json:"created_at"`    //创建时间
    UpdatedAt    string `json:"updated_at"`    //更新时间
    AppType      int `json:"app_type"`      //应用类型
    AccessList   gdb.List  `json:"access_list"` //应用接入类型
}

//  实例化方法
func NewAppModel() *App {
    return &App{}
}

func (ap *App) TableName() (table string) {
    table = "qz_sms_app"
    return
}

//  获取数据库链接
func (ap *App) link() (conn *gdb.Model) {
    conn = g.DB().Table(ap.TableName() + " g").Safe()
    return
}

//获取列表
func (ap *App) List() ([]App, error) {
    var App = []App{}
    m := ap.link()
    m = m.InnerJoin("qz_adminuser uc", "uc.id=g.creator")
    m = m.InnerJoin("qz_adminuser uo", "uo.id=g.operator")
    m = m.Fields("g.*,uc.name creator_name,uo.name operator_name," +
        "from_unixtime(g.created_at,'%Y-%m-%d %H:%i:%s') created_at," +
        "from_unixtime(g.updated_at,'%Y-%m-%d %H:%i:%s') updated_at")
    err := m.OrderBy("id desc").Structs(&App)

    return App, err
}

// 获取总数
func (ap *App) Count() (int, error) {
    m := ap.link()
    m = m.InnerJoin("qz_adminuser uc", "uc.id=g.creator")
    m = m.InnerJoin("qz_adminuser uo", "uo.id=g.operator")
    ret, err := m.Count()
    return ret, err
}

// 是否有数据
func (ap *App) Has(condition g.Map) (bool, error) {
    m := ap.link()
    m = m.Where(condition)
    ret, err := m.Count()
    return !!(ret > 0), err
}

// 获取详情
func (ap *App) Detail(id int) (App, error) {
    var App = App{}
    m := ap.link()
    m = m.Where("id=?", id)
    m = m.Fields("*")
    err := m.Struct(&App)
    return App, err
}

//保存数据
func (ap *App) Save(data g.Map) (bool, error) {
    saveRet, err := g.DB().Table(ap.TableName()).Safe().Data(data).Insert()
    if err != nil {
        return false, err
    }
    ret, err := saveRet.LastInsertId()
    return !!(ret > 0), err
}

//  更新数据
func (ap *App) Update(condition g.Map, data g.Map) (bool, error) {
    saveRet, err := ap.link().Data(data).Where(condition).Update()
    if err != nil {
        return false, err
    }
    ret, err := saveRet.RowsAffected()
    return !!(ret > 0), err
}


func (ap *App)GetById(id int) (info gdb.Map, err error){
    ret,err := ap.link().Where("id = ?", id).One()
    info = ret.ToMap()
    return info,err
}

func (ap *App) GetAll() (list gdb.List, err error){
    m := ap.link()
    m = m.Where("is_enable = ?", 1)
    m = m.Fields("id,name,slot")
    res, err := m.OrderBy("id desc").Select()

    if err == nil {
        return res.ToList(), nil
    }

    return list, err
}

func (ap *App)GetListByType(app_type string, type_id string) (list gdb.List, err error){
    m := ap.link()
    m = m.Where("g.app_type = ?", app_type)
    m = m.Where("a.type_id = ?", type_id)
    m = m.Where("g.is_enable = ?", 1)
    m = m.InnerJoin("qz_msg_type_app as a", "a.app_slot = g.slot")
    m = m.Fields("g.id,g.name,g.slot,g.app_type,a.type_id")
    res, err := m.OrderBy("id desc").GroupBy("g.id").Select()

    if err == nil {
        return res.ToList(), nil
    }

    return list, err
}