package router

import (
	"github.com/gogf/gf/frame/g"
	"github.com/gogf/gf/net/ghttp"
	v1 "passport/app/controller/v1"
	"passport/app/middleware"
	"passport/library/util"
)

// 统一路由注册.
func init() {
	s := g.Server()
	//v1版本
	//user := new(v1.UserController)
	authController := new(v1.AuthController)

	s.Group("v1", func(g *ghttp.RouterGroup) {
		g.Middleware(middleware.NewCORSMiddleware().Handle)
		g.Middleware(middleware.NewRequestMiddleware().Handle)

		g.POST("/auth/get", authController, "Get")
		g.POST("/auth/refresh", authController, "Refresh")
		g.POST("/auth/drop", authController, "Drop")
		g.POST("/auth/parse", authController, "Parse")

	})

	//404
	s.BindHandler("/*any", func(r *ghttp.Request) {
		util.Json(r, 404, "你想要的并不存在~", nil)
	})
}
