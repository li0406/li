// 添加案例
<template>
  <div class="add-case">
    <qz-card :title="$route.params.id === 'new' ? '添加案例' : '编辑案例'"
             divider
             style="margin-bottom:80px">
      <el-row>
        <el-col :span="12">
          <el-form ref="form"
                   label-width="100px"
                   :model="form">
            <el-form-item label="案例类型:"
                          prop="classid"
                          :rules="[{ required: true, message: '请选择案例类型', trigger: 'blur' }]">
              <el-select v-model="form.classid"
                         placeholder="请选择案例类型"
                         style="width:200px">
                <el-option v-for="item in option.classid"
                           :key="item.id"
                           :label="item.name"
                           :value="item.id"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="所在区域:"
                          prop="qx"
                          :rules="[{ required: true, message: '请选择区县', trigger: 'blur' }]">
              <el-input v-model="form.cname"
                        placeholder="请输入城市"
                        disabled
                        style="width:200px;margin-right:16px;"></el-input>
              <el-select v-model="form.qx"
                         placeholder="请选择区县"
                         style="width:200px">
                <el-option v-for="item in option.qx"
                           :key="item.id"
                           :label="item.name"
                           :value="item.id"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="小区名称:"
                          prop="xiaoqu"
                          :rules="[{ required: true, message: '请输入小区名称', trigger: 'blur' }]">
              <!-- FIXME: trim 去除前后空格，直接是前后无法输入空格，很糟糕 -->
              <el-input maxlength="20"
                        v-model.trim="form.xiaoqu"
                        placeholder="请输入小区名称"
                        style="width:250px"></el-input>
              <i class="el-icon-warning-outline"
                 style=" margin-right:10px;margin-left:10px;color:#FF5353"></i>
              <span class="info">为该案例命名，使用小区、楼盘名称</span>
            </el-form-item>
            <el-form-item label="户型结构:"
                          prop="huxing"
                          v-if="form.classid !== '2'"
                          :rules="[{ required: true, message: '请选择户型结构', trigger: 'blur' }]">
              <el-select v-model="form.huxing"
                         placeholder="请选择户型结构"
                         style="width:200px">
                <el-option v-for="item in option.huxing"
                           :key="item.id"
                           :label="item.name"
                           :value="item.id"></el-option>
              </el-select>
            </el-form-item>

            <!-- FIXME: 房屋类型数据从哪里来 -->
            <el-form-item label="房屋类型:"
                          prop="leixing"
                          :rules="[{ required: true, message: '请选择房屋类型', trigger: 'blur' }]"
                          v-if="form.classid === '2'">
              <el-select v-model="form.leixing"
                         placeholder="请选择房屋类型"
                         style="width:200px">
                <el-option v-for="item in option.leixing"
                           :key="item.id"
                           :label="item.name"
                           :value="item.id"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="房屋面积:"
                          prop="mianji"
                          :rules="rule.mianji">
              <!-- FIXME: 房屋面积添加去除空格 -->
              <el-input v-model.trim="form.mianji"
                        maxlength="20"
                        placeholder="请输入房屋面积"
                        style="width:200px"></el-input>
              <i class="el-icon-warning-outline"
                 style=" margin-right:10px;margin-left:10px;color:#FF5353"></i>
              <span class="info">面积请使用正整数</span>
            </el-form-item>
            <el-form-item label="装修风格:"
                          prop="fengge"
                          :rules="[{ required: true, message: '请选择装修风格', trigger: 'blur' }]">
              <el-select v-model="form.fengge"
                         placeholder="请选择装修风格">
                <el-option v-for="item in option.fengge"
                           :key="item.id"
                           :label="item.name"
                           :value="item.id"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="合同总价:"
                          prop="zaojia"
                          :rules="[{ required: true, message: '请选择合同总价', trigger: 'blur' }]">
              <el-select v-model="form.zaojia"
                         placeholder="请选择合同总价">
                <el-option v-for="item in option.zaojia"
                           :key="item.id"
                           :label="item.name"
                           :value="item.id"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="装修时间:">
              <el-date-picker type="daterange"
                              range-separator="至"
                              start-placeholder="请选择开始日期"
                              end-placeholder="请选择结束日期"
                              v-model="form.startEndTime"></el-date-picker>
            </el-form-item>
            <!--设计师列表选择-->
            <el-form-item label="设计师:"
                          prop="designer"
                          :rules="[{ required: false, message: '请选择设计师', trigger: 'blur' }]">
              <el-select v-model="form.designer"
                         placeholder="请选择设计师">
                <el-option v-for="item in option.designerList"
                           :key="item.id"
                           :label="item.nick_name"
                           :value="item.id"></el-option>
              </el-select>
            </el-form-item>
            <!-- FIXME: 图片rule提示 -->
            <el-form-item label="添加封面:"
                          prop="imageCover"
                          :rules="[{ required: true, message: '请添加图片', trigger: 'blur' }]">
              <div class="photo">
                <el-upload list-type="picture-card"
                           multiple
                           :action="upload.cover.aciton"
                           :headers="upload.cover.headers"
                           :data="upload.cover.data"
                           :file-list="upload.cover.fileList"
                           :limit="upload.cover.limit"
                           :on-preview="handleUploadCoverPreview"
                           :on-remove="handleUploadCoverRemove"
                           :on-success="handleUploadCoverSuccess"
                           :before-upload="handleUploadCoverBefore"
                           :on-exceed="handleUploadCoverExceed">
                  <i class="el-icon-plus"></i>
                </el-upload>
                <el-dialog :visible.sync="upload.cover.dialogVisible">
                  <img :src="upload.cover.previewImg"
                       width="100%"
                       alt="预览" />
                </el-dialog>
              </div>
            </el-form-item>
            <!-- FIXME: 图片rule提示 -->
            <el-form-item label="添加图片:"
                          prop="imageContent"
                          :rules="[{ required: true, message: '请添加图片', trigger: 'blur' }]">
              <div class="photo">
                <el-upload list-type="picture-card"
                           multiple
                           :action="upload.content.aciton"
                           :headers="upload.content.headers"
                           :data="upload.content.data"
                           :file-list="upload.content.fileList"
                           :limit="upload.content.limit"
                           :on-preview="handleUploadContentPreview"
                           :on-remove="handleUploadContentRemove"
                           :on-success="handleUploadContentSuccess"
                           :before-upload="handleUploadContentBefore"
                           :on-exceed="handleUploadContentExceed">
                  <i class="el-icon-plus"></i>
                </el-upload>
                <el-dialog :visible.sync="upload.content.dialogVisible">
                  <img :src="upload.content.previewImg"
                       width="100%"
                       alt="预览" />
                </el-dialog>
              </div>
            </el-form-item>
            <el-form-item>
              <el-button type="danger"
                         @click="handleFormSubmit">提交</el-button>
              <el-button type="danger"
                         plain
                         @click="handleButtonBack">返回</el-button>
            </el-form-item>
          </el-form>
        </el-col>
        <el-col :span="4">
          <div class="card">
            <div>
              <p>案例小贴士</p>
            </div>
            <div class="divider"></div>
            <div class="content">
              <p>1，带*为必填项</p>
              <p>
                2，案例信息，图片上，禁止包含联系方式\网址\其他网站LOGO
                \微博，微信帐号\二维码等“相关联系方式”，否则将会封号处理。
              </p>
              <p>
                3，请上传高清优质图片，齐装网会重点推荐高清优质的图片，增加您的曝光率，导致低质量差的图片将重新审核不通过。
              </p>
              <p>4，图片展示最佳的尺寸宽为660像素，最大尺寸为860像素，最小尺寸为460像素。</p>
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
import ApiPublic from '@/api/public';
import dayjs from 'dayjs';
import QZ_CONFIG from '@/utils/config';

