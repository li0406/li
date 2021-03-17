package v1

import (
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/frame/gmvc"
    "search/app/enum"
    "search/app/logic"
    "search/library/util"
    "strings"
    "unicode/utf8"
)

type ThematicWordsController struct {
    gmvc.Controller
}

// 根据词进行拆词匹配标签
func (c *ThematicWordsController)GetContentLable()  {
    title := c.Request.Get("title")
    t := c.Request.GetInt("type")
    limit := c.Request.GetInt("limit")

    str := strings.Split(title,",")
    var words  []string
    m := logic.NewElasticLogic()
    for _,v := range str{
        //分词操作
        analyzeWord := m.Analyze(v)
        for _,v := range  analyzeWord{
            if utf8.RuneCountInString(v.Token) >  1 {
                words = append(words,v.Token)
            }
        }
    }

    list := m.GetContentLabel(words, t, true, limit)

    json,_ := util.EncodeResponseJson(0,enum.CODE_0,list)
    _ = c.Response.WriteJson(json)
}

// 多个词不拆词匹配标签
func (c *ThematicWordsController)GetContentWords() {
    title := c.Request.Get("title")
    t := c.Request.GetInt("type")
    limit := c.Request.GetInt("limit")

    words := strings.Split(title,",")

    m := logic.NewElasticLogic()
    list := m.GetContentLabel(words, t, true, limit)

    json,_ := util.EncodeResponseJson(0,enum.CODE_0,list)
    _ = c.Response.WriteJson(json)
}

// 精确匹配目标词标签
func (c *ThematicWordsController)GetThematicLable() {
    title := c.Request.Get("title")
    ttype := c.Request.GetInt("type")
    limit := c.Request.GetInt("limit")

    m := logic.NewElasticLogic()
    list := m.GetThematicLable(title, ttype, true, limit)

    json,_ := util.EncodeResponseJson(0,enum.CODE_0,list)
    _ = c.Response.WriteJson(json)
}


func (c *ThematicWordsController)ThematicSearch()  {
    word := c.Request.Get("word")
    pageIndex := util.PageFormat(c.Request.GetInt("page"), 1)
    pageCount := util.PageFormat(c.Request.GetInt("count"), 10)

    if word == ""  {
        json,_ := util.EncodeResponseJson(0,enum.CODE_4000002,nil)
        _ = c.Response.WriteJson(json)
        c.Request.ExitAll()
    }

    index := []string{"qz_www_article","qz_baike","qz_ask","qz_little_article"}

    m := logic.NewElasticLogic()
    var list  = make(map[string] interface{})
    res,p := m.ThematicSearch(word,index,pageIndex,pageCount)

    list["list"] = res
    list["page"] = p

    json,_ := util.EncodeResponseJson(0,enum.CODE_0,list)
    _ = c.Response.WriteJson(json)
}

// 美图搜索
func (c *ThematicWordsController)ThematicTuSearch() {
    word := c.Request.Get("word")
    page := util.PageFormat(c.Request.GetInt("page"), 1)
    limit := util.PageFormat(c.Request.GetInt("limit"), 40)

    if word == "" {
        json,_ := util.EncodeResponseJson(0,enum.CODE_4000002,nil)
        _ = c.Response.WriteJson(json)
        c.Request.ExitAll()
    }

    // 分词
    m := logic.NewElasticLogic()
    analyzeWord := m.Analyze(word)

    partwords := g.SliceStr{}
    for _,v := range analyzeWord {
        if utf8.RuneCountInString(v.Token) >  1 {
            partwords = append(partwords, v.Token)
        }
    }

    var list = make(map[string] interface{})
    index := []string{"qz_tu_home","qz_tu_pub"}
    res,p := m.ThematicTuSearch(word, partwords, index, page, limit)

    list["list"] = res
    list["page"] = p

    json,_ := util.EncodeResponseJson(0,enum.CODE_0,list)
    _ = c.Response.WriteJson(json)
}