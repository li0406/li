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

type MeituController struct {
    gmvc.Controller
}

// 家装美图
func (that *MeituController) GetHomeList(){
    scene_word := that.Request.Get("scene_word")
    page := util.PageFormat(that.Request.GetInt("page"), 1)
    limit := util.PageFormat(that.Request.GetInt("limit"), 24)

    input := g.MapStrStr{
        "search_word": that.Request.Get("search_word"),
        "cate_field": that.Request.Get("cate_field"),
        "cate_id": that.Request.Get("cate_id"),
    }

    if scene_word == "" {
        json,_ := util.EncodeResponseJson(0,enum.CODE_4000002,nil)
        _ = that.Response.WriteJson(json)
        that.Request.ExitAll()
    }

    esLogic := logic.NewElasticLogic()
    input["words"] = that.analyzeWord(input["search_word"])
    list,p := esLogic.MeituHomeSearch(scene_word, input, page, limit)

    data := g.Map{
        "list": list,
        "page": p,
    }

    json,_ := util.EncodeResponseJson(0,enum.CODE_0, data)
    _ = that.Response.WriteJson(json)
}

// 公装美图
func (that *MeituController) GetPubList(){
    scene_word := that.Request.Get("scene_word")
    page := util.PageFormat(that.Request.GetInt("page"), 1)
    limit := util.PageFormat(that.Request.GetInt("limit"), 24)

    input := g.MapStrStr{
        "search_word": that.Request.Get("search_word"),
        "cate_id": that.Request.Get("cate_id"),
    }

    //if scene_word == "" {
    //    json,_ := util.EncodeResponseJson(0,enum.CODE_4000002,nil)
    //    _ = that.Response.WriteJson(json)
    //    that.Request.ExitAll()
    //}

    esLogic := logic.NewElasticLogic()
    input["words"] = that.analyzeWord(input["search_word"])
    list,p := esLogic.MeituPubSearch(scene_word, input, page, limit)

    data := g.Map{
        "list": list,
        "page": p,
    }

    json,_ := util.EncodeResponseJson(0,enum.CODE_0, data)
    _ = that.Response.WriteJson(json)
}

// 案例图
func (that *MeituController) GetCasesList(){
    scene_word := that.Request.Get("scene_word")
    page := util.PageFormat(that.Request.GetInt("page"), 1)
    limit := util.PageFormat(that.Request.GetInt("limit"), 24)

    input := g.MapStrStr{
        "search_word": that.Request.Get("search_word"),
        "classid": that.Request.Get("classid"),
        "huxing": that.Request.Get("huxing"),
        "leixing": that.Request.Get("leixing"),
        "fengge": that.Request.Get("fengge"),
        "zaojia": that.Request.Get("zaojia"),
        "mianji_min": that.Request.Get("mianji_min"),
        "mianji_max": that.Request.Get("mianji_max"),
        "cs": that.Request.Get("cs"),
    }

    esLogic := logic.NewElasticLogic()
    input["words"] = that.analyzeWord(input["search_word"])
    list,p := esLogic.MeituCasesSearch(scene_word, input, page, limit)

    data := g.Map{
        "list": list,
        "page": p,
    }

    json,_ := util.EncodeResponseJson(0,enum.CODE_0, data)
    _ = that.Response.WriteJson(json)
}


// 分词
func (that *MeituController) analyzeWord(search_word string) (words string) {
    esLogic := logic.NewElasticLogic()

    var wordList []string
    if search_word != "" {
        searchWord := esLogic.Analyze(search_word)
        for _,v := range searchWord {
            if utf8.RuneCountInString(v.Token) >  1 {
                wordList = append(wordList, v.Token)
            }
        }
    }

    return strings.Join(wordList, " ")
}