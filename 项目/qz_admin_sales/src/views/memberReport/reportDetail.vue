<template>
    <div class="member-repoort-detail">
        <div class="qian-main">
            <hdDetail v-if="parseInt(type) === 1 || parseInt(type) === 2 || parseInt(type) === 3 || parseInt(type) === 7 || parseInt(type) === 14"></hdDetail>
            <sanWeiJiaDetail v-if="parseInt(type) === 4"></sanWeiJiaDetail>
            <erpDetail v-if="parseInt(type) === 5"></erpDetail>
            <commissionDetail v-if="parseInt(type) === 6 || parseInt(type) === 15"></commissionDetail>
            <delayDetail v-if="parseInt(type) === 8" />
        </div>
    </div>
</template>

<script>
    import hdDetail from './components/hdDetail'
    import sanWeiJiaDetail from './components/sanWeiJiaDetail'
    import erpDetail from './components/erpDetail'
    import delayDetail from './components/delayDetail'
    import commissionDetail from './components/commissionDetail'
    export default {
        name: "reportDetail",
        data() {
            return {
                id: '',
                type: ''
            }
        },
        components: {
            hdDetail,
            sanWeiJiaDetail,
            erpDetail,
            delayDetail,
            commissionDetail
        },
        created() {
            this.id = this.$route.query.id
            this.type = this.$route.query.type
        }
    }
</script>

<style rel="stylesheet/scss" lang="scss">
    .member-repoort-detail {
        width: 100%;
        padding: 20px;
        box-sizing: border-box;
        .qian-main{
            margin-top: 20px;
            padding: 20px;
            border-top: 3px solid #999;
            box-sizing: border-box;
            background-color: #fff;
        }
    }

</style>
