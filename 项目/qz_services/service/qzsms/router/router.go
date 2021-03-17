package router

import (
	"github.com/gogf/gf/g"
	"github.com/gogf/gf/g/net/ghttp"
	v1 "qzsms/app/controller/v1"
	"qzsms/library/prometheus/factory"
	"qzsms/library/util"
)

// 统一路由注册.
func init() {
	s := g.Server()
	s.BindHandler("/",func(r *ghttp.Request){
		json,err := util.EncodeResponseJson(0,"请求成功",nil)
		if err == nil {
			r.Response.Writeln(json)
		}
	})

	//v1版本
	yunrongt := new(v1.YunrongtController)
	yuntongxun := new(v1.YuntongxunController)
	sms := new(v1.SmsController)
	chuanglan := new(v1.ChuanglanController)
	s.Group("/v1").Bind([]ghttp.GroupItem{
		{"ALL", "*", HookHandler, ghttp.HOOK_BEFORE_SERVE},
		//短信发送
		{"POST",  "/yrt/sendsms",yunrongt,"SendSms"},//云容正通短信路由 行业
		{"POST",  "/yrt/sendsmsyx",yunrongt,"SendSmsYx"},//云容正通短信路由 营销
		{"POST",  "/ytx/sendsms",yuntongxun,"SendSms"},//容联云通讯短信路由
		{"POST",  "/sendsms",sms,"SendSms"},//业务短信发送路由
		{"POST",  "/chuanglan/sendsms",chuanglan,"SendSms"}, //创蓝短信 行业
		{"POST",  "/chuanglan/sendsmsyx",chuanglan,"SendSmsYx"},//创蓝短信营销
	})
	s.BindHandler("/test",TestHandler)
	s.BindHandler("/metrics",MetricsHandler)
}


func HookHandler(r *ghttp.Request) {
	// 设置统一请求参数
	util.Request(r)
}

func MetricsHandler(r *ghttp.Request)  {
	factory.Create().Show(r.Response.Writer,r.Request)
}

func TestHandler(r *ghttp.Request)  {
	factory.Create().CreateCollector("Sms").ReqAdd("customer_send","1","2","3","4")
}