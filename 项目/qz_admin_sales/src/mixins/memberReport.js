/**
 * +----------------------------------------------------------------------
 * | 获取会员报备详情，多个页面用到，抽出来
 * +----------------------------------------------------------------------
 * | Author: zwl
 * +----------------------------------------------------------------------
 */
import { fetchMemberReportDetail, fetchMemberReportLogList } from '@/api/memberReport'
import { fetchSmallReportDetail, fetchSmallReportEdit } from '@/api/smallReport'
export default {
    methods: {
        memberReportDetail(queryObj, cb) {
            let that = this
            fetchMemberReportDetail(queryObj).then(res => {
                if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                    cb && cb.call(that, res.data.data)
                }else{
                    that.$message({
                        showClose: true,
                        message: res.data.error_msg,
                        type: 'error',
                        duration: '2000',
                        onClose() {
                          that.$router.go(-1)
                        }
                    })
                }
            }).catch(res => {
                that.$message.error('网络异常，请稍后再试')
            })
        },
        memberReportLogs(queryObj, cb) {
            fetchMemberReportLogList(queryObj).then(res => {
                if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                    cb && cb.call(this, res.data.data)
                }else{
                    this.$message.error(res.data.error_msg)
                }
            }).catch(res => {
                this.$message.error('网络异常，请稍后再试')
            })
        },
        smallReportDetail(queryObj, cb) {
            let that = this
            fetchSmallReportDetail(queryObj).then(res => {
                if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                    cb && cb.call(that, res.data.data)
                }else{
                  that.$message({
                    showClose: true,
                    message: res.data.error_msg,
                    type: 'error',
                    duration: '2000',
                    onClose() {
                      console.log('123')
                      that.$router.go(-1)
                    }
                  })
                }
            }).catch(res => {
              that.$message.error('网络异常，请稍后再试')
            })
        },
        smallReportEdit(queryObj, cb) {
            fetchSmallReportEdit(queryObj).then(res => {
                if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                    cb && cb.call(this, res.data.data)
                }else{
                    this.$message.error(res.data.error_msg)
                }
            }).catch(res => {
                this.$message.error('网络异常，请稍后再试')
            })
        }
    }
}
