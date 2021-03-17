<template>
    <div class="city-offer-record-list">
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
                        label="轮单费"
                >
                    <template slot-scope="scope">
                        {{ parseInt(scope.row.content.round_order_money)  ? scope.row.content.round_order_money : '-----' }}
                    </template>
                </el-table-column>
                <el-table-column
                        align="center"
                        prop="quarter_quote"
                        label="季度报价"
                >
                    <template slot-scope="scope">
                        {{ scope.row.content.quarter_quote }}
                    </template>
                </el-table-column>
                <el-table-column
                        align="center"
                        label="半年报价"
                >
                    <template slot-scope="scope">
                        {{ scope.row.content.half_year_quote }}
                    </template>
                </el-table-column>
                <el-table-column
                        align="center"
                        label="年度报价"
                >
                    <template slot-scope="scope">
                        {{ scope.row.content.year_quote }}
                    </template>
                </el-table-column>
                <el-table-column
                        align="center"
                        label="月承诺订单"
                >
                    <template slot-scope="scope">
                        {{ scope.row.content.month_promise_order }}
                    </template>
                </el-table-column>
                <el-table-column
                  align="center"
                  prop="user_order_limit"
                  label="1.0订单上限"
                 />
                <el-table-column
                        align="center"
                        label="年度会员时间"
                >
                    <template slot-scope="scope">
                        {{ scope.row.content.year_member_time }}
                    </template>
                </el-table-column>
                <el-table-column
                    align="center"
                    label="不同档报价及承诺单量"
                    width="120"
                >
                    <template slot-scope="scope">
                        <div>A:{{ scope.row.content.grade_a_price ? scope.row.content.grade_a_price : '---' }}, 单量：{{ scope.row.content.grade_a_order ? scope.row.content.grade_a_order : '---' }}</div>
                        <div>B:{{ scope.row.content.grade_b_price ? scope.row.content.grade_b_price : '---' }}, 单量：{{ scope.row.content.grade_b_order ? scope.row.content.grade_b_order : '---' }}</div>
                        <div>C:{{ scope.row.content.grade_c_price ? scope.row.content.grade_c_price : '---' }}, 单量：{{ scope.row.content.grade_c_order ? scope.row.content.grade_c_order : '---' }}</div>
                        <div>D:{{ scope.row.content.grade_d_price ? scope.row.content.grade_d_price : '---' }}, 单量：{{ scope.row.content.grade_d_order ? scope.row.content.grade_d_order : '---' }}</div>
                    </template>
                </el-table-column>
                <el-table-column
                        align="center"
                        prop="consist_price"
                        label="包干报价"
                >
                    <template slot-scope="scope">
                        {{ scope.row.content.consist_price ? scope.row.content.consist_price : '不包干' }}
                    </template>
                </el-table-column>
                <el-table-column
                        align="center"
                        label="包干最低承诺分单数"
                >
                    <template slot-scope="scope">
                        {{ scope.row.content.consist_fen ? scope.row.content.consist_fen : '----' }}
                    </template>
                </el-table-column>
                <el-table-column
                        align="center"
                        label="包干增单承诺"
                >
                    <template slot-scope="scope">
                        {{ scope.row.content.consist_zeng ? scope.row.content.consist_zeng : '----' }}
                    </template>
                </el-table-column>
                <el-table-column
                        align="center"
                        prop="exclusive_price"
                        label="独家报价"
                >
                    <template slot-scope="scope">
                        {{ scope.row.content.exclusive_price ? scope.row.content.exclusive_price : '不独家' }}
                    </template>
                </el-table-column>
                <el-table-column
                        align="center"
                        label="独家最低分单数"
                >
                    <template slot-scope="scope">
                        {{ scope.row.content.exclusive_fen_min ? scope.row.content.exclusive_fen_min : '----' }}
                    </template>
                </el-table-column>
                <el-table-column
                        align="center"
                        label="独家最高分单数"
                >
                    <template slot-scope="scope">
                        {{ scope.row.content.exclusive_fen_max ? scope.row.content.exclusive_fen_max : '----' }}
                    </template>
                </el-table-column>
                <el-table-column
                        align="center"
                        prop="exclusive_zeng"
                        label="独家增单承诺"
                >
                    <template slot-scope="scope">
                        {{ scope.row.content.exclusive_zeng ? scope.row.content.exclusive_zeng : '----' }}
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
                id: '8',
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
                    quote_type: 1,
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
                        this.$message.error(res.data.error_msg)
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
    .city-offer-record-list {
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
