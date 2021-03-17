package util

import (
	"crypto/md5"
	"encoding/hex"
)

/**
md5加密
 */
func MD5(str string) string{
	h := md5.New()
	h.Write([]byte(str))
	return hex.EncodeToString(h.Sum(nil))
}

// 检查值是否在切片中
func InSlice(word string, list []string) (ret bool) {
	for _,item := range list {
		if item == word {
			ret = true
			break
		}
	}

	return ret
}

// 切片去重
func SliceUnique(list []string) (newList []string) {
	for _,item := range list {
		if isExist := InSlice(item, newList); isExist == false {
			newList = append(newList, item)
		}
	}

	return newList
}