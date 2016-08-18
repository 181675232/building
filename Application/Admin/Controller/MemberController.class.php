<?php
namespace Admin\Controller;

class MemberController extends CommonController {
	
	public function index(){
		
		$Table = M('member'); // 实例化User对象
		
		//接收查询数据
		if (I('get.keyword')){
			$keyword = I('get.keyword');
			$data['name'] = array('like',"%{$keyword}%");
			$data['phone'] = array('like',"%{$keyword}%");
			$data['_logic'] = 'or';
		}elseif (I('get.verify')){
			$data = I('get.');
			$this->assign('verify',I('get.verify'));
		}
		if ($_SESSION['level'] != 0){
			switch ($_SESSION['level']){
				case  1:
					$data['provinceid'] = $_SESSION['provinceid'];
					break;
				case  2:
					$data['cityid'] = $_SESSION['cityid'];
					break;
				case  3:
					$data['areaid'] = $_SESSION['areaid'];
					break;
			}
		}
		$count      = $Table->where($data)->count();// 查询满足要求的总记录数
		$Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show       = $Page->show();// 分页显示输出
		// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
		$res = $Table->where($data)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
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
	
	public function add(){		
		if (IS_POST){
			$user = M('member');
			$where = I('post.');
			if ($where['password'] != $where['password1']){
				alertBack('两次密码不一致！');
			}
			$where['addtime'] = time();
			$where['password'] = md5($where['password']);
			unset($where['password1']);
			$data = $user->add($where);
			if ($data){
				$shop = M('shop');
				$shop->where("id = '{$where['pid']}'")->setField("uid",$data);
				alertBack('添加成功！');
			}else {
				$this->error('添加失败！');
			}
		}
		if (I('get.pid')){
			$this->assign('pid',I('get.pid'));
		}
		$this->display();
	}
	
	public function edit(){
		$id = I('get.id');
		if (IS_POST){
			$table = M('member');
			$where = I('post.');
			unset($where['addtime']);
			if ($table->save($where)){
				alertBack('修改成功！');
			}else {
				$this->error('没有任何修改！');
			}
		}
		$table = M('member');
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
		$table = M('member');
		$data = $table->delete($post);
		if ($data){
			echo '删除成功！';
		}else {
			echo '删除失败！';
		}
	}
	
	public function ajax(){
		if (!empty($_POST['param'])){
			$user = M('member');
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