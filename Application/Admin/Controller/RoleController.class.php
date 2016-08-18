<?php
namespace Admin\Controller;

class RoleController extends CommonController {
	
	public function index(){
		
		$table = M('role'); // 实例化User对象
		
		$count      = $table->count();// 查询满足要求的总记录数
		$Page       = new \Think\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show       = $Page->show();// 分页显示输出
		// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
		$res = $table->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('data',$res);// 赋值数据集
		$this->assign('page',$show);// 赋值分页输出
		$this->display(); // 输出模板			
	}
	
	public function add(){
		if (IS_POST){
			$table = M('role');
			$access = M('access');
			$where['name'] = I('post.name');
			$where['remark'] = I('post.remark');
			$id = $table->add($where);
			if ($id){
				$data = I('post.node_id');
				foreach ($data as $val){
					$r = explode('_', $val);
					$where1['node_id'] = $r[0];
					$where1['level'] = $r[1];
					$where1['role_id'] = $id;
					$access->add($where1);
				}
				alertLocation('添加成功！', '/Admin/Role');
			}else {
				$this->error('添加失败！');
			}		
		}
		$nav = D('Nav');
		$navs = $nav->nav_role();
		$this->assign('nav',$navs);
		$this->display();
	}
	
	public function edit(){
		$id = I('get.id');
		$table = M('role');
		$access = M('access');
		if (IS_POST){
			$where['name'] = I('post.name');
			$where['id'] = I('post.id');
			$where['remark'] = I('post.remark');
			$table->save($where);
			$access->where("role_id = '{$where['id']}'")->delete();
			$data = I('post.node_id');
			foreach ($data as $val){
				$r = explode('_', $val);
				$where1['node_id'] = $r[0];
				$where1['level'] = $r[1];
				$where1['role_id'] = $id;
				$access->add($where1);
			}
			alertBack('修改成功！');	
		}
		$res = $access->where("role_id = $id")->getField('node_id',true);
		$nav = D('Nav');
		$data = $table->find($id);
		$navs = $nav->nav_role();
		$this->assign('nav',$navs);
		$this->assign('select',$res);
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
		$table = M('role');
		$data = $table->delete($post);
		if ($data){
			echo '删除成功！';
		}else {
			echo '删除失败！';
		}
	}

	
} 