<template>
  <div class="goods-addedit">
    <editPage :title="pageTitle">
      <template slot="btn">
        <el-button v-if="!id" type="primary" @click="save(1)">直接上架</el-button>
        <el-button type="primary" @click="save(0)">保存</el-button>
        <el-button @click="close">关闭</el-button>
      </template>
      <div class="from">
        <el-form ref="ruleForm" :rules="rules" :model="formData" label-width="100px">
          <div class="sub-title">
            <h3>基础信息设置</h3>
          </div>
          <el-form-item label="商品名称" prop="goodsName">
            <el-input v-model="formData.goodsName" placeholder="商品名称" />
          </el-form-item>
          <el-form-item label="商品分类" prop="goodsTypeId">
            <el-select v-model="formData.goodsTypeId" placeholder="商品分类">
              <el-option v-for="item in goodsTypeList" :key="item.id" :label="item.typeName" :value="item.id" />
            </el-select>
          </el-form-item>
          <el-form-item label="排序值" prop="sort">
            <el-input v-model.number="formData.sort" placeholder="排序值" />
          </el-form-item>
          <el-form-item label="是否推荐" prop="resource">
            <el-radio-group v-model="formData.recommandStatus">
              <el-radio label="1">推荐该商品</el-radio>
              <el-radio label="0">不推荐该商品（默认）</el-radio>
            </el-radio-group>
          </el-form-item>
          <div class="sub-title">
            <h3>购买属性设置</h3>
            <span>（最多可自定义10个属性）</span>
            <el-button
              type="success"
              icon="el-icon-plus"
              size="mini"
              round
              @click="skuAddLine"
            >添加</el-button>
          </div>
          <el-table class="attribute" :data="goodsDetailVoList" style="width: 810px">
            <el-table-column prop="date" width="110">
              <template slot="header">
                <span><i class="red mr4">*</i>产品名称</span>
              </template>
              <template slot-scope="scope">
                <el-input v-model="scope.row.goodsSkuName" placeholder="产品名称" />
              </template>
            </el-table-column>
            <el-table-column prop="date" width="210" label="产品描述">
              <template slot-scope="scope">
                <el-input v-model="scope.row.goodsSkuDesc" class="w200" placeholder="产品描述" />
              </template>
            </el-table-column>
            <el-table-column prop="date" width="110">
              <template slot="header">
                <span><i class="red mr4">*</i>齐装采购价</span>
              </template>
              <template slot-scope="scope">
                <el-input v-model="scope.row.purchasePrice" placeholder="齐装采购价" />
              </template>
            </el-table-column>
            <el-table-column prop="date" width="110">
              <template slot="header">
                <span><i class="red mr4">*</i>齐装供销价</span>
              </template>
              <template slot-scope="scope">
                <el-input v-model="scope.row.supplyPrice" placeholder="齐装供销价" />
              </template>
            </el-table-column>
            <el-table-column prop="date" width="110">
              <template slot="header">
                <span><i class="red mr4">*</i>市场价</span>
              </template>
              <template slot-scope="scope">
                <el-input v-model="scope.row.normalPrice" placeholder="市场价" />
              </template>
            </el-table-column>
            <el-table-column prop="date" width="110" label="排序">
              <template slot-scope="scope">
                <el-input v-model="scope.row.detailsSort" placeholder="排序" />
              </template>
            </el-table-column>
            <el-table-column prop="date" width="50">
              <template slot-scope="scope">
                <el-button type="text" class="fs20" icon="el-icon-delete" @click="skuDelete(scope.$index)" />
              </template>
            </el-table-column>
          </el-table>
          <div class="sub-title">
            <h3>产品规格</h3>
            <span>（最多可自定义10个属性）</span>
            <el-button
              type="success"
              icon="el-icon-plus"
              size="mini"
              round
              @click="attrAddLine"
            >添加</el-button>
          </div>
          <el-table class="attribute" :data="goodsParameterList" style="width: 580px">
            <el-table-column width="210">
              <template slot="header">
                <span><i class="red mr4">*</i>属性名称</span>
              </template>
              <template slot-scope="scope">
                <el-input v-model="scope.row.goodsFormatName" class="w200" placeholder="属性名称" />
              </template>
            </el-table-column>
            <el-table-column width="210">
              <template slot="header">
                <span><i class="red mr4">*</i>属性值</span>
              </template>
              <template slot-scope="scope">
                <el-input v-model="scope.row.goodsFormatValue" class="w200" placeholder="属性值" />
              </template>
            </el-table-column>
            <el-table-column width="110" label="排序">
              <template slot-scope="scope">
                <el-input v-model="scope.row.sort" placeholder="排序" />
              </template>
            </el-table-column>
            <el-table-column width="50">
              <template slot-scope="scope">
                <el-button type="text" class="fs20" icon="el-icon-delete" @click="deleteAttr(scope.$index)" />
              </template>
            </el-table-column>
          </el-table>
          <div class="sub-title">
            <h3>商品主图</h3>
            <span>（最多上传5张，最少1张）</span>
          </div>
          <el-form-item prop="goodsCover">
            <el-upload
              class="upload"
              :action="action"
              :limit="5"
              :on-success="handleUploadSuccess"
              :data="uploadExtendData"
              :before-upload="beforeAction"
              :on-preview="handlePreview"
              :on-remove="handleRemove"
              :on-exceed="handleExceed"
              :file-list="fileList"
              list-type="picture-card"
            >
              <div slot="file" slot-scope="{file}" style="width:100%;height:100%">
                <el-image v-if="file.status === 'success' && file.url" class="el-upload-list__item-thumbnail" :src="file.url" alt="" />
                <el-progress v-if="file.status === 'uploading'" type="circle" :percentage="parseInt(file.percentage)" />
                <div class="btn-tips">
                  <el-button v-if="file.ifDefult === '1'" type="text" size="mini" disabled>封面图</el-button>
                  <el-button v-else type="text" size="mini">设置封面图</el-button>
                </div>
                <span class="el-upload-list__item-actions">
                  <span
                    class="el-upload-list__item-preview"
                    @click="handlePreview(file)"
                  >
                    <i class="el-icon-zoom-in" />
                  </span>
                  <span class="el-upload-list__item-delete" @click="handleRemove(file)">
                    <i class="el-icon-delete" />
                  </span>
                  <span class="pic-cover">
                    <el-button v-if="file.ifDefult === '1'" type="text" size="mini" disabled>封面图</el-button>
                    <el-button v-else type="text" size="mini" @click="setPicCover(file)">设置封面图</el-button>
                  </span>
                </span>
              </div>
              <el-button size="small" type="primary">点击上传</el-button>
              <div slot="tip" class="el-upload__tip">
                图片标准：图片标准尺寸为700*700，大小在1M以内，图片必须保证清晰。
              </div>
            </el-upload>
            <el-dialog :visible.sync="dialogVisible" width="30%">
              <img width="100%" :src="dialogImageUrl" alt="">
            </el-dialog>
          </el-form-item>
          <div class="sub-title">
            <h3>商品描述</h3>
          </div>
          <vue-ueditor-wrap v-model="formData.goodsLongpic" :config="myConfig" />
        </el-form>
      </div>
    </editPage>
  </div>
