<?php
namespace Admin\Controller;

class GoodsController extends CommonController {
	
	public function index(){
		
		$table = M('goods'); // 实例化User对象
		
		//接收查询数据
		if (I('get.keyword')){
			$keyword = I('get.keyword');
			$data['t_goods.title'] = array('like',"%{$keyword}%");
		}elseif (I('get.verify')){
			$data['t_goods.groupid'] = I('get.verify');
			$this->assign('verify',I('get.verify'));
		}
		if ($_SESSION['level'] != 0){
			switch ($_SESSION['level']){
				case  1:
					$data['t_goods.provinceid'] = $_SESSION['provinceid'];
					break;
				case  2:
					$data['t_goods.cityid'] = $_SESSION['cityid'];
					break;
				case  3:
					$data['t_goods.areaid'] = $_SESSION['areaid'];
					break;
			}
		}
		$data['t_goods.pid'] = 0;
		$count = $table->where($data)->count();
		$Page       = new \Think\Page($count,14);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show       = $Page->show();// 分页显示输出
		// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
		$res = $table->field('t_goods.*')
		//->join('left join t_shop on t_goods.pid = t_shop.id')
		->where($data)->order('isred desc,ord asc,addtime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$group = M('group');
		$type = $group->where('pid = 0')->select();
		$this->assign('type',$type);
		$this->assign('data',$res);// 赋值数据集
		$this->assign('page',$show);// 赋值分页输出
		$this->display(); // 输出模板			
	}
	
	public function add(){
		if (IS_POST){
			$where = I('post.');
			$table = M('goods');
			if (is_file('.'.$where['simg'])){
				$oss = new \Org\Util\Oss($this->AccessKeyID, $this->AccessKeySecret, $this->url_nei);
				$simg = substr(dirname($where['simg']), -10).'/'.basename($where['simg']);
				if ($oss->uploadFile('jolly',$simg)){
					unlink('./Public/upfile/'.$simg);
					//unlink('./Public/thumb/'.$simg);
					$where['simg'] = $this->url.'/shop/'.$simg;
					//$where['simgdesc'] = $this->url.'/thumb/'.$simg;
				}else {
					alertBack('上传失败');
				}
			}
			$where['content'] = stripslashes(htmlspecialchars_decode($_POST['content']));
			$where['spec'] = stripslashes(htmlspecialchars_decode($_POST['spec']));
			$where['service'] = stripslashes(htmlspecialchars_decode($_POST['service']));
			$id = $table->add($where);	
			if ($id){
				//$this->assign('id',$id);
				//$this->display('Goods_config');
				alertLocation('','/Admin/Goods/config/id/'.$id);
			}else {
				$this->error('添加失败！');
			}		
		}
		$group = M('group');
		$group1 = $group->where('pid = 0')->select();
		$this->assign('type1',$group1);
		$this->display();
	}
	
	public function config(){
		$table = M('goods');
		if (IS_POST){
			$img = M('img');
			$goods_attr = M('goods_attr');
			$post = I('post.');			
			$group_attr = M('group_attr');
			$where['pid'] = $where1['goods_id'] = $post['id'];
			$group_attrs = $group_attr->field('t_attr.id,t_attr.title,t_attr.remark')
			->join('left join t_attr on t_attr.id = t_group_attr.attr_id')
			->where("t_group_attr.group_id = '{$post['groupid']}'")->select();		
			foreach ($group_attrs as $key => $val){
				$ress = $post['tagss'.$val['id']];
				if ($ress[0]){
					$where1['attr_id'] = $val['id'];
					$goods_attr->add($where1);
				}
			}
			$where['groupid'] = $post['groupid'];
			if ($post['subgroupid']){
				$where['subgroupid'] = $post['subgroupid'];
			}
			$count = count(I('post.tags'));
			$data['type'] = 'goods';
			$where['addtime'] = $data['addtime'] = time();
			$oss = new \Org\Util\Oss($this->AccessKeyID, $this->AccessKeySecret, $this->url_nei);
			foreach ($post['title'] as  $key=>$val){
				$where['title'] = $val;
				$where['prices'] = $post['prices'][$key];
				$where['price'] = $post['price'][$key];
				$where['state'] = $post['state'][$key];
				$where['status'] = $post['status'][$key];
				$arr = explode(',', $post['user_simg'][$key]);
				$data['pid'] = $data1['goods_id'] = $table->add($where);
				if ($data['pid']){
					$arr = explode(',', $post['user_simg'][$key]);
					$array = explode('_', $post['tags'][$key]);
					$array = array_filter($array);
					if($arr){			
						foreach ($arr as $v){
							if (is_file('.'.$v)){							
								$simg = substr(dirname($v), -10).'/'.basename($v);
								if ($oss->uploadFile('jolly',$simg)){
									unlink('./Public/upfile/'.$simg);
									//unlink('./Public/thumb/'.$simg);
									//unlink('./Public/upfile/'.substr(dirname($v), -10).'/thumb_'.basename($v));
									$data['simg'] = $this->url.'/shop/'.$simg;
									//$data['simgdesc'] = $this->url.'/thumb/'.$simg;
									//print_r($data);
									$img->add($data);
								}else {
									alertBack('上传失败');
								}
							}
						}
					}
					if($array){
						foreach ($array as $value){
							$data1['attr_id'] = $value;
							$goods_attr->add($data1);
						}
					}
					
				}
			}
			//$this->assign('id',$id);
			//$this->display('Goods_config');
			$table->where("id = '{$post['id']}'")->setField('state',2);
			alertLocation('添加成功！', '/Admin/Product/index/id/'.$where['pid']);

		}
		$id = I('get.id');
		$res = $table->find($id);
		if ($table->where("pid = $id")->find()){
			alertBack('已发布完成，请不要重复操作');
		}else {
			$goods_attr = M('goods_attr');
			$goods_attr->where("goods_id = $id")->delete();
		}
		$group_attr = M('group_attr');
		$group_attrs = $group_attr->field('t_attr.id,t_attr.title,t_attr.remark')
		->join('left join t_attr on t_attr.id = t_group_attr.attr_id')
		->where("t_group_attr.group_id = '{$res['groupid']}'")->select();
		if ($group_attrs){
			$attr = M('attr');
			foreach ($group_attrs as $key => $val){
				$group_attrs[$key]['catid'] = $attr->where("pid = '{$val['id']}'")->select();
			}
		}
		$this->assign('data',$res);
		$this->assign('attr',$group_attrs);
		$this->display();
	}
	
	public function edit(){	
		$id = $where['id'] = I('get.id');
		$table = M('goods');
		if (IS_POST){
			$where = I('post.');
			if (is_file('.'.$where['simg'])){
				$oss = new \Org\Util\Oss($this->AccessKeyID, $this->AccessKeySecret, $this->url_nei);
				$simg = substr(dirname($where['simg']), -10).'/'.basename($where['simg']);
				if ($oss->uploadFile('jolly',$simg)){
					unlink('./Public/upfile/'.$simg);
					//unlink('./Public/thumb/'.$simg);
					$where['simg'] = $this->url.'/shop/'.$simg;
					//$where['simgdesc'] = $this->url.'/thumb/'.$simg;
				}else {
					alertBack('上传失败');
				}
			}
			$where['content'] = stripslashes(htmlspecialchars_decode($_POST['content']));
			$where['spec'] = stripslashes(htmlspecialchars_decode($_POST['spec']));
			$where['service'] = stripslashes(htmlspecialchars_decode($_POST['service']));
			if ($table->save($where)){
				alertBack('修改成功！');
			}else {
				$this->error('修改失败！');
			}
		}
		$data = $table->where("id = $id")->find();
		if (!$table->where("pid = $id")->find()){
			alertLocation('此系列没添加任何商品，请发布后再进行此操作', '/Admin/Goods/config/id/'.$data['id']);
		}
		$group = M('group');
		$group1 = $group->where('pid = 0')->select();
		if ($data['groupid']){
			$group2 = $group->where("pid = '{$data['groupid']}'")->select();
			$this->assign('type2',$group2);
		}
		$this->assign('type1',$group1);
		$this->assign($data);
		$this->display();
	}
	
	public function state(){
		$data['id'] = I('get.id');
		$table = M('goods');
		if (!$table->where("pid = '{$data['id']}'")->find()){
			alertLocation('此系列没添加任何商品，请发布后再进行此操作', '/Admin/Goods/config/id/'.$data['id']);
		}
		if (I('get.isred')){
			$data['isred'] = I('get.isred');
		}
		if (I('get.istop')){
			$data['istop'] = I('get.istop');
		}
		if (I('get.state')){
			$data['state'] = I('get.state');
		}
		if (I('get.iscomment')){
			$data['iscomment'] = I('get.iscomment');
		}
		$table = M('goods');
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
			$this->redirect('/Admin/Goods/index'.$str);
		}else {
			$this->error('没有任何修改！');
		}
	}
	
