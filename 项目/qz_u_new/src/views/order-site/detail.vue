<template>
  <div class="site-detail">
    <el-card class="box-card">
      <div slot="header" class="clearfix">
        <span>订单信息</span>
        <el-button style="float: right; padding: 3px 0;color: rgb(204, 204, 204)" type="text" @click="$router.go(-1)">
          返回
        </el-button>
      </div>
      <!--订单信息-->
      <div class="order-detail">
        <el-row>
          <el-col :span="18" class="sg-info">
            <el-col :span="8">
              <p>订单类型：{{info.type || '---'}}</p>
              <p>所在区域：{{info.area || '---'}}</p>
              <p>小区名称：{{info.xiaoqu || '---'}}</p>
              <p>地址：{{info.address || '---'}}</p>
            </el-col>
            <el-col :span="8">
              <p>签约日期：{{info.date || '---'}}</p>
              <p>联系方式：{{info.tel || '---'}}</p>
              <p>面积：{{info.mianji || '---'}}</p>
              <p>业主：{{info.yz || '---'}}</p>
            </el-col>
            <el-col :span="8">
              <p>户型：{{info.huxing || '---'}}</p>
              <p>
                施工编号：{{info.bianhao || '---'}}
                <el-button @click="copyClick(info.bianhao)" icon="el-icon-document-copy"
                           size="mini" class="copyBtn" type="text" style="margin-left: 5px"></el-button>

                <el-popover placement="bottom" width="250" trigger="hover">
              <p style="margin: 0">施工人员可使用微信<span style="color: rgb(255, 106, 25)">“齐装网施工跟进”</span>小程序，使用施工编号绑定施工订单，绑定后可发布装修现场信息
              </p>
              <el-button slot="reference" type="text" class="header-icon el-icon-question"
                         style="margin-left: 5px"></el-button>
              </el-popover>
              </p>
            </el-col>
          </el-col>

          <el-col :span="6">
            <p @click="toorderdetail()" class="goOder">订单详情>></p>
            <el-row class="sgewm">
              <el-col :span="8" class="sgewm-img">
                <el-image :src="'data:image/png;base64,'+codeIMG" style="width: 100%"
                          v-loading="qeCodeLoading"></el-image>
              </el-col>
              <el-col :span="16" class="sgewm-txt">
                <h3>施工二维码</h3>
                <p>扫描施工二维码绑定施工订单，绑定后可查看已签约业主订单信息，发布装修现场信息。</p>
                <el-button size="small" @click="downloadCodeImg(codeIMG)">保存图片</el-button>
              </el-col>
            </el-row>
          </el-col>

        </el-row>
      </div>
    </el-card>

    <!--无数据的施工详情-->
    <div class="sg-detail-null" v-if="live.length === 0">
      <el-card class="box-card" style="margin-top: 10px;">
        <img src="@/assets/u398.png">
        <h3>暂无施工信息</h3>
        <p>提示：已签约并且已审核通过的订单，将会展示给授权的施工人员， 施工人员上传的施工信息将在此处展示</p>
      </el-card>
    </div>

    <!--有数据的施工详情-->
    <div class="sg-detail clearfix" v-else>
      <el-col :span="20" style="height: 100%;padding-right: 10px">
        <el-card class="box-card" style="height: 100%">
          <div slot="header" class="clearfix">
            <span>装修现场详情</span>
          </div>

          <div class="sg-detail-list" v-for="item in live" :key="item.id">

            <h2><img :src='require(`@/assets/step-ico/step${item.step}.png`)' alt="">{{item.step_name}}</h2>

            <div class="sg-detail-box">
              <div class="feedback">
                <div class="feedback-head">
                  <div class="feedback-avatar">
                    <img :src="item.wx_headimg" alt="">
                    <span>{{item.wx_nickname}}</span>
                  </div>
                  <div class="cz">
                    <el-button type="text" class="el-icon-edit-outline" @click="editSgInfo(item.id)"></el-button>
                    <el-button type="text" class="el-icon-delete" @click="removeSgInfo(item.id)"></el-button>
                    <span>{{item.created_date}}</span>
                  </div>
                </div>
                <div class="feedback-con">
                  <p>{{item.content}}</p>
                  <div class="feedback-media">
                    <div class="clearfix">
                      <div v-for="item2 in item.media_list" :key="item2.id" style="float:left;list-style: none;">
                        <div v-if="item2.type === 1">
                          <el-image
                            style="width: 180px; height: 180px;"
                            :src="item2.url_full"
                            :preview-src-list="item.imgList"
                            fit="cover">
                          </el-image>
                        </div>
                        <div v-else>
                          <video :src="item2.url_full" controls="controls" width="440px" height="248px">
                            您的浏览器不支持 video 标签。
                          </video>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div v-if="item.check_state === 2">
                    <div class="sat">
                      <div v-if="item.check_ret === 1">
                        <img src="@/assets/step-ico/laugh.png" alt="">{{item.check_ret | sat}}
                      </div>
                      <div v-else-if="item.check_ret === 2">
                        <img src="@/assets/step-ico/smile.png" alt="">{{item.check_ret | sat}}
                      </div>
                      <div v-else-if="item.check_ret === 3">
                        <img src="@/assets/step-ico/sad.png">{{item.check_ret | sat}}
                      </div>
                    </div>
                  </div>

                  <div class="tag" v-if="item.check_effect_list && item.check_effect_list.length > 0">
                    <span>业主印象:</span>
                    <el-tag
                      v-for="(tag,index) in item.check_effect_list"
                      :key="index"
                      type="warning">
                      {{tag}}
                    </el-tag>
                  </div>

                  <div style="margin-top: 16px">
                    <div class="comment" v-for="comment in item.comment" :key="comment.id">
                      <div class="comment-head">
                        <div class="comment-avatar">

                          <img src="@/assets/step-ico/yzAvatar.png" alt="">
                          <span>业主评论</span>

                        </div>
                        <div class="time">
                          <span>{{comment.created_at_time}}</span>
                        </div>
                      </div>

                      <div class="comment-con">

                        <div class="reply">
                          <p>{{comment.content}}</p>
                          <el-button type="text" style="margin-top: 15px;padding: 0"
                                     @click="handleReply(comment.id,comment.info_id,comment.content)">回复
                          </el-button>
                        </div>
                        <div class="reply" v-for="reply in comment.company_comment" :key="reply.id">
                          <div class="reply-title"><h3>回复业主</h3>
                            <span>{{ reply.updated_at | formatDate }}</span></div>
                          <p>{{reply.content}}</p>
                        </div>

                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </el-card>
      </el-col>

      <el-col :span="4" class="sg-jieduan">
        <el-card>
          <div slot="header" class="clearfix">
            <span>订单信息</span>
          </div>
          <p @click="getSgInfo($route.params.id)">全部施工阶段({{ stepList.length }})</p>
          <div>
            <el-timeline style="padding-left: 0">
              <el-timeline-item v-for="(item,index) in stepList" :class="[{'active':active == index}] "
                                :key="item.id"><span
                @click="getSgInfo($route.params.id,item.id,index)">{{ item.name }}</span></el-timeline-item>
            </el-timeline>
          </div>
        </el-card>
      </el-col>
    </div>

    <!--修改施工信息弹窗-->
    <el-dialog title="修改施工信息" :visible.sync="editSgInfoVisible" width="710px" class="editSgInfoDialog">
      <el-form :model="editSgInfoForm">
        <el-form-item label="发布人：" label-width="85px">
          <div style="display: flex;align-items: center">
            <el-avatar :src="editSgInfoForm.wx_headimg" style="margin-right: 15px"></el-avatar>
            <span>{{editSgInfoForm.wx_nickname}}</span>
          </div>
        </el-form-item>

        <el-form-item label="施工阶段：" label-width="85px">
          <el-select v-model="editSgInfoForm.step">
            <el-option v-for="item in editSgInfoForm.stepList" :key="item.id" :label="item.name"
                       :value="item.id"></el-option>
          </el-select>
        </el-form-item>


        <el-form-item label="施工详情：" label-width="85px">
          <el-input v-model="editSgInfoForm.content" type="textarea" :rows="5" style="width: 584px"></el-input>
        </el-form-item>

        <!--施工现场图片编辑-->
        <el-form-item label="施工现场：" label-width="85px" v-if="editSgInfoForm.mediaType">
          <div class="clearfix xc-img-wrap" v-if="editSgInfoForm.mediaType === 1">
            <div v-for="(item, index) in editSgInfoForm.mediaList" :key="item.id" class="xc-img-box">
              <el-image :src="item.url_full" fit="cover" style="width: 100px;height: 100px"></el-image>
              <div class="xc-img-layer">
                <i class="el-icon-delete" @click="removeMedia(index)"></i>
              </div>
            </div>
          </div>


          <div v-else v-for="(item,index) in editSgInfoForm.mediaList" :key="item.id" class="xc-viedo">
            <video :src="item.url_full" controls="controls" width="240px" height="135px">
              您的浏览器不支持 video 标签。
            </video>
            <span @click="removeVideo(index)">刪除</span>
          </div>

        </el-form-item>

      </el-form>

      <div slot="footer" class="dialog-footer">
        <el-button @click="editSgInfoVisible = false">取 消</el-button>
        <el-button type="primary" @click="saveSgInfo">确 定</el-button>
      </div>

    </el-dialog>

    <!--回复业主弹窗-->
    <el-dialog title="回复业主" :visible.sync="replyVisible" width="710px" class="editSgInfoDialog">
      <el-form :model="replyForm">

        <el-form-item label="业主评语：" label-width="85px">
          <span style="color: #303133">{{replyForm.reviews}}</span>
        </el-form-item>

        <el-form-item label="施工详情：" label-width="85px">
          <el-input v-model="replyForm.content" placeholder="请填写回复语" type="textarea" :rows="6"></el-input>
        </el-form-item>


        <el-form-item label="提示：" label-width="55px">
          <span style="color: #909399"> *评价内容中，请勿包含联系方式\网址\其他网站LOGO\微博、微信账号\二维码等
            “相关联系方式”，否则将会删除。</span>
        </el-form-item>

      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="replyVisible = false">取 消</el-button>
        <el-button type="primary" @click="sumbitReply">确 定</el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
  import apiSite from '@/api/site';

  export default {
    name: 'detail',
    data() {
      return {
        active: false,
        // 施工二维码
        codeIMG: '',
        qeCodeLoading: false,
        // 编辑施工信息弹窗变量
        editSgInfoVisible: false,
        // 回复业主弹窗变量
        replyVisible: false,
        // 装修现场列表
        live: [],
        // 装修阶段
        stepList: [],
        // 订单信息
        info: {
          order_id: '',
          type: '',
          area: '',
          xiaoqu: '',
          address: '',
          date: '',
          tel: '',
          mianji: '',
          yz: '',
          huxing: '',
          bianhao: '',
        },

        // 编辑弹窗字段
        editSgInfoForm: {
          id: '',
          wx_nickname: '',
          wx_headimg: '',
          step: '',
          stepList: [],
          content: '',
          mediaList: [],
          mediaType: '',
        },
        // 回复业主字段
        replyForm: {
          id: '',
          infoId: '',
          reviews: '',
          content: '',
        },
      };
    },

    created() {
      // 装修现场订单信息
      this.getOrderDetail(this.$route.params.id);
      // 装修现场施工信息
      this.getSgInfo(this.$route.params.id);

    },
    mounted() {
    },
    methods: {
      toorderdetail(){
        this.$router.push({
          path:'/order-detail',
          query:{
            id:this.info.order_id,
            tabname:'basics'
          }
        })
      },
      // 获取装修现场订单信息
      async getOrderDetail(id) {
        const res = await apiSite.getOrderDetail(id);
        this.info.order_id = res.data.data.info.order_id;
        this.info.type = res.data.data.info.type_name;
        this.info.area = res.data.data.info.area_name;
        this.info.xiaoqu = res.data.data.info.xiaoqu;
        this.info.date = res.data.data.info.qiandan_date;
        this.info.tel = res.data.data.info.tel;
        this.info.mianji = res.data.data.info.mianji;
        this.info.yz = res.data.data.info.yz_name;
        this.info.bianhao = res.data.data.info.code;
        this.stepList = res.data.data.step_list;
        this.getQrCode(this.info.bianhao);
      },

      // 施工二维码
      async getQrCode(code) {
        this.qeCodeLoading = true;
        const res = await apiSite.getQrCode(code);
        this.codeIMG = res.data.data;
        this.qeCodeLoading = false;
      },

      // 装修现场施工信息
      async getSgInfo(id, step = '', index) {
        this.active = index;
        const res = await apiSite.getSgInfo(id, step);
        this.live = res.data.data.list;

        // 使用ele ui 的图片 放大，需要绑定图片路径数组 抽取图片信息为一个数组
        this.live.forEach((item) => {
          const Obj = item;
          Obj.imgList = [];
          item.media_list.forEach((item2) => {
            if (item.id === item2.info_id) {
              Obj.imgList.push(item2.url_full);
            }
          });
        });
      },

      // 编辑施工信息
      async editSgInfo(id) {
        const res = await apiSite.getSgDetail(id);
        this.editSgInfoForm.id = id;
        this.editSgInfoForm.wx_nickname = res.data.data.info.wx_nickname;
        this.editSgInfoForm.wx_headimg = res.data.data.info.wx_headimg;
        this.editSgInfoForm.step = res.data.data.info.step;
        this.editSgInfoForm.stepList = res.data.data.step_list;
        this.editSgInfoForm.content = res.data.data.info.content;
        this.editSgInfoForm.mediaList = res.data.data.info.media_list;
        this.editSgInfoForm.mediaType = res.data.data.info.media_type;
        this.editSgInfoVisible = true;
      },

      removeVideo(index) {
        this.$confirm('您确定要删除该记录吗?', '删除提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning',
        }).then(() => {
          this.removeMedia(index);
        });
      },

      // 删除施工现场媒体信息
      removeMedia(index) {
        this.editSgInfoForm.mediaList.splice(index, 1);
      },

      // 保存编辑的施工信息
      async saveSgInfo() {
        let mediaUrls = '';
        this.editSgInfoForm.mediaList.forEach((item) => {
          mediaUrls += `${item.url},`;
        });
        const res = await apiSite.saveSgInfo(this.editSgInfoForm, mediaUrls);
        if (res.data.error_code !== 0) {
          this.$message({
            message: res.data.error_msg,
            type: 'error',
          });
        } else {
          this.$message({
            message: '修改成功',
            type: 'success',
          });
          this.getSgInfo(this.$route.params.id);
          this.editSgInfoVisible = false;
        }
      },

      // 删除某一条施工信息
      removeSgInfo(id) {
        this.$confirm('您确定要删除该施工记录吗?', '删除提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning',
        }).then(() => {
          apiSite.removeSgInfo(id).then((res) => {
            if (res.data.error_code === 0) {
              this.getSgInfo(this.$route.params.id);
              this.$message({
                type: 'success',
                message: '删除成功!',
              });
            } else {
              this.$message({
                type: 'error',
                message: res.data.error_msg,
              });
            }
          });
        });
      },

      // 回复业主
      handleReply(id, infoId, reviews) {
        this.replyVisible = true;
        this.replyForm.content = '';
        this.replyForm.id = id;
        this.replyForm.infoId = infoId;
        this.replyForm.reviews = reviews;
      },
      // 提交回复
      async sumbitReply() {
        const res = await apiSite.reply(this.replyForm);
        if (res.data.error_code === 0) {
          this.replyVisible = false;
          this.getSgInfo(this.$route.params.id);
          this.$message({
            type: 'success',
            message: '回复成功',
          });
        } else {
          this.$message({
            type: 'error',
            message: res.data.error_code,
          });
        }
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

      // 点击保存施工二维码
      downloadCodeImg() {
        window.open(`http://api.qizuang.com/business/worksite/pc/live_qrcode?live_code=${this.info.bianhao}&download=1`, '_blank');
      },

    },

    filters: {
      sat(el) {
        switch (el) {
          case 1:
            return '业主非常满意';
          case 2:
            return '业主还算满意';
          case 3:
            return '业主很不满意';
          default:
            return '';
        }
      },

      formatDate(time) {
        const date = new Date(time * 1000);
        const Y = date.getFullYear();
        let m = date.getMonth() + 1;
        let d = date.getDate();
        let H = date.getHours();
        let i = date.getMinutes();
        let s = date.getSeconds();
        if (m < 10) {
          m = `0${m}`;
        }
        if (d < 10) {
          d = `0${d}`;
        }
        if (H < 10) {
          H = `0${H}`;
        }
        if (i < 10) {
          i = `0${i}`;
        }
        if (s < 10) {
          s = `0${s}`;
        }

        const t = `${Y}-${m}-${d} ${H}:${i}:${s}`;
        return t;

      },
    },
  };
