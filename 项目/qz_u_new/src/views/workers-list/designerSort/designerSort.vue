<template>
  <div class="designerSort">
    <div class="title">设计师排序</div>
    <div>
      <div class="tips">请拖拽设计师头像进行排序</div>
      <div class="tips">仅展示设计师职位以及首页推荐人员，网店首页按顺序展示前五位</div>
      <div class="demo-contain">
        <draggable v-model="items"
                   :animation='200'
                   @start="drag = true"
                   @end="drag = false">
          <transition-group class="container">
            <div class="item"
                 v-for="item in items"
                 :key="item.id">
              <div class="item-outline">
                <div class="mod">
                  <i class="el-icon-rank icon-arrow"></i>
                  <div>拖拽头像排序</div>
                </div>
                <img class="item-imgs"
                     :src="item.logo"
                     alt />
              </div>

              <div class="item-msg">
                <div>{{ item.nick_name }}</div>
                <div>{{ item.position }}</div>
              </div>
            </div>
          </transition-group>
        </draggable>
        <div class="btns">
          <el-button class="btn-back"
                     @click="goBack()">返回</el-button>
          <el-button type="danger"
                     @click="save()">确定</el-button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/api/staff';
import draggable from 'vuedraggable';

export default {
  name: 'Demo',
  components: {
    draggable,
  },
  data () {
    return {
      items: [],
      dragging: null,
    };
  },
  created () {
    this.designerlist();
  },
  methods: {
    //  员工管理-设计师排序列表
    designerlist () {
      const params = {};
      api
        .designerlist(params)
        .then((res) => {
          if (res.data.error_code === 0) {
            // 0成功 其余失败
            this.items = res.data.data;
            // console.log(res.data.data);
          } else {
            this.$message.error(res.data.error_msg);
          }
        })
        .catch((err) => {
          this.$message.error(err);
        });
    },
    //  员工管理-设置设计师排序
    designersortup () {
      const params = {};
      api
        .designersortup(params)
        .then((res) => {
          if (res.data.error_code === 0) {
            // 0成功 其余失败
          } else {
            this.$message.error(res.data.error_msg);
          }
        })
        .catch((err) => {
          this.$message.error(err);
        });
    },
    goBack () {
      this.$router.push({
        path: '/workers-list',
      });
    },
    save () {
      const designer = [];
      this.items.forEach((item, index) => {
        const obj = {};
        obj.id = item.id;
        obj.sort = index + 1;
        designer.push(obj);
      });
      const params = {
        designer,
      };
      api
        .designersortup(params)
        .then((res) => {
          if (res.data.error_code === 0) {
            // 0成功 其余失败
            this.goBack();
            this.$message({
              message: '提交成功！',
              type: 'success',
            });
          } else {
            //  失败
            this.$message.error(res.data.error_msg);
          }
        })
        .catch((err) => {
          this.$message.error(err);
        });
    },
  },
};
</script>

<style scoped>
.btns {
  display: flex;
  margin: 20px 0 100px 30px;
}
/* 取消按钮样式覆盖 */
.btn-back {
  color: #606266;
  border: 1px solid #dcdfe6;
}
.btn-back:hover {
  color: #f56c6c;
  background-color: rgba(255, 239, 239, 1);
  border: 1px solid #f56c6c;
}
.designerSort {
  background: #fff;
}
/* 标题 */
.title {
  width: 100%;
  height: 60px;
  padding-left: 30px;
  color: #303133;
  font-weight: 500;
  font-size: 16px;
  line-height: 60px;
  border-bottom: 2px solid #e4e7ed;
}
/* 提示 */
.tips {
  width: 100%;
  margin: 20px 0;
  padding-left: 30px;
  color: #303133;
  font-weight: 500;
  font-size: 16px;
}
.tips:nth-of-type(2) {
  color: #909399;
  font-size: 7px;
}
.demo-contain {
  background-color: #fff;
}
.container {
  display: flex;
  flex-wrap: wrap;
}
.item {
  width: 220px;
  height: 280px;
  margin: 0 20px 20px 30px;
  padding-top: 30px;
  border: 1px solid #dcdfe6;
  box-shadow: 0 0 5px #dcdfe6;
}
.item-outline {
  position: relative;
  width: 120px;
  height: 120px;
  margin: 0 auto;
  border-radius: 50%;
}
.mod {
  position: absolute;
  display: none;
  width: 120px;
  height: 120px;
  margin: 0 auto;
  text-align: center;
  background: rgba(0, 0, 0, 1);
  border-radius: 50%;
  cursor: move;
  opacity: 0.4;
}
.mod > img {
  width: 30px;
  height: 30px;
  margin-top: 50px;
}
.mod > div {
  margin-top: 10px;
  color: #fff;
  font-size: 15px;
}
.item-outline:hover > .mod {
  display: block;
}
.item-imgs {
  width: 100%;
  height: 100%;
  border-radius: 50%;
}
.item-msg > div:nth-of-type(1) {
  margin-top: 40px;
  text-align: center;
}
.item-msg > div:nth-of-type(2) {
  margin-top: 20px;
  text-align: center;
}
.icon-arrow {
  margin: 30px 0 0 0;
  color: #fff;
  font-size: 35px;
}
</style>
