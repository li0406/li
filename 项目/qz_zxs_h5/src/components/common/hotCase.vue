<!--
 * @Author: your name
 * @Date: 2020-07-21 13:23:39
 * @LastEditTime: 2020-07-27 18:36:27
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: \qz_admin_centerd:\wamp64\www\qz_zxs_h5\src\components\qz\components\hotCase.vue
-->
<template>
  <div class="pad">
    <h2 class="h2 font-40 bold">最热案例</h2>
    <div class="div-bor"
         v-for="hotCases of hotCasesList"
         :key="hotCases.id">
      <div>
        <div class="pic-liv mar-top">
          <img :src="hotCases.img_url"
               alt="">
        </div>
      </div>
      <div class="mar-tb font-32 bold">{{hotCases.title}}</div>
      <div class="flex ali-ite spa-aro pad-bot">
        <div class="flex width50 ali-ite">
          <div class="headr-img mar-rig">
            <img :class="[{'bor-rad':hotCases.owner_name!=='官方小齐'}]"
                 v-if="hotCases.owner_logo!=''"
                 :src="hotCases.owner_logo"
                 alt="">
            <img v-else
                 src="../../assets/img/qz/share/default-logo.png"
                 alt="">
          </div>
          <div class="headr-name col-666 font-26"
               v-if="hotCases.owner_name&&hotCases.owner_name.length>5">
            {{hotCases.owner_name.substring(0, 5)}}...
          </div>
          <div class="headr-name col-666 font-26"
               v-else>
            {{hotCases.owner_name}}
          </div>
        </div>
        <div class="width50">
          <div class="col-999 font-24 tex-rig"
               v-if="hotCases.tag_info&&hotCases.tag_info.length>12">
            {{hotCases.tag_info.substring(0, 12)}}...
          </div>
          <div class="col-999 font-24 tex-rig"
               v-else>
            {{hotCases.tag_info}}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/api/casedetails'
export default {
  name: 'hotCase',
  data () {
    return {
      hotCasesList: []//热门案例列表
    }
  },
  created () {
    this.share_hot_cases()//获取整屋案例-分享页-最热案例
  },
  methods: {
    //整屋案例-分享页-最热案例
    share_hot_cases () {
      let params = {}
      api.share_hot_cases(params).then((res) => {
        if (parseInt(res.data.error_code) === 0) {
          this.hotCasesList = res.data.data.list
        } else {
          this.$message.error(res.data.error_msg)
        }
      })
    }
  },
}
</script>

<style lang="scss" scoped>
/*公用 */
.pad {
  padding: 0 0.128rem 0.5rem 0.128rem;
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
}
.fon-siz {
  font-size: 0.155rem;
  font-weight: bold;
}
.mar {
  margin: 0.2rem;
}
.mar-top {
  margin-top: 0.1rem;
}
.pad-bot {
  margin-bottom: 0.128rem;
}
/*标题*/
.h2 {
  height: 0.5rem;
  align-items: center;
}
/*头像姓名等信息*/
.headr-img {
  width: 0.256rem;
  height: 0.256rem;
}
.headr-img > img {
  width: 100%;
  height: 100%;
}
.bor-rad {
  border-radius: 50%;
}
.headr-name {
  align-items: center;
}
.col-666 {
  color: #666;
}
.col-999 {
  color: #999;
}
/*热门案例图片*/
.div-bor {
  border-bottom: 1px #e4e4e4 solid;
  margin-bottom: 0.128rem;
}
.pic-liv {
  width: 100%;
  border-radius: 0.0341rem;
  height: 2.21rem;
  margin: 0 0 0.128rem 0;
}
.pic-liv > img {
  width: 100%;
  border-radius: 0.0341rem;
  height: 2.21rem;
}
/*案例信息*/
.width50 {
  width: 50%;
}
.bold {
  font-weight: bold;
}
.font-40 {
  font-size: 0.17rem;
}
.font-32 {
  font-size: 0.136rem;
}
.font-24 {
  font-size: 0.1024rem;
}
.font-26 {
  font-size: 0.11rem;
}
.tex-rig {
  text-align: right;
}
.mar-tb {
  margin-bottom: 0.085rem;
}
</style>
