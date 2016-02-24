<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        if($nickname=$this->check_login()){
			$welcome='<div id="welcome-text"><p class="bg-danger">欢迎您的登录，创始者【'.$nickname.'】！Enjoy your day!</p><a href="/Home/Index/logout" type="button" class="btn btn-danger">点击退出登录</a></div>';
			$this->assign('welcome',$welcome);
		}
		
		$this->display();
    }
	
	public function login(){
		if($this->check_login()){
			$this->show('<h1>您已登录！</h1>');
			$this->redirect('Index/home');
		}
		
		if(IS_POST){
			$nickname=I('name');
			$password=I('password');
			if( $nickname && $password ){
				$user_info=M('user')->where("nickname='{$nickname}'")->find();
				if($user_info){
					if($user_info['password']==$password){
						if($user_info['state']){
							session('user_info',$nickname);
							$data['last_login_time']=date('Y-m-d H:i:s');
							$result=M('user')->where("nickname='{$nickname}'")->save($data);
							if($result!=false){
								$this->redirect('Index/home');
							}elseif($result==false){
								$this->error('登录失败，请重试~');
							}
						}else{
							$this->error('Sorry,用户已被禁用！！！');
						}
					}else{
						$this->error('请输入正确的密码哦~');
					}
				}else{
					$this->error('用户名错误，请重试！');
				}
			}else{
				$this->error('请输入正确格式的用户名、密码！！');
			}
		}
		
		$this->display();
	}
	
	public function regist(){
		if($this->check_login()){
			$this->show('<h1>您已登录！</h1>');
			$this->redirect('Index/home');
		}
		
		if(IS_POST){
			$email=I('email');
			$xuehao=I('xuehao');
			$nickname=I('nickname');
			
			if($email && $xuehao && $nickname){
				$user=M('user');
				$user_state=$user->where("nickname='{$nickname}'")->find();
				if(!$user_state){
					$email_state=$user->where("email='{$email}'")->find();
					if(!$email_state){
						$xuehao_state=$user->where("xuehao='{$xuehao}'")->find();
						if(!$xuehao_state){
							//自动生成密码！~ 6位数字 很简单 后期需要强制改密码~~~
							$password=$this->get_password(6);
							
							$data['nickname']=$nickname;
							$data['password']=$password;
							$data['xuehao']=$xuehao;
							$data['email']=$email;
							$data['registe_time']=date('Y-m-d H:i:s');
							$result=$user->add($data);
							
							//邮件发送代码
							$content=
							"Hello!~【".$nickname."】同学~
							欢迎你注册选课助手.WEB版！
							您的登录密码是：".$password."
							登录后请尽快修改您的登录密码，以免密码泄露~
							点击下面的链接登录：http://xk0.loverchen.com/Home/Index/login
							Enjoy your DAY!~ ";
							
							/*$mail = new \SaeMail();
							$mail->clean();
							$ret = $mail->quickSend( $email , '选课助手.WEB版|登录密码邮件' ,  $content , '59960558@qq.com' , 'tmnrcexmfopwbibb' );
							邮件发送代码*/
							
							//更新版 快速 邮件发送代码
							Vendor('Anda.Send_mail');
							send_mail_quick($email,'选课助手.WEB版|登录密码邮件',$content);
							
							if($result!=false){
								$this->show('注册成功！！~请到邮箱查看密码。');
								$this->redirect('Index/index');
							}else{
								$this->error('邮件发送失败，请重试~');
							}
						} else $this->error('学号已被注册~~');
					} else $this->error('邮箱已被使用~~');
				} else $this->error('昵称已被使用~~');
			}else $this->error('请输入正确格式的邮箱、学号、昵称、密码！！');
		}
		
		$this->display();
	}
	
	public function help(){
		
		$this->display();
	}
	
	public function home(){
		if(!$this->check_login()){
			$this->show('<h1>请先登录！</h1>');
			$this->redirect('Index/login');
		}
		
		$this->display();
	}
	
	private function check_login(){
		$user_nickname=session('user_info');
		if($user_nickname==null){
			return false;
		}
		else return $user_nickname;
	}
	
	public function logout(){
		session(null);
		$this->success('成功退出','index');
	}
	
	/*
	核心教务系统代码功能
	*/
	
	public function xuanke_home(){
		if(!$this->check_login()){
			$this->show('<h1>请先登录！</h1>');
			$this->redirect('Index/login');
		}
		
		$this->display();
	}
	
	public function xuanke_jiaowu_bangding(){
		if(!($nickname=$this->check_login())){
			$this->show('<h1>请先登录！</h1>');
			$this->redirect('Index/login');
		}
		
		$user_info=M('user')->where("nickname='{$nickname}'")->find();
		if(IS_POST){
			$jiaowu_pwd=I('password');
			$data=array(
						'user_id'=>$user_info['id'],
						'key'=>'jiaowu_pwd',
						'value'=>$jiaowu_pwd,
						'up_time'=>date('Y-m-d H:i:s'),
					);
			$u_e=M('user_extra')->where("user_id={$user_info['id']} AND `key`='jiaowu_pwd'")->find();
				if($u_e){
					$ret=M('user_extra')->where("user_id={$user_info['id']} AND `key`='jiaowu_pwd'")->save($data);
				}else $ret=M('user_extra')->add($data);
				if($ret) {
					$this->success('学号密码绑定成功！~');
					return;
				}
				else $this->error("绑定信息失败");	
		}
		
		$this->assign('xuehao',$user_info['xuehao']);
		$this->display();
	}
	
	public function xuanke_economize(){
		if(!($nickname=$this->check_login())){
			$this->show('<h1>请先登录！</h1>');
			$this->redirect('Index/login');
		}
		
		$user_info=M('user')->where("nickname='{$nickname}'")->find();
		//抓取页面
		$f = new \SaeFetchurl();
		$content = $f->fetch("http://xk3.ahu.cn");
		if($f->errno() == 0)  $this->assign('content',$content);
		else $this->error("获取失败失败");
		
		$this->display();
	}
	
	public function jiaowu_login(){
		if(!($nickname=$this->check_login())){
			$this->show('<h1>请先登录！</h1>');
			$this->redirect('Index/login');
		}
		
		$user_info=M('user')->where("nickname='{$nickname}'")->find();
		$xuehao=$user_info['xuehao'];
		$jiaowu_pwd=M('user_extra')->where("user_id='{$user_info['id']}' AND `key`='jiaowu_pwd'")->getField('value');
		
		//教务密码为空
		if($jiaowu_pwd==null){
			$this->show('<h1>请先绑定教务系统密码~！</h1>');
			$this->redirect('Index/xuanke_jiaowu_bangding');
		}
		/*********************清风教务系统核心代码测试*********************/
		Vendor('Anda.Fetchjw');
		
		$jw_url=$this->get_jwurl('安徽大学');
		$url=$jw_url[rand(0,count($jw_url)-1)]['url'].'default2.aspx';
		$fetchjw=new \Fetchjw(array('url'=>$url));
		$xm=$fetchjw->Login($xuehao,$jiaowu_pwd);
		if($xm==null){
			$this->error('登录失败，请重试~');
		}
		if($user_info['realname']==NULL){
			$user_info['realname']=$xm;
			M('user')->save($user_info);
		}
		
		$fetchjw->GetScore();
		//dump($ret_result);
		/*********************清风教务系统核心代码测试*********************/
		
	}
	
	public function jiaowu_kebiao_get(){
		if(!($nickname=$this->check_login())){
			$this->show('<h1>请先登录！</h1>');
			$this->redirect('Index/login');
		}
		$user_info=M('user')->where("nickname='{$nickname}'")->find();
		$xuehao=$user_info['xuehao'];
		$jiaowu_pwd=M('user_extra')->where("user_id='{$user_info['id']}' AND `key`='jiaowu_pwd'")->getField('value');
		//教务密码为空
		if($jiaowu_pwd==null){
			$this->show('<h1>请先绑定教务系统密码~！</h1>');
			$this->redirect('Index/xuanke_jiaowu_bangding');
		}
		/*********************教务系统核心代码*********************/
		Vendor('Anda.Fetchjw');
		$jw_url=$this->get_jwurl('安徽大学');
		$url=$jw_url[rand(0,count($jw_url)-1)]['url'].'default2.aspx';
		$fetchjw=new \Fetchjw(array('url'=>$url));
		$xm=$fetchjw->Login($xuehao,$jiaowu_pwd);
		if($xm==null){
			$this->error('登录失败，请重试~');
		}
		if($user_info['realname']==NULL){
			$user_info['realname']=$xm;
			M('user')->save($user_info);
		}
		$ret=$fetchjw->GetSchedule();
		dump($ret);
		/*********************教务系统核心代码*********************/
		if($ret){
			$data['user_id']=$user_info['id'];
			$data['cache']=$ret;
			$data['up_time']=date('Y-m-d H:i:s');
			$data['date']=date('Y-m-d');
			$sqlresult=M('jiaowu_kebiao')->add($data);
		}
	}
	
	//获取到可用的教务系统列表
	private function get_jwurl($school){
		$jw_url=M('website_list')->where("school='{$school}' AND state=1")->select();
		return $jw_url;
	}
	
	
	/*
	验证码相关功能
	*/
	public function checkcode_send(){
		if(!($nickname=$this->check_login())){
			$this->show('<h1>请先登录！</h1>');
			$this->redirect('Index/login');
		}
		$user_info=M('user')->where("nickname='{$nickname}'")->find();
		Vendor('Anda.Send_mail');
		$checkcode=$this->get_password(6);
		//验证码替代 ps 一天只允许保持三个有效的验证码
		$user_verify=M('user_verify');
		$date=date('Y-m-d');
		$num=$user_verify->where("`user_id`='{$user_info['id']}' AND `state`='1' AND `date`='{$date}'")->count();
		if($num<3){
			$data['user_id']=$user_info['id'];
			$data['user_nickname']=$user_info['nickname'];
			$data['checkcode']=$checkcode;
			$data['up_time']=$time=date('Y-m-d H:i:s');
			$data['date']=$date;
			$sqlresult=$user_verify->add($data);
			if($sqlresult){
				//发送邮件
				$content="Hello~".$nickname."!
				您正在使用 选课助手.WEB版 自由邮箱验证码功能。您的验证码是：
				【".$checkcode."】
				请在当天输入到需要邮箱验证的界面！使用后或过期失效。";
				$ret=send_mail_quick($user_info['email'],'选课助手.WEB版|验证码邮件',$content);
				if($ret==""){
					$this->show("邮件发送成功，请到邮箱查询邮件，并到所需页面填写验证码。");
					$this->redirect('Index/index');
				}else {
					$data['state']=2;
					$data['extra']="【邮件发送失败：】".$ret;
					$user_verify->where("user_id='{$user_info['id']}' AND checkcode='{$checkcode}' AND up_time='{$time}'")->save($data);
					$this->error('邮件发送失败。');
				}
			}else $this->error('验证码获取失败。');
		}else $this->error('您今天已经有三个未使用的验证码了，请使用后再尝试发送验证码~');
	}
	
	public function checkcode_verify($code){
		
	}
		
	private function get_password($length){
		$chars="0123456789";
		$password = '';
		for($i=0;$i<$length;$i++){
			$password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
		}

		return $password;
	}
	
	public function test(){
		$password=$this->get_password(6);
		echo $password;
	}
	
	public function website_state(){
		//进行教务系统畅通性测试
		//教务系统网站采取数据库方式保存
		$website_list=M('website_list');
		$weblist=$website_list->where("school='安徽大学'")->select();
		$n=count($weblist);
		$f = new \SaeFetchurl();
		for($i=0;$i<$n;$i++){
			$url=$weblist[$i]['url'];
			$f->fetch($url);
			if($f->errno() == 0){
				echo $url."访问成功</br>";
				$data['state']=1;
				$data['change_time']=date('Y-m-d H:i:s');
				$ret=$website_list->where("url='{$url}'")->save($data);
				if($ret)
					echo "数据库保存成功！</br></br></br>";
				else 
					echo "数据库保存失败！！！！！！！！！！！！！</br></br></br>";
			}
			else{
				echo $url."访问失败</br>";
				$data['state']=0;
				$data['change_time']=date('Y-m-d H:i:s');
				$ret=$website_list->where("url='{$url}'")->save($data);
				if($ret)
					echo "数据库保存成功！</br></br></br>";
				else 
					echo "数据库保存失败！！！！！！！！！！！！！</br></br></br>";
			}
		}
		
	}
}