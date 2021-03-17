package model

import (
	"github.com/gogf/gf/database/gdb"
	"github.com/gogf/gf/frame/g"
)

type Model interface {
	link() (conn *gdb.Model)
	Get(condition g.Map) (g.Map, error)
	Has(condition g.Map) (bool, error)
	Save(data g.Map) (bool, error)
	Update(condition g.Map, data g.Map) (bool, error)
	Count(condition g.Map) (int, error)
}
