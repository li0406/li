package v1

import (
    "github.com/gogf/gf/g/frame/gmvc"
    "search/app/enum"
    "search/app/logic"
    "search/library/util"
)

type AppController struct {
    gmvc.Controller
}

func (c AppController)Search()  {
    query :=  c.Request.Get("query")
    page :=  c.Request.GetInt("page")
    module := c.Request.Get("type")
    var logtype []string
    indexArr := make(map[string] string)
    indexArr["picture"] = "qz_zxs_picture"
    indexArr["meijia"] = "qz_zxs_article_meijia"
    indexArr["video"] = "qz_zxs_vedio"
    indexArr["strategy"] = "qz_zxs_strategy"
    //索引范围
    if module != "" {
        logtype =  append(logtype, indexArr[module])
    } else {
       for _,v := range indexArr {
           logtype =  append(logtype, v)
       }
    }

    //查询的字段
    fields := []string{"title","tag"}

    pageIndex := 1
    pageCount := 10
    if page != 0 {
        pageIndex = page;
    }

    if pageIndex <= 0 {
        pageIndex = 1;
    }

    pageIndex = (pageIndex-1)*pageCount
    var list  = make(map[string] interface{})
    m := new(logic.ElasticLogic)
    res,p,_ := m.AppSearch(query,fields,indexArr,logtype,pageIndex,pageCount)
    list["list"] = res
    list["page"] = p
    json,_ := util.EncodeResponseJson(0,enum.CODE_0,list)
    c.Response.WriteJson(json)
}

func (c AppController)ZxsSearch()  {
    query :=  c.Request.Get("query")
    page :=  c.Request.GetInt("page")
    module := c.Request.Get("type")
    var logtype []string
    indexArr := make(map[string] string)
    indexArr["topic"] = "qz_zxs_topic"
    indexArr["meijia"] = "qz_zxs_article_meijia"
    indexArr["xinde"] = "qz_zxs_article_xinde"
    indexArr["experience"] = "qz_zxs_experience"
    //索引范围
    if module != "" {
        logtype =  append(logtype, indexArr[module])
    } else {
        for _,v := range indexArr {
            logtype =  append(logtype, v)
        }
    }

    //查询的字段
    fields := []string{"title","tag"}

    pageIndex := 1
    pageCount := 10
    if page != 0 {
        pageIndex = page;
    }

    if pageIndex <= 0 {
        pageIndex = 1;
    }

    pageIndex = (pageIndex-1)*pageCount
    var list  = make(map[string] interface{})
    m := new(logic.ElasticLogic)
    res,p,_ := m.AppSearch(query,fields,indexArr,logtype,pageIndex,pageCount)
    list["list"] = res
    list["page"] = p
    json,_ := util.EncodeResponseJson(0,enum.CODE_0,list)
    c.Response.WriteJson(json)
}


