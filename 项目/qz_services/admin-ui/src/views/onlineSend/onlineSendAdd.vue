<template>
  <div class="online-send-add">
    <div class="main">
      <h4>
        <span v-if="isAdd">新增</span>
        <span v-else-if="!isEdit">编辑</span>
        <span v-else-if="isEdit">查看</span><span>推送内容</span>
      </h4>
      <el-form ref="ruleForm" :model="form" :rules="rules" label-width="200px">
        <el-form-item label="消息标题:" prop="title">
          <el-input v-model="form.title" :style="{width:width}" :disabled="isEdit" />
        </el-form-item>
        <el-form-item label="消息图片:">
          <el-upload
            ref="upload"
            v-loading="picLoading"
            :disabled="isEdit"
            :auto-upload="true"
            :on-preview="handlePictureCardPreview"
            :on-remove="handleRemove"
            :on-success="handleAvatarSuccess"
            :before-upload="beforeAvatarUpload"
            :file-list="form.images"
            :http-request="handleUploadImg"
            accept=".jpg, .jpeg, .png, .JPG, .JPEG"
            list-type="picture-card"
            action
            :style="{width:width}"
          >
            <i class="el-icon-plus" />
            <div slot="tip" class="el-upload__tip">图片封面仅支持jpg、png,尺寸为900px*500px,大小不超过5M</div>
          </el-upload>
          <el-dialog :visible.sync="dialogVisible">
            <img width="100%" :src="dialogImageUrl" alt="消息图片">
          </el-dialog>
        </el-form-item>
        <el-form-item label="消息内容:" prop="body">
          <el-col :span="12">
            <el-input
              v-model="form.body"
              type="textarea"
              :rows="6"
              :style="{width:width}"
              :disabled="isEdit"
            />
          </el-col>
        </el-form-item>
        <el-form-item label="消息类型:" prop="type_id">
          <el-radio-group v-model="form.type_id" @change="typeChange">
            <el-radio
              v-for="(item, index) in messageTypeList"
              :key="index"
              :label="item.id"
              :disabled="isEdit"
            >{{ item.name }}</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="发送位置:" prop="app_type">
          <el-radio-group v-model="form.app_type" :disabled="true">
            <el-radio :label="1" :value="1">前台系统</el-radio>
            <el-radio :label="2" :value="2">后台系统</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="应用平台及跳转链接：" prop="link_json">
          <el-row :style="{width:width1}">
            <el-col :span="20">
              <div class="platformlinks">
                <el-tag
                  v-for="(tag, index) in form.link_json"
                  :key="index"
                  :closable="!isEdit"
                  :disable-transitions="false"
                  @close="handleClose(tag)"
                >{{ tag.app_name }}({{ tag.link }})</el-tag>
              </div>
            </el-col>
            <el-col :span="3" :offset="1">
              <el-row>
                <el-button v-show="!isEdit" type="primary" size="small" @click="addAppUrl">添加</el-button>
              </el-row>
            </el-col>
          </el-row>
          <el-input v-model="form.link_json.length" type="text" style="display:none" />
        </el-form-item>
        <el-form-item label="推送方式:" prop="push_type">
          <el-radio-group v-model="form.push_type" :disabled="true">
            <el-radio :label="1">群发</el-radio>
            <el-radio :label="2">单发</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="是否定时:" prop="send_type">
          <el-switch v-model="form.send_type" :disabled="isEdit" />
          <el-date-picker
            v-if="form.send_type"
            v-model="form.push_time"
            value-format="yyyy-MM-dd HH:mm:ss"
            :disabled="isEdit"
            type="datetime"
            placeholder="选择日期时间"
            :picker-options="pickerOptions"
          />
        </el-form-item>
        <el-form-item label="是否启用:" prop="enabled">
          <el-switch v-model="form.enabled" :active-value="1" :inactive-value="2" />
        </el-form-item>
        <el-form-item>
          <el-button v-show="!isEdit" type="primary" @click="saveForm('ruleForm')">保存</el-button>
          <el-button v-show="!isEdit" @click="goBack('ruleForm')">返回</el-button>
        </el-form-item>
      </el-form>
    </div>
    <!-- 筛选dialog -->
    <el-dialog
      title="新增应用平台及跳转链接"
      :visible.sync="dialogFormVisible"
      width="30%"
      @close="closeDialog"
    >
      <el-form ref="addForm" :model="addForm" :rules="addFormrules" label-width="100px">
        <el-form-item label="应用平台:" prop="appSlotVal">
          <el-select v-model="addForm.appSlotVal" placeholder="请选择">
            <el-option
              v-for="item in addForm.app_slot"
              :key="item.id"
              :label="item.name"
              :value="item.slot"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="跳转链接:" prop="link">
          <el-input v-model="addForm.link" autocomplete="off" />
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="handleQuxiao('addForm')">取 消</el-button>
        <el-button type="primary" @click="handleSavePlatformLink('addForm')">确 定</el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import request from '@/utils/request'
