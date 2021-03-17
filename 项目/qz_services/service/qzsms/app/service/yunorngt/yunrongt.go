package yunorngt

import (
	"github.com/gogf/gf/g/encoding/gjson"
	"io/ioutil"
	"qzsms/app/model"
	"qzsms/library/util"
	"strconv"
)

var (
	config Yunrongt
)


type Yunrongt struct {
	Yrt_url string `json:"yrt_url"`
	Yrt_username string `json:"yrt_username"`
	Yrt_password string `json:"yrt_password"`
	Mobile string `json:"mobile"`
	Content string `json:"content"`
}

func init()  {
	m := model.NewSmsGatewayModel()
	res,err := m.GetGatewayInfo("yunrongt")

	if  err != nil || len(res) == 0{
		return
	}
	
	gjson.DecodeTo(res["config"],&config)
}

/**
	tel [电话]
	content [短息模版内容]
	return code [状态码] 大于 0 的为短信唯一ID  小于0 错误代码
			msg [返回信息]
 */
func SendSms(tel string,content string) (code string,msg string) {
	if tel == "" || content == ""{
		return "-1","缺失参数"
	}

	if config.Yrt_username == "" {
		return "-1","缺失帐号参数"
	}

	//读取配置
	url := config.Yrt_url
	var params = make(map[string] string)
	params["username"] = config.Yrt_username
	params["password"] = config.Yrt_password
	params["password"] = util.MD5(params["username"]+util.MD5(params["password"]))
	params["content"] = content
	params["mobile"] = tel
	result,err := util.CurlGet(url,params,nil)
	if err == nil {
		data, _ := ioutil.ReadAll(result.Body)
		code,_ := strconv.ParseInt(string(data),10,64)
		if code > 0 {
			return  strconv.FormatInt(code,10),"发送成功"
		}
		var msg string
		switch code {
		default:
			msg = "发送失败"
			case 0:
				msg = "失败，提交抛出异常"
				break
			case -1 :
				msg = "用户名或者密码不正确"
				break
			case -3:
				msg = "短信内容0个字节"
				break
			case -4:
				msg = "0个有效号码"
				break
			case -5:
				msg = "余额不够"
				break
			case -10:
				msg = "用户被禁用"
				break
			case -11:
				msg = "短信内容超过500字0"
				break
			case -13:
				msg = "IP校验错误"
				break
			case -14:
				msg = "内容解析异常"
				break
			case -24:
				msg = "手机号码超过限定个数"
				break
			case -25:
				msg = "没有提交权限"
				break
		}
		return strconv.FormatInt(code,10),msg
	}
	result.Body.Close()
	return "-1","发送失败"
}
