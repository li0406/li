package clsfactory

import (
    "github.com/prometheus/client_golang/prometheus"
    "search/library/prometheus/factory"
)

type rquesetCollector struct {
    reqDesc *prometheus.CounterVec
    respsizeDesc  *prometheus.Desc
    respsizevalue int64
}

func init()  {
    t :=  CreateSearchRquestCollector()
    factory.Create().Register("Search",t)
}

func CreateSearchRquestCollector() *rquesetCollector {
    opts := prometheus.CounterOpts{Namespace: "search", Subsystem: "http", Name: "request_count", Help: "request_count"}
    return &rquesetCollector{
        reqDesc: prometheus.NewCounterVec(opts, []string{"module","action"}),
        respsizeDesc: prometheus.NewDesc("search_http_respsize_count", "http_respsize_count", nil, nil),
    }
}

func (s *rquesetCollector) ReqAdd(module string,action string,args ...string) {
    s.reqDesc.WithLabelValues(module,action).Inc()
}


func (s *rquesetCollector) Describe(ch chan<- *prometheus.Desc) {
    ch <- s.respsizeDesc
    s.reqDesc.Describe(ch)
}

func (s *rquesetCollector) Collect(ch chan<- prometheus.Metric) {
    ch <- prometheus.MustNewConstMetric(s.respsizeDesc, prometheus.CounterValue, float64(s.respsizevalue))
    s.reqDesc.Collect(ch)
}


