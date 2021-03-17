package clsfactory

import (
    "github.com/prometheus/client_golang/prometheus"
    "search/library/prometheus/factory"
)

type testCollector struct {
   reqDesc *prometheus.CounterVec
   respsizeDesc  *prometheus.Desc
   respsizevalue int64
}

func init()  {
    t :=  CreateTestCollector()
    factory.Create().Register("Error",t)
}

func CreateTestCollector() *testCollector{
    opts := prometheus.CounterOpts{Namespace: "Error", Subsystem: "http", Name: "count", Help: "count"}
    return &testCollector{
        reqDesc: prometheus.NewCounterVec(opts,[]string{"module","action"}),
        respsizeDesc: prometheus.NewDesc("error_http_respsize_count", "error_respsize_count", nil, nil),
    }
}

func (s *testCollector) Describe(ch chan<- *prometheus.Desc) {
  ch <- s.respsizeDesc
  s.reqDesc.Describe(ch)
}

func (s *testCollector) Collect(ch chan<- prometheus.Metric) {
  ch <- prometheus.MustNewConstMetric(s.respsizeDesc, prometheus.CounterValue, float64(s.respsizevalue))
  s.reqDesc.Collect(ch)
}

func (s *testCollector) ReqAdd(module string,action string,args ...string) {
    s.reqDesc.WithLabelValues(module,action).Inc()
}