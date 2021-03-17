<?php
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
use Library\Qizuang\JWTAuth\Src\Facade\Auth;
use Common\Enum\TellEnum;
use Think\Hook;
class MainController extends HomeBaseController{
	public function index(){
		//查询最近一个月的登录日志
		$logs = D("Adminlogging")->getLastLogWeekList(session("uc_userinfo.user"));
		$this->assign("logs",$logs);
		//获取在线系统的图片
		$system = array(
			array("id"=>"2","type" => "web", "img"=>"/assets/common/img/gl.png","link"=>"/redirect?type=gl","name"=>"后台管理系统","enabled"=>1),
			array("id"=>"5","type" => "web","img"=>"/assets/common/img/new_gl.png","link"=>"/redirect?type=website","name"=>"新后台管理系统","enabled"=>1),
			array("id"=>"7","type" => "web","img"=>"/assets/common/img/mall.png","link"=>"/redirect?type=yy","name"=>"运营系统","enabled"=>1),
            array("id"=>"8","type" => "web","img"=>"/assets/common/img/mall.png","link"=>"/redirect?type=jiaju","name"=>"家具管理系统","enabled"=>1),
            array("id"=>"13","type" => "vue","img"=>"/assets/common/img/mall.png","link"=>"//168services.qizuang.com/dashboard","name"=>"齐装开发云平台","enabled"=>1),
			array("id"=>"10","type" => "vue","img"=>"/assets/common/img/mall.png","link"=>"//168zxs.qizuang.com/dashboard","name"=>"APP管理系统","enabled"=>1),
            array("id"=>"11","type" => "vue","img"=>"/assets/common/img/mall.png","link"=>"//168ygj.qizuang.com/dashboard","name"=>"云管家-商家端","enabled"=>1),
            array("id"=>"12","type" => "vue","img"=>"/assets/common/img/mall.png","link"=>"//168sales.qizuang.com/dashboard","name"=>"销售系统","enabled"=>1),
            array("id"=>"14","type" => "vue","img"=>"/assets/common/img/mall.png","link"=>"//168ruanwen.qizuang.com/dashboard","name"=>"软文管理系统","enabled"=>1),
            array("id"=>"15","type" => "vue","img"=>"/assets/common/img/mall.png","link"=>"//168report.qizuang.com/jump","name"=>"数据驾驶舱","enabled"=>1),
        );
		$nodes = S("Cache:uc:menu:".session("uc_userinfo.uid"));
		if(session("uc_userinfo.uid") != 1){
			foreach ($system as $key => $value) {
			   if(!in_array($value["id"],$nodes)){
					$system[$key]["enabled"] = 0;
			   }
			}
		}
		//添加修改密码提醒(每隔60天修改一次密码)
		$this->checkPassTime();
		$this->assign("system",$system);
		$this->display();
	}

    /**
     * 个人信息
     * @return [type] [description]
     */
    public function basic(){
        $model = D("Adminuser");
        if($_POST){
            $data = array(
                "name"=>I("post.username"),
                // "tel_work1"=>I("post.tel"),
                "qq_work1"=>I("post.qq"),
                // "tel_customer_ser_num"=>I("post.tel_customer_ser_num")
                          );
            $status = 0;
            if($model->create($data,2)){
                $id = session("uc_userinfo.id");
                $i = $model->editUserInfo($id,$data);
                if($i !== false){
                    // $oldvoipaccount = I("post.oldvoipaccount");
                    // //解除原有的绑定
                    // if(!empty($oldvoipaccount)){
                    //     $subData = array(
                    //         "use_id"=>0
                    //               );
                    //     D("Adminvoiptels")->editVoipInfo($oldvoipaccount,$subData);
                    // }
                    // $voipAccount = I("post.voipaccount");
                    // if(!empty($voipAccount)){
                    //     //修改VOIP电话
                    //     $subData = array(
                    //         "use_id"=>$id
                    //                   );
                    //     D("Adminvoiptels")->editVoipInfo($voipAccount,$subData);
                    // }

                    $status = 1;
                    $errMsg ="操作完成";
                    session("uc_userinfo.name",$data["name"]);
                    //session("uc_userinfo.tel_work1",$data["tel_work1"]);
                    session("uc_userinfo.qq_work1",$data["qq_work1"]);
                    //session("uc_userinfo.voipaccount",$voipAccount);
                    $params = array(
                            "type"=>"editadmin",
                            "info"=>"编辑用户【$id ".session("uc_userinfo.name")."】信息",
                            "errMsg"=>"编辑账号:".$id
                                    );
                    \Think\Hook::listen("addAdminLog",$params);
                }else{
                    $errMsg = "操作失败,请稍后再试！";
                }
            }else{
                $errMsg = $model->getError();
            }
            $this->ajaxReturn(array("data"=>"","info"=>$errMsg,"status"=>$status));
        }else{
            $id = session("uc_userinfo.id");
            //查询用户的信息
            $userInfo = $model->findUserInfoById($id,"yuntongxun");
            $this->assign("userInfo",$userInfo);
            $this->assign("navIndex",1);
            $this->display();
        }
    }

