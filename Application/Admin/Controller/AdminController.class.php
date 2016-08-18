<?php
namespace Admin\Controller;

class AdminController extends CommonController {
	
	public function index(){
		
		$table = M('admin'); // 实例化User对象
		
		//接收查询数据
		if (I('get.keyword')){
			$keyword = I('get.keyword');
			$data['t_admin.name'] = array('like',"%{$keyword}%");
		}
		$data['t_admin.name'] = array('neq','admin');
		//$count      = $table->where($data)->count();// 查询满足要求的总记录数
		//$Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		//$show       = $Page->show();// 分页显示输出
		// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
		$res = $table->field('t_admin.id,t_admin.name,t_admin.state,t_role.name as title')
		->join('left join t_role_user on t_role_user.user_id = t_admin.id')
		->join('left join t_role on t_role.id = t_role_user.role_id')
		->where($data)->order('id desc')->select();
		$this->assign('data',$res);// 赋值数据集
		//$this->assign('page',$show);// 赋值分页输出
		$this->display(); // 输出模板			
	}
	
	public function add(){
		if (IS_POST){
			$table = M('admin');
			$where = I('post.');
			if (I('post.role_id') == 1){
				if ($where['provinceid'] == 0){
					alertReplace('请选择所代理的省市区县！');
				}else {
					if ($where['cityid'] == 0){
						$where['level'] = 1;
					}else {
						if ($where['areaid'] == 0){
							$where['level'] = 2;
						}else {
							$where['level'] = 3;
						}
					}
				}			
			}else {
				$where['level'] = 0;
				$where['provinceid'] = 0;
				$where['areaid'] = 0;
				$where['cityid'] = 0;
			}
			$where1['role_id'] = I('post.role_id');
			$where['password'] = md5($where['password']);
			$where['addtime'] = time();
			unset($where['role_id']);
			unset($where['pass']);
			$id = $table->add($where);
			if ($id){
				$role_user = M('role_user');
				$where1['user_id'] = $id;
				if ($role_user->add($where1)){
					alertLocation('添加成功！', '/Admin/Admin');
				}else {
					$this->error('添加出错！');
				}			
			}else {
				$this->error('添加失败！');
			}		
		}
		$table = M('province');
		$type1 = $table->select();
		$role = M('role');
		$data = $role->select();
		$this->assign('role',$data);
		$this->assign('type1',$type1);
		$this->display();
	}
	
	public function edit(){
		$id = I('get.id');
		$table = M('admin');
		if (IS_POST){	
			$where = I('post.');
			if (I('post.role_id') == 1){
				if ($where['provinceid'] == 0){
					alertReplace('请选择所代理的省市区县！');
				}else {
					if ($where['cityid'] == 0){
						$where['level'] = 1;
					}else {
						if ($where['areaid'] == 0){
							$where['level'] = 2;
						}else {
							$where['level'] = 3;
						}
					}
				}
			}else {
				$where['level'] = 0;
				$where['provinceid'] = 0;
				$where['areaid'] = 0;
				$where['cityid'] = 0;
			}
			$data['role_id'] =  I('post.role_id');
			unset($where['role_id']);
			$role_user = M('role_user');	
			$res1 = $table->save($where);
			$res2 = $role_user->where("user_id = '{$where['id']}'")->save($data);
			if ($res1 || $res2){
				alertBack('修改成功！');
			}else {
				$this->error('没有任何修改！');
			}			
		}
		$role = M('role');
		$role = $role->select();	
		$province = M('province');
		$type1 = $province->select();
		$data = $table->field('t_admin.*,t_role_user.role_id,t_area.area as areatitle,t_city.city as citytitle')
		->join('left join t_city on t_city.cityid = t_admin.cityid')
		->join('left join t_area on t_area.areaid = t_admin.areaid')
		->join('left join t_role_user on t_role_user.user_id = t_admin.id')
		->where("t_admin.id = $id")->find();
		$this->assign('role',$role);
		$this->assign('type1',$type1);
		$this->assign($data);
		$this->display();
	}
	
	public function editpass(){
		//$id = I('get.id');	
		if (I('post.')){
			$where['id'] = $where1['id'] = I('session.userid');
			$where['password'] = md5(I('post.password'));
			$table = M('Admin');
			if ($table->where($where)->find()){
				if (checkLength(I('post.newpass'), 6, 'min')){
					alertBack('密码不能小于6位');
				}
				if (!checkEquals(I('post.newpass'), I('post.pass'))){
					alertBack('两次密码不一致');
				}
				$where1['password'] = md5(I('post.newpass'));
				if ($table->save($where1)){
					alertBack('修改成功！');
				}else {
					$this->error('没有任何修改！');
				}
			}else {
				alertBack('原密码错误');
			}
			
		}
		$table = M('Admin');
		$this->display();
	}
	
	public function reset(){
		$where['id'] = I('get.id');
		$table = M('admin');
		if (I('post.')){		
			if (checkLength(I('post.newpass'), 6, 'min')){
				alertBack('密码不能小于6位');
			}
			if (!checkEquals(I('post.newpass'), I('post.pass'))){
				alertBack('两次密码不一致');
			}
			$where['password'] = md5(I('post.newpass'));
			if ($table->save($where)){
				alertBack('修改成功！');
			}else {
				$this->error('没有任何修改！');
			}
		}						
		$data = $table->find($where['id']);
		$this->assign($data);
		$this->display();
	}
	
	public function passajax(){
		if (!empty($_POST['param'])){
			$user = M('Admin');
			$data[$_POST['name']] = $_POST['param'];
			$return = $user->where($data)->find();
			if ($return){
				echo '账号已存在！';
			}else {
				echo 'y';
			}
		}
	}
	
	public function selectajax(){
		$id = I('get.id');
		$table = M('city');
		$data = $table->where("provinceid = $id")->select();
		$res['str'] = "<option value='0'>请选择在市级单位</option>";
		$res['str1'] = "<li class='sel' onclick='sel(this)'>请选择在市级单位</li>";
		foreach ($data as $val){
			$res['str'].="<option value='".$val['cityid']."'>".$val['city']."</option>";
		}
		foreach ($data as $val){
			$res['str1'].="<li class='sel' onclick='sel(this)'>".$val['city']."</li>";
		}
		echo json_encode($res);
	}
	
	public function selectajax1(){
		$id = I('get.id');
		$table = M('area');
		$data = $table->where("cityid = $id")->select();
		$res['str'] = "<option value='0'>请选择在区县单位</option>";
		$res['str1'] = "<li class='sel' onclick='sel(this)'>请选择在区县单位</li>";
		foreach ($data as $val){
			$res['str'].="<option value='".$val['areaid']."'>".$val['area']."</option>";
		}
		foreach ($data as $val){
			$res['str1'].="<li class='sel' onclick='sel(this)'>".$val['area']."</li>";
		}
		echo json_encode($res);
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
		$table = M('Admin');
		$data = $table->delete($post);
		if ($data){
			echo '删除成功！';
		}else {
			echo '删除失败！';
		}
	}

	
} 