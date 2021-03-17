package dongxin

import (
    "encoding/json"
    "fmt"
    "github.com/gogf/gf/encoding/gjson"
    "qzpnp/library/util"
    "sort"
    "strings"
)

type DongxinService struct {
    Send SendBindStruct
    Unbind UnBindStruct
    Request_url string //服务器地址
}

type SendBindStruct struct {
    Appkey string `json:"appkey"`
    Secretkey string `json:"secretkey"`
    Ts string `json:"ts"` //格式 yyyyMMddHHmmssSSS，时间 采用北京时间，24 小时制，精 确至毫秒，不能与当前标准时间 相差超过 5 分钟
    RequestId string `json:"request_id"` //业务 id 消息体的 MD5 摘要
    Tela string `json:"tela"`
    Telx string `json:"telx"`
    Subts string `json:"subts"` //绑定时间 M 格式为 yyyyMMddHHmmss。时间 采用北京时间，24 小时制。
    Name string `json:"name"` //姓名
    Cardtype string `json:"cardtype"`//证件类型
    Cardno string `json:"cardno"` //身份证号码
    Areacode string `json:"areacode"` //区号 北京（10）；在平台分配 X 号码模式中，平台从号码池中 分配该地区的 X 号码，避免产生 呼叫长途费。
    Expiration string  `json:"expiration"` // 过期时间 单位：秒， 自绑定时间开始后 expiration 秒自动解绑；0 表示 不限制时间，不会自动解绑
    Extra SendBindExtra
    Remark string
}

type SendBindExtra struct{
    Callrecording string `json:"callrecording"` //录音控制 默认是 0（不开 通录音功能）。 0：不录音 1：接通后录音 2：被叫响铃后录音
    Calldisplay string `json:"calldisplay"` //来显控制 。默认是 0（显示 真实号码）。 0：显示真实号码 1：显示 X 号码 2: 显示 Z 号码池中的号码
    Anucodecalled string `json:"anucodecalled"` //放音编码 其他号码拨打 X 时，给其他号码 的放音 不带，不放音
    Anucodecaller string `json:"anucodecaller"` //放音编码 其他号码拨打 X 时，给 A 的放音 不带，不放音
    Callunsub string `json:"callunsub"` //解绑推送消息控制 仅下列值有效。默认是 0。 0 解绑不推送消息 1 解绑推送消息
    Callpickup string `json:"callpickup"`//被叫接通推送事件 控制 0 可选。 仅下列值有效。默认是 0。 0 不推送被叫接通事件 1 推送被叫接通事件
    Ringpickup string `json:"ringpickup"` //被叫振铃推送事件 控制 0 可选。 仅下列值有效。默认是 0。 0 不推送被叫振铃事件 1 推送被叫振铃事件
    Callcontrol string `json:"callcontrol"` //被叫接通控制 0 可选。其他号码呼叫 X 时，对来 显进行鉴权，仅下列值有效。默 认是 0。 0 不对来话号码鉴权 1 对来话号码鉴权
    Callduration string `json:"callduration"` //通话持续时间 0 可选，单位秒，如果出现则通话 有效时长为此值，如果没有出现 按现在默认处理。
}

type UnBindStruct struct {
    Appkey string `json:"appkey"`
    Secretkey string `json:"secretkey"`
    Ts string `json:"ts"` //格式 yyyyMMddHHmmssSSS，时间 采用北京时间，24 小时制，精 确至毫秒，不能与当前标准时间 相差超过 5 分钟
    Subid string `json:"subid"`
}

type  Response struct {
    Code int
    Message string
    Data struct{
        Subid string
        TelX string
    }
}

func NewDongXin() *DongxinService{
    return &DongxinService{}
}

