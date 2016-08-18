<?php
namespace Admin\Controller;

class NavController extends CommonController {
	public function index(){
		$nav = D('Nav');
		$navs = $nav->nav_info();
		$this->assign('nav',$navs);
		$this->display();
	}
	
	public function add(){
		if (IS_POST){
			$user = D('Nav');
			$where1 = I('post.');
			$data = I('post.cblActionType');			
			unset($where1['cblActionType']);
			if ($where1['level'] == 2){
				$where1['pid'] = 9;
			}
			$id = $user->add($where1);
			if ($id){
				foreach ($data as $val){
					$res = explode(' ', $val);
					$where['bid'] = $id;
					$where['name'] = $res[0];
					$where['title'] = $res[1];
					$where['level'] = 3;
					$where['state'] = 1;
					$user->add($where);
				}
				alertLocation('导航添加成功！', '/Admin/Nav');
			}else {
				$this->error('添加失败！');
			}			
		}
		if (I('get.id')){
			$this->assign('bid',I('get.id'));
		}else {
			$this->assign('bid',0);
		}
		$nav = D('Nav');
		$navs = $nav->nav_info();
		$this->assign('nav',$navs);
		$this->display();
	}
	
	public function edit(){
		$id = I('get.id');
		$nav = D('Nav');
		$data = I('post.cblActionType');
		if (IS_POST){
			if ($nav->create()){
				$nav->save();
				$where['bid'] = I('post.id');
				$where['level'] = 3;
				$nav->where($where)->delete();
				foreach ($data as $val){
					$res = explode(' ', $val);
					$where['bid'] = I('post.id');
					$where['name'] = $res[0];
					$where['title'] = $res[1];
					$where['level'] = 3;
					$where['state'] = 1;
					$nav->add($where);
				}
				alertLocation('修改成功！', '/Admin/Nav');			
			}else {
				$this->error($nav->getError());
			}
		}
		$data = $nav->find($id);
		$navs = $nav->nav_info();
		$select = $nav->where("bid = $id and level = 3")->getField('name',true);
		$this->assign($data);
		$this->assign('nav',$navs);
		$this->assign('select',$select);
		$this->display();
	}
	
	public function state(){
		$data = I('get.');
		$user = M('Nav');
	
		if ($user->save($data)){
			$this->redirect("/Admin/Nav");
		}else {
			$this->error('没有任何修改！');
		}
	}
	
	public function delete(){
		$post = implode(',',$_POST['id']);
		$user = M('Nav');
		$data = $user->delete($post);
		if ($data){
			echo '删除成功！';
		}else {
			echo '删除失败！';
		}
	}
	
	
	
	//	后台获取导航AJAX
	public function nav(){
		$nav = D('Nav');
		$navs = $nav->nav_info(0,1);
		$sql = '';
		foreach ($navs as $val){
			if ($val){
				$sql.='<div class="list-group" name="'.$val['title'].'">';			
				if ($val['catid'] != ''){
					foreach ($val['catid'] as $val1){
						if ($val1){
							$sql.='<h2>'.$val1['title'].'<i></i></h2><ul>';
							if ($val1['catid'] != ''){
								foreach ($val1['catid'] as $val2){
									if ($val2){
										$sql.='<li><a navid="'.$val2['name'].'" class="item"><span>'.$val2['title'].'</span></a>';
										if ($val2['catid'] != ''){
											$sql.='<ul>';
											foreach ($val2['catid'] as $val3){
												if ($val3){
													$sql.='<li><a navid="'.$val3['name'].'" href="'.$val3['url'].'" target="mainframe" class="item"><span>'.$val3['title'].'</span></a></li>';
												}	
											}
											$sql.='</ul>';
										}
										$sql.='</li>';
									}
								}
							}
						$sql.='</ul>';	
						}
					}
				}
				$sql.='</div>';
			}
		}
		echo $sql;	
	}
}