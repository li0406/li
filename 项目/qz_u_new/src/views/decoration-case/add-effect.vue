// 添加效果图
<template>
  <div class="add-effect">
    <qz-card title="添加效果图" divider>
      <el-row :gutter="16">
        <el-col :span="16">
          <el-form ref="form" label-width="100px" :model="form">
            <el-form-item
              label="标题名称:"
              prop="xiaoqu"
              :rules="[{ required: true, message: '请输入标题名称', trigger: 'blur' }]"
            >
              <el-input
                v-model.trim="form.xiaoqu"
                maxlength="20"
                placeholder="请输入标题名称"
                style="width:200px"
              ></el-input>
            </el-form-item>
            <el-form-item
              label="装修风格:"
              prop="fengge"
              :rules="[{ required: true, message: '请选择装修风格', trigger: 'blur' }]"
            >
              <el-select v-model="form.fengge" placeholder="请选择装修风格" style="width:200px">
                <el-option
                  v-for="item in option.fengge"
                  :key="item.id"
                  :label="item.name"
                  :value="item.id"
                ></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="房屋面积:" prop="mianji" :rules="rule.mianji">
              <el-input
                v-model.trim="form.mianji"
                placeholder="请输入房屋面积"
                style="width:200px;margin-right:20px"
                suffix-icon
              >
                <template slot="suffix">
                  <span>㎡</span>
                </template>
              </el-input>
              <i class="el-icon-warning-outline" style="margin-right:10px;color:#FF5353"></i>
              <!-- ISSUE: 文案，rule提示为输入，侧边提示信息为使用，还是红色，面积可以带小数 -->
              <span class="mianji-info">面积请使用正整数</span>
            </el-form-item>
            <!-- ISSUE: 文案改成图片 -->
            <el-form-item label="设计师:">
              <el-select v-model="form.designer" placeholder="请选择设计师">
                <el-option key="0" label="全部" value></el-option>
                <el-option
                  v-for="item in designerList"
                  :key="item.id"
                  :label="item.nick_name"
                  :value="item.id"
                ></el-option>
              </el-select>
            </el-form-item>
            <!-- FIXME: rule 提示 -->
            <el-form-item
              label="添加图片:"
              prop="images"
              :rules="[{ required: true, message: '请添加图片', trigger: 'blur' }]"
            >
              <div class="photo">
                <el-upload
                  :action="upload.aciton"
                  :headers="upload.headers"
                  :data="upload.data"
                  list-type="picture-card"
                  :file-list="upload.fileList"
                  :limit="upload.limit"
                  :on-preview="handleUploadPreview"
                  :on-remove="handleUploadRemove"
                  :on-success="handleUploadSuccess"
                  :before-upload="handleBeforeUpload"
                  :on-exceed="handleUploadExceed"
                >
                  <i class="el-icon-plus"></i>
                </el-upload>
                <el-dialog :visible.sync="upload.dialogVisible">
                  <img :src="upload.previewImg" width="100%" alt="预览" />
                </el-dialog>
              </div>
            </el-form-item>
            <el-form-item>
              <el-button type="danger" @click="onSubmit">提交</el-button>
              <el-button type="danger" plain @click="handleButtonBack">返回</el-button>
            </el-form-item>
          </el-form>
        </el-col>
        <el-col :span="8">
          <div class="card">
            <div>
              <p>效果图小贴士</p>
            </div>
            <div class="divider"></div>
            <div class="content">
              <p>1，带*为必填项</p>
              <p>2，案例信息，图片上，禁止包含联系方式\网址\其他网站LOGO \微博，微信帐号\二维码等“相关联系方式”，否则将会封号处理。</p>
              <p>3，请上传高清优质图片，齐装网会重点推荐高清优质的图片，增加您的曝光率，导致低质量差的图片将重新审核不通过。</p>
            </div>
          </div>
        </el-col>
      </el-row>
    </qz-card>
  </div>
</template>

<script>
import QzCard from '@/components/card.vue';
import ApiCase from '@/api/case';
import QZ_CONFIG from '@/utils/config';

