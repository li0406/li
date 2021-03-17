package logic

import (
    "context"
    "fmt"
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/encoding/gjson"
    "github.com/olivere/elastic/v7"
    "log"
    "os"
    "search/library/prometheus/factory"
    "search/library/util"
    "time"
)

var (
    client *elastic.Client
)

type ElasticLogic struct {

}

type Article struct {
    Title string `json:"title"`
    Id int `json:"id"`
    Description string `json:"description"`
    Img_path string `json:"img_path"`
    Three_short_name string `json:"three_short_name"`
    Createtime int `json:"createtime"`
    Logtype string `json:"logtype"`
    Frist_short_name string `json:"first_short_name"`
}

type Ask struct {
    Title string `json:"title"`
    Id int `json:"id"`
    Content string `json:"content"`
    Createtime int `json:"createtime"`
    Anwsers int  `json:"anwsers"`
    Adop_time int `json:"adop_time"`
    Views int `json:"views"`
    Logtype string `json:"logtype"`
    User_logo string `json:"user_logo"`
    User_name  string `json:"user_name"`
}

type Baike struct {
    Id int `json:"id"`
    Title string `json:"title"`
    Description string `json:"description"`
    Img_path string `json:"img_path"`
    Createtime string `json:"createtime"`
    Views int `json:"views"`
    Favorites int `json:"favorites"`
    Logtype string `json:"logtype"`
    User_logo string `json:"user_logo"`
}

type App struct {
    Id int `json:"id"`
    Logtype string `json:"logtype"`
    Img_path string `json:"img_path"`
    Title string  `json:"title"`
    Createtime int `json:"createtime"`
}

type Word struct {
    Token string `json:"token"`
    Position int `json:"position"`
}

type ThematicWords struct {
    Id int `json:"id"`
    Name string `json:"name"`
    Module int `json:"module"`
    Type int `json:"type"`
}

type Content struct {
    Id int `json:"id"`
    Title string `json:"title"`
    Img_path string `json:"img_path"`
    Content string `json:"content"`
    Description string `json:"description"`
    Three_short_name string `json:"three_short_name"`
    Logtype string `json:"logtype"`
    Createtime int `json:"createtime"`
    Anwsers int `json:"anwsers"`
    Bm string `json:"bm"`
}

type Colligate struct {
    Id int `json:"id"`
    Title string `json:"title"`
    Img_path string `json:"img_path"`
    Content string `json:"content"`
    Description string `json:"description"`
    Three_short_name string `json:"three_short_name"`
    Logtype string `json:"logtype"`
    Createtime int `json:"createtime"`
    Anwsers int `json:"anwsers"`
    Bm string `json:"bm"`
    User_logo string `json:"user_logo"`
    User_name  string `json:"user_name"`
}

type Meitu struct {
    Id int `json:"id"`
    Title string `json:"title"`
    Img string `json:"img"`
    ImgHost string `json:"img_host"`
    ImgPath string `json:"img_path"`
    ImgSrc string `json:"img_src"`
    ImgTitle string `json:"img_title"`
    Logtype string `json:"logtype"`
    Createtime int `json:"createtime"`
}

type MeituTu struct {
    Id int `json:"id"`
    Title string `json:"title"`
    Description string `json:"description"`
    Keyword string `json:"keyword"`
    ImgSrc string `json:"img_src"`
    ImgTitle string `json:"img_title"`
    HomeSpaceCategory int `json:"home_space_category"`
    HomePartCategory int `json:"home_part_category"`
    HomeStyleCategory int `json:"home_style_category"`
    HomeLayoutCcategory int `json:"home_layout_category"`
    PubCategory int `json:"pub_category"`
    Logtype string `json:"logtype"`
    Createtime int `json:"createtime"`
    CreatedAt int `json:"created_at"`
}

