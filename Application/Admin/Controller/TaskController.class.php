<?php
namespace Admin\Controller;

class TaskController extends CommonController {
	
	public function index(){
		
		$table = M('Task'); // 实例化User对象
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
		$res = $table->where($data)	->order('id asc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$group = M('group');
		$groupdata = $group->where("pid = 0")->select();
		$this->assign('group',$groupdata);
		$this->assign('data',$res);// 赋值数据集
		$this->assign('page',$show);// 赋值分页输出
		$this->display(); // 输出模板			
	}
	
	public function add(){
		$type = I('get.type')?I('get.type'):0;
		
		if (I('post.')){
			$where = array_filter(I('post.'));
			
			$table = M('Task');
			$goods = M('goods');
			if ($_SESSION['level'] != 0){
				if ($_SESSION['provinceid']){
					$where['provinceid'] = $_SESSION['provinceid'];
				}
				if ($_SESSION['cityid']){
					$where['cityid'] = $_SESSION['cityid'];
				}
				if ($_SESSION['areaid']){
					$where['areaid'] = $_SESSION['areaid'];
				}
			}
			if ($where['tag']){
				$where['tag'] = implode(',', $where['tag']);
			}
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
			
			if ($where['user_simg']){
				$data['user_simg'] = $where['user_simg'];
				$data['user_desc'] = $where['user_desc'];
				unset($where['user_simg']);
				unset($where['user_desc']);
				unset($where['hidFocusPhoto']);
			}
			if ($where['content']){
				$where['content'] = stripslashes(htmlspecialchars_decode($_POST['content']));
			}
			if ($where['description']){
				$arr = explode(' ', $where['description']);
				foreach ($arr as $val){
					if (!$goods->find($val)){
						alertBack('产品编号'.$val.'出错');
					}
				}
			}		
			$where['addtime'] = time();
			$res =$table->add($where);
			if ($res){
// 				if ($data['user_simg']){
// 					$img = M('img');
// 					$data_img['pid'] = $res;
// 					for($i=0;$i<count($data['user_simg']);$i++){
// 						$data_img['type'] = 'Task';
// 						$data_img['simg'] = $data['user_simg'][$i];
// 						$data_img['title'] = $data['user_desc'][$i];
// 						$data_img['addtime'] = time();
						
// 						$img->add($data_img);
// 					}
// 				}
				alertLocation('添加成功！', '/Admin/Task');
			}else {
				$this->error('添加失败！');
			}
		}
		$group = M('group');
		$selfcity = M('selfcity');
		$gourpdata = $group->where('pid = 0')->select();
		$tag = $group->where("pid = $type")->select();
		$city = $selfcity->order('isred desc,id desc')->select();
		$this->assign('tag',$tag);
		$this->assign('group',$gourpdata);
		$this->assign('city',$city);
		$this->assign('type',$type);
		$this->display();

	}
	
	public function edit(){
		$id = I('get.id');
		if (IS_POST){
			$table = M('Task');
			$goods =M('goods');
			$where = array_filter(I('post.'));

			if ($where['tag']){
				$where['tag'] = implode(',', $where['tag']);
			}
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
			if ($where['user_simg']){
				$data['user_simg'] = $where['user_simg'];
				$data['user_desc'] = $where['user_desc'];
				unset($where['user_simg']);
				unset($where['user_desc']);
				unset($where['hidFocusPhoto']);
			}
			if ($where['content']){
				$where['content'] = stripslashes(htmlspecialchars_decode($_POST['content']));
			}
			if ($where['description']){
				$arr = explode(' ', $where['description']);
				foreach ($arr as $val){
					if (!$goods->find($val)){
						alertBack('产品编号'.$val.'出错');
					}
				}
			}
			$table->save($where);
			if ($data['user_simg']){
				$img = M('img');
				$img->where("pid = '{$where['id']}'")->delete();
				$data_img['pid'] = $where['id'];
				for($i=0;$i<count($data['user_simg']);$i++){
					$data_img['type'] = 'Task';
					$data_img['simg'] = $data['user_simg'][$i];
					$data_img['title'] = $data['user_desc'][$i];
					$data_img['addtime'] = time();
					$img->add($data_img);
				}
			}
			alertBack('修改成功！');			
		}
		$table = M('Task');
		$group = M('group');
		$selfcity = M('selfcity');
		$img = M('img');
		$simg = $img->where("pid = $id")->order('id asc')->select();
		$this->assign('data_img',$simg);
		$data = $table->where("id = $id")->find();
		$data['tag'] = explode(',', $data['tag']);
		$gourpdata = $group->where('pid = 0')->select();
		$tag = $group->where("pid = '{$data['gourpid']}'")->select();
		$city = $selfcity->order('isred desc,id desc')->select();
		$this->assign('tags',$tag);
		$this->assign('group',$gourpdata);
		$this->assign('city',$city);
		$this->assign($data);
		$this->display();
	}
	
	public function state(){
		$data = I('get.');			
		$table = M('Task');
		if ($table->save($data)){
			$this->redirect("/Admin/Task");
		}else {
			$this->error('没有任何修改！');
		}
	}
	
	public function delete(){		
		$post = implode(',',$_POST['id']);	
		$table = M('Task');
		$data = $table->delete($post);
		if ($data){
			echo '删除成功！';
		}else {
			echo '删除失败！';
		}
	}
	
	public function ajaxstate(){
		$data = I('get.');
		$table = M('Task');
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
			$res['str'].="<label style='display: none;'><input type='checkbox' Value='".$val['title']."' name='tag[]' />".$val['title']."</label>";
		}
		foreach ($data as $val){
			$res['str1'].="<a onclick='checkb(this)'>".$val['title']."</a>";
		}
		echo json_encode($res);
	}
	
	public function ajax(){
		if (!empty($_POST['param'])){
			$table = M('Task');
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