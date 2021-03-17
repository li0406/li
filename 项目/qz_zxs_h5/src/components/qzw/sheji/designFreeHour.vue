<template>
  <div class="free_hour">
    <div class="hour_box">
      <p>姓名/称呼</p>
      <div class="names">
        <input type="text" maxlength="20" placeholder="小姐/先生" v-model="men">
      </div>
      <div class="items">
        <ul>
          <li v-for="(item,index) in designList" :key="index" @click="toogleDes(index,item.name)" :style="{background:activeIndex ==  index ?'#FFEDED':'#F5F6FA',color:activeIndex ==  index ?'#FF5353':'#333333'}">{{ item.name }}</li>
        </ul>
      </div>
      <p>你家的楼盘、小区名称</p>
      <div class="names">
        <input type="text" maxlength="30" v-model="cityqu" placeholder="请输入小区名称">
      </div>
      <div class="nextBtn" @click="subFn">精准匹配</div>
    </div>
    <div class="tanpo" v-if="isShow">
      <div class="tanceng">
        <p class="tupian"><img src="../../../assets/img/qzw/owosucc.png" alt=""></p>
        <p class="succ">申请成功</p>
        <p>小齐感谢您的耐心填写，稍后齐装 </p>
        <p>家居顾问将会与您联系，请注意接听来电～</p>
      </div>
      <div class="close" @click="bibtn"><img src="../../../assets/img/qzw/close.png" alt=""></div>
    </div>

  </div>
</template>
<script>
import { premark } from '@/api/api'
export default {
  name: 'free_hour',
  data () {
    return {
      designList:[
        {
          id:1,
          name: '女士'
        }, {
          id:2,
          name: '先生'
        },
      ],
      activeIndex:0,
      isShow: false,
      men: '',
      cityqu:''
    }
  },
  components: {
  },
  methods: {
    toogleDes(index,name) {
      this.activeIndex = index
      this.sexname = name
    },
    bibtn() {
      this.isShow = false
    },
    subFn() {
      let obj = {
        order_id:this.$route.query.orderid,
        xiaoqu:this.cityqu,
        name:this.men,
        sex: this.sexname?this.sexname:'女士',
      }
      premark(obj).then((res) => {
        if(res.data.error_code ===0){
          this.isShow = true
        }
      })
      this.$bridge.callNative('Native_bory_point', 'main_sheji_bc-submit', function (res) {
      })
    }
  }

}
</script>
<style scoped>
.free_hour {
  width: 100%;
  height: 100%;
  background-color: #F2F3F7;
  padding-top: 0.128rem;
  box-sizing: border-box;
}
.hour_box {
  width: 2.944rem;
  padding: 0.213rem 0.235rem;
  box-sizing: border-box;
  background-color: #fff;
  margin: 0 auto;
  font-size: 0.119rem;
  font-weight: 500;
  color: #333;
}
.names {
  width: 2.603rem;
  height: 0.410rem;
  border-radius: 0.026rem;
  background-color: #F5F6FA;
  padding-left: 0.085rem;
  padding-right: 0.085rem;
  box-sizing: border-box;
  margin-top: 0.102rem;
  margin-bottom: 0.085rem;

}
.names>input {
  width: 100%;
  height: 100%;
  background-color:  #F5F6FA;
}
.items>ul {
  display: flex;
}
.items>ul>li {
  width: 0.768rem;
  height: 0.273rem;
  background-color: #F5F6FA;
  border-radius: 0.009rem;
  margin-bottom: 0.166rem;
  text-align: center;
  line-height: 0.273rem;
  margin-right: 0.085rem;
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
.tanceng {
  width: 2.688rem;
  height: 1.963rem;
  background-color: #fff;
  margin: 0 auto;
  margin-top: 1.924rem;
  border-radius: 0.068rem;
  text-align: center;
  font-size: 0.119rem;
  font-weight: 500;
  color: #999;
}
.tanpo {
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.7);
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
}
.succ {
  font-size: 0.179rem;
  font-weight: bold;
  color: #333;
  margin-top: 0.192rem;
  margin-bottom: 0.273rem;

}
.tupian {
  width: 1.192rem;
  margin: 0 auto;
}
.tupian img {
  width: 1.192rem;
  height: 0.875rem;
  margin-top: -0.427rem;
  position: relative;
}
.close {
  width: 0.316rem;
  height: 0.316rem;
  margin: 0 auto;
  margin-top: 0.277rem;
}
.close img {
  width: 100%;
  height: 100%;
}

</style>