	/**
	 * 个人头像
	 * @return [type] [description]
	 */
	public function portrait(){
		if($_POST){
			$model = D("Adminuser");
			//去掉头像的参数部门
			$src = I("post.src");
			$data = array(
				"logo"=>$src
			);
			$status = 0;
			if($model->create($data,3)){
				$id = session("uc_userinfo.id");
				$i = $model->editUserInfo($id,$data);
				if($i !== false){
					$status = 1;
					$errMsg ="操作完成";
					session("uc_userinfo.logo",$data["logo"]);

					$params = array(
							"type"=>"editadmin",
							"info"=>"编辑用户【$id ".session("uc_userinfo.name")."】的头像信息",
							"errMsg"=>"编辑账号:".$id
									);
					\Think\Hook::listen("addAdminLog",$params);
				}else{
					$errMsg = "操作失败,请稍后再试！";
				}
			}else{
				 $errMsg = $model->getError();
			}
			$this->ajaxReturn(array("data"=>"","info"=>$errMsg,"status"=>$status));
		}else{
			$this->assign("navIndex",'portrait');
			$this->display();
		}
	}

	/**
	 * 修改密码
	 * @return [type] [description]
	 */
	public function password(){
		if($_POST){
			$status = 0;
			$safe_tel = session("uc_userinfo.safe_tel");
			if(!empty($safe_tel)){
				$code = I("post.code");
				$tel = session("uc_userinfo.safe_tel");
				$result = R("Common/Sms/verifysmscode",array($code,$tel));
				if(!$result["status"]){
					$this->ajaxReturn(array("data"=>"","info"=>$result["errMsg"],"status"=>$status));
				}
			}

			$model = D("Adminuser");
			$data = array(
				"pass"=>I("post.newpassword"),
				"confirmpassword"=>I("post.confirmpassword")
						  );
			if($model->create($data,5)){
				//查询用户的信息
				$id = session("uc_userinfo.id");
				//查询用户的信息
				$userInfo = $model->findUserInfoById($id,"yuntongxun");
				$password = I("post.password");
				if(md5($password) == $userInfo["pass"]){
					$newpassword = I("post.newpassword");
					$data = array(
						"pass"=>md5(trim($newpassword)),
						"time"=>date("Y-m-d H:i:s"),
								  );
					$i = $model->editUserInfo($id,$data);
					if($i !== false){
						$status = 1;
						$errMsg = "操作成功";
						$params = array(
							"type"=>"editadmin",
							"info"=>"编辑用户【$id ".session("uc_userinfo.name")."】的密码",
							"errMsg"=>"编辑账号:".$id
									);
					\Think\Hook::listen("addAdminLog",$params);
					}else{
						$errMsg = "操作失败,请联系技术部门！";
					}
				}else{
					$errMsg = "原始密码错误！";
				}
			}else{
				$errMsg = $model->getError();
			}
			$this->ajaxReturn(array("data"=>"","info"=>$errMsg,"status"=>$status));

		}else{
			$this->assign("navIndex",'password');
			$this->display();
		}
	}

