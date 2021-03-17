<template>
  <div class="order-content">
    <div class="content-box">
      <h2>订单信息</h2>
    </div>
    <div class="content-form">
      <el-row>
        <el-col :span="5">
          <div class="item">
            订单类型:
            <span class="ml-10">{{dataDetails.type_name}}</span>
          </div>
          <div class="item">
            业主:
            <span class="ml-10">{{dataDetails.yz_name}}</span>
          </div>
        </el-col>
        <el-col :span="5">
          <div class="item">
            订单编号:
            <span class="ml-10">{{dataDetails.order_type ===  1 ?dataDetails.order_id : '--'}}</span>
          </div>
          <div class="item">
            所在区域:
            <span class="ml-10">{{dataDetails.city_name ? dataDetails.city_name + ' ' + dataDetails.area_name: '--'}}</span>
          </div>
        </el-col>
        <el-col :span="5">
          <div class="item">
            装修公司:
            <span class="ml-10">{{dataDetails.company_id?dataDetails.company_jc+'('+dataDetails.company_id+')' : '--'}}</span>
          </div>
          <div class="item">
            户型:
            <span class="ml-10" v-if="dataDetails.order_type ===  1">{{dataDetails.huxing_name || '--'}}</span>
            <span class="ml-10" v-else>{{dataDetails.huxing_name ? dataDetails.huxing_name+'  '+dataDetails.huxing_other_name : '--'}}</span>
          </div>
        </el-col>
        <el-col :span="5">
          <div class="item">
            签约时间:
            <span class="ml-10">{{dataDetails.qiandan_date}}</span>
          </div>
          <div class="item">
            面积:
            <span class="ml-10">{{dataDetails.mianji?dataDetails.mianji+'m³' :  '--'}}</span>
          </div>
        </el-col>
        <el-col :span="4">
          <div class="item">
            施工编号:
            <span class="ml-10">{{dataDetails.code}}</span>
          </div>
          <div class="item">
            施工二维码: <br>
          </div>
        </el-col>
      </el-row>
      <el-row>
        <el-col :span="20">
          <el-row>
            <el-col :span="6">
              <div class="item">
                签单金额:
                <span class="ml-10" v-if="dataDetails.order_type ===  1">{{dataDetails.qiandan_jine?dataDetails.qiandan_jine+'万元' : '--'}}</span>
                <span class="ml-10" v-else>{{dataDetails.yusuanjt?dataDetails.yusuanjt+'万元' : '--'}}</span>
              </div>
            </el-col>
            <el-col :span="18">
              <div class="item one-text">
                小区名称:
                <span class="ml-10">{{dataDetails.xiaoqu || '--'}}</span>
              </div>
            </el-col>
          </el-row>
          <el-row>
            <el-col :span="24">
              <div class="item">
                地址:
                <span class="ml-10" >{{dataDetails.dz || '--'}}</span>
              </div>
            </el-col>
          </el-row>
        </el-col>
        <el-col :span="4">
          <div class="item">
            <img :src="prefixBase64+qrcode" alt="" width="110" height="110" >
            <span class="down" v-viewer style="position:relative;">
              放大
              <img :src="prefixBase64+qrcode" style="position: absolute;left: 0;top: 0;opacity: 0;cursor: pointer" width="28" height="15" alt="" title="点击放大，显示二维码图片">
              <!--<img :src="downImg" style="position: absolute;left: 0;top: 0;opacity: 0;cursor: pointer" width="28" height="15" alt="" title="点击放大，显示二维码图片">-->
            </span>
            <span  class="down">
              <a v-if="!isFirefox" @click.stop.prevent="handleDownloadQrIMg" title="点在下载，将图片下载">下载</a>
              <a v-if="isFirefox" :href="prefixBase64+qrcode" download="qrcode.png" title="点在下载，将图片下载">下载</a>
            </span>
          </div>
        </el-col>
      </el-row>
    </div>
    <div class="content-box">
      <h2>施工信息</h2>
    </div>
    <div class="content-main">
      <div v-show="isStep">
        <div :class="fixed == true ? 'navBarWrap' :''" ref="topInfo" id="fixedCard" class="top">
          <div class="setp_list">
            <span @click="getInfoListBtn()" :class="stepActiveId?'':'active'">全部</span>
            <span v-for="(item, index) in stepList" @click="getInfoListBtn(item.id)" :key="index" :class="item.id === stepActiveId?'active':''">{{item.name}}</span>
          </div>
        </div>
        <div v-if="fixed" class="top">
          <div class="setp_list">
            <span @click="getInfoListBtn()" :class="stepActiveId?'':'active'">全部</span>
            <span v-for="(item, index) in stepList" @click="getInfoListBtn(item.id)" :key="index" :class="item.id === stepActiveId?'active':''">{{item.name}}</span>
          </div>
        </div>
      </div>
      <div class="list" v-for="(item, index) in stepListInfo" :key="index">
        <div class="list_top">
          <span class="item-name">{{item.step_name}}</span>
          <span class="item-time">{{item.created_date}}</span>
          <div class="fr">
            <el-button type="text" size="small" @click="editHandle(item.id)">修改</el-button>
            <el-button type="text" size="small" @click="deleteHandle(index,stepListInfo)">删除</el-button>
          </div>
        </div>
        <div class="list-conter">
          <div class="left">
            <img :src="item.wx_headimg" alt />
          </div>
          <div class="right">
            <h4 class="title">{{item.wx_nickname}}</h4>
            <p>{{item.content}}</p>
            <div class="img" v-viewer>
              <span v-if="item.media_type === 1">
                <img  v-for="(t, i) in item.media_list" :key="i" :src="t.url_full" alt="" />
              </span>
              <video v-if="item.media_type === 2" :src="item.media_list[0].url_full" controls width="300" height="200" preload="meta" :poster="item.url_thumb"></video>
            </div>
          </div>
        </div>
      </div>
      <div class="icon-load" v-if="isLoading">
        <i class="el-icon-loading"></i>
      </div>
      <!--<div class="list-no" v-if="!isLoad">
        没有数据了
      </div>-->
      <div class="list-no" v-if="stepListInfo.length === 0 && !isLoad">
        暂无数据
      </div>
    </div>
    <!-- 编辑弹框 -->
    <dialogVisit :dialog="dialog" :orderinfos="orderinfos" :stepDetailsList="stepDetailsList" @closeDia="closeDialog"/>
    <div class="up" v-if="isUpBtn" @click="upBtn">
      <i class="el-icon-arrow-up"></i>
    </div>
  </div>
