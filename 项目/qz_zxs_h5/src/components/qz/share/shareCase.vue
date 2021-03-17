<!--
 * @Author: your name
 * @Date: 2020-07-21 09:36:49
 * @LastEditTime: 2020-07-28 11:41:16
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: \qz_admin_centerd:\wamp64\www\qz_zxs_h5\src\components\qz\share\shareCase.vue
-->

<template>
  <div class="shareCase">
    <open-app></open-app>
    <!--中间详情部分-->
    <div>
      <!--图片-->
      <div v-if="detailsdata.images"
           class="div-img">
        <img class="swi-img"
             :src="detailsdata.images[0].url"
             alt="">
      </div>
      <div class="div-img"
           v-else>
        <img class="swi-img"
             v-if="detailsdata.spacelist"
             :src="detailsdata.spacelist[0].images[0].url"
             alt="">
      </div>
      <!--标题-->
      <div class="flex h2 bold">{{detailsdata.title}}</div>
      <!--头像 姓名 发布篇数 阅读量-->
      <div class="flex ali-ite characterMsg">
        <div class="flex mar-lef ali-ite">
          <div class="headr-img mar-rig">
            <img v-if="detailsdata.owner_logo!=''"
                 :src="detailsdata.owner_logo"
                 alt="">
            <img v-else
                 src="../../../assets/img/qz/share/default-logo.png"
                 alt="">
          </div>
          <div class="headr-name col-666"
               v-if="detailsdata.owner_name&&detailsdata.owner_name.length>5">
            {{detailsdata.owner_name.substring(0, 5)}}...
          </div>
          <div class="headr-name col-666"
               v-else>
            {{detailsdata.owner_name}}
          </div>
        </div>
        <div class="flex rig-bor">
          <div class="col-999 font-999">发布</div>
          <div class="bold font-24">&nbsp;{{detailsdata.owner_works_num}}</div>
          <div class="col-999 font-999">篇</div>
        </div>
        <div class="flex">
          <div class="col-999 font-999">阅读</div>
          <div class="bold font-24"
               v-if="detailsdata.owner_views<10000">&nbsp;{{detailsdata.owner_views}}</div>
          <div class="bold font-24"
               v-else>&nbsp;{{detailsdata.owner_views/10000}}w</div>
        </div>
      </div>
      <!--户型风格-->
      <div class="flex spa-aro casestyle">
        <div class="tex-cen"
             v-for="tag of detailsdata.taglist"
             :key="tag.px">
          <div class="col-666 font-999">{{tag.tag_type_name}}</div>
          <div class="fon-siz bold">{{tag.sub_tag_name}}</div>
        </div>
      </div>
      <!--案例简介说明-->
      <div class="mar">
        <div class="case-explain bold">案例简介说明</div>
        <p class="mar-top font-0.13 mar-bot36">{{detailsdata.describe}}</p>
      </div>
      <div>
        <div v-if="detailsdata.faceImages"
             class="div-img">
          <img class="swi-img mar-top"
               v-for="(faceImg,i) of detailsdata.faceImages"
               :src="faceImg.url"
               :key="i"
               alt="">
        </div>
      </div>
      <!--空间-->
      <div v-for="(space,index) of detailsdata.spacelist"
           :key="index">
        <!--空间名称-->
        <div class="mar">
          <h3>{{space.title}}</h3>
          <!--空间描述-->
          <p class="mar-top">{{space.describe}}</p>
        </div>
        <!--空间图片-->
        <div>
          <div class="pic-liv mar-top"
               v-for="(image,i) of space.images"
               :key="i">
            <img :src="image.url"
                 alt="">
          </div>
        </div>
      </div>
      <div>
        <div class="tex-cen mar-bot">
          <el-link type="danger"
                   :underline="false"
                   @click="viewfulltext">查看全文<i class="el-icon-arrow-right"></i></el-link>
        </div>
      </div>
      <div class="mar-bot">
        <!--最热案例-->
        <hotCase></hotCase>
      </div>
    </div>
    <div class="to-app"
         @click="qzOpenApp">
      <button type="button">去APP内查看</button>
    </div>
    <wx-tips :isWechat="isWechat"></wx-tips>
  </div>
</template>