type MeituCases struct {
    Id int `json:"id"`
    Classid string `json:"classid"`
    Title string `json:"title"`
    Bm string `json:"bm"`
    Mianji string `json:"mianji"`
    Leixing string `json:"leixing"`
    LeixingName string `json:"leixing_name"`
    Fengge string `json:"fengge"`
    FenggeName string `json:"fengge_name"`
    Zaojia string `json:"zaojia"`
    JiageName string `json:"jiage_name"`
    Huxing string `json:"huxing"`
    HuxingName string `json:"huxing_name"`
    Userid int `json:"userid"`
    CompanyJc string `json:"company_jc"`
    CompanyQc string `json:"company_qc"`
    Cs int `json:"cs"`
    Cname string `json:"cname"`
    Img string `json:"img"`
    ImgHost string `json:"img_host"`
    ImgPath string `json:"img_path"`
    ImgSrc string `json:"img_src"`
    ImgTitle string `json:"img_title"`
    Logtype string `json:"logtype"`
    Createtime int `json:"createtime"`
}

type LittleArticle struct {
    Title string `json:"title"`
    Id int `json:"id"`
    Description string `json:"description"`
    Img_path string `json:"img_path"`
    Bm string `json:"bm"`
    Createtime int `json:"createtime"`
    Logtype string `json:"logtype"`
}

func init()  {
    c := g.Config()
    host := c.GetString("elasticsearch.host")
    username := c.GetString("elasticsearch.username")
    password := c.GetString("elasticsearch.password")
    res, err := elastic.NewClient(
        elastic.SetURL(host),
        elastic.SetHealthcheckInterval(5*time.Second),
        elastic.SetSniff(false),
        elastic.SetBasicAuth(username,password),
        elastic.SetGzip(true),
        elastic.SetErrorLog(log.New(os.Stderr, "ELASTIC ", log.LstdFlags)),
    )
    if err != nil {
        fmt.Println(err)
    }
    client = res
}

func NewElasticLogic() *ElasticLogic{
    return &ElasticLogic{}
}

func (c ElasticLogic)SearchMatchPhrase(index string,key string ,word string,pageIndex int,pageCount int)(list []*Article,p util.PageStrut) {
    q := elastic.NewMatchPhraseQuery(key,word).Slop(1)
    res,err := client.Search().Index(index).Query(q).From(pageIndex).Size(pageCount).Do(context.Background())
    if err == nil {
        p = util.GetPage(pageIndex,pageCount,int(res.Hits.TotalHits.Value))
        for _,v := range res.Hits.Hits {
            sub := new(Article)
            data,_ := gjson.Encode(v.Source)
            _ = gjson.DecodeTo(data,sub)
            list = append(list, sub)
        }

        factory.Create().CreateCollector("Search").ReqAdd("elastic","SearchMatchPhrase")
    } else {
        factory.Create().CreateCollector("Error").ReqAdd("elastic","SearchMatchPhrase")
    }

    return list,p
}

func (c ElasticLogic)AppSearch(query string,fields []string,indexArr map[string] string,logtype []string,pageIndex int,pageCount int)(list []*App,page util.PageStrut,err error)  {
    //must参数
    mq := elastic.NewMultiMatchQuery(query).MinimumShouldMatch("30%").Type("best_fields").
            Fuzziness("auto")
    for _,v := range fields  {
        mq.Field(v)
    }
    //过滤参数
    fq := elastic.NewBoolQuery()
    for _,v := range logtype {
       fq.Should(elastic.NewTermQuery("logtype",v))
    }

    bq := elastic.NewBoolQuery().Must(mq).Filter(fq)
    cc := client.Search().Sort("_score",false).From(pageIndex).Size(pageCount).Query(bq)
    for _,v := range indexArr {
        cc.Index(v)
    }

    res,err :=cc.Do(context.Background())
    if err == nil {
        page = util.GetPage( pageIndex/pageCount+1,pageCount,int(res.Hits.TotalHits.Value))
        for _,v := range res.Hits.Hits {
            sub := new(App)
            data,_ := gjson.Encode(v.Source)
            gjson.DecodeTo(data,sub)
            list = append(list, sub)
        }
        factory.Create().CreateCollector("Search").ReqAdd("elastic","AppSearch")
    } else {
        factory.Create().CreateCollector("Error").ReqAdd("elastic","AppSearch")
    }

    return list,page,err
}

