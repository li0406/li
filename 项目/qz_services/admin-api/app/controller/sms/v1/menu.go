package v1

import (
    "admin-api/app/enum"
    "admin-api/app/model"
    "admin-api/library/util"
    "github.com/gogf/gf/g/frame/gmvc"
    "github.com/gogf/gf/g/util/gconv"
    "strconv"
)

type MenuController struct {
    gmvc.Controller
}

type menuStruct struct {
    Path string `json:"path"`
    Link string `json:"link"`
    LinkType int `json:"link_type"`
    Component string `json:"component"`
    Meta struct{
        Title string `json:"title"`
        Icon string `json:"icon"`
    } `json:"meta"`
    Redirect string `json:"redirect"`
    Children []*menuStruct `json:"children"`
}

func (m *menuStruct) setChild(o *menuStruct) {
    m.Children = append(m.Children, o)
}

func (c *MenuController)GetSmsMenu() {
    var json []byte
    role_id := model.AdminUser.RoleId
    result,err := model.GetSmsMenu()

    if err == nil {
        var myList  = make(map[string]string)
        var menuList []map[string]interface{}
        if role_id != 1 {
            //获取当前角色的权限
            myMenu,err := model.GetMyMenu(role_id)
            if err == nil {
                if len(myMenu) > 0 {
                    for _,val := range myMenu {
                        myList[val["node_id"].(string)] = val["node_id"].(string)
                    }

                    for _,val :=range result {
                        if _,ok := myList[val["nodeid"].(string)];ok {
                            menuList = append(menuList, val)
                        }
                    }
                }
            }
        } else {
            menuList = result
        }

        var list = make(map[string] interface{})
        for _,val := range menuList {
            parentid,_ := strconv.ParseFloat(val["parentid"].(string),64)
            link_type := gconv.Int(val["link_type"])

            if parentid == 0 {
                var menu = new(menuStruct)
                menu.Link =  val["link"].(string)
                menu.Path =   "/" + val["link"].(string)
                menu.Meta.Icon =    val["icon"].(string)
                menu.Meta.Title = val["name"].(string)
                menu.LinkType = link_type
                //menu.Redirect = val["link"].(string)
                menu.Component = "Layout"
                list[val["nodeid"].(string)] = menu
            } else {
                var sub = new(menuStruct)
                sub.Path =   val["link"].(string)
                sub.Meta.Icon =    val["icon"].(string)
                sub.Meta.Title = val["name"].(string)
                sub.LinkType = link_type

                var item = list[val["parentid"].(string)]
                if (item != nil) {
                    sub.Component = item.(*menuStruct).Link + "/" + val["link"].(string)
                    if  len(list[val["parentid"].(string)].(*menuStruct).Children) == 0{
                        list[val["parentid"].(string)].(*menuStruct).Redirect = val["link"].(string)
                    }

                    list[val["parentid"].(string)].(*menuStruct).setChild(sub)
                }
            }
        }
        json,_ = util.EncodeResponseJson(0,enum.CODE_0,list)
    } else {
        json,_ = util.EncodeResponseJson(5000002,enum.CODE_5000002,err.Error())
    }
    c.Response.WriteJson(json)
}

