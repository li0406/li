package v1

import (
    "github.com/gogf/gf/g/frame/gmvc"
    "search/app/enum"
    "search/app/logic"
    "search/library/util"
    "unicode/utf8"
)

type SegmentationController struct {
    gmvc.Controller
}

func (c SegmentationController)WordSegmentation()  {
    word :=  c.Request.Get("word")
    m := logic.NewElasticLogic()

    list := m.Analyze(word)
    json,_ := util.EncodeResponseJson(0,enum.CODE_0,list)
    c.Response.WriteJson(json)
}

func (c SegmentationController)WordCount()  {
    word :=  c.Request.Get("word")
    module := c.Request.Get("module")

    index := "qz_www_article"
    switch module {
        case "1": index = "qz_www_article"; break
        case "2": index = "qz_baike"; break
        case "3": index = "qz_ask"; break
    }

    m := logic.NewElasticLogic()
    //分词操作
    analyzeWord := m.Analyze(word)
    words := ""
    for _,v := range  analyzeWord{
        if len(v.Token) >  1 {
            words += v.Token + " "
        }
    }


    var query = make(map[string] string)
    query["name"] = "title"
    query["query"] = words

    count := m.WordCount(query,index)

    json,_ := util.EncodeResponseJson(0,enum.CODE_0,count)
    c.Response.WriteJson(json)
}

func (c SegmentationController)WordAllCount()  {
    word :=  c.Request.Get("word")
    t :=  c.Request.GetInt("type")

    index := []string{"qz_www_article","qz_ask","qz_baike","qz_little_article"}
    if t == 2 {
        //图片
        index = []string{"qz_tu_home","qz_tu_pub"}
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

    var query = make(map[string] string)
    query["name"] = "title"
    query["query"] = words

    count := m.WordAllCount(query,index)

    json,_ := util.EncodeResponseJson(0,enum.CODE_0,count)
    c.Response.WriteJson(json)
}