<template>
    <div class="logs">
        <el-table
                :data="tableData"
                border
                style="width: 100%">
            <el-table-column
                    align="center"
                    prop="created_at"
                    label="时间"
            >
            </el-table-column>
            <el-table-column
                    align="center"
                    label="状态"
            >
                <template slot-scope="scope">
                    {{ scope.row.status | statusFilter }}
                </template>
            </el-table-column>
            <el-table-column
                    align="center"
                    label="备注">
                <template slot-scope="scope">
                    {{ scope.row.remark }}
                </template>
            </el-table-column>
            <el-table-column
                    align="center"
                    prop="op_name"
                    label="操作人">
            </el-table-column>
        </el-table>
    </div>
</template>

<script>
    import memberReportDetail from '@/mixins/memberReport'
    export default {
        name: "logs",
        mixins: [memberReportDetail],
        data() {
            return {
                tableData: []
            }
        },
        filters: {
            statusFilter(val) {
                switch (val) {
                    case 1:
                        return '待提交'
                        break
                    case 2:
                        return '待审核'
                        break
                    case 3:
                        return '审核通过/待客服上传'
                        break
                    case 4:
                        return '未通过'
                        break
                    case 5:
                        return '客服审核通过'
                        break
                    case 6:
                        return '客服未通过，普通信息更改'
                        break
                    case 7:
                        return '客服未通过，需要重新审核'
                        break
                    case 8:
                        return '客服完成上传'
                        break
                    case 9:
                        return '客服暂停'
                        break
                    case 10:
                        return '待客服补充'
                        break
                    case 11:
                        return '客服补充完成'
                        break
                    default:
                        return ''
                }
            }
        },
        created() {
            this.getMemberReportLogs()
        },
        methods: {
            getMemberReportLogs() {
                const queryObj = {
                    cooperation_type: this.$route.query.type,
                    report_id: this.$route.query.id
                }
                this.memberReportLogs(queryObj, this.setLogData)
            },
            setLogData(data) {
                const ret = data.list
                this.tableData = ret
            }
        }
    }
</script>

<style scoped>

</style>
