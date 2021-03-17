package util

import (
	"crypto/md5"
	"encoding/hex"
	"sort"
)

/**
md5加密
 */
func MD5(str string) string{
	h := md5.New()
	h.Write([]byte(str))
	return hex.EncodeToString(h.Sum(nil))
}

func Ksort(m map[string] string)  map[string] string {
	var keys []string
	for k:= range m {
	   keys = append(keys,k)
	}
	sort.Strings(keys)
	return m
}