func (c ElasticLogic)Analyze(word string)(list []*Word)  {
    res,err := elastic.NewIndicesAnalyzeService(client).Analyzer("ik_max_word").Text(word).Do(context.Background())
    if err == nil {
        for _,v := range res.Tokens {
           sub := new(Word)
           data,_ := gjson.Encode(v)
           _ = gjson.DecodeTo(data,sub)
           list = append(list, sub)
        }
    }
    return list
}

func (c ElasticLogic)WwwSearch(query string,pageIndex int,pageCount int)(list []*Article,p util.PageStrut) {
    q := elastic.NewMatchQuery("title",query).Operator("OR").MinimumShouldMatch("40%")
    res,err :=client.Search().Index("qz_www_article").Query(q).From(pageIndex).Size(pageCount).Sort("_score",false).Do(context.Background())

    if err == nil {
        p = util.GetPage(pageIndex/pageCount+1,pageCount,int(res.Hits.TotalHits.Value))

        for _,v := range res.Hits.Hits {
            sub := new(Article)
            data,_ := gjson.Encode(v.Source)
            _ = gjson.DecodeTo(data,sub)
            list = append(list, sub)
        }

        factory.Create().CreateCollector("Search").ReqAdd("elastic","WwwSearch")
    } else {
        factory.Create().CreateCollector("Error").ReqAdd("elastic","WwwSearch")
    }

    return list,p
}

func (c ElasticLogic)WendaSearch(query string,pageIndex int,pageCount int)(list []*Ask,p util.PageStrut) {
    q := elastic.NewMatchQuery("title",query).Operator("OR").MinimumShouldMatch("40%")
    res,err :=client.Search().Index("qz_ask").Query(q).From(pageIndex).Size(pageCount).Sort("_score",false).Do(context.Background())

    if err == nil {
        p = util.GetPage(pageIndex/pageCount+1,pageCount,int(res.Hits.TotalHits.Value))

        for _,v := range res.Hits.Hits {
            sub := new(Ask)
            data,_ := gjson.Encode(v.Source)
            _ = gjson.DecodeTo(data,sub)
            list = append(list, sub)
        }

        factory.Create().CreateCollector("Search").ReqAdd("elastic","WendaSearch")
    } else {
        factory.Create().CreateCollector("Error").ReqAdd("elastic","WendaSearch")
    }

    return list,p
}

func (c ElasticLogic)BaikeSearch(query string,pageIndex int,pageCount int)(list []*Baike,p util.PageStrut) {
    q := elastic.NewMatchQuery("title",query).Operator("OR").MinimumShouldMatch("40%")
    res,err :=client.Search().Index("qz_baike").Query(q).From(pageIndex).Size(pageCount).Sort("_score",false).Do(context.Background())

    if err == nil {
        p = util.GetPage(pageIndex/pageCount+1,pageCount,int(res.Hits.TotalHits.Value))

        for _,v := range res.Hits.Hits {
            sub := new(Baike)
            data,_ := gjson.Encode(v.Source)
            _ = gjson.DecodeTo(data,sub)
            list = append(list, sub)
        }

        factory.Create().CreateCollector("Search").ReqAdd("elastic","BaikeSearch")
    } else {
        factory.Create().CreateCollector("Error").ReqAdd("elastic","BaikeSearch")
    }

    return list,p
}

func (c ElasticLogic)WordCount(query map[string] string, index string) int64 {
    q := elastic.NewMatchQuery(query["name"], query["query"]).Operator("AND")
    res,err := client.Count().Query(q).Index(index).Do(context.Background())

    if err == nil {
        factory.Create().CreateCollector("Search").ReqAdd("elastic","WordCount")
    } else {
        factory.Create().CreateCollector("Error").ReqAdd("elastic","WordCount")
    }

    return res
}

func (c ElasticLogic)WordAllCount(query map[string] string, index []string) int64 {
    q := elastic.NewMatchQuery(query["name"], query["query"]).Operator("AND")
    cc := client.Count().Query(q)
    for _,v := range index  {
        cc.Index(v)
    }
    res,err := cc.Do(context.Background())

    if err == nil {
        factory.Create().CreateCollector("Search").ReqAdd("elastic","WordAllCount")
    } else {
        factory.Create().CreateCollector("Error").ReqAdd("elastic","WordAllCount")
    }

    return res
}

