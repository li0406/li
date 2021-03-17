<template>
  <div class="base-company" v-loading="loading">
    <el-form
      ref="ruleForm"
      :model="ruleForm"
      status-icon
      :rules="rules"
      label-position="right"
      label-width="100px"
      class="demo-ruleForm"
    >
      <el-row>
        <el-col :span="12" class="title">
          请选择服务标签，最多只能选择
          <span>3</span>项
          <span class="sm">(请慎重选择符合公司标准的服务标签)</span>
        </el-col>
      </el-row>
      <el-form-item label-width="0px">
        <el-checkbox-group v-model="ruleForm.fwtags" :max="3" class="checks">
          <el-checkbox-button v-for="item in fwList" :label="item.id" :key="item.id">{{item.tag}}</el-checkbox-button>
        </el-checkbox-group>
      </el-form-item>
      <div class="more" @click="bindMore">{{tagshow ? '收起' : '查看更多公司标签'}}</div>
      <div class="tagshow" v-show="tagshow">
        <el-row>
          <el-col :span="6" class="title">
            请选择公司标签，最多只能选择
            <span>5</span>项
          </el-col>
        </el-row>
        <el-form-item label-width="0px">
          <el-checkbox-group v-model="ruleForm.tags" :max="5">
            <div class="checks" v-for="(item) in tagsList" :key="item.name">
              <div class="check-title">{{item.name}}:</div>
              <el-checkbox-button v-for="(it) in item.tags" :key="it.id" :label="it.id">{{it.name}}</el-checkbox-button>
            </div>
          </el-checkbox-group>
        </el-form-item>
        <el-row class="tips">
          <el-col :span="1" class="gap">已选标签:</el-col>
          <span v-for="item in selectTags" :key="item.id">{{item.name}}</span>
        </el-row>
      </div>
      <el-form-item class="last-item">
        <el-button type="danger" @click="submitForm('ruleForm')">保存</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import base from '@/api/base-info';

