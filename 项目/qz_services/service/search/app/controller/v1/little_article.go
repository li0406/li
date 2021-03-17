package v1

import (
"github.com/gogf/gf/g/frame/gmvc"
"search/app/enum"
"search/app/logic"
"search/library/util"
"unicode/utf8"
)

type LittleArticleController struct {
    gmvc.Controller
}

func (c LittleArticleController)Search()  {
    word := c.Request.Get("word")
    pageIndex := c.Request.GetInt("page")
    pageCount := c.Request.GetInt("limit")

    if pageCount == 0 {
        pageCount = 10;
    }

    if pageIndex <= 0 {
        pageIndex = 1;
    }

    pageIndex = (pageIndex-1)*pageCount

    if  word == ""  {
        json,_ := util.EncodeResponseJson(0,enum.CODE_4000002,nil)
        c.Response.WriteJson(json)
        c.Request.ExitAll()
    }
    m := logic.NewElasticLogic()
    //分词操作
    analyzeWord := m.Analyze(word)
    words := ""
    for _,v := range  analyzeWord{
        if utf8.RuneCountInString(v.Token) >  1 {
            words += v.Token + " "
        }
    }

    var list  = make(map[string] interface{})
    res,p := m.LittleArticleSearch(words,pageIndex,pageCount)
    list["list"] = res
    list["page"] = p
    json,_ := util.EncodeResponseJson(0,enum.CODE_0,list)
    c.Response.WriteJson(json)
}




