package util

import (
	"crypto/md5"
	"encoding/hex"
	"strings"
)

/**
md5加密
 */
func MD5(str string) string{
	h := md5.New()
	h.Write([]byte(str))
	return hex.EncodeToString(h.Sum(nil))
}

// 字符串是否在切片中
func StrInSlice(text string, list []string) bool {
	result := false
	for _,val := range list {
		val = strings.Trim(val, " ")
		text = strings.Trim(text, " ")
		if val == text {
			result = true
			break
		}
	}

	return result
}

//func StrParse(text interface{}) string {
//	ret := ""
//	if text != nil {
//		ret = gconv.String()
//	}
//
//	return ret
//}