func  (c ElasticLogic)GetContentLabel(query []string, t int, is_show bool, size int)(list []*ThematicWords) {
    q := elastic.NewBoolQuery()
    for _,v :=range query {
        sq := elastic.NewMatchQuery("name",v)
        q.Should(sq)
    }

    if is_show {
      q.Must(elastic.NewMatchQuery("is_show", 1))
    }

    if t != 0 {
       q.Must(elastic.NewMatchQuery("type", t))
    }
    fmt.Println(q.Source())
    res,err := client.Search().MinScore(11).
        Index("qz_thematic_words").
        Query(q).Size(size).
        Sort("is_show",true).
        Sort("_score",false).
        Do(context.Background())

    if err == nil {
        for _,v := range res.Hits.Hits {
            sub := new(ThematicWords)
            data,_ := gjson.Encode(v.Source)
            _ = gjson.DecodeTo(data,sub)
            list = append(list, sub)
        }

        factory.Create().CreateCollector("Search").ReqAdd("elastic","GetContentLabel")
    } else {
        factory.Create().CreateCollector("Error").ReqAdd("elastic","GetContentLabel")
    }

    return list
}

func (c ElasticLogic)GetThematicLable(word string, ttype int, is_show bool, size int)(list []*ThematicWords){
    wordMap := elastic.NewMatchPhraseQuery("name", word)
    query := elastic.NewBoolQuery().Must(wordMap)

    if is_show {
        query.Must(elastic.NewMatchQuery("is_show", 1))
    }

    if ttype != 0 {
        query.Must(elastic.NewMatchQuery("type", ttype))
    }

    res,err := client.Search().
        Index("qz_thematic_words").
        Query(query).Size(size).
        Sort("is_show",true).
        Sort("_score",false).
        Do(context.Background())

    if err == nil {
        for _,v := range res.Hits.Hits {
            sub := new(ThematicWords)
            data,_ := gjson.Encode(v.Source)
            _ = gjson.DecodeTo(data,sub)
            list = append(list, sub)
        }

        factory.Create().CreateCollector("Search").ReqAdd("elastic","GetThematicLable")
    } else {
        factory.Create().CreateCollector("Error").ReqAdd("elastic","GetThematicLable")
    }

    return list
}

func (c ElasticLogic)ThematicSearch(query string,index []string,pageIndex int,pageCount int) (list []*Content,p util.PageStrut) {
    offset := util.GetPageOffset(pageIndex, pageCount)
    q := elastic.NewBoolQuery()

    mq := elastic.NewMatchPhraseQuery("title",query).Slop(4)
    q.Must(mq)
    cc := client.Search().Query(q).From(offset).Size(pageCount).Sort("_score",false)
    for _,v := range index {
       cc.Index(v)
    }

    count := 0
    res,err :=cc.Do(context.Background())

    if err == nil {
        count = int(res.Hits.TotalHits.Value)
        for _,v := range res.Hits.Hits {
            sub := new(Content)
            data,_ := gjson.Encode(v.Source)
            _ = gjson.DecodeTo(data,sub)
            list = append(list, sub)
        }
        factory.Create().CreateCollector("Search").ReqAdd("elastic","ThematicSearch")
    } else {
        factory.Create().CreateCollector("Error").ReqAdd("elastic","ThematicSearch")
    }

    p = util.GetPage(pageIndex, pageCount, count)
    return list,p
}

