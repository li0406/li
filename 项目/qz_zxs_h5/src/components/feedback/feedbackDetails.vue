<template>
	<section :style='{"height":screenHeight}' class="feedbackdetail-box" >
		<div style="width:100%;height:10px;background:#F7F7F7;"></div>
		<div class="feedback-box">
			<div>
                <!-- 用户反馈 -->
				<div style="height:25px;">
					<span class="pic">
						<img :src="`${logo}`" alt="" width="25px" height="25px">
					</span>
					<div class="nick">{{nick_name}}</div>
				</div>
				<div class="feedback-type">反馈类型：{{feedback_leixing}}</div>
				<div class="feedback-details">{{feedback_content}}</div>
				<div class="box">
                    <div v-for="(item,index) in feedback_imgs" :key="index"  class="tu">
                        <img  :src='`${item}`' alt=""/>
                    </div>
				</div>
                <!-- 大图 -->
                <div class="model" v-show="mask" @click="guanbi">
                    <img class="bigPic" :src="`${bigPic}`" alt=''>
                </div>
				<!-- 管理员回复 -->
				<div class="manage" v-if="noReply">
					<div style='padding:15px;'>
						<b class='reply'>齐装管理员回复：</b>
						<div v-for="(reply,re) in reply_conrents" :key="re" class="reply-content">
                            <div v-html='reply.reply_content' class="guanli"></div>
                            <div class="reply-time"> {{reply.reply_time}}</div>    
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</template>
<script>
import { getfeedbackdetails } from '@/api/api'
import moment from 'moment';
export default {
	name: 'feedbackDetails',
	data(){
		return{
			screenHeight: '',
			feedback_id:'',
			nick_name:'',
			logo:'',
			feedback_leixing:'',
			feedback_content:'',
			reply_time:'',
            feedback_imgs:[],
			reply_conrents:[],
            content:{
                'tit':'',
                'con':''
            },
            newPic:[],
            noReply:false,
            mask:false,
            bigPic:'',
            token: ''
		}
    },
	created(){
        this.feedback_id =  this.$route.query.id
        if(!this.$route.query.token) {
            this.token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImtpZCI6InN2U1hNVGRQU3Y5ZjBSbm4ifQ.eyJuYmYiOjE1NzM1MjgwNjgsImV4cCI6MTU3NjIwNjQ2OCwiand0X2lkZSI6InN2U1hNVGRQU3Y5ZjBSbm4iLCJkYXRhIjp7InVzZXJfaWQiOjIzNiwibmlja19uYW1lIjoiXHU5MDA2XHU2N2FiMCIsInR5cGUiOjIsImxvZ28iOiJodHRwOlwvXC90aGlyZHd4LnFsb2dvLmNuXC9tbW9wZW5cL3ZpXzMyXC9RMGo0VHdHVGZUSXNSZjhCdEJTYlFDSVpyc3dSd09RZERPdVlKMGtueElsRksyalZFNEdsQmZ3SWgxazRLT0pHcDlUTUY0VlRjUHJjTXhXb1RqaWM3dGdcLzEzMiIsInNleCI6MSwiYmlydGhkYXkiOiIiLCJjaXR5X2lkIjoiIiwicHJvdmluY2VfaWQiOiIiLCJwaG9uZSI6IjEzMjk4MTg2OTY3IiwiY2l0eV9uYW1lIjoiIiwicHJvdmluY2VfbmFtZSI6IiIsImRlY29yYXRpb25fc3RhZ2UiOjQsImhhZF9jaGFyYWN0ZXJ0YWdzIjoyLCJjaGFyYWN0ZXJfdGFncyI6W10sInRva2VuX3R5cGUiOjF9fQ.3XrWbpB8EpfbVynyI7S7aC7MpVzquRknyRE1BjdNCOU'
        }else{
            this.token = this.$route.query.token
        }
        this.$cookies.set('token', this.token)
        this.getDetails();
       
	},
	methods:{
        moment,
		getDetails(){
			var that = this
            that.feedback_id = this.$route.query.id
            getfeedbackdetails({
                feedback_id: that.feedback_id
            }).then((res) => {
                if (res.data.error_code === 0) {
                    this.feedback_id = res.data.data.feedback_id;
                    this.nick_name = res.data.data.nick_name;
                    this.logo = res.data.data.logo;
                    this.feedback_leixing = res.data.data.feedback_leixing;
                    this.feedback_content = res.data.data.feedback_content;
                    this.feedback_imgs = res.data.data.feedback_imgs.map((a,b) => {
                        return a;
                    });
                    this.reply_conrents = res.data.data.reply_conrent;//管理员回复
                    if(res.data.data.reply_conrent == null || res.data.data.reply_conrent == ''){
                        this.noReply = false
                    }else{
                        this.noReply = true
                    }
                    this.sendStatus()
                }
            })
        },
        sendStatus() {
            this.$bridge.callNative('Native_unread_message')
        },
        mask1(item){
            this.bigPic=item
            this.mask = true
        },
        guanbi(){
            this.bigPic=''
            this.mask = false
        },
        // 区分管理员回复的内容跟图片
        getCaption(obj,state) {
            var index = obj.indexOf("<img");
            return state === 0 ? obj.substring(0,index - 3) : obj.substring(index - 3, obj.length);
        },
        //
        getTuwen(){
            this.reply_conrents.map((item,index) => {
                this.content.tit = this.getCaption(this.reply_conrents[index].reply_content,0)
                this.content.con = this.getCaption(this.reply_conrents[index].reply_content)
            })
        },
        replaceStr(reply,re){
            return reply.reply_content.replace(/(>[^<]+)/g, $1 => ' style="display: block;width:100%;padding-bottom: 10px;"' + $1)
        }
	}
}
</script>
<style scope>
    .t_con {
        margin-top: 10px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        grid-row-gap: 10px;
        grid-column-gap: 10px;
    }

    .t_con  img {
        display: block;
        width: 100%;
    }

	.feedbackdetail-box {
		/* height: calc(100% - 10px); */
		/* background:#F7F7F7; */
		height: 100%;
		background: #fff;
		position: relative;
   }
   .feedbackdetail-box .feedback-box{
		background:#fff;
		padding:10px 15px 15px 15px; 
		box-sizing: border-box;
   }
	.feedbackdetail-box .pic img{
		display: block;
		width:0.25rem;
		height:0.25rem;
		float: left;
		border-radius: 50%;
	}
	.feedbackdetail-box .nick{
		font-size: 0.14rem;
		color:#333;
		font-weight: 700;
		float: left;
		line-height: 25px;
		padding-left: 5px;
	}
	.feedbackdetail-box .feedback-type{
		color:#999;
		font-size: 13px;
		padding: 10px 0 5px 0;
	}
	.feedbackdetail-box .feedback-details{
		color:#333;
		font-size: 0.12rem;
		line-height: 24px;
		letter-spacing:1px;
        padding-bottom: 10px;
	}
    .feedbackdetail-box .box{
        display: grid;
		grid-template-columns:  repeat(3, 1fr);
		grid-column-gap: 10px;
		grid-row-gap: 10px;
    }
	.feedbackdetail-box .box .tu img{
		width:100%;
		max-width: 100%;
		vertical-align: middle; 
	}
    .feedbackdetail-box .model{
		width:100%;
		height:100%;
		background: rgba(0,0,0,.7);
		position: fixed;
		top:0;
		left:0;
	}
    .feedbackdetail-box .bigPic{
		position: fixed;
		left: 50%;
		top:50%;
		transform: translate(-50%);
		margin-top: -202.5px;
    }
	.feedbackdetail-box .manage{
		height: auto;
		border-radius: 5px;
		background: #F7F7F7;
        margin-top: 15px;
	}
	.feedbackdetail-box .reply{
		font-size: 0.14rem;
		color: #333;
		font-weight: 700;
        display: block;
        padding-bottom: 10px;
	}
	.feedbackdetail-box .reply-content{
		font-size: 0.13rem;
		color: #333;
		line-height: 19px;
		letter-spacing:2px;
	}
	.feedbackdetail-box .reply-time{
		font-size: 0.13rem;
		color:#333;
		line-height: 0.19rem;
		letter-spacing:1px;
		text-align: right;
	}

    .feedbackdetail-box .guanli p{
        display: inline;
	}
 
	.feedbackdetail-box .reply-content img{
		max-width: 100%;
        margin-bottom:10px;
        vertical-align: middle;
	}
    .guanli >>> p img{
		width:100%;
		/* max-width: 100%; */
		height: 5rem;
		vertical-align: middle;
	}
</style>


