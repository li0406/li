package factory

import (
    "bytes"
    "fmt"
    "github.com/prometheus/client_golang/prometheus"
    "github.com/prometheus/common/expfmt"
    "net/http"
)

type PrometheusClass interface {
    Describe(ch chan<- *prometheus.Desc)
    Collect(ch chan<- prometheus.Metric)
    ReqAdd(args ...string)
}

type Factory struct {
}

var (
    plist map[string] PrometheusClass
    registry  *prometheus.Registry
)

const (
    contentTypeHeader   = "Content-Type"
    contentLengthHeader = "Content-Length"
)

func init()  {
    plist = make(map[string] PrometheusClass)
    registry  =  prometheus.NewRegistry()
}

func Create() *Factory {
    return &Factory{}
}

func (s *Factory)Register(name string,plugin PrometheusClass)  {
    plist[name] = plugin
    registry.MustRegister(plugin)
}

func (s *Factory)CreateCollector(name string) PrometheusClass  {
    if f,ok := plist[name];ok {
        return f
    } else {
        return nil
    }
}

func (s *Factory)RegisterCollect()  {
   for _,val := range plist {
       registry.MustRegister(val)
   }
}

func (s *Factory)Show(w http.ResponseWriter,r *http.Request)  {
    entry,err := registry.Gather()
    if err != nil {
       fmt.Println("An error has occurred during metrics collection:\n\n"+err.Error())
       return
    }
    buf := bytes.NewBuffer(nil)
    contentType := expfmt.Negotiate(r.Header)
    enc := expfmt.NewEncoder(buf, contentType)

    for _, met := range entry {
       if err := enc.Encode(met); err != nil {
           fmt.Println("An error has occurred during metrics encoding:\n\n"+err.Error())
           return
       }
    }

    header := w.Header()
    header.Set(contentTypeHeader, string(contentType))
    header.Set(contentLengthHeader, fmt.Sprint(buf.Len()))

    w.Write(buf.Bytes())
}