</template>

<script type="es6">
/* 弹窗 */
import dialogVisit from './components/dialogVisit'

import { fetchGetListDetail, fetchGetInfoList, fetchGetInfoListqrcode, fetchGetInfoListDelete, fetchGetInfoDetail } from '@/api/decorate'

import Vue from 'vue'
import Viewer from 'v-viewer'
import 'viewerjs/dist/viewer.css'
Vue.use(Viewer)
Viewer.setDefaults({
  Options: { 'inline': true, 'button': true, 'navbar': true, 'title': true, 'toolbar': true, 'tooltip': true, 'movable': true, 'zoomable': true, 'rotatable': true, 'scalable': true, 'transition': true, 'fullscreen': true, 'keyboard': true, 'url': 'data-source' }
})
export default {
  name: 'DecorateDetails',
  components: {
    dialogVisit
  },
  data() {
    return {
      dataDetails: {}, // 详情数据
      stepList: [], // 装修阶段
      isStep: true, // 是否显示全部
      stepDetailsList: [], // 装修阶段
      orderinfos: {}, // 编辑数据
      stepListInfo: [], // 装修阶段内容
      id: '', // 当前id
      activeClass: 0, // 阶段列表选中样式
      offset: 30, // 滚动加载距离
      offsetTop: 0, // 阶段列表距顶距离
      offsetHeight: 0, // 阶段列表本身高度
      scrollTop: 0, // 距顶部高度
      fixed: false, // 阶段列表固定顶部
      isUpBtn: false, // 放回顶部按钮
      qrcode: '', // 二维码
      prefixBase64: 'data:image/png;base64,', // base64前缀
      isFirefox: false, // 火狐浏览器
      loadPage: 1, // 动态加载页数
      loadPageSize: 5, // 动态加载条数
      isLoad: true, // 是否还有数据
      isLoading: true, // 是否获取接口
      stepActiveId: 0,
      timer: '',
      // 弹窗
      dialog: {
        show: false
      }
    }
  },
  created() {
    this.id = this.$route.params.id
    this.getDetail()
    this.getInfoList()
  },
  mounted() {
    // 事件监听滚动条
    window.addEventListener('scroll', this.watchScroll)
    // 判断浏览器是否是火狐
    if (navigator.userAgent.indexOf('Firefox') > 0) {
      this.isFirefox = true
    }
    this.$nextTick(() => {
      // 需吸顶元素 距 离浏览器顶端的高度
      const height = document.getElementById('fixedCard')
      this.offsetTop = height.offsetTop + 90
    })
  },
  beforeDestroy() {
    clearTimeout(this.timer)
  },
  destroyed() {
    // 移除监听
    window.removeEventListener('scroll', this.watchScroll)
  },
  methods: {
    // 页面滚动事件
    watchScroll() {
      // 获取滚动条卷去的高度
      const scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop
      this.scrollTop = scrollTop
      // 获取整个网页的高的
      const pageHeight = document.querySelector('html').scrollHeight
      // 获取可视区域高度
      const windowHeight = window.innerHeight || document.documentElement.clientHeight
      // 高度
      const scrollBottomHeight = pageHeight - scrollTop - windowHeight
      //  当滚动超过  时，实现吸顶效果
      const isTop = this.offsetTop
      if (scrollTop > isTop) {
        this.fixed = true
      } else {
        this.fixed = false
      }
      if (scrollBottomHeight < this.offset) {
        if (this.isLoading) {
          if (this.isLoad) {
            this.loadPage++
            this.getInfoList()
          }
          this.isLoading = false
        }
      }
      // 显示放回顶部
      if (scrollTop > windowHeight) {
        this.isUpBtn = true
      } else {
        this.isUpBtn = false
      }
    },
    // 无数据跳转到列表页
    out() {
      this.$router.push({
        path: '/allOrder/decorate'
      })
    },
    // 获取数据
    getDetail() {
      fetchGetListDetail({
        id: this.id
      }).then(res => {
        // console.log('详情', res)
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.dataDetails = res.data.data.info
          this.stepList = res.data.data.step_list
          if (this.stepList.length < 2) {
            this.isStep = false
          }
          this.getInfoListQrcode()
        } else {
          this.$message({
            message: '此订单施工信息已删除，请查看其它订单施工信息',
            type: 'warning'
          })
          this.timer = setTimeout(this.out, 3000)
        }
      })
    },
    // 获取施工信息
    getInfoList() {
      const query = {}
      const stepActiveId = this.stepActiveId
      query.live_id = this.id
      query.limit = this.loadPageSize
      query.page = this.loadPage
      if (stepActiveId) {
        query.step = stepActiveId
      }
      fetchGetInfoList(query).then(res => {
        const loadPageSize = this.loadPageSize
        // console.log('施工阶段', res)
        if (res.data.data.list.length === 0) {
          this.isLoad = false
          this.isLoading = false
        } else if (res.data.data.list.length < loadPageSize) {
          this.stepListInfo = this.stepListInfo.concat(res.data.data.list)
          this.isLoad = false
          this.isLoading = false
        } else {
          this.stepListInfo = this.stepListInfo.concat(res.data.data.list)
          this.isLoading = true
        }
      })
    },
    // 获取信息按钮
    getInfoListBtn(value) {
      const stepActiveId = this.stepActiveId
      if (value !== stepActiveId) {
        this.loadPage = 1
        this.isLoad = true
        this.isLoading = true
        this.stepActiveId = value
        this.stepListInfo = []
        this.getInfoList()
      }
    },
    // 获取二维码
    getInfoListQrcode() {
      fetchGetInfoListqrcode({
        live_code: this.dataDetails.code
      }).then(res => {
        // console.log('二维码', res)
        this.qrcode = res.data.data
      })
    },
    // 点击下载图片二维码
    handleDownloadQrIMg() {
      // 这里是获取到的图片base64编码,这里只是个例子哈，要自行编码图片替换这里才能测试看到效果
      const imgUrl = this.prefixBase64 + this.qrcode
      // const imgUrl = this.downImg
      // 如果浏览器支持msSaveOrOpenBlob方法（也就是使用IE浏览器的时候），那么调用该方法去下载图片
      if (window.navigator.msSaveOrOpenBlob) {
        const bstr = atob(imgUrl.split(',')[1])
        let n = bstr.length
        const u8arr = new Uint8Array(n)
        while (n--) {
          u8arr[n] = bstr.charCodeAt(n)
        }
        const blob = new Blob([u8arr])
        window.navigator.msSaveOrOpenBlob(blob, 'qrcode.png')
      } else {
        // 这里就按照chrome等新版浏览器来处理
        const a = document.createElement('a')
        a.href = imgUrl
        a.setAttribute('download', 'qrcode.png')
        a.click()
      }
    },
    // 修改
    editHandle(val) {
      fetchGetInfoDetail({ id: val }).then(res => {
        // console.log('单个详情', res)
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.orderinfos = res.data.data.info
          this.stepDetailsList = res.data.data.step_list
          this.dialog.show = true
        }
      })
    },
    // 删除
    deleteHandle(index, rows) {
      const id = rows[index].id
      this.$confirm('确认删除此条施工信息？', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      })
        .then(() => {
          fetchGetInfoListDelete({ id: id }).then(res => {
            // console.log(res)
            if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
              rows.splice(index, 1)
              this.$message({
                type: 'success',
                message: '删除成功!',
                duration: '500'
              })
            }
          })
        })
        .catch(() => {
          this.$message({
            type: 'info',
            duration: '500',
            message: '已取消删除'
          })
        })
    },
    // 关闭弹窗
    closeDialog(tag) {
      this.dialog.show = false
      if (tag === 'updata') {
        this.stepListInfo = []
        this.loadPage = 1
        this.isLoad = true
        this.getInfoList()
      }
    },
    // 返回顶部
    upBtn() {
      this.isUpBtn = false
      const that = this
      const timer = setInterval(() => {
        const ispeed = Math.floor(-that.scrollTop / 5)
        document.documentElement.scrollTop = document.body.scrollTop = that.scrollTop + ispeed
        if (that.scrollTop === 0) {
          clearInterval(timer)
        }
      }, 16)
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss">
.navBarWrap {
  position: fixed;
  top: 0;
  z-index: 999;
  width: 100%;
}

.order-content {
  padding: 10px 15px;

  .search-box h2,
  .content-box h2 {
    font-size: 16px;
    font-weight: normal;
    color: #666;
  }

  .content-form,
  .content-main {
    border-top: 3px solid #d2d6de;
    border-radius: 3px;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
    /*padding: 5px 15px;*/
    background: #fff;
  }

  .content-form {
    padding: 5px 15px;
    .item {
      min-height: 36px;
      line-height: 36px;
      font-size: 14px;
      color: #666;
      .down{
        color: #3a8ee6;
        a{
          color: #3a8ee6;
        }
      }
      .ml-10 {
        margin-left: 10px;
      }
    }
    .one-text{
      display: block;
      width: 100%;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
  }

  .content-main {
    padding: 5px 0;
    .top {
      background: #fff;
      line-height: 40px;
      font-size: 16px;
      border-bottom: 1px solid #ccc;
      padding: 0 15px;
      .fr {
        float: right;
      }
    }
    .setp_list{
      span{
        margin-right: 10px;
        font-size: 14px;
        color: #333;
        cursor: pointer;
        padding: 3px 5px;
        border: 1px solid #ccc;
        border-radius: 5px;
      }
      span:hover{
        color: #3a8ee6;
        border-color: #3a8ee6;
      }
      .active{
        color: #3a8ee6;
        border-color: #3a8ee6;
      }
    }
    .list {
      padding: 0 15px;
      .list_top {
        font-size: 14px;
        line-height: 40px;

        .item-name {
          font-weight: bold;
          margin-right: 5px;
          color: #333333;
        }
        .item-time {
          color: #999999;
        }
      }
      .list-conter {
        margin-bottom: 10px;
        .left {
          float: left;
          margin: 10px 0;
          img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
          }
        }
        .right {
          margin-left: 40px;
          border-bottom: 1px solid #e3e3e3;
          .title {
            font-size: 14px;
            line-height: 30px;
            margin: 0;
            color: #666;
          }
          p {
            margin: 0 0 10px;
            line-height: 20px;
            font-size: 14px;
            color: #666;
          }
          .img {
            -webkit-display: flex;
            -moz-display: flex;
            -ms-display: flex;
            -o-display: flex;
            -khtml-display: flex;
            display: flex;
            flex-wrap:wrap;
            justify-content: flex-start;
            align-content: flex-start;
            img {
              width: 145px;
              height: 100px;
              margin: 0 10px 10px 0;
              object-fit: cover;
            }
            video{
              background: #000;
              margin-bottom: 10px;
            }
          }
        }
      }
    }
    .icon-load{
      font-size: 30px;
      text-align: center;
      margin: 10px;
    }
    .list-no{
      text-align: center;
      font-size: 14px;
      color: #333;
      padding-left: 15px;
      line-height: 10;
      color: #999;
    }
  }
}
.dia_table {
  width: 100%;
}

.dia_table td {
  line-height: 2.5;
}

.dialog-title span {
  margin-top: 35px;
  font-size: 14px;
}

.dia_table input {
  padding: 5px 10px;
  border: 1px solid #d7d7d7;
}

.up{
  width: 24px;
  height: 24px;
  position: fixed;
  bottom: 30px;
  right: 50px;
  border-radius: 50%;
  border:1px solid #666;
  font-size: 18px;
  line-height: 24px;
  color: #666;
  text-align: center;
  cursor: pointer;
  background: #fff;
}
</style>