func (c *DongxinService)SendBind() ( int,string,error)  {
    //获取签名
    p := c.getBindParam()
    sign := c.getBindSign()
    url := c.Request_url + "/v2/ax/mode101";
    //请求头部
    var headers = make(map[string] string)
    headers["Accept"] = "application/json"
    headers["appkey"] = c.Send.Appkey
    headers["ts"] = c.Send.Ts
    headers["msgdgt"] = sign

    result,err := util.CurlPostByBodyJson(url,p,nil,headers)
    if err == nil {
        response := Response{}
        json.NewDecoder(result.Body).Decode(&response)
        if response.Code ==  0 {
            fmt.Println(response)
            return response.Code,response.Data.Subid,err
        } else {
            //绑定失败
            return response.Code,response.Message,err
        }
    }
    return  404,"",err
}

func (c *DongxinService)SendUnBind() (int,string ,error) {
    //获取签名
    sign := c.getUnBindSign()
    url :=  c.Request_url + "/v2/ax/"+c.Unbind.Subid;

    //请求头部
    var headers = make(map[string] string)
    headers["Accept"] = "application/json"
    headers["appkey"] = c.Unbind.Appkey
    headers["ts"] = c.Unbind.Ts
    headers["msgdgt"] = sign

    result,err := util.CurlDeleteByBodyJson(url,nil,nil,headers)
    if err == nil {
       response := Response{}
       json.NewDecoder(result.Body).Decode(&response)
       fmt.Println(response)
       return response.Code, response.Message,err
    }
    return  404, "",err
}

func (c *DongxinService)GetTexInfo(tel string)  {
    sign := c.getUnBindSign()
    url := c.Request_url + "/v2/ax/telX/"+tel
    //请求头部
    var headers = make(map[string] string)
    headers["Accept"] = "application/json"
    headers["appkey"] = c.Unbind.Appkey
    headers["ts"] = c.Unbind.Ts
    headers["msgdgt"] = sign

    result,err := util.CurlGet(url,nil,headers)
    if err == nil {
        response := Response{}
        json.NewDecoder(result.Body).Decode(&response)
        fmt.Println(response)
    }
}

func (c *DongxinService)getBindParam() []byte{
    send := c.Send
    extra := map[string] string{
       "callrecording": send.Extra.Callrecording,
    }
    json_s,_ := gjson.Encode(extra)

    m := map[string] string{
        "requestId" : send.RequestId,
        "telA" : send.Tela,
        "telX" : send.Telx,
        "subts" : send.Subts,
        "name" : send.Name,
        "cardtype" : send.Cardtype,
        "cardno" : send.Cardno,
        "areacode" : send.Areacode,
        "expiration" : send.Expiration,
        "remark":send.Remark,
        "extra" : string(json_s),
    }
    r,_ := gjson.Encode(m)
    return r
}

func (c *DongxinService)getBindSign() string {
    send := c.Send
    m := map[string] string{
       "appkey":send.Appkey,
       "ts" :send.Ts,
       "requestId" : send.RequestId,
       "telA" : send.Tela,
       "telX" : send.Telx,
       "subts" : send.Subts,
       "name" : send.Name,
       "cardtype" : send.Cardtype,
       "cardno" : send.Cardno,
       "areacode" : send.Areacode,
       "expiration" : send.Expiration,
       "callrecording" : send.Extra.Callrecording,
       "remark" : send.Remark,
    }

    var keys []string
    for k,_ := range m {
        keys = append(keys,k)
    }
    sort.Strings(keys)
    str := send.Secretkey
    for _,k := range keys {
        str += k + m[k]
    }

    str =  strings.ToUpper(util.MD5(str));
    return str
}

func (c *DongxinService)getUnBindSign() string {
    send := c.Unbind
    m := map[string] string{
        "appkey":send.Appkey,
        "ts" :send.Ts,
    }

    var keys []string
    for k,_ := range m {
        keys = append(keys,k)
    }
    sort.Strings(keys)
    str := send.Secretkey
    for _,k := range keys {
        str += k + m[k]
    }
    str =  strings.ToUpper(util.MD5(str));
    return str
}
