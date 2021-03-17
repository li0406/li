package v1

import (
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/frame/gmvc"
    "search/app/enum"
    "search/app/logic"
    "search/library/redis"
    "search/library/util"
    "strings"
    "unicode/utf8"
)

type SearchController struct {
    gmvc.Controller
}

func (c SearchController)Search() {
    word :=  c.Request.Get("word")
    from := c.Request.Get("from")
    limit := c.Request.GetInt("limit")
    if limit == 0 {
         limit = 10
    }

    if from == "" {
        from = "m"
    }

    index := []string{"qz_www_article","qz_baike","qz_ask","qz_little_article"}

    m := logic.NewElasticLogic()
    //分词操作
    analyzeWord := m.Analyze(word)
    words := ""
    for _,v := range  analyzeWord{
        if utf8.RuneCountInString(v.Token) >  1 {
            words += v.Token + " "
        }
    }

    //添加搜索词统计
    redis.NewRedis().SetHashIncr("search",from + "-" + word,1)

    list := m.ColligateSearch(words,index,limit)
    json,_ := util.EncodeResponseJson(0,enum.CODE_0,list)
    c.Response.WriteJson(json)
}

/**
 * 图文场景搜索
 */
func (c SearchController)SceneGraphicSearch()  {
    sence  := c.Request.Get("sence")
    pageIndex  := c.Request.GetInt("page")
    limit := c.Request.GetInt("limit")
    word :=  c.Request.Get("word")

    if pageIndex <= 0 {
        pageIndex = 1;
    }

    if limit == 0 {
        limit = 10
    }
    index := []string{"qz_www_article","qz_baike","qz_ask"}

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
    res,page := m.SceneGraphicSearch(sence,words,index,pageIndex,limit)
    list["list"] = res
    list["page"] = page
    json,_ := util.EncodeResponseJson(0,enum.CODE_0,list)
    c.Response.WriteJson(json)
}

func (c SearchController)SceneArticleSearch(){
    page := util.PageFormat(c.Request.GetInt("page"), 1)
    limit := util.PageFormat(c.Request.GetInt("limit"), 10)

    sence  := c.Request.Get("sence")
    word :=  c.Request.Get("word")

    esLogic := logic.NewElasticLogic()
    //分词操作
    analyzeWord := esLogic.Analyze(word)
    words := ""
    for _,v := range  analyzeWord{
        if utf8.RuneCountInString(v.Token) >  1 {
            words += v.Token + " "
        }
    }

    sences := strings.Split(sence, ",")
    res,p := esLogic.SceneArticleSearch(sences,words,page,limit)

    resp := g.Map{
        "list": res,
        "page": p,
    }

    json,_ := util.EncodeResponseJson(0,enum.CODE_0,resp)
    _ = c.Response.WriteJson(json)
}


/**
 * 内容场景搜索
 */
func (c SearchController)SceneContentSearch()  {
    pageIndex  := c.Request.GetInt("page")
    limit := c.Request.GetInt("limit")
    word :=  c.Request.Get("word")

    if pageIndex <= 0 {
        pageIndex = 1;
    }

    if limit == 0 {
        limit = 10
    }
    index := []string{"qz_www_article","qz_baike","qz_ask"}

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
    res,page := m.SceneContentSearch(words,index,pageIndex,limit)
    list["list"] = res
    list["page"] = page
    json,_ := util.EncodeResponseJson(0,enum.CODE_0,list)
    c.Response.WriteJson(json)
}

/**
 * 分类场景搜索
 */
func (c SearchController)SceneContentSubSearch()  {
    pageIndex  := c.Request.GetInt("page")
    limit := c.Request.GetInt("limit")
    word :=  c.Request.Get("word")
    module :=  c.Request.Get("module")

    if pageIndex <= 0 {
        pageIndex = 1;
    }

    if limit == 0 {
        limit = 10
    }
    index := []string{"qz_www_article"}

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
    res,page := m.SceneContentSubSearch(module,words,index,pageIndex,limit)
    list["list"] = res
    list["page"] = page
    json,_ := util.EncodeResponseJson(0,enum.CODE_0,list)
    c.Response.WriteJson(json)
}
