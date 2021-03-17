<template>
  <div class="content">
    <div class="content-box">
      <h2>Voip电话记录</h2>
      <div class="content-table">
        <el-table
          :data="tableData"
          border
          style="width: 100%;">
          <el-table-column
            prop="time_add"
            align="center"
            label="产生时间"
            width="180">
          </el-table-column>
          <el-table-column
            prop="action_name"
            label="呼叫动作"
            align="center"
            width="180">
          </el-table-column>
          <el-table-column
            prop="bye"
            align="center"
            width="240px"
            label="挂机类型(代码)">
          </el-table-column>
          <el-table-column
            align="center"
            label="通话录音">
            <template slot-scope="scope" >
              <div @click="recordDetail(scope.row,scope.row.id)" v-if="scope.row.action =='Hangup' && parseInt(scope.row.duration)>0">
                <span class="record-play"><i class="fa fa-play-circle-o"></i></span>
                <span>{{scope.row.duration}}秒</span>
              </div>
              <div v-else>
                <span>-</span>
              </div>
            </template>
          </el-table-column>
          <el-table-column
            prop="call_sid"
            width="300"
            align="center"
            label="callSid(callID)">
          </el-table-column>
          <el-table-column
            prop="caller"
            align="center"
            label="主叫号码">
          </el-table-column>
          <el-table-column
            prop="called"
            align="center"
            label="被叫号码">
          </el-table-column>
          <el-table-column
            prop="admin_user"
            align="center"
            label="拨打人">
          </el-table-column>
        </el-table>
        <!-- <el-row type="flex" justify="end" style="padding: 20px 0;">
           <el-pagination
             :current-page="currentPage"
             :page-sizes="[10, 20, 30, 40]"
             :page-size="pageSize"
             :total="pageTotal"
             layout="total, prev, pager, next, jumper"
             @size-change="handleSizeChange"
             @current-change="handleCurrentChange"/>
         </el-row>-->
      </div>
      <el-dialog
        title="录音播放"
        :visible="dialogVisible"
        width="30%"
        @close="closeDialogHandle"
      >
        <audio class="player" v-if="dialogVisible" :src="recordUrl"
               autoplay preload="auto" controls="" data-on="0"
               ref="record">
        </audio>
      </el-dialog>
    </div>
  </div>
</template>

<script>
  import {fetchVoipRecord, fetchVoipUrl} from '@/api/orderManage'

  export default {
    name: "voipRecord",
    data() {
      return {
        tableData: [],
        // 分页
        currentPage: 1,
        pageSize: 10,
        pageTotal: 20,
        dialogVisible: false,
        recordUrl: '',
        orderid: ''
      }
    },
    created() {
      if (this.$route.query.orderid) {
        this.orderid = this.$route.query.orderid
        this.getRecordList()
      }
    },
    methods: {
      getRecordList() {
        let query = {orderid: this.orderid}
        fetchVoipRecord(query).then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.tableData = res.data.data.list
            this.tableData.forEach((item, index) => {
            if(item.byetype_name === '-') {
              item.bye = '-'
            }else{
              item.bye = item.byetype_name + "(" + item.byetype + ")"
            }
          })
          }
        })
      },
      // 录音
      recordDetail(obj, id) {
        if (obj.TelCenter_Channel == 'cuct' || obj.TelCenter_Channel == 'holly') {
          let query = {
            call_sid: obj.call_sid,
            channel: obj.channel
          }
          fetchVoipUrl(query).then(res => {
            if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
              this.dialogVisible = true
              this.recordUrl = this.$qzconfig.oss_audio_host + res.data.data.url
            } else {
              this.$message.error(res.data.error_msg)
            }
          })
        } else if (obj.TelCenter_Channel == 'ytx') {
          this.dialogVisible = true
          this.recordUrl = obj.recordurl
        }
      },
      closeDialogHandle() {
        this.dialogVisible = false
      }
    }
  }
</script>

<style scoped>
  .content-box {
    padding: 5px 10px;
  }

  .content-box h2 {
    font-size: 16px;
    font-weight: normal;
    color: #666;
  }

  .content-table {
    border-top: 3px solid #d2d6de;
    border-radius: 3px;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
    padding: 5px 15px;
    background: #fff;
  }

  .el-pagination {
    margin: 0 auto;
  }

  .record-play {
    font-size: 20px;
    cursor: pointer;
  }
</style>
