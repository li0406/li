<template>
  <div class="create-sms-tpl">
    <div>新增消息内容</div>
    <div class="qian-main">
      <el-form :model="tplForm" ref="tplForm" :rules="rules" label-width="600px">
        <el-row>
          <el-col :span="16">
            <el-form-item label="消息提醒内容:" :label-width="formLabelWidth">
              <el-input v-model="tplForm.notice" auto-complete="off"></el-input>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="16">
            <el-form-item label="消息标题:" :label-width="formLabelWidth">
              <el-input v-model="tplForm.title" auto-complete="off"></el-input>
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="消息图片:" :label-width="formLabelWidth">
          <div class="imgdiv">
            <div class="addimg">
              <el-upload
                ref="upload"
                :limit="1"
                :auto-upload="true"
                :on-exceed="handleExceed"
                :on-preview="handlePictureCardPreview"
                :on-remove="handleRemove"
                :on-success="handleAvatarSuccess"
                :before-upload="beforeAvatarUpload"
                :file-list="tplForm.images"
                :http-request="handleUploadImg"
                accept=".jpg, .jpeg, .png, .JPG, .JPEG"
                action
                list-type="picture-card"
              >
                <i class="el-icon-plus"></i>
              </el-upload>
              <el-dialog :visible.sync="dialogVisible">
                <img width="100%" :src="dialogImageUrl" alt />
              </el-dialog>
            </div>
          </div>
          <p class="imgtext">图片封面仅支持jpg、png,尺寸为900px*500px,大小不超过5M</p>
        </el-form-item>
        <el-form-item label="消息内容:" :label-width="formLabelWidth" prop="content">
          <el-row>
            <el-col :span="16">
              <el-input v-model="tplForm.content" auto-complete="off" type="textarea" :rows="4"></el-input>
              <p>示例：尊敬的齐装网会员：您已成功预约{变量}；联系人：{变量}；电话：{变量}。</p>
              <p>备注：短信模板中的{变量}，在实际发送短信时，会用变量值进行填充。</p>
            </el-col>
          </el-row>
        </el-form-item>
        <el-form-item label="消息类型:" :label-width="formLabelWidth" prop="type_id">
          <el-radio-group v-model="tplForm.type_id" @change="switchSeatSystem()">
            <el-radio
              @change="typeId()"
              :label="item.id"
              :key="item.id"
              v-for="item in tplForm.typeList"
            >{{item.name}}</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="发送位置:" :label-width="formLabelWidth" prop="app_type" >
          <el-radio-group v-model="tplForm.app_type" @change="switchSeatSystem()">
            <el-radio :label="1" @change="typeId()">前台系统</el-radio>
            <el-radio :label="2" @change="typeId()">后台系统</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="应用平台及跳转链接:" :label-width="formLabelWidth">
          <el-row>
            <el-col :span="16">
              <div class="platformlinks">
                <div
                  v-for="(item, index) in tplForm.link_json"
                  :key="index"
                  class="links-item"
                  :class="{'link_json_active': parseInt(linkJsonActive) === parseInt(index)}"
                  @click="chooseLinkJson(index)"
                >
                    {{item.app_name}}({{item.link}})
                    <span class="del" @click="delThisLink(item)">×</span>
                </div>
              </div>
            </el-col>
            <el-col :span="7" :offset="1">
              <el-row>
                <el-button type="primary" size="small" @click="addApplyAndLink">添加</el-button>
              </el-row>
            </el-col>
          </el-row>
        </el-form-item>

        <el-form-item label="推送方式:" :label-width="formLabelWidth" prop="send_type">
          <el-radio-group v-model="tplForm.send_type">
            <el-radio :label="1">单发</el-radio>
            <el-radio :label="2">群发</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="是否启用:" :label-width="formLabelWidth" prop="enabled">
          <el-switch v-model="tplForm.enabled" active-value="1" inactive-value="2"></el-switch>
        </el-form-item>
        <el-form-item label prop="name">
          <el-button type="primary" @click="submitForm('tplForm')">保存</el-button>
          <el-button @click="goBack('tplForm')">返回</el-button>
        </el-form-item>
      </el-form>
    </div>
    <!-- 筛选dialog -->
    <el-dialog title="新增应用平台及跳转链接" :visible.sync="dialogFormVisible"  @close='closeDialog'>
      <el-form :model="addForm" :rules="addFormrules" ref="addForm" >
        <el-form-item label="应用平台" prop='appSlotVal' :label-width="formLabelWidth">
          <el-select v-model="addForm.appSlotVal" placeholder="请选择">
            <el-option
              :key="item.value"
              :label="item.label"
              :value="item.value"
              v-for="item in addForm.app_slot"
            ></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="跳转链接" prop='link' :label-width="formLabelWidth">
          <el-input v-model="addForm.link" autocomplete="off"></el-input>
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
import {
  fetchMessageInfo,
  fetchMessageSave,
  fetchAppList
} from "@/api/messageContent";
import request from "@/utils/request";