	public function delete(){		
		$post = implode(',',$_POST['id']);	
		$table = M('goods');
		$data = $table->delete($post);
		if ($data){
			foreach ($_POST['id'] as $val){
				$table->where("pid = $val")->delete();
			}	
			echo '删除成功！';
		}else {
			echo '删除失败！';
		}
	}
	
	public function pinleiajax(){
		$data = I('get.');
		$table = M('goods');
		if ($table->save($data)){
			echo 1;
		}else {
			echo 0;
		}
	}
	
	public function ajaxstate(){
		$data = I('get.');
		$table = M('goods');
		if ($table->save($data)){
			echo 1;
		}else {
			echo 0;
		}
	}
	
	public function selectajax(){
		$id = I('get.id');
		$table = M('group');
		$data = $table->where("pid = $id")->select();
		$res['str'] = "<option value='0'>请选择二级分类</option>";
		$res['str1'] = "<li class='sel' onclick='sel(this)'>请选择二级分类</li>";
		foreach ($data as $val){
			$res['str'].="<option value='".$val['id']."'>".$val['title']."</option>";
		}
		foreach ($data as $val){
			$res['str1'].="<li class='sel' onclick='sel(this)'>".$val['title']."</li>";
		}
		echo json_encode($res);
	}
	
	public function selectajax3(){
		$id = I('get.id');
		$table = M('group');
		$ress = $table->find($id);
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
			$table = M('goods');
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