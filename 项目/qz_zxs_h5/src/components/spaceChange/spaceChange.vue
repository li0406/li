<template>
  <div class="space-change">
    <div class="space-banner">
      <img class="space-imgs" src="https://staticqn.qizuang.com/custom/20200819/FuOKwP1EUziyfNt_q7K_9L3dcl82" alt="">
    </div>
    <div class="space-fffback">
      <div class="flex space-title-div jus-con ali-ite">
        <div class="space-imgs-div">
          <img class="space-imgs" src="https://staticqn.qizuang.com/custom/20200819/FtbWL1nK34UYPbQsVYmydIiuI_kh" alt="">
        </div>
        <div class="space-title font-36">现在的你是否面临这些困扰？</div>
        <div class="space-imgs-div">
          <img class="space-imgs" src="https://staticqn.qizuang.com/custom/20200819/FtbWL1nK34UYPbQsVYmydIiuI_kh" alt="">
        </div>
      </div>
      <div class="space-item mdtp">
        <img class="space-imgs" src="https://staticqn.qizuang.com/custom/20200819/FgDW7ielnuFAkpJjFwApft5pkBPX" alt="">
      </div>
    </div>
    <div class="space-about">
      <div class="flex space-title-div jus-con ali-ite">
        <div class="space-imgs-div">
          <img class="space-imgs" src="https://staticqn.qizuang.com/custom/20200819/FtbWL1nK34UYPbQsVYmydIiuI_kh" alt="">
        </div>
        <div class="space-title font-36">只需一键预约，齐装都帮你解决</div>
        <div class="space-imgs-div">
          <img class="space-imgs" src="https://staticqn.qizuang.com/custom/20200819/FtbWL1nK34UYPbQsVYmydIiuI_kh" alt="">
        </div>
      </div>
      <div class="flex mdtp">
        <div class="space-about-img">
          <img class="space-imgs" src="https://staticqn.qizuang.com/custom/20200819/FvFThE0S84ayK110EUlwbBSEruxR" alt="">
        </div>
        <div class="space-msg-div">
          <div class="font-28 city_div">
            <div class="city_address font-28 col999" @click="openPicker()"><div :class="['city-xw',{'col000':addrText!='请选择您所在城市'},{'col999':addrText=='请选择您所在城市'}]">{{addrText}}</div><div class="triangle-icon"></div></div>
          </div>
          <div class="font-28 city_div">
            <input v-model="tel" class="city_tel" type="tel" maxlength="11" placeholder="请输入手机号码" oninput="value=value.replace(/\D/g,'')">
          </div>
          <div class="checkbox-div font-18 flex ali-ite">
            <div class="check-div" @click="changeexemptionbul()">
              <i class="fa fa-check colff7e00" v-if="exemptionbul"></i>
            </div>
            <div>
              我已阅读并同意<span class="org point" @click="todisclaimer()">《免责声明》</span>
            </div>
          </div>
          <div class="about-btn font-36 point" @click="fb(0)">立即预约</div>
        </div>
      </div>
    </div>
    <div class="space-renovation">
      <div class="ren-title font-40 bold">翻新计划</div>
      <div class="font-36 col999 flex jus-aro padlf-202">
      <!--repair\modify\reload-->
        <div class="bold" v-for="tab of tabnamelist" :key="tab.type">
          <div :class="['ren-tab',{'col333':sel_type==tab.type}]" @click="changetab(tab.type)">{{tab.name}}</div>
          <div :class="[{'on':sel_type==tab.type}]"></div>
        </div>
      </div>
      <mt-tab-container v-model="sel_type" class="tab-content" :swipeable="true">
        <mt-tab-container-item :id="1">
          <div class="tab-img-con">
            <div class="tab-imgs-div">
              <img class="tab-imgs" src="https://staticqn.qizuang.com/custom/20200825/FoBOo10zdOhTjyDH7mwS0avbGNCr" alt="">
            </div>
            <div class="font-32 col333 item-title bold">墙面/顶面/地面修复</div>
            <div class="font-24 col666 item-con">针对老房屋的墙面裂缝、防潮问题，通过微调将整体风格统一，恢复新装修的面貌，利用新能源材料，用最少的时间还你一个舒心的家，提升居住品质。</div>
          </div>
        </mt-tab-container-item>
        <mt-tab-container-item :id="2">
          <div class="tab-img-con">
            <div class="tab-imgs-div">
              <img class="tab-imgs" src="https://staticqn.qizuang.com/custom/20200825/FhEqSwLv442xechxvovzlDAM8gJ_" alt="">
            </div>
            <div class="font-32 col333 item-title bold">局部改造/水电改造</div>
            <div class="font-24 col666 item-con">例如阳台拆改，增加采光和阳台利用率；卫生间干湿分离，提高舒适感和整洁度；水电老化，对水电进行全部升级改造；小小的改动，提升整体幸福感。</div>
          </div>
        </mt-tab-container-item>
        <mt-tab-container-item :id="3">
          <div class="tab-img-con">
            <div class="tab-imgs-div">
              <img class="tab-imgs" src="//staticqn.qizuang.com/custom/20200825/Fp8nF2Qem9IXQY69vSTEkK9EJ0JL" alt="">
            </div>
            <div class="font-32 col333 item-title bold">整体/局部功能性重装设计</div>
            <div class="font-24 col666 item-con">十几年前的装修已过时？整体拆除、局部重新设计，设计师上门量房，商议翻新计划，让你花最少的钱，亲眼见证变魔术般的新房体验。</div>
          </div>
        </mt-tab-container-item>
      </mt-tab-container>
    </div>
    <div class="realcases">
      <div class="font-40 bold col333 realcases-title">真实案例</div>
      <div class="realcases-item">
        <div class="realcases-imgs-div">
          <div class="newcost font-24">翻新费用：6万</div>
          <img class="realcases-imgs" src="https://staticqn.qizuang.com/custom/20200825/FkIoDIjz4oCWG0VZZHTcuIwkINmw" alt="">
        </div>
          <div class="col333 font-32 bold rea-tit">7年老城区户型整屋翻新装</div>
          <div class="font-24 col999 rea-label">二居室 | 现代简约</div>
          <div class="flex ali-ite marb15">
            <div class="lab-btn font-18">翻新前</div>
            <div class="explain font-28 col666">风格不明、整体昏暗、利用率低</div>
          </div>
          <div class="flex ali-ite">
            <div class="lab-btn font-18">翻新后</div>
            <div class="explain font-28 col666">时尚大方、搭配合理、色彩鲜明</div>
          </div>
      </div>
      <div class="realcases-item">
        <div class="realcases-imgs-div">
          <div class="newcost font-24">翻新费用：3万</div>
          <img class="realcases-imgs" src="https://staticqn.qizuang.com/custom/20200825/FlDlSrvXdo7YKJhiTupOEwzEhw4s" alt="">
        </div>
          <div class="col333 font-32 bold rea-tit">3年二手房卧室&浴室改造</div>
          <div class="font-24 col999 rea-label">二居室 | 清新北欧</div>
          <div class="flex ali-ite marb15">
            <div class="lab-btn font-18">翻新前</div>
            <div class="explain font-28 col666">墙面开裂、渗水严重、昏暗陈旧</div>
          </div>
          <div class="flex ali-ite">
            <div class="lab-btn font-18">翻新后</div>
            <div class="explain font-28 col666">温馨舒适、水电重铺、整洁有序</div>
          </div>
      </div>
    </div>
    <div class="bigshot-div" v-if="bigshotlist!=''">
      <div class="font-40 bold col333 realcases-title">大咖云集</div>
      <div class="bigshot-info" v-for="bigshot of bigshotlist" :key="bigshot.id" @click="gouideatil(bigshot)">
        <div class="flex">
          <div class="header-imgs-div">
            <img class="header-imgs" :src="bigshot.logo" alt="">
          </div>
          <div class="bigshot-resume">
            <div class="flex ali-ite mb768">
              <div class="font-36 col333 bold">{{bigshot.name}}</div>
              <div class="rank font-18">{{bigshot.position}}</div>
            </div>
            <div class="mb768 font-30 col333" v-if="bigshot.job_time">从业时间: {{bigshot.job_time}}</div>
            <div class="mb768 font-30 col333" v-if="bigshot.case_num!=0">作品: {{bigshot.case_num}}</div>
            <div class="lab-flex" v-if="bigshot.fengge!=''">
              <div class="lab-item" v-for="fg of bigshot.fengge" :key="fg">{{fg}}</div>
            </div>
          </div>
          <div class="pre-btn font-24" @click="opendialog($event)">免费预约</div>
        </div>
        <div class="all-newhouse-imgs-div flex" v-if="bigshot.img_list">
          <div class="newhouse-imgs-div" v-for="(img,index) of bigshot.img_list" :key="index">
            <img class="newhouse-imgs" :src="img.img_url" alt="">
          </div>
        </div>
      </div>
    </div>
    <div class="space-about">
      <div class="flex space-title-div jus-con ali-ite">
        <div class="space-imgs-div">
          <img class="space-imgs" src="https://staticqn.qizuang.com/custom/20200819/FtbWL1nK34UYPbQsVYmydIiuI_kh" alt="">
        </div>
        <div class="space-title font-36 bold">服务与保障</div>
        <div class="space-imgs-div">
          <img class="space-imgs" src="https://staticqn.qizuang.com/custom/20200819/FtbWL1nK34UYPbQsVYmydIiuI_kh" alt="">
        </div>
      </div>
      <div class="font-28 col999 guarantee-text">齐装四大保障随行  翻新省钱更省心</div>
      <div class="guarantee-imgs-div">
        <img class="guarantee-imgs" src="https://staticqn.qizuang.com/custom/20200820/FuD5CRquyye9UIf1fV5iljp-T_3S" alt="">
      </div>
    </div>
    <div class="freedesign-dialog" v-show="opendialogbul">
      <div class="win-dialog">
        <div class="title-dialog bold font-40">免费预约设计师</div>
        <div class="subtitle-div flex">
          <div class="dash"></div>
          <div class="subtitle col999 font-28">省心装修 从此刻开启</div>
          <div class="dash"></div>
        </div>
        <div class="city-tel-dialog font-28 col999" @click="openPicker1()"><div :class="['city-xw-dialog',{'col000':addrText1!='请选择您所在城市'},{'col999':addrText1=='请选择您所在城市'}]" class=" col000">{{addrText1}}</div><div class="triangle-icon"></div></div>
        <div class="city-tel-dialog font-28 col999">
            <input v-model="tel1" class="input-dialog" type="tel" maxlength="11" placeholder="输入手机号，让我们为您服务" oninput="value=value.replace(/\D/g,'')">
        </div>
        <div class="checkbox-dialog font-18 flex ali-ite">
          <div class="check-div" @click="changeexemptionbul1()">
            <i class="fa fa-check colff7e00" v-if="exemptionbul1"></i>
          </div>
          <div>
            我已阅读并同意<span class="org point" @click="todisclaimer()">《免责声明》</span>
          </div>
        </div>
        <div class="btn-dialog font-36" @click="fb(1)">立即预约</div>
        <div class="grey-div">
          <div class="grey-x el-icon-close" @click="closedialog()"></div>
        </div>
      </div>
    </div>
    <div class="suc-bg-dia" v-show="sucdiabul">
      <div class="div-dia">
        <div class="suc-img-div">
          <img class="suc-img" src="https://staticqn.qizuang.com/custom/20200825/Fi1sz-bPX5c8b-Pnc7bqKdC3NZ_n" alt="">
        </div>
        <div class="font-36 col333 bold suc-title">恭喜您预约成功</div>
        <div class="font-26 col999 suc-subtitle">稍后客服将会与您联系，请注意接听来电~</div>
      </div>
      <div class="suc-greyx el-icon-close" @click="colsucdia()"></div>
    </div>
    <citySelect ref='city' v-on:getCityVlaue="getCityVlaue"></citySelect>
    <citySelect ref='city1' v-on:getCityVlaue="getCityVlaue1"></citySelect>
    <!--
    <div class="fixed flex">
      <div class="fixed-left">
        <div class="call-msg">
          <div class="call-imgs-div">
            <img class="call-imgs" src="https://staticqn.qizuang.com/custom/20200820/FvRafNO64QsAzz_-OQ23SHqdnNZk" alt="">
          </div>
          <div class="inquiry font-20">咨询</div>
        </div>
      </div>
      <div class="fixed-right">
        <div class="reform-btn font-32 point">预约改造我的家</div>
      </div>
    </div>
    -->
  </div>
