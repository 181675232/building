<?php
namespace Admin\Controller;

class Ads1Controller extends CommonController {
	
	public function index(){
		
		$table = M('ads1'); // 实例化User对象
		
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
		$data['pid'] = 0;
		$count      = $table->where($data)->count();// 查询满足要求的总记录数
		$Page       = new \Think\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show       = $Page->show();// 分页显示输出
		// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
		$res = $table->where($data)	->order('ord asc,addtime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('data',$res);// 赋值数据集
		$this->assign('page',$show);// 赋值分页输出
		$this->display(); // 输出模板			
	}
	
	public function config(){
		if (IS_POST){
			$table = M('ads1');
			$data = I('post.');
			if ($table->save($data)){
				alertLocation('操作成功！', '/Admin/Ads1/config/pid/'.$data['id']);
			}else {
				$this->error('没有任何修改！');
			}
		}	
		$table = M('ads1'); // 实例化User对象
		$data['pid'] = I('get.pid');
		$res = $table->find($data['pid']);
		$data = $table->where($data)	->select();
		if ($res['type'] == 1){
			foreach ($data as $val){
				$res[$val['title']] = $val['simg'];
			}
		}
		$this->assign('data',$data);// 赋值数据集
		$this->assign('res',$res);// 赋值数据集
		$this->display(); // 输出模板
		
	}
	
	public function add(){
		if (IS_POST){
			$table = M('ads1');
			$where = I('post.');
			if (is_file('.'.$where['simg'])){
				$oss = new \Org\Util\Oss($this->AccessKeyID, $this->AccessKeySecret, $this->url_nei);
				$simg = substr(dirname($where['simg']), -10).'/'.basename($where['simg']);
				if ($oss->uploadFile('jolly',$simg)){
					unlink('./Public/upfile/'.$simg);
					//unlink('./Public/thumb/'.$simg);
					$where['simg'] = $this->url.'/shop/'.$simg;
					//$data['simgdesc'] = $this->url.'/thumb/'.$simg;
				}else {
					alertBack('上传失败');
				}
			}
			if ($where['content']){
				$where['content'] = stripslashes(htmlspecialchars_decode($where['content']));
			}
			$where['addtime'] = time();
			$id = $table->add($where);
			if ($id){
				$data['pid'] = $id;
				if (I('post.type') == 1){
					$data['title'] = 'F1';
					$data['w_h'] = '320*480';
					$table->add($data);
					$data['title'] = 'F2';
					$data['w_h'] = '320*240';
					$table->add($data);
					$data['title'] = 'F3';
					$data['w_h'] = '320*240';
					$table->add($data);
					$data['title'] = 'F4';
					$data['w_h'] = '640*222';
					$table->add($data);
				}
				alertLocation('添加成功！', '/Admin/Ads1/config/pid/'.$id);
			}else {
				$this->error('添加失败！');
			}
			
		}
		$this->display();
	}
	
	public function edit(){
		$id = I('get.id');
		$table = M('ads1');
		
		if (IS_POST){
			$table = M('ads1');
			$data = I('post.');
			if (is_file('.'.$data['simg'])){
				$oss = new \Org\Util\Oss($this->AccessKeyID, $this->AccessKeySecret, $this->url_nei);
				$simg = substr(dirname($data['simg']), -10).'/'.basename($data['simg']);
				if ($oss->uploadFile('jolly',$simg)){
					unlink('./Public/upfile/'.$simg);
					//unlink('./Public/thumb/'.$simg);
					$data['simg'] = $this->url.'/shop/'.$simg;
					//$data['simgdesc'] = $this->url.'/thumb/'.$simg;
				}else {
					alertBack('上传失败');
				}
			}
			if ($data['content']){
				$data['content'] = stripslashes(htmlspecialchars_decode($data['content']));
			}
			if ($table->save($data)){
				alertLocation('操作成功！', '/Admin/Ads1/config/pid/'.$data['pid']);
			}else {
				$this->error('没有任何修改！');
			}				
		}	
		$data = $table->where("id = $id")->find();
		$res = explode('*', $data['w_h']);
		$data['w'] = $res[0]/2;
		$data['h'] = $res[1]/2;
		$this->assign($data);
		$this->display();
	}
	
	public function state(){
		$data = I('get.');			
		$table = M('ads1');
		if ($table->save($data)){
			$this->redirect("/Admin/Ads1");
		}else {
			$this->error('没有任何修改！');
		}
	}
	
	public function delete(){		
		$post = implode(',',$_POST['id']);	
		$table = M('ads1');
		$data = $table->delete($post);
		if ($data){
			echo '删除成功！';
		}else {
			echo '删除失败！';
		}
	}
	
	public function ajaxstate(){
		$data = I('get.');
		$table = M('ads1');
		if ($table->save($data)){
			echo 1;
		}else {
			echo 0;
		}
	}
	
	public function ajax(){
		if (!empty($_POST['param'])){
			$table = M('ads1');
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