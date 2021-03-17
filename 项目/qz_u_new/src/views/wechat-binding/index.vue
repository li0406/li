<template>
  <div class="wechat-binding">
      <qz-card :title="title" divider>
          <div class="wechat-div">
              <div v-if="isBinding">
                  <div class="font24 wechat-title">业主订单消息 装企刻不容缓</div>
                  <div class="col999 font14 wechat-subtitle">缩短等待时间 提升成单效率</div>
                  <div>已绑定微信号</div>
                  <div class="flex wechat-msg">
                      <img class="wechat-header"
                      :src="wechatList[0].img" alt="">
                      <div class="wechat-namedate">
                          <div class="font16">{{wechatList[0].nickname}}</div>
                          <div class="font12">{{wechatList[0].bind_date}}</div>
                      </div>
                      <div class="colf53642 wechat-relieve font14 point" @click="relieveBinding()">解除绑定</div>
                  </div>
              </div>
              <div class="mb flex" v-else>
                  <div>
                      <div class="font24 wechat-title">业主订单消息 装企刻不容缓</div>
                      <div class="col999 font14 wechat-subtitle">缩短等待时间 提升成单效率</div>
                  </div>
                  <div class="font12 flex qrCode-div">
                      <img v-if="wechatQrCodeUrl" class="qrCode"
                      :src="wechatQrCodeUrl" oncontextmenu="return false;" alt="二维码">
                      <img v-else
                      src="https://staticqn.qizuang.com/custom/20200918/FuwqwiEdqRe337jmBx_x0yvaZ0C7"
                      oncontextmenu="return false;" alt="">
                      <div class="qrCode-msg">
                          <div class="col2DABFF point qrCode-refresh" @click="showBindQrcode()">刷新</div>
                          <div class="col999 qrCode-5min">使用微信扫码绑定，二维码有效期5min</div>
                      </div>
                  </div>
              </div>
              <div class="wechat-sort">绑定流程示意</div>
              <div class="flex wechar-guide">
                <div class="wechat-img-div">
                    <img class="wechat-img"
                    src="https://staticqn.qizuang.com/custom/20200916/FkSKV3zGbQ9-LQpuZNh0sYmm_T7P" alt="">
                    <div class="col999 explain font12">打开手机微信APP,打开扫一扫功能</div>
                </div>
                <i class="el-icon-d-arrow-right wechat-icon"></i>
                <div class="wechat-img-div">
                    <img class="wechat-img"
                    src="https://staticqn.qizuang.com/custom/20200916/FqIwzpsQUpeltVmy-FKfzhOlrbPC" alt="">
                    <div class="col999 explain font12">关注公众号</div>
                </div>
                <i class="el-icon-d-arrow-right wechat-icon"></i>
                <div class="wechat-img-div">
                    <img class="wechat-img"
                    src="https://staticqn.qizuang.com/custom/20200916/FjDK5Vur78nA6nFq25RtR0T9U1Ul" alt="">
                    <div class="col999 explain font12">绑定微信成功</div>
                </div>
            </div>
          </div>
      </qz-card>
  </div>
</template>

<script>
import api from '@/api/order';