	/**
	 * 账号安全
	 * @return [type] [description]
	 */
	public function account(){
		$model = D("Adminuserquestion");
		//查询码是否设置过安全问题
		$id = session("uc_userinfo.id");
		$count = $model->getQuestionRecord($id);
		if($_POST){
			if($count == 0){
				$data = array(
					"question1"=>I("post.question1"),
					"answer1"=>md5(md5(I("post.answer1"))),
					"question2"=>I("post.question2"),
					"answer2"=>md5(md5(I("post.answer2"))),
					"question3"=>I("post.question3"),
					"answer3"=>md5(md5(I("post.answer3")))
							  );
				$status = 0;
				if($model->create($data,1)){
					$data["uid"] = session("uc_userinfo.id");
					$data["time"] = time();
					$i = $model->addQuestion($data);
					if($i !== false){
						$status = 1;
						$errMsg = "操作成功";
						$params = array(
							"type"=>"editadmin",
							"info"=>"设置用户【$id ".session("uc_userinfo.name")."】的密保账号问题",
							"errMsg"=>"设置账号:".$id
									);
						\Think\Hook::listen("addAdminLog",$params);
					}else{
						$errMsg = "操作失败,请联系技术部门！";
					}
				}else{
					$errMsg = $model->getError();
				}
				$this->ajaxReturn(array("data"=>"","info"=>$errMsg,"status"=>$status));
			}
		}else{
			if($count == 0){
				$question["question1"] = array(
					"我爷爷的姓名是",
					"我外公的姓名是？",
					"我外婆的姓名是？",
					"我母亲的生日是？",
					"我爱人的生日是？",
					"我孩子的生日是？",
				);
				$question["question2"] = array(
						"我比较喜欢的球类运动是？",
						"我最喜欢的一首歌是？",
						"我最喜欢车的汽车品牌是？",
						"我最喜欢的一部电视剧名称是？",
						"我的幸运数字？",
						"我向往的旅游地是？"
				);
				$question["question3"] = array(
						"我第一次购房的日期？",
						"我的宠物的名字是？",
						"我的高中班主任的名字是？",
						"我高考的总分是？",
						"我高中的学号是？",
						"我的工号是？",
				);
				$this->assign("question",$question);
			}
			$this->assign("navIndex",4);
			$this->display();
		}
	}

	/**
	 * 绑定电话
	 * @return [type] [description]
	 */
	public function telephone(){
		if($_POST){
			$safe_tel = session("uc_userinfo.safe_tel");
			if(empty($safe_tel)){
				$code = I("post.code");
				$tel  = I("post.tel");
				$id = session("uc_userinfo.id");
				$status  = 0;
				$result = R("Common/Sms/verifysmscode",array($code,$tel));
				if(!$result["status"]){
					$this->ajaxReturn(array("data"=>"","info"=>$result["errMsg"],"status"=>$status));
				}
				$data = array(
					"safe_tel"=>$tel
							  );
				$model = D("Adminuser");
				if($model->create($data,6)){
					$i = $model->editUserInfo($id,$data);
					if($i !== false){
						$status = 1;
						session("uc_userinfo.safe_tel",$tel);
						$errMsg = "操作成功";
						$params = array(
							"type"=>"editadmin",
							"info"=>"设置用户【$id ".session("uc_userinfo.name")."】的密保手机",
							"errMsg"=>"设置账号:".$id
									);
						\Think\Hook::listen("addAdminLog",$params);
					}else{
						$errMsg = "操作失败,请联系技术部门！";
					}
				}else{
					$errMsg = $model->getError();
				}
			}else{
				$errMsg = "安全手机已绑定,请勿重复绑定！";
			}
			$this->ajaxReturn(array("data"=>"","info"=>$errMsg,"status"=>$status));
		}else{
			$safe_tel = session("uc_userinfo.safe_tel");
			if(!empty($safe_tel)){
				header("location:".C("UC_URL")."/account");
				die();
			}
			$this->assign("navIndex",4);
			$this->display();
		}
	}

