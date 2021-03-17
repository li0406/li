package util

import (
	"bytes"
	"encoding/json"
	"errors"
	"net/http"
	"time"
)

func CurlGet(url string,params map[string] string,headers map[string] string)(*http.Response,error){
	req, err := http.NewRequest("GET", url, nil)
	if err != nil {
		return nil,errors.New("new request is fail")
	}

	q := req.URL.Query()
	//请求参数
	if params != nil {
		 for key,val := range params{
		 	q.Add(key,val)
		 }
		req.URL.RawQuery = q.Encode()
	}
	//请求header参数
	if headers != nil {
		for key,val := range headers{
			req.Header.Add(key,val)
		}
	}
	//fmt.Println(req.URL.String())
	client := &http.Client{}
	//设置超时时间
	client.Timeout = 3 * time.Second
	res,err := client.Do(req)
	return res,err
}

func CurlPost(url string, body map[string] string, params map[string] string , headers map[string]string)(*http.Response,error) {
	var bodyJson []byte
	if body != nil {
		var err error
		bodyJson, err = json.Marshal(body)
		if err != nil {
			return nil, errors.New("http post body to json failed")
		}
	}

	req, err := http.NewRequest("POST", url, bytes.NewBuffer(bodyJson))
	if err != nil {
		return nil, errors.New("new request is fail: %v \n")
	}
	req.Header.Set("Content-type", "application/json")
	q := req.URL.Query()
	if params != nil {
		for key, val := range params {
			q.Add(key, val)
		}
		req.URL.RawQuery = q.Encode()
	}
	//add headers
	if headers != nil {
		for key, val := range headers {
			req.Header.Add(key, val)
		}
	}

	client := &http.Client{}
	client.Timeout = 3 * time.Second
	res,err := client.Do(req)
	return res,err
}
