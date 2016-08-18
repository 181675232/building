<?php
namespace Admin\Controller;

class AttrController extends CommonController {
	public function index(){
		$table = D('Attr');
		$group = $table->group_info();
		$this->assign('group',$group);
		$this->display();
	}
	
	public function add(){
		if (IS_POST){
			$user = D('Attr');
			$data = I('post.');			
			if ($user->add($data)){
				alertLocation('添加成功！', '/Admin/Attr');
			}else {
				$this->error('添加失败！');
			}			
		}
		if (I('get.id')){
			$this->assign('pid',I('get.id'));
		}else {
			$this->assign('pid',0);
		}
		$group = D('Attr');
		$groups = $group->group_info();
		$this->assign('group',$groups);
		$this->display();
	}
	
	public function edit(){
		$id = I('get.id');
		$group = D('Attr');
		if (IS_POST){
			$where = I('post.');
			if ($group->save($where)){
				alertBack('修改成功！');		
			}else {
				$this->error('没有任何改动！');
			}
		}
		$data = $group->find($id);
		$groups = $group->group_info();
		$this->assign($data);
		$this->assign('group',$groups);
		$this->display();
	}
	
	public function state(){
		$data = I('get.');
		$user = M('Attr');
		if ($user->save($data)){
			$this->redirect(geturl());
		}else {
			$this->error('没有任何修改！');
		}
	}
	
	public function ajaxstate(){
		$data = I('get.');
		$table = M('Attr');
		if ($table->save($data)){
			echo 1;
		}else {
			echo 0;
		}
	}
	
	public function delete(){
		$post = implode(',',$_POST['id']);
		$user = M('Attr');
		$data = $user->delete($post);
		if ($data){
			$table = M('group_attr');
			foreach (I('post.id') as $val){
				$table->where("attr_id = $val")->delete();
			}
			$t = M('goods_attr');
			$where['attr_id'] = array('in',$_POST['id']);
			$datas = $t->where($where)->getField('goods_id',true);
			if ($datas){
				$where1['id'] = array('in',array_unique($datas));
				$goods = M('goods');
				$res = $goods->where($where1)->delete();
			}
			echo '删除成功！';
		}else {
			echo '删除失败！';
		}
	}
	
    public function delete_check(){
    	//$post = implode(',',$_POST['id']);
     	$table = M('goods_attr');
     	$where['attr_id'] = array('in',$_POST['id']);
     	$data = $table->where($where)->getField('goods_id',true);
     	if ($data){
     		$where1['id'] = array('in',array_unique($data));
     		$goods = M('goods');
     		$res = $goods->where($where1)->find();
     	}
    	if ($res){
    		echo 2;
    	}
    }
	
}