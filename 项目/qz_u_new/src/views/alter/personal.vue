<template>
  <div class="personal-contains" v-loading="loading">
    <el-card class="box-card">
      <div class="clearfix">
        <span>个人中心</span>
      </div>
    </el-card>
    <el-form label-width="100px" class="demo-ruleForm">
      <el-form-item label="头像：">
        <el-col class="logo-item">
          <img :src="logo" alt />
        </el-col>
        <el-col :span="6">
          <el-popover placement="right-start" width="400" trigger="hover">
            <div class="popcontent">
              <div class="img">
                <img src="@/assets/people.png" alt />
              </div>
              <div class="content">
                <span>头像上传要求(参照左图标准头像)</span>
                <br />1.人物免冠正面彩色半身照；
                <br />2.人物面部完整，无遮挡，光线充足；
                <br />3.衣物色彩与背景对比明显，清晰度高；
                <br />4.照片不含有水印，联系方式等广告信息；
                <br />5.其他(包含盗用明星照片等)
              </div>
            </div>
            <span class="tips" slot="reference">上传头像要求</span>
          </el-popover>
        </el-col>
      </el-form-item>
      <el-form-item label="姓名：">
        <el-col class="form-item">
          <div class="details">{{datas.nick_name}}</div>
        </el-col>
      </el-form-item>
      <el-form-item label="职位：">
        <el-col class="form-item">
          <div class="details">{{positionData.label}}</div>
        </el-col>
      </el-form-item>
      <el-form-item label="工作经验：">
        <el-col class="form-item">
          <div class="details">{{datas.experience}}</div>
        </el-col>
      </el-form-item>
      <el-row>
        <el-col :span="4">
          <el-form-item label="设计收费：" v-show="isMore">
            <div class="details">{{datas.charge}}元/平米</div>
          </el-form-item>
        </el-col>
      </el-row>
      <el-form-item label="擅长风格：" class="tipitem" v-show="isMore">
        <span v-for="item in fgList" :key="item.id">{{item.name}}</span>
      </el-form-item>
      <el-form-item label="擅长领域：" class="tipitem" v-show="isMore">
        <span v-for="item in lyList" :key="item.id">{{item.name}}</span>
      </el-form-item>
      <el-form-item label="荣誉奖励：">
        <div class="details">{{datas.honor}}</div>
      </el-form-item>
      <el-form-item label="代表作品：" v-show="isMore">
        <div class="details">{{datas.works}}</div>
      </el-form-item>
      <el-form-item label="设计理念：" v-show="isMore">
        <div class="details">{{datas.concept}}</div>
      </el-form-item>
      <el-form-item label="个人简介：">
        <div class="details">{{datas.profile}}</div>
      </el-form-item>
      <el-form-item class="last-btn">
        <el-button type="danger" @click="goEdit">编辑</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import alter from '@/api/alter';