</template>

<script>
import api from '@/api/spaceChange'
  export default {
    data() {
      return {
        tabnamelist:[
          {name:'修复',type:1},
          {name:'拆改',type:2},
          {name:'重装',type:3}
        ],
        sel_type:1,
        addrText:'请选择您所在城市',
        addrText1:'请选择您所在城市',
        opendialogbul:false,
        sucdiabul:false,
        bigshotlist:[],
        province:'',
        province1:'',
        tel:'',
        tel1:'',
        cs:'',
        qx:'',
        cs1:'',
        qx1:'',
        exemptionbul:true,
        exemptionbul1:true,
        rxg:/^1\d{10}$/,
        scrollTop:''
      }
    },
    components:{
      citySelect:()=>import('../common/citySelect')
    },
    created() {
      this.scrollTop = document.documentElement.scrollTop || window.pageYOffset || document.body.scrollTop;

      this.popularity()
      if(this.$route.query.tel){
        this.tel = this.$route.query.tel
        this.te1l = this.$route.query.tel
      }else{
        this.tel = ''
        this.te1l = ''
      }

    },
    methods: {
      changeexemptionbul(){
        this.exemptionbul=!this.exemptionbul
      },
      changeexemptionbul1(){
        this.exemptionbul1=!this.exemptionbul1
      },
      gouideatil(uiobj) {
          this.$bridge.callNative('Native_designer_detail', {
            designer:uiobj,
            company_id:uiobj.company_id

          }, function (res) {})
      },
      //发单
      fb(bul){
        let params
        if(bul){
          if((this.province1=='')||(this.cs1=='')||(this.qx1=='')){
            this.$toast('请选择城市')
            return
          }
          if(this.tel1==''){
            this.$toast('请填写手机号码')
            return
          }
          if(!this.rxg.test(this.tel1)){
            this.$toast('请输入正确手机号')
            return
          }
          if(!this.exemptionbul1){
            this.$toast('请先阅读并同意齐装网的免责声明')
            return
          }
          params={
            province:this.province1,
            cs:this.cs1,
            tel:this.tel1,
            qx:this.qx1,
            source:20081435,
            fb_type:'sheji'
          }

        }else{
          if((this.province=='')||(this.cs=='')||(this.qx=='')){
            this.$toast('请选择城市')
            return
          }
          if(this.tel==''){
            this.$toast('请填写手机号码')
            return
          }
          if(!this.rxg.test(this.tel)){
            this.$toast('请输入正确手机号')
            return
          }
          if(!this.exemptionbul){
            this.$toast('请先阅读并同意齐装网的免责声明')
            return
          }
          params={
            province:this.province,
            cs:this.cs,
            tel:this.tel,
            qx:this.qx,
            source:20081435,
            fb_type:'sheji'
          }
        }
        api.fb(params).then((res) => {
          if (parseInt(res.data.error_code) === 0) {
            if(bul){
              this.opendialogbul=false
            }
            this.sucdiabul=true
          } else {
            this.$toast(res.data.error_msg)
          }
        })
      },
      colsucdia(){
        this.sucdiabul=false
      },
      //  空间改造-大咖云集
      popularity(){
        let params={}
        api.popularity(params).then((res) => {
            if (parseInt(res.data.error_code) === 0) {
              this.bigshotlist = res.data.data.list
            } else {
              this.$toast(res.data.error_msg)
            }
          })
      },
      //  跳转免责声明
      todisclaimer(){
        this.$router.push({
          path:'/disclaimer'
        })
      },
      changetab(type){
        this.sel_type=type
      },
      //  城市选择
      openPicker () {
        this.$refs.city.openPicker()
      },
      //  城市选择
      openPicker1 () {
        this.$refs.city1.openPicker()
      },
      getCityVlaue (city) {
        this.addrText = city[0].name + ' ' + city[1].name + ' ' + city[2].name
        this.province = city[0].id
        this.cs = city[1].id
        this.qx = city[2].id
      },
      getCityVlaue1 (city) {
        this.addrText1 = city[0].name + ' ' + city[1].name + ' ' + city[2].name
        this.province1 = city[0].id
        this.cs1 = city[1].id
        this.qx1 = city[2].id
      },
      opendialog(e){
        e.stopPropagation()
        this.opendialogbul=true
        document.body.style = `${document.body.style.cssText};position:fixed;width:100%;margin-top:-${this.scrollTop}px`;
      },
      closedialog(){
        document.body.style = `${document.body.style.cssText};position:relative;margin-top:0;`;
        window.scrollTo(0, this.scrollTop);
        this.opendialogbul=false
      }
    },
  }