func (c ElasticLogic) ThematicTuSearch(word string, partwords g.SliceStr, index []string, pageIndex int, pageLimit int) (list []*Meitu, p util.PageStrut) {

    query := elastic.NewBoolQuery()
    if len(partwords) > 0 {
        // 分词匹配
        for _,item := range partwords {
            if item != "" {
                itemMap := elastic.NewMatchPhraseQuery("title", item).Slop(0)
                query.Must(itemMap)
            }
        }
    } else {
        // 标题全匹配
        wordMap := elastic.NewMatchPhraseQuery("title", word).Slop(4)
        query.Must(wordMap)
    }

    offset := util.GetPageOffset(pageIndex, pageLimit)
    cc := client.Search().Query(query).From(offset).Size(pageLimit).Sort("_score",false)
    for _,v := range index {
        cc.Index(v)
    }

    count := 0
    res,err := cc.Do(context.Background())
    if err == nil {
        count = int(res.Hits.TotalHits.Value)
        for _,v := range res.Hits.Hits {
            sub := new(Meitu)
            data,_ := gjson.Encode(v.Source)
            _ = gjson.DecodeTo(data, sub)
            list = append(list, sub)
        }

        factory.Create().CreateCollector("Search").ReqAdd("elastic","ThematicTuSearch")
    } else {
        factory.Create().CreateCollector("Error").ReqAdd("elastic","ThematicTuSearch")
    }

    p = util.GetPage(pageIndex, pageLimit, count)
    return list,p
}

func (c ElasticLogic)ColligateSearch(query string,index []string,limit int)(list []*Colligate)  {
    q := elastic.NewMatchQuery("title",query).Operator("OR").MinimumShouldMatch("40%")

    topHisAgg := elastic.NewTopHitsAggregation().Size(limit).Sort("_score",false)
    termAgg := elastic.NewTermsAggregation().Field("logtype").SubAggregation("result",topHisAgg)
    cc := client.Search().Query(q).Aggregation("result",termAgg)

    for _,v := range index  {
        cc.Index(v)
    }

    res,e :=cc.Do(context.Background())

    if e == nil {
        raw := res.Aggregations["result"]
        var ar elastic.AggregationBucketKeyItems
        _ = gjson.DecodeTo(raw,&ar)
        for _, item := range ar.Buckets {
            var arr elastic.AggregationTopHitsMetric
            _ = gjson.DecodeTo(item.Aggregations["result"],&arr)
            for _,v := range arr.Hits.Hits {
                sub := new(Colligate)
                data,_ := gjson.Encode(v.Source)
                _ = gjson.DecodeTo(data,sub)
                list = append(list, sub)
            }
        }

        factory.Create().CreateCollector("Search").ReqAdd("elastic","ColligateSearch")
    } else {
        factory.Create().CreateCollector("Error").ReqAdd("elastic","ColligateSearch")
    }

    return list
}

func (c ElasticLogic)LittleArticleSearch(query string,pageIndex int,pageCount int)(list []*LittleArticle,p util.PageStrut)  {
    q := elastic.NewMatchQuery("title",query).Operator("OR").MinimumShouldMatch("40%")
    res,err :=client.Search().Index("qz_little_article").Query(q).From(pageIndex).Size(pageCount).Sort("_score",false).Do(context.Background())

    if err == nil {
        p = util.GetPage(pageIndex/pageCount+1,pageCount,int(res.Hits.TotalHits.Value))

        for _,v := range res.Hits.Hits {
            sub := new(LittleArticle)
            data,_ := gjson.Encode(v.Source)
            gjson.DecodeTo(data,sub)
            list = append(list, sub)
        }

        factory.Create().CreateCollector("Search").ReqAdd("elastic","LittleArticleSearch")
    } else {
        factory.Create().CreateCollector("Error").ReqAdd("elastic","LittleArticleSearch")
    }

    return list,p
}

// 家装美图搜索
func (c ElasticLogic) MeituHomeSearch(scene_word string, input g.MapStrStr, pageIndex int, pageLimit int) (list []*MeituTu, p util.PageStrut) {
    offset := util.GetPageOffset(pageIndex, pageLimit)

    sceneMap := elastic.NewMatchPhraseQuery("title", scene_word)
    query := elastic.NewBoolQuery().Must(sceneMap)

    // 搜索词
    if input["words"] != "" {
        searchMap := elastic.NewMatchQuery("title", input["words"]).Operator("OR").MinimumShouldMatch("40%")
        query.Must(searchMap)
    }

    // 家装美图分类搜索
    if input["cate_field"] != "" && input["cate_id"] != "" && input["cate_id"] != "0" {
        searchMap := elastic.NewTermQuery(input["cate_field"], input["cate_id"])
        query.Filter(searchMap)
    }

    //ret,_:= query.Source()
    //jret,_ := json.Marshal(ret)
    //fmt.Println(string(jret))

    cc := client.Search().Index("qz_tu_home").MinScore(1).Query(query).
        From(offset).Size(pageLimit).Sort("_score",false)

    count := 0
    res,err := cc.Do(context.Background())
    if err == nil {
        count = int(res.Hits.TotalHits.Value)
        for _,v := range res.Hits.Hits {
            sub := new(MeituTu)
            data,_ := gjson.Encode(v.Source)
            _ = gjson.DecodeTo(data, sub)
            list = append(list, sub)
        }

        factory.Create().CreateCollector("Search").ReqAdd("elastic","MeituHomeSearch")
    } else {
        factory.Create().CreateCollector("Error").ReqAdd("elastic","MeituHomeSearch")
    }

    p = util.GetPage(pageIndex, pageLimit, count)
    return list,p
}

