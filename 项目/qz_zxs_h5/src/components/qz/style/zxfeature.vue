<template class="p-container">
  <section class="section">
    <div class="zx-bg">
      <img src="../../../assets/img/qz/style/bg_one.jpg" class='bg-one' style="width:100%;"/>
      <div class="fix-tips">
        <div v-for="item in indexTips">
          <div :class="item.show">{{item.text}}</div>
        </div>
        <div :class="[canIgoNext, 'bottom-btn']" @click="toHuxing">
          <img src="../../../assets/img/qz/style/bottom_btn.png" class="center-center">
          <span class="btn-content">开启测试</span>
        </div>
      </div>
    </div>
  </section>
</template>
<script>
    import citySelect from '../../common/citySelect.vue'
    export default {
        components: {
           citySelect
        },
        name: 'zxfeature',
        data () {
            return {
                canIgoNext:'fadeOut',
                indexTips:[
                    {
                        text:'您期望的装修是怎样的？',
                        show:'fadeOut one'
                    },
                    {
                        text:'或许你难以描述出…',
                        show:'fadeOut one'
                    },
                    {
                        text:'但肯定的是：家，装修后的样子就在你的心里。',
                        show:'fadeOut'
                    },
                    {
                        text:'我们做的只是帮你发现它，还原它原本的面貌。',
                        show:'fadeOut'
                    },
                    {
                        text:'简单3步，定制专属装修设计方案，看到未来家的样子。',
                        show:'fadeOut'
                    }
                ]
            }
        },
        computed: {

        },
        created () {
           this.changeHeader()
        },
        mounted () {
            let _this = this
            setTimeout(function(){
                _this.setTipsFadeIn(0)
            }, 1500);
            document.title = '风格测试-寻找未来家的样子'
        },
        methods: {
            setTipsFadeIn(index){
                let _this = this
                if(index == 0 ){
                    _this.indexTips[0].show = 'fadeIn one'
                }
                if(index == 1) {
                    _this.indexTips[1].show = 'fadeIn one'
                }
                if(index >1){
                    _this.indexTips[index].show = 'fadeIn'
                }
                if (index<4) {
                    index++
                    setTimeout(function(){
                        _this.setTipsFadeIn(index)
                    }, 1500);
                } else {
                    setTimeout(function(){
                        _this.canIgoNext = 'fadeIn'
                    }, 1500)
                }
            },
            toHuxing(){
                if(this.canIgoNext == 'fadeOut'){
                    return
                }
                this.$router.push('/huxing')
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
                if (!that.endCall) {
                    setTimeout(function () {
                        that.changeHeader()
                    }, 200)
                }
            }
        }
    }
</script>
<style scoped>
  .zx-bg img{
    display: block;
    position: relative;
    z-index: 1;
  }
  .section{
    height: 100%;
    background: #3A1D94;
  }
  .p-container {
    position: relative;
    height: 100%;
    overflow: hidden;
    background: #3B1E95
  }
  .p-container .bg-one {
    width:100%;
    margin:0;
    padding: 0;
    position: absolute;
    bottom: 0;
    z-index: 1;
  }
  .fix-tips{
    position: absolute;
    width: 90%;
    z-index: 3;
    left: 0.15rem;
    top:27%;
    display: block;
    font-size: 0.1rem;
    color: #0FEBFF;
  }
  .fix-tips>div div{
    margin: 0.07rem 0;
    padding: 0.1rem 0.127rem;
    max-width: 95%;
    min-width: 40%;
    font-size:0.12rem;
    display: inline-block;
    border-radius: 0.16rem;
    background: url("../../../assets/img/qz/style/inputbgone.png") no-repeat center;
    background-size: 100% 100%;
    transition: all 1s;
    -moz-transition: all 1s; /* Firefox 4 */
    -webkit-transition: all 1s; /* Safari 和 Chrome */
    -o-transition: all 1s; /* Opera */
  }
  .fix-tips>div .one{
    background: url("../../../assets/img/qz/style/inputbgg.png") no-repeat center;
    background-size: 100% 100%;
  }
  .fadeOut{
    opacity: 0 !important;
  }
  .fadeIn{
    opacity: 1 !important;
  }
  .bottom-btn {
    position: absolute;
    z-index: 2;
    text-align: center;
    width: 100%;
    margin-top: 0.5rem;
    transition: all 1s;
    -moz-transition: all 1s;
    -webkit-transition: all 1s;
    -o-transition: all 1s;
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
  .btn-content{
    color:#ff5353;
  }
  .bottom-btn{
    width:84%;
    height:0.45rem;
    margin: 0.35rem auto;
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
</style>