export default {
  name: "createSmsTpl",
  data() {
    return {
      id: "",
      image: [],
      dialogImageUrl: "",
      dialogVisible: false,
      formLabelWidth: "200px",
      dialogTableVisible: false,
      dialogFormVisible: false,
      linkJsonActive: 0,
      tplForm: {
        notice: "", // 消息提醒内容
        title: "", // 消息标题
        content: "", // 消息内容
        type_id: "", // 消息类型
        link_json: [], // 平台及链接跳转
        send_type: "", // 推送方式
        enabled: "1", // 是否启用
        typeList: [], //消息类型
        app_type: "", //发送位置
        images: []
      },
      addForm: {
        app_slot: [
          {
            value: "",
            label: "请选择"
          }
        ],
        link: "",
        appSlotVal: "" 
      },
      rules: {
        content: [
          { required: true, message: "请输入消息内容", trigger: "blur" }
        ],
        type_id: [
          { required: true, message: "请选择消息类型", trigger: "change" }
        ],
        app_type: [
          { required: true, message: "请选择发送位置", trigger: "change" }
        ],
        send_type: [
          { required: true, message: "请选择推送方式", trigger: "change" }
        ],
        enabled: [
          { required: true, message: "请选择是否启用", trigger: "change" }
        ]
      },
      addFormrules: {
        appSlotVal: [
            { required: true, message: "请选择应用平台", trigger: "change" }
        ],
        link: [
            { required: true, message: "请填写跳转链接", trigger: "change" }
        ]
      }
    };
  },
  created() {
    this.id = this.$route.query.id
    // if(this.id) {
    this.getMessageDetail()
    // }
    
  },
  mounted() {
    // this.handleFixTitle()
  },
  methods: {
      //切换发送位置跟消息类型 平台连接清空
    switchSeatSystem(){
        this.tplForm.link_json = []  
    },
    // 应用平台筛选
    typeId() {
        if (this.tplForm.type_id && this.tplForm.app_type) {
                const obj = {
                app_type: this.tplForm.app_type, //发送位置
                type_id: this.tplForm.type_id   //消息类型
            };
            fetchAppList(obj).then(res => {
                this.addForm.app_slot = [
                    {
                        value: "",
                        label: "请选择"
                    }
                ];
                res.data.data.list.forEach((v, i) => {
                    this.addForm.app_slot.push({
                        value: v.slot,
                        label: v.name
                    });
                    this.addForm.link = v.link;
                });
            });
            if(!this.id) {
              this.tplForm.link_json = []
            }  
        }
    },
    // 应用平台添加
    addLink() {},
    chooseLinkJson(index) {
         this.linkJsonActive = index
    },
    addApplyAndLink() {
        this.dialogFormVisible = true
        this.$nextTick(() => {
                this.$refs.addForm.clearValidate()
        })
    },
    handleAvatarSuccess(res, file) {
        this.imageUrl = URL.createObjectURL(file.raw);
    },
    beforeAvatarUpload(file) {
        const _this = this;
        const isLt2M = file.size / 1024 / 1024 < 5;
        if (!isLt2M) {
            this.$message.error("上传图片大小不能超过 5MB!");
            return isLt2M;
        }
        return new Promise(function(resolve, reject) {
            const reader = new FileReader();
            reader.onload = function() {
            const img = new Image();
            img.onload = function() {
                const valid =
                parseInt(this.width) === 900 && parseInt(this.height) === 500;
                if (!valid) {
                _this.$message.error("图片尺寸不符合要求");
                reject();
                }
                resolve();
            };
            img.src = reader.result;
            };
            reader.readAsDataURL(file);
        });
    },
    handleExceed(file, fileList) {
        this.$message.error("最多上传1张图片");
    },
    handlePictureCardPreview(file) {
        this.dialogImageUrl = file.url;
        this.dialogVisible = true;
    },
    handleRemove(file, fileList) {
        this.tplForm.images = [];
    },
    handleUploadImg(item) {
      const formData = new FormData();
      formData.append("file", item.file);
      this.picLoading = true;
      request
        .post("/common/upload", formData)
        .then(res => {
            console.log(this.tplForm.images)
          this.tplForm.images.push({
            name: "",
            url: res.data.data.img_full,
            url_part: res.data.data.img_path
          });
          this.picLoading = false;
          console.log(this.tplForm.images)
        })
        .catch(err => {
          this.$message.error(err);
          this.picLoading = false;
        });
    },

    getMessageDetail() {
      if (this.id) {
        fetchMessageInfo({
          id: this.id
        }).then(res => {
            if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                const data = res.data.data.detail
                this.tplForm.notice = data.notice
                this.tplForm.title = data.title
                if(data.image_full) {
                    this.tplForm.images.push({
                        name: "",
                        url: data.image_full,
                        url_part: data.image
                    })
                }
                this.tplForm.content = data.content
                this.tplForm.type_id = data.type_id
                this.tplForm.app_type = data.app_type  //发送位置
                this.tplForm.send_type = data.send_type
                this.tplForm.enabled = String(data.enabled);
                this.tplForm.typeList = res.data.data.type_list; //消息类型
                this.tplForm.link_json = !data.link_list ? [] : data.link_list //平台及链接跳转
                this.typeId()
            } else {
                this.$message.error(res.data.error_msg);
            }
        });
      } else {
        fetchMessageInfo({}).then(res => {
          if (parseInt(res.status) === 200 &&parseInt(res.data.error_code) === 0) {
            this.tplForm.typeList = res.data.data.type_list;
          }
        });
      }
    },
    // 保存
    submitForm(formName) {
      this.$refs[formName].validate(valid => {
        if (valid) {
          this.handleSubmitPlatformLink();
        } else {
          console.log("error submit!!");
          return false;
        }
      });
    },

    handleSubmitPlatformLink() {
      const queryObj = this.handleArgs();
      fetchMessageSave(queryObj)
        .then(res => {
          if (
            parseInt(res.status) === 200 &&
            parseInt(res.data.error_code) === 0
          ) {
            this.$message({
              message: "保存成功",
              type: "success"
            });
            this.$router.push({
              path: "messageContent"
            });
          } else {
            this.$message.error(res.data.error_msg);
          }
        })
        .catch(res => {
          this.$message.error("网络异常，请稍后再试");
        });
    },
    // 所有参数
    handleArgs() {
      const queryObj = {
        id: this.id || "",
        content: this.tplForm.content, // 消息内容
        enabled: this.tplForm.enabled, // 启用状态
        title: this.tplForm.title, // 标题
        notice: this.tplForm.notice, // 提醒内容
        type_id: this.tplForm.type_id, //消息类型
        send_type: this.tplForm.send_type, // 推送方式
        app_type: this.tplForm.app_type, // 发送位置
        link_json: this.tplForm.link_json, // 应用平台连接
        image: this.tplForm.images.length > 0 ? this.tplForm.images[0].url_part : '' // 图片
      };
      return queryObj;
    },
    goBack() {
      history.go(-1);
    },
    //弹框确定
    handleSavePlatformLink(formName) {
        this.$refs[formName].validate(valid => {
            if (valid) {
                this.dialogFormVisible = false
                let platformName = ""
                this.addForm.app_slot.forEach((item, index) => {
                    if (item.value === this.addForm.appSlotVal) {
                        platformName = item.label
                    }
                });
                this.tplForm.link_json.push({
                    app_name: platformName,
                    link: this.addForm.link,
                    app_slot: this.addForm.appSlotVal
                });
                this.handleClear()
                this.$refs[formName].resetFields();
            } else {
                console.log("error submit!!");
                return false;
            }
        });
    },
    //弹框取消
    handleQuxiao(formRule){
        this.dialogFormVisible = false;
        this.handleClear();
        this.$refs[formRule].resetFields();
    },
    //清空数据
    handleClear(){
        this.addForm.appSlotVal = '';
        this.addForm.link = ''
    },
    //关闭（X）弹窗  清空数据跟验证
    closeDialog(){
        this.handleClear()
        this.$nextTick(() => {
            this.$refs.addForm.clearValidate()
       })
    },
    delThisLink(obj) {
      let cindex = -1;
      this.tplForm.link_json.forEach((item, index) => {
        if (obj.link === item.link) {
          cindex = index;
        }
      });
      if (parseInt(cindex) >= 0) {
        this.tplForm.link_json.splice(cindex, 1);
      }
    }
  }
};
</script>