		/**
	 * 绑定电话
	 * @return [type] [description]
	 */
	public function untelephone(){
		 $safe_tel = session("uc_userinfo.safe_tel");
		if($_POST){
			if(!empty($safe_tel)){
				$code = I("post.code");
				$id = session("uc_userinfo.id");
				$status  = 0;
				$result = R("Common/Sms/verifysmscode",array($code,$safe_tel));
				if(!$result["status"]){
					$this->ajaxReturn(array("data"=>"","info"=>$result["errMsg"],"status"=>$status));
				}
				$data = array(
					"safe_tel"=>''
							  );
				$model = D("Adminuser");
				$i = $model->editUserInfo($id,$data);
				if($i !== false){
					$status = 1;
					session("uc_userinfo.safe_tel",null);
					$errMsg = "操作成功";
					$params = array(
							"type"=>"editadmin",
							"info"=>"解除用户【$id ".session("uc_userinfo.name")."】的密保手机",
							"errMsg"=>"设置账号:".$id
									);
					\Think\Hook::listen("addAdminLog",$params);
				}else{
					$errMsg = "操作失败,请联系技术部门！";
				}
			}

			$this->ajaxReturn(array("data"=>"","info"=>$errMsg,"status"=>$status));
		}else{
			if(empty($safe_tel)){
				header("location:".C("UC_URL")."/account");
				die();
			}
			$this->assign("navIndex",4);
			$this->display();
		}
	}

    /**
     * 登录设置
     * @return [type] [description]
     */
    public function loginset()
    {
        if (1 != session("uc_userinfo.uid")) {
            $this->_error('本功能只有超级管理员才可以使用!');
            die();
        }
        $Doption        = D('Options');
        $optionone      = $Doption->getOptionNameCC('onlyadmin');
        $optionother    = $Doption->getOptionNameCC('onlyadmin_and_with_otherthings');
        $names = $Doption->getOptionAdminName(1,$optionother['option_value']);
        $info['onlyadmin'] = $optionone['option_value'];
        if($_POST) {
            $data = array();
            $data['onlyadmin'] = I("post.onlyadmin");
            $otheradmins = I("post.otheradmin");
            if (isset($data['onlyadmin'])) {
                if($data['onlyadmin'] == 1){
                //onlyadmin = 2 保存为超管登录状态+额外账号登录状态
                    $option['onlyadmin'] = 1;
                    $status         = $Doption->setOptionNameValue($option);//写入超管登录
                    if(!empty($otheradmins)){
                        $ids            = $Doption->getOptionAdminName(2,$otheradmins);//获取id拼接的字符串
                        $otheroption['onlyadmin_and_with_otherthings'] = $ids;
                        $otherstatus    =  $Doption->setOptionNameValue($otheroption);//写入特殊登录账号
                    }

                }elseif($data['onlyadmin'] == 0){
                    $option['onlyadmin'] = 0;
                    $status         = $Doption->setOptionNameValue($option);//写入正常登陆
                }

                $reinfo = '保存成功!';
                $status = count($status);
            } else {
                $reinfo = '提交的信息部有误!';
                $status = 0;
            }

			$this->ajaxReturn(array("data"=>"","info"=>$reinfo,"status"=>$status));
		}
		$this->assign('otheradmin',$names);
		$this->assign('info', $info);
		$this->display();
	}

