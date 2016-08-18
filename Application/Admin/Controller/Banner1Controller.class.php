<?php
namespace Admin\Controller;

class Banner1Controller extends CommonController {
	
	public function index(){
		
		$table = M('banner'); // 实例化User对象
		
		//接收查询数据
		if (I('get.keyword')){
			$keyword = I('get.keyword');
			$data['title'] = array('like',"%{$keyword}%");
		}elseif (I('get.verify')){
			$data = I('get.');
			$this->assign('verify',I('get.verify'));
		}
		$count      = $table->where($data)->count();// 查询满足要求的总记录数
		$Page       = new \Think\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show       = $Page->show();// 分页显示输出
		// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
		$res = $table->where($data)->order('ord asc,id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('data',$res);// 赋值数据集
		$this->assign('page',$show);// 赋值分页输出
		$this->display(); // 输出模板
	}
	
	public function add(){
		if (IS_POST){
			$table = M('banner');
			$where = I('post.');
			if (is_file('.'.$where['simg'])){
				$oss = new \Org\Util\Oss($this->AccessKeyID, $this->AccessKeySecret, $this->url_nei);
				$simg = substr(dirname($where['simg']), -10).'/'.basename($where['simg']);
				if ($oss->uploadFile('jolly',$simg)){
					unlink('./Public/upfile/'.$simg);
					//unlink('./Public/thumb/'.$simg);
					$where['simg'] = $this->url.'/shop/'.$simg;
					//$where['simgdesc'] = $this->url.'/thumb/'.$simg;
				}else {
					alertBack('上传失败');
				}
			}
			$where['addtime'] = time();
			if ($table->add($where)){
				alertLocation('添加成功！', '/Admin/Banner1');
			}else {
				$this->error('添加失败！');
			}		
		}
		$this->display();
	}
	
	public function edit(){
		$id = I('get.id');
		$table = M('banner');
		if (IS_POST){
			$where = I('post.');
			if (is_file('.'.$where['simg'])){
				$oss = new \Org\Util\Oss($this->AccessKeyID, $this->AccessKeySecret, $this->url_nei);
				$simg = substr(dirname($where['simg']), -10).'/'.basename($where['simg']);
				if ($oss->uploadFile('jolly',$simg)){
					unlink('./Public/upfile/'.$simg);
					//unlink('./Public/thumb/'.$simg);
					$where['simg'] = $this->url.'/shop/'.$simg;
					//$where['simgdesc'] = $this->url.'/thumb/'.$simg;
				}else {
					alertBack('上传失败');
				}
			}
			if ($table->save($where)){
				alertReplace('修改成功！');
			}else {
				$this->error('没有任何修改！');
			}			
		}
		$data = $table->where("id = $id")->find();
		$this->assign($data);
		$this->display('');
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

	public function ajaxstate(){
		$data = I('get.');
		$table = M('banner');
		if ($table->save($data)){
			echo 1;
		}else {
			echo 0;
		}
	}

	
	public function delete(){		
		$post = implode(',',$_POST['id']);	
		$table = M('banner');
		$data = $table->delete($post);
		if ($data){
			echo '删除成功！';
		}else {
			echo '删除失败！';
		}
	}

	
} 