export default {
  async created() {
    this.getOptionFengGe();
    this.getemployeedropdowlist();
  },
  components: { QzCard },
  methods: {
    // 网店管理-装修案例-获取设计师下拉菜单
    getemployeedropdowlist() {
      const params = {
        position: 2, //  职位 1客服 2设计师
        state: 2, //  显示状态 1显示在职 显示全部
      };
      ApiCase.getemployeedropdowlist(params)
        .then((res) => {
          if (res.data.error_code === 0) {
            this.designerList = res.data.data; //  获取设计师列表
          } else {
            this.$message.error(res.data.error_msg);
          }
        })
        .catch((err) => {
          this.$message.error(err);
        });
    },
    handleButtonBack() {
      this.$router.push('/decoration-case/effect');
    },
    // FIXME: 需要调用接口
    async onSubmit() {
      this.$refs.form.validate(async (valid) => {
        if (valid) {
          const res = await ApiCase.caseSave(this.form);

          if (res.data.error_code === 0) {
            this.$message.success('提交成功');
            this.$router.push('/decoration-case/effect');
          } else {
            this.$message.error('提交失败');
          }
        }
      });
    },
    // 装修风格
    async getOptionFengGe() {
      const result = await ApiCase.fengGe();

      const {
        data: { data },
      } = result;

      this.option.fengge = data;
    },
    handleUploadPreview(file) {
      this.upload.previewImg = file.url;
      this.upload.dialogVisible = true;
    },
    handleUploadRemove(file, fileList) {
      this.form.images = fileList.map((fileValue) => fileValue.response.data.img_path);
    },
    handleUploadSuccess(res) {
      this.form.images = res.data.map((value) => value.path);
    },
    handleBeforeUpload(file) {
      const isLt3M = file.size / 1024 / 1024 < 3;
      if (!isLt3M) {
        this.$message.error('上传图片大小不能超过 3MB!');
      }
      const isJPG = file.type === 'image/jpeg' || file.type === 'image/jpg';
      if (!isJPG) {
        this.$message.error('请上传jpg、jpeg图片');
      }

      const isSize = new Promise((resolve, reject) => {
        const url = window.URL || window.webkitURL;
        const img = new Image();
        img.src = url.createObjectURL(file);
        img.onload = () => {
          if (img.width / img.height === 2) {
            resolve();
          } else {
            reject();
          }
        };
      }).then(
        () => {
          return file;
        },
        () => {
          this.$message.error('请上传长宽比为2：1的图片');
          return Promise.reject();
        },
      );

      return isLt3M && isJPG && isSize;
    },
    handleUploadExceed() {
      this.$message.error(`最多上传${this.upload.limit}张图片`);
    },
  },
  data() {
    return {
      upload: {
        aciton: `${QZ_CONFIG.OSS_AUDIO_HOST}cp/v1/case/upload_3d`,
        headers: {
          token: localStorage.getItem('token'),
        },
        data: {
          prefix: 'zxgscase',
          id: 'WU_FILE_0',
          name: '1.jpg',
          type: 'jpg',
        },
        fileList: [],
        limit: 1,
        dialogVisible: false,
        previewImg: '',
      },
      option: {
        fengge: [],
      },
      rule: {
        mianji: [
          { required: true, message: '请输入房屋面积', trigger: 'blur' },
          {
            pattern: /^[1-9]\d*$/,
            message: '请输入正整数',
            trigger: 'blur',
          },
        ],
      },
      designerList: [],
      form: {
        designer: '', //  设计师
        classid: 4, // 4:3d效果图
        xiaoqu: '', // 标题名称
        fengge: '', // 装修风格
        mianji: '', // 房屋面积
        images: [], // 上传的案例/3d效果图图片
      },
    };
  },
};
</script>

<style lang="less" scoped>
.add-effect {
  .mianji-info {
    width: 84px;
    height: 12px;
    color: #ff5353;
    font-weight: 400;
    font-size: 12px;
  }
  .pic-back {
    box-sizing: border-box;
    width: 582px;
    padding: 20px;
    padding-bottom: 0;
    background: rgba(250, 250, 250, 1);
    border: 1px solid rgba(227, 227, 227, 1);

    .el-form-item {
      margin-bottom: 0;
    }
  }
  .card {
    width: 300px;
    height: 270px;
    padding: 20px;
    background-color: #fff8e9;
    border: 1px solid #f3cece;
    .title {
      width: 70px;
      height: 12px;
      color: rgba(255, 135, 61, 1);
      font-weight: bold;
      font-size: 14px;
      line-height: 60px;
    }
    .divider {
      width: 260px;
      height: 1px;
      border: 1px solid rgba(243, 206, 206, 1);
    }
    .content p {
      width: 260px;
      color: rgba(255, 135, 61, 1);
      font-weight: 400;
      font-size: 12px;
      line-height: 18px;
    }
  }
}
</style>
