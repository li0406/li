<template>
  <div class="editPromotions">
    <h2>{{ !Number(eventForm.id) ? '添加' : '编辑'}}活动</h2>
    <div>
      <el-form
        :model="eventForm"
        ref="eventForm"
        :rules="eventFormRules"
        label-width="100px"
        class="demo-ruleForm"
      >
        <el-form-item label="活动标题" prop="eventName">
          <el-input v-model="eventForm.eventName" style="width: 350px"></el-input>
        </el-form-item>

        <el-form-item label="活动日期" prop="eventDate">
          <el-date-picker
            v-model="eventForm.eventDate"
            type="daterange"
            align="right"
            unlink-panels
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
            :picker-options="pickerOptions"
            value-format="yyyy-MM-dd"
            @change="dateChange"
          ></el-date-picker>
        </el-form-item>

        <el-form-item label="活动内容" prop="eventContent" class="ue-item">
          <UE
            ref="ue"
            :defaultMsg="eventForm.eventContent"
            :config="config"
            :id="ue1"
            class="fixlineheight"
          ></UE>
        </el-form-item>

        <el-form-item>
          <el-button type="danger" @click="sumbit('eventForm')">确认发布</el-button>
          <el-button type="danger" plain @click="$router.push('/promotions')">取消</el-button>
        </el-form-item>
      </el-form>
    </div>
  </div>
</template>

<script>
import api from '@/api/promotions';
import UE from '@/components/ue.vue';
import QZ_CONFIG from '@/utils/config';

export default {
  name: 'edit',

  data() {
    const validateUe = (rule, value, callback) => {
      if (this.$refs.ue.getUEContent() === '') {
        callback(new Error('请填写活动内容'));
      } else {
        callback();
      }
    };

    return {
      eventForm: {
        id: 0,
        eventName: '',
        eventDate: [],
        eventContent: '',
      },
      pickerOptions: {
        shortcuts: [
          {
            text: '未来一周',
            onClick(picker) {
              const end = new Date();
              const start = new Date();
              start.setTime(start.getTime() + 3600 * 1000 * 24 * 7);
              picker.$emit('pick', [end, start]);
            },
          },
          {
            text: '未来一个月',
            onClick(picker) {
              const end = new Date();
              const start = new Date();
              start.setTime(start.getTime() + 3600 * 1000 * 24 * 30);
              picker.$emit('pick', [end, start]);
            },
          },
          {
            text: '未来三个月',
            onClick(picker) {
              const end = new Date();
              const start = new Date();
              start.setTime(start.getTime() + 3600 * 1000 * 24 * 90);
              picker.$emit('pick', [end, start]);
            },
          },
        ],
        disabledDate(time) {
          return time.getTime() < Date.now() - 8.64e7;
        },
      },

      eventFormRules: {
        eventName: { required: true, message: '请输入活动标题', trigger: 'blur' },
        eventDate: { required: true, message: '请输入活动日期', trigger: 'blur' },
        eventContent: { required: true, validator: validateUe, trigger: 'blur' },
      },
      config: {
        initialFrameWidth: null,
        initialFrameHeight: 350,
        serverUrl: `${QZ_CONFIG.OSS_AUDIO_HOST}cp/v1/upload/img`,
        token: localStorage.getItem('token'),
        imageActionName: 'jkjhkj',
        imageAllowFiles: ['.jpg', '.gif', '.png', '.jpeg'],
        imageMaxSize: 60000000,
        imageUrlPrefix: 'https://staticqn.qizuang.com/',
      },
      ue1: 'ue1',
    };
  },

  created() {
    this.eventForm.id = this.$route.params.id;
    if (Number(this.eventForm.id) !== 0) {
      this.init(this.eventForm.id);
    }
  },

  methods: {
    init(id) {
      api.getDetail(id).then((res) => {
        this.eventForm.eventName = res.data.data.title;
        this.eventForm.eventContent = res.data.data.text;
        this.eventForm.eventDate.push(new Date(res.data.data.start * 1000), new Date(res.data.data.end * 1000));
        this.eventForm.content = res.data.data.text;
        this.$refs.ue.editor.body.innerHTML = res.data.data.text;
      });
    },
    dateChange() {
    },

    sumbit(eventForm) {
      // eslint-disable-next-line consistent-return
      this.$refs[eventForm].validate((valid) => {
        if (valid) {
          const params = {
            id: this.eventForm.id,
            title: this.eventForm.eventName,
            start: new Date(this.eventForm.eventDate[0]),
            end: new Date(this.eventForm.eventDate[1]),
            content: this.$refs.ue.getUEContent(),
          };
          api.saveDetail(params).then((res) => {
            if (res.data.error_code === 0) {
              this.$router.push('/promotions');
            } else {
              this.$message.error('保存失败');
            }
          });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
  },

  components: {
    // VueUeditorWrap,
    UE,
  },
};
</script>


<style lang="scss">
.ue-item {
  .el-form-item__content {
    line-height: 20px !important;
  }
}
</style>

<style scoped lang="scss">
.editPromotions {
  margin-bottom: 200px;
  padding: 20px;
  background: #fff;

  h2 {
    margin: 0 0 30px 0;
    font-weight: 400;
  }
}
</style>


