<template>
  <div class="quote_succ">
    <div class="red"><img src="../../../assets/img/qzw/bgc_red.png" alt=""></div>
    <div class="result_box">
      <div class="imgs"><img src="../../../assets/img/qzw/bgc_zou.png" alt=""></div>
      <p class="succe">预约成功</p>
      <div class="texts">小齐感谢您的耐心填写，稍后齐装家居顾问将会与您联系，请注意接听来电～</div>
    </div>
    <div class="contnei">
      <div class="con_item">
        <div class="cons">
          <p class="tit">价比4家</p>
          <p class="tit_span">4家设计机构提供方案，供你对比挑选</p>
        </div>
        <div class="succ_img"><img src="../../../assets/img/qzw/bgc_one.png" alt=""></div>
      </div>
      <div class="con_item">
        <div class="succ_img"><img src="../../../assets/img/qzw/bgc_two.png" alt=""></div>
        <div class="cons">
          <p class="tit">简单快速</p>
          <p class="tit_span">您完善简单信息 服务交给我们</p>
        </div>
      </div>
      <div class="con_item">
        <div class="cons">
          <p class="tit">报价清晰</p>
          <p class="tit_span">项目、人工、主材、一清二楚</p>
        </div>
        <div class="succ_img"><img src="../../../assets/img/qzw/bgc_three.png" alt=""></div>
      </div>
      <div class="con_item">
        <div class="succ_img"><img src="../../../assets/img/qzw/bgc_first.png" alt=""></div>
        <div class="cons">
          <p class="tit">靠谱大数据</p>
          <p class="tit_span">基于500座城市 施工报价数据评估</p>
        </div>
      </div>
    </div>
  </div>
</template>
<script>;
export default {
  name: 'quote_succ',
  data () {
    return {

    }
  },
  components: {
  },
  mounted() {

  },
  methods: {

  }

}
</script>
<style scoped>
.quote_succ {
  width: 100%;
  background-color: #F2F3F7;
}
.red {
  width: 100%;
  height: 0.301rem;
}
img {
  width: 100%;
  height: 100%；
}
.result_box {
  width: 2.731rem;
  margin-top: -1.067rem;
  position: relative;
  background-color: #fff;
  margin: 0 auto;
  border-radius: 0.034rem;
  padding: 0.213rem 0.307rem 0.205rem;
  box-sizing: border-box;
  text-align: center;

}
.imgs {
  width: 1.024rem;
  height: 0.853rem;
  margin: 0 auto;

}
.texts {
  font-size: 0.119rem;
  color: #333;
  font-weight: 400;
  margin-top: 0.137rem;
}
.succe {
  font-size: 0.179rem;
  font-weight: bold;
  color: #333;
}
.contnei {
  width: 2.731rem;
  border-radius: 0.034rem;
  padding:0.213rem 0.188rem;
  box-sizing: border-box;
  background-color: #fff;
  margin: 0.085rem auto;
}
.con_item {
  width: 100%;
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.171rem;
}
.tit {
  font-size: 0.137rem;
  color: #333333;
  font-weight: bold;
  margin-bottom: 0.088rem;
  margin-top: 0.088rem;
}
.tit_span {
  font-size: 0.119rem;
  font-weight: 500;
  color: #666;
}
.succ_img {
  width: 1.028rem;
  height: 0.768rem;
}
.succ_img img{
  width: 100%;
  height: 100%;
}
.cons {
  width: 1.028rem;
  height: 0.768rem;
}
</style>
