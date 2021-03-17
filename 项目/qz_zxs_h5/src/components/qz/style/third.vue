<template class="p-container">
  <section class="section">
    <div class="top">
      <div class="top-item one">
        1.选择户型
      </div>
      <div class="top-item">
        2.选择风格
      </div>
      <div class="top-item active">
        3.免费领取方案
      </div>
    </div>
    <div class="bg">
      <img src="../../../assets/img/qz/style/bg_two.jpg" class='bg-one bg-img' style="width:100%;"/>
      <div class="bg-con">
        <div class="wenan">我们已根据您的兴趣爱好，精选出了<span>4套</span>设计方案</div>
        <div class="wenan-bottom">填写信息，免费领取4套设计方案</div>
        <div class="input-box">
          <img src="../../../assets/img/qz/style/input_bg.png">
          <input type="type" class="inputqc" placeholder="输入手机号获取设计方案" maxlength="11" v-model="phone"/>
        </div>
        <div class="disclaimer" style="margin-bottom:0.037rem;">
          <label @click="isDisclaimer">
            <i class="fa fa-check" v-if="disclaimer"></i>
          </label>
          <span>我已阅读并同意齐装网的</span>
          <span class="mznav" @click="isSming">《免责声明》</span>
        </div>
        <div @click="toGetPlan" class="bottom-btn" style="margin-top:0.3rem">
          <img src="../../../assets/img/qz/style/bottom_btn.png" class="center-center">
          <span style="color:#ff5353">立即领取</span>
        </div>
        <div @click="toStyle" class="bottom-btn">
          <img src="../../../assets/img/qz/style/bottom_back.png" class="center-center">
          <span class="back-btn" style="color:#fff;">返回重选</span>
        </div>
      </div>
    </div>
    <m-tips ref="tips"/>
    <div class="end-mask" v-if="openMask">
      <div class="end-box">
        <div class="end-content">
          <img src="../../../assets/img/qz/style/success.png" style="width:100%;">
          <img src="../../../assets/img/qz/style/close.png" @click="closeMask" style="width:0.18rem;height: 0.18rem;position: absolute;top:-5px;right: 5px;z-index: 999;">
          <div class="wenan-text">
            <div style="font-size:0.135rem; padding-top:0.45rem; padding-bottom:0.11rem;">
              领取成功
            </div>
            <div style="font-size:0.11rem; padding-bottom:0.187rem;">
              稍后我们会致电给您，请注意接听电话
            </div>
            <div  class="ok-btn" @click='closeMask'>确&nbsp;定</div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
<script>
    import mTips from '../../common/mTips.vue'
    import citySelect from '../../common/citySelect.vue'
    import { postFenge } from '@/api/api'
    import { getInfo } from '@/api/activityApi.js'
    export default {
        components: {
            mTips, citySelect
        },
        name: 'third',
        data () {
            return {
                phone: '',
                disclaimer: true,
                openMask:false,
                endCall: false,
                fg: '',
                hx: '',
                huxing: '',
                fengge: '',
                fengid: '',
                cs: 320500,
                qx: 320508,
                currentCity:[]
            }
        },
        computed: {

        },
        created () {
            var theRequest = new Object();
            let url = location.search
            if(url.indexOf("?") != -1 ){
                var str = url.substr(1);
                var strs = str.split("&");
                for(var i= 0;i<strs.length;i++){
                    theRequest[strs[i].split("=")[0]] = (strs[i].split("=")[1]);
                }
            }
            let hx = theRequest.hx
            let fg = theRequest.fg
            let fd = theRequest.fd
            this.huxing = hx
            this.fengge = fg
            this.fengid = fd
            this.getHasPhone()
            this.changeHeader()
        },
        mounted () {
            document.title = '风格测试-寻找未来家的样子'
            this.getCity()
        },
        methods: {
            getHasPhone () {
                if (!this.endCall) {
                    setTimeout(() => {
                        this.getHasPhone()
                        this.getAppStatus()
                    }, 200)
                }
            },
            getCity () {
                let that = this
                if (sessionStorage.currentCity) {
                    let currentCity = JSON.parse(sessionStorage.currentCity)
                    that.currentCity = currentCity
                }
            },
            getAppStatus () {
                this.$bridge.callNative('base_userData', {}, res => {
                    if (res) {
                        this.$cookies.set('token', res)
                        getInfo().then(res => {
                            if (res.data.error_code === 0) {
                                this.phone = res.data.data.info.phone
                                this.endCall = true
                            }
                        })
                    }
                })
            },
            toGetPlan(){
                let that = this
                let currentCity = that.currentCity
                if(currentCity.length){
                    let cs = currentCity[1].id
                    let qx = currentCity[2].id
                    this.cs= cs
                    this.qx = qx
                }
                let tel_reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
                if(that.phone == ''){
                    setTimeout(()=>{
                        that.$refs.tips.tipsFadeIn({
                            text: '请输入您的手机号'
                        })
                    },1000)
                    return false
                }
                if(!tel_reg.test(that.phone)){
                    setTimeout(()=>{
                        that.$refs.tips.tipsFadeIn({
                            text: '请输入正确的手机号'
                        })
                    },1000)
                    return false
                }
                if (!this.disclaimer) {
                    setTimeout(()=>{
                        that.$refs.tips.tipsFadeIn({
                            text: '免责声明不能为空'
                        })
                    },1000)
                    return false
                }
                let parms = {
                    tel: that.phone,
                    cs: that.cs,
                    qx: that.qx,
                    fengge: that.fengid,
                    huxing: that.huxing,
                    source: 19093055
                }
                postFenge(parms, 'zxs-h5').then((result) => {
                    if (result.data.error_code === 0) {
                        that.openMask = true
                    } else {
                        that.$refs.tips.tipsFadeIn({
                            text: result.data.error_msg
                        })
                        return false
                    }
                })
            },
            isDisclaimer () {
                this.disclaimer = !this.disclaimer
            },
            toStyle(){
                let that = this
                window.location.href = "fengge?hx="+that.huxing+'&fg='+that.fengge+'&fd='+that.fengid
            },
            isSming() {
                this.$router.push('/disclaimer')
            },
            closeMask(){
                this.openMask = false
                this.fg = ''
                window.location.href = "/zxfeature"
                this.changeHeader()
            },
            changeHeader (){
                let that = this
                // 回退
                that.$bridge.callNative('UI_onBackClick', '000', function (res) {
                    if (res) {
                        that.endCall = true
                    }
                })
                that.$bridge.registWebNew('JS_onBackClick', function (data, nativeCallBack) {
                    nativeCallBack(1)
                })
            }
        }
    }
