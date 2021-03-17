package router

import (
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/net/ghttp"
    v1 "search/app/controller/v1"
    "search/library/prometheus/factory"
    "search/library/util"
)

func init() {
    s := g.Server()
    s.BindHandler("/",func(r *ghttp.Request){
        json,err := util.EncodeResponseJson(0,"请求成功",nil)
        if err == nil {
            r.Response.Writeln(json)
        }
    })

    //v1版本
    article := new(v1.ArticleController)
    app := new(v1.AppController)
    segmentation := new(v1.SegmentationController)
    wenda := new(v1.WendaController)
    baike := new(v1.BaikeController)
    themtic := new(v1.ThematicWordsController)
    search := new(v1.SearchController)
    littleArticle := new(v1.LittleArticleController)
    meitu := new(v1.MeituController)
    s.Group("/v1").Bind([]ghttp.GroupItem{
        {"ALL", "*", HookHandler, ghttp.HOOK_BEFORE_SERVE},
        {"GET", "/www/search", article, "Search"},//主站文章专题搜索
        {"GET", "/app/search", app, "Search"},//齐装app搜索
        {"GET", "/app/zxssearch", app, "ZxsSearch"},//装修说App搜索
        {"GET", "/wordsegmentation", segmentation, "WordSegmentation"},//分词
        {"GET", "/wenda/search", wenda, "Search"},//问答专题搜索
        {"GET", "/baike/search", baike, "Search"},//百科专题搜索
        {"GET", "/getcount", segmentation, "WordCount"},//搜索匹配对应内容数量
        {"GET", "/getallcount", segmentation, "WordAllCount"},//搜索匹配所有内容数量
        {"GET", "/themtic/getlabel", themtic, "GetContentLable"},//获取内容标签(分词)
        {"GET", "/themtic/getwords", themtic, "GetContentWords"},//获取内容标签(全匹配不分词)
        {"GET", "/themtic/getlabels", themtic, "GetThematicLable"},//获取内容标签(完全匹配)
        {"GET", "/themtic/thematicsearch", themtic, "ThematicSearch"},//WEB、PC内容搜索
        {"GET", "/themtic/thematic_tu_search", themtic, "ThematicTuSearch"},//WEB、PC内容搜索
        {"GET",  "/colligate/search",search,"Search"},//综合内容搜索
        {"GET",  "/littlearticle/search",littleArticle,"Search"},//分站文章搜索内容
        {"GET",  "/meitu/home", meitu, "GetHomeList"},//家装美图搜索内容
        {"GET",  "/meitu/pub", meitu, "GetPubList"},//公装美图搜索内容
        {"GET",  "/meitu/cases", meitu, "GetCasesList"},//案例图搜索内容
        {"GET",  "/scene/scenearticleearch", search, "SceneArticleSearch"},//图文场景搜索
        {"GET",  "/scene/scenegraphicsearch", search, "SceneGraphicSearch"},//图文场景搜索
        {"GET",  "/scene/scenecontentsearch", search, "SceneContentSearch"},//内容场景搜索
        {"GET",  "/scene/scenecontentsubsearch", search, "SceneContentSubSearch"},//内容分类景搜索
    })
    s.BindHandler("/test",TestHandler)
    s.BindHandler("/metrics",MetricsHandler)
}

func HookHandler(r *ghttp.Request) {
    // 设置统一请求参数
    util.Request(r)
}

func MetricsHandler(r *ghttp.Request)  {
    f :=  factory.Create()
    f.Show(r.Response.Writer,r.Request)
}

func TestHandler(r *ghttp.Request)  {
    //factory.Create().CreateCollector("Test").ReqAdd("error","search","testSearch")
    factory.Create().CreateCollector("Error").ReqAdd("elastic","statusSearch")
}
