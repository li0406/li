package middleware

import "github.com/gogf/gf/net/ghttp"

//  cors请求中间件
type CORSMiddleware struct {
}

func NewCORSMiddleware() *CORSMiddleware {
	return &CORSMiddleware{}
}

func (m *CORSMiddleware) Handle(r *ghttp.Request) {
	options := ghttp.CORSOptions{
		AllowOrigin:      "*",
		AllowMethods:     ghttp.HTTP_METHODS,
		AllowCredentials: "true",
		AllowHeaders:     "Origin,Content-Type,Accept,User-Agent,Cookie,Authorization,X-Auth-Token,X-Requested-With,TOKEN",
		MaxAge:           3628800,
	}
	r.Response.CORS(options)
	r.Middleware.Next()
}