</script>

<style lang="scss">

  .el-image-viewer__canvas {
    img {
      max-width: 60% !important;
      max-height: 60% !important;
    }
  }

  .el-message-box {
    .el-button--default {
      background-color: rgba(255, 239, 239, 1);
      border: 1px solid rgba(233, 71, 71, 1);

      &:hover {
        color: #ff5353;
        background-color: rgba(255, 233, 233, 1);
        border: 1px solid rgba(233, 71, 71, 1);
      }
    }

    .el-button--primary {
      color: #fff;
      background-color: rgba(233, 71, 71, 1);

      &:hover {
        color: #fff;
        background-color: rgba(240, 87, 87, 1);
        border-color: #ff5353;;
      }
    }
  }

  .site-detail {
    .el-icon-circle-close::before {
      color: #fff;
    }

    .el-dialog {
      border-radius: 5px;

      .el-form-item__label {
        color: #303133;
      }
    }

    .el-dialog__header {
      padding: 20px;
      border-bottom: 1px solid #F2F6FC;
    }

    .el-dialog__footer {
      padding: 20px;
      border-top: 1px solid #F2F6FC;
    }

    .el-dialog__body {
      padding: 10px 20px;
    }

    .el-timeline-item__wrapper {
      top: 0;
      height: 14px;
      line-height: 14px;
    }

    .el-timeline-item__node--normal {
      left: -1px;
      box-sizing: border-box;
      width: 14px;
      height: 14px;
      background-color: #fff;
      border: 4px solid #DCDFE6;
      border-radius: 50%;
    }

    .el-timeline-item:hover {
      cursor: pointer;

      .el-timeline-item__node--normal {
        border: 4px solid #e94747;
      }

      &.active {
        cursor: pointer;
      }
    }

    .el-timeline-item.active {
      .el-timeline-item__node--normal {
        border: 4px solid #e94747;
      }
    }

    .editSgInfoDialog {
      .el-button--default {
        background-color: rgba(255, 239, 239, 1);
        border: 1px solid rgba(233, 71, 71, 1);

        &:hover {
          color: #ff5353;
          background-color: rgba(255, 233, 233, 1);
          border: 1px solid rgba(233, 71, 71, 1);
        }
      }

      .el-button--primary {
        background-color: rgba(233, 71, 71, 1);

        &:hover {
          background-color: rgba(240, 87, 87, 1);
          border-color: #ff5353;;
        }
      }
    }

  }