// 公装美图搜索
func (c ElasticLogic) MeituPubSearch(scene_word string, input g.MapStrStr, pageIndex int, pageLimit int) (list []*MeituTu, p util.PageStrut) {
    offset := util.GetPageOffset(pageIndex, pageLimit)

    query := elastic.NewBoolQuery()
    if scene_word != "" {
        sceneMap := elastic.NewMatchPhraseQuery("title", scene_word)
        query.Must(sceneMap)
    }

    // 搜索词
    if input["words"] != "" {
        searchMap := elastic.NewMatchQuery("title", input["words"]).Operator("OR").MinimumShouldMatch("40%")
        query.Must(searchMap)
    }

    // 公装美图分类搜索
    if input["cate_id"] != "" && input["cate_id"] != "0" {
        searchMap := elastic.NewTermQuery("pub_category", input["cate_id"])
        query.Filter(searchMap)
    }

    cc := client.Search().Index("qz_tu_pub").MinScore(1).Query(query).
        From(offset).Size(pageLimit).Sort("_score",false)

    count := 0
    res,err := cc.Do(context.Background())
    if err == nil {
        count = int(res.Hits.TotalHits.Value)
        for _,v := range res.Hits.Hits {
            sub := new(MeituTu)
            data,_ := gjson.Encode(v.Source)
            _ = gjson.DecodeTo(data, sub)
            list = append(list, sub)
        }

        factory.Create().CreateCollector("Search").ReqAdd("elastic","MeituPubSearch")
    } else {
        factory.Create().CreateCollector("Error").ReqAdd("elastic","MeituPubSearch")
    }

    p = util.GetPage(pageIndex, pageLimit, count)
    return list,p
}