</template>

<script>
import VueUeditorWrap from 'vue-ueditor-wrap'
import { getGoodsDetail } from '@/api/goods'
export default {
  name: 'GoodsAddedit',
  components: {
    VueUeditorWrap
  },
  data() {
    var verificationInteger = (rule, value, callback) => {
      if (!value) {
        callback()
      } else {
        const reg = /^[+]{0,1}(\d+)$/
        if (reg.test(value)) {
          callback()
        } else {
          return callback(new Error('请输入整数'))
        }
      }
    }

    return {
      action: '',
      myConfig: {
        autoHeightEnabled: false,
        // 初始容器高度
        initialFrameHeight: 440,
        // 初始容器宽度
        initialFrameWidth: '100%',
        imageAllowFiles: ['.jpg', '.gif', '.png', '.jpeg'],
        // 上传文件接口
        serverUrl: process.env.VUE_APP_BASE_API + '/console/ueditor/exec',
        UEDITOR_HOME_URL: '/UEditor/'
      },
      id: '',
      pageTitle: '商品添加',
      goodsTypeList: [],
      goodsType: [],
      goodsDetailVoList: [
        {
          goodsSkuName: '',
          goodsSkuDesc: '',
          purchasePrice: '',
          supplyPrice: '',
          normalPrice: '',
          detailsSort: ''
        }
      ],
      goodsParameterList: [
        {
          goodsFormatName: '',
          goodsFormatValue: '',
          sort: ''
        }
      ],
      formData: {
        goodsName: '', // 商品名
        goodsCover: '', // 封面图
        addGoodsDetailDTOList: [],
        addGoodsParameterDTOList: [],
        goodsPicList: [],
        recommandStatus: '0'
      },
      rules: {
        goodsName: [
          { required: true, message: '请输入商品名称', trigger: 'change' }
        ],
        goodsTypeId: [
          { required: true, message: '请选择商品分类', trigger: 'change' }
        ],
        sort: [
          { validator: verificationInteger, trigger: 'blur' }
        ],
        goodsCover: [
          { required: true, message: '选择封面图', trigger: 'change' }
        ]
      },
      postForm: {
        content: ''
      },
      uploadExtendData: {},
      fileList: [],
      dialogImageUrl: '',
      dialogVisible: false
    }
  },
  computed: {},
  watch: {},
  created() {
    this.action = this.$config.sevenCattleUrl
    this.getGoodsTypes()
    this.getSevenCattleToken()
    if (this.$route.query.id) {
      this.id = this.$route.query.id
      this.getDetail()
      this.pageTitle = '商品编辑'
    } else {
      this.pageTitle = '商品添加'
    }
  },
  mounted() {},
  methods: {
    save(val) {
      this.$refs['ruleForm'].validate((valid) => {
        const obj = this.handleSetData(val)
        if (!obj) {
          this.$message.warning('正确输入*各项')
        }
        if (valid && obj) {
          this.$apis.COMMODITY.goodsSave(obj).then(res => {
            if (res.code === 0) {
              this.$message.success(res.message)
              this.$router.push('/wares/goods-manage')
            } else {
              this.$message.error(res.message)
            }
          })
        }
      })
    },
    handleSetData(val) {
      const reg = /^(([1-9][0-9]*)|(([0]\.\d{1,2}|[1-9][0-9]*\.\d{1,2})))$/
      const goodsDetailVoList = this.goodsDetailVoList
      const goodsParameterList = this.goodsParameterList
      for (var i in goodsDetailVoList) {
        if (
          !goodsDetailVoList[i].goodsSkuName ||
          !reg.test(goodsDetailVoList[i].purchasePrice) ||
          !reg.test(goodsDetailVoList[i].supplyPrice) ||
          !reg.test(goodsDetailVoList[i].normalPrice)
        ) {
          return false
        }
      }
      for (var z in goodsParameterList) {
        if (
          !goodsParameterList[z].goodsFormatName ||
          !goodsParameterList[z].goodsFormatValue
        ) {
          return false
        }
      }

      const obj = {
        id: this.id || '',
        sort: this.formData.sort,
        goodsName: this.formData.goodsName,
        goodsCover: this.formData.goodsCover,
        goodsTypeId: this.formData.goodsTypeId,
        addGoodsDetailDTOList: this.goodsDetailVoList,
        goodsPicList: this.formData.goodsPicList,
        addGoodsParameterDTOList: this.goodsParameterList,
        recommandStatus: this.formData.recommandStatus,
        goodsLongpic: this.formData.goodsLongpic
      }
      if (val) {
        obj.goodsStatus = 1
      } else {
        if (!this.id) {
          obj.goodsStatus = 2
        }
      }
      return obj
    },
    getDetail() {
      getGoodsDetail({ id: this.id }).then(res => {
        if (res.code === 0) {
          this.formData = res.data
          this.formData.goodsTypeId = res.data.goodsTypeId
          this.goodsDetailVoList = res.data.goodsDetailVoList
          this.goodsParameterList = res.data.goodsParameterList
          this.formData.goodsPicList = res.data.goodsPicList
          this.formData.goodsLongpic = res.data.goodsLongpic
          this.fileList = res.data.goodsPicList.map(item => {
            item.url = item.goodsPic
            return item
          })
        } else {
          this.$message.error(res.message)
        }
      })
    },
    skuAddLine() { // 添加行数
      const num = this.goodsDetailVoList.length
      var newValue = {
        goodsSkuName: '',
        goodsSkuDesc: '',
        purchasePrice: '',
        supplyPrice: '',
        normalPrice: '',
        detailsSort: ''
      }
      // 添加新的行数
      if (num < 10) {
        this.goodsDetailVoList.push(newValue)
      } else {
        this.$message.warning('最多添加10条')
      }
    },

    skuDelete(index) {
      const num = this.goodsDetailVoList.length
      if (num > 1) {
        this.goodsDetailVoList.splice(index, 1)
      } else {
        this.$message.warning('不能删除了')
      }
    },

    attrAddLine() { // 添加行数
      const num = this.goodsParameterList.length
      var newValue = {
        goodsFormatName: '',
        goodsFormatValue: '',
        sort: ''
      }
      // 添加新的行数
      if (num < 10) {
        this.goodsParameterList.push(newValue)
      } else {
        this.$message.warning('最多添加10条')
      }
    },
    deleteAttr(index) {
      const num = this.goodsParameterList.length
      if (num > 1) {
        this.goodsParameterList.splice(index, 1)
      } else {
        this.$message.warning('不能删除了')
      }
    },
    upImageKey() {
      const date = new Date().toLocaleDateString()
      const time = new Date().getTime()
      const math = Math.ceil(Math.random() * 100000)
      return `img/${date}/${time}${math}.jpg`
    },
    beforeAction(file) {
      const isImg = file.type === 'image/jpeg' || file.type === 'image/png'
      const isLt1M = file.size / 1024 / 1024 < 1
      if (!isImg) {
        this.$message.error('上传图片只能是JPG或PNG 格式!')
        return isImg
      }
      if (!isLt1M) {
        this.$message.error('上传图片大小不能超过 1MB!')
        return isLt1M
      }
      const key = this.upImageKey()
      this.uploadExtendData.key = key
    },
    handleUploadSuccess(res, file, fileList) {
      file.ifDefult = '0'
      const imgUrl = this.$config.imgServerDomain + res.key
      // this.formData.goodsCover = imgUrl
      this.fileList = fileList
      this.formData.goodsPicList.push({ goodsPic: imgUrl, ifDefult: '0' })
    },
    handleRemove(file, fileList) {
      if (file.response) {
        this.formData.goodsPicList.forEach((item, index) => {
          if (item.goodsPic.indexOf(file.response.key) !== -1) {
            this.fileList.splice(index, 1)
            this.formData.goodsPicList.splice(index, 1)
          }
        })
      } else {
        this.formData.goodsPicList.forEach((item, index) => {
          if (item.url.indexOf(file.url) !== -1) {
            this.fileList.splice(index, 1)
            this.formData.goodsPicList.splice(index, 1)
          }
        })
      }
    },
    handlePreview(file) {
      this.dialogImageUrl = file.url
      this.dialogVisible = true
    },
    handleExceed(files, fileList) {
      console.log(files)
      console.log(fileList)
      this.$message.warning('图片上传超出个数限制')
    },
    // 设置封面图
    setPicCover(file) {
      console.log(this.fileList)
      if (file.response) {
        this.formData.goodsPicList.forEach((item, index) => {
          console.log(item)
          if (item.goodsPic.indexOf(file.response.key) !== -1) {
            item.ifDefult = '1'
            const change = this.fileList[index]
            change.ifDefult = '1'
            this.$set(this.fileList, index, change)
            this.formData.goodsCover = item.goodsPic
          } else {
            this.fileList[index].ifDefult = '0'
            item.ifDefult = '0'
          }
        })
      } else {
        this.formData.goodsPicList.forEach((item, index) => {
          if (item.url.indexOf(file.url) !== -1) {
            item.ifDefult = '1'
            this.formData.goodsCover = item.url
          } else {
            item.ifDefult = '0'
          }
        })
      }
    },
    getSevenCattleToken() {
      const params = {}
      this.$apis.COMMON.getSevenCattleToken(params).then(res => {
        if (res.code === 0) {
          this.uploadExtendData.token = res.data
        } else {
          this.$message.error(res.message)
        }
      }).catch(err => {
        console.log(err)
      })
    },
    close() {
      this.$router.push({
        path: '/wares/goods-manage'
      })
    },
    async getGoodsTypes() {
      const res = await this.$apis.COMMODITY.getGoodsTypes()
      if (res.code === 0) {
        this.goodsTypeList = res.data
      } else {
        this.$message.error(res.message)
      }
    }
  }
}
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
.goods-addedit {
  padding: 20px;
  .page-title {
    font-size: 20px;
    padding-bottom: 20px;
    border-bottom: 3px solid #ccc;
  }
  .sub-title {
    margin-top: 20px;
    font-size: 14px;
    line-height: 26px;
    margin-bottom: 20px;
    h3 {
      // font-weight: normal;
      color: #333;
      display: inline-block;
    }
    span {
      font-size: 12px;
      color: #666;
    }
  }
  .pic-cover{
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    font-size: 12px;
    margin-left: 0;
  }
  .btn-tips{
    position: absolute;
    bottom: 0;
    left: 0;
    text-align: center;
    width: 100%;
    font-size: 12px;
    margin-left: 0;
    color: #3a8ee6;
    background: rgba(0,0,0,.7);
  }
  .from {
    .el-input {
      width: 250px;
    }
    .attribute{
      .el-input {
        width: 100px;
        &.w200{
          width: 200px;
        }
      }
    }
  }
}
</style>
