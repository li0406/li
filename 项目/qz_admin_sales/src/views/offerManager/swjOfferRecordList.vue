<template>
    <div class="sanweijia-offer-record-list">
        <div class="search">
            <div class="clearfix">
                <div class="fl mr20">
                    修改时间: <br>
                    <div class="block">
                        <el-date-picker
                                v-model="recordTime"
                                type="daterange"
                                range-separator="至"
                                start-placeholder="开始日期"
                                end-placeholder="结束日期">
                        </el-date-picker>
                    </div>
                </div>
                <div class="fl">
                    <br>
                    <el-button type="primary" icon="el-icon-search" @click="handleSearch">查询</el-button>
                </div>
            </div>
        </div>
        <div class="qian-main">
            <el-table
                    v-loading="loading"
                    :data="tableData"
                    border
            >
                <el-table-column
                        align="center"
                        prop="create_time"
                        label="修改时间"
                />
                <el-table-column
                        align="center"
                        label="合作报价"
                >
                    <template slot-scope="scope">
                        {{ scope.row.content.cooperation_price }}
                    </template>
                </el-table-column>
            </el-table>
            <el-pagination
                    :current-page="currentPage"
                    :page-size="20"
                    layout="total, prev, pager, next, jumper"
                    :total="totals"
                    @size-change="handleSizeChange"
                    @current-change="handleCurrentChange"
            />
        </div>
    </div>
</template>

<script>
    import { fetchOfferRecordList } from '@/api/memberReport'
    import { fortmatDate } from '@/utils/index'
    export default {
        name: "cityOfferRecordList",
        data() {
            return {
                id: '',
                loading: false,
                tableData: [],
                currentPage: 1,
                totals: 0,
                recordTime: null
            }
        },
        created() {
            if(this.$route.query && this.$route.query.id) {
                this.id = this.$route.query.id
            }
            this.getOfferRecordList()
        },
        methods: {
            getOfferRecordList() {
                let start = ''
                let end = ''
                if(this.recordTime) {
                    start = fortmatDate(this.recordTime[0])
                    end = fortmatDate(this.recordTime[1])
                }
                const queryObj = {
                    quote_id: this.id,
                    quote_type: 2,
                    start: start,
                    end: end,
                    p: this.currentPage
                }
                this.loading = true
                fetchOfferRecordList(queryObj).then(res => {
                    if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                        this.tableData = res.data.data.list
                        this.totals = res.data.data.page.total_number
                    }else{
                        this.$message.error(res.data.error_code)
                    }
                    this.loading = false
                }).catch(res => {
                    this.$message.error('网络异常，请稍后再试')
                })
            },
            handleSearch() {
                this.getOfferRecordList()
            },
            handleSizeChange() {
                this.currentPage = 1
                this.getOfferRecordList()
            },
            handleCurrentChange(val) {
                this.currentPage = val
                this.getOfferRecordList()
            }
        }
    }
</script>

<style rel="stylesheet/scss" lang="scss">
    .sanweijia-offer-record-list {
        width: 100%;
        padding: 20px;
        box-sizing: border-box;
        .search {
            background: #fff;
            padding: 20px;
            border-top: 3px solid #999;
            box-sizing: border-box;
            line-height: 36px;
        }
        .qian-main {
            margin-top: 20px;
            padding: 20px;
            border-top: 3px solid #999;
            box-sizing: border-box;
            background-color: #fff;
        }
        .el-pagination{
            text-align: center;
            margin-top: 20px;
        }
        .fl {
            float: left;
        }
        .mr20 {
            margin-right: 20px;
        }
        .el-date-editor{
            .el-range-separator{
                padding: 0;
            }
        }
    }
</style>