// 案例图搜索
func (c ElasticLogic) MeituCasesSearch(scene_word string, input g.MapStrStr, pageIndex int, pageLimit int) (list []*MeituCases, p util.PageStrut){
    offset := util.GetPageOffset(pageIndex, pageLimit)

    query := elastic.NewBoolQuery()
    if scene_word != "" {
        sceneMap := elastic.NewMatchQuery("subtags", scene_word)
        query.Must(sceneMap)
    }

    // 搜索词
    if input["words"] != "" {
        searchMap := elastic.NewMatchQuery("title", input["words"]).Operator("OR").MinimumShouldMatch("40%")
        query.Must(searchMap)
    }

    // 城市筛选
    if input["cs"] != "" && input["cs"] != "0" {
        searchMap := elastic.NewTermQuery("cs", input["cs"])
        query.Filter(searchMap)
    }

    // 类型
    if input["classid"] != "" && input["classid"] != "0" {
        searchMap := elastic.NewTermQuery("classid", input["classid"])
        query.Filter(searchMap)
    }

    // 户型
    if input["huxing"] != "" && input["huxing"] != "0" {
        searchMap := elastic.NewTermQuery("huxing", input["huxing"])
        query.Filter(searchMap)
    }

    // 类型
    if input["leixing"] != "" && input["leixing"] != "0" {
        searchMap := elastic.NewTermQuery("leixing", input["leixing"])
        query.Filter(searchMap)
    }

    // 风格
    if input["fengge"] != "" && input["fengge"] != "0" {
        searchMap := elastic.NewTermQuery("fengge", input["fengge"])
        query.Filter(searchMap)
    }

    // 造价
    if input["zaojia"] != "" && input["zaojia"] != "0" {
        searchMap := elastic.NewTermQuery("zaojia", input["zaojia"])
        query.Filter(searchMap)
    }

    // 面积区间
    mianjiMinRet := input["mianji_min"] != "" && input["mianji_min"] != "0"
    mianjiMaxRet := input["mianji_max"] != "" && input["mianji_max"] != "0"
    if mianjiMinRet || mianjiMaxRet {
        mianjiQuery := elastic.NewRangeQuery("mianji_val")

        if mianjiMinRet {
            mianjiQuery.Gte(input["mianji_min"])
        }

        if mianjiMaxRet {
            mianjiQuery.Lte(input["mianji_max"])
        }

        query.Filter(mianjiQuery)
    }

    //res,_:=query.Source()
    //jret,_ := json.Marshal(res)
    //fmt.Println(string(jret))

    cc := client.Search().Index("qz_cases").Query(query).
        From(offset).Size(pageLimit).Sort("_score",false)

    count := 0
    ret,err := cc.Do(context.Background())
    if err == nil {
        count = int(ret.Hits.TotalHits.Value)
        for _,v := range ret.Hits.Hits {
            sub := new(MeituCases)
            data,_ := gjson.Encode(v.Source)
            _ = gjson.DecodeTo(data, sub)
            list = append(list, sub)
        }

        factory.Create().CreateCollector("Search").ReqAdd("elastic","MeituCasesSearch")
    } else {
        factory.Create().CreateCollector("Error").ReqAdd("elastic","MeituCasesSearch")
    }

    p = util.GetPage(pageIndex, pageLimit, count)
    return list,p
}

/**
 *场景内容搜索
 */
func (c ElasticLogic)SceneGraphicSearch(sence string,words string,index []string,pageIndex int,pageCount int)(list []*Colligate, p util.PageStrut )  {
    offset := util.GetPageOffset(pageIndex, pageCount)
    q := elastic.NewMatchPhraseQuery("title",sence)
    q_exists := elastic.NewExistsQuery("first_short_name")
    q_bool := elastic.NewBoolQuery().Must(q).MustNot(q_exists)
    q_filter := elastic.NewTermsQuery("first_short_name", "lc","xcdg","fg","jubu","fs","jjsh","news")
    q1_bool := elastic.NewBoolQuery().Must(q).Filter(q_filter)

    if words != "" {
        q_w := elastic.NewMatchQuery("title",words).Operator("OR")
        q_bool.Must(q_w)
        q1_bool.Must(q_w)
    }

    q_all := elastic.NewBoolQuery().Should(q_bool).Should(q1_bool)
    cc:= client.Search().Query(q_all).Size(pageCount).From(offset).Sort("_score",false)
    for _,v := range index  {
        cc.Index(v)
    }
    res,error := cc.Do(context.Background())

    if error == nil {
        for _,v := range res.Hits.Hits {
            sub := new(Colligate)
            data,_ := gjson.Encode(v.Source)
            _ = gjson.DecodeTo(data, sub)
            list = append(list, sub)
        }
        p = util.GetPage(pageIndex, pageCount, int(res.Hits.TotalHits.Value))
        factory.Create().CreateCollector("Search").ReqAdd("elastic","SceneGraphicSearch")
        return list,p
    } else {
        fmt.Println(error.Error())
        //添加错误+1
        factory.Create().CreateCollector("Error").ReqAdd("elastic","SceneGraphicSearch")
    }
    return list,p
}

/**
   words 【搜索词】
   index  【索引】
 */
