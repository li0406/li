<template>
  <div class="content-note">
    <tableSearch title="笔记管理">
      <el-form slot="from" ref="ruleForm" :inline="true" :model="formData">
        <el-form-item label="标题" prop="goodsName">
          <el-input v-model="formData.goodsName" clearable placeholder="标题" />
        </el-form-item>
        <el-form-item label="发布来源" prop="goodsStatus">
          <el-select v-model="formData.goodsStatus" clearable placeholder="请选择">
            <el-option label="管理后台" value="1" />
            <el-option label="APP" value="2" />
            <el-option label="小程序" value="3" />
          </el-select>
        </el-form-item>
        <el-form-item label="审核状态" prop="goodsStatus">
          <el-select v-model="formData.checkStatus" clearable placeholder="请选择">
            <el-option label="待审核" value="1" />
            <el-option label="通过" value="2" />
            <el-option label="不通过" value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="标签" prop="goodsTypeId">
          <el-select v-model="formData.goodsTypeId" clearable placeholder="请选择">
            <el-option v-for="item in goodsTypeList" :key="item.id" :label="item.name" :value="item.id" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="onSearch">查询</el-button>
          <el-button @click="onReset">重置</el-button>
          <el-button type="success" @click="addNote">新建笔记</el-button>
        </el-form-item>
        <el-form-item class="fr">
          <el-button type="primary" @click="addNote">批量上架</el-button>
          <el-button type="primary" @click="addNote">批量下架</el-button>
        </el-form-item>
      </el-form>
      <el-table
        slot="table"
        :data="tableData"
        border
        style="width: 100%"
        @selection-change="handleSelectionChange"
      >
        <el-table-column type="selection" width="55" align="center" />
        <el-table-column label="图片" align="center">
          <template slot-scope="scope">
            <el-image
              v-if="scope.row.previewType == 'IMAGE'"
              style="width: 100px; height: 100px"
              :src="scope.row.imageUrls ? scope.row.imageUrls[0] : ''"
              :preview-src-list="scope.row.imageUrls"
              fit="fit"
            />
            <el-image
                v-if="scope.row.previewType == 'VIDEO'"
                style="width: 100px; height: 100px"
                :src="scope.row.videoUrl ? scope.row.videoUrl + '?vframe/jpg/offset/3' : ''"
                fit="fit"
                @click="clickBf(scope.row.videoUrl)"
            />
          </template>
        </el-table-column>
        <el-table-column prop="title" label="标题" align="center" />
        <el-table-column label="关联商品数" align="center">
          <template slot-scope="scope">
            <span>{{ scope.row.goodsList ? scope.row.goodsList.length : '' }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="channel" label="发布来源" align="center" />
        <el-table-column prop="collectCount" label="收藏" align="center" />
        <el-table-column prop="viewCount" label="浏览量" align="center" />
        <el-table-column label="内部标签" align="center">
          <template slot-scope="scope">
            <span>{{ scope.row.recommandStatus === '1' ? '已推荐' : '未推荐' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="审核状态" align="center">
          <template slot-scope="scope">
            <span v-if="scope.row.goodsStatus === '0'" class="red">待发布</span>
            <span v-else-if="scope.row.goodsStatus === '1'" class="green">已发布</span>
            <span v-else-if="scope.row.goodsStatus === '2'" class="red">下架</span>
          </template>
        </el-table-column>
        <el-table-column prop="createDate" label="审核人/审核时间" align="center" />
        <el-table-column label="操作" align="center">
          <template slot-scope="scope">
            <el-button type="text" @click="check(scope.row.id)">查看</el-button>
            <el-button type="text" @click="state(scope.row.id)">审核</el-button>
            <el-button type="text" @click="edit(scope.row.id)">编辑</el-button>
          </template>
        </el-table-column>
      </el-table>
      <el-pagination
        v-if="total"
        class="mt20 text-center"
        :current-page="currentPage"
        :page-sizes="[10, 20, 50, 100]"
        :page-size="pageSize"
        layout="total, sizes, prev, pager, next, jumper"
        :total="total"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
      />
      <el-dialog :title="detailInformation.title" :visible.sync="dialogDetailVisible" width="60%">
        <div style="overflow: hidden;margin-top: -30px;">
          <div :class="hidden === true ? 'left' : 'all'">
            <el-row>
              <el-col :span="8">发布人：{{ detailInformation.author.nickName }}</el-col>
              <el-col :span="8">{{ detailInformation.createDate }}</el-col>
              <el-col :span="8">来源：{{ detailInformation.channel }}</el-col>
            </el-row>
            <el-row>{{ detailInformation.content }}</el-row>
            <el-row>
              <h3>关联标签</h3>
              <el-tag>{{ detailInformation.tag ? detailInformation.tag : '暂无标签' }}</el-tag>
            </el-row>
            <el-row v-if="detailInformation.goodsList">
              <h3>关联商品</h3>
              <div class="good-list" v-for="(item,index) in detailInformation.goodsList" :key="index">
                <img :src="item.goodsCover" />
                <p class="name">{{ item.goodsName }}</p>
                <p>
                  <span>价格：<span class="price">￥{{ item.salePrice }}</span></span>
                  <span class="fr red">主推商品</span>
                </p>
                <p>推荐语：我推荐了这个宝贝</p>
              </div>
            </el-row>
            <el-row>
              <h3>{{detailInformation.previewType == 'IMAGE' ? '图片' : detailInformation.previewType == 'VIDEO' ? '视频' : '文字' }}</h3>
              <el-col :span="8" v-if="detailInformation.previewType == 'VIDEO'">
                <video :src="detailInformation.videoUrl" class="video" controls autoplay></video>
              </el-col>
              <el-col v-if="detailInformation.previewType == 'IMAGE'">
                <img class="detail-img" v-for="(item, i) in detailInformation.imageUrls" :src="item"  alt="">
              </el-col>
              <el-col v-if="detailInformation.previewType == 'TEXT'">
                <text>{{detailInformation.content}}</text>
              </el-col>
            </el-row>
          </div>
          <div class="right fr" v-if="hidden">
            <el-row>
              <h3>项目名称项目名称项目名称项目名称</h3>
            </el-row>
            <el-row>发布人：XXX</el-row>
            <el-row>当前状态：已发布</el-row>
            <el-row>审核：
              <el-radio v-model="radio" label="1">已发布</el-radio>
              <el-radio v-model="radio" label="2">下架</el-radio>
            </el-row>
            <el-row class="recommend">是否推荐：
              <el-switch v-model="isRecommend"> </el-switch>
              <el-input v-model="paiXu" />
            </el-row>
            <el-row class="mtp20">
              <el-input
                type="textarea"
                :rows="2"
                placeholder="请填写标签"
                v-model="tags">
              </el-input>
            </el-row>
            <el-row class="mtp20 center">
              <el-button type="primary">提交</el-button>
              <el-button @click="diaCancel">取消</el-button>
            </el-row>
            <el-row class="mtp20 center edit-btn">
              <el-col :span="12">
                <i class="el-icon-arrow-left" @click="pre"></i>
                <el-row class="hand" @click="pre">上一条</el-row>
              </el-col>
              <el-col :span="12">
                <i class="el-icon-arrow-right" @click="next"></i>
                <el-row class="hand" @click="next">下一条</el-row>
              </el-col>
            </el-row>
          </div>
        </div>
      </el-dialog>
    </tableSearch>
  </div>
</template>

<script>
export default {
  name: 'ContentNote',
  components: {},
  data() {
    return {
      formData: {
        goodsName: '',
        goodsNo: '',
        goodsStatus: '',
        checkStatus: '',
        goodsTypeId: ''
      },
      createTime: '',
      endTime: '',
      goodsTypeList: [],
      tableData: [],
      currentPage: 1,
      pageSize: 20,
      total: 0,
      dialogDetailVisible: false,
      radio: 1,
      isRecommend: true,
      paiXu: 0,
      tags: '111',
      detailInformation: {
        author: {}
      },
      hidden: true
    }
  },
  computed: {},
  watch: {},
  created() {
    this.getTagList()
    this.getNoteList()
  },
  methods: {
    // 获取标签列表
    getTagList() {
      this.$apis.CONTENT.getTagList().then(res => {
        if (res.code === 0) {
          this.goodsTypeList = res.data
        }
      })
    },
    onSearch() {
      this.getNoteList()
    },
    onReset() {
      this.$refs['ruleForm'].resetFields()
    },
    getNoteList() {
      this.$apis.CONTENT.getNoteList(
        {
          channel: 'NOTE',
          pageNo: this.currentPage,
          pageSize: 10
        }
      ).then(res => {
        if (res.code === 0) {
          this.tableData = res.data
          this.total = res.page.total
        }
      })
    },
    handleSizeChange(val) {
      this.currentPage = 1
      this.pageSize = val
      this.getNoteList()
    },
    handleCurrentChange(val) {
      this.currentPage = val
      console.log(this.currentPage)
      this.getNoteList()
    },
    getNoteDetail(id) {
      this.$apis.CONTENT.getNoteDetail(
        {
          id: id
        }
      ).then(res => {
        console.log(res)
        if (res.code === 0) {
          this.dialogDetailVisible = true
          this.detailInformation = res.data
          console.log(this.detailInformation)
        }
      })
    },
    check(id) {
      this.hidden = false
      this.getNoteDetail(id)
    },
    addNote() {
      this.$router.push('/content/add-note')
    },
    handleSelectionChange(val) {
      console.log(val)
    },
    // 启用禁用
    state(id, val) {
      this.hidden = true
      this.dialogDetailVisible = true
      this.getNoteDetail(id)
    },
    async getGoodsTypes() {
      const res = await this.$apis.COMMODITY.getGoodsTypes()
      if (res.code === 0) {
        this.goodsTypeList = res.data
      }
    },
    // 取消
    diaCancel() {
      this.dialogDetailVisible = false
    },
    pre() {
      console.log(11)
    },
    next() {
      console.log(22)
    },
    // 播放
    clickBf(e){
      this.$message('点击操作-查看视频播放');
    },
    edit(e){
      this.$router.push({
        path: '/content/add-note',
        query: {
          id: e,
        }
    });
    }
  }
}
</script>
<style rel=" stylesheet/scss" lang="scss" scoped>
.content-note{
  padding: 20px;
  .el-row{
    line-height: 40px;
  }
  .el-col{
    line-height: 40px;
  }
  .left{
    width: 65%;
    float: left;
  }
  .all{
    width:100%;
  }
  .right{
    width: 35%;
    padding: 0 20px;
  }
  .el-tag{
    margin-right: 15px;
  }
  .good-list{
    width: 48%;
    padding: 10px 20px;
    border: 1px solid #ccc;
    border-radius: 5px;
    float: left;
    margin-right: 4%;
    margin-bottom: 20px;
    img{
      max-width: 100%;
      max-height: 170px;
      display: block;
      margin: 0 auto;
    }
    .price{
      font-weight: bold;
      font-size: 16px;
    }
  }
  .good-list:nth-child(odd){
    margin-right: 0;
  }
  .el-row.recommend .el-input{
    width: 50%;
    margin-left: 20px;
  }
  .name{
    word-break:break-all;
    word-wrap:break-word;
    overflow: hidden;
    text-overflow:ellipsis;
    white-space: nowrap;
  }
  .mtp20{
    margin-top:20px;
  }
  .center{
    text-align: center;
  }
  .edit-btn{
    margin-top: 80px;
  }
  .edit-btn i{
    font-size: 30px;
    cursor: pointer;
  }
  .hand{
    cursor: pointer;
  }
  .detail-img{
    width: 200px;
    height: 200px;
    display: block;
    margin: 10px;
    float: left;
  }
}
</style>