export default {
  name: 'Basedetail',
  data() {
    return {
      ruleForm: {
        tags: [],
        fwtags: [],
      },
      rules: {},
      tagsList: [
        {
          name: '公司',
          tags: [
            { id: 1, name: '全国连锁企业' },
            { id: 2, name: '本土知名企业' },
            { id: 3, name: '本地老牌公司' },
          ],
        },
        {
          name: '展厅',
          tags: [{ id: 4, name: '全景3D设计图' }],
        },
        {
          name: '资质',
          tags: [
            { id: 5, name: '公装一级资质' },
            { id: 6, name: '公装二级资质' },
          ],
        },
        {
          name: '经营',
          tags: [
            { id: 7, name: '整装拎包入住' },
            { id: 8, name: '主营旧房改造' },
          ],
        },
        {
          name: '口碑 ',
          tags: [
            { id: 9, name: '业主好评率高' },
            { id: 10, name: '公司资质优良' },
            { id: 11, name: '回头客多' },
          ],
        },
        {
          name: '报价',
          tags: [
            { id: 12, name: '报价合理透明' },
            { id: 13, name: '性价比高' },
          ],
        },
        {
          name: '团队',
          tags: [
            { id: 14, name: '资深设计团队' },
            { id: 15, name: '设计创意新颖' },
            { id: 16, name: '主营大宅别墅' },
            { id: 17, name: '设计私人定制' },
            { id: 18, name: '擅长中式风格' },
            { id: 19, name: '擅长欧式风格' },
          ],
        },
        {
          name: '装修',
          tags: [
            { id: 20, name: '工地不转包' },
            { id: 21, name: '工地管理规范' },
            { id: 22, name: '工期不拖延' },
            { id: 23, name: '施工无增项' },
            { id: 24, name: '施工质量好' },
          ],
        },
        {
          name: '材料',
          tags: [
            { id: 25, name: '材料绿色环保' },
            { id: 26, name: '材料展厅大' },
            { id: 27, name: '材料一线品牌' },
          ],
        },
        {
          name: '售后',
          tags: [
            { id: 28, name: '售后有保障' },
            { id: 29, name: '先装修后付款' },
            { id: 30, name: '提供贷款服务' },
          ],
        }
      ],
      tags: [],
      loading: false,
      tagshow: false,
      fwList: [],
      fwId: []
    };
  },
  computed: {
    selectTags() {
      const arrId = this.ruleForm.tags;
      const arrTags = this.tags;
      return arrTags.filter((ele) => arrId.filter((x) => x === ele.id).length > 0);
    },
  },
  created() {
    const tagsArr = this.tagsList.map((item) => {
      return item.tags;
    });
    let tags = [];
    tagsArr.forEach((item) => {
      tags = [...tags, ...item];
    });
    this.tags = tags;
    this.taglist();
  },
  methods: {
    taglist() {
      base.taglist({ tag_mode: 2 }).then((res) => {
        // eslint-disable-next-line radix
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          const fwList = res.data.data;
          this.fwList = res.data.data;
          this.fwId = fwList.map((item) => item.id)
          this.getData();
        } else {
          this.$message({
            message: res.data.error_msg,
            type: 'error',
          });
        }
      });
    },
    getData() {
      base.tags({}).then((res) => {
        // eslint-disable-next-line radix
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          const tagsAll = res.data.data;
          const fwId = this.fwId;
          const fwtags = tagsAll.filter(function(v){ return fwId.indexOf(v) > -1 })
          this.ruleForm.fwtags= fwtags
          this.ruleForm.tags = tagsAll.filter(function(v){ return fwtags.indexOf(v) === -1 })
        } else {
          this.$message({
            message: res.data.error_msg,
            type: 'error',
          });
        }
      });
    },
    submitForm(formName) {
      const query = {
        tags : [...this.ruleForm.tags, ...this.ruleForm.fwtags]
      }
      this.loading = true;
      // eslint-disable-next-line consistent-return
      this.$refs[formName].validate((valid) => {
        if (valid) {
          this.loading = true;
          base.editTags(query).then((res) => {
            // eslint-disable-next-line radix
            if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
              this.loading = false;
              this.$message({
                message: '上传成功',
                type: 'success',
              });
            } else {
              this.loading = false;
              this.$message({
                message: res.data.error_msg,
                type: 'error',
              });
            }
          });
        } else {
          return false;
        }
      });
    },
    bindMore() {
      this.tagshow = !this.tagshow;
    },
  },
};
</script>

<style lang="less">
.base-company {
  min-height: 1000px;
  .title {
    color: rgba(102, 102, 102, 1);
    font-weight: bold;
    font-size: 14px;
    line-height: 60px;

    span {
      color: #ff5353;
    }
    .sm {
      margin-left: 20px;
      font-size: 12px;
      line-height: 20px;
    }
  }

  .checks {
    margin-top: 20px;
    line-height: 30px;

    .el-checkbox-button__inner {
      margin-right: 10px;
      color: #666;
      font-size: 12px;
      vertical-align: initial;
      background-color: #fafafa;
      border: none;
      border-radius: 2px;
      box-shadow: none;
    }

    .el-checkbox-button__inner:hover {
      color: #666;
    }

    .check-title {
      display: inline-block;
      width: 100px;
      color: rgba(51, 51, 51, 1);
      font-weight: 400;
      font-size: 12px;
      line-height: 30px;
      text-align: center;
    }

    .el-checkbox-button.is-checked .el-checkbox-button__inner {
      color: #ff5353;
      background-color: #fff7f7;
      border-radius: 2px;
      box-shadow: none;
    }
  }
  .tips {
    padding-bottom: 20px;
    font-size: 12px;
    .gap {
      margin-right: 32px;
      padding-left: 12px;
      font-weight: bold;
    }
    span {
      display: inline-block;
      height: 36px;
      margin-right: 10px;
      padding: 0 12px;
      color: #fff;
      line-height: 36px;
      text-align: center;
      background: #f56c6c;
      border-radius: 2px;
    }
  }
  .more {
    color: #ff5353;
    font-weight: bold;
    font-size: 14px;
    line-height: 60px;
    cursor: pointer;
  }
  .last-item {
    padding-top: 40px;
    padding-bottom: 200px;
  }
}
</style>