<script>
import qzOpenApp from "@/mixins/qzOpenApp";
import { MessageBox } from 'mint-ui';
import api from '@/api/casedetails'
export default {
  name: 'shareCase',
  mixins: [qzOpenApp],
  components: {
    openApp: () => import('@/components/common/openApp'),
    wxTips: () => import('@/components/common/wxTips'),
    hotCase: () => import('@/components/common/hotCase')
  },
  data () {
    return {
      isWechat: false,
      id: '',
      detailsdata: {}//详情
    }
  },
  created () {
    this.id = this.$route.query.id
    // this.id = 34
    this.detail()
  },
  methods: {
    detail () {
      let params = {
        id: this.id
      }
      api.detail(params).then((res) => {
        if (parseInt(res.data.error_code) === 0) {
          this.detailsdata = res.data.data
          document.title = this.detailsdata.title
        } else {
          this.$message.error(res.data.error_msg)
        }
      })
    },
    //查看全文
    viewfulltext () {
      MessageBox({
        title: '',
        message: '是否打开齐装APP查看全文？',
        showCancelButton: true,
        confirmButtonClass: 'determine-btn'
      }).then(action => {
        if (action === 'confirm') {
          this.qzOpenApp()
          // console.log('确定')
        } else {
          // console.log('取消')
        }
      })
    }
  },
}
</script>

<style lang="scss" scoped>
.shareCase {
  background: #f5f5f5;
  width: 100%;
  box-sizing: border-box;
}
/*公用 */
.bold {
  font-weight: bold;
}
.flex {
  display: flex;
}
.spa-bet {
  justify-content: space-between;
}
.spa-aro {
  justify-content: space-around;
}
.ali-ite {
  align-items: center;
}
.mar-lef {
  margin: 0 0 0 0.15rem;
}
.mar-rig {
  margin: 0 0.08rem 0 0;
}
.tex-cen {
  text-align: center;
  line-height: 0.2rem;
}
.fon-siz {
  font-size: 0.119rem;
}
.mar {
  margin: 0.341rem 0.128rem 0 0.128rem;
}
.mar-top {
  margin-top: 0.1rem;
}
.mar-bot {
  margin-bottom: 0.6rem;
}
/*底部*/
.to-app {
  width: 100%;
  height: 0.512rem;
  background: #fff;
  position: fixed;
  bottom: 0;
  text-align: center;

  button {
    width: 1.707rem;
    height: 0.341rem;
    background: linear-gradient(
      90deg,
      rgba(255, 121, 69, 1),
      rgba(255, 83, 83, 1)
    );
    border-radius: 0.171rem;
    margin-top: 0.085rem;
    font-size: 0.137rem;
    color: #fff;
  }
}
/*头部图片*/
.div-img {
  width: 100%;
  margin-top: -0.1rem;
}
.swi-img {
  width: 100%;
}
/*标题*/
.h2 {
  height: 0.5rem;
  padding: 0.1664rem 0.128rem 0.1749rem 0.128rem;
  align-items: center;
  font-size: 0.187rem;
}
/*头像姓名等信息*/
.characterMsg {
  width: 100%;
}
.headr-img {
  border-radius: 50%;
  width: 0.256rem;
  height: 0.256rem;
}
.headr-img > img {
  border-radius: 50%;
  width: 100%;
  height: 100%;
}
.headr-name {
  font-size: 0.119rem;
}
.col-666 {
  color: #666;
}
.col-999 {
  color: #999;
}
.font-999 {
  font-size: 0.1024rem;
}
/*客厅图片*/
.pic-liv {
  width: 94%;
  height: 2.21rem;
  margin: 0.25rem 2%;
}
.pic-liv > img {
  width: 100%;
  height: 2.21rem;
}
.casestyle {
  box-shadow: 0px 1px 10px 0px rgba(0, 0, 0, 0.07);
  padding: 0.2rem 0;
  margin: 0.341rem 0.128rem;
}
.rig-bor {
  border-right: 1px solid #e4e4e4;
  padding: 0 0.15rem 0 0;
  margin: 0 0.15rem;
}
.case-explain {
  font-size: 0.17rem;
}
.font-0.13 {
  font-size: 0.13rem;
}
.font-24 {
  font-size: 0.1024rem;
}
.mar-bot36 {
  margin-bottom: 36px;
}
</style>
<style>
.determine-btn {
  color: #ff5353 !important;
}
.mint-msgbox-message {
  color: #000;
}
.mint-msgbox-btn {
  font-size: 0.136rem;
}
</style>
