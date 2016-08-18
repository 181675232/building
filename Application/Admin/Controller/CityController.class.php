<?php
namespace Admin\Controller;

use Think;

class CityController extends CommonController {
	
	public function index(){
		
		$table = M('city'); // 实例化User对象
		
		//接收查询数据
		if (I('get.keyword')){
			$keyword = I('get.keyword');
			$data['title'] = array('like',"%{$keyword}%");	
		}
		$data['type'] = 2;
		$count      = $table->where($data)->count();// 查询满足要求的总记录数
		$Page       = new \Think\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show       = $Page->show();// 分页显示输出
		// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
		$res = $table->where($data)->order('isred desc,id asc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('data',$res);// 赋值数据集
		$this->assign('page',$show);// 赋值分页输出
		$this->display(); // 输出模板			
	}
	
	public function add(){
		if (IS_POST){
			$table = M('city');
			$where = I('post.');
			$where['type'] = 2;
			if ($table->add($where)){
				alertLocation('添加成功！', '/Admin/City');
			}else {
				$this->error('添加失败！');
			}		
		}
		$table = M('city');
		$province = $table->where('type = 1')->order('isred desc,id asc')->select();
		$this->assign('province',$province);
		$this->display();
	}
	
	public function edit(){
		$id = I('get.id');
		if (IS_POST){
			$table = M('city');
			if ($table->save(I('post.'))){
				alertBack('修改成功！');
			}else {
				$this->error('没有任何修改！');
			}			
		}
		$table = M('city');
		$data = $table->where("id = $id")->find();
		$province = $table->where('type = 1')->order('isred desc,id asc')->select();
		$this->assign('province',$province);
		$this->assign($data);
		$this->display();
	}
	
	public function state(){
		$data['id'] = I('get.id');
		if (I('get.isred')){
			$data['isred'] = I('get.isred');
		}
		if (I('get.istop')){
			$data['istop'] = I('get.istop');
		}
		if (I('get.iscomment')){
			$data['iscomment'] = I('get.iscomment');
		}
		$table = M('city');
		$str ='/';
		$p = I('get.p');
		$verify = I('get.verify');
		$keyword = I('get.keyword');
		if (I('get.p')){
			$str.= 'p/'.I('get.p').'/';
		}
		if (I('get.verify')){
			$str.= 'verify/'.I('get.verify').'/';
		}
		if (I('get.keyword')){
			$str.= 'keyword/'.I('get.keyword').'/';
		}
		if ($table->save($data)){
			$this->redirect('/Admin/City/index'.$str);
		}else {
			$this->error('没有任何修改！');
		}
	}

	
	public function delete(){		
		$post = implode(',',$_POST['id']);	
		$table = M('city');
		$data = $table->delete($post);
		if ($data){
			echo '删除成功！';
		}else {
			echo '删除失败！';
		}
	}

	
} 