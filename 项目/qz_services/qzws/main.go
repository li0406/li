package main

import (
	"github.com/gogf/gf/g"
	_ "qzws/boot"
	_ "qzws/router"
)

func main() {
	g.Server().Run()
}