func (c ElasticLogic)SceneContentSearch(words string,index []string,pageIndex int,pageCount int)(list []*Colligate, p util.PageStrut ) {
    offset := util.GetPageOffset(pageIndex, pageCount)
    q := elastic.NewMatchQuery("title",words).Operator("OR")
    //q_exists := elastic.NewExistsQuery("first_short_name")
    //q_bool := elastic.NewBoolQuery().Must(q).MustNot(q_exists)
    //q_filter := elastic.NewTermsQuery("first_short_name", "lc","xcdg","fg","jubu","fs","jjsh","news")
    //q1_bool := elastic.NewBoolQuery().Must(q).Filter(q_filter)

    q_all := elastic.NewBoolQuery().Should(q)
    cc:= client.Search().Query(q_all).Size(pageCount).From(offset).Sort("_score",false)
    for _,v := range index  {
        cc.Index(v)
    }
    res,error := cc.Do(context.Background())

    if error == nil {
        for _,v := range res.Hits.Hits {
            sub := new(Colligate)
            data,_ := gjson.Encode(v.Source)
            _ = gjson.DecodeTo(data, sub)
            list = append(list, sub)
        }
        p = util.GetPage(pageIndex, pageCount, int(res.Hits.TotalHits.Value))
        factory.Create().CreateCollector("Search").ReqAdd("elastic","SceneContentSearch")
        return list,p
    } else {
        fmt.Println(error.Error())
        //添加错误+1
        factory.Create().CreateCollector("Error").ReqAdd("elastic","SceneContentSearch")
    }
    return list,p
}

/**
    装修攻略场景词搜索
 */
func (c ElasticLogic)SceneArticleSearch(sences []string,words string,pageIndex int,pageCount int) (list []*Colligate, p util.PageStrut){
    offset := util.GetPageOffset(pageIndex, pageCount)
    query := elastic.NewBoolQuery()

    for _,v := range sences  {
        sceneMap := elastic.NewMatchPhraseQuery("title", v)
        query.Should(sceneMap)
    }

    if words != "" {
        searchMap := elastic.NewMatchQuery("title",words).Operator("OR")
        query.Must(searchMap)
    }

    cc := client.Search().Index("qz_www_article").Query(query).
        Size(pageCount).From(offset).
        Sort("_score",false)

    res,err := cc.Do(context.Background())

    if err == nil {
        for _,v := range res.Hits.Hits {
            sub := new(Colligate)
            data,_ := gjson.Encode(v.Source)
            _ = gjson.DecodeTo(data, sub)
            list = append(list, sub)
        }
        p = util.GetPage(pageIndex, pageCount, int(res.Hits.TotalHits.Value))
        factory.Create().CreateCollector("Search").ReqAdd("elastic","SceneArticleSearch")
        return list,p
    } else {
        fmt.Println(err.Error())
        //添加错误+1
        factory.Create().CreateCollector("Error").ReqAdd("elastic","SceneArticleSearch")
    }
    return list,p
}

/**
  分类场景搜索
  words 【搜索词】
  index  【索引】
*/
func (c ElasticLogic)SceneContentSubSearch(module string,words string,index []string,pageIndex int,pageCount int)(list []*Article, p util.PageStrut ) {
    offset := util.GetPageOffset(pageIndex, pageCount)
    q := elastic.NewMatchQuery("title",words).Operator("OR")
    var q_filter *elastic.TermsQuery
    switch module {
        case "lc":
            q_filter = elastic.NewTermsQuery("first_short_name", "lc")
            break;
        case "xcdg":
            q_filter = elastic.NewTermsQuery("first_short_name", "xcdg")
            break;
        case "zhinan":
            q_filter = elastic.NewTermsQuery("first_short_name", "fg","jubu","fs","jjsh","news")
            break;
    }

    q_bool := elastic.NewBoolQuery().Must(q).Filter(q_filter)

    q_all := elastic.NewBoolQuery().Must(q_bool)
    cc:= client.Search().Query(q_all).Size(pageCount).From(offset).Sort("_score",false)
    for _,v := range index  {
        cc.Index(v)
    }
    res,error := cc.Do(context.Background())

    if error == nil {
        for _,v := range res.Hits.Hits {
            sub := new(Article)
            data,_ := gjson.Encode(v.Source)
            _ = gjson.DecodeTo(data, sub)
            list = append(list, sub)
        }
        p = util.GetPage(pageIndex, pageCount, int(res.Hits.TotalHits.Value))
        factory.Create().CreateCollector("Search").ReqAdd("elastic","SceneContentSubSearch")
        return list,p
    } else {
        factory.Create().CreateCollector("Error").ReqAdd("elastic","SceneContentSubSearch")
    }
    return list,p
}

