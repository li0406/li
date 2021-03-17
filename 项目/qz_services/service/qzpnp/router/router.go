package router

import (
	"fmt"
	"os"
	v1 "qzpnp/app/controller/v1"
	"qzpnp/library/prometheus/factory"
	"qzpnp/library/util"

	"github.com/gogf/gf/frame/g"
	"github.com/gogf/gf/net/ghttp"
)

// 统一路由注册.
func init() {
	s := g.Server()
	s.BindHandler("/", func(r *ghttp.Request) {
		json, err := util.EncodeResponseJson(0, "请求成功", nil)
		if err == nil {
			r.Response.Writeln(json)
		}
	})

	//v1版本
	pnp := new(v1.PnpController)
	s.Group("/v1").Bind([]ghttp.GroupItem{
		{"ALL", "*", HookHandler, ghttp.HOOK_BEFORE_SERVE},
		{"GET", "/getordertel", pnp, "GetTelphoneByOrderId"},  //获取电话绑定信息
		{"POST", "/bindordertel", pnp, "BindTelXByOrder"},     //根据订单绑定X号
		{"POST", "/bindtel", pnp, "BindTelXByTel"},            //根据电话号码绑定x号
		{"POST", "/unbindordertel", pnp, "UnBindTelXByOrder"}, //根据订单号解绑X号
		{"POST", "/unbindtel", pnp, "UnbindTelXByTel"},        //根据电话解绑X号
		{"POST", "/gettelxinfo", pnp, "GetTexInfo"},           //获取X号信息
		{"GET", "/voice/sync", pnp, "VoiceSync"},              //录音转存
	})

	//s.BindHandler("/test",TestHandler)
	//s.BindHandler("/metrics",MetricsHandler)

	// 运行状态
	s.BindHandler("/healthz", func(r *ghttp.Request) {
		msg := ""
		Hostname, _ := os.Hostname()
		msg += fmt.Sprintf("HostName: %s\n", Hostname)
		msg += fmt.Sprintf("RunStatus: %s\n", "running")
		r.Response.Writeln(msg)
	})
}

func HookHandler(r *ghttp.Request) {
	// 设置统一请求参数
	util.Request(r)
}

func MetricsHandler(r *ghttp.Request) {
	factory.Create().Show(r.Response.Writer, r.Request)
}

func TestHandler(r *ghttp.Request) {
	factory.Create().CreateCollector("Pnp").ReqAdd("customer_send", "1", "2", "3", "4")
}
