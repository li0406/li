<template>
  <div class="sales-map">
    <div class="section3">
      <div class="ui_base u_p3d">
        <div class="ball_b">
          <img src="@/assets/imgs/map-bj.png" alt="">
        </div>
        <div class="ball_c">
          <img src="@/assets/imgs/center-bj.png" alt="">
          <NumberGrow class="num" :value="amount" />
          <span>元</span>
        </div>
        <div class="base u_p3d">
          <template v-for="(item, index) in listData">
            <div :key="item.name" class="ball_base u_p3d" :class="'ball_'+((index+1)*2-1)">
              <div class="ball">
                <img src="@/assets/imgs/item-bj.png" alt="">
                <p class="mt30">{{ item.name }}业绩进度</p>
                <p class="num">{{ item.percent }}%</p>
              </div>
            </div>
            <div :key="item.name+1" class="ball_base u_p3d" :class="'ball_'+(index+1)*2">
              <div class="ball">
                <img src="@/assets/imgs/item-bj.png" alt="">
                <p class="mt16">{{ item.name }}业绩</p>
                <p class="num">{{ item.value }}</p>
                <p>元</p>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SalesMap',
  components: {
    NumberGrow: () => import('@/components/numscroll/index')
  },
  props: {
    mapList: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      listData: [],
      amount: 0
    }
  },
  watch: {
    mapList: {
      handler(val) {
        this.amount = parseInt(val.amount)
        this.listData = val.data
      },
      immediate: true
    }
  }
}
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
.sales-map {
  position: relative;
  .section3 {
    width: 100%;
    height: 500px;
    margin: 0 auto;
    background: url(~@/assets/imgs/map-bj.png) 0 0 no-repeat;
    // background-size: 100% 100%;
    .u_p3d {
      transform-style: preserve-3d;
    }
    .ui_base {
      position: relative;
      top: 90px;
      left: 280px;
      width: 400px;
      height: 0px;
      perspective: 1000px;
      perspective-origin: 50% 0%;
    }
    .ball_c {
      transform-origin: 50% 50%;
      position: absolute;
      width: 150px;
      height: 150px;
      text-align: center;
      left: 60px;
      top: 120px;
      z-index: 3;
      img {
        position: absolute;
        left: 0;
        top: 0;
        width: 150px;
        height: 150px;
        z-index: -1;
        animation: cir_c 10s linear 0s infinite;
      }
      span{
        display: block;
        font-size: 16px;
        line-height: 26px;
        color: #01cdea;
      }
      .num{
        color: #01cdea;
        margin-top: 55px;
        font-size: 22px;
        font-weight: bold;
      }
    }

    .ball_b{
      transform-origin: 50% 50%;
      position: absolute;
      width: 347px;
      height: 160px;
      top: 40px;
      left: -38px;
      // background: url(~@/assets/imgs/map-bj.png) 242px -128px no-repeat;
      border-radius: 347px 347px 0 0;
      z-index: 2;
      overflow: hidden;
      img{
        position: absolute;
        left: -242px;
        top: -128px;
      }
    }
    .base {
      transform: rotateX(80deg) rotateY(-10deg);
      position: relative;
      width: 450px;
      height: 450px;
      backface-visibility: hidden;
      top: -120px;
      left: -100px;

      .ball_base {
        transform-origin: 295px 0px;
        position: absolute;
        top: 230px;
        left: -40px;
        width: 200px;
        height: 127px;
      }
    }

  }

  .section3 .ball_1 {
    transform: rotateX(-90deg) rotateY(0deg) translateY(0px);
  }

  .section3 .ball_2 {
    transform: rotateX(-90deg) rotateY(60deg) translateY(0px);
  }

  .section3 .ball_3 {
    transform: rotateX(-90deg) rotateY(120deg) translateY(0px);
  }

  .section3 .ball_4 {
    transform: rotateX(-90deg) rotateY(180deg) translateY(0px);
  }

  .section3 .ball_5 {
    transform: rotateX(-90deg) rotateY(240deg) translateY(0px);
  }

  .section3 .ball_6 {
    transform: rotateX(-90deg) rotateY(300deg) translateY(0px);
  }

  .section3 .ball_1 .ball {
    transform: rotateY(10deg) rotateZ(10deg);
  }

  .section3 .ball_2 .ball {
    transform: rotateY(-50deg) rotateZ(10deg);
  }

  .section3 .ball_3 .ball {
    transform: rotateY(-110deg) rotateZ(10deg);
  }

  .section3 .ball_4 .ball {
    transform: rotateY(-170deg) rotateZ(10deg);
  }

  .section3 .ball_5 .ball {
    transform: rotateY(-230deg) rotateZ(10deg);
  }

  .section3 .ball_6 .ball {
    transform: rotateY(-290deg) rotateZ(10deg); // 旋转
  }

  .section3 .ball {
    transition: all 10s ease-out 0ms; // 过渡
    transform-origin: 50% 50%;  // 旋转中心
    position: absolute;
    left: -70px;
    top: 20px;
    overflow: hidden;
    width: 90px;
    height: 90px;
    text-align: center;
    img{
      width: 90px;
      height: 90px;
      position: absolute;
      left: 0;
      top: 0;
      z-index: -1;
    }
    p{
      font-size: 12px;
      line-height: 22px;
      color: #01cdea;
      &.mt16{
        margin-top: 16px;
      }
      &.mt30{
        margin-top: 30px;
      }
      &.num{
        font-size: 18px;
      }
    }
  }

  .section3 .ball_1 .ball {
    transition-delay: 1100ms; // 指定何时将开始切换效果
  }

  .section3 .ball_2 .ball {
    transition-delay: 900ms;
  }

  .section3 .ball_3 .ball {
    transition-delay: 700ms;
  }

  .section3 .ball_4 .ball {
    transition-delay: 500ms;
  }

  .section3 .ball_5 .ball {
    transition-delay: 300ms;
  }

  .section3 .ball_6 .ball {
    transition-delay: 100ms;
  }

  .section3 .ball_1 .ball {
    animation: cir1 28s linear 0s infinite;
  }

  .section3 .ball_2 .ball {
    animation: cir2 28s linear 0s infinite;
  }

  .section3 .ball_3 .ball {
    animation: cir3 28s linear 0s infinite;
  }

  .section3 .ball_4 .ball {
    animation: cir4 28s linear 0s infinite;
  }

  .section3 .ball_5 .ball {
    animation: cir5 28s linear 0s infinite;
  }

  .section3 .ball_6 .ball {
    animation: cir6 28s linear 0s infinite;
  }

  .section3 .base {
    animation: cir 28s linear 0s infinite;
  }

  @keyframes cir1 {
    0% {
      transform: rotateY(0deg) rotateZ(10deg);
    }
    50% {
      display: none;
    }
    100% {
      transform: rotateY(-360deg) rotateZ(10deg);
    }
  }

  @keyframes cir2 {
    0% {
      transform: rotateY(-60deg) rotateZ(10deg);
    }
    100% {
      transform: rotateY(-420deg) rotateZ(10deg);
    }
  }

  @keyframes cir3 {
    0% {
      transform: rotateY(-120deg) rotateZ(10deg);
    }
    100% {
      transform: rotateY(-480deg) rotateZ(10deg);
    }
  }

  @keyframes cir4 {
    0% {
      transform: rotateY(-180deg) rotateZ(10deg);
    }
    100% {
      transform: rotateY(-540deg) rotateZ(10deg);
    }
  }

  @keyframes cir5 {
    0% {
      transform: rotateY(-240deg) rotateZ(10deg);
    }
    100% {
      transform: rotateY(-600deg) rotateZ(10deg);
    }
  }

  @keyframes cir6 {
    0% {
      transform: rotateY(-300deg) rotateZ(10deg);
    }
    100% {
      transform: rotateY(-660deg) rotateZ(10deg);
    }
  }

  @keyframes cir {
    0% {
      transform: rotateX(80deg) rotateY(-10deg) rotateZ(0deg);
    }
    100% {
      transform: rotateX(80deg) rotateY(-10deg) rotateZ(-360deg);
    }
  }

  @keyframes cir_c {
    0% {
      transform:rotate(0deg);
    }
    100% {
      transform:rotate(360deg);
    }
  }
}
</style>
