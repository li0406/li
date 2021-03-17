<template>
    <div class="p-container">
        <div class="top">
            <div class="top-item one active">
                1.选择户型
            </div>
            <div class="top-item">
                2.选择风格
            </div>
            <div class="top-item">
                3.免费领取方案
            </div>
        </div>
        <div class="select-container">
            <div class="select-title">
                装修房<span>户型</span>
            </div>
            <div class="select-table p-owf">
                <div class="select-table-td" v-for="item in hxData" :key="item.id">
                    <div class="select-box">
                        <div class="select-image-border">
                            <img :src="item.imageUrl" alt="">
                        </div>
                    </div>
                    <div @click='changeSelect(item.id)'>
                        <div v-if="!item.selected"  class="radius 1323">
                            <input type="radio" class="radio-select" size='16' name="radios" ></input>
                            <label>{{item.text}}</label>
                        </div>
                        <div v-else  class="radius">
                            <input type="radio" class="radio-select" size='16' name="radios"></input>
                            <label class="colorSelect">{{item.text}}</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div @click="toStyle" class="bottom-btn">
            <img src="../../../assets/img/qz/style/bottom_btn.png" class="center-center">
            <span>选好了</span>
        </div>
        <city-select ref='city'></city-select>
        <m-tips ref="tips"/>
    </div>
</template>

<script>
import mTips from '../../common/mTips.vue' // 引入tips 提示框
import citySelect from '../../common/citySelect.vue'
export default {
    name: "huxing",
    components: {
      mTips,
      citySelect
    },
    data() {
        return {
            hxData: [
                {
                    text:"一居室",
                    selected: false,
                    imageUrl:'http://staticqn.qizuang.com/meitu/20181225/1462720799166-717824-s5.jpg',
                    id: '38'
                },
                {
                    text:"二居室",
                    selected: false,
                    imageUrl:'http://staticqn.qizuang.com/meitu/20190626/QQjietu20190612172817-539615-s5.jpg',
                    id: '39'
                },
                {
                    text:"三居室",
                    selected: false,
                    imageUrl:'http://staticqn.qizuang.com/meitu/20190626/QQjietu20190612172833-539990-s5.jpg',
                    id: '42'
                },
                {
                    text:"四居室",
                    selected: false,
                    imageUrl:'http://staticqn.qizuang.com/meitu/20190524/4-688209-s5.jpg',
                    id: '48'
                }
            ],
            hxselectId: '',
            cid:''
        }
    },
    created () {
        let url = location.search
        var theRequest = new Object();
        if(url.indexOf("?") != -1 ){
            var str = url.substr(1);
            var strs = str.split("&");
            for(var i= 0;i<strs.length;i++){
                theRequest[strs[i].split("=")[0]] = (strs[i].split("=")[1]);
            }
        }
        let hx = theRequest.hx
        this.setCurrent(hx)
        this.changeHeader()
    },
    methods: {
        changeSelect(e) {
            this.setCurrent(e)
        },
        toStyle(parent) {
            let that =this
            if (that.hxselectId === '') {
                that.$refs.tips.tipsFadeIn({
                    text: '请选择户型'
                })
                return
            }
            window.location.href = "fengge?hx="+that.hxselectId
        },
        setCurrent:function(id){
            let me = this
            for (let i in me.hxData) {
                if (me.hxData[i].id == id) {
                    me.hxData[i].selected = true
                    me.hxselectId = id
                } else {
                    me.hxData[i].selected = false
                }
            }
        },
        changeHeader (){
            let that = this
            // 回退
            that.$bridge.callNative('UI_onBackClick', '000', function (res) {
                if (res) {
                    that.endCall = true
                }
            })
            that.$bridge.registWebNew('JS_onBackClick', function (data, nativeCallBack) {
                nativeCallBack(1)
            })
            if (!that.endCall) {
                setTimeout(function () {
                    that.changeHeader()
                }, 200)
            }
        }
    }
}
</script>