</script>

<style scoped>
body{
  overflow: hidden;
  height:100%;
}
.space-change{
  background-color: #F5F6FA;
  padding: 0 0 0.6rem 0;
}
.flex{
  display:flex;
}
.ali-ite{
  align-items: center;
}
.jus-con{
  justify-content: center;
}
.jus-aro{
  justify-content: space-around;
}
.spa-bet{
  justify-content: space-between;
}
.space-banner{
  width: 100%;
  height: 1.621rem;
}
.space-imgs{
  width: 100%;
  height: 100%;
}
.space-imgs-div{
  width: 0.256rem;
  height: 0.1621rem;
  margin: 0 0.1rem;
}
.space-title-div{
  padding: 0.17rem 0 0 0;
}
.space-title{
  font-family:PingFang SC;
  font-weight:500;
  color:rgba(51,51,51,1);
}
.font-36{
  font-size: 0.1536rem;
}
.space-fffback{
  height: 1.86rem;
  background-color: #fff;
}
.space-item{
  width: 2.944rem;
  height: 1.2032rem;
  margin: 0 auto;
}
.space-about{
  width: 100%;
  /*height: 2.1rem;*/
  margin: 0.1rem 0 0 0;
  background-color: #fff;
}
.space-about-img{
  width: 45%;
  height: 1.2288rem;
  margin: 0.24rem 0 0 0.128rem;
}
.space-msg-div{
  width: 55%;
}
.city_address{
  background:rgba(238,241,244,1);
  position: relative;
  margin: 0 0 0.128rem 0.1rem;
}
.city_tel{
  width: 1.2421rem;
  background:rgba(238,241,244,1);
  margin: 0 0.1322rem 0.128rem 0.1rem;
}
.city_div{
  width: 1.3421rem;
  height: 0.384rem;
  line-height: 0.384rem;
  background:rgba(238,241,244,1);
  border-radius:0.0256rem;
  margin: 0 0.1322rem 0.128rem 0.2rem;
}
.triangle-icon{
  width: 0;
  height: 0;
  border-right: 5px solid transparent;
  border-left: 5px solid transparent;
  border-top: 5px solid #90959D;
  position: absolute;
  top: 0.18rem;
  right: 0.1rem;
}
.font-28{
  font-size: 0.1194rem;
}
.about-btn{
  /*width: 1.3421rem;*/
  height: 0.384rem;
  line-height: 0.384rem;
  background:rgba(255,126,0,1);
  border-radius:0.0256rem;
  font-family:PingFang SC;
  font-weight:500;
  color:rgba(255,255,255,1);
  margin: 0 0.1322rem 0.1792rem 0.2rem;
  text-align: center;
}
.point{
  cursor: pointer;
}
.checkbox-div{
  margin: 0 0 0.17rem 0.2rem;
}
.font-18{
  font-size: 0.0768rem;
}
.org{
  color:rgba(255,126,0,1);
  margin: 0 0 0 0.001rem;
}
.c-box{
  vertical-align:middle;
  margin: 0 0.02rem 0 0;
  border:2px solid rgba(221,221,221,1);
  border-radius:2px;
}
.space-renovation{
  width: 100%;
  margin: 0.1rem 0 0 0;
  background-color: #fff;
}
.font-40{
  font-size: 0.17rem;
}
.bold{
  font-weight: bold;
}
.ren-title{
  text-align: center;
  font-family:PingFang SC;
  font-weight:bold;
  color:rgba(51,51,51,1);
  padding: 0.128rem 0 0.213rem 0;
}
.col999{
  color: #999;
}
.col666{
  color: #333;
}
.col333{
  color: #333;
}
.padlf-202{
  padding: 0 0.461rem;
}
.ren-tab{
  margin: 0 0 0.0832rem 0;
}
.on{
  width: 0.17rem;
  height: 0.0256rem;
  background:rgba(255,126,0,1);
  border-radius: 0.0128rem;
  margin: 0 auto;
}
.tab-content{
  height: 2.3978rem;
  border-radius:0.0256rem;
  overflow: hidden;
  overflow-x: scroll;
  margin: 0.0832rem 0 0 0;
  padding: 0 0 0.3rem 0.128rem;
}
.tab-img-con{
  margin: 0 0.128rem 0.128rem 0;
  background:rgba(246,247,251,1);
  padding: 0 0 0.001rem 0;
}
.tab-imgs-div{
  height: 1.5rem;
  border-radius:0.0256rem 0.0256rem 0 0;
}
.tab-imgs{
  width: 100%;
  height: 100%;
  border-radius:0.0256rem 0.0256rem 0 0;
}
.font-24{
  font-size: 0.1024rem;
}
.font-32{
  font-size: 0.1365rem;
}
.item-title{
  margin: 0.128rem 0 0 0.128rem;
}
.item-con{
  margin: 0.128rem;
}
.realcases{
  width: 100%;
  margin: 0.1rem 0 0 0;
  background-color: #fff;
}
.realcases-title{
  padding: 0.17rem 0 0 0;
  text-align: center;
}
.realcases-imgs-div{
  position: relative;
  height: 1.7rem;
  border-radius: 0.0256rem;
}
.newcost{
  position: absolute;
  width: 0.836rem;
  height: 0.2133rem;
  line-height: 0.2133rem;
  text-align: center;
  background-color: #FF7E00;
  color: #fff;
  left: 1.07rem;
  top: 0;
  border-radius:0px 0px 0.0256rem 0.0256rem;
}
.realcases-imgs{
  width: 100%;
  height: 100%;
  border-radius: 0.0256rem;
}
.rea-tit{
  margin: 0.128rem 0 0.1024rem 0;
}
.rea-label{
  font-weight:500;
  margin: 0 0 0.145rem 0;
}
.font-18{
  font-size: 0.0768rem;
}
.lab-flex{
  width: 2rem;
  display: flex;
  flex-wrap: wrap;
  height: 0.5rem;
  overflow: hidden;
  margin-top: 0.1rem;
  font-size: 0.0896rem;
}
.lab-btn{
  height: 0.1536rem;
  line-height: 0.1536rem;
  border-radius: 0.0213rem;
  background-color: #FFF1E4;
  color: #FF8001;
  text-align: center;
  padding: 0.0184rem 0.0512rem;
  margin: 0 0.064rem 0 0;
}
.lab-item{
  width: 0.5rem;
  height: 0.1536rem;
  line-height: 0.1536rem;
  border-radius: 0.0213rem;
  background-color: #FFF1E4;
  color: #FF8001;
  text-align: center;
  padding: 0.0184rem 0.0512rem;
  margin: 0 0.064rem 0.064rem 0;
}
.marb15{
  margin: 0 0 0.064rem 0;
}
.font-28{
  font-size: 0.1194rem;
}
.realcases-item{
  padding: 0.17rem 0 0.145rem 0;
  width: 2.944rem;
  margin: 0 auto;
}
.bigshot-div{
  width: 100%;
  margin: 0.1rem 0 0 0;
  background-color: #fff;
  padding: 0 0 0.17rem 0;
}
.bigshot-info{
  position: relative;
  width:2.944rem;
  /*height:1.8304rem;*/
  background:rgba(255,255,255,1);
  box-shadow:0 1px 20px 0px rgba(0, 0, 0, 0.05);
  border-radius:0.0853rem;
  margin: 0.17rem auto 0 auto;
  padding: 0 0 0.128rem 0;
}
.bor{
  border: 1px solid red;
}
.header-imgs-div{
  width: 0.64rem;
  height: 0.64rem;
  border-radius: 50%;
  margin: 0.095rem;
}
.header-imgs{
  width: 100%;
  height: 100%;
  border-radius: 50%;
}
.bigshot-resume{
  padding: 0.128rem 0 0 0;
}
.rank{
  background:linear-gradient(135deg,rgba(215,186,163,1) 0%,rgba(143,115,92,1) 100%);
  border-radius:0.021rem;
  color: #fff;
  padding: 0.0256rem 0.034rem;
  /*width: 0.388rem;
  height: 0.1194rem;*/
  margin: 0 0 0 0.085rem;
}
.mb768{
  margin: 0 0 0.0468rem 0;
}
.font-30{
  font-size: 0.128rem;
}
.pre-btn{
  position: absolute;
  top: 0.33rem;
  right: 0.0853rem;
  width: 0.6016rem;
  height: 0.2133rem;
  line-height: 0.2133rem;
  border-radius: 0.1066rem;
  color: #fff;
  text-align: center;
  background-color: #FF7E00;
}
.all-newhouse-imgs-div{
  width: 2.773rem;
  height: 0.657rem;
  margin: 0.128rem auto 0 0.128rem;
}
.newhouse-imgs-div{
  width: 0.8789rem;
  height: 0.657rem;
  border-radius: 0.02133rem;
  margin: 0 0.05rem 0 0;
}
.newhouse-imgs{
  width: 100%;
  height: 100%;
  border-radius: 0.02133rem;
}
.guarantee-text{
  text-align: center;
  margin: 0 0 0.17rem 0;
}
.guarantee-imgs-div{
  width: 2.944rem;
  height: 0.7168rem;
  margin: 0 auto;
  padding: 0 0 0.17rem 0;
}
.guarantee-imgs{
  width: 100%;
  height: 100%;
}
.fixed{
  width: 100%;
  height: 0.512rem;
  position: fixed;
  bottom: 0;
  background-color: #fff;
}
.fixed-left{
  flex-grow:1;
  align-items: center;
}
.fixed-right{
  flex-grow:2;
}
.call-imgs-div{
  width: 0.2048rem;
  height: 0.2048rem;
}
.call-imgs{
  width: 100%;
  height: 100%;
}
.inquiry{
  /*width:0.1664rem;
  height:0.0853rem;*/
  font-family:PingFang SC;
  font-weight:500;
  color:rgba(140,146,149,1);
}
.font-20{
  font-size: 0.0853rem;
}
.call-msg{
  width: 0.2048rem;
  margin: 0.0981rem 0 0 0.4352rem;
}
.reform-btn{
  width: 1.962rem;
  height: 0.341rem;
  line-height: 0.341rem;
  border-radius: 0.17rem;
  background:linear-gradient(90deg,rgba(255,126,0,1) 0%,rgba(255,90,0,1) 100%);
  color: #fff;
  text-align: center;
  margin: 0.0853rem 0 0.0853rem 0.34rem;
}
.city-xw{
  width: 1.1rem;
  overflow: hidden;/*超出部分隐藏*/
  white-space: nowrap;/*不换行*/
  text-overflow: ellipsis;/*超出部分文字以...显示*/
}
.city-xw-dialog{
  width: 2rem;
  overflow: hidden;/*超出部分隐藏*/
  white-space: nowrap;/*不换行*/
  text-overflow: ellipsis;/*超出部分文字以...显示*/
}
.freedesign-dialog{
  width: 100%;
  height: 100%;
  position: fixed;
  left: 0;
  top: 0;
  z-index: 99;
  background-color: rgba(0,0,0,0.6);
}
.win-dialog{
  position: absolute;
  top: 1.2885rem;
  left:0.32rem;
  width: 2.688rem;
  height: 2.6368rem;
  border-radius: 0.06826rem;
  background-color: #fff;
}
.title-dialog{
  width: 1.6085rem;
  height: 0.3242rem;
  line-height: 0.3242rem;
  background:linear-gradient(0deg,rgba(255,125,0,1) 0%,rgba(255,90,0,1) 100%);
  border-radius:0px 0px 0.0682rem 0.0682rem;
  margin: 0 auto;
  color: #fff;
  text-align: center;
}
.subtitle-div{
  width: 100%;
  justify-content: center;
  align-items: center;
  margin: 0.128rem 0.085rem 0.17rem 0;
}
.subtitle{
  margin: 0 0.085rem;
}
.dash{
  width: 0.192rem;
  border-top: 1px solid #E3E3E3;
}
.city-tel-dialog{
  /*width:2.432rem;*/
  height:0.384rem;
  line-height: 0.384rem;
  background:rgba(238,241,244,1);
  border-radius:0.0256rem;
  margin: 0 0.128rem 0.128rem 0.128rem;
  border-radius:0.0256rem;
  padding: 0 0 0 0.1rem;
  position: relative;
}
.input-dialog{
  width: 100%;
  height: 100%;
  background:rgba(238,241,244,1);
}
.checkbox-dialog{
  margin: 0 0 0 0.128rem;
}
.btn-dialog{
  width: 2.432rem;
  height: 0.384rem;
  line-height: 0.384rem;
  background:rgba(255,125,0,1);
  border-radius:0.0256rem;
  margin: 0.17rem auto;
  text-align: center;
  color: #fff;
}
.grey-div{
  text-align: center;
  margin: 0.3rem 0 0 0;
}
.grey-x{
  width: 0.31146rem;
  height: 0.31146rem;
  line-height: 0.31146rem;
  border:0.0128rem solid rgba(165,165,165,1);
  color: rgba(165,165,165,1);
  border-radius:50%;
  text-align: center;
  font-size: 0.23rem;
}
.h{
  width: 100%;
  height: 5rem;
  border: 1px solid;
}
.suc-bg-dia{
  width: 100%;
  height: 100%;
  position: fixed;
  left: 0;
  top: 0;
  z-index: 99;
  background-color: rgba(0,0,0,0.6);
}
.div-dia{
  position: relative;
  width: 2.517rem;
  height: 1.809rem;
  border-radius: 0.06826rem;
  background-color: #fff;
  margin: 1.3824rem auto;
}
.suc-img-div{
  position: absolute;
  top: 0.3285rem;
  left: 0.96rem;
  width: 0.576rem;
  height: 0.576rem;
}
.suc-img{
  width: 100%;
  height: 100%;
}
.font-26{
  font-size: 0.11rem;
}
.suc-title{
  position: absolute;
  top: 1.07rem;
  left: 0.725rem;
}
.suc-subtitle{
  position: absolute;
  top: 1.344rem;
  left: 0.2346rem;
}
.suc-greyx{
  width: 0.31146rem;
  height: 0.31146rem;
  line-height: 0.31146rem;
  border:0.0128rem solid rgba(165,165,165,1);
  color: rgba(165,165,165,1);
  border-radius:50%;
  position: absolute;
  top: 3.4474rem;
  left: 1.4421rem;
  text-align: center;
  font-size: 0.23rem;
}
.col000{
  color: #000;
}
.colff7e00{
  color: #FF7E00;
}
.check-div{
  width: 0.1024rem;
  height: 0.1024rem;
  line-height: 0.1024rem;
  border: 1px solid #999;
  border-radius: 0.0256rem;
  text-align: center;
  margin: 0 0.0256rem 0 0;
}
.mdtp{
  margin-top: 0.17rem;
}
</style>
