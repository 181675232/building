<?php
namespace Admin\Controller;

class ProvideruserController extends CommonController {
	
	public function index(){
		
		$Table = M('provider_user'); // 实例化User对象
		
		//接收查询数据
		if (I('get.keyword')){
			$keyword = I('get.keyword');
			$data['provider_user.NAME'] = array('like',"%{$keyword}%");
			$Table = $Table->where($data);		
		}elseif (I('get.verify')){
			$data = I('get.');
			$Table = $Table->where($data);
			$this->assign('verify',I('get.verify'));
		}
		$count      = $Table->count();// 查询满足要求的总记录数
		$Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show       = $Page->show();// 分页显示输出
		// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
		$res = $Table->field('provider_user.*,provider.NAME as title')
		->join('left join provider on provider.ID = provider_user.PROVIDER_ID')
		->where($data)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('data',$res);// 赋值数据集
		$this->assign('page',$show);// 赋值分页输出
		$this->display(); // 输出模板			
	}
	
//	public function show(){
//	
//		$User = M('User'); // 实例化User对象	
//		//接收查询数据
//		if (IS_POST){
//			$post = I('post.');
//			$data['nickname'] = $post['keyword'];
//			$User = $User->where($data);
//		}
//		$count      = $User->where('verify = 1')->count();// 查询满足要求的总记录数
//		$Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数(25)
//		$show       = $Page->show();// 分页显示输出
//		// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
//		$res = $User->where($data)->where('verify = 1')->order('last_login_ts')->limit($Page->firstRow.','.$Page->listRows)->select();
//		$this->assign('data',$res);// 赋值数据集
//		$this->assign('page',$show);// 赋值分页输出
//		$this->display(); // 输出模板
//	}
	
//	public function add(){
//		if (IS_POST){
//			$user = D('User');
//			if ($user->create()){
//				if ($user->add()){
//					alertLocation('添加会员成功！', '/Admin/User');
//				}else {
//					$this->error('添加失败！');
//				}
//			}else {
//				$this->error($user->getError());
//			}
//			
//		}
//		$this->display();
//	}
	
	public function edit(){
		$id = I('get.id');
		if (IS_POST){
			$table = D('provider_user');
			if ($table->create()){
				if ($table->save()){
					alertBack('修改成功！');
				}else {
					$this->error('没有任何修改！');
				}
			}else {
				$this->error($table->getError());
			}
				
		}
		$table = M('provider_user');
		$data = $table->where("id = $id")->find();
		$this->assign($data);
		$this->display();
	}
	
//	public function state(){
//		$data = I('get.');			
//		$user = M('User');
//
//		if ($user->save($data)){
//			$this->redirect("/Admin/User/show");
//		}else {
//			$this->error('没有任何修改！');
//		}
//	}

	
	public function delete(){		
		$post = implode(',',$_POST['id']);	
		$table = M('provider_user');
		$data = $table->delete($post);
		if ($data){
			echo '删除成功！';
		}else {
			echo '删除失败！';
		}
	}
	
	public function ajax(){
		if (!empty($_POST['param'])){
			$user = M('Owner');
			$data[$_POST['name']] = $_POST['param'];
			$return = $user->where($data)->find();
			if ($return){
				echo '手机号已存在！';
			}else {
				echo 'y';
			}
		}
	}
	
} 