export default {
  name: 'Personal',
  data() {
    return {
      options: [
        {
          value: '',
          label: '请选择职位',
        },
        {
          value: '1',
          label: '客服',
        },
        {
          value: '2',
          label: '设计师',
        },
        {
          value: '3',
          label: '首席设计师',
        },
        {
          value: '4',
          label: '设计总监',
        },
      ],
      fg: [
        { id: '现代简约', name: '现代简约' },
        { id: '欧式风格', name: '欧式风格' },
        { id: '中式风格', name: '中式风格' },
        { id: '古典风格', name: '古典风格' },
        { id: '田园风格', name: '田园风格' },
        { id: '地中海风格', name: '地中海风格' },
        { id: '美式风格', name: '美式风格' },
        { id: '混搭风格', name: '混搭风格' },
        { id: '其他', name: '其他' },
      ],
      ly: [
        { id: '住宅公寓', name: '住宅公寓' },
        { id: '写字楼', name: '写字楼' },
        { id: '别墅', name: '别墅' },
        { id: '专卖展示店', name: '专卖展示店' },
        { id: '酒店宾馆', name: '酒店宾馆' },
        { id: '餐饮酒吧', name: '餐饮酒吧' },
        { id: '歌舞迪厅', name: '歌舞迪厅' },
        { id: '其他', name: '其他' },
      ],
      account: '',
      loading: false,
      datas: {},
      positionData: {},
      fgList: [],
      lyList: [],
      logo: '',
      isMore: false,
    };
  },
  computed: {},
  created() {
    this.getData();
  },
  methods: {
    getData() {
      this.loading = true;
      alter.getCenter().then((res) => {
        if (parseInt(res.status, 10) === 200 && parseInt(res.data.error_code, 10) === 0) {
          this.loading = false;
          const datas = res.data.data;
          const positionData = this.options.find(function(obj) {
            return obj.label === datas.position;
          });
          if (positionData.value !== '1') {
            const fgList = this.fg.filter((ele) => {
              return datas.fengge.filter((x) => x === ele.id).length > 0;
            });
            const lyList = this.ly.filter((ele) => {
              return datas.lingyu.filter((x) => x === ele.id).length > 0;
            });
            this.fgList = fgList;
            this.lyList = lyList;
            // 区分客服与设计
            this.isMore = true;
          }
          this.logo = datas.logo;
          this.datas = datas;
          this.positionData = positionData;
        } else {
          this.loading = false;
          this.$message({
            message: res.data.error_msg,
            type: 'error',
          });
        }
      });
    },
    goEdit() {
      this.$router.push({
        path: '/editPersonal',
      });
    },
  },
};
</script>

<style lang="less">
.personal-contains {
  width: 100%;
  height: 100%;
  background: #fff;
  .demo-ruleForm {
    margin-top: 20px;
  }
  .tips {
    color: rgba(233, 71, 71, 1);
    font-weight: 400;
    font-size: 12px;
    cursor: pointer;
  }
  .logo-item {
    width: 120px;
    img {
      display: block;
      width: 60px;
      height: 60px;
      border-radius: 50%;
    }
  }
  .el-form-item__label {
    font-size: 12px;
  }

  .el-input__inner {
    height: 36px;
    padding: 0 10px;
    padding-right: 32px;
    font-size: 12px;
    line-height: 36px;
  }
  .pay-title {
    margin-left: 10px;
    color: rgba(48, 49, 51, 1);
    font-weight: 400;
    font-size: 12px;
    line-height: 42px;
  }
  .form-item {
    width: 240px;
  }
  .el-select {
    width: 100%;
  }
  .el-checkbox__label {
    font-size: 12px;
  }
  .el-textarea {
    font-size: 12px;
  }
  .item-words {
    float: right;
    font-size: 12px;
  }

  .avatar {
    display: block;
    width: 60px;
    height: 60px;
    border-radius: 50%;
  }

  .picOne {
    .el-upload--picture-card {
      display: none;
    }
  }
  .el-form-item {
    margin-bottom: 12px;
  }
  .details {
    width: 500px;
    overflow: hidden;
    color: #606266;
    font-size: 12px;
  }
  .tipitem {
    width: 600px;
    span {
      display: inline-block;
      height: 32px;
      margin-right: 10px;
      margin-bottom: 10px;
      padding: 0 16px;
      color: rgba(96, 98, 102, 1);
      font-weight: 400;
      font-size: 12px;
      line-height: 32px;
      background: rgba(255, 255, 255, 1);
      border: 1px solid rgba(220, 223, 230, 1);
      border-radius: 16px;
    }
  }
  .last-btn {
    margin-bottom: 200px;
  }
  .el-card.is-always-shadow {
    box-shadow: none;
  }
  .el-card {
    border: none;
    border-bottom: 2px solid #e4e7ed;
  }
}
</style>
<style lang="less" scoped>
.popcontent {
  width: 340px;
  .img {
    float: left;
    width: 80px;
    height: 80px;
    img {
      display: block;
      width: 80px;
      margin-top: 20px;
    }
  }
  .content {
    float: right;
    color: rgba(144, 147, 153, 1);
    font-weight: 400;
    font-size: 12px;
    line-height: 28px;
    span {
      color: rgba(48, 49, 51, 1);
      font-weight: 400;
      font-size: 14px;
      line-height: 40px;
    }
  }
}
</style>
