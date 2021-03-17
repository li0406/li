<template>
  <section>
    <div class="bj-banner">
      <img src="../../assets/img/baojia/banner.jpg">
    </div>
    <img src="../../assets/img/baojia/baojia-item5.jpg" class="success-ok-img">
    <div class="baojia-list">
      <ul id="accordion" class="accordion">
        <li class="baojia-item">
          <div class="baojia-text">客厅总价</div>
          <div class="baojia-money">
            <span>{{result.detail.child.kt}}</span>
            <i>元</i>
          </div>
        </li>
        <li class="baojia-item">
          <div class="baojia-text">卧室总价</div>
          <div class="baojia-money">
            <span>{{result.detail.child.zw}}</span>
            <i>元</i>
          </div>
        </li>
        <li class="baojia-item">
          <div class="baojia-text">卫生间总价</div>
          <div class="baojia-money">
            <span>{{result.detail.child.wsj}}</span>
            <i>元</i>
          </div>
        </li>
        <li class="baojia-item">
          <div class="baojia-text">厨房总价</div>
          <div class="baojia-money">
            <span>{{result.detail.child.cf}}</span>
            <i>元</i>
          </div>
        </li>
        <li class="baojia-item">
          <div class="baojia-text">水电总价</div>
          <div class="baojia-money">
            <span>{{result.detail.child.sd}}</span>
            <i>元</i>
          </div>
        </li>
        <li class="baojia-item">
          <div class="baojia-text">其他总价</div>
          <div class="baojia-money">
            <span>{{result.detail.child.other}}</span>
            <i>元</i>
          </div>
        </li>
        <li class>
          <div class="baojia-text">半包价</div>
          <div class="baojia-money">
            <span>{{result.detail.total}}</span>
            <i>元</i>
          </div>
        </li>
      </ul>
      <p class="disclaimer">*该报价为半包估算价，实际以上门量房实测为准</p>
      <div class="success-footer">
        <p class="success-tip">[齐装网] 给小主请安！小齐已经接到您的装修圣旨，将会第一时间与您联系，请您注意接听哦。</p>
        <div class="success-footer-img">
          <div class="detail-text">
            <p>随进了解装修进程</p>
            <p>美女设计师1对1对答</p>
            <p>“2房”变“3房”秘决</p>
            <p>实时装修报价动态</p>
            <p>装修案例抢先看</p>
          </div>
          <img src="../../assets/img/baojia/baojia-item6.jpg">
        </div>
        <p class="gongzhonghao-text">微信公众号：
          <span class="red-color copy-text">{{result.weixin_name}}</span>
        </p>
        <p class="guanzhu-text">关注方法：长按复制，在微信搜索框中搜索，点击关注即可</p>
      </div>
      <div class="recount">
        <a href="/baojia">重新计算</a>
      </div>
    </div>
  </section>
</template>
<script>
import {baojiadone} from '@/api/api'
export default {
  name: 'baojiaDone',
  data () {
    return {
      result: {
        detail: {
          child: '',
          total: ''
        }
      }
    }
  },
  mounted () {
    let that = this
    if (this.$route.query.orderid) {
      baojiadone(this.$route.query.orderid).then((res) => {
        if (res.data.error_code === 0) {
          that.result = res.data.data
        }
      })
    }
  }
}
</script>
<style scoped>
.bj-banner img {
  width: 100%;
  vertical-align: top;
}
.out-box {
  margin: 0px 15px;
}
.success-ok-img {
  width: 100%;
  height: auto;
}
.accordion {
  margin: 0 12.5px;
  background: #f8f8f8;
  padding-top: 15px;
  padding-bottom: 15px;
  margin-bottom: 20px;
}
.success-footer {
  margin: 0 12.5px;
  background: #f8f8f8;
  overflow: hidden;
  margin-bottom: 17.5px;
  padding-top: 17.5px;
  padding-bottom: 12.5px;
}
.success-tip {
  font-size: 13px;
  margin: 0 3%;
  color: #666;
  text-align: justify;
  margin-bottom: 25px;
}
.success-footer-img {
  width: 100%;
  position: relative;
}
.detail-text {
  position: absolute;
  position: absolute;
  font-size: 0.1rem;
  left: 1.8rem;
  top: 0.138rem;
  color: #666;
  line-height: 0.15rem;
}
.success-footer-img img {
  width: 100%;
  height: auto;
}
.gongzhonghao-text {
  text-align: center;
  font-size: 16px;
  line-height: 16px;
  margin: 17px auto;
}
.guanzhu-text {
  font-size: 13px;
  line-height: 15px;
  color: #666;
  margin: 0 3%;
  text-align: justify;
}
.copy-text {
  font-weight: bold;
}
.red-color {
  color: #ff5353;
}
.recount {
  margin: 0 12.5px;
  margin-bottom: 20px;
}
.m-bj-edit li .m-bj-edit-list {
  color: #333 !important;
}
/*报价结果页*/
.baojia-list ul li {
  height: 2.9em;
  line-height: 2.9em;
  font-size: 0.95em;
  overflow: hidden;
  margin: 0 3%;
}
.baojia-list ul li:last-child {
  font-size: 1.3em;
}
.baojia-list ul li:last-child {
  border-top: 1px dashed #facdcd;
}
.baojia-list ul li div:first-child {
  float: left;
}
.baojia-list ul li div.baojia-money {
  float: right;
  color: #ff5659;
}
.baojia-list ul li div.baojia-money span {
  color: #ff5659;
  font-weight: bold;
}
.baojia-list ul li div.baojia-money i {
  margin-left: 0.5em;
  color: #000;
}
.recount a {
  width: 100%;
  height: 2.8em;
  display: block;
  line-height: 2.8em;
  text-align: center;
  background: #ff5659;
  color: #fff;
  font-size: 1.1em;
}
.baojia-list .disclaimer {
  margin: 0 3% 0.1rem 3%;
  font-size: 13px;
  color: #666;
}
/*城市*/
.chengshi-box {
  width: 100%;
  height: 100%;
  position: fixed;
  top: 0;
  right: 0;
  background: rgba(0, 0, 0, 0.6);
}
#box {
  width: 80%;
  height: 100%;
  background: #fff;
  float: right;
  position: relative;
}
.baojia-list ul li.baojia-item.active {
  height: auto;
  border-bottom: 0;
}
.baojia-list ul li.baojia-item .baojia-money span {
  color: #ff5353;
  font-weight: bold;
}
.baojia-item table {
  width: 100%;
  border: 0;
  border-top: 1px solid #ccc;
  display: none;
}
.baojia-item table tr td {
  border: 0;
  border-bottom: 1px solid #ccc;
  border-right: 1px solid #ccc;
  text-align: center;
  vertical-align: middle;
}
.baojia-item table tr td:last-child {
  border-left: 0;
}
</style>