<style rel="stylesheet/scss" lang="scss">
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
  width: 178px;
  height: 178px;
  line-height: 178px;
  text-align: center;
}
.avatar {
  width: 178px;
  height: 178px;
  display: block;
}

.create-sms-tpl {
  width: 100%;
  padding: 20px;
  box-sizing: border-box;
  .link_json_active{
    background-color: #eee;
  }
  .qian-main {
    margin-top: 20px;
    padding: 20px;
    border-top: 3px solid #999;
    box-sizing: border-box;
    background-color: #fff;
    .imgdiv {
      overflow: hidden;
      .addimg {
        float: left;
      }
      .againbutton {
        float: left;
        margin-top: 113px;
        margin-left: 20px;
      }
    }
    .imgtext {
      color: #999;
    }
    .delbutton {
      margin-top: 10px;
    }
    .tpl-content-note {
      padding-left: 100px;
    }
    .red {
      color: red;
    }
    .gateways {
      margin-bottom: 10px;
    }
    .gateways > td {
      padding-right: 10px;
    }
    .gatewayinput {
      width: auto;
    }
  }
  .platformlinks {
    border: 1px solid #dcdfe6;
    min-height: 100px;
    overflow-y: auto;
    .links-item {
      margin: 0;
      line-height: 2;
      padding-left: 10px;
      color: #606266;
      cursor: pointer;
      position: relative;
      .del{
          position: absolute;
          right: 10px;
      }
    }
  }
}
</style>
