package router

import (
    "admin-api/app/controller/common"
    msgv1 "admin-api/app/controller/msg/v1"
    pnp "admin-api/app/controller/pnp/v1"
    v1 "admin-api/app/controller/sms/v1"
    "admin-api/app/enum"
    "admin-api/app/model"
    "admin-api/library/jwt"
    "admin-api/library/util"
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/net/ghttp"
    "github.com/gogf/gf/g/util/gconv"
    "time"
)

func init() {
    s := g.Server()

    s.BindHandler("/", func(r *ghttp.Request) {
        json, err := util.EncodeResponseJson(0, "请求成功", nil)
        if err == nil {
            r.Response.Writeln(json)
        }
    })

    /**
     * 公共部分路由
     */
    //获取系统菜单
    menu := new(v1.MenuController)
    send := new(v1.SendSmsController)

    // 三方通道网关设置
    gatewayController := new(v1.GatewayController)
    appController := new(v1.AppController)
    signController := new(v1.SignController) // 短信签名设置
    tempController := new(v1.TemplateController) // 短信模板设置
    smslogsendController := new(v1.SmsLogSendController) //数据统计
    //短信路由
    s.Group("/sms").Bind([]ghttp.GroupItem{
        {"ALL", "*", CORSHookHandler, ghttp.HOOK_BEFORE_SERVE},
        {"GET", "/getsmsmenu", menu, "GetSmsMenu"},

        // 签名管理
        {"GET", "/v1/sign/list", signController, "List"},        // 获取签名列表
        {"POST", "/v1/sign/enabled", signController, "Enabled"}, // 签名是否启用设置
        {"POST", "/v1/sign/save", signController, "Save"},       // 签名编辑

        // 短信发送模板管理
        {"GET", "/v1/temp/options", tempController, "Options"},     // 获取短信模板列表筛选项
        {"GET", "/v1/temp/list", tempController, "List"},           // 获取短信模板列表
        {"GET", "/v1/temp/export", tempController, "Export"},       // 短信模板导出
        {"POST", "/v1/temp/enabled", tempController, "Enabled"},    // 短信模板是否启用设置
        {"POST", "/v1/temp/save", tempController, "Save"},          // 编辑短信模板
        {"GET", "/v1/temp/getinfo", tempController, "GetEditInfo"}, // 获取短信模板编辑信息
        {"GET", "/v1/temp/export-excel", tempController, "ExportExcel"}, // 导出Excel（返回数据流）

        // 三方通道网关设置
        {"GET", "/v1/gateway/list", gatewayController, "List"},      //获取列表
        {"GET", "/v1/gateway/detail", gatewayController, "Detail"},  //获取详情
        {"POST", "/v1/gateway/enable", gatewayController, "Enable"}, //是否启用
        {"POST", "/v1/gateway/save", gatewayController, "Save"},     //保存

        // 项目应用接入
        {"GET", "/v1/app/list", appController, "List"},      //获取列表
        {"GET", "/v1/app/detail", appController, "Detail"},  //获取详情
        {"POST", "/v1/app/enable", appController, "Enable"}, //是否启用
        {"POST", "/v1/app/save", appController, "Save"},     //保存

        //短信发送
        {"POST",  "/v1/sendsms",send, "SendSms"},//在线发送
        {"GET",  "/v1/getsmstemplatelist",send, "GetSmsTemplateList"},//在线发送
        {"GET",  "/v1/getsignlist",signController, "GetSignList"},//在线发送

        //数据统计
        {"GET",  "/v1/getloglist",smslogsendController, "Getloglist"},//在线发送
    })


    msgTempController := new(msgv1.MsgTemplateController) // 消息模板设置
    msgTypeController := new(msgv1.MsgTypeController) // 消息类型
    msgSendLogController := new(msgv1.MsgSendLogController) // 消息日志
    msgAppSendController := new(msgv1.MsgAppSendController) // APP消息通知
    msgInAppController := new(msgv1.MsgInAppController) // APP内部消息

    s.Group("/msg").Bind([]ghttp.GroupItem{
        {"ALL", "*", CORSHookHandler, ghttp.HOOK_BEFORE_SERVE},

        // 消息内容管理
        {"GET", "/v1/template/list", msgTempController, "List"},        //获取消息模板列表
        {"GET", "/v1/template/detail", msgTempController, "Detail"},    //消息模板详情
        {"GET", "/v1/template/applist", msgTempController, "AppList"},    //应用项目列表
        {"POST", "/v1/template/save", msgTempController, "Save"},       //编辑消息模板信息
        {"POST", "/v1/template/enabled", msgTempController, "Enabled"}, //消息模板启用状态修改
        {"GET", "/v1/template/export", msgTempController, "Export"}, //消息模板导出

        // 消息类型管理
        {"GET", "/v1/type/list", msgTypeController, "TypeList"},      //获取消息类型列表
        {"POST", "/v1/type/save", msgTypeController, "SaveMsgType"},      //新增/编辑 消息类型状态
        {"POST", "/v1/type/change", msgTypeController, "ChangeMsgType"},      //更改消息类型状态
        {"GET", "/v1/type/get", msgTypeController, "GetMsgType"},      //获取消息类型详情
        {"GET", "/v1/type/all", msgTypeController, "GetAll"},      //获取消息类型

        //消息推送记录
        {"GET", "/v1/log/list", msgSendLogController, "MsgSendLogList"},      //获取消息日志列表

        //app消息推送
        {"GET", "/v1/app/list", msgAppSendController, "MsgAppList"},      //消息推送列表
        {"POST", "/v1/app/save", msgAppSendController, "MsgAppSave"},      //新增消息推送
        {"GET", "/v1/app/get", msgAppSendController, "GetMsgAppInfo"},      //消息推送详情
        {"POST", "/v1/app/del", msgAppSendController, "MsgAppDel"},      //删除消息推送
        {"GET", "/v1/app/export", msgAppSendController, "MsgAppExportExcel"},      //导出消息推送

        // APP站内消息
        {"GET", "/v1/msginapp/list", msgInAppController, "MsgInAppList"},      //APP站内消息列表
        {"POST", "/v1/msginapp/save", msgInAppController, "MsgInAppSave"},      //新增APP站内消息  GetMsgDetail
        {"GET", "/v1/msginapp/detail", msgInAppController, "GetMsgDetail"},    //APP站内消息 详情

    })

    //号码保护
    pnpController := new(pnp.PnpController)
    s.Group("/pnp").Bind([]ghttp.GroupItem{
        {"ALL", "*", CORSHookHandler, ghttp.HOOK_BEFORE_SERVE},
        {"POST", "/v1/providerup", pnpController, "SetProviderInfo"},      //设置供应商 新增/编辑
        {"GET", "/v1/providerup", pnpController, "GetProviderInfo"},      //获取供应商
        {"GET", "/v1/providerlist", pnpController, "GetProviderList"}, //供应商列表
        {"GET", "/v1/config", pnpController, "GetConfig"}, //配置文件
        {"GET", "/v1/providerdropdownlist", pnpController, "GetProviderDropDownlist"}, //供应商下拉列表
        {"POST", "/v1/configup", pnpController, "SetConfigUp"},//保存配置
        {"POST", "/v1/providerstatusup", pnpController, "SetProviderStatus"},//供应商状态编辑
        {"GET", "/v1/getpnptellist", pnpController, "GetPnpTelList"},//虚拟号码列表
        {"GET", "/v1/getpnpdropdowncity", pnpController, "GetPnpDropDownCity"},//虚拟号码城市下拉列表
        {"GET", "/v1/gettelhistory", pnpController, "GetTelHistory"},//虚拟号码城市下拉列表
        {"GET", "/v1/getvoicelist", pnpController, "GetVoiceList"},//虚拟号码城市下拉列表
    })

    // 公共模块
    commonController := new(common.CommonController)
    s.Group("/common").Bind([]ghttp.GroupItem{
        {"ALL", "*", CORSHookHandler, ghttp.HOOK_BEFORE_SERVE},
        {"POST", "/upload", commonController, "Upload"},      //新增/编辑 消息类型状态
        {"GET", "/city", commonController, "GetAllCity"},     //获取城市列表页
    })

    // 404
    s.BindHandler("/*any", func(r *ghttp.Request) {
       ret, err := util.EncodeResponseJson(404, "not found", nil)
       if err == nil {
           _ = r.Response.WriteJson(ret)
           r.ExitAll()
       }
    })
}

