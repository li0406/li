<template>
  <div class="add-menu">
    <div v-loading="loading" class="main">
      <el-form ref="ruleForm" :model="ruleForm" :rules="rules" class="demo-ruleForm">
        <el-form-item prop="name" class="narrow">
          <div class="minifonts"><span>*</span>名称:</div>
          <el-input v-model="ruleForm.name" placeholder="菜单名称" @blur="blurHan($event)" class="mb5"/>
        </el-form-item>
        <el-form-item prop="link" class="narrow pather">
          <div class="minifonts"><span>*</span>路径:</div>
          <el-input v-model="ruleForm.link" placeholder="请输入路径" class="mb5 path" :disabled="!infoId"/>
        </el-form-item>
        <el-form-item prop="parentid" class="narrow">
          <div class="minifonts"><span>*</span>父级菜单:</div>
          <el-select v-model="ruleForm.parentid" placeholder="请选择"  class="mb5" :disabled="!infoId">
            <el-option value="1" label="工作台"></el-option>
            <el-option value="2" label="统计"></el-option>
          </el-select>
        </el-form-item>
        <div class='minilink' v-if="infoId">
          <div class="minifonts"><span>*</span>目标功能:</div>
          <el-form-item class="link mr">
            <el-select
                    v-model="laForm.nodeidA"
                    placeholder="请选择"
                    filterable
                    @change="getCityHandle(laForm.nodeidA)"
            >
              <el-option
                      v-for="item in citys"
                      :key="item.name"
                      :value="item.nodeidA"
                      :label="item.name"
              />
            </el-select>
          </el-form-item>
          <el-form-item class="link mr" v-if="twoSel">
            <el-select
                    v-model="laForm.nodeidB"
                    placeholder="请选择"
                    filterable
                    @change="getThreeHandle(laForm.nodeidB)"
            >
              <el-option
                      v-for="item in qx"
                      :key="item.name"
                      :value="item.nodeidB"
                      :label="item.value"
              />
            </el-select>
          </el-form-item>
          <el-form-item class="link mr" v-if="threeSel">
            <el-select
                    v-model="laForm.nodeidC"
                    placeholder="请选择"
                    filterable
                    @change="getFourHandle(laForm.nodeidC)"
            >
              <el-option
                      v-for="item in dq"
                      :key="item.name"
                      :value="item.nodeidC"
                      :label="item.value"
              />
            </el-select>
          </el-form-item>
          <br>
        </div>
        <div class="infoId"  v-if="infoIdThree">请选择目标功能 </div>
        <div class='minilink' v-if="!infoId">
          <div class="minifonts"><span>*</span>目标功能:</div>
          <el-select v-for="(item, index) in system_menu" :value="item" :key="index" placeholder="请选择"  class="mb5 sysRight" :disabled="!infoId">
          </el-select>
        </div>
        <el-form-item v-loading="picLoading">
          <div class="minifonts"><span>*</span>图标:</div>
          <el-upload
                  class="avatar-uploader"
                  :action="upload_img_url"
                  :limit="1"
                  :data="uploadExtendData"
                  :headers="uploadContentType"
                  :on-success="handleUploadSuccess"
                  :before-upload="beforeAction"
                  :on-preview="handlePreview"
                  :on-remove="handleRemove"
                  :file-list="imgList">
            <img v-if="uploadedImg" :src="uploadedImg" class="avatar">
            <i v-else class="el-icon-plus avatar-uploader-icon"></i>
          </el-upload>
          <div class="el-upload__tip">图片尺寸为80*80px，图片大小不超过2M</div>
        </el-form-item>
        <div class="infoId" v-if="infoIdTu">请上传正确尺寸的图标</div>
        <el-form-item prop="px" class="narrow">
          <div class="minifonts">排序:</div>
          <el-input v-model="ruleForm.px" placeholder="未填写默认为0" @blur="blurText($event)" class="mb5"/>
          <div class="pxlass">排序值越大,前台越靠前</div>
        </el-form-item>
        <el-form-item class="narrow">
          <div class="minifonts">状态:</div>
          <el-tooltip :content="'Switch value: ' + valueEnable" placement="top">
            <el-switch
              @change="test"
              v-model="valueEnable"
              active-value="1"
              inactive-value="2"
              active-text="开启"
              inactive-text="禁用">
            </el-switch>
          </el-tooltip>
        </el-form-item>
        <el-form-item prop="remark">
          <div class="minifonts">描述:</div>
            <el-input
              v-model="ruleForm.remark"
              type="textarea"
              :rows="2"
              placeholder="菜单描述..."
              class="mb20"
            />
        </el-form-item>
        <el-button type="primary" @click="submitForm('ruleForm')">提交</el-button>
      </el-form>
    </div>
  </div>
