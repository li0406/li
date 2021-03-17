package applet

import (
    "encoding/json"
    "fmt"
    "github.com/gogf/gf/g/database/gdb"
    "github.com/gogf/gf/g/util/gconv"
    "io/ioutil"
    "qzmsg/library/util"
)

type getHandler func(appid string) string
type setTokenHandler func(appid string, token string, expires_in int64)

type WeixinApi struct {
    appid string
    secret string
    getAccessTokenHandler getHandler
    setAccessTokenHandler setTokenHandler
}

// 实例化微信API
func NewWeixinApi(appid string, secret string, getCallback getHandler, setCallback setTokenHandler) *WeixinApi {
    return &WeixinApi{
        appid: appid,
        secret: secret,
        getAccessTokenHandler: getCallback,
        setAccessTokenHandler: setCallback,
    }
}

// 获取access_token
func (that *WeixinApi) GetAccessToken(reset bool) (string, error) {
    var err error
    var access_token string
    if reset == false {
        access_token = that.getAccessTokenHandler(that.appid)
    }

    if access_token == "" {
        link_param := fmt.Sprintf("grant_type=client_credential&appid=%s&secret=%s", that.appid, that.secret)
        url := fmt.Sprintf("https://api.weixin.qq.com/cgi-bin/token?%s", link_param)

        response,err := util.CurlGet(url, nil, nil)
        if err == nil {
            body,err := ioutil.ReadAll(response.Body)
            if err == nil {
                var result gdb.Map
                err = json.Unmarshal(body, &result)

                if err == nil && result["access_token"] != nil {
                    access_token = gconv.String(result["access_token"])
                    expires_in := gconv.Int64(result["expires_in"])

                    that.setAccessTokenHandler(that.appid, access_token, expires_in)
                }
            }
        }
    }

    return access_token,err
}

func (that *WeixinApi) SendUniformMessage() {
    access_token,err := that.GetAccessToken(false)
    if err == nil {
        fmt.Println(access_token)
    }
}

