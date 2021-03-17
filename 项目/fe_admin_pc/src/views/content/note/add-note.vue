<template>
  <div class="content-addnote">
    <el-form
      ref="ruleForm"
      :model="ruleForm"
      :rules="rules"
      label-width="100px"
      class="ruleForm"
    >
      <el-form-item label="标题" prop="title">
        <el-input
          v-model="ruleForm.title"
          class="w600"
          placeholder="请输入标题"
        />
      </el-form-item>
      <el-form-item label="内容模块" prop="content">
        <el-upload
          v-if="upImg"
          class="upload mb20"
          :limit="10"
          :data="uploadExtendData"
          :file-list="fileList"
          :action="action"
          list-type="picture-card"
          :on-exceed="handleExceed"
          :on-success="handleUploadSuccess"
          :before-upload="beforeAction"
          :on-preview="handlePreview"
          :on-remove="handleRemove"
          multiple
        >
          <el-button size="small" type="primary">上传图片</el-button>
        </el-upload>
        <el-upload
          v-if="upVideo"
          class="upload mb20"
          :action="action"
          :on-progress="uploadVideoProcess"
          :on-success="handleVideoSuccess"
          :before-upload="beforeUploadVideo"
          :data="uploadExtendData"
          list-type="picture-card"
          :show-file-list="false"
        >
          <video
            v-if="ruleForm.videoPath != '' && !videoFlag"
            :src="ruleForm.videoPath"
            class="video-avatar"
            controls="controls"
          >
            您的浏览器不支持视频播放
          </video>
          <el-button
            v-else-if="ruleForm.videoPath == '' && !videoFlag"
            size="small"
            type="primary"
            >上传视频</el-button
          >
          <el-progress
            v-if="videoFlag == true"
            type="circle"
            :percentage="videoUploadPercent"
            style="margin-top: 7px"
          ></el-progress>
        </el-upload>
        <el-input
          v-model="ruleForm.content"
          type="textarea"
          :rows="5"
          autosize
          placeholder="请输入内容模块"
        />
      </el-form-item>
      <el-form-item label="选择城市" prop="provinceVal">
        <el-select v-model="ruleForm.provinceVal" class="mr20" placeholder="请选择省份" @change="getCity">
          <el-option v-for="item in provinceList" :label="item.name" :value="item.code" :key="item.id" />
        </el-select>
        <el-select v-model="ruleForm.cityVal" placeholder="请选择城市">
          <el-option v-for="item in cityList" :label="item.name" :value="item.code" :key="item.id" />
        </el-select>
      </el-form-item>
      <el-form-item label="是否推荐" prop="delivery">
        <el-switch active-value="1" inactive-value="0"  v-model="ruleForm.ifRecommend" />
        <el-input  type="number" v-if="ruleForm.ifRecommend == 1" v-model="ruleForm.recommendValue" class="w300 ml10" placeholder="请输入推荐值" />
      </el-form-item>
      <el-form-item label="发布时间" prop="delivery">
        <el-switch active-value="1" inactive-value="0"  v-model="ruleForm.ifScheduled" />
        <el-date-picker v-if="ruleForm.ifScheduled == 1" v-model="ruleForm.scheduledTime" type="date"  value-format="timestamp" class="ml10" placeholder="选择日期" />
      </el-form-item>
      <el-form-item label="标签" prop="tag">
        <el-select v-model="ruleForm.tag" placeholder="请选择标签">
          <el-option
              v-for="item in tagOptions"
              :key="item.id"
              :label="item.name"
              :value="item.id +'&'+ item.name">
          </el-option>
        </el-select>
      </el-form-item>
      <el-form-item label="关联用户" prop="userId">
        <el-input
          v-model="ruleForm.userId"
          class="w300"
          placeholder="请输入用户id"
        />
      </el-form-item>
      <el-form-item>
        <el-button type="success" @click="submitForm('ruleForm')"
          >发布</el-button
        >
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
export default {
  name: "ContentaAddnote",
  data() {
    return {
      tagOptions: [],
      // 表单
      ruleForm: {
        title: "",
        content: "",
        userId: "",
        provinceVal: '',
        cityVal: '',
        imgList: [],
        videoPath: "",
        ifRecommend: '0',
        recommendValue: '',
        ifScheduled: '0',
        scheduledTime: '',
        tag: ''
      },

      rules: {
        title: [
          { required: true, message: "请输入标题", trigger: "blur" },
          {
            min: 1,
            max: 200,
            message: "标题长度不能超过200个字",
            trigger: "blur",
          },
        ],
        content: [
          { required: true, message: "请输入内容模块", trigger: "blur" },
        ],
        userId: [
          { required: true, message: "请输入用户", trigger: "blur" },
        ],
      },
      // 城市
      provinceList: [],
      cityList: [],
      // 上传
      action: "",
      fileList: [],
      uploadExtendData: {},
      // 上传视频
      videoFlag: false,
      videoUploadPercent: "",
      isShowUploadVideo: false,
      addPostItemDTOList: []
    };
  },
  computed:{
    upImg(){
      return true
    },
    upVideo(){
      let judge = this.ruleForm.imgList.length > 0
      return !judge
    }
  },
  created() {
    let id = this.$route.query.id ? this.$route.query.id : '';
    if(id){
      this.id = id
      this.getxTag(id)
    }
    this.action = this.$config.sevenCattleUrl;
    this.getSevenCattleToken();
    this.getProvinceList();
    this.getTags();
  },
  methods: {
    // 回显
    getDetail(id) {
      this.$apis.CONTENT.getNoteDetail({
        id
      }).then(res => {
        if (res.code === 0) {
          let datas= res.data
          this.ruleForm.title = datas.title
          this.ruleForm.content = datas.content
          this.ruleForm.userId = datas.userId
          this.ruleForm.id = id

          if(datas.previewType == 'IMAGE'){
            this.ruleForm.imgList = datas.imageUrls
            datas.imageUrls.forEach((it) => {
              const obj = { url: it };
              this.fileList.push(obj);
            });
          }
          if(datas.previewType == 'VIDEO'){
            this.ruleForm.videoPath = datas.videoUrl
            this.videoFlag = false
            this.videoUploadPercent = 100
          }
          let tag = datas.tag
          let obj=this.tagOptions.find(function (obj) {
            return obj.name ==tag;
          })
          this.ruleForm.tag = obj.id +'&'+ obj.name
        } else {
          this.$message.error(res.message)
        }
      })
    },
    getxTag(id) {
      this.$apis.CONTENT.getTagList({
        pageNo: 1,
        pageSize: 200,
      }).then((res) => {
        if (res.code === 0) {
          this.tagOptions = res.data;
          this.getDetail(id)
        }
      });
    },
    // 标签
    getTags() {
      this.$apis.CONTENT.getTagList({
        pageNo: 1,
        pageSize: 200,
      }).then((res) => {
        if (res.code === 0) {
          this.tagOptions = res.data;
        }
      });
    },
    // 城市
    getProvinceList() {
      this.$apis.COMMON.getCityList().then((res) => {
        if (res.code === 0) {
          this.provinceList = res.data;
        }
      });
    },
    getCity() {
      this.ruleForm.cityVal = "";
      const obj = this.provinceList.find((item) => {
        return item.code === this.ruleForm.provinceVal;
      });
      this.cityList = obj.children;
    },
    // 上传
    getSevenCattleToken() {
      const params = {};
      this.$apis.COMMON.getSevenCattleToken(params)
        .then((res) => {
          if (res.code === 0) {
            this.uploadExtendData.token = res.data;
          } else {
            this.$message.error(res.message);
          }
        })
        .catch((err) => {
          console.log(err);
        });
    },
    upImageKey() {
      const date = new Date().toLocaleDateString();
      const time = new Date().getTime();
      const math = Math.ceil(Math.random() * 100000);
      return `img/${date}/${time}${math}.jpg`;
    },
    beforeAction(file) {
      const isImg = file.type === "image/jpeg" || file.type === "image/png";
      const isLt1M = file.size / 1024 / 1024 < 1;
      if (!isImg) {
        this.$message.error("上传图片只能是JPG或PNG 格式!");
        return isImg;
      }
      if (!isLt1M) {
        this.$message.error("上传图片大小不能超过 1MB!");
        return isLt1M;
      }
      const key = this.upImageKey();
      this.uploadExtendData.key = key;
    },
    handleUploadSuccess(res) {
      this.ruleForm.videoPath = ''
      const imgUrl = this.$config.imgServerDomain + res.key;
      this.ruleForm.imgList.push(imgUrl);
    },
    handleRemove(res, fileList) {
      if (res.response) {
        this.ruleForm.imgList.forEach((item, index) => {
          if (res.response.data.img_path.indexOf(item) !== -1) {
            this.ruleForm.imgList.splice(index, 1);
          }
        });
      } else {
        this.ruleForm.imgList.forEach((item, index) => {
          if (res.url.indexOf(item) !== -1) {
            this.ruleForm.imgList.splice(index, 1);
          }
        });
      }
    },
    handlePreview(file) {
      this.dialogImageUrl = file.url;
      this.dialogImg = true;
    },
    handleExceed(files, fileList) {
      this.$message.warning("图片上传超出个数限制");
    },
    //上传视频
    upVideoKey() {
      const date = new Date().toLocaleDateString();
      const time = new Date().getTime();
      const math = Math.ceil(Math.random() * 100000);
      return `video/${date}/${time}${math}.mp4`;
    },
    beforeUploadVideo(file) {
      var fileSize = file.size / 1024 / 1024 < 200;
      if (
        [
          "video/mp4",
          "video/ogg",
          "video/flv",
          "video/avi",
          "video/wmv",
          "video/rmvb",
          "video/mov",
        ].indexOf(file.type) == -1
      ) {
        this.$message.warning("请上传正确的视频格式");
        return false;
      }
      if (!fileSize) {
        this.$message.warning("视频大小不能超过200MB");
        return false;
      }
      const key = this.upVideoKey();
      this.uploadExtendData.key = key;
      this.isShowUploadVideo = false;
    },
    uploadVideoProcess(event, file, fileList) {
      this.videoFlag = true;
      this.videoUploadPercent = file.percentage.toFixed(0) * 1;
    },
    handleVideoSuccess(res, file) {
      this.ruleForm.imgList = []
      this.isShowUploadVideo = true;
      this.videoFlag = false;
      this.videoUploadPercent = 0;
      const videoPath = this.$config.imgServerDomain + res.key;
      this.ruleForm.videoPath = videoPath;
    },
    // 提交
    submitForm(formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          if(this.ruleForm.ifRecommend == 1 && !this.ruleForm.recommendValue){
            this.$message.error('请填写推荐值')
            return
          }
          if(this.ruleForm.ifScheduled == 1 && !this.ruleForm.scheduledTime){
            this.$message.error('请填写发布时间')
            return
          }
          let type = this.ruleForm.videoPath ? '2' : '1'
          if(type ==2){
             this.addPostItemDTOList = [
              {
                type:2,
                value: this.ruleForm.videoPath.replace('https://ugcvideo.qizuang.com/','')
              }
            ]
          } else {
            this.addPostItemDTOList = this.ruleForm.imgList.map((item)=>{
              return {
                type: 1,
                value: item.replace('https://ugcvideo.qizuang.com/','')
              }
            })
          }
          let value = this.ruleForm.videoPath ? this.ruleForm.videoPath : this.ruleForm.imgList
          let tags = this.ruleForm.tag.split('&')
          let tag = tags[1]
          let tagId = tags[0]
          let obj = {
            areaCode: this.ruleForm.cityVal,
            content:  this.ruleForm.content,
            addPostItemDTOList:this.addPostItemDTOList,
            ifRecommend:this.ruleForm.ifRecommend,
            ifScheduled: this.ruleForm.ifScheduled,
            recommendValue: this.ruleForm.recommendValue ,
            scheduledTime:  this.ruleForm.scheduledTime,
            tag: tag,
            tagId: tagId,
            title: this.ruleForm.title,
            userId: this.ruleForm.userId
          }
          if(this.id){
            obj.id = this.id
            this.$apis.CONTENT.getEdit(obj).then(res => {
              if (res.code === 0) {
                this.$message.success(res.message)
                this.$router.push({ path:'/content/note-manage'  })
              } else {
                this.$message.error(res.message)
              }
            })
          } else {
            this.$apis.CONTENT.getUpdate(obj).then(res => {
              if (res.code === 0) {
                this.$message.success(res.message)
                this.$router.push({ path:'/content/note-manage'  })
              } else {
                this.$message.error(res.message)
              }
            })
          }
        } else {
          console.log("error submit!!");
          return false;
        }
      });
    },
  },
};
</script>
<style rel=" stylesheet/scss" lang="scss" scoped>
.content-addnote {
  padding: 20px;
  .ruleForm {
    width: 800px;
  }
  .font20 {
    font-size: 20px;
    color: #666;
    cursor: pointer;
  }
  .relation {
    margin-bottom: 20px;
  }
  .relation:last-child {
    margin-bottom: 0;
  }
  .upload{
    float: left;
    margin-right: 30px;
  }
  .video-avatar{
    width: 148px;
    height: 148px;
  }
  textarea{
    min-height: 200px;
  }
  .w600{
    width: 600px;
  }
}
</style>
<style>
  .content-addnote .el-textarea__inner{
    min-height: 200px !important;
  }
</style>