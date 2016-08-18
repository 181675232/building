<?php
namespace Admin\Controller;

class FeedbackController extends CommonController {
	
	public function index(){
		
		$table = M('feedback'); // 实例化User对象
// 		if (I('get.keyword')){
// 			$keyword = I('get.keyword');
// 			$data['t_comment.description'] = array('like',"%{$keyword}%");
// 		}
		$count      = $table->count();// 查询满足要求的总记录数
		$Page       = new \Think\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show       = $Page->show();// 分页显示输出
		// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
		$res = $table->field('t_feedback.*,t_user.username')
		->join('left join t_user on t_user.id = t_feedback.uid')
		->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach ($res as $key=>$val){
			$type = M($val['type']);
			if ($val['type'] == 'news'){
				$res[$key]['type'] = '新闻';
				$res[$key]['description'] = $type->where("id = '{$val['typeid']}'")->getField('title');
			}elseif ($val['type'] == 'user'){
				$res[$key]['type'] = '会员';
				$res[$key]['description'] = $type->where("id = '{$val['typeid']}'")->getField('username');
			}elseif ($val['type'] == 'dynamic'){
				$res[$key]['type'] = '动态';
				$res[$key]['description'] = $type->where("id = '{$val['typeid']}'")->getField('content');
			}elseif ($val['type'] == 'comment'){
				$res[$key]['type'] = '评论';
				$res[$key]['description'] = $type->where("id = '{$val['typeid']}'")->getField('description');
			}elseif ($val['type'] == 'groups'){
				$res[$key]['type'] = '群组';
				$res[$key]['description'] = $type->where("id = '{$val['typeid']}'")->getField('title');
			}			
		}		
		$this->assign('data',$res);// 赋值数据集
		$this->assign('page',$show);// 赋值分页输出
		$this->display(); // 输出模板			
	}	
	
	public function add(){
		if (IS_POST){
			$table = M('Vehicle_mfg');
			if ($table->add(I('post.'))){
				alertLocation('添加成功！', '/Admin/Mfg');
			}else {
				$this->error('添加失败！');
			}		
		}
		$this->display();
	}
	
	public function edit(){
		$id = I('get.id');
		if (IS_POST){
			$table = M('Vehicle_mfg');
			if ($table->save(I('post.'))){
				alertBack('修改成功！');
			}else {
				$this->error('没有任何修改！');
			}			
		}
		$table = M('Vehicle_mfg');
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

	
	public function delete(){		
		$post = implode(',',$_POST['id']);	
		$table = M('feedback');
		$data = $table->delete($post);
		if ($data){
			echo '删除成功！';
		}else {
			echo '删除失败！';
		}
	}

	
} 