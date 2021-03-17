<template>
  <div class="free_result">
    <div class="red"><img src="../../../assets/img/qzw/bgc_red.png" alt=""></div>
    <div class="result_box">
        <div class="imgs"><img src="../../../assets/img/qzw/chahua.png" alt=""></div>
        <div class="texts">【齐装网】给小主请安，小齐已经接到您的装修申请，正马不停蹄赶来你的身边，请您注意接听哦，如有疑问请致电<span class="teles">400-6969-338</span></div>
    </div>
    <div class="supple">
      <div class="item">
        <p>补充资料   更快匹配设计师</p>
        <ul>
          <li v-for="(item,index) in designList" :key="index" @click="toogleDes(index,item.id)" :style="{background:activeIndex ==  index ?'#FFEDED':'#F5F6FA',color:activeIndex ==  index ?'#FF5353':'#333333'}">{{ item.name }}</li>
        </ul>
      </div>
      <div>
        <p>你准备什么时候开始装修</p>
        <ul>
          <li v-for="(item,index) in renovaList" :key="index" @click="toogleRen(index,item.name)" :style="{background:renovaIndex ==  index ?'#FFEDED':'#F5F6FA',color:renovaIndex ==  index ?'#FF5353':'#333333'}">{{ item.name }}</li>
        </ul>
      </div>
      <div>
        <p>你理想的上门量房时间</p>
        <ul>
          <li v-for="(item,index) in timerList" :key="index" @click="toogleTim(index,item.name)" :style="{background:timerIndex ==  index ?'#FFEDED':'#F5F6FA',color:timerIndex ==  index ?'#FF5353':'#333333'}">{{ item.name }}</li>
        </ul>
      </div>
      <div class="nextBtn" @click="subFn">下一步</div>
    </div>
  </div>
</template>
<script>
import { premark } from '@/api/api'
export default {
  name: 'free_result',
  data () {
    return {
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
      activeIndex:0,
      renovaIndex: 0,
      timerIndex: 0,
      id:'',
      nameone:'',
      nametwo: '',
    }
  },
  components: {
  },
  methods: {
    toogleDes(index,id) {
      this.activeIndex = index
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
     // 立即预约
    subFn() {
      let obj = {
        order_id:this.$route.query.orderid,
        zx_time: this.nameone ? this.nameone : '1个月内',
        lf_time:this.nametwo ? this.nametwo :'1周内',
        lxs: this.id ? this.id: 1,
      }
      premark(obj).then((res) => {
        console.log(res)
        if(res.data.error_code ===0){
          location.href = '/qzw/design-free-hour?orderid=' + this.$route.query.orderid
        }
      })
      this.$bridge.callNative('Native_bory_point', 'main_sheji_bc-nextbut', function (res) {
      })
    }
  }

}
</script>
<style scoped>
.free_result {
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
.result_box {
  width: 2.944rem;
  height: 1.894rem;
  margin-top: -1.067rem;
  position: relative;
  background-color: #fff;
  margin: 0 auto;
  border-radius: 0.034rem;
  padding-top: 0.213rem;
  box-sizing: border-box;

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
  margin-left: 0.222rem;
  margin-right: 0.222rem;
  margin-top: 0.137rem;
}
.teles {
  color: #FF5353;
}
.supple {
  width: 2.944rem;
  padding: 0.213rem 0.235rem;
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
</style>
