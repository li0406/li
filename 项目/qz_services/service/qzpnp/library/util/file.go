package util

import (
	"errors"
	"io/ioutil"
	"net/http"
	"path"
	"path/filepath"
	"strings"
	"time"
)

// IsExt 判断扩展名
func IsExt(f string, ext string) bool {
	return strings.HasSuffix(f, ext)
}

// FileName 从路径中获取文件名
func FileName(fp string) string {
	return filepath.Base(fp)
}

// FileExt 从路径中获取文件扩展名
func FileExt(fp string) string {
	return path.Ext(fp)
}

// GetNetFile 获取网络上的文件
func GetNetFileByte(url string, timeout int64) ([]byte, error) {

	req, err := http.NewRequest("GET", url, nil)
	if err != nil {
		return nil, errors.New("new request is fail: %v \n")
	}
	client := http.DefaultClient
	client.Timeout = time.Duration(timeout) * time.Second
	resp, err := client.Do(req)
	if err != nil {
		return nil, errors.New("http client is fail: %v \n")
	}
	raw := resp.Body
	defer raw.Close()
	rawByte, err := ioutil.ReadAll(raw)
	if err != nil {
		return nil, errors.New("read body byte is fail: %v \n")
	}
	return rawByte, nil
}
