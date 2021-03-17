package logic

import (
    "admin-api/app/model"
    "crypto/sha1"
    "fmt"
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/database/gdb"
    "github.com/gogf/gf/g/util/gconv"
    "math/rand"
    "strconv"
    "time"
)

// 项目应用接入 逻辑
type App struct {
}

//  数据验证
type AppValidate struct {
    ID       int    `gvalid:"id      @integer#无效的ID"`
    Name     string `gvalid:"name      @required#请输入应用名称"`
    Note     string `gvalid:"note      @required#请输入应用说明"`
    IsEnable uint8  `gvalid:"is_enable      @required|in:0,1#请选择是否启用|是否启用选择有误"`
    AppType  int  `gvalid:"app_type      @required|in:1,2#请选择应用类型|应用类型选择有误"`
}

//  实例化三方网关model
var AppModel = model.NewAppModel()

//  网关logic  实例化方式
func NewAppService() *App {
    return &App{}
}

var AccessTypeEnum = g.MapIntStr{
    1: "短信",
    2: "消息",
};

// 获取列表
func (ap *App) List() (list []model.App, err error) {
    list, err = AppModel.List()
    if err != nil {
        return nil, err
    }

    if len(list) > 0 {
        var app_slots g.Slice
        for key, value := range list {
            app_slots = append(app_slots, value.Slot)
            list[key] = ap.transform(&value)
        }

        appAccessModel := model.NewSmsAppAccessModel()
        accessList,err := appAccessModel.GetListByAppSlot(app_slots)
        if err == nil {
            for key,item := range list {
                for _,li := range accessList {
                    if item.Slot == gconv.String(li["app_slot"]) {
                        access_type := gconv.Int(li["access_type"])
                        li["access_type_name"] = AccessTypeEnum[access_type]
                        list[key].AccessList = append(list[key].AccessList, li)
                    }
                }
            }
        }
    }

    return list,err
}

// 获取详情
func (ap *App) Detail(id int) (data map[string]interface{}, err error) {
    list, err := AppModel.Detail(id)
    if err != nil {
        list = model.App{}
        return
    }

    appAccessModel := model.NewSmsAppAccessModel()
    accessList, errs := appAccessModel.GetListByAppSlot(g.Slice{list.Slot})
    if errs == nil {
        list.AccessList = accessList
    }

    list = ap.transform(&list)
    data = make(map[string]interface{})
    data["list"] = list
    return
}

func (ap *App) GetById(id int) (info gdb.Map, err error) {
    info, err = AppModel.GetById(id)
    return info, err
}

//  根据id判断是否存在
func (ap *App) HasById(id int) (isExist bool, err error) {
    condition := g.Map{"id": id}
    isExist, err = AppModel.Has(condition)
    return
}

//  根据name判断是否存在
func (ap *App) HasByName(id int, name string) (isExist bool, err error) {
    var condition g.Map
    if id > 0 {
        condition = g.Map{"id <> ?": id, "name": name}
    } else {
        condition = g.Map{"name": name}
    }
    isExist, err = AppModel.Has(condition)
    return
}

// 生成Slot     sha1
func (ap *App) SetSlot() (slot string) {
    key := strconv.FormatInt(time.Now().Unix(), 10) + strconv.FormatInt(rand.Int63(), 10)
    hash := sha1.New()
    hash.Write([]byte(key))
    slot = fmt.Sprintf("%x", hash.Sum(nil))
    return
}

//  保存数据
func (ap *App) Save(App *model.App) (ret bool, err error) {
    data := g.Map{
        "slot":       App.Slot,
        "name":       App.Name,
        "note":       App.Note,
        "app_type":   App.AppType,
        "is_enable":  App.IsEnable,
        "creator":    model.AdminUser.UserId,
        "operator":   model.AdminUser.UserId,
        "created_at": time.Now().Unix(),
        "updated_at": time.Now().Unix(),
    }
    ret, err = AppModel.Save(data)
    return
}

//  更新数据
func (ap App) Update(App *model.App) (ret bool, err error) {
    data := g.Map{
        "name":       App.Name,
        "note":       App.Note,
        "app_type":   App.AppType,
        "is_enable":  App.IsEnable,
        "operator":   model.AdminUser.UserId,
        "updated_at": time.Now().Unix(),
    }
    condition := g.Map{"id": App.ID}
    ret, err = AppModel.Update(condition, data)
    return
}

//  是否启用网关
func (ap *App) Enable(App *model.App) (ret bool, err error) {
    data := g.Map{
        "is_enable":  App.IsEnable,
        "operator":   model.AdminUser.UserId,
        "updated_at": time.Now().Unix(),
    }
    condition := g.Map{"id": App.ID}
    ret, err = AppModel.Update(condition, data)
    return
}

// 清洗数据
func (ap *App) transform(App *model.App) (data model.App) {
    data = *App
    return
}