export default {
  name: 'AddCase',
  async created () {
    await this.getCompany();
    await this.getOptionClassId();
    await this.getOptionQx();
    await this.getOptionFengGe();
    await this.getOptionZaoJia();
    await this.getLeiXing();
    await this.getemployeedropdowlist();

    if (this.$route.params.id !== 'new') {
      await this.getCaseDetail();
    }
  },
  methods: {
    // 网店管理-装修案例-获取设计师下拉菜单
    getemployeedropdowlist () {
      const params = {
        position: 2, //  职位 1客服 2设计师
        state: 2, //  显示状态 1显示在职 显示全部
      };
      ApiCase.getemployeedropdowlist(params)
        .then((res) => {
          if (res.data.error_code === 0) {
            this.option.designerList = res.data.data; //  获取设计师列表
          } else {
            this.$message.error(res.data.error_msg);
          }
        })
        .catch((err) => {
          this.$message.error(err);
        });
    },
    handleButtonBack () {
      this.$router.push('/decoration-case/case');
    },
    async getCaseDetail () {
      const res = await ApiCase.caseGet({ id: this.$route.params.id });
      if (res.data.error_code === 0) {
        const {
          classid,
          cs,
          qx,
          title,
          xiaoquid,
          huxing,
          leixing,
          mianji,
          fengge,
          zaojia,
          start,
          end,
          images,
        } = res.data.data;

        // TIP: `?.` optional chaining
        // https://www.babeljs.cn/docs/babel-plugin-proposal-optional-chaining
        // if (images?.length > 1) {
        //   const [firstArr, ...restArr] = images;

        //   this.upload.cover.fileList = [{ url: firstArr.img_path }];
        //   this.upload.content.fileList = restArr.map((value) => ({ url: value.img_path }));

        //   this.form.imageCover = [firstArr.img_path_original];
        //   this.form.imageContent = restArr.map((value) => value.img_path_original);
        // }
        // this.upload.cover.fileList = [{ url: firstArr.img_path }];
        //           this.upload.content.fileList = restArr.map((value) => ({ url: value.img_path }));
        this.upload.cover.fileList = [{ url: res.data.data.cover_image }];
        this.upload.content.fileList = images.map((value) => ({ url: value.img_path }));
        this.form.imageCover = [res.data.data.thumb];
        this.form.imageContent = images.map((value) => value.img_path_original);
        this.form.designer = res.data.data.userid;
        // console.log(this.form.imageCover);
        // console.log(this.form.imageContent);
        // console.log(res.data.data.userid);
        this.form.classid = classid;
        this.form.cs = cs;
        this.form.qx = qx;
        this.form.xiaoqu = title;
        this.form.xiaoquid = xiaoquid;
        this.form.huxing = huxing;
        this.form.leixing = leixing;
        this.form.mianji = mianji;
        this.form.fengge = fengge;
        this.form.zaojia = zaojia;
        this.form.start = start;
        this.form.end = end;
        this.form.images = images;
        if (start !== '' && end !== '') {
          this.form.startEndTime = [new Date(start), new Date(end)];
        }
      } else {
        this.$message.error('显示错误');
      }
    },
    async handleFormSubmit () {
      this.$refs.form.validate(async (valid) => {
        if (valid) {
          if (this.form.startEndTime?.length === 2) {
            this.form.start = dayjs(this.form.startEndTime[0]).format('YYYY-MM-DD');
            this.form.end = dayjs(this.form.startEndTime[1]).format('YYYY-MM-DD');
          } else {
            this.form.start = null;
            this.form.end = null;
          }
          const {
            classid,
            cs,
            qx,
            xiaoqu,
            xiaoquid,
            huxing,
            leixing,
            mianji,
            fengge,
            zaojia,
            start,
            end,
            designer,
          } = this.form;
          const data = {
            classid, // 1：家装,2:公装,3:在建工地,4:3d效果图
            cs, // 城市
            qx, // 区县
            xiaoqu, // 小区
            xiaoquid, // 小区id,如果有的话
            huxing, // 户型
            leixing, // 房屋类型
            mianji, // 面积
            fengge, // 风格
            zaojia, // 合同造价
            start, // 装修开始时间
            end, // 装修结束时间
            designer,
            thumb: this.form.imageCover[0],
            images: [...this.form.imageContent], // 上传的案例/3d效果图图片
          };
          // id: this.$route.params.id !== 'new' ? this.$route.params.id : undefined,
          if (this.$route.params.id !== 'new') {
            data.id = this.$route.params.id;
          }
          // console.log(data);
          const res = await ApiCase.caseSave(data);

          if (res.data.error_code === 0) {
            this.$message.success('提交成功');
            this.$router.push('/decoration-case/case');
          } else {
            this.$message.error('提交失败');
          }
        }
      });
    },
    // 案例类型
    async getOptionClassId () {
      const result = await ApiCase.searchParams();

      const {
        data: { data },
      } = result;

      this.option.classid = Object.keys(data.class).map((value) => ({
        id: value,
        name: data.class[value],
      }));
    },
    // 区县
    async getOptionQx () {
      const result = await ApiPublic.getAreaInfoByCityId({
        city_name: this.form.cname,
      });

      const {
        data: { data },
      } = result;

      // 丢弃第一个全部选项
      data.shift();
      this.option.qx = data.map((value) => ({ id: value.area_id.toString(), name: value.area_name.toString() }));
    },
    // 装修风格
    async getOptionFengGe () {
      const result = await ApiCase.fengGe();

      const {
        data: { data },
      } = result;

      this.option.fengge = data.map((value) => ({ id: value.id.toString(), name: value.name.toString() }));
    },
    // 合同总价
    async getOptionZaoJia () {
      const result = await ApiCase.jiaGe();

      const {
        data: { data },
      } = result;

      this.option.zaojia = data.map((value) => ({ id: value.id.toString(), name: value.name.toString() }));
    },
    // 房屋类型
    async getLeiXing () {
      const res = await ApiCase.leiXing({ type: 'leixing' });

      const {
        data: { data },
      } = res;

      this.option.leixing = data.map((value) => ({ id: value.id.toString(), name: value.name.toString() }));
    },
    async getCompany () {
      const res = await ApiPublic.getBasicinfo();
      this.form.cs = res.data.data.cs;
      this.form.cname = res.data.data.cname;
    },

    // 上传封面图片
    handleUploadContentExceed () {
      this.$message.error(`最多上传${this.upload.content.limit}张图片`);
    },

    handleUploadCoverPreview (file) {
      this.upload.cover.previewImg = file.url;
      this.upload.cover.dialogVisible = true;
    },
    handleUploadCoverRemove (file, fileList) {
      this.form.imageCover = fileList.map((fileValue) => fileValue.response.data.img_path);
    },
    handleUploadCoverSuccess (res) {
      this.form.imageCover.push(res.data.img_path);
    },
    handleUploadCoverBefore (file) {
      const isLt3M = file.size / 1024 / 1024 < 3;
      if (!isLt3M) {
        this.$message.error('上传图片大小不能超过 3MB!');
      }
      const isJPG = file.type === 'image/jpeg' || file.type === 'image/png' || file.type === 'image/jpg';
      if (!isJPG) {
        this.$message.error('请上传jpg、png、jpeg图片');
      }
      return isLt3M && isJPG;
    },
    handleUploadCoverExceed () {
      this.$message.error(`最多上传${this.upload.cover.limit}张图片`);
    },
    // 上传内容图片
    handleUploadContentPreview (file) {
      this.upload.content.previewImg = file.url;
      this.upload.content.dialogVisible = true;
    },
    handleUploadContentRemove (file, fileList) {
      this.form.imageContent = fileList.map((fileValue) => fileValue.response.data.img_path);
    },
    handleUploadContentSuccess (res) {
      this.form.imageContent.push(res.data.img_path);
    },
    handleUploadContentBefore (file) {
      const isLt3M = file.size / 1024 / 1024 < 3;
      if (!isLt3M) {
        this.$message.error('上传图片大小不能超过 3MB!');
      }
      const isJPG = file.type === 'image/jpeg' || file.type === 'image/png' || file.type === 'image/jpg';
      if (!isJPG) {
        this.$message.error('请上传jpg、png、jpeg图片');
      }
      return isLt3M && isJPG;
    },
  },
  // ISSUE: 变量命名未使用 camel case 或其他风格
  components: { QzCard },
  data () {
    // 校验面积
    const validateArea = (rule, value, callback) => {
      if (this.form.classid === '1' && parseInt(value, 10) > 1000) {
        callback(new Error('房屋面积请输入1-1000之间的数字'));
      }
      if (this.form.classid === '2' && parseInt(value, 10) > 10000) {
        callback(new Error('房屋面积请输入1-10000之间的数字'));
      }
      callback();
    };
    return {
      upload: {
        cover: {
          aciton: `${QZ_CONFIG.OSS_AUDIO_HOST}cp/v1/upload/img`,
          headers: {
            token: localStorage.getItem('token'),
          },
          data: {
            prefix: '',
          },
          fileList: [],
          limit: 1,
          dialogVisible: false,
          previewImg: '',
        },
        content: {
          aciton: `${QZ_CONFIG.OSS_AUDIO_HOST}cp/v1/upload/img`,
          headers: {
            token: localStorage.getItem('token'),
          },
          data: {
            prefix: '',
          },
          fileList: [],
          limit: 14,
          dialogVisible: false,
          previewImg: '',
        },
      },
      option: {
        classid: [],
        huxing: [
          { id: '10', name: '普通户型' },
          { id: '11', name: '跃层户型' },
          { id: '12', name: '复式户型' },
          { id: '14', name: '别墅' },
          { id: '15', name: '其它' },
        ],
        designerList: [], //  设计师列表
        leixing: [],
        fengge: [],
        zaojia: [],
        qx: [],
      },
      form: {
        startEndTime: [],
        cname: '',
        classid: '', // 1：家装,2:公装,3:在建工地,4:3d效果图
        cs: '', // 城市
        qx: '', // 区县
        xiaoqu: '', // 小区
        xiaoquid: '', // 小区id,如果有的话
        huxing: '', // 户型
        leixing: '', // 房屋类型
        mianji: '', // 面积
        fengge: '', // 风格
        zaojia: '', // 合同造价
        start: '', // 装修开始时间
        end: '', // 装修结束时间
        imageCover: [], // 封面图片
        imageContent: [], // 内容图片
        designer: []// 设计师
      },
      rule: {
        mianji: [
          { required: true, message: '请输入房屋面积', trigger: 'blur' },
          {
            pattern: /^[1-9]\d*$/,
            message: '请输入正整数',
            trigger: 'blur',
          },
          { validator: validateArea, trigger: 'blur' },
        ],
      },
    };
  },
};
</script>

<style lang="less" scoped>
.info {
  width: 84px;
  height: 12px;
  color: #ff5353;
  font-weight: 400;
  font-size: 12px;
}
.add-case {
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
    height: 350px;
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
