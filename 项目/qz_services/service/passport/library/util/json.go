package util

import (
	"github.com/gogf/gf/net/ghttp"
	"log"
)

type JsonResponse struct {
	Error_Code int         `json:"error_code"`
	Error_Msg  string      `json:"error_msg"`
	Data       interface{} `json:"data"`
}

// 标准返回结果数据结构封装。
// 返回固定数据结构的JSON:
// err:  错误码(0:成功, 1:失败, >1:错误码);
// msg:  请求结果信息;
// data: 请求结果,根据不同接口返回结果的数据结构不同;
func Json(r *ghttp.Request, code int, msg string, data interface{}) {
	j := JsonResponse{}
	j.Error_Code = code
	j.Error_Msg = msg
	j.Data = data

	err := r.Response.WriteJson(j)
	if err != nil {
		log.Fatal("格式化json失败:", err)
	}
	r.Exit()
}