</template>

<script>
import { fetchMiniAdd, fetchMiniDetail, fetchMiniSys } from '@/api/miniMenu'
export default {
  name: 'AddMenu',
  data() {
    return {
      ruleForm: {
        name: '',
        link: '',
        parentid: '1',
        px: '',
        enabled: 1,
        icon: '',
        remark: '',
        nodeid: '',
        version: ''
      },
      nodeidAdd: '',
      laForm: {
        nodeidA: '',
        nodeidB: '',
        nodeidC: ''
      },
      infoId: true,
      twoSel: false,
      threeSel: false,
      imgList: [],
      system_menu: [],
      valueEnable: '1',
      enValue: true,
      uploadedImg: '',
      dialogVisible: false,
      uploadExtendData: {
        prefix: 'yyzz'
      },
      uploadContentType: {
        'token': localStorage.getItem('token')
      },
      citys: [],
      qx: [],
      dq: [],
      oneVal: '',
      rules: {
        name: [
          { required: true, message: '请输入菜单名称', trigger: 'blur' },
          { min: 1, max: 6, message: '最长输入六个中文', trigger: 'blur' }
        ],
        link: [
          { required: true, message: '请输入路径', trigger: 'blur' }
        ],
        parentid: [
          { required: true, message: '请选择父级菜单', trigger: 'change' }
        ],
        px: [
          { min: 0, max: 8, message: '最长输入8个字符', trigger: 'blur' }
        ]
      },
      loading: false,
      picLoading: false,
      infoIdThree: false,
      infoIdTu: false,
      visitedV: {
        fullPath: '/miniMenu/AddMenu/',
        hash: '',
        matched: Array(2),
        meta: Object,
        name: '菜单新增/编辑',
        params: Object,
        path: '/miniMenu/AddMenu/',
        query: Object,
        title: '菜单新增/编辑'
      },
      upload_img_url: this.$qzconfig.base_api + '/uploads/upimg'
    }
  },
  created() {
    this.getDetail()
  },
  computed: {
    visitedViews() {
      return this.$store.state.tagsView.visitedViews
    },
    routes() {
      return this.$store.state.permission.routes
    }
  },
  methods: {
    closeSelectedTag(view) {
      this.$store.dispatch('delView', view).then(({ visitedViews }) => {
        if (this.isActive(view)) {
          this.toLastView(visitedViews)
        }
      })
    },
    // 获取详情
    getDetail() {
      const queryId = { id: this.$route.query.id }
      fetchMiniDetail(queryId).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          // 下拉
          this.citys = []
          const cityAll = res.data.data.system_menu_list
          cityAll.forEach((item, index) => {
            item.nodeidA = item.id
            this.citys.push(item)
          })
          // 其他
          if (this.$route.query.id) {
            this.infoId = false
            const otherData = res.data.data.detail
            this.ruleForm.name = otherData.name
            this.ruleForm.enabled = otherData.enabled
            this.ruleForm.link = otherData.link
            this.ruleForm.parentid = String(otherData.parentid)
            this.nodeidAdd = otherData.system_menu_nodeid
            this.ruleForm.icon = otherData.icon
            this.imgList.push({
              url: this.$qzconfig.oss_img_host + otherData.icon
            })
            this.uploadedImg = this.$qzconfig.oss_img_host + otherData.icon
            this.ruleForm.px = String(otherData.px)
            this.ruleForm.enabled = otherData.enabled
            this.valueEnable = String(otherData.enabled)
            this.ruleForm.remark = otherData.remark
            this.ruleForm.version = otherData.version
            this.system_menu = otherData.system_menu
            this.infoIdThree = false
          }
        } else {
          this.citys = []
          this.$message.warning('未查询到符合要求的数据')
        }
      })
    },
    // 选择下拉列表
    getCityHandle(val) {
      this.laForm.nodeidB = ''
      this.laForm.nodeidC = ''
      this.oneVal = val
      const query = { version: val, parentid: '' }
      fetchMiniSys(query).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.qx = res.data.data.list
          this.qx = []
          const qxAll = res.data.data.list
          const infoNum = res.data.data.count
          if (parseInt(infoNum) === 0) {
            this.twoSel = false
            this.threeSel = false
          } else {
            this.twoSel = true
          }
          qxAll.forEach((item, index) => {
            item.nodeidB = item.nodeid
            this.qx.push(item)
          })
        } else {
          this.qx = []
          this.$message.warning('未查询到符合要求的数据')
        }
      })
    },
    getThreeHandle(val) {
      this.laForm.nodeidC = ''
      const oneVal = this.oneVal
      const query = { version: oneVal, parentid: val }
      fetchMiniSys(query).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.dq = []
          const dqAll = res.data.data.list
          const infoNum = res.data.data.count
          if (parseInt(infoNum) === 0) {
            this.threeSel = false
          } else {
            this.threeSel = true
          }
          dqAll.forEach((item, index) => {
            item.nodeidC = item.nodeid
            this.dq.push(item)
          })
        } else {
          this.dq = []
          this.$message.warning('未查询到符合要求的数据')
        }
      })
    },
    getFourHandle(val) {
      this.infoIdThree = false
      const oneVal = this.oneVal
      const query = { version: oneVal, parentid: val }
      fetchMiniSys(query).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          console.log(res.data.data.count)
        } else {
          this.$message.warning('未查询到符合要求的数据')
        }
      })
    },
    // 提交表单
    submitForm(formName) {
      if (!this.laForm.nodeidC) {
        this.infoIdThree = true
      } else {
        this.infoIdThree = false
      }
      if (!this.uploadedImg) {
        this.infoIdTu = true
      } else {
        this.infoIdTu = false
      }
      const nodeidAdd = this.nodeidAdd
      this.$refs[formName].validate((valid) => {
        if (valid) {
          if (!this.ruleForm.icon) {
            return
          }
          if (this.$route.query.id) {
            this.infoIdThree = false
            this.ruleForm.nodeid = nodeidAdd
            const copy = this.ruleForm
            copy.id = parseInt(this.$route.query.id)
            fetchMiniAdd(copy).then(res => {
              if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                this.$message({
                  type: 'success',
                  message: '编辑成功!'
                })
                history.go(-1)
                this.closeSelectedTag(this.visitedV)
              } else {
                this.$message.error(res.data.error_msg)
                this.loading = false
              }
            }).catch(res => {
              this.$message({
                message: '保存失败，请重新保存',
                type: 'error',
                duration: 5 * 1000
              })
              this.loading = false
            })
            return
          } else {
            const laNodeid = this.laForm
            if (laNodeid.nodeidC) {
              this.ruleForm.nodeid = laNodeid.nodeidC
            } else if (laNodeid.nodeidB) {
              this.ruleForm.nodeid = laNodeid.nodeidB
            } else {
              this.ruleForm.nodeid = ''
            }
            const version = this.oneVal
            this.ruleForm.version = version
            const copy = this.ruleForm
            fetchMiniAdd(copy).then(res => {
              if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                this.$message({
                  type: 'success',
                  message: '创建成功!'
                })
                window.history.go(-1)
                this.closeSelectedTag(this.visitedV)
              } else {
                // this.$message.error('')
                this.loading = false
              }
            }).catch(res => {
              this.$message({
                message: '保存失败，请重新保存',
                type: 'error',
                duration: 5 * 1000
              })
              this.loading = false
            })
          }
        } else {
          return false
        }
      })
    },
    fetchAdd() {
      this.loading = true
    },
    // 状态切换
    test(val) {
      this.ruleForm.enabled = val
    },
    // 图片上传
    beforeAction(file) {
      // 清空数据，保证只能上传一张图片
      this.ruleForm.icon = []
      const width = 80
      const height = 80
      const _this = this
      return new Promise(function(resolve, reject) {
        const reader = new FileReader()
        reader.onload = function() {
          const img = new Image()
          img.onload = function() {
            const valid = parseInt(this.width) === parseInt(width) && parseInt(this.height) === parseInt(height)
            if (!valid) {
              _this.$message.error('图片尺寸不符合要求')
              reject()
            }
            resolve()
          }
          img.src = reader.result
        }
        reader.readAsDataURL(file)
      })
    },
    handlePreview(file) {
      // this.uploadedImg = file.url
      this.dialogVisible = true
      console.log(file)
    },
    handleRemove(file, fileList) {
    },
    handleUploadSuccess(res, file) {
      this.uploadedImg = this.$qzconfig.oss_img_host + res.data.img_path
      this.ruleForm.icon = res.data.img_path
      this.infoIdTu = false
    },
    // 输入数字/汉字
    blurText(e) {
      const boolean = new RegExp('^[1-9][0-9]*$').test(e.target.value)
      const eInfo = e.target.value
      if (!boolean && eInfo) {
        this.$message.warning('请输入正整数')
        e.target.value = ''
      }
    },
    blurHan(e) {
      const boolean = new RegExp('[\\u4E00-\\u9fa5]+', 'g').test(e.target.value)
      const eInfo = e.target.value
      if (!boolean && eInfo) {
        this.$message.warning('请输入汉字')
        e.target.value = ''
      }
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss">
  .add-menu {
    padding: 20px;
    box-sizing: border-box;
    .infoDiv{
      float: left;
      width: 200px;
      height: 36px;
      box-sizing: border-box;
      line-height: 36px;
      margin-right: 20px;
      padding:0 16px;
      border-radius: 6px;
      color: #606266;
      font-size: 14px;
      border-radius: 4px;
      border: 1px solid #dcdfe6;
      background: #F5F7FA;
    }
    .narrow{
      width: 200px;
    }
    .minifonts{
      font-size: 14px;
      color: #606266;
      font-weight: bold;
      line-height: 24px;
    }
    .minifonts span{
      color: red;
    }
    .minilink{
      overflow: hidden;
      margin-bottom: 10px;
    }
    .link{
      float: left;
    }
    .el-form-item{
       margin-bottom: 20px;
    }
    .main {
      margin-top: 20px;
      padding: 20px;
      border-top: 3px solid #999;
      box-sizing: border-box;
      background-color: #fff;
    }
    .demo-ruleForm{
      width: 1000px;
    }
    .mb5{
      margin-bottom: 0px;
    }
    .mb20{
      margin-bottom: 20px;
    }
    .mr{
      margin-right: 20px;
    }
    .avatar-uploader .el-upload {
      border: 1px dashed #d9d9d9;
      border-radius: 6px;
      cursor: pointer;
      position: relative;
      overflow: hidden;
    }
    .avatar-uploader .el-upload:hover {
      border-color: #409EFF;
    }
    .avatar-uploader-icon {
      font-size: 28px;
      color: #8c939d;
      width: 80px;
      height: 80px;
      line-height: 80px;
      text-align: center;
    }
    .avatar {
      width: 80px;
      height: 80px;
      display: block;
    }
    .el-upload{
      border:1px solid #e6e6e6;
    }
    .el-upload--picture-card{
      width: 80px;
      height: 80px;
      line-height: 80px;
    }
    .el-upload-list--picture-card .el-upload-list__item{
      width: 80px;
      height: 80px;
    }
    .el-upload__tip{
      margin-top:0;
    }
    .el-switch__label--left{
      position: relative;
      left: 46px;
      color: #fff;
      z-index: -1111;
    }
    .el-switch__label--right{
      position: relative;
      right: 62px!important;
      color: #fff;
      z-index: -1111;
    }
    .el-switch__label.is-active {
      z-index: 1111;
      color: #fff;
    }
    .el-switch__label--left{
      position: relative;
      left: 62px;
      color: #fff;
      z-index: -1111;
    }
    .el-switch__label--right{
      position: relative;
      right: 62px;
      color: #fff;
      z-index: -1111;
    }
    .el-switch__label--right.is-active{
      z-index: 1111;
      color: #fff !important;
    }
    .el-switch__label--left.is-active{
      z-index: 1111;
      color: #9c9c9c !important;
    }
    .el-switch__core{
      width: 64px !important;
      height: 28px;
      border-radius: 6px;
    }
    .el-switch__core:after{
      top:5px;
    }
    .el-select .el-input.is-disabled .el-input__inner{
      color: #c0c4cc;
    }
    .pxlass{
      display: inline-block;
      color: #c0c4cc;
      font-size: 14px;
    }
    .sysRight{
      margin-right: 30px;
    }
    .el-tooltip__popper.is-dark{
      display: none;
    }
    .infoId{
      color: #f56c6c;
      font-size: 12px;
      margin-top: -18px;
      margin-bottom: 10px;
    }
    .el-upload-list__item{
      font-size:16px;
    }
  }
</style>
