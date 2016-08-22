<?php
namespace Api\Controller;
use Think\Controller;
use Think;
use Think\Exception;
use Api\Controller\CommonController;

class IndexController extends CommonController { 
	//基本配置
	//Jpush key
	private $title = 'JollyShop';
	private $app_key='bdf045e466d05c077ec2dd74';
	private $master_secret = 'e412050beb8b2dd8036fc9ed';
	
	//融云
	private $appKey = 'e0x9wycfxozdq'; 
	private $appSecret = '7nfnbzm8Npq7A';	
	
	//oss
	private $url = 'http://jolly.img-cn-beijing.aliyuncs.com'; //外网
	private $url_nei = 'oss-cn-beijing-internal.aliyuncs.com';
	private $AccessKeyID='0zHXT3orDFaVxFWn';
	private $AccessKeySecret = 'yjBwj2Om9l4Bu8qSu7yx3XOAcSxFhv';
	
	//测试
	public function test(){
		
	}

    //快速注册登录
    public  function  reg(){
        $datas = check_param(array('phone','code','jpushid'));
        if(!$datas){
            $data = I('post.');
            if (S('code'.$data['phone'])){
                if (S('code'.$data['phone']) != $data['code']){
                    json('400','验证码不正确');
                }else {
                    unset($data['code']);
                }
            }else {
                json('400','验证码已失效');
            }
            $data['addtime'] = $data['logintime']  = time();
            $data['simg'] = 'http://jolly.img-cn-beijing.aliyuncs.com/shop/touxiang.jpg';
            //$data['simg_desc'] = 'http://jolly.oss-cn-beijing.aliyuncs.com/shop/touxiang.jpg';
            $table = M('user');
            $res = $table->field('id,jpushid,token,phone')->where("phone='{$data['phone']}'")->find();
            if ($res){
                $where['logintime'] = time();
                $where['jpushid'] = I('post.jpushid');
                if ($table->where("id = '{$res['id']}'")->save($where)){
                    $res['jpushid'] = I('post.jpushid');
                    json('200','成功',$res);
                }else {
                    json('400','更新失败');
                }
            }else {
                $return = $table->add($data);
                if ($return){
                    $rongyun = new  \Org\Util\Rongyun($this->appKey,$this->appSecret);
                    $r = $rongyun->getToken($return,$data['phone'],$data['simg']);
                    if($r){
                        $rong = json_decode($r);
                        if ($rong->code == 200){
                            $where['token'] = $rong->token;
                            if ($table->where("id = $return")->save($where)){
                                $user = $table->field('id,jpushid,token,phone')->where("id = $return")->find();
                                json('200','成功',$user);
                            }else {
                                json('400','融云集成失败');
                            }
                        }else {
                            json('400','融云内部错误');
                        }
                    }else {
                        json('400','融云token获取失败');
                    }
                }else{
                    json('400','注册失败');
                }
            }
        }
        json('404','缺少参数 '.$datas);
    }
    
    //发送验证码(不检测调用)
    public function yzm(){
        $phone = I('post.phone') ? I('post.phone') : json('404','缺少参数 phone');
        if (!checkPhone($phone)) {
            json('400', '手机格式不正确');
        }
        yzm($phone);
    }

    //忘记密码
    public function forgetpass(){

        $phone = I('post.phone') ? I('post.phone') : json('404','缺少参数 phone');
        $code = I('post.code') ? I('post.code') : json('404','缺少参数 code');
        $password = I('post.password') ? I('post.password') : json('404','缺少参数 password');
        $pass = I('post.pass') ? I('post.pass') : json('404','缺少参数 pass');

        if (S('code'.$phone)){
            if (S('code'.$phone != I('post.code'))){
                json('400','验证码不正确');
            }
        }else {
            json('400','验证码已失效');
        }
        $user = M('user');
        if (mb_strlen(trim(I('post.password'))) < 6){
            json('400','密码不能小于6位');
        }
        if (I('post.password') != I('post.pass')){
            json('400','两次密码输入不一致，请重新输入');
        }
        $data['password'] = md5(trim(I('post.password')));
        if ($user->where("phone = $phone")->save($data)){
            json('200','成功');
        }else {
            json('400','新密码不能和原密码相同');
        }
    }
    
    //修改密码
    public function passedit(){
    	$datas = check_param(array('id','password','pass','fpass'));
    	if(!$datas){
    		$user = M('user');
    		$where['id'] = I('post.id');
    		$where['password'] = md5(I('post.fpass'));
    		if (!$user->where($where)->find()){
    			json('400','原密码输入有误');
    		}
    		if (mb_strlen(trim(I('post.password'))) < 6){
    			json('400','密码不能小于6位');
    		}
    		if (I('post.password') != I('post.pass')){
    			json('400','两次密码输入不一致，请重新输入');
    		}
    		$data['password'] = md5(trim(I('post.password')));
    		if ($user->where("id = '{$where['id']}'")->save($data)){
    			json('200','成功');
    		}else {
    			json('400','新密码不能和原密码相同');
    		}
    	}
    	json('404','缺少参数 '.$datas);
    }  
    
    //个人资料
    public function userinfo(){
    	$datas = check_param(array('id'));
    	if(!$datas){
 			$user = M('user');
 			$data = $user->field('id,simg,username,sex,description,age,birth,phone')->find(I('post.id'));
 			if ($data){
 				json('200','成功',$data);
 			}else {
 				json('400','获取失败');
 			}	
		}
		json('404','缺少参数 '.$datas);
    }
    
    //修改个人信息
    public function useredit(){
    	$datas = check_param(array('id'));
    	if(!$datas){
    		$user = M('user');
    		$data = I('post.');   
    		if ($data['birth']){
    			$data['birth'] = strtotime($data['birth']);
    			$data['age'] = date('Y',time()) - date('Y',$data['birth']);
    		}	
// 			if ($_FILES['simg']){
// 				$oss = new \Org\Util\Oss($this->AccessKeyID, $this->AccessKeySecret, $this->url_nei);
// 				$simg = file_simg($_FILES['simg']);
// 				if ($oss->uploadFile('jolly',$simg)){
// 	 				unlink('./Public/upfile/'.$simg);
// 	 				//unlink('./Public/thumb/'.$simg);
// 	 				$data['simg'] = $this->url.'/shop/'.$simg;
// 	 				//$data['simgdesc'] = $this->url.'/thumb/'.$simg;
// 				}else {
// 					json('400','上传失败');
// 				}
// 			}
			if ($data['simg']){
				$oss = new \Org\Util\Oss($this->AccessKeyID, $this->AccessKeySecret, $this->url_nei);
				$simg = base64_simg($data['simg']);
				if ($oss->uploadFile('jolly',$simg)){
					unlink('./Public/upfile/'.$simg);
					//unlink('./Public/thumb/'.$simg);
					$data['simg'] = $this->url.'/shop/'.$simg;
					//$data['simgdesc'] = $this->url.'/thumb/'.$simg;
				}else {
					json('400','上传失败');
				}
			}	
    		if ($user->save($data)){
    			json('200','成功');
    		}else {
    			json('400','没有修改');
    		}
    	}
    	json('404','缺少参数 '.$datas);
    }


	
	
	
	
	
	
	
	
	
}