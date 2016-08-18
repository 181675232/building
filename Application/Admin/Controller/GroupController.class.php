<?php
namespace Admin\Controller;

class GroupController extends CommonController {
	public function index(){
		$table = D('Group');
		$group = $table->group_info();
		$this->assign('group',$group);
		$this->display();
	}
	
	public function add(){
		if (IS_POST){
			$user = D('Group');
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
			unset($data['attr']);
			$res = $user->add($data);
			if ($res){
				if (I('post.attr')){
					$t = M('group_attr');
					$where['group_id'] = $res;			
					foreach (I('post.attr') as $val){
						$where['attr_id'] = $val;
						$t->add($where);
					}
				}
				alertLocation('添加成功！', '/Admin/Group');
			}else {
				$this->error('添加失败！');
			}			
		}
		if (I('get.id')){
			$this->assign('pid',I('get.id'));
		}else {
			$this->assign('pid',0);
		}
		$group = D('Group');
		$groups = $group->group_info();
		$this->assign('group',$groups);
		//自定义属性
		$attr = M('attr');
		$attrs = $attr->where("pid = 0")->select();
		$this->assign('attr',$attrs);
		$this->display();
	}
	
	public function edit(){
		$id = I('get.id');
		$group = D('Group');
		
		if (IS_POST){
			
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
					json('400','上传失败');
				}
			}
			unset($data['attr']);
			$res = $group->save($data);

// 			$t = M('group_attr');
// 			$where['group_id'] = $data['id'];
// 			$t->where("group_id = '{$data['id']}'")->delete();
// 			foreach (I('post.attr') as $val){
// 				$where['attr_id'] = $val;
// 				$t->add($where);
// 			}
			alertReplace('修改成功！');		

		}
		$data = $group->find($id);
		$groups = $group->group_info();
		$this->assign($data);
		$this->assign('group',$groups);
		
		//自定义属性
		$attr = M('attr');
		$attrs = $attr->where("pid = 0")->select();
		$this->assign('attr',$attrs);
		
		$group_attr = M('group_attr');
		$group_attrs = $group_attr->where("group_id = $id")->getField('attr_id',true);
		$this->assign('select',$group_attrs);
		
		$this->display();
	}
	
	public function state(){
		$data = I('get.');
		$user = M('Group');
		if ($user->save($data)){
			$this->redirect(geturl());
		}else {
			$this->error('没有任何修改！');
		}
	}
	
	public function ajaxstate(){
		$data = I('get.');
		$table = M('Group');
		if ($table->save($data)){
			echo 1;
		}else {
			echo 0;
		}
	}
	
	public function delete(){
		$post = implode(',',$_POST['id']);
		$user = M('Group');
		$data = $user->delete($post);
		if ($data){
			echo '删除成功！';
		}else {
			echo '删除失败！';
		}
	}
	
}