<template>
  <div class="quote_result">
    <div class="red"><img src="../../../assets/img/qzw/bgc_red.png" alt=""></div>
    <div class="title">
      <p>估价结果已生成</p>
      <p class="telpho">请保持手机畅通，客服会尽快联系你</p>
    </div>
    <div class="swiper-container">
      <swiper :options="swiperOption" ref="mySwiper" v-if="this.resData.length>1">
        <swiper-slide class="slide_dd" v-for="(item,index) in this.resData" :key="index">
          <div class="swiper_fff">
            <div class="ding">
              <span class="num">{{ item.total_price }}</span>
              <span class="jia">元(半包价格)</span>
            </div>
            <div class="fuliao">
              <p>
                <span class="one">辅材</span>
                <span class="one">|</span>
                <span>{{ item.fucai_price }}</span>
              </p>
              <p>
                <span class="one">人工费</span>
                <span class="one">|</span>
                <span>{{ item.rengong_price }}</span>
              </p>
            </div>
            <div :class="sstt[index]">{{ item.type }}</div>
          </div>
        </swiper-slide>
      </swiper>
    </div>
    <!-- echart图 -->
    <div class="e-biao" v-if="isesct">
      <div id="main" ref="main" :style="{width: '100%', height: '121px'}"></div>
      <div class="tips">该报价为估算价，实际报价以量房为准</div>
    </div>
    <div class="baojia" @click="toogleEch">
      <span>空间详细报价</span>
      <span class="bgc_down" v-if="isesct == false"><img src="../../../assets/img/qzw/bgc_down.png" alt=""></span>
      <span class="bgc_down" v-if="isesct == true"><img src="../../../assets/img/qzw/bgc_up.png" alt=""></span>
    </div>
    <div class="rgse">
      <div class="supple">
        <div>
          <p>预约上门量房时间</p>
          <ul>
            <li v-for="(item,index) in timerList" :key="index" @click="toogleTim(index,item.name)" :style="{background:timerIndex ==  index ?'#FFEDED':'#F5F6FA',color:timerIndex ==  index ?'#FF5353':'#333333'}">{{ item.name }}</li>
          </ul>
        </div>
        <p>你家的楼盘、小区名称</p>
        <div class="quote_box">
          <input type="text" maxlength="30" v-model="cityxq" placeholder="请输入小区名称">
        </div>
        <div>
          <p>你准备什么时候开始装修</p>
          <ul>
            <li v-for="(item,index) in renovaList" :key="index" @click="toogleRen(index,item.name)" :style="{background:renovaIndex ==  index ?'#FFEDED':'#F5F6FA',color:renovaIndex ==  index ?'#FF5353':'#333333'}">{{ item.name }}</li>
          </ul>
        </div>
        <div class="item">
          <p>你的房屋类型</p>
          <ul>
            <li v-for="(item,index) in designList" :key="index" @click="toogleDes(index,item.id)" :style="{background:cmfamIndex ==  index ?'#FFEDED':'#F5F6FA',color:cmfamIndex ==  index ?'#FF5353':'#333333'}">{{ item.name }}</li>
          </ul>
        </div>
        <div class="nextBtn" @click="subFn">立即预约</div>
      </div>
    </div>
  </div>
