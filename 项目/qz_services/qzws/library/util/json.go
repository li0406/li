package util

import (
	"github.com/gogf/gf/g/encoding/gjson"
)

var jsonStruct struct{
	Error_code int `json:"error_code"`
	Error_msg string  `json:"error_msg"`
	Data interface{}  `json:"data"`
}

/**
 * 返回相应的json数据
 */
func EncodeResponseJson(error_code int,error_msg string ,data interface{}) ([]byte ,error) {
	obj := jsonStruct
	obj.Error_code = error_code
	obj.Error_msg = error_msg
	obj.Data = data
	res,err := gjson.Encode(obj)
	return res,err
}

func DecodeJson(data []byte)(*gjson.Json){
	r,_ := gjson.DecodeToJson(data)
	return r
}
