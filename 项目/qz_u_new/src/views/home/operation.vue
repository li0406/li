// 操作记录
<template>
  <div class="operation">
    <div v-for="i in data" :key="i.name">
      <div>{{ i.name }}</div>
      <div v-for="(j,index) in i.child" :key="index">
        <el-row>
          <el-col :span="8">
            <p>
              <span>
                <i class="el-icon-time"></i>
              </span>
              <qz-date-format :date="j.time"></qz-date-format>
            </p>
          </el-col>
          <el-col :span="16">
            <p>{{ j.content }}</p>
            <p>{{ j.remark }}</p>
          </el-col>
        </el-row>
      </div>
    </div>
  </div>
</template>

<script>
import ApiHome from '@/api/home';
import QzDateFormat from '@/components/date-format.vue';

export default {
  components: {
    QzDateFormat,
  },
  async created() {
    // FIXME: 添加滚动到距离底部百分之多少拉取数据，拉满十天
    const res0 = await ApiHome.operation({ day: 0 });
    const res1 = await ApiHome.operation({ day: 1 });
    const res2 = await ApiHome.operation({ day: 2 });
    const res3 = await ApiHome.operation({ day: 3 });
    const res4 = await ApiHome.operation({ day: 4 });
    const res5 = await ApiHome.operation({ day: 5 });
    const res6 = await ApiHome.operation({ day: 6 });
    const res7 = await ApiHome.operation({ day: 7 });
    const res8 = await ApiHome.operation({ day: 8 });
    const res9 = await ApiHome.operation({ day: 9 });

    this.data = this.data.concat(
      res0.data.data,
      res1.data.data,
      res2.data.data,
      res3.data.data,
      res4.data.data,
      res5.data.data,
      res6.data.data,
      res7.data.data,
      res8.data.data,
      res9.data.data,
    );
  },
  data() {
    return {
      data: [],
    };
  },
};
</script>

<style scoped>
.operation {
  height: 450px;
  overflow-y: scroll;
}
</style>
