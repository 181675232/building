<?php
namespace Admin\Controller;

use Think;

class TypeController extends CommonController {
	
	public function index(){ 
		
		$table = M('vehicle_model_type'); // 实例化User对象
		
		//接收查询数据
		if (I('get.keyword')){
			$keyword = I('get.keyword');
			$data['MODEL'] = array('like',"%{$keyword}%");
			$table = $table->where($data);		
		}elseif (I('get.verify')){
			$data = I('get.');
			$table = $table->where($data);
			$this->assign('verify',I('get.verify'));
		}
		$count      = $table->count();// 查询满足要求的总记录数
		$Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show       = $Page->show();// 分页显示输出
		// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
		$res = $table->where($data)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('data',$res);// 赋值数据集
		$this->assign('page',$show);// 赋值分页输出
		$this->display(); // 输出模板			
	}
	
	public function add(){
		if (IS_POST){
			if (I('post.MODEL_YEAR_ID') == 0){
				alertReplace('请选择所属分类');
			}
			$table = M('vehicle_model_type');
			if ($table->add(I('post.'))){
				alertLocation('添加成功！', '/Admin/Type');
			}else {
				$this->error('添加失败！');
			}		
		}
		$table = M('vehicle_mfg');
		$mfg = $table->select();
		$this->assign('mfg',$mfg);
		$this->display();
	}
	
	public function edit(){
		$id = I('get.id');
		if (IS_POST){
			if (I('post.MODEL_YEAR_ID') == 0){
				alertReplace('请选择所属分类');
			}
			$table = M('vehicle_model_type');
			if ($table->save(I('post.'))){
				alertBack('修改成功！');
			}else {
				$this->error('没有任何修改！');
			}			
		}
		$table = M('vehicle_model_type');
		$data = $table->where("id = $id")->find();
		$year = M('vehicle_model_year');
		$data2 = $year->find($data['model_year_id']);
		$model = M('vehicle_model');
		$data1 = $model->find($data2['model_id']);
		$table = M('vehicle_mfg');
		$mfg = $table->select();
		$this->assign('year',$data2);
		$this->assign('model',$data1);
		$this->assign('mfg',$mfg);
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
		$table = M('vehicle_model_type');
		$data = $table->delete($post);
		if ($data){
			echo '删除成功！';
		}else {
			echo '删除失败！';
		}
	}

	public function selectajax(){
		$id = I('get.id');
		$table = M('vehicle_model');
		$data = $table->where("MFG_ID = $id")->select();
		$res['str'] = "<option value='0'>请选择所属车系</option>";
		$res['str1'] = "<li class='sel' onclick='sel(this)'>请选择所属车系</li>";
		foreach ($data as $val){
			$res['str'].="<option value='".$val['id']."'>".$val['model']."</option>";
		}
		foreach ($data as $val){
			$res['str1'].="<li class='sel' onclick='sel(this)'>".$val['model']."</li>";
		}
		echo json_encode($res);
	}
	
	public function selectajax1(){
		$id = I('get.id');
		$table = M('vehicle_model_year');
		$data = $table->where("MODEL_ID = $id")->select();
		$res['str'] = "<option value='0'>请选择所属年份</option>";
		$res['str1'] = "<li class='sel' onclick='sel(this)'>请选择所属年份</li>";
		foreach ($data as $val){
			$res['str'].="<option value='".$val['id']."'>".$val['model_year']."</option>";
		}
		foreach ($data as $val){
			$res['str1'].="<li class='sel' onclick='sel(this)'>".$val['model_year']."</li>";
		}
		echo json_encode($res);
	}
	
} 