func CORSHookHandler(r *ghttp.Request) {
    // 设置统一请求参数
    util.Request(r)

    //设置跨域
    options := ghttp.CORSOptions{}
    options.AllowOrigin = "*"
    options.AllowMethods = "GET,PUT,POST,DELETE,PATCH,HEAD,CONNECT,OPTIONS,TRACE"
    options.AllowHeaders = "Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With,token"
    r.Response.CORS(options)

    //获取请求的token
    token := r.Request.Header.Get("token")
    res, err := jwt.ParseToken(token)
    if err {
        exp := gconv.Int64(res["exp"])
        unixTime := time.Now().Unix()
        if exp >= unixTime {
            data := res["data"].(map[string]interface{})
            model.AdminUser.Logo = gconv.String(data["logo"])
            model.AdminUser.NickName = gconv.String(data["nick_name"])
            model.AdminUser.RoleId = gconv.Int64(data["role_id"])
            model.AdminUser.RoleName = gconv.String(data["role_name"])
            model.AdminUser.UserId = gconv.Int64(data["user_id"])
            model.AdminUser.UserName = gconv.String(data["user_name"])
        } else {
            json, err := util.EncodeResponseJson(3000002, enum.CODE_3000002, nil)
            if err == nil {
                _ = r.Response.WriteJson(json)
                r.ExitAll()
            }
        }
    } else {
        json, err := util.EncodeResponseJson(3000001, enum.CODE_3000001, nil)
        if err == nil {
            _ = r.Response.WriteJson(json)
        }
        //全部退出
        r.ExitAll()
    }
}
