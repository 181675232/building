<?php
namespace Admin\Controller;

class AdsController extends CommonController {
	
	public function index(){
		
		$table = M('ads'); // 实例化User对象
		
		//接收查询数据
		if (I('get.keyword')){
			$keyword = I('get.keyword');
			$data['title'] = array('like',"%{$keyword}%");
			$table = $table->where($data);
		}elseif (I('get.verify')){
			$data = I('get.');
			$table = $table->where($data);
			$this->assign('verify',I('get.verify'));
		}
		$count      = $table->count();// 查询满足要求的总记录数
		$Page       = new \Think\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show       = $Page->show();// 分页显示输出
		// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
		$res = $table->where($data)	->order('istop desc,isred desc,ord asc,addtime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('data',$res);// 赋值数据集
		$this->assign('page',$show);// 赋值分页输出
		$this->display(); // 输出模板			
	}
	
	public function add(){
		if (IS_POST){
			$table = M('ads');
			$where = I('post.');
			if (!$where['simg']){
				alertBack('图片必须上传');
			}
			if ($where['content']){
				$where['content'] = stripslashes(htmlspecialchars_decode($where['content']));
			}
			$where['addtime'] = time();
			if ($table->add($where)){
				alertLocation('添加成功！', '/Admin/Ads');
			}else {
				$this->error('添加失败！');
			}
			
		}
		$this->display();
	}
	
	public function edit(){
		$id = I('get.id');
		if (IS_POST){
			$table = M('ads');
			$data = I('post.');
			if ($data['content']){
				$data['content'] = stripslashes(htmlspecialchars_decode($data['content']));
			}
			if ($table->save($data)){
				alertBack('修改成功！');
			}else {
				$this->error('没有任何修改！');
			}				
		}
		$table = M('ads');
		$data = $table->where("id = $id")->find();
		$this->assign($data);
		$this->display();
	}
	
	public function state(){
		$data = I('get.');			
		$table = M('ads');
		if ($table->save($data)){
			$this->redirect("/Admin/Activity");
		}else {
			$this->error('没有任何修改！');
		}
	}
	
	public function delete(){		
		$post = implode(',',$_POST['id']);	
		$table = M('ads');
		$data = $table->delete($post);
		if ($data){
			echo '删除成功！';
		}else {
			echo '删除失败！';
		}
	}
	
	public function ajaxstate(){
		$data = I('get.');
		$table = M('ads');
		if ($table->save($data)){
			echo 1;
		}else {
			echo 0;
		}
	}
	
	public function ajax(){
		if (!empty($_POST['param'])){
			$table = M('ads');
			$data[$_POST['name']] = $_POST['param'];
			$return = $table->where($data)->find();
			if ($return){
				echo '手机号已存在！';
			}else {
				echo 'y';
			}
		}
	}
	
} 