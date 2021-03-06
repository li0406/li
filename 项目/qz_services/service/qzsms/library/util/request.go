package util

import (
    "encoding/json"
    "github.com/gogf/gf/g/net/ghttp"
    "github.com/gogf/gf/g/util/gconv"
    "io/ioutil"
    "strings"
)

// 设置请求
func Request(r *ghttp.Request) {
    contentType := r.Request.Header.Get("Content-type")
    if strings.Index(contentType, "application/json") > -1 {
        body,_ := ioutil.ReadAll(r.Body)
        var queryMap map[string]interface{}
        err := json.Unmarshal(body, &queryMap)
        if err == nil {
            method := r.Request.Method

            var value string
            for key := range queryMap {
                value = gconv.String(queryMap[key])
                if method == "POST" {
                    r.AddPost(key, value)
                } else {
                    r.AddQuery(key, value)
                }
            }
        }
    }
}