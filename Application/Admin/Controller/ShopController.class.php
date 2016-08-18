<?php
namespace Admin\Controller;

class ShopController extends CommonController {
	
	public function index(){
		
		$table = M('shop'); // 实例化User对象
		
		//接收查询数据
		if (I('get.keyword')){
			$keyword = I('get.keyword');
			$data['name'] = array('like',"%{$keyword}%");
		}elseif (I('get.verify')){
			$data['groupid'] = I('get.verify');
			$this->assign('verify',I('get.verify'));
		}
		if ($_SESSION['level'] != 0){
			switch ($_SESSION['level']){
				case  1:
					$data['t_shop.provinceid'] = $_SESSION['provinceid'];
					break;
				case  2:
					$data['t_shop.cityid'] = $_SESSION['cityid'];
					break;
				case  3:
					$data['t_shop.areaid'] = $_SESSION['areaid'];
					break;
			}
		}
		$count = $table->where($data)->count();
		$Page       = new \Think\Page($count,14);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show       = $Page->show();// 分页显示输出
		// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
		$res = $table->where($data)->order('ord asc,addtime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$member = M('member');
		$this->assign('data',$res);// 赋值数据集
		$this->assign('page',$show);// 赋值分页输出
		$this->display(); // 输出模板			
	}
	
	public function add(){
		if (IS_POST){
			if (I('post.areaid') == 0){
				alertReplace('请选择所在省市县');
			}
			if (I('post.groupid') == 0){
				alertReplace('请选择所在属分类');
			}
			$table = M('shop');
			$where = I('post.');
			if ($where['discount']){
				$where['discount'] = $where['discount'];
			}
			if ($where['tags']){
				$where['tags'] = implode(',', $where['tags']);
			}
			if ($where['subgroupid']){
				$where['subgroupid'] = implode(',', $where['subgroupid']);
			}
			if ($where['uid']){
				$member = M("member");
				$data = $member->where("phone = '{$where['uid']}'")->find();
				if (!$data){
					alertReplace("店主手机号填写错误!");
				}else {
					$where['uid'] = $data['id'];
				}			
			}
			$where['addtime'] = time();
			$where['content'] = stripslashes(htmlspecialchars_decode($_POST['content']));
			$res = $table->add($where);
			if ($res){
				alertLocation('添加成功！', '/Admin/Shop');
			}else {
				$this->error('添加失败！');
			}		
		}
		$table = M('province');
		$group = M('group');
		$type1 = $table->select();
		$group1 = $group->where('pid = 0')->select();
		$this->assign('type1',$type1);
		$this->assign('group1',$group1);
		$this->display();
	}
	
	public function edit(){
		$where['id'] = I('get.id');
		$table = M('shop');
		if (IS_POST){
			if (I('post.areaid') == 0){
				alertReplace('请选择所在省市县');
			}
			if (I('post.groupid') == 0){
				alertReplace('请选择所在属分类');
			}
			$where = I('post.');
			if ($where['discount']){
				$where['discount'] = $where['discount'];
			}
			if ($where['tags']){
				$where['tags'] = implode(',', $where['tags']);
			}
			if ($where['subgroupid']){
				$where['subgroupid'] = implode(',', $where['subgroupid']);
			}
			$where['content'] = stripslashes(htmlspecialchars_decode($_POST['content']));
			if ($table->save($where)){
				alertBack('修改成功！');
			}else {
				$this->error('修改失败！');
			}	
				
		}
		$province= M('province');
		$group = M('group');
		$data = $table->field('t_shop.*,t_area.area as areatitle,t_city.city as citytitle')
		->join('left join t_city on t_city.cityid = t_shop.cityid')
		->join('left join t_area on t_area.areaid = t_shop.areaid')
		->where("t_shop.id = '{$where['id']}'")->find();
		$type1 = $province->select();
		$group1 = $group->where('pid = 0')->select();
		$group2 = $group->where("pid = '{$data['groupid']}'")->select();
		$select = explode(',', $data['subgroupid']);
		$select1 = explode(',', $data['tags']);
		$this->assign('select',$select);
		$this->assign('select1',$select1);
		$this->assign('type1',$type1);
		$this->assign('group1',$group1);	
		$this->assign('group2',$group2);	
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
		$table = M('shop');
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
			$this->redirect('/Admin/Shop/index'.$str);
		}else {
			$this->error('没有任何修改！');
		}
	}

	
	public function delete(){		
		$post = implode(',',$_POST['id']);	
		$table = M('shop');
		$data = $table->delete($post);
		if ($data){
			echo '删除成功！';
		}else {
			echo '删除失败！';
		}
	}
	
	public function ajaxstate(){
		$data = I('get.');
		$table = M('shop');
		if ($table->save($data)){
			echo 1;
		}else {
			echo 0;
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
	
	public function selectajax3(){
		$id = I('get.id');
		$table = M('group');
		if ($id){
			$data = $table->where("pid = $id")->select();
		}else {
			$data = 0;
		}
		
		if (!$data){
			echo 0;
			exit;
		}
		foreach ($data as $val){		
			$res['str'].="<label style='display: none;'><input type='checkbox' Value='".$val['id']."' name='subgroupid[]' />".$val['title']."</label>";
		}
		foreach ($data as $val){
			$res['str1'].="<a onclick='checkb(this)'>".$val['title']."</a>";
		}
		echo json_encode($res);
	}
	
	
	public function ajax(){
		if (!empty($_POST['param'])){
			$table = M('member');
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