export default {
    components:{
        QzCard:()=>import('@/components/card.vue')
    },
    data(){
        return{
            title:'微信绑定通知',
            isBinding:false,
            wechatList:[],//  微信绑定列表
            wechatQrCodeUrl:'',// 微信二维码地址
            timer:''
        }
    },
    created(){
      this.wechat()
    },
    methods:{
      // 订单管理-微信接收订单-绑定列表
      wechat() {
        const params = {}
        api.wechat(params).then((res)=>{
          if (res.data.error_code === 0) {
            this.wechatList=res.data.data.list
            if(res.data.data.list&&res.data.data.list.length>0){
              this.isBinding=true
              this.title='微信绑定通知(已绑定)'
            }else{
              this.isBinding=false
              this.title='微信绑定通知'
              this.showBindQrcode()
            }
          } else {
            this.$message.error(res.data.error_msg);
          }
        }).catch((err)=>{
          this.$message.error(err);
        })
      },
      // 订单管理-微信接收订单-删除
      wechatDel() {
        const params = {
          open_id:this.wechatList[0].open_id
        }
        api.wechatDel(params).then((res)=>{
          if (res.data.error_code === 0) {
            this.$message({
              type: 'success',
              message: '解除绑定成功!'
            });
            this.wechat()
          } else {
            this.$message.error(res.data.error_msg);
          }
        }).catch((err)=>{
          this.$message.error(err);
        })
      },
      // 订单管理-微信接收订单-生成绑定二维码
      showBindQrcode() {
        const params = {}
        api.showBindQrcode(params).then((res)=>{
          if (res.data.error_code === 0) {
            this.wechatQrCodeUrl = res.data.data.url;
            this.isBinding=false
            this.title='微信绑定通知'
            this.timer = window.setInterval(async () => {
              this.checkBindWechat()
            }, 1000);
          } else {
            this.$message.error(res.data.error_msg);
          }
        }).catch((err)=>{
          this.$message.error(err);
        })
      },
      // 订单管理-微信-验证用户扫描绑定二维码
      checkBindWechat(){
        const params = {}
        api.checkBindWechat(params).then((res)=>{
          if (res.data.error_code === 0) {
            // 扫描并点击确认
              if (res.data.error_code === 0) {
                this.wechat()
                this.$message({
                  type: 'success',
                  message: '绑定成功!'
                });
                clearInterval(this.timer);
              }
              // 二维码过期
              if (res.data.error_code === 4000600) {
                this.$message.info('二维码过期');
                this.wechatQrCodeUrl = '';
                clearInterval(this.timer);
              }
          }
        }).catch((err)=>{
          this.$message.error(err);
        })
      },
      relieveBinding(){
          this.$confirm('确认解除绑定?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            this.wechatDel()
          }).catch(() => {

          });
      }
    }
}
</script>

<style scoped>
.wechat-binding{
    width: 100%;
    height: 100%;
    background-color: #fff;
}
.wechat-div{
    width: 900px;
    margin: 0 auto;
}
.wechat-img{
    width: 174px;
    height: 192px;
}
.flex{
    display: flex;
}
.font24{
    font-size: 24px;
}
.font12{
    font-size: 12px;
}
.font14{
    font-size: 14px;
}
.font16{
    font-size: 16px;
}
.col999{
    color: #999;
}
.wechar-guide{
    align-items: center;
}
.wechat-icon{
    margin: 0 20px;
}
.explain{
    margin: 20px 0 0 0;
}
.wechat-title{
    margin: 60px 0 0 0;
}
.wechat-subtitle{
    margin: 5px 0 30px 0;
}
.wechat-sort{
    margin: 40px 0;
}
.wechat-msg{
    width: 303px;
    height: 82px;
    margin: 10px 0 0 0;
    border: 1px solid rgba(229, 229, 229, 1);
}
.wechat-header{
    width: 52px;
    height: 52px;
    margin: 15px;
    border-radius: 50%;
}
.colf53642{
    color: #f53642;
}
.wechat-relieve{
    height: 82px;
    padding: 0 10px 0 0;
    line-height: 82px;
    text-align: right;
}
.wechat-namedate{
    width: 150px;
    height: 40px;
    margin: 18px 0 0 0;
}
.point{
    cursor: pointer;
}
.mb{
    margin: 0 0 100px 0;
}
.qrCode{
    width: 176px;
    height: 176px;
}
.qrCode-div{
    margin: 0 0 0 120px;
}
.qrCode-msg{
    position: relative;
    width: 300px;
}
.qrCode-refresh{
    position: absolute;
    bottom: 20px;
    left: 20px;
}
.qrCode-5min{
    position: absolute;
    bottom: 0;
    left: 20px;
}
.wechat-img-div{
    text-align: center;
}
.col2DABFF{
    color: #2DABFF;
}
</style>