	/**
	 * 全局设置
	 */
	public function globalset()
	{
		if (1 != session("uc_userinfo.uid")) {
			$this->_error('本功能只有超级管理员才可以使用!');
			die();
		}
		$Doption        = D('Options');
		$TelCenter_Channel      = $Doption->getOptionNameCC('TelCenter_Channel');
		$TelCenter_Diy_id       = $Doption->getOptionNameCC('TelCenter_Diy_id');
		$TelCenter_Diy_Channel  = $Doption->getOptionNameCC('TelCenter_Diy_Channel');
		$TelCenter_Diy_names      = $Doption->getOptionAdminName(1,$TelCenter_Diy_id['option_value']);
		$info['TelCenter_Channel'] = $TelCenter_Channel['option_value'];
		$info['TelCenter_Diy_names'] = $TelCenter_Diy_names;
		$info['TelCenter_Diy_Channel'] = $TelCenter_Diy_Channel['option_value'];//查询
		if($_POST) {
			$data = array();
			$data['TelCenter_Channel']      = I("post.TelCenter_Channel");
			$data['TelCenter_Diy_Channel']  = I("post.TelCenter_Diy_Channel");
			$data['TelCenter_Diy_id']    = I("post.TelCenter_Diy_names");
			$data['TelCenter_Diy_id']    = $Doption->getOptionAdminName(2,$data['TelCenter_Diy_id']);

			$result  =  $Doption->setOptionNameValue($data);//写入通信数据
			if($result['TelCenter_Channel'] == 1 || $result['TelCenter_Diy_Channel'] == 1 || $result['TelCenter_Diy_id'] == 1){
				$this->ajaxReturn(array("data"=>$result,"info"=>'保存成功!',"status"=>1));
			}else{
				$this->ajaxReturn(array("data"=>$result,"info"=>'操作失败!',"status"=>0));
			}

		}

		// 电话提供商 枚举信息
        $tellChannelList = TellEnum::TEL_CHANNEL;
		$this->assign('tellChannelList', $tellChannelList);

		$this->assign('info', $info);
		$this->display();
	}

	/**
	 * 跳转页面
	 * @return [type] [description]
	 */
	public function redirect(){
        $SCHEME_HOST = $this->SCHEME_HOST;
		$type = I("get.type");
		switch ($type) {
			case 'gl':
				$href = $SCHEME_HOST['scheme_full']. "168.qizuang.com/wwgadmins/chklogin";
				break;
			case 'website':
                //添加token
//                $token = $this->approvalToken(true);
//                session('uc_userinfo.sales_token', $token);
				$href = $SCHEME_HOST['scheme_full']."168new.qizuang.com";
				break;
			case 'yy':
				$href = $SCHEME_HOST['scheme_full']."168yy.qizuang.com";
				break;
            case 'jiaju':
                $href = $SCHEME_HOST['scheme_full']."168jiaju.qizuang.com";
                break;
            case 'sms':
                $href = $SCHEME_HOST['scheme_full']."168services.qizuang.com";
                break;
			default:
				$this->_error();
			   die();
				break;
		}
		header("location:".$href);
	}

	/**
	 * 发送短信
	 * @return [type] [description]
	 */
	public function sendaccountsms(){
		R("Common/Sms/sendaccountsms");
	}

	/**
	 * 解除绑定
	 * @return [type] [description]
	 */
	public function sendunaccountsms(){
		R("Common/Sms/sendunaccountsms");
	}


    /**
     * 添加修改密码提醒(每隔60天修改一次密码)
     */
    private function checkPassTime()
    {
        $id = session("uc_userinfo.id");
        $check_alert = S('Uc:Home:Main:CheckAlert:' . $id);
        if ($check_alert != 1 || $check_alert == false) {
            S('Uc:Home:Main:CheckAlert:' . $id, 1, 5184000);
            $this->assign('check_alert', 1);
        }
    }
	//下发去装修说后台的token
	public function approvalToken($diretc_use = false)
	{
		$user = session('uc_userinfo');
		if(empty($user)){
			return ['error_code' => 4000005, 'error_msg' =>'未获得后台授权'];
		}
		$user_profile = $token_data = [
			'nick_name' => $user['name'],
			'user_name' => $user['user'],
			'role_name' => $user['role_name'],
			'logo' => $user['logo'],
			'user_id' => $user['id'],
		];
		$token_data['token_type'] = 2;
		$token_data['role_id'] = $user['uid'];
		//下发Token
		$jwt_token = Auth::getToken($token_data);
		if($diretc_use){
		    return $jwt_token;
        }
		$response = [
			'error_code' => 0,
			'data' => [
				'info' => $user_profile,
				'jwt_token' => $jwt_token
			]
		];
		$this->ajaxReturn($response);
	}
}