import {
  fetchSendInfo,
  fetchMessageSave,
  fetchAppList
} from '@/api/onlineSend'
export default {
  name: 'OnlineSendAdd',
  data() {
    return {
      width: '500px',
      width1: '600px',
      picLoading: false,
      pickerOptions: {
        disabledDate(time) {
          return time.getTime() <= Date.now() - 8.64e7
        }
      },
      id: '',
      form: {
        id: '', // 消息ID（编辑时必填)
        title: '', // 消息标题
        img: '', // 图片地址
        body: '', // 消息内容
        type_id: '', // 消息类型ID
        push_type: 1, // 推送方式，默认1，1:群发；2:单发
        push_time: '', // 推送时间
        date: '', // 推送时间
        time: '', // 推送时间
        app_type: 1, // 应用类型（1：前台系统；2：后台系统）
        send_type: false, // 是否定时  1.定时发送 2.立即发送
        enabled: 1, // 启用状态（1：是；2：否）
        link_json: [], // 应用平台链接json（格式见备注）
        images: []
      },
      linkJsonActive: 0,
      messageTypeList: [],
      fileList: { name: '', url: '' },
      imageUrl: '',
      dialogImageUrl: '',
      dialogVisible: false,
      isEdit: false, // 是否编辑（查看）
      isAdd: true, // 是否添加（编辑）
      rules: {
        title: [{ required: true, message: '请输入消息标题', trigger: 'blur' }],
        body: [{ required: true, message: '请输入消息内容', trigger: 'blur' }],
        type_id: [
          { required: true, message: '请选择消息类型', trigger: 'change' }
        ],
        push_type: [
          { required: true, message: '请选择推送方式', trigger: 'change' }
        ],
        app_type: [
          { required: true, message: '请选择推送方式', trigger: 'change' }
        ],
        send_type: [
          { required: true, message: '请选择是否定时', trigger: 'change' }
        ],
        enabled: [
          { required: true, message: '请选择是否启用', trigger: 'change' }
        ],
        push_time: [
          {
            type: 'date',
            required: true,
            message: '请选择日期',
            trigger: 'change'
          }
        ],
        link_json: [
          { required: true, message: '请添加应用平台及跳转链接', trigger: 'change' }
        ]
      },
      addForm: {
        app_slot: [],
        link: '',
        appSlotVal: ''
      },
      addFormrules: {
        appSlotVal: [
          { required: true, message: '请选择应用平台', trigger: 'change' }
        ],
        link: [{ required: true, message: '请填写跳转链接', trigger: 'change' }]
      },
      dialogFormVisible: false
    }
  },
  created() {
    if (this.$route.query.id) {
      this.id = this.$route.query.id
      this.isAdd = false
    } else {
      this.isAdd = true
    }
    if (this.$route.query.isEdit !== undefined) {
      if (this.$route.query.isEdit === '0' || this.$route.query.isEdit === 0) {
        this.isEdit = true
      } else {
        this.isEdit = false
      }
    }
    this.getSendInfo()
  },
  methods: {
    // 应用平台删除按钮
    handleClose(tag) {
      this.form.link_json.splice(this.form.link_json.indexOf(tag), 1)
    },
    getSendInfo() {
      const that = this
      const obj = {}
      if (that.id) {
        obj.id = that.id
      }
      fetchSendInfo(obj).then(res => {
        // console.log(res.data.data)
        if (res.status === 200 && res.data.error_code === 0) {
          that.messageTypeList = res.data.data.type_list
          if (that.id) {
            const detailData = JSON.parse(JSON.stringify(res.data.data.detail))
            that.form = detailData
            that.imageUrl = detailData.image_full
            if (detailData.send_type === 1) {
              that.form.send_type = true
              that.form.push_time = detailData.push_time_string
            } else {
              that.form.send_type = false
            }
            that.form.images = []
            if (detailData.image_full) {
              that.form.images.push({
                name: '',
                url: detailData.image_full,
                url_part: detailData.img
              })
            }
            // console.log('123', that.form)
          }
        }
      })
    },
    saveForm(formName) {
      this.$refs[formName].validate(valid => {
        if (valid) {
          if (this.form.send_type) {
            if (this.form.push_time === null) {
              this.$message.warning('请选择时间')
              return false
            }
          }
          const queryObj = this.handleArgs()
          fetchMessageSave(queryObj)
            .then(res => {
              console.log(res)
              if (
                parseInt(res.status) === 200 &&
                parseInt(res.data.error_code) === 0
              ) {
                this.$message({
                  message: '保存成功',
                  type: 'success'
                })
                this.$router.push({
                  path: '/messageManage/onlineSend'
                })
              } else {
                this.$message.error(res.data.error_msg)
              }
            })
            .catch(res => {
              this.$message.error('网络异常，请稍后再试')
            })
        } else {
          console.log('error submit!!')
          return false
        }
      })
    },
    // 所有参数
    handleArgs() {
      const queryObj = {
        id: this.id || '',
        title: this.form.title, // 消息标题
        img: this.form.images.length > 0 ? this.form.images[0].url_part : '', // 图片地址
        body: this.form.body, // 消息内容
        type_id: this.form.type_id, // 消息类型ID
        push_type: this.form.push_type, // 推送方式，默认1，1:群发；2:单发
        app_type: this.form.app_type, // 应用类型（1：前台系统；2：后台系统）
        send_type: this.form.send_type ? 1 : 2, // 是否定时  1.定时发送 2.立即发送
        enabled: this.form.enabled, // 启用状态（1：是；2：否）
        link_json: this.form.link_json // 应用平台链接json（格式见备注）
      }
      if (this.form.send_type) {
        queryObj.push_time = this.form.push_time
      }
      return queryObj
    },
    goBack(formName) {
      this.$refs[formName].resetFields()
      this.$router.push({
        path: `/messageManage/onlineSend`
      })
    },
    // 上传成功
    handleAvatarSuccess(res, file) {
      this.imageUrl = URL.createObjectURL(file.raw)
    },
    // 大小限制
    beforeAvatarUpload(file) {
      const _this = this
      const isLt5M = file.size / 1024 / 1024 < 5
      if (!isLt5M) {
        this.$message.error('上传图片大小不能超过 5MB!')
        return isLt5M
      }
      return new Promise(function(resolve, reject) {
        const reader = new FileReader()
        reader.onload = function() {
          const img = new Image()
          img.onload = function() {
            const valid =
              parseInt(this.width) === 900 && parseInt(this.height) === 500
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
    // 点击文件列表中已上传的文件时
    handlePictureCardPreview(file) {
      this.dialogImageUrl = file.url
      this.dialogVisible = true
    },
    handleRemove(file, fileList) {
      this.form.images = []
    },
    handleUploadImg(item) {
      const that = this
      const formData = new FormData()
      formData.append('file', item.file)
      that.picLoading = true
      request
        .post('/common/upload', formData)
        .then(res => {
          // console.log('<<<', res.data.data)
          that.form.images = []
          that.form.images.push({
            name: '',
            url: res.data.data.img_full,
            url_part: res.data.data.img_path
          })
          that.picLoading = false
          // console.log(that.form.images)
        })
        .catch(err => {
          this.$message.error(err)
          this.picLoading = false
        })
    },
    // 应用平台及跳转链接添加按钮
    typeChange() {
      this.form.link_json = []
    },
    addAppUrl() {
      if (!this.form.type_id) {
        this.$message.warning('请选择消息类型')
      } else {
        this.dialogFormVisible = true
        this.getAppUrlList()
      }
    },
    // 获取应用平台
    getAppUrlList() {
      const obj = {
        type_id: this.form.type_id, // 消息类型
        app_type: this.form.app_type // 发送位置
      }
      fetchAppList(obj).then(res => {
        this.addForm.app_slot = []
        if (res.status === 200 && res.data.error_code === 0) {
          if (res.data.data) {
            this.addForm.app_slot = res.data.data.list
          }
        } else {
          this.$message.error(res.data.error_msg)
        }
      })
    },
    // 弹框取消
    handleQuxiao(formRule) {
      this.dialogFormVisible = false
      this.handleClear()
      this.$refs[formRule].resetFields()
    },
    // 弹框确定
    handleSavePlatformLink(formName) {
      this.$refs[formName].validate(valid => {
        if (valid) {
          this.dialogFormVisible = false
          let platformName = ''
          console.log(this.addForm.appSlotVal)
          this.addForm.app_slot.forEach((item, index) => {
            if (item.slot === this.addForm.appSlotVal) {
              platformName = item.name
            }
          })
          this.form.link_json.push({
            app_name: platformName,
            link: this.addForm.link,
            app_slot: this.addForm.appSlotVal
          })
          this.handleClear()
          this.$refs[formName].resetFields()
        } else {
          console.log('error submit!!')
          return false
        }
      })
    },
    // 清空数据
    handleClear() {
      this.addForm.appSlotVal = ''
      this.addForm.link = ''
    },
    // 关闭（X）弹窗  清空数据跟验证
    closeDialog() {
      this.handleClear()
      this.$nextTick(() => {
        this.$refs.addForm.clearValidate()
      })
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss">
.online-send-add {
  background: #fff;
  margin: 20px;
  padding: 20px;
  border-top: 3px solid #999;
  .main {
    // width: 40%;
    .avatar-uploader .el-upload {
      border: 1px dashed #d9d9d9;
      border-radius: 6px;
      cursor: pointer;
      position: relative;
      overflow: hidden;
    }
    .avatar-uploader .el-upload:hover {
      border-color: #409eff;
    }
    .avatar-uploader-icon {
      font-size: 28px;
      color: #8c939d;
      width: 120px;
      height: 120px;
      line-height: 120px;
      text-align: center;
    }
    .avatar {
      width: 120px;
      height: 120px;
      display: block;
    }
    .el-tag + .el-tag {
      margin-left: 10px;
    }
    .button-new-tag {
      margin-left: 10px;
      height: 32px;
      line-height: 30px;
      padding-top: 0;
      padding-bottom: 0;
    }
    .input-new-tag {
      width: 90px;
      margin-left: 10px;
      vertical-align: bottom;
    }
    .platformlinks {
      border: 1px solid #dcdfe6;
      padding: 10px;
      min-height: 150px;
      overflow: auto;
      position: relative;
      .links-item {
        margin: 0;
        line-height: 2;
        padding-left: 10px;
        color: #606266;
        white-space: nowrap;
        .del {
          position: absolute;
          right: 0;
          width: 28px;
          height: 28px;
          line-height: 28px;
          text-align: center;
          background: #fff;
          cursor: pointer;
        }
        .el-button + .el-button {
          margin-left: 0;
        }
      }
    }
  }
}
</style>
