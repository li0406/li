package middleware

import (
	"encoding/json"
	"github.com/gogf/gf/net/ghttp"
	"io/ioutil"
	"passport/app/enum"
	"passport/library/util"
	"strings"
)

//  过滤请求 中间件 : 将 application/json 格式的数据放到框架中
type RequestMiddleware struct {
}

func NewRequestMiddleware() *RequestMiddleware {
	return &RequestMiddleware{}
}

// 解析请求的application/json 数据
func (m *RequestMiddleware) Handle(r *ghttp.Request) {
	contentType := r.Request.Header.Get("Content-type")
	method := r.Request.Method
	if strings.Index(contentType, "application/json") > -1 {
		data, err := ioutil.ReadAll(r.Body)
		if err != nil {
			util.Json(r, 5000004, enum.CODE_5000004, nil)
		}
		var query interface{}
		decoder := json.NewDecoder(strings.NewReader(string(data)))
		if err := decoder.Decode(&query); err != nil {
			util.Json(r, 5000004, enum.CODE_5000004, nil)
		}
		queryMap, ok := query.(map[string]interface{})
		if !ok {
			util.Json(r, 5000004, enum.CODE_5000004, nil)
		}

		m.Push(r, method, queryMap)
	}
	r.Middleware.Next()
}

// 解析map数据，并传入到请求数据中
func (m *RequestMiddleware) Push(r *ghttp.Request, method string, data map[string]interface{}) {

	for key, value := range data {
		if method == "POST" {
			r.SetPost(key, value)
		} else {
			r.SetQuery(key, value)
		}
	}

}
