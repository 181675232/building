<?php
namespace Admin\Controller;

class GroupsController extends CommonController {
	
	public function index(){
		
		$table = M('groups'); // 实例化User对象
		//接收查询数据
		if (I('get.keyword')){
			$keyword = I('get.keyword');
			$data['t_groups.title'] = array('like',"%{$keyword}%");
		}
		if (I('get.verify')){
			$data['t_groups.type'] = I('get.verify');
			$this->assign('verify',I('get.verify'));
		}
		$count      = $table->where($data)->count();// 查询满足要求的总记录数
		$Page       = new \Think\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show       = $Page->show();// 分页显示输出
		// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
		$res = $table->field('t_groups.*,t_user.username,t_user.phone')
		->join('left join t_user on t_user.id = t_groups.uid')
		->where($data)	->order('state asc,id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('data',$res);// 赋值数据集
		$this->assign('page',$show);// 赋值分页输出
		$this->display(); // 输出模板			
	}
	
	public function add(){	
		if (I('post.')){		
			$where = I('post.');
			$user = M('user');
			$userinfo = $user->find($where['uid']);
			if (!$userinfo){
				alertBack('绑定的群主qb号不存在！');
			}
			$table = M('groups');
			$where['addtime'] = time();
			$where['type'] = 3;
			$where['state'] = 2;
			$res =$table->add($where);
			if ($res){			
				$groupuser = M('groupuser');
				$where1['group_id'] = $res;
				$where1['username'] = $userinfo['username'];
				$where1['user_id'] = $where['uid'];
				$where1['level'] = 5;
				$where1['addtime'] = time();
				$res1 = $groupuser->add($where1);
				if ($res1){
					$rongyun = new  \Org\Util\Rongyun($this->appKey,$this->appSecret);
					$r = $rongyun->groupCreate($where['uid'],$res, $where['title']);
					if($r){
						$rong = json_decode($r);
						if($rong->code == 200){
							alertLocation('操作成功', "/Admin/Groups");
						}else {
							alertBack('系统内部错误1');
						}
					}else {
						alertBack('系统内部错误2');
					}
				}else {
					alertBack('添加成员失败');
				}
				alertLocation('创建成功！', '/Admin/Groups');
			}else {
				$this->error('创建失败！');
			}
		}
		$class = M('class');
		$selfcity = M('selfcity');
		$gourpdata = $class->select();
		$city = $selfcity->order('isred desc,id desc')->select();
		$this->assign('group',$gourpdata);
		$this->assign('city',$city);
		$this->display();

	}
	
	public function edit(){
		$id = I('get.id');
		if (IS_POST){
			$table = M('groups');
			$where = I('post.');
			if ($table->save($where)){
				alertBack('修改成功！');
			}else {
				$this->error('没有任何修改！');
			}			
		}
		$table = M('groups');
		$class = M('class');
		$selfcity = M('selfcity');
		$data = $table->where("id = $id")->find();
		$gourpdata = $class->where('pid = 0')->select();
		$city = $selfcity->order('isred desc,id desc')->select();
		$this->assign('group',$gourpdata);
		$this->assign('city',$city);
		$this->assign($data);
		$this->display();
	}
	
	public function state(){
		$data = I('get.');
		$data['state'] = 2;
		$table = M('groups');
		if ($table->save($data)){
			$data = $table->find(I('get.id'));
			$user = M('user');
			$userinfo = $user->find($data['uid']);
			$groupuser = M('groupuser');
			$where['group_id'] = $data['id'];
			$where['username'] = $userinfo['username'];
			$where['user_id'] = $data['uid'];
			$where['level'] = 5;
			$where['addtime'] = time();
			$res = $groupuser->add($where);
			if ($res){
				$rongyun = new  \Org\Util\Rongyun($this->appKey,$this->appSecret);
				$r = $rongyun->groupCreate($data['uid'], $data['id'], $data['title']);
				if($r){
					$rong = json_decode($r);
					if($rong->code == 200){
						$message = M('message');
						$groups = M('groups');
						$res = $groups->find(I('get.id'));
						$data1['state'] = 0;
						$data1['uid'] = $data['uid'];
						$data1['title'] = '您的群组 '.$res['title'].' 通过了审核';
						$data1['content'] = '您的群组 '.$res['title'].' 通过了审核'; 
						$data1['addtime'] = time();
						$data1['type'] = 'user';
						$data1['typeid'] = I('get.id');
						$message->add($data1);
						$jpush = new \Org\Util\Jpush($this->app_key,$this->master_secret);
						if ($userinfo['jpushid']){
							$jpushid[] = $userinfo['jpushid'];
						}
						$array['type'] = 'message';
						$content = $data1['title'];
						if ($jpushid){
							$jpush->push($jpushid, $this->title,$content,$array);
						}
						alertLocation('操作成功', "/Admin/Groups");
					}else {
						alertBack('系统内部错误1');
					}
				}else {
					alertBack('系统内部错误2');
				}
			}else {
				alertBack('添加成员失败');
			}		
		}else {
			$this->error('没有任何修改！');
		}
	}
	
	public function delete(){
		if ($_POST['id']){
			$post = implode(',',$_POST['id']);	
		}elseif ($_GET['id']) {
			$post = $_GET['id'];
		}else {
			alertBack('非法操作');
		}
		$table = M('groups');
		$res = $table->find(I('get.id'));
		$data = $table->delete($post);
		if ($data){
			if ($_POST['id']){
				echo '删除成功！';
			}elseif ($_GET['id']) {
				$message = M('message');
				$user = M('user');
				$userinfo = $user->find($res['uid']);
				$data1['state'] = 0;
				$data1['uid'] = $userinfo['id'];
				$data1['title'] = '您的群组 '.$res['title'].' 未通过审核';
				$data1['content'] = '您的群组 '.$res['title'].' 未通过审核';
				$data1['addtime'] = time();
				$data1['type'] = 'user';
				$data1['typeid'] = I('get.id');
				$message->add($data1);
				$jpush = new \Org\Util\Jpush($this->app_key,$this->master_secret);
				if ($userinfo['jpushid']){
					$jpushid[] = $userinfo['jpushid'];
				}
				$array['type'] = 'message';
				$content = $data1['title'];
				if ($jpushid){
					$jpush->push($jpushid, $this->title,$content,$array);
				}
				alertLocation('操作成功', "/Admin/Groups");
			}else {
				alertBack('非法操作');
			}		
		}else {
			if ($_POST['id']){
				echo '删除失败！';
			}elseif ($_GET['id']) {
				alertBack('操作成功');
			}else {
				alertBack('非法操作');
			}
		}
	}
	
	public function ajaxstate(){
		$data = I('get.');
		$table = M('groups');
		if ($table->save($data)){
			echo 1;
		}else {
			echo 0;
		}
	}
	
	public function ajax(){
		if (!empty($_POST['param'])){
			$table = M('groups');
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