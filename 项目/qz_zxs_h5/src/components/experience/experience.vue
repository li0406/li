<template>
  <section>
     <div class="bk-banner">
        <img src="../../assets/img/bkzn/bkzn.png" alt="">
        <div class="bk-table">
          <div class="bk-table-cell">
              <div class="articel-title">{{articleData.title}}</div>
              <p class="articel-title-fbt">{{articleData.outline}}</p>
          </div>
        </div>
      </div>
    <template v-if="!noData">
      <div class="mt-spinner" v-if="load">
        <mt-spinner type="fading-circle" :size="60" color="black"></mt-spinner>
      </div>
      <div class="point-list">
        <template v-for="(item,index) in articleData.list">
          <div class="list-item" :key="index" >
            <div class="item-title" @click="openDetail(item,index)">
              <p>{{item.title}}</p>
              <i :class="item.hide?'fa fa-angle-up':'fa fa-angle-down'"></i>
            </div>
            <div class="articel-detail" v-show="item.hide" v-html="item.content" style="white-space:pre-wrap">
            </div>
          </div>
        </template>
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
import { experience } from '@/api/api'
export default {
  name: 'experience',
  data () {
    return {
      articleData: {
      },
      id: '',
      noData: false,
      load: false
    }
  },
  mounted () {
    var that = this
    that.id = that.$route.query.id
    if (that.id) {
      experience(that.id).then((res) => {
        that.load = false
        if (res.data.error_code === 0) {
          for (let i = 0; i < res.data.data.list.length; i++) {
            res.data.data.list[i].hide = true
          }
          that.articleData = {
            title: res.data.data.title,
            outline: res.data.data.outline,
            list: res.data.data.list
          }
        }
      })
    }
  },
  methods: {
    openDetail (item, index) {
      item.hide = !item.hide
    }
  }
}
</script>
<style scoped>
  section{
    height: 100%;
  }
  .bk-banner {
    position: relative;
  }
  .bk-banner img{
    width:100%;
  }
  .bk-table{
    display: table;
    position:absolute;
    margin:auto;
    top:0px;
    bottom:0px;
    width:100%;
    color:#fff;
    vertical-align: middle;
  }
  .bk-table-cell{
    text-align: center;
  }
  .articel-title{
    font-size: 0.16rem;
    padding-bottom: 0.07rem;
  }
  .articel-title-fbt{
    font-size: 0.13rem;
    width:70%;
    height: 0.34rem;
    line-height: 0.17rem;
    margin: 0px auto;
    text-align: center;
    -webkit-line-clamp: 2;
    overflow: hidden;
  }
  .item-title{
    overflow: hidden;
    font-weight: bold;
    padding:8px;
    background:#F7F7F7;
    border-bottom: 2px solid white;
  }
  .point-list{
    margin-top: 0.1rem;
  }
  .item-title p{
    float: left;
    color:#333;
    width:90%;
    overflow: hidden;
    text-overflow:ellipsis;
    white-space: nowrap;
  }
  .item-title i{
    float: right;
    color:#999;
    font-size: 18px;
    margin-top: 3px;
  }
  .articel-detail{
    margin:0px 0.1rem;
    overflow: hidden;
    padding:0.05rem 0px;
    word-break:break-all;
    color:#999 !important;
    text-align: justify;
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
   .mt-spinner{
     position: fixed;
     left: 0px;
     right: 0px;
     top:0px;
     bottom:0px;
     margin:auto;
     width: 60px;
     height: 60px;
   }
</style>