</template>
<script>
import 'swiper/dist/css/swiper.css'
import { swiper, swiperSlide } from 'vue-awesome-swiper'
import echarts from "echarts";
import { echartBill,premark } from '@/api/api'
export default {
  name: 'quote_result',
  data () {
    const that =this
    return {
      swiperOption: {
        //移动端轮播
        effect: "side",
        slidesPerView: 1.34,
        centeredSlides: true,
        loopAdditionalSlides: 1,
        loop: true,
        observer:true,//修改swiper自己或子元素时，自动初始化swiper
        observeParents:true,//修改swiper的父元素时，自动初始化swiper
        pagination: {
            el: '.swiper-pagination',
        },
        on: {
          slideChangeTransitionStart: function(){
            if(that.isesct){
              that.echartIndex = this.realIndex
              that.isEchartFn(that.resData[this.realIndex].detail)
            }
          },
        },

      },
      designList:[
        {
          id:1,
          name: '新房装修'
        }, {
          id:2,
          name: '整体翻新'
        }, {
          id:3,
          name: '局部改造'
        },
      ],
      renovaList:[
        {
          id:1,
          name: '1个月内'
        }, {
          id:2,
          name: '3个月内'
        }, {
          id:3,
          name: '方案满意'
        },
      ],
      timerList:[
        {
          id:1,
          name: '1周内'
        }, {
          id:2,
          name: '2周内'
        }, {
          id:3,
          name: '1个月以上'
        },
      ],
      echartIndex: 0,
      cmfamIndex:0,
      renovaIndex: 0,
      timerIndex: 0,
      isesct: false,
      resData:[],
      cityxq:'',
      id:'',
      nameone:'',
      nametwo: '',
      option : {},
      sstt:[
        "ss1",
        "ss2",
        "ss3",
      ]
    }
  },
  components: {
    swiper,
    swiperSlide,
  },
  mounted() {
    this.getechartBill()
  },
  watch: {
    '$route'(to, from) {
      this.$router.go(0);
    }
  },
  methods: {
    getechartBill(){
      let orderid = this.$route.query.orderid
      echartBill({orderid}).then((res) => {
      if(res.data.error_code ===0){
        this.resData = res.data.data
      }
    })
    },
    toogleDes(index,id) {
      this.cmfamIndex = index,
      this.id = id
    },
    toogleRen(index,name) {
      this.renovaIndex = index
      this.nameone = name
    },
    toogleTim(index,name) {
      this.timerIndex = index
      this.nametwo = name
    },
    toogleEch() {
      this.isesct = !this.isesct
      if(this.isesct){
         this.$nextTick( () => {
            this.isEchartFn(this.resData[0].detail)
        });
      }
    },
    isEchartFn(detail) {
      this.option = {
        legend: {
          itemHeight:5,
          orient: 'vertical',
          selectedMode:false,
          left: 105,
          icon:"circle",
          textStyle: { //图例文字的样式
            color: '#999',
            fontSize: 12
          },
        },
        color: ['#3F93ED','#CE7B9B','#FFB141','#80D2AE','#8E72D3','#79C4FF'],
        series: [
          {
            center:  ['19%', '45%'],
            type: 'pie',
            radius: ['50%', '70%'],
            avoidLabelOverlap: false,
            hoverAnimation: false,
            label: {
                show: false,
                position: 'center'
            },
            emphasis: {
                label: {
                    show: false,
                    fontSize: '14',
                    fontWeight: 'bold'
                }
            },
            labelLine: {
              show: false
            },
            data: [
              {value: detail.keting, name: `客厅           ${detail.keting}元`},
              {value: detail.woshi, name: `卧室           ${detail.woshi}元`},
              {value: detail.weishengjian, name: `卫生间       ${detail.weishengjian}元`},
              {value: detail.chufang, name: `厨房           ${detail.chufang}元`},
              {value: detail.yangtai, name: `阳台过道    ${detail.yangtai}元`}
            ]
          }
        ],
      }
      this.generateChart()
    },
    generateChart(){
      const dom = this.$refs.main
      let myChart = echarts.init(dom)
      myChart.setOption(this.option);
    },
    // 立即预约
    subFn() {
      let obj = {
        order_id:this.$route.query.orderid,
        zx_time: this.nameone ? this.nameone : '1个月内',
        lf_time:this.nametwo ? this.nametwo :'1周内',
        lxs: this.id ? this.id: 1,
        xiaoqu: this.cityxq
      }
      premark(obj).then((res) => {
        console.log(res)
        if(res.data.error_code ===0){
          location.href = '/qzw/quote-free-succ'
        }
      })
      this.$bridge.callNative('Native_bory_point', 'main_sbj_bc_submit', function (res) {
      })

    }
  }

}
</script>
<style scoped>
.quote_result {
  width: 100%;
  height: 100%;
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
.swiper-container {
  margin: 0 auto;
  position: relative;
  z-index: 1;
  overflow: hidden;
  padding: 0.075rem 0 0.075rem;
  box-sizing: border-box;
}

.swiper-container .swiper-wrapper {
  position: relative;
  width: 100%;
  height: 100%;
  z-index: 1;
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  -webkit-transition-property: -webkit-transform;
  transition-property: -webkit-transform;
  -o-transition-property: transform;
  transition-property: transform,-webkit-transform;
  -webkit-box-sizing: content-box;
  box-sizing: content-box
}

.swiper-container .swiper-wrapper {
  position: relative
}

.swiper-container .swiper-wrapper .swiper-slide {
  transform: scale(.9,.8);
  transition-duration: .5s;
  -webkit-flex-shrink: 0;
  -ms-flex-negative: 0;
  flex-shrink: 0;
  -webkit-box-shadow: .01rem .01rem .12rem 0 rgba(82,91,100,.16);
  box-shadow: .01rem .01rem .12rem .01rem rgba(82,91,100,.16);
  border-radius: .06rem
}

.swiper-container .swiper-wrapper .swiper-slide.swiper-slide-active {
  transform: scale(1,1);
  transition-duration: .5s
}
.swiper_fff {
  width: 100%;
  border-radius: 0.034rem;
  background-color: #fff;
  padding: 0.171rem;
  box-sizing: border-box;
  position: relative;
}
.ding {
  color: #333;
}
.num {
  font-size: 0.307rem;
  font-weight: bold;
}
.jia {
  font-size: 0.102rem;
  font-weight: 500;
}
.fuliao {
  display: flex;
  color:#333333;
  font-size: 0.102rem;
}
.fuliao p {
  margin-right: 0.166rem;
}
.one {
  color: #999999;
}
.ss1 {
  width: 0.341rem;
  height: 0.171rem;
  line-height: 0.171rem;
  text-align: center;
  background-color: #0CC188;
  font-size: 0.102rem;
  color: #fff;
  border-top-left-radius: 0.085rem;
  border-bottom-left-radius: 0.085rem;
  position: absolute;
  top: 0.085rem;
  right: 0;
}
.ss2 {
  width: 0.341rem;
  height: 0.171rem;
  line-height: 0.171rem;
  text-align: center;
  font-size: 0.102rem;
  color: #fff;
  border-top-left-radius: 0.085rem;
  border-bottom-left-radius: 0.085rem;
  position: absolute;
  top: 0.085rem;
  right: 0;
  background-color: #6173F1;
}
.ss3 {
  width: 0.341rem;
  height: 0.171rem;
  line-height: 0.171rem;
  text-align: center;
  background-color: #FFA21E;
  font-size: 0.102rem;
  color: #fff;
  border-top-left-radius: 0.085rem;
  border-bottom-left-radius: 0.085rem;
  position: absolute;
  top: 0.085rem;
  right: 0;
}
.title {
  color: #fff;
  font-size: 0.179rem;
  font-weight: bold;
  margin-left: 0.260rem;
}
.telpho {
  font-size: 0.102rem;
  font-weight: 500;
  margin-top: 0.085rem;
}
.bgc_down {
  width: 0.077rem;
  height: 0.077rem;
  margin-left:0.034rem;

}
.bgc_down img {
  width: 100%;
  height: 100%;
}
.baojia {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.085rem;
  color:#FF5353;
}
.rgse {
  background-color: #F2F3F7;
}
.supple {
  width: 2.731rem;
  padding: 0.209rem 0.128rem;
  box-sizing: border-box;
  background-color: #fff;
  margin: 0.137rem auto;
  border-radius: 0.034rem;
  font-size: 0.119rem;
  font-weight: 500;
  color: #333;
}
.supple>div>ul {
  display: flex;
  justify-content: space-between;
}
.supple>div>ul>li {
  width: 0.768rem;
  height: 0.273rem;
  background-color: #F5F6FA;
  border-radius: 0.009rem;
  margin-top: 0.102rem;
  margin-bottom: 0.209rem;
  text-align: center;
  line-height: 0.273rem;
  font-size: 0.119rem;
  font-weight: 500;
}
.nextBtn {
  width: 100%;
  height: 0.410rem;
  line-height: 0.410rem;
  background-color: #FF5353;
  color: #fff;
  text-align: center;
  font-size: 0.137rem;
  border-radius: 0.026rem;
}
.quote_box {
  margin-bottom: 0.128rem;
  height: 0.410rem;
  line-height: 0.410rem;
  border-radius: 0.026rem;
  background-color: #F5F6FA;
  padding-left: 0.085rem;
  padding-right: 0.085rem;
  box-sizing: border-box;
  margin-top: 0.102rem;
}
.quote_box input{
  width: 100%;
  background-color: #F5F6FA;
}
.e-biao {
  width: 2.731rem;
  padding: 0.213rem 0.235rem;
  box-sizing: border-box;
  background-color: #fff;
  margin: 0 auto;
  margin-bottom: 0.137rem;
  border-radius: 0.034rem;
  font-size: 0.085rem;
  color: #ccc;
  text-align: center;
}
.tips {
  margin-top: 0.085rem;
}

</style>
