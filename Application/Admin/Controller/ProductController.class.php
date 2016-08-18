<?php
namespace Admin\Controller;

class ProductController extends CommonController {
	
	public function index(){
		$table = M('goods'); // 实例化User对象
		if (I('get.id')){
			$data['pid'] = I('get.id');
			if (!$table->where("pid = '{$data['pid']}'")->find()){
				alertLocation('此系列没添加任何商品，请发布后再进行此操作', '/Admin/Goods/config/id/'.$data['pid']);
			}
		}else {
			$data['pid'] = array('neq',0);
		}
		//接收查询数据
		if (I('get.keyword')){
			$keyword = I('get.keyword');
			$data['title'] = array('like',"%{$keyword}%");
		}
		if (I('get.verify')){
			$data['groupid'] = I('get.verify');
			$this->assign('verify',I('get.verify'));
		}
		if ($_SESSION['level'] != 0){
			switch ($_SESSION['level']){
				case  1:
					$data['provinceid'] = $_SESSION['provinceid'];
					break;
				case  2:
					$data['cityid'] = $_SESSION['cityid'];
					break;
				case  3:
					$data['areaid'] = $_SESSION['areaid'];
					break;
			}
		}
		$count      = $table->where($data)->count();// 查询满足要求的总记录数
		$Page       = new \Think\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show       = $Page->show();// 分页显示输出
		// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
		$res = $table->where($data)	->order('status desc,ord asc,addtime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$goods_attr = M('goods_attr');
		$attr = M('attr');
		
		foreach ($res as $key=>$val){
			$test1 = $goods_attr->where("goods_id = '{$val['id']}'")->getField("attr_id",true);
			if ($test1){
				$where11['id'] = array('in',$test1);
				$test2 = $attr->where($where11)->getField('title',true);
				$res[$key]['pinlei'] = implode(' ', $test2);
			}else {
				$res[$key]['pinlei'] = '无';
			}
		}
		$this->assign('data',$res);// 赋值数据集
		$this->assign('page',$show);// 赋值分页输出
		$this->display(); // 输出模板			
	}
	
	public function add(){
		$table = M('goods');
		$goods_attr = M('goods_attr');
		$attr = M('attr');
		if (I('post.')){
			$where = I('post.');	
			$one = $goods_attr->where("goods_id = '{$where['pid']}'")->getField("attr_id",true);
			$test = $table->field("id")->where("pid = '{$where['pid']}'")->select();
			if ($one){
				if ($test){
					foreach ($test as $val){
						$test1[] = $goods_attr->where("goods_id = '{$val['id']}'")->getField("attr_id",true);
					}
				}
				if ($test1){
					foreach ($test1 as $v){
						if (!array_diff($where['tags'],$v)){
							$where11['id'] = array('in',$v);
							$test2 = $attr->where($where11)->getField('title',true);
							alertLocation('添加失败：您所添加的品类 '.implode(' ', $test2).'已经存在，请查看后再进行添加！', '/Admin/Product/index/id/'.$where['pid']);
						}
					}
					unset($where['tags']);
				}
			}else {
				if ($test){
					alertLocation('添加失败：此系类没有品类，只能有添加一件产品！', '/Admin/Product/index/id/'.$where['pid']);
				}
			}

			
			if ($where['user_simg']){
				$data['user_simg'] = $where['user_simg'];
				$data['user_desc'] = $where['user_desc'];
				unset($where['user_simg']);
				unset($where['user_desc']);		
			}
			unset($where['hidFocusPhoto']);
			$ress = $table->field('groupid,subgroupid')->find($where['pid']);
			$where['groupid'] = $ress['groupid'];
			$where['subgroupid'] = $ress['subgroupid'];
			$where['addtime'] = $data_img['addtime'] = time();
			$data_img['type'] = 'goods';
			$res =$table->add($where);
			if ($res){
				$where2['goods_id'] = $res;
				if (I('post.tags')){
					foreach (I('post.tags') as $val){
						$where2['attr_id'] = $val;
						$goods_attr->add($where2);
					}
				}		
				$oss = new \Org\Util\Oss($this->AccessKeyID, $this->AccessKeySecret, $this->url_nei);
				if ($data['user_simg']){
					$img = M('img');
					$data_img['pid'] = $res;
					for($i=0;$i<count($data['user_simg']);$i++){
						if (is_file('.'.$data['user_simg'][$i])){
							$simg = substr(dirname($data['user_simg'][$i]), -10).'/'.basename($data['user_simg'][$i]);
							if ($oss->uploadFile('jolly',$simg)){
								unlink('./Public/upfile/'.$simg);
								//unlink('./Public/thumb/'.$simg);
								//unlink('./Public/upfile/'.substr(dirname($v), -10).'/thumb_'.basename($v));
								$data_img['simg'] = $this->url.'/shop/'.$simg;
								$data_img['title'] = $data['user_desc'][$i];
								//$data['simgdesc'] = $this->url.'/thumb/'.$simg;
								//print_r($data);
								$img->add($data_img);
							}else {
								alertBack('上传失败');
							}
						}
					}
				}
				alertLocation('添加成功！', '/Admin/Product/index/id/'.$where['pid']);
			}else {
				$this->error('添加失败！');
			}
		}
		$id = I('get.id');
		//$groupid = $table->where("id = $id")->getField('groupid');
		$goods_attr = M('goods_attr');
		$goods_attrs = $goods_attr->field('t_attr.id,t_attr.title,t_attr.remark')
		->join('left join t_attr on t_attr.id = t_goods_attr.attr_id')
		->where("t_goods_attr.goods_id = $id")->select();
		if ($goods_attrs){
			
			foreach ($goods_attrs as $key => $val){
				$goods_attrs[$key]['catid'] = $attr->where("pid = '{$val['id']}'")->select();
			}
		}
		$this->assign('attr',$goods_attrs);
		$this->assign('pid',$id);
		$this->display();

	}
	
	public function edit(){
		$id = I('get.id');
		if (IS_POST){
			$table = M('goods');
			$where = I('post.');
			if ($where['user_simg']){
				$data['user_simg'] = $where['user_simg'];
				$data['user_desc'] = $where['user_desc'];
				unset($where['user_simg']);
				unset($where['user_desc']);		
			}
			unset($where['hidFocusPhoto']);
			if ($where['content']){
				$where['content'] = stripslashes(htmlspecialchars_decode($_POST['content']));
			}
			$img = M('img');
			$img->where("pid = '{$where['id']}' and type = 'goods'")->delete();
			$table->save($where);
			if ($data['user_simg']){
				$oss = new \Org\Util\Oss($this->AccessKeyID, $this->AccessKeySecret, $this->url_nei);			
				$data_img['pid'] = $where['id'];
				$data_img['type'] = 'goods';
				$data_img['addtime'] = time()  ;
				for($i=0;$i<count($data['user_simg']);$i++){
					if (is_file('.'.$data['user_simg'][$i])){
						$simg = substr(dirname($data['user_simg'][$i]), -10).'/'.basename($data['user_simg'][$i]);
						if ($oss->uploadFile('jolly',$simg)){
							unlink('./Public/upfile/'.$simg);
							//unlink('./Public/thumb/'.$simg);
							//unlink('./Public/upfile/'.substr(dirname($v), -10).'/thumb_'.basename($v));
							$data_img['simg'] = $this->url.'/shop/'.$simg;
							$data_img['title'] = $data['user_desc'][$i];
							//$data['simgdesc'] = $this->url.'/thumb/'.$simg;
							//print_r($data);
							$img->add($data_img);
						}else {
							alertBack('上传失败');
						}
					}else {
						$data_img['simg'] = $data['user_simg'][$i];
						$data_img['title'] = $data['user_desc'][$i];
						$img->add($data_img);
					}
				}
			}
			alertBack('修改成功！');			
		}
		$table = M('goods');
		$img = M('img');
		$simg = $img->where("pid = $id and type = 'goods'")->order('id asc')->select();
		$this->assign('data_img',$simg);
		$data = $table->where("id = $id")->find();
		$goods_attr = M('goods_attr');
		$test1 = $goods_attr->where("goods_id = '{$data['id']}'")->getField("attr_id",true);
		if ($test1){
			$attr = M('attr');
			$where11['id'] = array('in',$test1);
			$test2 = $attr->where($where11)->getField('title',true);						
			$data['pinlei'] = implode(' ', $test2);
		}
		$this->assign($data);
		$this->display();
	}
	
	public function state(){
		$data = I('get.');			
		$table = M('goods');
		if ($table->save($data)){
			$this->redirect("/Admin/Product");
		}else {
			$this->error('没有任何修改！');
		}
	}
	
	public function delete(){		
		$post = implode(',',$_POST['id']);	
		$table = M('goods');
		$data = $table->delete($post);
		if ($data){
			echo '删除成功！';
		}else {
			echo '删除失败！';
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
	
// 	public function selectajax3(){
// 		$id = I('get.id');
// 		$table = M('group');
// 		if ($id){
// 			$data = $table->where("pid = $id")->select();
// 		}else {
// 			$data = 0;
// 		}
	
// 		if (!$data){
// 			echo 0;
// 			exit;
// 		}
// 		foreach ($data as $val){
// 			$res['str'].="<label style='display: none;'><input type='checkbox' Value='".$val['title']."' name='tag[]' />".$val['title']."</label>";
// 		}
// 		foreach ($data as $val){
// 			$res['str1'].="<a onclick='checkb(this)'>".$val['title']."</a>";
// 		}
// 		echo json_encode($res);
// 	}
	
	public function selectajax3(){
		$id = I('get.id');
		
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