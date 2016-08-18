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
		$oss = new \Org\Util\Oss($this->AccessKeyID, $this->AccessKeySecret, $this->url_nei);
		if ($_FILES['simg']){
			$simg = file_simg($_FILES['simg']);
			if ($oss->uploadFile('jolly',$simg)){
 				unlink('./Public/upfile/'.$simg);
 				unlink('./Public/thumb/'.$simg);
			}else {
				json('400','上传失败');
			}
		}
		
	}
	
    //注册
    public  function  register(){
    	$datas = check_param(array('phone','password','pass','jpushid','code'));
    	if(!$datas){
    		$data = I('post.');
    		$data['addtime'] = $data['logintime']  = time();
			if (mb_strlen(trim($data['password'])) < 6){
				json('400','密码不能小于6位');
			}
			if ($data['password'] != $data['pass']){
				json('400','两次密码输入不一致，请重新输入');
			}
			if (S('code'.$data['phone'])){
				if (S('code'.$data['phone']) != $data['code']){
					json('400','验证码不正确');
				}else {
					unset($data['code']);
				}
			}else {
				json('400','验证码已失效');
			}
			unset($data['pass']);
    		$data['password'] = md5(trim(I('post.password')));
			$data['simg'] = 'http://jolly.img-cn-beijing.aliyuncs.com/shop/touxiang.jpg';
			//$data['simg_desc'] = 'http://jolly.oss-cn-beijing.aliyuncs.com/shop/touxiang.jpg';
    		$table = M('user');
    		if ($table->where("phone='{$data['phone']}'")->find()){
    			json('400','该账号已注册');
    		}
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
    	json('404','缺少参数 '.$datas);
    }
    
    //发送验证码(不检测调用)
    public function yzm(){
    	$datas = check_param(array('phone'));
    	if(!$datas){
    		$phone = I('post.phone');
    		if (!checkPhone($phone)){
    			json('400','手机格式不正确');
    		}
    		yzm($phone);
    	}
    	json('404','缺少参数 '.$datas);
    }
    
    //登录
    public function login(){
    	$datas = check_param(array('phone','password','jpushid'));
    	if(!$datas){
	    	$table = M('user');
	    	$phone=I('post.phone');
	    	$return = $table->where("phone=$phone or id = $phone")->find();	
	    	if($return){
	    		$data['password'] = md5(I('post.password'));
	    		$data['_string'] = "(id = $phone) or (phone = $phone)";
	    		$user = $table->field('id,phone,jpushid,token,username')->where($data)->find();
	    		if($user){
	    			$where['logintime'] = time();
	    			$where['jpushid'] = I('post.jpushid');	
	    			if ($table->where("id = '{$user['id']}'")->save($where)){
	    				$user['jpushid'] = I('post.jpushid');
	    				json('200','成功',$user);
	    			}else {
	    				json('400','更新失败');
	    			}
	    		}else{
	    			json('400','密码错误');
	    		}
	    	}else{
	    		json('400','未注册');
	    	}
    	}
    	json('404','缺少参数 '.$datas);
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
    
    //忘记密码
    public function forgetpass(){
    	$datas = check_param(array('phone','code','password','pass'));
    	if(!$datas){
    		$phone = I('post.phone');
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
    	json('404','缺少参数 '.$datas);
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

	//意见反馈
	public function feelback(){
		$datas = check_param(array('uid'));
    	if(!$datas){
			$where = I('post.');
			$where['addtime'] = time();
			$table = M('feedback');
			if ($table->add($where)){
				json('200');
			}else {
				json('400','反馈失败');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//banner
	public function banner(){
		
		$table = M('banner');
		$data = $table->field('id,title,simg,pid')->select();
		if ($data){
			json('200','成功',$data);
		}else {
			json('400','失败');
		}		
	}
	
	//商品评价
	public function addcomment(){
		$datas = check_param(array('uid','description','mark','is_good','id','orderid'));
		if(!$datas){
			$where = I('post.');
			$table = M('comment');
			$goods = M('goods');
			$user = M('user');
			$users = $user->field('username,simg')->find($where['uid']);
			$where['typeid'] = $goods->where("id = '{$where['id']}'")->getField('pid');
			$where['username'] = $users['username'];
			$where['simg'] = $users['simg'];
			$where['addtime'] = $data['addtime'] = time();
			$where['type'] = 'goods';
			unset($where['id']);
			$id = $table->add($where);
			if ($id){
				if($_FILES){
					$data['type'] = 'comment';
					$data['pid'] = $id;
					$img = M('img');
					$data1 = $_FILES;
					$oss = new \Org\Util\Oss($this->AccessKeyID, $this->AccessKeySecret, $this->url_nei);
					foreach ($data1 as $val){				
						$simg = file_simg($val);
						if ($oss->uploadFile('jolly',$simg)){
							unlink('./Public/upfile/'.$simg);
							//unlink('./Public/thumb/'.$simg);
							$data['simg'] = $this->url.'/shop/'.$simg;
							//$data['simgdesc'] = $this->url.'/thumb/'.$simg;
							$img->add($data);
						}else {
							json('400','上传失败');
						}
					}
					$table->where("id = $id")->setField('is_img',2);
				}
				//改变清单状态
				
				//改变订单状态
				
				json('200','评价成功');
			}else {
				json('400','评价失败');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//回复评价
	public function replycomment(){
		$datas = check_param(array('uid','description','pid'));
		if(!$datas){
			$where = I('post.');
			$table = M('comment');
			$user = M('user');
			$users = $user->field('username,simg')->find($where['uid']);
			$where['username'] = $users['username'];
			$where['simg'] = $users['simg'];
			$where['addtime'] = time();
			$where['type'] = 'goods';
			if ($table->add($where)){
				json('200','回复成功');
			}else {
				json('400','回复失败');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//商品列表
	public function goodslist(){
		//$datas = check_param(array('uid'));
		//if(!$datas){
			$table = M('goods');
			$img = M('img');
			$page = I('post.page') ? I('post.page') : 1;
			$pages = ($page - 1)*15;
			if (I('post.keyword')){
				$keyword = I('post.keyword');
				$where['t_goods.title'] = array('like',"%$keyword%");
			}
			if (I('post.groupid')){
				$where['g.groupid'] = I('post.groupid');
			}
			if (I('post.subgroupid')){
				$where['g.subgroupid'] = I('post.subgroupid'); 
			}
			if (I('post.min') || I('post.max')){
				$min = I('post.min') ? I('post.min') : 0;
				$max = I('post.max') ? I('post.max') : 0;
				if ($min < $max){
					$where['t_goods.prices'] = array(array('egt',$min),array('elt',$max));
					//$where['t_goods.prices'] = array('elt',$max);					
				}else {
					$where['t_goods.prices'] = array(array('egt',$max),array('elt',$min));
				}
			}
			if (I('post.attr')){
				$attr =I('post.attr');
				$attrs = explode(',', $attr);
				$goods = M('goods');
				$where['t_goods_attr.attr_id'] = array('in',$attrs);
				$count = 'count ='.count($attrs);
			}
			if (I('post.ord') == 1){
				$order = 't_goods.sale desc,g.status desc,t_goods.status desc,t_goods.addtime desc';
			}elseif (I('post.ord') == 2){
				$order = 't_goods.prices asc,g.status desc,t_goods.status desc,t_goods.addtime desc';
			}elseif (I('post.ord') == 3){
				$order = 't_goods.prices desc,g.status desc,t_goods.status desc,t_goods.addtime desc';
			}else {
				$order = 'g.status desc,t_goods.status desc,t_goods.addtime desc';
			}
			$where['t_goods.pid'] = array('neq',0);
			$where['g.state'] = 2;
			$where['t_goods.state'] = 2;
			//coalesce(t_img.simg,g.simg) as simg
			$data = $table->field('t_goods.id,t_goods.title,g.simg,count(t_goods_attr.attr_id) as count,g.groupid,g.subgroupid,t_goods.price,t_goods.prices')
			->join('left join t_goods as g on g.id = t_goods.pid')
			->join('left join t_goods_attr on t_goods.id = t_goods_attr.goods_id')
			//->join("left join t_img on t_img.pid = t_goods.id and t_img.type = 'goods'")
			->group('t_goods.id')
			->where($where)->having($count)->order($order)->limit($pages,15)->select();
			if ($data){
				$comment = M('comment');
				foreach ($data as $key=>$val){
					$data[$key]['comment'] = $comment->where("type = 'goods' and typeid = '{$val['id']}'")->count();
					$simg = $img->where("pid = '{$val['id']}' and type='goods'")->getField('simg');
					if ($simg){
						$data[$key]['simg'] = $simg;
					}
				}
				json('200','成功',$data);
			}else {
				json('400','没有商品了');
			}
		//}
		//json('404','缺少参数 '.$datas);
	}
	
	//获取分类
	public function getgroup(){
		$table = M('group');
		$pid = I('post.pid') ? I('post.pid') : 0;
		$data = $table->field('id,title,simg')->where("pid='{$pid}'")->order('ord asc')->select();
		if ($data){
			json('200','成功',$data);
		}else {
			json('400','没有分类');
		}
	}
	
	//获取自定义属性
	public function getattr(){
		$datas = check_param(array('pid'));
		if(!$datas){
			$table = M('group_attr');
			$attr = M('attr');
			$pid = I('post.pid');
			$data = $table->field('t_attr.id,t_attr.title')
			->join('left join t_attr on t_attr.id = t_group_attr.attr_id')
			->where("t_group_attr.group_id='{$pid}'")->select();
			foreach ($data as $key=>$val){
				$data[$key]['catid'] = $attr->field('id,title')->where("pid = '{$val['id']}'")->select() ? $attr->field('id,title')->where("pid = '{$val['id']}'")->select() : array();
			}
			if ($data){
				json('200','成功',$data);
			}else {
				json('400','没有分类');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//添加地址
	public function address(){
		$datas = check_param(array('name','phone','city','address','uid'));
		if(!$datas){
			$where = I('post.');
			$table = M('address');
			$data = $table->add($where);
			if ($data){
				json('200','成功');
			}else {
				json('400','失败');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//编辑地址
	public function edit_address(){
		$datas = check_param(array('id','name','phone','city','address'));
		if(!$datas){
			$where = I('post.');
			$table = M('address');
			$data = $table->save($where);
			if ($data){
				json('200','成功');
			}else {
				json('400','没有任何修改');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//我的地址
	public function my_address(){
		$datas = check_param(array('uid'));
		if(!$datas){
			$uid = I('post.uid');
			$table = M('address');
			$data = $table->field('id,name,phone,city,address,state')->where("uid = '{$uid}'")->order('id desc')->select();
			if ($data){
				json('200','成功',$data);
			}else {
				json('400','您还没有添加地址');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//地址详情
	public function address_info(){
		$datas = check_param(array('id'));
		if(!$datas){
			$table = M('address');
			$data = $table->field('id,name,phone,city,address,state')->find(I('post.id'));
			if ($data){
				json('200','成功',$data);
			}else {
				json('400','失败');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//地址删除
	public function del_address(){
		$datas = check_param(array('id'));
		if(!$datas){
			$table = M('address');
			$data = $table->delete(I('post.id'));
			if ($data){
				json('200','成功');
			}else {
				json('400','失败');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//地址设为默认
	public function def_address(){
		$datas = check_param(array('id','uid'));
		if(!$datas){
			$where = I('post.');
			$table = M('address');
			$res = $table->where($where)->find();
			if ($res){
				$table->where("uid = '{$where['uid']}'")->setField("state","1");
				$data = $table->where("id = '{$where['id']}'")->setField("state","2");
				if ($data){
					json('200','成功');
				}else {
					json('400','失败');
				}
			}else {
				json('400','地址不存在');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//商品基础信息
	public function goods_info(){
		$datas = check_param(array('id','uid'));
		if(!$datas){
			$table = M('goods');
			$where['id'] = I('post.id');
			$where['uid'] = I('post.uid');
			//$data = $table->field('id,title,price,description,prices')->find($where['id']);
			$data = $table->field('t_goods.id,t_goods.title,IF(t_goods.description,t_goods.description,g.description) as description,t_goods.good,t_goods.sale,t_goods.pid,g.simg,g.groupid,g.subgroupid,t_goods.price,t_goods.prices,t_goods.state,g.state as state1,g.seotitle,g.seokeyword,g.seodescription')
			->join('left join t_goods as g on g.id = t_goods.pid')
			->where("t_goods.id = '{$where['id']}'")->find();
			if ($data['state'] == 2 && $data['state1'] == 2){
				//获取图片
				$img = M('img');
 				$imgs = $img->where("pid = '{$data['id']}' and type = 'goods'")->getField('simg',true);
 				if ($imgs){				
					$data['simg'] = $imgs;
 				}else{
					$simg = $data['simg'];
					unset($data['simg']);
					$data['simg'][0] = $simg;
				}
				//获取地址
				$address = M('address');
				$data['address'] = $address->field('id,name,phone,city,address')->where("uid = '{$where['uid']}'")->order('state desc,id desc')->find() ? $address->field('id,name,phone,city,address')->where("uid = '{$where['uid']}'")->order('state desc,id desc')->find() : '';
				//好评率是否计算
				if (!$data['good']){
					//后续加入
					$data['good'] = '100';
				}
				//显示评论
				$comment = M('comment');
				$data['comment_count'] = $comment->where("type = 'goods' and pid = 0 and typeid = '{$data['pid']}'")->count();
				$where1['t_comment.typeid'] = $data['pid'];
				$where1['t_comment.type'] = 'goods';
				$where1['t_comment.pid'] = 0;
				$where1['t_comment.is_good'] = array('lt',3);
				$where1['t_comment.is_img'] = 2;
				$data['comment'] = $comment->field("t_comment.id,t_comment.uid,t_comment.mark,t_comment.username,t_comment.simg,t_comment.description,t_comment.addtime,t_comment.is_hide")
				//->join('left join t_comment as c on c.pid = t_comment.id')
				->where($where1)->order("t_comment.is_good asc,t_comment.addtime desc")->limit(3)->select();
				foreach ($data['comment'] as $key=>$val){
					$data['comment'][$key]['img'] = $img->where("pid = '{$val['id']}' and type = 'comment'")->getField('simg',true);
				}
				//是否收藏
				$cell = M('cell');
				$data['is_cell'] = $cell->where("type = 'goods' and uid = '{$where['uid']}' and pid = '{$where['id']}'")->find() ? 2 : 1;
				//品类筛选
				$attr = M('goods_attr');
				$attrs = M('attr');				
				$data['attr'] = $attr->join('left join t_attr on t_attr.id = t_goods_attr.attr_id')
				->where("t_goods_attr.goods_id = '{$data['id']}'")->getField('t_attr.pid,t_goods_attr.attr_id');
				$data['attr_list'] = $attr->field("t_attr.id,t_attr.title")
				->join('left join t_attr on t_attr.id = t_goods_attr.attr_id')
				->where("t_goods_attr.goods_id = '{$data['pid']}'")->select();
				foreach ($data['attr_list'] as $key=>$v){
					$data['attr_list'][$key]['catid'] = $attrs->field("t_attr.id,t_attr.title")->where("t_attr.pid = '{$v['id']}'")->select();		
					//print_r($data['attr_list'][$key]['catid']);			
					foreach ($data['attr_list'][$key]['catid'] as $keys=>$value){
						$data['attr_list'][$key]['catid'][$keys]['is_select'] = in_array($value['id'], $data['attr']) ? 2 : 1;
						$array = array();
						if ($data['attr_list'][$key]['catid'][$keys]['is_select'] == 2){
							$data['attr_list'][$key]['catid'][$keys]['goods_id'] = $where['id'];
							$data['attr_list'][$key]['catid'][$keys]['is_state'] = 2;
						}else {
							$array[$data['attr_list'][$key]['id']] = $data['attr_list'][$key]['catid'][$keys]['id'];
							$arr = array_replace($data['attr'], $array);
							//print_r($arr);
							$where11['t_goods_attr.attr_id'] = array('in',$arr);
							$where11['t_goods.pid'] = $data['pid'];
							$count = 'count ='.count($arr);
							$ress = $table->field('t_goods.id,count(t_goods_attr.attr_id) as count,t_goods.state')
							->join('left join t_goods_attr on t_goods.id = t_goods_attr.goods_id')
							->group('t_goods.id')
							->where($where11)->having($count)->find();
							$data['attr_list'][$key]['catid'][$keys]['goods_id'] = $ress['id'];
							$data['attr_list'][$key]['catid'][$keys]['is_state'] =$ress['state'];
							if (!$data['attr_list'][$key]['catid'][$keys]['goods_id']){
								unset($data['attr_list'][$key]['catid'][$keys]);
							}
						}
						//print_r($data['attr_list'][$key]['catid']);
					}
					$ressss = array();
					
					foreach ($data['attr_list'][$key]['catid'] as $val){
						$ressss[] = $val;
					}
					//print_r($ressss);
					unset($data['attr_list'][$key]['catid']);
					$data['attr_list'][$key]['catid'] = $ressss;
				}
				//print_r($data['attr_list']);
				//exit;
				json('200','成功',$data);
			}else {
				json('400','该商品已下架');
			}
			
		}
		json('404','缺少参数 '.$datas);
	}
	
	//获取分类列表 //所有分类 二级显示 
	public function shopgroup(){
		$table = M('group');
		$data = $table->where('pid = 0')->select();
		if ($data){
			foreach ($data as $key=>$val){
				$data[$key]['catid'] = $table->where("pid = '{$val['id']}'")->select();
			}
			json('200','成功',$data);
		}else {
			json('400','暂无数据');
		}
	}
	
	//踩赞
	public function upper(){
		if (I('post.')){
			$where = $where1 = I('post.');
			if (!$where['uid']){
				json('400','登陆后才能执行此操作！');
			}
			unset($where['state']);
			$table = M('upper');
			$tab = M(I('post.type'));
			if ($table->where($where)->find()){
				json('400','不可重复操作');
			}else {
				$where1['addtime'] = time();
				if ($table->add($where1)){
					if (I('post.state') == 1){
						$tab->where("id = '{$where['pid']}'")->setInc('upper',1);
						json('200','成功');
					}elseif (I('post.state') == 2){
						$tab->where("id = '{$where['pid']}'")->setInc('lower',1);
						json('200','成功');
					}else {
						json('400','非法操作');
					}
				}else {
					json('400','操作失败');
				}
			}
		}
		json('404');
	}
	
	//收藏/取消
	public function cell(){
		$datas = check_param(array('pid','uid','type'));
		if(!$datas){
			$table = M('cell');
			$where = I('post.');
			if (!$where['uid']){
				json('400','登陆后才能执行此操作！');
			}
			if (!checkNum($where['pid']) || !checkNum($where['uid']) || checkNull($where['type'])){
				json('400','参数不合法');
			}
			if ($table->where($where)->find()){
				if ($table->where($where)->delete()){
					json('200','取消成功');
				}else {
					json('400','操作失败');
				}
			}else {
				$where['addtime']= time();
				if ($table->add($where)){
					json('200','收藏成功');
				}else {
					json('400','操作失败');
				}
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//商品详细信息
	public function goods_content(){
		$datas = check_param(array('id'));
		if(!$datas){
			$table = M('goods');
			$res = $table->field('pid')->find(I('post.id'));
			if ($res){
				$data = $table->field('content,spec,service')->where("id = '{$res['pid']}'")->find();
				json('200','成功',$data);
			}else {
				json('400','该商品不存在');
			}	
		}
		json('404','缺少参数 '.$datas);
	}
	
	//评价列表
	public function goods_comment_list(){
		$datas = check_param(array('id'));
		if(!$datas){
			$page = I('post.page') ? I('post.page') : 1;
			$pages = ($page - 1)*15;
			$type = I('post.type') ? I('post.type') : 1;
			$table = M('goods');
			$img = M('img');
			$comment = M('comment');
			$res = $table->field('pid')->find(I('post.id'));
			$data['comment_count'] = $comment->where("type = 'goods' and pid = 0 and typeid = '{$res['pid']}'")->count();
			$data['comment_count1'] = $comment->where("type = 'goods' and pid = 0 and typeid = '{$res['pid']}' and is_good = 1")->count();
			$data['comment_count2'] = $comment->where("type = 'goods' and pid = 0 and typeid = '{$res['pid']}' and is_good = 2")->count();
			$data['comment_count3'] = $comment->where("type = 'goods' and pid = 0 and typeid = '{$res['pid']}' and is_good = 3")->count();
			$data['comment_count4'] = $comment->field('t_img.id')
			->join('left join t_img on t_img.pid = t_comment.id and t_img.type = "comment"')
			->where("t_comment.type = 'goods' and t_comment.pid = 0 and t_comment.typeid = '{$res['pid']}'")->count();
			$where['t_comment.typeid'] = $res['pid'];
			$where['t_comment.type'] = 'goods';
			$where['t_comment.pid'] = 0;
			if ($type == 5){
				$data['img'] = $comment->field('t_img.simg')
				->join('left join t_img on t_img.pid = t_comment.id and t_img.type = "comment"')
				->where("t_comment.type = 'goods' and t_comment.pid = 0 and t_comment.typeid = '{$res['pid']}'")->select();
			}else {
				$order = M('order');
				$goods_attr = M('goods_attr');
				$where['t_comment.is_good'] = $type;
				$data['comment'] = $comment->field("t_comment.id,t_comment.uid,t_comment.upper,t_comment.mark,t_comment.goods_id,t_comment.username,t_comment.simg,t_comment.description,t_comment.addtime,t_comment.is_hide,t_order.addtime as buytime")
				->join('left join t_order on t_order.orderid = t_comment.orderid')
				->where($where)->order("t_comment.addtime desc")->limit($pages,15)->select();
				foreach ($data['comment'] as $key=>$val){
					$data['comment'][$key]['img'] = $img->where("pid = '{$val['id']}' and type = 'comment'")->getField('simg',true);
					$data['comment'][$key]['count'] = $comment->where("pid = '{$val['id']}'")->count();
					$data['comment'][$key]['attr'] = $goods_attr
					->join('left join t_attr on t_attr.id = t_goods_attr.attr_id')
					->where("t_goods_attr.goods_id = '{$val['goods_id']}'")->getField('title',true);
					$data['comment'][$key]['attr'] = implode(' ', $data['comment'][$key]['attr']);
				}
			}
			if ($data){
				json('200','成功',$data);
			}else {
				json('400','该商品不存在');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//回复列表
	public function comment_comment_list(){
		$datas = check_param(array('id'));
		if(!$datas){
			$page = I('post.page') ? I('post.page') : 1;
			$pages = ($page - 1)*15;
			$comment = M('comment');
			$id = I('post.id');
			$data = $comment->field("id,uid,username,simg,pid,description,addtime,is_hide")->where("pid = $id")->order("addtime desc")->limit($pages,15)->select();
			if ($data){
				json('200','成功',$data);
			}else {
				json('400','该商品不存在');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//购物车列表
	public function cart_list(){
		$datas = check_param(array('uid'));
		if(!$datas){
			$cart = new  \Org\Util\Cart();
			$data = $cart->getcart(I('post.uid'));
			if($data){
				json('200','成功',$data);
			}else {
				json('400','没有数据了');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//添加购物车
	public function addcart(){
		$datas = check_param(array('id','uid'));
		if(!$datas){
			$cart = new  \Org\Util\Cart();
			$data = $cart->addcart(I('post.id'), I('post.uid'),I('post.num'));
			if($data){
				json('200','成功');
			}else {
				json('400','失败');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//修改数量
	public function editcart(){
		$datas = check_param(array('id','num'));
		if(!$datas){
			$cart = new  \Org\Util\Cart();
			$data = $cart->editNum(I('post.id'), I('post.num'));
			if($data){
				json('200','成功');
			}else {
				json('400','失败');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//删除购物车
	public function delcart(){
		$datas = check_param(array('id'));
		if(!$datas){
			$cart = new  \Org\Util\Cart();
			$data = $cart->delItem(I('post.id'));
			if($data){
				json('200','成功');
			}else {
				json('400','重复操作');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//清空购物车
	public function clearcart(){
		$datas = check_param(array('uid'));
		if(!$datas){
			$cart = new  \Org\Util\Cart();
			$data = $cart->clear(I('post.uid'));
			if($data){
				json('200','成功');
			}else {
				json('400','重复操作');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//购物车数量
	public function cart_num(){
		$datas = check_param(array('uid'));
		if(!$datas){
			$cart = new  \Org\Util\Cart();
			$data['num'] = $cart->getNum(I('post.uid'));
			json('200','成功',$data);
		}
		json('404','缺少参数 '.$datas);
	}
	
	//是否为分销商
	public function is_vip(){
		$datas = check_param(array('id'));
		if(!$datas){
			$user = M('user');
			$id = I('post.id');
			$data['level'] = $user->where("id = $id")->getField('level');
			json('200','成功',$data);
		}
		json('404','缺少参数 '.$datas);
	}
	
	//申请分销商
	public function add_vip(){
		$datas = check_param(array('uid'));
		if(!$datas){
			$uid = I('post.uid');
			$base = M('base');
			$bases = $base->find();
			$order = M('order');
			$price = $order->where("uid = $uid and state > 1")->sum('price');
			if ($price < $bases['price']){
				json('400','您消费的金额不足，还不能申请分销商');
			}
			$user = M('user');
			$data = $user->where("id = $uid")->setField('level',0);
			if ($data){
				json('200','成功');
			}else {
				json('400','失败');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//可领取优惠券列表
	public function coupons_list(){
		$datas = check_param(array('pid'));
		if(!$datas){
			$page = I('post.page') ? I('post.page') : 1;
			$pages = ($page - 1)*15;
			if (I('post.pid')){
				$where['pid'] = I('post.pid');
			}
			$table = M('coupons');
			$where['state'] = 1;
			$where['stoptime'] = array('gt',time());
			$data = $table->field('id,title,sum,num,addtime,stoptime,man,jian')->where($where)->order('addtime desc')->limit($pages,15)->select();
			if ($data){
				json('200','成功',$data);
			}else {
				json('400','没有更多数据');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//领取优惠券
	public function add_coupons(){
		$datas = check_param(array('id','uid'));
		if(!$datas){
			$where['coupons_id'] = I('post.id');
			$where['uid'] = I('post.uid');
			$table = M('user_coupons');
			$coupons = M('coupons');
			$where['state'] = 1;
			if ($table->where($where)->find()){
				json('400','您已经领取过此优惠券');
			}
			$res = $coupons->find($where['coupons_id']);
			if ($res['num']){
				$where['pid'] = $res['pid'];
				$where['addtime'] = time();
				$where['stoptime'] = $res['stoptime'];
				$where['title'] = $res['title'];
				$where['man'] = $res['man'];
				$where['jian'] = $res['jian'];		
				if ($table->add($where)){
					$coupons->where("id = '{$where['coupons_id']}'")->setDec('num');
					json('200','成功');
				}else {
					json('400','失败');
				}
			}else {
				json('400','此优惠券已经抢光了');
			}		
		}
		json('404','缺少参数 '.$datas);
	}
	
	//我的优惠券列表
	public function my_coupons_list(){
		$datas = check_param(array('uid'));
		if(!$datas){
			$page = I('post.page') ? I('post.page') : 1;
			$pages = ($page - 1)*15;
			$where['state'] = I('post.state') ? I('post.state') : 1;
			$table = M('user_coupons');
			if ($where['state'] == 2){
				$where['state'] = 2;
			}elseif ($where['state'] == 3){
				$where['state'] = 1;
				$where['stoptime'] = array('lt',time());
			}else {
				$where['state'] = 1;
				$where['stoptime'] = array('gt',time());
			}		
			$data = $table->field('id,title,addtime,stoptime,pid,man,jian')->where($where)->order('addtime desc')->limit($pages,15)->select();
			if ($data){
				json('200','成功',$data);
			}else {
				json('400','没有更多数据');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//评价点赞
	public function comment_upper(){
		$datas = check_param(array('id'));
		if(!$datas){
			$id = I('post.id');
			$table = M('comment');
			if ($table->where("id = $id")->setInc('upper')){
				json('200','成功');
			}else {
				json('400','失败');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//首页产品
	public function index_goods(){
		$table = M('goods');
		$img = M('img');
		$page = I('post.page') ? I('post.page') : 1;
		$pages = ($page - 1)*15;
		$order = 'g.status desc,g.ord asc,t_goods.status desc,t_goods.addtime desc';
		$where['t_goods.pid'] = array('neq',0);
		$where['g.state'] = 2;
		$where['t_goods.state'] = 2;
		$data = $table->field('t_goods.id,t_goods.title,g.simg,g.groupid,g.subgroupid,t_goods.price,t_goods.prices,t_goods.sale')
		->join('left join t_goods as g on g.id = t_goods.pid')
		->where($where)->order($order)->limit($pages,15)->select();
		if ($data){
			$comment = M('comment');
			foreach ($data as $key=>$val){
				$data[$key]['comment'] = $comment->where("type = 'goods' and typeid = '{$val['id']}'")->count();
				$simg = $img->where("pid = '{$val['id']}' and type='goods'")->getField('simg');
				if ($simg){
					$data[$key]['simg'] = $simg;
				}
			}
			json('200','成功',$data);
		}else {
			json('400','没有商品了');
		}
	}
	
	//获取网站基本配置信息
	public function base_config(){
		$table = M('base');
		$data = $table->find();
		json('200','成功',$data);
	}
	
	//banner详情
	public function banner_info(){
		$datas = check_param(array('id'));
		if(!$datas){
			$table = M('banner');
			$data = $table->field('id,title,addtime,content')->find();
			if ($data){
				json('200','成功',$data);
			}else {
				json('400','失败');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//优惠券分类
	public function coupons_group(){
		$table = M('group');
		$data = $table->field('t_group.id,t_group.title,count(t_coupons.pid) as count')
		->join('left join t_coupons on t_coupons.pid = t_group.id')
		->group('t_group.id')
		->where("t_group.pid=0")->having('count > 0')->order('t_group.ord asc')->select();
		if ($data){
			json('200','成功',$data);
		}else {
			json('400','没有分类');
		}
	}
	
	//我收藏的商品
	public function cell_goods(){
		$datas = check_param(array('uid'));
		if(!$datas){
			$table = M('cell');
			$img = M('img');
			$page = I('post.page') ? I('post.page') : 1;
			$pages = ($page - 1)*15;
			$where['t_goods.pid'] = array('neq',0);
			$where['t_cell.uid'] = I('post.uid');
			$where['t_cell.type'] = 'goods';
			$data = $table->field('t_goods.id,t_goods.title,g.simg,g.groupid,g.subgroupid,t_goods.price,t_goods.prices,t_goods.sale,g.state,t_goods.state as state1')
			->join('left join t_goods on t_goods.id = t_cell.pid')
			->join('left join t_goods as g on g.id = t_goods.pid')
			->where($where)->order("t_cell.addtime desc")->limit($pages,15)->select();
			if ($data){
				$comment = M('comment');
				foreach ($data as $key=>$val){
					$data[$key]['comment'] = $comment->where("type = 'goods' and typeid = '{$val['id']}'")->count();
					$simg = $img->where("pid = '{$val['id']}' and type='goods'")->getField('simg');
					if ($simg){
						$data[$key]['simg'] = $simg;
					}
					if ($val['state'] == 2 && $val['state1'] == 2){
						$data[$key]['status'] = 2;
					}else {
						$data[$key]['status'] = 1;
					}
					unset($data[$key]['state']);
					unset($data[$key]['state1']);
				}
				json('200','成功',$data);
			}else {
				json('400','没有商品了');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//商品提问
	public function add_faq(){
		$datas = check_param(array('uid','description','goods_id'));
		if(!$datas){
			$where = I('post.');
			$goods = M('goods');
			$res = $goods->find($where['goods_id']);
			if (!$res){
				json('400','非法操作');
			}
			$table = M('faq');
			$where['addtime'] = time();
			$where['type'] = 'goods';
			$where['typeid'] = $res['pid'];
			if ($table->add($where)){
				json('200','成功');
			}else {
				json('400','失败');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//faq 列表
	public function faq_list(){
		$datas = check_param(array('id'));
		if(!$datas){
			$id = I('post.id');
			$page = I('post.page') ? I('post.page') : 1;
			$pages = ($page - 1)*15;
			$goods = M('goods');
			$res = $goods->find($id);
			if (!$res){
				json('400','非法操作');
			}
			$table = M('faq');
			$where['type'] = 'goods';
			$where['typeid'] = $res['pid'];
			$where['state'] = 2;
			$data = $table->field('id,description,content')->where($where)->order('addtime desc')->limit($pages,15)->select();
			if ($data){
				json('200','成功',$data);
			}else {
				json('400','没有数据');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//结算页面
	public function settlement(){
		$datas = check_param(array('ids','uid'));
		if(!$datas){
			$where = I('post.');
			$base = M('base');
			$bases = $base->field('man_ems,ems')->find(1);
			//获取地址
			$address = M('address');
			$data['address'] = $address->field('id,name,phone,city,address')->where("uid = '{$where['uid']}'")->order('state desc,id desc')->find();
			//获取商品
			$table = M('cart');
			$coupons = M('user_coupons');
			$where1['t_cart.id'] = array('in',$where['ids']);
			$goods = $table->field('t_goods.id,t_goods.title,g.simg,g.groupid,sum(t_goods.price*t_cart.num) as sum1,sum(t_goods.prices*t_cart.num) as sum2')
			->join('left join t_goods on t_goods.id = t_cart.goods_id')
			->join('left join t_goods as g on g.id = t_goods.pid')
			->group('g.groupid')
			->where($where1)->select();	
			$user = M('user');
			$users = $user->field('level')->find($where['uid']);
			$time = time();
			if ($users['level'] == 0){
				$data['sum'] = $table
				->join('left join t_goods on t_goods.id = t_cart.goods_id')
				->where($where1)->sum('t_goods.price*t_cart.num');
				$data['coupons'] = $coupons->field('id,title,addtime,stoptime,man,jian')->where("state = 1 and stoptime > $time and uid = '{$where['uid']}' and man <= '{$data['sum']}' and pid = 0")->select();
				foreach ($goods as $key=>$val){
					$coupons_info = $coupons->field('id,title,addtime,stoptime,man,jian')->where("state = 1 and stoptime > $time and uid = '{$where['uid']}' and man <= '{$val['sum1']}' and pid = '{$val['groupid']}'")->select();
					foreach ($coupons_info as $v){
						$data['coupons'][] = $v;
					}
				}
			}else {
				$data['sum'] = $table
				->join('left join t_goods on t_goods.id = t_cart.goods_id')
				->where($where1)->sum('t_goods.prices*t_cart.num');
				$data['coupons'] = $coupons->field('id,title,addtime,stoptime,man,jian')->where("state = 1 and stoptime > $time and uid = '{$where['uid']}' and man <= '{$data['sum']}' and pid = 0")->select();
				foreach ($goods as $key=>$val){
					$coupons_info = $coupons->field('id,title,addtime,stoptime,man,jian')->where("state = 1 and stoptime > $time and uid = '{$where['uid']}' and man <= '{$val['sum2']}' and pid = '{$val['groupid']}'")->select();
					foreach ($coupons_info as $v){
						$data['coupons'][] = $v;
					}
				}
			}
			if ($bases['man_ems'] < $data['sum']){
				$data['ems'] = 0;
			}else {
				$data['ems'] = $bases['ems'];
			}
			$data['ids'] = $where['ids'];
			json('200','成功',$data);
		}
		json('404','缺少参数 '.$datas);
	}
	
	//生成订单
	public function addorder(){
		$datas = check_param(array('ids','uid','address','coupons','ems','price'));
		if(!$datas){
			$where = I('post.');
			//获取商品
			$cart = M('cart');
			$user = M('user');
			$table = M('order');
			$where['orderid'] = $data['orderid'] = $where2['orderid'] = date('YmdHis').rand(100000,999999);
			$coupons = M('user_coupons');
			//$coup_price = $coupons->where("id = '{$where['coupons']}'")->getField('jian');
			$users = $user->field('level')->find($where['uid']);
			$where1['t_cart.id'] = array('in',$where['ids']);
			unset($where['ids']);
			$where['addtime'] = time();
			$where['type'] = 'goods';
			if ($table->add($where)){
				$detail = M('order_detail');		
				$goods = $cart->field('t_goods.id,t_goods.title,coalesce(t_img.simg,g.simg) as simg,t_goods.price,t_goods.prices,t_cart.num')
				->join('left join t_goods on t_goods.id = t_cart.goods_id')
				->join('left join t_goods as g on g.id = t_goods.pid')
				->join('left join t_img on t_img.pid = t_cart.goods_id and t_img.type="goods"')
				->group('t_cart.goods_id')
				->where($where1)->select();
				foreach ($goods as $val){
					$where2['type'] = 'goods';
					$where2['typeid'] = $val['id'];
					$where2['title'] = $data['title'] = $val['title'];
					$where2['simg'] = $val['simg'];
					$where2['num'] = $val['num'];
					if ($users['level'] == 0){
						$where2['price'] = $val['price'];
					}else {
						$where2['price'] = $val['prices'];
					}
					if (!$detail->add($where2)){
						json('400','购物清单出错');
					}	
				}
				if ($where['coupons']){
					$coupons->where("id = '{$where['coupons']}'")->setField('state',2);
				}		
				$data['price'] = $where['price'];
				$carts = new  \Org\Util\Cart();
				$carts->delItem(I('post.ids'));
				json('200','成功',$data);
			}else {
				json('400','失败');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//购物清单
	public function select_cart(){
		$datas = check_param(array('ids'));
		if(!$datas){
			$cart = new  \Org\Util\Cart();
			$data = $cart->select_cart(I('post.ids'));
			if($data){
				json('200','成功',$data);
			}else {
				json('400','没有数据了');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//支付宝回调
	public function alipay_notify_url(){
		$alipay = new  \Org\Util\Alipay();
		if($alipay->notify()) { //验证成功
			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			//请在这里加上商户的业务逻辑程序代
				
				
			//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
				
			//获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
				
			//商户订单号
				
			$out_trade_no = $_POST['out_trade_no'];
	
			//支付宝交易号
	
			$trade_no = $_POST['trade_no'];
	
			//交易状态
			$trade_status = $_POST['trade_status'];
				
			$order = M('order');
			if($_POST['trade_status'] == 'TRADE_FINISHED') {
				//判断该笔订单是否在商户网站中已经做过处理
				//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
				//请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
				//如果有做过处理，不执行商户的业务程序
	
				//注意：
				//退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
	
				//调试用，写文本函数记录程序运行情况是否正常
				//logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
			}
			else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
				//判断该笔订单是否在商户网站中已经做过处理
				$res = $order->where("orderid = $out_trade_no")->find();
				//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
				if ($res['state'] == 1){
					$where2['state'] = 2;
					$where2['account'] = $trade_no;
					if ($order->where("orderid = $out_trade_no")->save($where2)){
						
					}
				}
				//请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
				//如果有做过处理，不执行商户的业务程序
	
				//注意：
				//付款完成后，支付宝系统发送该交易状态通知
	
				//调试用，写文本函数记录程序运行情况是否正常
				//logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
			}
	
			//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
	
			echo "success";		//请不要修改或删除
				
			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		}
		else {
			//验证失败
			echo "fail";
	
			//调试用，写文本函数记录程序运行情况是否正常
			//logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
		}
	}
	
	public function wx_notify_url(){
		$data = file_get_contents( "php://input" );
		$data = read_xml( $data );
		$appid = $data['appid']['0']['#cdata-section'];		
		$mch_id = $data['mch_id']['0']['#cdata-section'];	
		if( $appid != C( 'wx_appid' ) || $mch_id != C( 'mch_id' )){
			json('400','参数不正确');		
		}		
		$code = $data['result_code']['0']['#cdata-section'];		
		if( $code != 'SUCCESS' ){
			json('400','支付失败');
		} 
		$order = M('order');
		$res = $order->where("orderid = '{$data['out_trade_no']['0']['#cdata-section']}'")->find();
		if ($res['state'] == 1){
			//价格判断
			//if ($res['price'] = )		
			$where['state'] = 2;
			$where['buytime'] = time();
			$where['payment'] = '微信支付';
			$where['account'] = $data['transaction_id']['0']['#cdata-section'];
			if ($order->where("id = '{$res['id']}'")->save($where)){
				$w['return_code'] = 'SUCCESS';
				arrtoxml($w);
				exit;
			}else {
				json('400','状态修改失败');
			}
		}else {
			json('400');
		}
	}
	
	//我的订单
	public function user_order(){
		$datas = check_param(array('uid'));
		if(!$datas){
			$table = M('order');
			$attr = M('goods_attr');
			$detail = M('order_detail');
			$shop = M('shop');
			$uid = I('post.uid');
			$page = I('post.page') ? I('post.page') : 1;
			$pages = ($page - 1)*15;
			$state = I('post.state') ? I('post.state') : 0;
			$where['t_order.uid'] = $uid;
			$where['t_order.del'] = 1;
			
			if ($state){
				if ($state == 4){
					$where['state'] = array('gt',3);
				}else {
					$where['state'] = $state;
				}
			}
			$data = $table->field('t_order.id,t_order.orderid,t_order.addtime,t_order.state,t_order.price,t_order.ems')
			->where($where)->order('t_order.id desc')->limit($pages,15)->select();
			foreach ($data as $key=>$val){
				$data[$key]['goods'] = $detail->field('typeid as id,title,simg,num,price,state')->where("orderid = '{$val['orderid']}'")->select();
				foreach ($data[$key]['goods'] as $k=>$v){
					$arr1 = $attr
					->join('left join t_attr on t_attr.id = t_goods_attr.attr_id')
					->where("t_goods_attr.goods_id = '{$v['id']}'")->getField("title",true);
					$data[$key]['goods'][$k]['attr'] = implode(' ', $arr1) ? implode(' ', $arr1) : '';
				}
			}
			if ($data){
				json('200','成功',$data);
			}else {
				json('400','没有数据');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//取消订单
	public function cancel_order(){
		$datas = check_param(array('id'));
		if(!$datas){
			$table = M('order');
			$id = I('post.id');
			if ($table->where("id = $id")->delete()){		
				json('200','成功');
			}else {
				json('400','非法操作');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//删除订单
	public function del_order(){
		$datas = check_param(array('id'));
		if(!$datas){
			$table = M('order');
			$id = I('post.id');
			if ($table->where("id = $id")->setField('del',2)){
				json('200','成功');
			}else {
				json('400','非法操作');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//获取热搜关键字
	public function hot_word(){
		$table = M('base');
		$res = $table->where("id = 1")->getField('keyword');
		if ($res){
			$data['word'] = explode(' ', $res);
			json('200','成功',$data);
		}else {
			json('400','没有热搜词语');
		}
	}
	
	//获取禁用词语
	public function ban_word(){
		$table = M('base');
		$res = $table->where("id = 1")->getField('ban');
		if ($res){
			$data['word'] = explode(' ', $res);
			json('200','成功',$data);
		}else {
			json('400','没有热搜词语');
		}
	}
	
	//发现列表
	public function news_list(){
		$table = M('news');
		$page = I('post.page') ? I('post.page') : 1;
		$pages = ($page -1)*15;
		$data = $table->field('t_news.id,t_news.title,t_news.simg,t_news.upper,t_news.isred,t_news.addtime,count(t_comment.id) as count')
		->join("left join t_comment on t_comment.typeid = t_news.id and t_comment.type = 'news'")
		->group('t_news.id')
		->order('t_news.isred desc,t_news.addtime desc')->limit($pages,15)->select();
		if ($data){
			json('200','成功',$data);
		}else {
			json('400','没有数据');
		}
	}
	
	//发现详情
	public function news_info(){
		$datas = check_param(array('id'));
		if(!$datas){
			$table = M('news');
			$goods = M('goods');
			$img = M('img');
			$id = I('post.id');
			$data = $table->field('t_news.id,t_news.title,t_news.simg,t_news.upper,t_news.origin,t_news.isred,t_news.addtime,count(t_comment.id) as count,t_news.description,t_news.content')
			->join("left join t_comment on t_comment.typeid = t_news.id and t_comment.type = 'news'")
			->where("t_news.id = $id")->find();
			if ($data){
				if ($data['description']){
					$where['t_goods.id'] = array('in',explode(' ', $data['description']));
					$data['goods'] = $goods->field('t_goods.id,t_goods.title,g.simg,g.groupid,g.subgroupid,t_goods.price,t_goods.prices')
					->join('left join t_goods as g on g.id = t_goods.pid')
					->join('left join t_goods_attr on t_goods.id = t_goods_attr.goods_id')
					//->join("left join t_img on t_img.pid = t_goods.id and t_img.type = 'goods'")
					->group('t_goods.id')
					->where($where)->select();
					if ($data['goods']){
						$comment = M('comment');
						foreach ($data['goods'] as $key=>$val){
							$data['goods'][$key]['comment'] = $comment->where("type = 'goods' and typeid = '{$val['id']}'")->count();
							$simg = $img->where("pid = '{$val['id']}' and type='goods'")->getField('simg');
							if ($simg){
								$data['goods'][$key]['simg'] = $simg;
							}
						}
					}
				}else {
					$data['goods'] = array();
				}
				json('200','成功',$data);
			}else {
				json('400','没有数据');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//发现评论
	public function news_comment(){
		$datas = check_param(array('id'));
		if(!$datas){
			$table = M('comment');
			$id = I('post.id');
			$page = I('post.page') ? I('post.page') : 1;
			$pages = ($page - 1)*15;
			$data = $table->field('id,description,uid,simg,username,addtime')->where("typeid = $id and type = 'news'")->order('addtime desc')->limit($pages,15)->select();
			if ($data){
				json('200','成功',$data);
			}else {
				json('400','没有数据');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//评论发现
	public function comment_news(){
		$datas = check_param(array('uid','typeid','description'));
		if(!$datas){
			$table = M('comment');
			$where = I('post.');
			$user = M('user');
			$users = $user->field('username,simg')->find(I('post.uid'));
			$where['username'] = $users['username'];
			$where['simg'] = $users['simg'];
			$where['addtime'] = time();
			$where['type'] = 'news';
			$where['id'] = $table->add($where);
			if ($where['id']){
				json('200','成功',$where);
			}else {
				json('400','没有数据');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//发现点赞
	public function upper_news(){
		$datas = check_param(array('id'));
		if(!$datas){
			$table = M('news');
			$id = I('post.id');
			$data = $table->where("id = $id")->setInc('upper');
			if ($data){
				json('200','成功');
			}else {
				json('400','没有数据');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//订单详情
	public function order_info(){
		$datas = check_param(array('id'));
		if(!$datas){
			$table = M('order');
			$attr = M('goods_attr');
			$detail = M('order_detail');
			$id = I('post.id');
			$data = $table->field('id,orderid,addtime,state,price,ems,address,coupons,express,express_num,payment')
			->where("id = $id")->find();
			if ($data['coupons']){
				$coupons = M('user_coupons');
				$data['coupons'] = $coupons->field('title,man,jian')->find($data['coupons']);
			}
			if ($data['address']){
				$address = M('address');
				$data['address'] = $address->field('name,phone,city,address')->find($data['address']);
			}
			$data['goods'] = $detail->field('typeid as id,title,simg,num,price,state')->where("orderid = '{$data['orderid']}'")->select();
			foreach ($data['goods'] as $k=>$v){
				$arr1 = $attr
				->join('left join t_attr on t_attr.id = t_goods_attr.attr_id')
				->where("t_goods_attr.goods_id = '{$v['id']}'")->getField("title",true);
				$data['goods'][$k]['attr'] = implode(' ', $arr1) ? implode(' ', $arr1) : '';
			}
			if ($data){
				json('200','成功',$data);
			}else {
				json('400','没有数据');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//微信回调
	public function weixin(){
		$datas = check_param(array('orderid'));
		if(!$datas){
			$table = M('order');
			$id = I('post.orderid');
			$res = $table->where("orderid = $id")->setField('state',2);
			json('200','成功');
		}
		json('404','缺少参数 '.$datas);
	}
	
	//确认收货
	public function  finish_order(){
		$datas = check_param(array('id'));
		if(!$datas){
			$table = M('order');
			$base = M('base');
			$user = M('user');
			$message = M('message');
			$bases = $base->field('two_distribution,three_distribution')->find(1);
			$id = I('post.id');
			$data = $table->field('state,status,uid,price')->find($id);
			if ($data['state'] == 3){
				json('400','重复操作');
			}
			if ($data['state'] == 2 && $data['status'] == 2){
				$where['state'] = 3;
				$where['finishtime'] = time();
				$users = $user->where("id = '{$data['uid']}'")->find();
				if ($users['pid']){
					$where['three_uid'] = $users['pid'];
					$where['three_distribution'] = round($data['price']*$bases['three_distribution']/100,2);
					$users2 = $user->where("id = '{$users['pid']}'")->find();
					if ($users2['pid']){
						$where['two_uid'] = $users2['pid'];
						$where['two_distribution'] = round($data['price']*$bases['three_distribution']/100,2);
						$users3 = $user->where("id = '{$users2['pid']}'")->find();
					}
				}
				$res = $table->where("id = $id")->setField($where);
				if ($res){
					//返利
					if ($where['three_distribution']){
						if ($user->where("id = '{$users['pid']}'")->setInc('money',$where['three_distribution'])){
							$where1['uid'] = $users['pid'];
							$where1['type'] = 3;
							$where1['addtime'] = time();
							$where1['title'] = '返利通知';
							$where1['description'] = '会员 '.$users['id'].' 消费 '.$data['price'].' 元，您获得直接分销返利 '.$where['three_distribution'].' 元';
							if($message->add($where1)){
								if ($users2['jpushid']){
									$jpushid[] = $users2['jpushid'];
									$jpush = new \Org\Util\Jpush($this->app_key,$this->master_secret);
									$array['type'] = '3';
									$content = '收到一条返利通知';
									if ($jpushid){
										$jpush->push($jpushid, $this->title,$content,$array);
									}
								}
								if ($where['two_distribution']){
									if ($user->where("id = '{$users2['pid']}'")->setInc('money',$where['two_distribution'])){
										$where2['uid'] = $users2['pid'];
										$where2['type'] = 3;
										$where2['addtime'] = time();
										$where2['title'] = '返利通知';
										$where2['description'] = '会员 '.$users['id'].' 消费 '.$data['price'].' 元，您获得间接分销返利 '.$where['two_distribution'].' 元';
										if($message->add($where2)){
											if ($users3['jpushid']){
												$jpushid[] = $users3['jpushid'];
												$jpush = new \Org\Util\Jpush($this->app_key,$this->master_secret);
												$array['type'] = '3';
												$content = '收到一条返利通知';
												if ($jpushid){
													$jpush->push($jpushid, $this->title,$content,$array);
												}
											}
										}else {
											json('400','间接返利消息推送失败');
										}
									}
								}
							}else {
								json('400','直接返利消息推送失败');
							}
						}
					}	
					json('200','成功');
				}else {
					json('400','失败');
				}
			}else {
				json('400','非法操作');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//获取消息数量
	public function message_num(){
		$datas = check_param(array('uid'));
		if(!$datas){
			$where = $where1 = I('post.');
			$table = M('message');
			
			$where['state'] = 1; 
			$res['sum_num'] = $table->where($where)->count();
			$where['type'] = $where1['type'] = 1;
			$res['type1_num'] = $table->where($where)->count();
			$res['type1_message'] = $table->field('id,title,description,addtime,pid')->where($where1)->order('addtime desc')->find() ? $table->field('id,title,description,addtime,pid')->where($where1)->order('addtime desc')->find() : '';
			$where['type'] = $where1['type'] = 2;
			$res['type2_num'] = $table->where($where)->count();
			$res['type2_message'] = $table->field('id,title,description,addtime,pid')->where($where1)->order('addtime desc')->find() ? $table->field('id,title,description,addtime,pid')->where($where1)->order('addtime desc')->find() : '';
			if ($res['type2_message']){
				$notice = M('notice');
				$ress = $notice->field("title,description")->find($res['type2_message']['pid']);
				$res['type2_message']['title'] = $ress['title'];
				$res['type2_message']['description'] = $ress['description'];
			}
			$where['type'] = $where1['type'] = 3;
			$res['type3_num'] = $table->where($where)->count();
			$res['type3_message'] = $table->field('id,title,description,addtime,pid')->where($where1)->order('addtime desc')->find() ? $table->field('id,title,description,addtime,pid')->where($where1)->order('addtime desc')->find() : '';
			json('200','成功',$res);
		}
		json('404','缺少参数 '.$datas);
	}
	
	//获取列表
	public function message_list(){
		$datas = check_param(array('uid','type'));
		if(!$datas){
			$where = I('post.');
			$table = M('message');
			$page = I('post.page') ? I('post.page') : 1;
			$pages = ($page - 1)*15; 
			$data = $table->field("id,title,description,pid,type,state,addtime")->where($where)->order('addtime desc')->limit($pages,15)->select();
			if ($where['type'] == 2){
				foreach ($data as $key=>$val){
					$notice = M('notice');
					$res = $notice->field("title,description")->find($val['pid']);
					if ($res){
						$data[$key]['title'] = $res['title'];
						$data[$key]['description'] = $res['description'];				
					}
				}
			}
			if ($data){
				json('200','成功',$data);
			}else {
				json('400','没有数据');
			}	
		}
		json('404','缺少参数 '.$datas);
	}
	
	//消息详情
	public function message_info(){
		$datas = check_param(array('id'));
		if(!$datas){
			$id = I('post.id');
			$table = M('message');
			$data = $table->field("id,title,description,pid,type,state,addtime")->find($id);
			if ($data['type'] == 2 && $data['pid'] != 0){
				$notice = M('notice');
				$res = $notice->field("title,content")->find($data['pid']);
				$data['title'] = $res['title'];
				$data['description'] = $res['content'];
			}
			if ($data){
				if ($data['state'] == 1){
					$table->where("id = $id")->setField("state",2);
				}
				json('200','成功',$data);
			}else {
				json('400','消息已删除');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//全部标为已读
	public function message_state(){
		$datas = check_param(array('uid','type'));
		if(!$datas){
			$where = I('post.');
			$table = M('message');
			if ($table->where($where)->setField('state',2)){
				json('200','成功');
			}else {
				json('400','失败');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//删除消息接口
	public function del_message(){
		$datas = check_param(array('id'));
		if(!$datas){
			$where = I('post.');
			$table = M('message');
			if ($table->delete($where['id'])){
				json('200','成功');
			}else {
				json('400','失败');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//我的
	public function my_info(){
		$datas = check_param(array('uid'));
		if(!$datas){
			$table = M('order');
			$user = M('user');
			$where['del'] = 1;
			$where['uid'] = I('post.uid');
			$data['sum_num'] = $table->where($where)->count();
			$where['state'] = 1;
			$data['type1_num'] = $table->where($where)->count();
			$where['state'] = 2;
			$data['type2_num'] = $table->where($where)->count();
			$where['state'] = 3;
			$data['type3_num'] = $table->where($where)->count();
			$data['money'] = $user->where("id = '{$where['uid']}'")->getField('money');
			//$data['distribution'] = $table->where("three_uid = '{$where['uid']}' or two_uid = '{$where['uid']}'")->sum("IF(three_uid = '{$where['uid']}',three_distribution,two_distribution)");
			$data['distribution'] = $table->where("three_uid = '{$where['uid']}'")->sum("three_distribution");
			$data['distribution'] += $table->where("two_uid = '{$where['uid']}'")->sum("two_distribution");
			$data['distribution'] = number_format($data['distribution'],2);
			if ($data){
				json('200','成功',$data);
			}else {
				json('400','失败');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//我的分销
	public function my_distribution(){
		$datas = check_param(array('uid'));
		if(!$datas){
			$table = M('order');
			$user = M('user');
			//php获取昨日起始时间戳和结束时间戳
			$beginYesterday=mktime(0,0,0,date('m'),date('d')-1,date('Y'));
			$endYesterday=mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
			
			$where['uid'] = I('post.uid');
			$data['money'] = $user->where("id = '{$where['uid']}'")->getField('money');
			//$data['distribution'] = $table->where("three_uid = '{$where['uid']}' or two_uid = '{$where['uid']}'")->sum("IF(three_uid = '{$where['uid']}',three_distribution,two_distribution)");
			//总收入
			$data['distribution'] = $table->where("three_uid = '{$where['uid']}'")->sum("three_distribution");
			$data['distribution'] += $table->where("two_uid = '{$where['uid']}'")->sum("two_distribution");
			$data['distribution'] = number_format($data['distribution'],2);
			//昨日收入
			$data['yesterday_distribution'] = $table->where("three_uid = '{$where['uid']}' and finishtime >= $beginYesterday and finishtime <= $endYesterday")->sum("three_distribution");
			$data['yesterday_distribution'] += $table->where("two_uid = '{$where['uid']}' and finishtime >= $beginYesterday and finishtime <= $endYesterday")->sum("two_distribution");
			$data['yesterday_distribution'] = number_format($data['yesterday_distribution'],2);
			//二级分销
			$res2 = $user->where("pid = '{$where['uid']}'")->getField('id',true);
			$data['two_distribution'] = count($res2);
			$where2['uid'] = array('in',$res2);
			$where2['state'] = 3;
			if ($res2){
				$data['two_distribution_order'] = $table->where($where2)->count();
				//三级分销
				$where1['pid'] = array('in',$res2);
				$res3 = $user->where($where1)->getField('id',true);
				if ($res3){
					$data['three_distribution'] = count($res3);
					$where3['uid'] = array('in',$res3);
					$where3['state'] = 3;
					$data['three_distribution_order'] = $table->where($where3)->count();
				}else {
					$data['three_distribution'] = '0';
					$data['three_distribution_order'] = '0';
				}			
			}else {
				$data['two_distribution_order'] = '0';
				$data['three_distribution'] = '0';
				$data['three_distribution_order'] = '0';
			}

			if ($data){
				json('200','成功',$data);
			}else {
				json('400','失败');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//返利记录
	public function distribution_list(){

		$datas = check_param(array('uid','type'));
		if(!$datas){
			$table = M('order');
			$user = M('user');
			$page = I('post.page') ? I('post.page') : 1;
			$pages = ($page - 1)*15;
			$id = I('post.uid');
			//php获取本周起始时间戳和结束时间戳
    		$beginLastweek=mktime(0,0,0,date('m'),date('d')-(date('w') ? date('w') : 7)+1,date('Y'));
    		$endLastweek=mktime(23,59,59,date('m'),date('d')-(date('w') ? date('w') : 7)+7,date('Y'));
    		//php获取本月起始时间戳和结束时间戳
    		$beginThismonth=mktime(0,0,0,date('m'),1,date('Y'));
    		$endThismonth=mktime(23,59,59,date('m'),date('t'),date('Y'));
			if (I('post.type') == 2){
				$where['finishtime'] = $where1['finishtime'] = $where2['finishtime'] = array(array('egt',$beginLastweek),array('elt',$endLastweek));
			}elseif (I('post.type') == 3) {
				$where['finishtime'] = $where1['finishtime'] = $where2['finishtime'] = array(array('egt',$beginThismonth),array('elt',$endThismonth));
			}		
			$where['_string'] = "three_uid = $id or two_uid = $id";
			$where1['three_uid'] = $id;
			$where2['two_id'] = $id;
			$data['distribution_list'] = $table->field("IF(three_uid = '{$id}',three_distribution,two_distribution) as distribution,finishtime")->where($where)->order('finishtime desc')->limit($pages,15)->select();
			$data['distribution'] = $table->where($where1)->sum("three_distribution");
			$data['distribution'] += $table->where($where2)->sum("two_distribution");
			$data['distribution'] = number_format($data['distribution'],2);
			if ($data){
				json('200','成功',$data);
			}else {
				json('400','失败');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//添加银行卡
	public function add_bank(){	
		$datas = check_param(array('uid','title','phone','banknum','banktype'));
		if(!$datas){
			$table = M('bank');
			$where = I('post.');
			$where['addtime'] = time();
			if ($table->add($where)){
				json('200','成功');
			}else {
				json('400','失败');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//删除银行卡
	public function del_bank(){
		$datas = check_param(array('id'));
		if(!$datas){
			$table = M('bank');
			if ($table->delete(I('post.id'))){
				json('200','成功');
			}else {
				json('400','失败');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//银行卡列表
	public function bank_list(){
		$datas = check_param(array('uid'));
		if(!$datas){
			$table = M('bank');
			$data = $table->field("id,title,phone,banknum,banktype")->select();
			if ($data){
				json('200','成功',$data);
			}else {
				json('400','失败');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//提现限制
	public function take_limit(){
		$datas = check_param(array('uid'));
		if(!$datas){
			$id = I('post.uid');
			$data['min_price'] = M('base')->getField('take_price');
			$data['money'] = M('user')->where("id = $id")->getField('money');
			json('200','成功',$data);
		}
		json('404','缺少参数 '.$datas);
	}
	
	//申请提现
	public function take(){
		$datas = check_param(array('uid','id','price'));
		if(!$datas){
			$table = M('take');
			$take_price = M('base')->getField('take_price');
			$where['price'] = I('post.price');
			if ($take_price > $where['price']){
				json('400','提现最小金额为 '.$take_price.' 元');
			}		
			$where['uid'] = I('post.uid');
			$money = M('user')->where("id = '{$where['uid']}'")->getField('money');
			if ($where['price'] > $money){
				json('400','超出可提现金额 '.$money.' 元');
			}
			$res = M('bank')->find(I('post.id'));
			$where['title'] = $res['title'];
			$where['phone'] = $res['phone'];
			$where['banktype'] = $res['banktype'];
			$where['banknum'] = $res['banknum'];
			$where['addtime'] = time();
			if ($table->add($where)){
				M('user')->where("id = '{$where['uid']}'")->SetDec('money',$where['price']);
				json('200','成功');
			}else {
				json('400','失败');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//提现记录
	public function take_list(){
		$datas = check_param(array('uid'));
		if(!$datas){
			$table = M('take');
			$id = I('post.uid');
			$page = I('post.page') ? I('post.page') : 1;
			$pages = ($page - 1)*15;
			$data = $table->field("id,title,phone,banktype,banknum,price,state,finishtime,description,addtime")->where("uid = $id")->order('addtime desc')->limit($pages,15)->select();
			if ($data){
				json('200','成功',$data);
			}else {
				json('400','失败');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	//分销二维码
	public function erweima(){
		vendor("phpqrcode.phpqrcode");
		$data = 'http://baidu.com';
		// 纠错级别：L、M、Q、H
		$level = 'L';
		// 点的大小：1到10,用于手机端4就可以了
		$size = 10;
		// 下面注释了把二维码图片保存到本地的代码,如果要保存图片,用$fileName替换第二个参数false
		//$path = "images/";
		// 生成的文件名
		//$fileName = $path.$size.'.png';
		\QRcode::png($data, false, $level, $size);
	}
	
	//首页广告位
	public function index_ads(){
		$table = M('ads1');
		$data = $table->field("id,title,type")->where("pid = 0")->order('ord asc,addtime desc')->limit(8)->select();
		foreach ($data as $key=>$val){
			$data[$key]['simg'] = $table->field("id,title,simg,goods_id")->where("pid = '{$val['id']}'")->select();
		}
		if ($data){
			json('200','成功',$data);
		}else {
			json('400','失败');
		}
	}
	
	//广告位详情
	public function ads_info(){
		$datas = check_param(array('id'));
		if(!$datas){
			$table = M('ads1');
			$data = $table->field("id,content")->find(I('post.id'));
			if ($data){
				json('200','成功',$data);
			}else {
				json('400','失败');
			}
		}
		json('404','缺少参数 '.$datas);
	}
	
	
	
	
	
	
	
	
	
}