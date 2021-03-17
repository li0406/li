<template>
  <div>
    <el-card class="box-card">
      <div slot="header" class="clearfix">
        <span>装修现场</span>
      </div>
      <div class="search">
        <el-input v-model="search" placeholder="业主/小区/联系号码" class="search"></el-input>
        <el-button type="danger" @click="getLiveList">查询</el-button>
      </div>
    </el-card>

    <el-collapse>
      <el-collapse-item>
        <template slot="title">
          <div style="padding: 0 20px;">
            施工编号
            <i class="header-icon el-icon-info"></i>
          </div>
        </template>
        <div class="tip">
          <div class="tip-text">
            <div>施工编号是什么？</div>
            <br />
            <div>施工编号是业主与装修公司签约后生成的安全识别码，只有使用此安全码的施工人员才可查看对应订单信息以及上传该订单工地的施工进度，此安全码具有唯一性，提升服务同时，确保订单信息安全。</div>
            <br />
            <div>
              提示：施工人员可通过
              <span style="color: rgb(255, 106, 25)">“齐装网施工跟进”</span>小程序，使用施工编号绑定施工订单，或使用微信扫描施工二维码绑定施工订单，绑定后可查看已签约业主订单信息，发布装修现场信息。
            </div>
          </div>
          <div class="tip-ewm">
            <img src="@/assets/qrcode/u395.png" style="width: 150px" />
            <p>微信扫描二维码，打开齐装网施工跟进小程序。</p>
          </div>
        </div>
      </el-collapse-item>
    </el-collapse>

    <div class="tables">
      <el-table :data="siteList" border style="width: 100%;">
        <el-table-column prop="qiandan_date" label="签约日期" align="center"></el-table-column>
        <el-table-column prop="yz_name" label="业主" align="center"></el-table-column>
        <el-table-column prop="area_name" label="所在区域" align="center"></el-table-column>

        <el-table-column prop="xiaoqu" label="小区名称" align="center"></el-table-column>

        <el-table-column prop="mianji" label="建筑面积" align="center"></el-table-column>

        <el-table-column prop="huxing_name" label="户型" align="center"></el-table-column>

        <el-table-column prop="type_name" label="订单类型" align="center"></el-table-column>

        <el-table-column prop="code" label="施工编号" align="center">
          <template slot-scope="scope">
            <el-row>
              <el-col :span="16">
                <el-popover placement="bottom" trigger="click" @show="getQrCode(scope.row.code)">
                  <p style="margin-bottom: 10px;text-align: center">微信扫码绑定</p>
                  <el-image
                    :src="'data:image/png;base64,' + codeIMG"
                    style="width: 150px"
                    v-loading="qeCodeLoading"
                  ></el-image>
                  <span slot="reference">{{ scope.row.code }}</span>
                </el-popover>
              </el-col>

              <el-col :span="3">
                <el-button
                  @click="copyClick(scope.row.code)"
                  icon="el-icon-document-copy"
                  size="mini"
                  class="copyBtn"
                ></el-button>
              </el-col>
            </el-row>
          </template>
        </el-table-column>

        <el-table-column prop="address" label="装修现场" align="center">
          <template slot-scope="scope">
            <el-button
              type="danger"
              size="small"
              @click="$router.push(`/site-detail/${scope.row.id}`)"
            >装修现场</el-button>
          </template>
        </el-table-column>
      </el-table>

      <div class="pagination">
        <el-pagination
          style="text-align:right"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
          :current-page="pagination.page"
          :page-sizes="[10, 20, 50, 100]"
          :page-size="pagination.size"
          layout="total, sizes, prev, pager, next, jumper"
          :total="pagination.total"
        ></el-pagination>
      </div>
    </div>
  </div>
</template>

<script>
import apiSite from '@/api/site';

export default {
  name: 'order-site',
  data() {
    return {
      search: '',
      codeIMG: '',
      qeCodeLoading: false,
      siteList: [],
      pagination: {
        page: 1,
        size: 10,
        total: 0,
      },
    };
  },

  created() {
    this.getLiveList();
  },

  methods: {
    // 获取装修现场列表
    async getLiveList() {
      // 调用api
      const res = await apiSite.getLiveList(this.search, this.pagination.page, this.pagination.size);
      if (res.data.error_code === 0) {
        this.siteList = res.data.data.list;
        this.pagination.total = res.data.data.page.total_number;
      }
    },

    // 施工二维码
    async getQrCode(code) {
      this.qeCodeLoading = true;
      const res = await apiSite.getQrCode(code);
      this.codeIMG = res.data.data;
      this.qeCodeLoading = false;
    },

    // 更改每页显示个数
    handleSizeChange(val) {
      this.pagination.size = val;
      this.getLiveList();
    },
    // 更改页数
    handleCurrentChange(val) {
      this.pagination.page = val;
      this.getLiveList();
    },

    // 点击复制施工编号
    copyClick(data) {
      const input = document.createElement('input');
      document.body.appendChild(input);
      input.setAttribute('value', data);
      input.select();
      if (document.execCommand('copy')) {
        document.execCommand('copy');
        this.$message({
          message: '复制成功',
          type: 'success',
        });
      }
      document.body.removeChild(input);
    },
  },
};
</script>

<style scoped lang="scss">
p {
  margin: 0;
}

.pagination {
  margin-bottom: 110px;
  padding: 10px 0;
  background: #fff;
}

.search {
  .el-input {
    width: 200px;
  }

  button {
    margin-left: 30px;
  }
}

.tip {
  display: flex;
  justify-content: space-between;
  padding: 20px 24px;
  background: #fbfbfb;
}

.tip-con {
  display: flex;
  justify-content: space-between;
  height: 0;
  overflow: hidden;
  transition: all 0.3s;
}

.tip-text {
  width: 60%;
  color: rgb(102, 102, 102);
  font-size: 14px;
}

.tip-ewm {
  width: 30%;
  text-align: center;
}

.tipActive {
  height: 220px;
}

.copyBtn {
  padding: 0;
  color: #8c939d;
  background: transparent;
  border: none;
}

.copyBtn:hover {
  color: red !important;
}
.tables {
  padding: 30px;
  background: #fff;
}
</style>