</style>

<style scoped lang="scss">

  .clearfix {
    zoom: 1;

    &::after {
      display: block;
      clear: both;
      height: 0;
      content: '';
    }

  }

  .sg-info {

    h3 {
      margin: 0;
      font-weight: 400;
      font-size: 16px;
    }

    p {
      margin: 30px 0;
      font-size: 14px;
    }
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

  .sgewm {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-right: 30px;

    .sgewm-txt {
      padding-left: 15px;

      h3 {
        font-weight: 400;
        font-size: 14px;
      }

      p {
        margin: 10px 0 0;
        color: rgb(153, 153, 153);
        font-size: 12px;
        line-height: 20px;
      }

      .el-button {
        margin-top: 10px;
        background-color: rgba(255, 239, 239, 1);

        &:hover {
          color: #ff5353;
          background-color: rgba(255, 233, 233, 1);
          border-color: rgba(233, 71, 71, 1);
        }
      }
    }
  }

  .el-button--text {
    color: #000;
  }

  .el-button--text:hover {
    color: rgb(132, 193, 255);
  }

  .sg-detail-null {
    margin: 0 auto;
    text-align: center;

    .el-card {
      padding: 100px 0 200px;
    }

    h3 {
      margin-top: 50px;
      font-weight: 400;
      font-size: 16px;
    }

    p {
      padding: 0 39%;
      color: #999;
      font-size: 12px;
      line-height: 20px;
    }
  }

  .sg-detail {
    display: table;
    width: 100%;
    height: 100%;
    margin-top: 15px;
  }


  .sg-detail-list {
    padding-top: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid #DCDFE6;

    h2 {
      margin: 0 auto 30px;
      font-size: 16px;

      img {
        margin-right: 15px;
        vertical-align: -2px;
      }
    }

    .sg-detail-box {
      padding-left: 10px;

      .feedback {
        .feedback-head {
          display: flex;
          align-items: center;
          justify-content: space-between;

          .feedback-avatar {
            display: flex;
            align-items: center;

            img {
              width: 40px;
              height: 40px;
              margin-right: 15px;
              border-radius: 50%;
            }

            span {
              color: #333;
              font-size: 16px;
            }
          }

          .cz {
            .el-button {
              margin-right: 10px;
              font-size: 16px;
            }

            span {
              margin-left: 10px;
              color: #999;
              font-size: 16px;
            }
          }
        }

        .feedback-con {
          margin-top: 15px;
          padding-left: 55px;

          p {
            margin: 0;
            color: #303133;
            font-size: 14px;
            line-height: 22px;
            letter-spacing: 1px;
          }

          .feedback-media {
            margin-top: 20px;

            .el-image {
              margin-right: 15px;
              margin-bottom: 11px;
            }

            video {
              outline: none;
            }
          }
        }
      }

      .tag {
        margin-top: 10px;

        span:first-child {
          margin-right: 15px;
          color: #303133;
          font-size: 14px;
        }

        .el-tag {
          margin-right: 10px;
          padding: 0 20px;
          color: #FFAB3F;
          background: #FFF8E2;
          border: none;
          border-radius: 20px;
        }
      }

      .sat {
        margin-top: 15px;
        color: #303133;
        font-size: 14px;

        img {
          width: 20px;
          height: 20px;
          margin-right: 15px;
          vertical-align: -5px;
        }
      }

      .comment {
        padding: 20px;
        background: #F5F7FA;
        border-bottom: 1px solid #DCDFE6;

        &:last-of-type {
          border-bottom: none;
        }

        .comment-head {
          display: flex;
          align-items: center;
          justify-content: space-between;

          .comment-avatar {
            display: flex;
            align-items: center;

            img {
              width: 40px;
              height: 40px;
              margin-right: 15px;
              border-radius: 50%;
            }

            span {
              color: #576B95;
              font-size: 16px;
            }
          }

          .time {
            span {
              color: #999;
              font-size: 14px;
            }
          }
        }

        .comment-con {
          padding-left: 55px;

          .reply {
            padding: 15px 0;
            border-bottom: 1px solid #DCDFE6;

            &:last-child {
              border-bottom: none;
            }

            p {
              color: #333;
              font-size: 14px;
            }

            .el-button {
              color: #6BA6FF;
              font-size: 14px;
            }

            .reply-title {
              display: flex;
              align-items: center;
              justify-content: space-between;

              span {
                color: #999;
                font-size: 14px;
              }
            }

            h3 {
              margin: 0 0 15px;
              color: #576B95;
              font-weight: 400;
              font-size: 16px;
            }
          }
        }
      }
    }
  }


  .sg-jieduan {
    height: 100%;

    .el-card {
      height: 100%;
    }

    p {
      margin-bottom: 32px;
      color: #303133;
      font-size: 14px;

      &:hover {
        cursor: pointer;
      }
    }


  }

  .editSgInfoDialog {
    .xc-img-wrap {
      .xc-img-box {
        position: relative;
        float: left;
        width: 100px;
        height: 100px;
        margin-right: 15px;
        margin-bottom: 15px;
        overflow: hidden;

        img {
          width: 100%;
        }

        .xc-img-layer {
          position: absolute;
          top: 0;
          right: 0;
          bottom: 0;
          left: 0;
          display: none;
          align-items: center;
          justify-content: center;
          background: rgba(0, 0, 0, .5)
        }

        &:hover {
          .xc-img-layer {
            display: flex;
            cursor: pointer;

            i {
              color: #fff;
              font-size: 18px
            }
          }
        }
      }
    }
  }


  .xc-viedo {
    span {
      margin-left: 15px;
      color: #E94747;
      font-size: 14px;
      vertical-align: 5px;

      &:hover {
        cursor: pointer;
      }
    }
  }

  .goOder {
    padding-right: 30px;
    color: red;
    text-align: right;

    &:hover {
      cursor: pointer;
    }
  }
</style>
