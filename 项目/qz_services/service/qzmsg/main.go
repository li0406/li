package main

import (
	"github.com/gogf/gf/g"
	_ "qzmsg/boot"
	_ "qzmsg/router"
)

func main() {
	g.Server().Run()
}