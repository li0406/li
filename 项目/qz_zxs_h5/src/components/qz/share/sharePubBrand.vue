<template>
  <section class="bg">
    <open-app></open-app>
    <div class="share-brand">
        <div class="top-banner">
           <img src="../../../assets/img/qz/share/koubei.png" class="top-banner-bg"/>
        </div>
        <div class="main">
            <ul class="list">
                <li v-for="(val,index) in list" :key="index">
                    <div class="sort" v-if="index == 0"></div>
                    <div class="sort" v-else-if="index == 1"></div>
                    <div class="sort" v-else-if="index == 2"></div>
                    <div class="sort" v-else>{{index + 1}}</div>
                    <div class="logo">
                        <img :src="val.logo" alt="">
                    </div>
                    <div class="info">
                        <div class="company-name">{{val.name}}</div>
                        <div class="stars">
                            <span class="star" v-for="(n,index) in val.star" :key="index"></span>
                            <span class="red">{{val.percent}}%</span>
                        </div>
                        <p><span>案例：{{val.case_count}}</span></p>
                    </div>
                    <div class="right">
                        <img src="../../../assets/img/qz/share/right.png" alt="">
                    </div>
                    <div class="koubei">口碑值: {{val.rank}}</div>
                </li>
            </ul>
        </div>
    </div>
    <wx-tips :isWechat="isWechat"></wx-tips>
  </section>
</template>
<script>
import openApp from '@/components/common/openApp'
import qzOpenApp from '@/mixins/qzOpenApp'
import wxTips from '@/components/common/wxTips'
import { sharePubBrand } from '@/api/api'
export default {
  name: 'sharePubBrand',
  components: {
    openApp,
    wxTips
  },
  mixins: [qzOpenApp],
  data () {
    return {
      isWechat: false,
      list: []
    }
  },
  created () {
    this.getList()
  },
  methods: {
    getList () {
      sharePubBrand({
      }).then(res => {
        if (parseInt(res.data.error_code) === 0) {
          this.list = res.data.data
        }
      }).catch(err => {
        console.log(err)
      })
    }
  }
}
</script>
<style scoped>
.bg {
    background: #f5f5f5;
    width: 100%;
    box-sizing: border-box;
}
.top-banner {
  width: 100%;
  height: 1.4rem;
}
.top-banner img {
    display: block;
    width: 100%;
    height: 100%;
}
.main {
    margin: 0 0.135rem;
    position: relative;
    top: -35px;
}
.list li {
    width: 100%;
    height: 0.81rem;
    background: #fff;
    border-radius: 5px;
    margin-bottom: 10px;
    position: relative;
}
.list li .sort {
    width: 20px;
    height: 25px;
    color: #5D4B3B;
    font-size: 12px;
    font-weight: bold;
    line-height: 28px;
    text-align: center;
    float: left;
    margin: 32.5px 14px 32.5px 19.5px;
    background: url(../../../assets/img/qz/share/medal.png) no-repeat;
    background-size: 100%;
}
.list li:nth-child(1) .sort {
    width: 32.5px;
    margin: 32.5px 7.5px 32.5px 14px;
    background: url(../../../assets/img/qz/share/gold.png) no-repeat;
    background-size: 100%;
}
.list li:nth-child(2) .sort {
    width: 32.5px;
    margin: 32.5px 7.5px 32.5px 14px;
    background: url(../../../assets/img/qz/share/silver.png) no-repeat;
    background-size: 100%;
}
.list li:nth-child(3) .sort {
    width: 32.5px;
    margin: 32.5px 7.5px 32.5px 14px;
    background: url(../../../assets/img/qz/share/bronze.png) no-repeat;
    background-size: 100%;
}
.list li .logo {
    float: left;
    width:75px;
    height:66px;
    background:rgba(255,255,255,1);
    border:1px solid rgba(204,204,204,1);
    border-radius:5px;
    margin: 14px 11px 14px 0;
}
.list li .logo img {
    display: block;
    width: 100%;
    height: 100%;
}
.list li .info {
    float: left;
    margin-top: 20px;
}
.list li .info .company-name {
    color: #333;
    font-size: 15px;
    width: 1.4rem;
    height: 0.18rem;
    overflow: hidden;
    text-overflow: ellipsis;
    display: box;
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
}
.list li .info .stars .star {
    display: block;
    width: 10px;
    height: 10px;
    background: url(../../../assets/img/qz/share/star.png) no-repeat;
    background-size: 100%;
    float: left;
    position: relative;
    top: 5px;
}
.list li .info .stars .red {
    font-size: 12px;
    color: #FF5353;
    margin-left: 5px;
}
.list li .info p {
    font-size: 12px;
}
.list li .right {
    width: 10px;
    height: 20px;
    float: right;
    margin: 35px 15px 0 0;
}
.list li .right img {
    display: block;
    width: 100%;
    height: 100%;
}
.list li .koubei {
    position: absolute;
    top: 10px;
    right: 10px;
    color: #333;
    font-size: 8px;
}
</style>