</script>
<style scoped>
  .bg{
    position: relative;
    top:0;
    left: 0;
    bottom: 0;
    right: 0;
  }
  .bg .bg-img{
    position: absolute;
    top:0;
    left: 0;
    width: 100%;
    z-index: 1;
  }
  .section{
    height: 104%;
    background: #41349E;
  }
  .wenan{
    text-align: center;
    color: #fff;
    font-size: 0.12rem;
    padding:0.07rem 0;
  }
  .wenan span{
    color: #0FEBFF;
    font-size: 0.173rem;
  }
  .wenan-bottom{
    padding-top:1.58rem;
    color: #fff;
    text-align: center;
    font-size:0.13rem;
  }
  .input-box{
    width:80%;
    margin:10px auto;
    position: relative;
  }
  .input-box img{
    width:100%
  }
  .input-box input{
    width:85%;
    position: absolute;
    top:-5px;
    left:0;
    bottom:0;
    right:0;
    margin:auto;
    color:#fff !important;
    background: none;
    font-family: '微软雅黑';
  }
  .input-box input{
    box-shadow:none; /*去除阴影*/
    outline: none;/*聚焦input的蓝色边框*/
    border: none; /*去除边框*/
    -webkit-appearance: none;/*常用于IOS下移除原生样式*/
    -webkit-tap-highlight-color: rgba(0,0,0,0); /*点击高亮的颜色*/
  }
  /*input:-ms-input-placeholder{
    color: #fff;
  }*/
  .bottom-btn {
    position: absolute;
    z-index: 2;
    text-align: center;
    width: 100%;
    transition: all 1s;
    -moz-transition: all 1s; /* Firefox 4 */
    -webkit-transition: all 1s; /* Safari 和 Chrome */
    -o-transition: all 1s; /* Opera */
  }
  .disclaimer {
    color:#fff;
    width:74%;
    margin:0 auto;
    font-size:10px;
  }
  .center-center{
    position: absolute;
    width:2.25rem;
    margin:auto;
    left:0;
    right:0;
    top:0;
    bottom: 0;
  }
  .mznav {
    display:inline;
    color:#1AABFF;
  }
  .disclaimer label{
    display: inline-block;
    width: 0.09rem;
    height: 0.09rem;
    border-radius: 100%;
    border: 1px solid #1AABFF;
    position: relative;
    top: 1.4px;
  }
  .disclaimer label i{
    color: #468dcc;
    position: absolute;
    font-size:0.09rem;
  }
  .end-mask{
    width: 100%;
    height: 100%;
    background:rgba(0, 0, 0, 0.7);
    position: fixed;
    left: 0;
    top:0;
    z-index: 5;
    display: table;
  }
  .end-box{
    display: table-cell;
    vertical-align: middle;
  }
  .end-content{
    width:80%;
    margin:0 auto;
    position: relative
  }
  .end-box image{
    width:100%;
  }
  .close-icon{
    position: absolute;
    right: 0;
    z-index:999;
    top:-0.075rem;
  }
  .wenan-text{
    color:#fff;
    text-align: center;
    position:absolute;
    width:100%;
    height: 100%;
    top:0;
    left: 0;
  }
  .ok-btn{
    display: inline-block;
    border:1px solid #0FEBFF;
    padding:8px 0.3rem;
    color:#FFF100;
    margin-top: 0.075rem;
  }
  .top{
    position: absolute;
    padding:0 0.12rem;
    width: 100%;
    box-sizing: border-box;
    z-index: 99;
    height: 0.42rem;
    margin-top:0.10rem;
    display: flex;
    align-items: center;
  }
  .bottom-btn{
    width:84%;
    height:0.45rem;
    margin: 0.35rem auto;
    margin-top:0;
    margin-bottom: 0.12rem;
    background:rgba(255,15,88,0.1);
    border-radius:0.22rem;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .bottom-btn img{
    width: 100%;
    height: 100%;
    position: absolute;
    top:0;
    left: 0;
    right: 0;
    bottom: 0;
  }
  .bottom-btn span{
    color:rgba(255,35,113,1);
    font-size: 0.18rem;
  }
  .top .one{
    margin-left: 0;
  }
  .top-item{
    width: 36%;
    height: 100%;
    margin-left: -0.08rem;
    background: url("../../../assets/img/qz/style/topitem.png") center no-repeat;
    background-size: 100% 100%;
    line-height: 0.42rem;
    text-align: center;
    font-size:0.12rem;
    font-weight:500;
    color:rgba(255,255,255,1);
  }
  .top .active{
    background: url("../../../assets/img/qz/style/topact.png") center no-repeat;
    background-size: 100% 100%;
    color:#02F0FF;
  }
  .bg-con{
    padding-top:0.80rem;
    position: absolute;
    z-index:3;
  }
  .m-tips-mask .m-mask-box{
      vertical-align: baseline !important;
      padding-top:60% !important;
  }
</style>
