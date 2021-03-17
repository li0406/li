<template>
  <div class="dialog">
    <el-dialog
      title="修改施工信息"
      :visible.sync="dialog.show"
      :close-on-click-modal='false'
      :close-on-press-escape='false'
      width="50%"
    >
      <div class="line"></div>
      <div class="content">
          <el-row :gutter="20">
            <el-col :span="3">
              <h4 class="title">发布人：</h4>
            </el-col>
            <el-col :span="21">
              <div class="left-img">
                <img :src="headimg" alt="">
                <span>{{nickname}}</span>
              </div>
            </el-col>

          </el-row>
          <el-row :gutter="20">
            <el-col :span="3"><h4 class="title">施工阶段：</h4></el-col>
            <el-col :span="21">
              <el-select v-model="value" placeholder="请选择">
                <el-option
                  v-for="item in stepDetailsList"
                  :key="item.id"
                  :label="item.name"
                  :value="item.id">
                </el-option>
              </el-select>
            </el-col>
          </el-row>
        <el-row :gutter="20">
          <el-col :span="3"><h4 class="title">施工详情：</h4></el-col>
          <el-col :span="21">
            <el-input
              type="textarea"
              :rows="6"
              placeholder="施工情况详细描述，如此次完成水电改造等施工细节，施工信息将传达给装修公司和业主"
              v-model="content">
            </el-input>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="3"><h4 class="title">施工现场:</h4></el-col>
          <el-col :span="21" v-if="mediaType === 1">
            <el-row :gutter="20" v-viewer>
              <el-col :span="4" v-for="(item, index) in mediaList" :key="index">
                <div class="list-img">
                  <img :src="item.url_full" alt="">
                  <span class="icon" @click="delImg(index)">x</span>
                </div>
              </el-col>
            </el-row>
          </el-col>
          <el-col :span="21" v-else >
            <div v-for="(item, index) in mediaList" :key="index">
              <video :src="item.url_full" controls width="300" height="200" :poster="item.url_thumb"></video>
              <span class="del—btn" @click="delVideo">删除</span>
            </div>
          </el-col>
          <el-col :span="21" v-if="mediaList.length === 0">
            暂无现场的图片/视频
          </el-col>
        </el-row>
        <el-row :gutter="23">
          <div class="btn-bm">
            <el-button @click="handleCancel">取消</el-button>
            <el-button type="primary" @click="pushHandle">保存</el-button>
          </div>
        </el-row>

      </div>
    </el-dialog>
  </div>
</template>

<script>
import { fetchGetInfoListEdit } from '@/api/decorate'
import Vue from 'vue'
import Viewer from 'v-viewer'
import 'viewerjs/dist/viewer.css'
Vue.use(Viewer)

export default {
  props: {
    dialog: Object,
    orderinfos: Object,
    stepDetailsList: Array
  },
  data() {
    return {
      options: {},
      textarea: '',
      value: '',
      nickname: '',
      headimg: '',
      content: '',
      mediaType: 1,
      mediaList: []
    }
  },
  watch: {
    'dialog.show'(val) {
      // console.log('1231231', this.orderinfos)
      if (val) {
        this.value = this.orderinfos.step
        this.nickname = this.orderinfos.wx_nickname
        this.headimg = this.orderinfos.wx_headimg
        this.content = this.orderinfos.content
        this.mediaType = this.orderinfos.media_type
        this.mediaList = this.orderinfos.media_list
      } else {
        this.companys = []
      }
    }
  },
  methods: {
    // 删除图片
    delImg(index) {
      this.mediaList.splice(index, 1)
    },
    delVideo() {
      // console.log(this.mediaList)
      this.mediaList = []
    },
    // 保存
    pushHandle() {
      if (!this.content) {
        this.$message.warning('请填写施工详情')
        return false
      }
      const params = this.handleArgs()
      fetchGetInfoListEdit(params).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.$message({
            message: '保存成功',
            duration: '500',
            type: 'success'
          })
          this.emitCloseDiaFn('updata')
        } else {
          this.$message.error(res.data.error_msg)
        }
      }).catch(res => {
        this.$message.error('网络异常，请稍后再试')
      })
    },
    // 保存参数
    handleArgs() {
      const queryObj = {
        id: this.orderinfos.id, // Id
        step: this.value, // 施工阶段
        content: this.content // 施工详情
      }
      if (this.orderinfos.media_list.length > 0) {
        queryObj.media_type = this.orderinfos.media_type
        const urlAyyay = []
        this.mediaList.forEach((item) => {
          urlAyyay.push(item.url)
        })
        queryObj.media_urls = urlAyyay.join(',')
      }
      return queryObj
    },
    handleCancel() {
      this.emitCloseDiaFn()
    },
    emitCloseDiaFn(tag) {
      this.$emit('closeDia', tag)
    }
  }
}
</script>

<style scoped>
  el-dialog {
    border: 1px solid #ccc;
  }
  .el-col{
    margin-bottom: 20px;
  }
  .content .title{
    text-align: right;
    line-height: 35px;
    margin: 0;
  }
  .content .left-img{
    font-size: 0;
  }
  .content .left-img img{
    width: 35px;
    height: 35px;
    border-radius: 50%;
    vertical-align: top;
  }
  .content .left-img span{
    font-size: 14px;
    line-height: 35px;
    margin-left: 10px;
  }
  .list-img {
    position: relative;
    text-align: center;
    height: 100px;
  }
  .list-img img{
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  .list-img span{
    position: absolute;
    top: -8px;
    right: -8px;
    display: inline-block;
    width: 16px;
    height: 16px;
    background: #c00;
    border-radius: 50%;
    text-align: center;
    line-height: 16px;
    color: #fff;
    font-size: 16px;
    cursor: pointer;
  }
  .del—btn{
    margin-left: 10px;
    color: #c00;
    cursor: pointer;
  }
  .btn-bm{
    text-align: right;
  }
</style>
