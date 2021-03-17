<template>
  <section>
    <template v-if="!noData">
      <div class="banner-item">
        <img :src="image_path" />
      </div>
      <div class="body-item">
        <div class="content-title">{{title}}</div>
        <ul class="hydropower">
          <li v-for="(val,index) in contentList" :key="index">{{val}}</li>
        </ul>
        <div class="content" v-html="content"></div>
      </div>
    </template>
    <template v-if="noData">
      <div class="no-data">
        <img src="../../assets/img/bkzn/nodata.png">
        <p>这里啥都木有, 换个地儿瞅瞅 ~ _ ~ </p>
      </div>
    </template>
  </section>
</template>
<script>
import { gonglue } from '@/api/api'
export default {
  name: 'gonglue',
  data () {
    return {
      contentList: [],
      id: '',
      title: '',
      image_path: '',
      content: '',
      noData: false
    }
  },
  mounted () {
    var that = this
    that.id = that.$route.query.id
    if (that.id) {
      gonglue(that.id).then((res) => {
        if (res.data.error_code === 0) {
          that.title = res.data.data.title
          that.image_path = res.data.data.image_path
          that.content = res.data.data.content
          res.data.data.tags.map(val => {
            that.contentList.push(val.tag_name)
            return that.contentList
          })
        } else {
          that.noData = true
        }
      })
    }
  }
}
</script>
<style scoped>
  a{
    display: block;
    width:100%;
    height: 100%;
    user-select: none;
    -webkit-user-select: none;
    -webkit-touch-callout: none;
  }
  img{
    vertical-align: middle;
    width:100%;
  }
  .banner-item{
    width:100%;
  }
  .body-item{
    padding:0 0.13rem;
  }
  .content-title{
    font-weight: bold;
    color:#333;
    font-size:0.14rem;
    padding-right:0.02rem;
    margin-top:0.2rem;
    margin-bottom: 0.077rem;
    word-break:break-all;
  }
  .hydropower{
    overflow: hidden;
  }
  .hydropower li{
    display: inline-block;
    padding: 0.03rem 0.08rem;
    border:1px solid #999;
    border-radius:0.023rem;
    text-align:center;
    font-weight:500;
    color:#999;
    margin-bottom:0.12rem;
    margin-right: 0.1rem;
    font-size:14px;
  }
  .content{
    padding:0 1px;
    margin-bottom:0.22rem;
    overflow: hidden;
    word-break: break-all;
    line-height: 2;
  }
  .content >>> img{
    max-width:100%;
    border-radius: 10px;
  }
  .no-data {
    height: 100%;
    padding:0.3rem 0px;
    background: #F7F7F7;
    color:#999999;
    text-align: center;
    font-size: 0.13rem;
  }
  .no-data img{
    width: 61.3333%;
  }
  .no-data p{
    padding:0.1rem;
  }
</style>
