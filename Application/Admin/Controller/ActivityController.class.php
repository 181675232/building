<?php
namespace Admin\Controller;

class ActivityController extends CommonController {
	
	public function index(){
		
		$table = M('activity'); // 实例化User对象
		
		//接收查询数据
		if (I('get.keyword')){
			$keyword = I('get.keyword');
			$data['title'] = array('like',"%{$keyword}%");
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
		$count      = $table->where($data)->count();// 查询满足要求的总记录数
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
			$table = M('activity');
			$where = I('post.');
			$shop = M('shop');
			if (!$shop->find(I('post.shopid'))){
				alertBack('商铺不存在');
			}
			if ($_SESSION['level'] != 0){
				if ($_SESSION['provinceid']){
					$where['provinceid'] = $_SESSION['provinceid'];
				}
				if ($_SESSION['cityid']){
					$where['cityid'] = $_SESSION['cityid'];
				}
				if ($_SESSION['areaid']){
					$where['areaid'] = $_SESSION['areaid'];
				}
			}	
			if (!$where['simg']){
				alertBack('图片必须上传');
			}
			if (!$where['img']){
				alertBack('图片必须上传');
			}
			$where['addtime'] = time();
			if ($table->add($where)){
				alertLocation('添加成功！', '/Admin/Activity');
			}else {
				$this->error('添加失败！');
			}
				
		}
		$this->display();
	}
	
	public function edit(){
		$id = I('get.id');
		if (IS_POST){
			$table = M('activity');
			$shop = M('shop');
			if (!$shop->find(I('post.shopid'))){
				alertBack('商铺不存在');
			}
			$data = I('post.');
			if (!$data['simg']){
				alertBack('图片必须上传');
			}
			if (!$data['img']){
				alertBack('图片必须上传');
			}
			if ($table->save($data)){
				alertBack('修改成功！');
			}else {
				$this->error('没有任何修改！');
			}				
		}
		$table = M('activity');
		$data = $table->where("id = $id")->find();
		$this->assign($data);
		$this->display();
	}
	
	public function state(){
		$data = I('get.');			
		$table = M('activity');
		if ($table->save($data)){
			$this->redirect("/Admin/Activity");
		}else {
			$this->error('没有任何修改！');
		}
	}
	
	public function delete(){		
		$post = implode(',',$_POST['id']);	
		$table = M('activity');
		$data = $table->delete($post);
		if ($data){
			echo '删除成功！';
		}else {
			echo '删除失败！';
		}
	}
	
	public function ajaxstate(){
		$data = I('get.');
		$table = M('activity');
		if ($table->save($data)){
			echo 1;
		}else {
			echo 0;
		}
	}
	
	public function ajax(){
		if (!empty($_POST['param'])){
			$table = M('activity');
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