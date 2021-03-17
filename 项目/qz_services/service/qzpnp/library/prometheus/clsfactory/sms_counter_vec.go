package clsfactory

import (
    "github.com/prometheus/client_golang/prometheus"
    "qzpnp/library/prometheus/factory"
)

type smsCollector struct {
    reqDesc *prometheus.CounterVec
    respsizeDesc  *prometheus.Desc
    respsizevalue int64
}

func init()  {
    t :=  NewSmsCollect()
    factory.Create().Register("Sms",t)
}

func NewSmsCollect() *smsCollector {
    opts := prometheus.CounterOpts{Namespace: "qzpnp", Subsystem: "http", Name: "sms_send_count", Help: "sms_send_count"}
    return &smsCollector{
        reqDesc: prometheus.NewCounterVec(opts, []string{"action","module","type","gateway","template_id"}),
        respsizeDesc: prometheus.NewDesc("qzpnp_http_respsize_count", "http_respsize_count", nil, nil),
    }
}

func (s *smsCollector) Describe(ch chan<- *prometheus.Desc) {
    ch <- s.respsizeDesc
    s.reqDesc.Describe(ch)
}

func (s *smsCollector) Collect(ch chan<- prometheus.Metric) {
    ch <- prometheus.MustNewConstMetric(s.respsizeDesc, prometheus.CounterValue, float64(s.respsizevalue))
    s.reqDesc.Collect(ch)
}

func (s *smsCollector) ReqAdd(args ...string) {
    s.reqDesc.WithLabelValues(args ...).Inc()
}