<style scoped>
    .p-container{
        height: 104%;
        padding:0 0.12rem;
        background: -webkit-linear-gradient(#5D2FAC 10%, #38349B);
        background: -o-linear-gradient(#5D2FAC 10%, #38349B);
        background: -moz-linear-gradient(#5D2FAC 10%, #38349B);
        background: linear-gradient(#5D2FAC 10%, #38349B);
        overflow: hidden;
    }
    .bottom-btn{
        width:84%;
        height:0.45rem;
        margin: 0.35rem auto;
        background:rgba(255,15,88,0.1);
        border-radius:0.22rem;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .bottom-btn img{
        width: 100%;
        height: 100%;
        position: absolute;
        top:0;
        left: 0;
        right: 0;
        bottom: 0;
    }
    .bottom-btn span{
        color:rgba(255,35,113,1);
        font-size: 0.18rem;
    }
    .select-title{
        height: 0.60rem;
        line-height: 0.60rem;
        font-size:0.16rem;
        font-weight:500;
        color:rgba(255,255,255,1);
    }
    .select-title span{
        color: #02EAFF;
    }
    .select-table{
        width: 100%;
        overflow: hidden;
        box-sizing: border-box;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-content: space-between;
    }
    .select-table-td{
        width: 48%;
        height:auto;
    }
    .select-box{
        overflow: hidden;
        border-radius: 0.06rem;
    }
    .select-image-border{
        width:100%;
        height:1.08rem;
        border:0.024rem solid  ;
        border-radius:0.06rem;
        box-sizing: border-box;
        overflow: hidden;
        border-image:linear-gradient(0deg, rgba(46,41,255,1), rgba(47,189,255,1)) 10 10;
        position: relative;
    }
    .select-image-border img{
        width: 100%;
        height: 100%;
        position: absolute;
        top:0;
        left: 0;
        z-index: 0;
    }
    .radius{
        height: 0.34rem;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        margin: 0.05rem 0 0.1rem 0;
    }
    .radius label{
        color: #ffffff;
        font-size: 0.14rem;
        margin-left: 0.06rem;
    }
    .radius .colorSelect{
        color: #0BC0DC;
    }
    .colorSelect::before{
        border: 1px solid #0BC0DC;
    }
    div>input{
        display: none;
    }
    div>label{
        position: relative;
        margin-right: 34px;
    }
    div>label::before{
        display: inline-block;
        content: "";
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 1px solid rgb(219, 219, 219);
        margin-right: 6px;
        vertical-align: bottom;
        margin-bottom: 2px;
    }
    div>input:checked+label::before{
        background-color: rgba(239, 66, 56, 0);
    }
    div>input:checked+label::after{
        display: inline-block;
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        position: absolute;
        left: 6px;
        bottom: 8px;
        background-color: #0BC0DC;
    }
    .colorSelect::after{
        display: inline-block;
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        position: absolute;
        left: 6px;
        bottom: 8px;
        background-color: #0BC0DC;
    }
    .radius input {
        display: block;
        opacity: 0;
        position: absolute;
        width: 100%;
        height: 100%;
        z-index: 3;
    }
    .top{
        width: 100%;
        height: 0.42rem;
        margin-top:0.10rem;
        display: flex;
        align-items: center;
    }
    .top .one{
        margin-left: 0;
    }
    .top-item{
        width: 36%;
        height: 100%;
        margin-left: -0.08rem;
        background: url("../../../assets/img/qz/style/topitem.png") center no-repeat;
        background-size: 100% 100%;
        line-height: 0.42rem;
        text-align: center;
        font-size:0.12rem;
        font-weight:500;
        color:rgba(255,255,255,1);
    }
    .top .active{
        background: url("../../../assets/img/qz/style/topact.png") center no-repeat;
        background-size: 100% 100%;
        color:#02F0FF;
    }
</style>
